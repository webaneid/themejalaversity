<?php
/**
 * Mesin SEO Hybrid Kampus (Yoast/RankMath Booster & Standalone Engine),
 * Open Graph, Twitter Card, Schema.org JSON-LD (`CollegeOrUniversity`,
 * `WebSite`, `SiteNavigationElement`, `BreadcrumbList`, `NewsArticle`,
 * `SpecialAnnouncement`, `EducationEvent`), serta Generative Engine
 * Optimization (GEO `/llms.txt`).
 *
 * Lihat docs/architecture/architecture-seo.md dan ADR-0006.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Periksa apakah plugin SEO pihak ketiga (Yoast SEO, Rank Math, SEOPress) aktif.
 *
 * @return bool
 */
function jalaversity_has_seo_plugin(): bool {
	return defined( 'WPSEO_VERSION' )
		|| class_exists( 'WPSEO_Options' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'SEOPRESS_VERSION' );
}

/**
 * Normalisasi nomor telepon/WhatsApp ke format internasional (+62...).
 *
 * @param string $raw Nomor mentah.
 * @return string
 */
function jalaversity_seo_normalize_phone( string $raw ): string {
	$digits = (string) preg_replace( '/[^0-9]/', '', $raw );
	if ( '' === $digits ) {
		return '';
	}
	if ( str_starts_with( $digits, '0' ) ) {
		$digits = '62' . substr( $digits, 1 );
	}
	return '+' . $digits;
}

/**
 * Potong teks secara rapi pada batas kata maksimal panjang tertentu.
 *
 * @param string $text       Teks sumber.
 * @param int    $max_length Panjang karakter maksimum.
 * @return string
 */
function jalaversity_seo_trim_description( string $text, int $max_length = 158 ): string {
	$clean = trim( (string) preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );
	if ( '' === $clean || strlen( $clean ) <= $max_length ) {
		return $clean;
	}
	$cut   = substr( $clean, 0, $max_length );
	$space = strrpos( $cut, ' ' );
	if ( false !== $space && $space > 80 ) {
		$cut = substr( $cut, 0, $space );
	}
	return rtrim( $cut, '.,;:- ' ) . '...';
}

/**
 * Kumpulkan profil institusi kampus dari jalaversity_options & pengaturan WP
 * (di-cache statis per request).
 *
 * @return array<string, mixed>
 */
function jalaversity_get_campus_profile(): array {
	static $profile = null;
	if ( is_array( $profile ) ) {
		return $profile;
	}

	$site_name = trim( wp_strip_all_tags( (string) get_bloginfo( 'name' ) ) );
	$tagline   = trim( wp_strip_all_tags( (string) get_bloginfo( 'description' ) ) );
	$home_url  = home_url( '/' );

	$about = trim( wp_strip_all_tags( (string) jalaversity_get_option( 'about_body', '' ) ) );
	if ( '' === $about ) {
		$about = trim( wp_strip_all_tags( (string) jalaversity_get_option( 'hero_lead', '' ) ) );
	}

	$address  = trim( wp_strip_all_tags( (string) jalaversity_get_option( 'contact_address', '' ) ) );
	$phone    = trim( wp_strip_all_tags( (string) jalaversity_get_option( 'contact_phone', '' ) ) );
	$whatsapp = trim( wp_strip_all_tags( (string) jalaversity_get_option( 'contact_whatsapp', '' ) ) );
	$email    = sanitize_email( (string) jalaversity_get_option( 'contact_email', '' ) );
	$hours    = trim( wp_strip_all_tags( (string) jalaversity_get_option( 'contact_hours', '' ) ) );
	$pmb_url  = esc_url_raw( (string) jalaversity_get_option( 'pmb_url', '' ) );
	if ( '#' === $pmb_url ) {
		$pmb_url = '';
	}

	$platforms = [ 'facebook', 'instagram', 'youtube', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'tiktok', 'threads' ];
	$socials   = [];
	foreach ( $platforms as $platform ) {
		$s_url = trim( (string) jalaversity_get_option( 'social_' . $platform, '' ) );
		if ( '' !== $s_url && '#' !== $s_url ) {
			$socials[] = esc_url_raw( $s_url );
		}
	}

	$cs_list      = [];
	$raw_contacts = jalaversity_get_option( 'tim_layanan_contacts', [] );
	if ( is_array( $raw_contacts ) ) {
		foreach ( $raw_contacts as $row ) {
			if ( ! is_array( $row ) || empty( $row['whatsapp'] ) ) {
				continue;
			}
			$norm_wa = jalaversity_seo_normalize_phone( (string) $row['whatsapp'] );
			if ( '' !== $norm_wa ) {
				$cs_list[] = [
					'name'  => trim( wp_strip_all_tags( (string) ( $row['nama'] ?? '' ) ) ),
					'role'  => trim( wp_strip_all_tags( (string) ( $row['jabatan'] ?? '' ) ) ),
					'phone' => $norm_wa,
				];
			}
		}
	}

	$logo_url    = '';
	$custom_logo = (int) get_theme_mod( 'custom_logo' );
	if ( $custom_logo > 0 ) {
		$logo_src = wp_get_attachment_image_src( $custom_logo, 'full' );
		if ( is_array( $logo_src ) && ! empty( $logo_src[0] ) ) {
			$logo_url = (string) $logo_src[0];
		}
	}
	if ( '' === $logo_url ) {
		$site_icon = get_site_icon_url( 512 );
		if ( '' !== $site_icon ) {
			$logo_url = $site_icon;
		}
	}

	$profile = [
		'site_name' => $site_name,
		'tagline'   => $tagline,
		'about'     => $about,
		'address'   => $address,
		'phone'     => $phone,
		'whatsapp'  => $whatsapp,
		'email'     => $email,
		'hours'     => $hours,
		'pmb_url'   => $pmb_url,
		'logo_url'  => $logo_url,
		'home_url'  => $home_url,
		'socials'   => array_values( array_unique( array_filter( $socials ) ) ),
		'cs_list'   => $cs_list,
	];

	return $profile;
}

/**
 * Hasilkan Meta Description otomatis sesuai konteks halaman aktif.
 *
 * @return string
 */
function jalaversity_generate_smart_description(): string {
	$profile   = jalaversity_get_campus_profile();
	$site_name = (string) $profile['site_name'];
	$tagline   = (string) $profile['tagline'];
	$about     = (string) $profile['about'];
	$address   = (string) $profile['address'];

	if ( is_front_page() || is_home() ) {
		$parts = array_filter( [ $site_name, $tagline, $about ?: ( $address ? 'Alamat: ' . $address : '' ) ] );
		return jalaversity_seo_trim_description( implode( ' — ', $parts ), 158 );
	}

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$post    = get_post( $post_id );
		if ( ! $post instanceof WP_Post || post_password_required( $post ) ) {
			return '';
		}

		$title    = trim( wp_strip_all_tags( get_the_title( $post ) ) );
		$raw_body = '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;

		if ( 'agenda' === $post->post_type ) {
			$tgl    = (string) get_field( 'tanggal_mulai', $post_id );
			$lokasi = (string) get_field( 'lokasi', $post_id );
			$meta   = array_filter( [ $tgl, $lokasi ] );
			$prefix = 'Agenda Kampus: ' . $title . ( $meta ? ' (' . implode( ', ', $meta ) . ')' : '' ) . ' di ' . $site_name . '. ';
			return jalaversity_seo_trim_description( $prefix . $raw_body, 158 );
		}

		if ( 'pengumuman' === $post->post_type ) {
			$prefix = 'Pengumuman Resmi ' . $site_name . ': ' . $title . '. ';
			return jalaversity_seo_trim_description( $prefix . $raw_body, 158 );
		}

		if ( is_page_template( 'page-templates/page-contact.php' ) ) {
			$desc = 'Hubungi ' . $site_name . ( '' !== $address ? ' — Alamat: ' . $address . '.' : '.' ) . ' Informasi layanan akademik, admisi PMB, dan lokasi kampus.';
			return jalaversity_seo_trim_description( $desc, 158 );
		}

		$trimmed = jalaversity_seo_trim_description( $raw_body, 158 );
		if ( '' !== $trimmed ) {
			return $trimmed;
		}

		return jalaversity_seo_trim_description( $title . ' — ' . $site_name . ( '' !== $tagline ? ' (' . $tagline . ')' : '' ), 158 );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$queried_term = get_queried_object();
		if ( $queried_term instanceof WP_Term ) {
			if ( '' !== trim( $queried_term->description ) ) {
				return jalaversity_seo_trim_description( $queried_term->description, 158 );
			}
			return jalaversity_seo_trim_description( 'Arsip informasi dan artikel ' . $queried_term->name . ' di ' . $site_name . '.', 158 );
		}
	}

	if ( is_post_type_archive() ) {
		$pt_obj = get_queried_object();
		if ( is_object( $pt_obj ) && isset( $pt_obj->labels->name ) ) {
			return jalaversity_seo_trim_description( 'Daftar ' . (string) $pt_obj->labels->name . ' resmi di ' . $site_name . '.', 158 );
		}
	}

	return jalaversity_seo_trim_description( implode( ' — ', array_filter( [ $site_name, $tagline ] ) ), 158 );
}

/**
 * Ambil URL gambar terbaik untuk Open Graph / Twitter Card / Schema.
 *
 * @return string
 */
function jalaversity_get_seo_image_url(): string {
	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		if ( $post_id > 0 && has_post_thumbnail( $post_id ) ) {
			$img = wp_get_attachment_image_src( (int) get_post_thumbnail_id( $post_id ), 'jalaversity-large' );
			if ( is_array( $img ) && ! empty( $img[0] ) ) {
				return (string) $img[0];
			}
		}
	}

	$hero_img_id = (int) jalaversity_get_option( 'hero_image_id', 0 );
	if ( $hero_img_id > 0 ) {
		$hero_src = wp_get_attachment_image_src( $hero_img_id, 'jalaversity-large' );
		if ( is_array( $hero_src ) && ! empty( $hero_src[0] ) ) {
			return (string) $hero_src[0];
		}
	}

	$profile = jalaversity_get_campus_profile();
	return (string) $profile['logo_url'];
}

/**
 * Kumpulkan daftar kata kunci (keywords) untuk halaman aktif.
 *
 * @param int $post_id ID post (0 untuk deteksi otomatis).
 * @return array<int, string>
 */
function jalaversity_get_current_keywords( int $post_id = 0 ): array {
	$profile  = jalaversity_get_campus_profile();
	$keywords = [];

	if ( $post_id <= 0 && is_singular() ) {
		$post_id = (int) get_queried_object_id();
	}

	if ( $post_id > 0 ) {
		$yoast_kw = trim( wp_strip_all_tags( (string) get_post_meta( $post_id, '_yoast_wpseo_focuskw', true ) ) );
		if ( '' !== $yoast_kw ) {
			$keywords[] = $yoast_kw;
		}

		$tags = get_the_tags( $post_id );
		if ( is_array( $tags ) ) {
			foreach ( $tags as $ptag ) {
				if ( $ptag instanceof WP_Term && '' !== trim( $ptag->name ) ) {
					$keywords[] = trim( $ptag->name );
				}
			}
		}

		$cats = get_the_category( $post_id );
		if ( is_array( $cats ) ) {
			foreach ( $cats as $pcat ) {
				if ( $pcat instanceof WP_Term && ! in_array( $pcat->name, [ 'Uncategorized', 'Tak Berkategori' ], true ) ) {
					$keywords[] = trim( $pcat->name );
				}
			}
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$queried_term = get_queried_object();
		if ( $queried_term instanceof WP_Term ) {
			$keywords[] = trim( $queried_term->name );
		}
	}

	if ( '' !== (string) $profile['site_name'] ) {
		$keywords[] = (string) $profile['site_name'];
	}

	$clean_list = [];
	$seen       = [];
	foreach ( $keywords as $kw ) {
		$kw_clean = trim( wp_strip_all_tags( $kw ) );
		$kw_lower = strtolower( $kw_clean );
		if ( '' !== $kw_clean && ! isset( $seen[ $kw_lower ] ) ) {
			$seen[ $kw_lower ] = true;
			$clean_list[]      = $kw_clean;
		}
	}

	return $clean_list;
}

/**
 * Filter Yoast SEO: Isi otomatis Meta Description & OG/Twitter Description bila kosong.
 *
 * @param mixed $desc Deskripsi dari Yoast.
 * @return string
 */
function jalaversity_yoast_fallback_description( mixed $desc ): string {
	if ( is_string( $desc ) && '' !== trim( $desc ) ) {
		return $desc;
	}
	return jalaversity_generate_smart_description();
}
add_filter( 'wpseo_metadesc', 'jalaversity_yoast_fallback_description', 20 );
add_filter( 'wpseo_opengraph_desc', 'jalaversity_yoast_fallback_description', 20 );
add_filter( 'wpseo_twitter_description', 'jalaversity_yoast_fallback_description', 20 );

/**
 * Filter Yoast SEO: Fallback otomatis gambar Open Graph / Twitter.
 *
 * @param mixed $image URL gambar OG dari Yoast.
 * @return string
 */
function jalaversity_yoast_fallback_og_image( mixed $image ): string {
	if ( is_string( $image ) && '' !== trim( $image ) ) {
		return $image;
	}
	return jalaversity_get_seo_image_url();
}
add_filter( 'wpseo_opengraph_image', 'jalaversity_yoast_fallback_og_image', 20 );
add_filter( 'wpseo_twitter_image', 'jalaversity_yoast_fallback_og_image', 20 );

/**
 * Atur direktif robots (`noindex` untuk search & 404, serta `max-image-preview:large` untuk halaman publik).
 *
 * @param array<string, bool|string> $robots Direktif robots WordPress.
 * @return array<string, bool|string>
 */
function jalaversity_seo_robots_directives( array $robots ): array {
	if ( is_search() || is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		return $robots;
	}

	if ( empty( $robots['noindex'] ) ) {
		$robots['max-snippet']       = '-1';
		$robots['max-image-preview'] = 'large';
		$robots['max-video-preview'] = '-1';
	}

	return $robots;
}
add_filter( 'wp_robots', 'jalaversity_seo_robots_directives', 20 );

/**
 * Ambil item menu utama (`primary`) untuk Schema `SiteNavigationElement` (Google Sitelinks) & `/llms.txt`.
 *
 * @return array<int, array{name: string, url: string}>
 */
function jalaversity_get_sitelinks_navigation(): array {
	$items     = [];
	$locations = get_nav_menu_locations();

	if ( is_array( $locations ) && ! empty( $locations['primary'] ) ) {
		$menu_items = wp_get_nav_menu_items( (int) $locations['primary'] );
		if ( is_array( $menu_items ) ) {
			foreach ( $menu_items as $menu_obj ) {
				if ( ! is_object( $menu_obj ) || empty( $menu_obj->url ) || empty( $menu_obj->title ) ) {
					continue;
				}
				if ( isset( $menu_obj->menu_item_parent ) && 0 !== (int) $menu_obj->menu_item_parent ) {
					continue;
				}
				$url   = trim( (string) $menu_obj->url );
				$title = trim( wp_strip_all_tags( (string) $menu_obj->title ) );
				if ( '' === $title || '' === $url || '#' === $url ) {
					continue;
				}
				$items[] = [
					'name' => $title,
					'url'  => $url,
				];
			}
		}
	}

	return $items;
}

/**
 * Bangun node `BreadcrumbList` Schema.org untuk halaman aktif.
 *
 * @return array<string, mixed>|null
 */
function jalaversity_get_breadcrumb_schema_node(): ?array {
	if ( is_front_page() || is_home() ) {
		return null;
	}

	$home_url  = home_url( '/' );
	$positions = [
		[
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => __( 'Beranda', 'jalaversity' ),
			'item'     => $home_url,
		],
	];
	$pos_index = 2;

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$post    = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			if ( 'post' === $post->post_type ) {
				$posts_page_id = (int) get_option( 'page_for_posts' );
				if ( $posts_page_id > 0 ) {
					$positions[] = [
						'@type'    => 'ListItem',
						'position' => $pos_index++,
						'name'     => wp_strip_all_tags( get_the_title( $posts_page_id ) ),
						'item'     => (string) get_permalink( $posts_page_id ),
					];
				}
			} elseif ( ! in_array( $post->post_type, [ 'page', 'attachment' ], true ) ) {
				$pt_obj       = get_post_type_object( $post->post_type );
				$archive_link = get_post_type_archive_link( $post->post_type );
				if ( is_object( $pt_obj ) && isset( $pt_obj->labels->name ) && is_string( $archive_link ) && '' !== $archive_link ) {
					$positions[] = [
						'@type'    => 'ListItem',
						'position' => $pos_index++,
						'name'     => (string) $pt_obj->labels->name,
						'item'     => $archive_link,
					];
				}
			} elseif ( 'page' === $post->post_type && $post->post_parent > 0 ) {
				$ancestors = array_reverse( get_post_ancestors( $post ) );
				foreach ( $ancestors as $ancestor_id ) {
					$positions[] = [
						'@type'    => 'ListItem',
						'position' => $pos_index++,
						'name'     => wp_strip_all_tags( get_the_title( $ancestor_id ) ),
						'item'     => (string) get_permalink( $ancestor_id ),
					];
				}
			}

			$positions[] = [
				'@type'    => 'ListItem',
				'position' => $pos_index,
				'name'     => wp_strip_all_tags( get_the_title( $post ) ),
				'item'     => (string) get_permalink( $post ),
			];
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$queried_term = get_queried_object();
		if ( $queried_term instanceof WP_Term ) {
			$term_link = get_term_link( $queried_term );
			if ( is_string( $term_link ) ) {
				$positions[] = [
					'@type'    => 'ListItem',
					'position' => $pos_index,
					'name'     => $queried_term->name,
					'item'     => $term_link,
				];
			}
		}
	} elseif ( is_post_type_archive() ) {
		$pt_obj = get_queried_object();
		if ( is_object( $pt_obj ) && isset( $pt_obj->name, $pt_obj->labels->name ) ) {
			$archive_link = get_post_type_archive_link( (string) $pt_obj->name );
			if ( is_string( $archive_link ) ) {
				$positions[] = [
					'@type'    => 'ListItem',
					'position' => $pos_index,
					'name'     => (string) $pt_obj->labels->name,
					'item'     => $archive_link,
				];
			}
		}
	}

	if ( count( $positions ) < 2 ) {
		return null;
	}

	return [
		'@type'           => 'BreadcrumbList',
		'@id'             => home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) ) . '#breadcrumb',
		'itemListElement' => $positions,
	];
}

/**
 * Bangun node Schema `EducationEvent` / `Event` untuk CPT `agenda`.
 *
 * @param WP_Post $post    Objek post agenda.
 * @param string  $org_id  ID node organisasi kampus.
 * @param string  $img_url URL gambar acara.
 * @param string  $desc    Deskripsi acara.
 * @return array<string, mixed>
 */
function jalaversity_build_agenda_event_schema( WP_Post $post, string $org_id, string $img_url, string $desc ): array {
	$post_id       = $post->ID;
	$permalink     = (string) get_permalink( $post );
	$tgl_mulai     = (string) get_field( 'tanggal_mulai', $post_id );
	$tgl_selesai   = (string) get_field( 'tanggal_selesai', $post_id );
	$jam_mulai     = (string) get_field( 'jam_mulai', $post_id );
	$jam_selesai   = (string) get_field( 'jam_selesai', $post_id );
	$tipe_lokasi   = (string) ( get_field( 'tipe_lokasi', $post_id ) ?: 'offline' );
	$lokasi        = (string) get_field( 'lokasi', $post_id );
	$link_online   = (string) get_field( 'link_online', $post_id );
	$penyelenggara = (string) get_field( 'penyelenggara', $post_id );
	$kuota         = absint( get_field( 'kuota', $post_id ) );
	$narasumber    = get_field( 'narasumber', $post_id );

	$start_date = $tgl_mulai ?: (string) get_the_date( 'Y-m-d', $post );
	if ( $jam_mulai && preg_match( '/^\d{2}:\d{2}/', $jam_mulai ) ) {
		$start_date .= 'T' . substr( $jam_mulai, 0, 5 ) . ':00+07:00';
	}

	$end_base = $tgl_selesai ?: $tgl_mulai;
	$end_date = '';
	if ( $end_base ) {
		$end_date = $end_base;
		if ( $jam_selesai && preg_match( '/^\d{2}:\d{2}/', $jam_selesai ) ) {
			$end_date .= 'T' . substr( $jam_selesai, 0, 5 ) . ':00+07:00';
		}
	}

	$attendance_map = [
		'offline' => 'https://schema.org/OfflineEventAttendanceMode',
		'online'  => 'https://schema.org/OnlineEventAttendanceMode',
		'hybrid'  => 'https://schema.org/MixedEventAttendanceMode',
	];

	$event_node = [
		'@type'               => [ 'EducationEvent', 'Event' ],
		'@id'                 => $permalink . '#event',
		'name'                => wp_strip_all_tags( get_the_title( $post ) ),
		'description'         => $desc,
		'url'                 => $permalink,
		'startDate'           => $start_date,
		'eventStatus'         => 'https://schema.org/EventScheduled',
		'eventAttendanceMode' => $attendance_map[ $tipe_lokasi ] ?? $attendance_map['offline'],
		'organizer'           => '' !== $penyelenggara
			? [
				'@type' => 'Organization',
				'name'  => $penyelenggara,
			]
			: [ '@id' => $org_id ],
	];

	if ( '' !== $end_date ) {
		$event_node['endDate'] = $end_date;
	}
	if ( '' !== $img_url ) {
		$event_node['image'] = [ $img_url ];
	}
	if ( $kuota > 0 ) {
		$event_node['maximumAttendeeCapacity'] = $kuota;
	}

	$locations = [];
	if ( 'online' !== $tipe_lokasi && '' !== $lokasi ) {
		$locations[] = [
			'@type' => 'Place',
			'name'  => $lokasi,
		];
	}
	if ( 'offline' !== $tipe_lokasi && '' !== $link_online ) {
		$locations[] = [
			'@type' => 'VirtualLocation',
			'url'   => esc_url_raw( $link_online ),
		];
	}
	if ( 1 === count( $locations ) ) {
		$event_node['location'] = $locations[0];
	} elseif ( count( $locations ) > 1 ) {
		$event_node['location'] = $locations;
	}

	if ( is_array( $narasumber ) && $narasumber ) {
		$performers = [];
		foreach ( $narasumber as $spk ) {
			if ( ! is_array( $spk ) || empty( $spk['nama'] ) ) {
				continue;
			}
			$p_item = [
				'@type' => 'Person',
				'name'  => wp_strip_all_tags( (string) $spk['nama'] ),
			];
			if ( ! empty( $spk['jabatan'] ) ) {
				$p_item['jobTitle'] = wp_strip_all_tags( (string) $spk['jabatan'] );
			}
			$performers[] = $p_item;
		}
		if ( $performers ) {
			$event_node['performer'] = $performers;
		}
	}

	return $event_node;
}

/**
 * Bangun JSON-LD `@graph` lengkap untuk SEO Kampus, Google Sitelinks, & Knowledge Graph.
 *
 * @return array<string, mixed>
 */
function jalaversity_build_schema_graph(): array {
	$has_seo_plugin = jalaversity_has_seo_plugin();
	$profile        = jalaversity_get_campus_profile();
	$home_url       = (string) $profile['home_url'];
	$org_id         = $home_url . '#organization';
	$web_id         = $home_url . '#website';

	// 1. Node CollegeOrUniversity / EducationalOrganization.
	$org_node = [
		'@type' => [ 'CollegeOrUniversity', 'EducationalOrganization', 'Organization' ],
		'@id'   => $org_id,
		'name'  => (string) $profile['site_name'],
		'url'   => $home_url,
	];

	if ( '' !== (string) $profile['logo_url'] ) {
		$org_node['logo']  = [
			'@type' => 'ImageObject',
			'@id'   => $home_url . '#logo',
			'url'   => (string) $profile['logo_url'],
		];
		$org_node['image'] = (string) $profile['logo_url'];
	}
	if ( '' !== (string) $profile['about'] ) {
		$org_node['description'] = (string) $profile['about'];
	} elseif ( '' !== (string) $profile['tagline'] ) {
		$org_node['description'] = (string) $profile['tagline'];
	}
	if ( '' !== (string) $profile['address'] ) {
		$org_node['address'] = [
			'@type'          => 'PostalAddress',
			'streetAddress'  => (string) $profile['address'],
			'addressCountry' => 'ID',
		];
	}
	if ( '' !== (string) $profile['phone'] ) {
		$org_node['telephone'] = (string) $profile['phone'];
	} elseif ( '' !== (string) $profile['whatsapp'] ) {
		$org_node['telephone'] = jalaversity_seo_normalize_phone( (string) $profile['whatsapp'] );
	}
	if ( '' !== (string) $profile['email'] ) {
		$org_node['email'] = (string) $profile['email'];
	}
	if ( ! empty( $profile['socials'] ) ) {
		$org_node['sameAs'] = $profile['socials'];
	}

	$contact_points = [];
	if ( '' !== (string) $profile['phone'] ) {
		$contact_points[] = [
			'@type'             => 'ContactPoint',
			'telephone'         => (string) $profile['phone'],
			'contactType'       => 'customer service',
			'availableLanguage' => [ 'Indonesian' ],
		];
	}
	if ( ! empty( $profile['cs_list'] ) && is_array( $profile['cs_list'] ) ) {
		foreach ( $profile['cs_list'] as $cs ) {
			$contact_points[] = [
				'@type'             => 'ContactPoint',
				'telephone'         => (string) $cs['phone'],
				'contactType'       => '' !== (string) $cs['role'] ? (string) $cs['role'] : 'admissions',
				'name'              => (string) $cs['name'],
				'availableLanguage' => [ 'Indonesian' ],
			];
		}
	}
	if ( $contact_points ) {
		$org_node['contactPoint'] = $contact_points;
	}

	$graph = [ $org_node ];

	// 2. Node WebSite + Sitelinks Searchbox (hanya bila tidak ada plugin SEO agar tidak duplikat).
	if ( ! $has_seo_plugin ) {
		$graph[] = [
			'@type'           => 'WebSite',
			'@id'             => $web_id,
			'url'             => $home_url,
			'name'            => (string) $profile['site_name'],
			'description'     => (string) $profile['tagline'],
			'publisher'       => [ '@id' => $org_id ],
			'inLanguage'      => get_bloginfo( 'language' ),
			'potentialAction' => [
				[
					'@type'       => 'SearchAction',
					'target'      => [
						'@type'       => 'EntryPoint',
						'urlTemplate' => $home_url . '?s={search_term_string}',
					],
					'query-input' => 'required name=search_term_string',
				],
			],
		];
	}

	// 3. Node SiteNavigationElement dari Menu Utama (`primary`).
	$nav_items = jalaversity_get_sitelinks_navigation();
	if ( $nav_items ) {
		$nav_elements = [];
		$pos          = 1;
		foreach ( $nav_items as $nav ) {
			$nav_elements[] = [
				'@type'    => 'SiteNavigationElement',
				'position' => $pos++,
				'name'     => $nav['name'],
				'url'      => $nav['url'],
			];
		}
		$graph[] = [
			'@type'           => 'ItemList',
			'@id'             => $home_url . '#site-navigation',
			'name'            => __( 'Navigasi Utama', 'jalaversity' ),
			'itemListElement' => $nav_elements,
		];
	}

	// 4. Node BreadcrumbList JSON-LD (bila tidak ada plugin SEO).
	if ( ! $has_seo_plugin ) {
		$breadcrumb_node = jalaversity_get_breadcrumb_schema_node();
		if ( is_array( $breadcrumb_node ) ) {
			$graph[] = $breadcrumb_node;
		}
	}

	// 5. Node Spesifik Halaman (NewsArticle, SpecialAnnouncement, EducationEvent).
	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$post    = get_post( $post_id );
		if ( $post instanceof WP_Post && ! post_password_required( $post ) ) {
			$permalink = (string) get_permalink( $post );
			$title     = wp_strip_all_tags( get_the_title( $post ) );
			$desc      = jalaversity_generate_smart_description();
			$img_url   = jalaversity_get_seo_image_url();
			$keywords  = jalaversity_get_current_keywords( $post_id );

			if ( 'agenda' === $post->post_type ) {
				$graph[] = jalaversity_build_agenda_event_schema( $post, $org_id, $img_url, $desc );
			} elseif ( 'pengumuman' === $post->post_type ) {
				$announcement_node = [
					'@type'         => [ 'SpecialAnnouncement', 'Article' ],
					'@id'           => $permalink . '#announcement',
					'name'          => $title,
					'headline'      => $title,
					'description'   => $desc,
					'text'          => $desc,
					'url'           => $permalink,
					'datePosted'    => (string) get_the_date( DATE_W3C, $post ),
					'datePublished' => (string) get_the_date( DATE_W3C, $post ),
					'dateModified'  => (string) get_the_modified_date( DATE_W3C, $post ),
					'publisher'     => [ '@id' => $org_id ],
					'inLanguage'    => get_bloginfo( 'language' ),
				];
				if ( '' !== $img_url ) {
					$announcement_node['image'] = [ $img_url ];
				}
				$graph[] = $announcement_node;
			} elseif ( 'post' === $post->post_type && ! $has_seo_plugin ) {
				$author_id   = (int) $post->post_author;
				$author_name = (string) get_the_author_meta( 'display_name', $author_id );
				$cats        = get_the_category( $post_id );
				$sections    = ! empty( $cats ) ? wp_list_pluck( $cats, 'name' ) : [];

				$article_node = [
					'@type'            => [ 'NewsArticle', 'BlogPosting' ],
					'@id'              => $permalink . '#article',
					'mainEntityOfPage' => $permalink,
					'headline'         => $title,
					'description'      => $desc,
					'datePublished'    => (string) get_the_date( DATE_W3C, $post ),
					'dateModified'     => (string) get_the_modified_date( DATE_W3C, $post ),
					'author'           => [
						'@type' => 'Person',
						'name'  => '' !== $author_name ? $author_name : (string) $profile['site_name'],
						'url'   => get_author_posts_url( $author_id ),
					],
					'publisher'        => [ '@id' => $org_id ],
					'isPartOf'         => [ '@id' => $web_id ],
					'inLanguage'       => get_bloginfo( 'language' ),
				];

				if ( '' !== $img_url ) {
					$article_node['image'] = [ $img_url ];
				}
				if ( $keywords ) {
					$article_node['keywords'] = implode( ', ', $keywords );
				}
				if ( $sections ) {
					$article_node['articleSection'] = array_values( $sections );
				}

				$editor = get_field( 'editor', $post_id );
				if ( is_array( $editor ) && ! empty( $editor['display_name'] ) ) {
					$article_node['editor'] = [
						'@type' => 'Person',
						'name'  => (string) $editor['display_name'],
					];
				}

				$graph[] = $article_node;
			}
		}
	}

	return [
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	];
}

/**
 * Cetak meta tags SEO, Open Graph, Twitter Card, link `/llms.txt` (GEO), dan JSON-LD `@graph` di `<head>`.
 */
function jalaversity_render_seo_head(): void {
	if ( is_admin() || is_feed() || is_robots() ) {
		return;
	}

	$has_seo_plugin = jalaversity_has_seo_plugin();
	$profile        = jalaversity_get_campus_profile();
	$description    = jalaversity_generate_smart_description();
	$keywords       = jalaversity_get_current_keywords();
	$img_url        = jalaversity_get_seo_image_url();
	$llms_url       = home_url( '/llms.txt' );

	if ( ! $has_seo_plugin ) {
		if ( '' !== $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		}

		if ( ! is_404() && ! is_search() ) {
			$canonical = wp_get_canonical_url();
			if ( ! is_string( $canonical ) || '' === $canonical ) {
				$canonical = home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) );
			}
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		} else {
			$canonical = home_url( '/' );
		}

		$og_type  = is_singular( [ 'post', 'pengumuman', 'agenda' ] ) ? 'article' : 'website';
		$og_title = wp_get_document_title();

		echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		if ( '' !== $description ) {
			echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		}
		echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( (string) $profile['site_name'] ) . '">' . "\n";
		if ( '' !== $img_url ) {
			echo '<meta property="og:image" content="' . esc_url( $img_url ) . '">' . "\n";
		}
		if ( is_singular( [ 'post', 'pengumuman', 'agenda' ] ) ) {
			echo '<meta property="article:published_time" content="' . esc_attr( (string) get_the_date( DATE_W3C ) ) . '">' . "\n";
			echo '<meta property="article:modified_time" content="' . esc_attr( (string) get_the_modified_date( DATE_W3C ) ) . '">' . "\n";
		}

		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		if ( '' !== $description ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
		}
		if ( '' !== $img_url ) {
			echo '<meta name="twitter:image" content="' . esc_url( $img_url ) . '">' . "\n";
		}
	}

	if ( $keywords ) {
		echo '<meta name="keywords" content="' . esc_attr( implode( ', ', $keywords ) ) . '">' . "\n";
	}

	echo '<link rel="alternate" type="text/plain" title="LLMs.txt - Ringkasan Pengetahuan AI Kampus" href="' . esc_url( $llms_url ) . '">' . "\n";

	$schema_graph = jalaversity_build_schema_graph();
	$json_ld      = wp_json_encode( $schema_graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG );
	if ( is_string( $json_ld ) && '' !== $json_ld ) {
		echo '<script type="application/ld+json" class="jalaversity-schema-graph">' . $json_ld . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'jalaversity_render_seo_head', 5 );

/**
 * Otomatis isi atribut `alt` pada gambar attachment yang kosong dengan judul post + nama kampus.
 *
 * @param array<string, string> $attr       Atribut tag `<img>`.
 * @param WP_Post               $attachment Objek post attachment.
 * @return array<string, string>
 */
function jalaversity_auto_image_alt_attributes( array $attr, WP_Post $attachment ): array {
	$current_alt = isset( $attr['alt'] ) ? trim( (string) $attr['alt'] ) : '';
	if ( '' === $current_alt ) {
		$profile  = jalaversity_get_campus_profile();
		$fallback = '';
		if ( is_singular() ) {
			$fallback = trim( wp_strip_all_tags( get_the_title() ) );
		}
		if ( '' === $fallback && '' !== trim( $attachment->post_title ) ) {
			$fallback = trim( wp_strip_all_tags( $attachment->post_title ) );
		}
		if ( '' !== $fallback && '' !== (string) $profile['site_name'] ) {
			$fallback .= ' - ' . (string) $profile['site_name'];
		} elseif ( '' === $fallback ) {
			$fallback = (string) $profile['site_name'];
		}
		$attr['alt'] = $fallback;
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'jalaversity_auto_image_alt_attributes', 10, 2 );

/**
 * Bangun isi dokumen Markdown `/llms.txt` untuk Generative Engine Optimization (GEO).
 *
 * @return string
 */
function jalaversity_generate_llms_txt_content(): string {
	$cached = get_transient( 'jalaversity_llms_txt_cache' );
	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}

	$profile = jalaversity_get_campus_profile();
	$lines   = [];

	$lines[] = '# ' . (string) $profile['site_name'];
	$lines[] = '';

	if ( '' !== (string) $profile['tagline'] ) {
		$lines[] = '> ' . (string) $profile['tagline'];
		$lines[] = '';
	}
	if ( '' !== (string) $profile['about'] ) {
		$lines[] = (string) $profile['about'];
		$lines[] = '';
	}

	$lines[] = '## Identitas & Kontak Resmi Kampus';
	$lines[] = '- Website Resmi: ' . (string) $profile['home_url'];
	if ( '' !== (string) $profile['address'] ) {
		$lines[] = '- Alamat Kampus: ' . (string) $profile['address'];
	}
	if ( '' !== (string) $profile['phone'] ) {
		$lines[] = '- Telepon: ' . (string) $profile['phone'];
	}
	if ( '' !== (string) $profile['whatsapp'] ) {
		$lines[] = '- WhatsApp: ' . (string) $profile['whatsapp'];
	}
	if ( '' !== (string) $profile['email'] ) {
		$lines[] = '- Email: ' . (string) $profile['email'];
	}
	if ( '' !== (string) $profile['hours'] ) {
		$lines[] = '- Jam Operasional: ' . (string) $profile['hours'];
	}
	if ( '' !== (string) $profile['pmb_url'] ) {
		$lines[] = '- Portal Pendaftaran Mahasiswa Baru (PMB): ' . (string) $profile['pmb_url'];
	}
	if ( ! empty( $profile['cs_list'] ) && is_array( $profile['cs_list'] ) ) {
		foreach ( $profile['cs_list'] as $cs ) {
			$role_str = '' !== (string) $cs['role'] ? ' (' . (string) $cs['role'] . ')' : '';
			$lines[]  = '- Tim Layanan & Admisi: ' . (string) $cs['name'] . $role_str . ' — WhatsApp ' . (string) $cs['phone'];
		}
	}
	$lines[] = '';

	$nav_items = jalaversity_get_sitelinks_navigation();
	if ( $nav_items ) {
		$lines[] = '## Halaman Utama (Sitelinks)';
		foreach ( $nav_items as $nav ) {
			$lines[] = '- [' . $nav['name'] . '](' . $nav['url'] . ')';
		}
		$lines[] = '';
	}

	if ( function_exists( 'jalaversity_get_faculties' ) ) {
		$faculties = jalaversity_get_faculties();
		if ( $faculties ) {
			$lines[] = '## Fakultas & Program Studi';
			foreach ( $faculties as $fac ) {
				$f_title = trim( wp_strip_all_tags( (string) ( $fac['title'] ?? '' ) ) );
				$f_desc  = trim( wp_strip_all_tags( (string) ( $fac['desc'] ?? '' ) ) );
				$f_url   = (string) ( $fac['link']['url'] ?? '' );
				$label   = ( '' !== $f_url && '#' !== $f_url ) ? '[' . $f_title . '](' . $f_url . ')' : $f_title;
				$lines[] = '- ' . $label . ( '' !== $f_desc ? ': ' . $f_desc : '' );
			}
			$lines[] = '';
		}
	}

	$agenda_posts = get_posts(
		[
			'post_type'      => 'agenda',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
			'has_password'   => false,
		]
	);
	if ( $agenda_posts ) {
		$lines[] = '## Agenda & Kegiatan Kampus';
		foreach ( $agenda_posts as $ag ) {
			if ( ! $ag instanceof WP_Post ) {
				continue;
			}
			$ag_title  = trim( wp_strip_all_tags( get_the_title( $ag ) ) );
			$ag_date   = (string) get_field( 'tanggal_mulai', $ag->ID );
			$ag_lokasi = (string) get_field( 'lokasi', $ag->ID );
			$ag_meta   = implode( ', ', array_filter( [ $ag_date, $ag_lokasi ] ) );
			$lines[]   = '- [' . $ag_title . '](' . (string) get_permalink( $ag ) . ')' . ( '' !== $ag_meta ? ' (' . $ag_meta . ')' : '' );
		}
		$lines[] = '';
	}

	$pengumuman_posts = get_posts(
		[
			'post_type'      => 'pengumuman',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
			'has_password'   => false,
		]
	);
	if ( $pengumuman_posts ) {
		$lines[] = '## Pengumuman Resmi Kampus';
		foreach ( $pengumuman_posts as $pg ) {
			if ( ! $pg instanceof WP_Post ) {
				continue;
			}
			$pg_title = trim( wp_strip_all_tags( get_the_title( $pg ) ) );
			$pg_date  = (string) get_the_date( 'Y-m-d', $pg );
			$pg_desc  = jalaversity_seo_trim_description( '' !== $pg->post_excerpt ? $pg->post_excerpt : $pg->post_content, 110 );
			$lines[]  = '- [' . $pg_title . '](' . (string) get_permalink( $pg ) . ') (' . $pg_date . ')' . ( '' !== $pg_desc ? ': ' . $pg_desc : '' );
		}
		$lines[] = '';
	}

	$recent_posts = get_posts(
		[
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
			'has_password'   => false,
		]
	);
	if ( $recent_posts ) {
		$lines[] = '## Berita & Artikel Akademik Terbaru';
		foreach ( $recent_posts as $rp ) {
			if ( ! $rp instanceof WP_Post ) {
				continue;
			}
			$rp_title = trim( wp_strip_all_tags( get_the_title( $rp ) ) );
			$rp_date  = (string) get_the_date( 'Y-m-d', $rp );
			$rp_desc  = jalaversity_seo_trim_description( '' !== $rp->post_excerpt ? $rp->post_excerpt : $rp->post_content, 110 );
			$lines[]  = '- [' . $rp_title . '](' . (string) get_permalink( $rp ) . ') (' . $rp_date . ')' . ( '' !== $rp_desc ? ': ' . $rp_desc : '' );
		}
		$lines[] = '';
	}

	$output = implode( "\n", $lines );
	set_transient( 'jalaversity_llms_txt_cache', $output, 6 * HOUR_IN_SECONDS );

	return $output;
}

/**
 * Bersihkan cache `/llms.txt` saat ada post atau opsi tema yang disimpan/diperbarui.
 */
function jalaversity_flush_llms_txt_cache(): void {
	delete_transient( 'jalaversity_llms_txt_cache' );
}
add_action( 'save_post', 'jalaversity_flush_llms_txt_cache' );
add_action( 'update_option_jalaversity_options', 'jalaversity_flush_llms_txt_cache' );

/**
 * Tangani request ke `/llms.txt` atau `?llms_txt=1` secara dinamis.
 */
function jalaversity_serve_llms_txt_endpoint(): void {
	$req_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$req_path = (string) wp_parse_url( $req_uri, PHP_URL_PATH );
	$is_llms  = str_ends_with( $req_path, '/llms.txt' )
		|| ( isset( $_GET['llms_txt'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['llms_txt'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! $is_llms ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: index, follow' );

	echo jalaversity_generate_llms_txt_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'init', 'jalaversity_serve_llms_txt_endpoint', 1 );

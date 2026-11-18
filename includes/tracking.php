<?php
/**
 * Integrasi Google, Search Console, Yandex, Bing, Meta & TikTok (`includes/tracking.php`).
 *
 * Admin cukup mengisi kode verifikasi atau ID pelacakan di halaman
 * "Jalaversity → Integrasi & Tracking". Theme mengekstrak & memvalidasi ID
 * dengan regex ketat lalu mencetak skrip resmi di `wp_head` dan `wp_body_open`,
 * selaras dengan standar `sekolahane`, `newsane`, dan `jalawarta`.
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ekstrak kode verifikasi murni dari input yang mungkin berupa tag `<meta ... content="KODE" />`
 * atau hanya `KODE`.
 *
 * @param string $raw Input mentah dari admin.
 * @return string Kode verifikasi bersih.
 */
function jalaversity_extract_verification_token( string $raw ): string {
	$text = trim( $raw );
	if ( '' === $text ) {
		return '';
	}

	if ( preg_match( '/content\s*=\s*["\']([^"\']+)["\']/i', $text, $found_matches ) && ! empty( $found_matches[1] ) ) {
		$text = trim( $found_matches[1] );
	}

	return (string) preg_replace( '/[^a-zA-Z0-9_\-\.]/', '', $text );
}

/**
 * Bersihkan & validasi satu nilai field tracking/verifikasi berdasarkan key-nya.
 *
 * Dipakai saat sanitasi penyimpanan (`jalaversity_sanitize_options()`) maupun
 * saat pembacaan di front-end (`jalaversity_get_tracking_settings()`).
 *
 * @param string $key Key option di `jalaversity_options`.
 * @param string $raw Nilai mentah.
 * @return string Nilai bersih yang tervalidasi (atau string kosong bila tidak valid).
 */
function jalaversity_clean_tracking_field( string $key, string $raw ): string {
	$text = trim( $raw );
	if ( '' === $text ) {
		return '';
	}

	switch ( $key ) {
		case 'gsc_verification':
		case 'yandex_verification':
		case 'bing_verification':
		case 'fb_domain_verify':
			return jalaversity_extract_verification_token( $text );

		case 'gtm_id':
			if ( preg_match( '/(GTM-[A-Z0-9]+)/i', $text, $found_match ) ) {
				return strtoupper( $found_match[1] );
			}
			return '';

		case 'ga4_id':
			if ( preg_match( '/((?:G|UA|AW|DC)-[A-Z0-9\-]+)/i', $text, $found_match ) ) {
				return strtoupper( $found_match[1] );
			}
			return '';

		case 'yandex_metrica_id':
			if ( preg_match( '/([0-9]{5,12})/', $text, $found_match ) ) {
				return $found_match[1];
			}
			return '';

		case 'fb_pixel_id':
			if ( preg_match( '/([0-9]{8,25})/', $text, $found_match ) ) {
				return $found_match[1];
			}
			return '';

		case 'tiktok_pixel_id':
			$upper = strtoupper( $text );
			if ( preg_match( '/^([A-Z0-9]{10,30})$/', $upper, $found_match ) ) {
				return $found_match[1];
			}
			return '';

		default:
			return sanitize_text_field( $text );
	}
}

/**
 * Ambil dan validasi seluruh pengaturan tracking dari `jalaversity_options`.
 *
 * @return array{
 *     gsc: string,
 *     yandex_verify: string,
 *     bing_verify: string,
 *     fb_verify: string,
 *     gtm_id: string,
 *     ga4_id: string,
 *     yandex_metrica: string,
 *     fb_pixel: string,
 *     tiktok_pixel: string
 * }
 */
function jalaversity_get_tracking_settings(): array {
	static $settings = null;
	if ( is_array( $settings ) ) {
		return $settings;
	}

	$settings = [
		'gsc'            => jalaversity_clean_tracking_field( 'gsc_verification', (string) jalaversity_get_option( 'gsc_verification', '' ) ),
		'yandex_verify'  => jalaversity_clean_tracking_field( 'yandex_verification', (string) jalaversity_get_option( 'yandex_verification', '' ) ),
		'bing_verify'    => jalaversity_clean_tracking_field( 'bing_verification', (string) jalaversity_get_option( 'bing_verification', '' ) ),
		'fb_verify'      => jalaversity_clean_tracking_field( 'fb_domain_verify', (string) jalaversity_get_option( 'fb_domain_verify', '' ) ),
		'gtm_id'         => jalaversity_clean_tracking_field( 'gtm_id', (string) jalaversity_get_option( 'gtm_id', '' ) ),
		'ga4_id'         => jalaversity_clean_tracking_field( 'ga4_id', (string) jalaversity_get_option( 'ga4_id', '' ) ),
		'yandex_metrica' => jalaversity_clean_tracking_field( 'yandex_metrica_id', (string) jalaversity_get_option( 'yandex_metrica_id', '' ) ),
		'fb_pixel'       => jalaversity_clean_tracking_field( 'fb_pixel_id', (string) jalaversity_get_option( 'fb_pixel_id', '' ) ),
		'tiktok_pixel'   => jalaversity_clean_tracking_field( 'tiktok_pixel_id', (string) jalaversity_get_option( 'tiktok_pixel_id', '' ) ),
	];

	return $settings;
}

/**
 * Apakah request ini boleh memuat tag pelacakan (bukan admin, feed, robots, atau pratinjau Customizer).
 *
 * @return bool
 */
function jalaversity_should_render_tracking(): bool {
	return ! ( is_admin() || is_feed() || is_robots() || is_customize_preview() );
}

/**
 * Cetak meta verifikasi (Google, Yandex, Bing, Meta) serta skrip resmi GTM, GA4,
 * Yandex Metrica, Meta Pixel & TikTok Pixel di `<head>`.
 *
 * @return void
 */
function jalaversity_render_tracking_head(): void {
	if ( ! jalaversity_should_render_tracking() ) {
		return;
	}

	$t = jalaversity_get_tracking_settings();

	if ( '' !== $t['gsc'] ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $t['gsc'] ) . '" />' . "\n";
	}

	if ( '' !== $t['yandex_verify'] ) {
		echo '<meta name="yandex-verification" content="' . esc_attr( $t['yandex_verify'] ) . '" />' . "\n";
	}

	if ( '' !== $t['bing_verify'] ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( $t['bing_verify'] ) . '" />' . "\n";
	}

	if ( '' !== $t['fb_verify'] ) {
		echo '<meta name="facebook-domain-verification" content="' . esc_attr( $t['fb_verify'] ) . '" />' . "\n";
	}

	if ( '' !== $t['gtm_id'] ) {
		$gtm_js = esc_js( $t['gtm_id'] );
		echo "<!-- Google Tag Manager -->\n";
		echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $gtm_js . "');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ID divalidasi regex & di-esc_js.
	}

	if ( '' !== $t['ga4_id'] ) {
		$ga_url = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $t['ga4_id'] );
		$ga_js  = esc_js( $t['ga4_id'] );
		echo "<!-- Google Analytics (gtag.js) -->\n";
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		echo '<script async src="' . esc_url( $ga_url ) . '"></script>' . "\n";
		echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . $ga_js . "');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ID divalidasi regex & di-esc_js.
	}

	if ( '' !== $t['yandex_metrica'] ) {
		$ym_id = (int) $t['yandex_metrica'];
		echo "<!-- Yandex.Metrika counter -->\n";
		echo "<script>(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();for(var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r){return;}}k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,'script','https://mc.yandex.ru/metrika/tag.js','ym');ym(" . $ym_id . ",'init',{clickmap:true,trackLinks:true,accurateTrackBounce:true});</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ID dicasting ke integer.
	}

	if ( '' !== $t['fb_pixel'] ) {
		$fb_js = esc_js( $t['fb_pixel'] );
		$fb_tr = 'https://www.facebook.com/tr?id=' . rawurlencode( $t['fb_pixel'] ) . '&ev=PageView&noscript=1';
		echo "<!-- Meta Pixel Code -->\n";
		echo "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','" . $fb_js . "');fbq('track','PageView');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ID divalidasi regex & di-esc_js.
		echo '<noscript><img height="1" width="1" style="display:none" src="' . esc_url( $fb_tr ) . '" alt="" /></noscript>' . "\n";
	}

	if ( '' !== $t['tiktok_pixel'] ) {
		$tt_js = esc_js( $t['tiktok_pixel'] );
		echo "<!-- TikTok Pixel Code -->\n";
		echo "<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement('script');o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;var a=document.getElementsByTagName('script')[0];a.parentNode.insertBefore(o,a)};ttq.load('" . $tt_js . "');ttq.page();}(window,document,'ttq');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ID divalidasi regex & di-esc_js.
	}
}
add_action( 'wp_head', 'jalaversity_render_tracking_head', 2 );

/**
 * Cetak `<noscript>` GTM & Yandex Metrica tepat setelah tag pembuka `<body>` (`wp_body_open`).
 *
 * @return void
 */
function jalaversity_render_tracking_body_open(): void {
	if ( ! jalaversity_should_render_tracking() ) {
		return;
	}

	$t = jalaversity_get_tracking_settings();

	if ( '' !== $t['gtm_id'] ) {
		$gtm_ns_url = 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $t['gtm_id'] );
		echo '<!-- Google Tag Manager (noscript) -->' . "\n";
		echo '<noscript><iframe src="' . esc_url( $gtm_ns_url ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
	}

	if ( '' !== $t['yandex_metrica'] ) {
		$ym_img = 'https://mc.yandex.ru/watch/' . rawurlencode( $t['yandex_metrica'] );
		echo '<noscript><div><img src="' . esc_url( $ym_img ) . '" style="position:absolute;left:-9999px;" alt="" /></div></noscript>' . "\n";
	}
}
add_action( 'wp_body_open', 'jalaversity_render_tracking_body_open', 2 );

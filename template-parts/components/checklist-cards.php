<?php
/**
 * Checklist + Cards — Generic
 *
 * Section 2-kolom: kiri label+heading+checklist polos (icon+teks satu
 * baris), kanan sub-heading+grid kartu kecil (icon+judul+desc). Dipakai
 * untuk pola "Kompetensi Lulusan + Prospek Karier", tapi generik. Komposisi
 * dari komponen yang sudah ada (icon-list, card-grid) — tidak menulis ulang
 * styling kartu/list.
 *
 * Pure render component — semua data via $args.
 *
 * @param string $args['label']         Section label kolom kiri (opsional).
 * @param string $args['heading']       Heading kolom kiri (wajib).
 * @param array  $args['checklist']     List ['icon','text'] untuk kolom kiri (wajib).
 * @param string $args['cards_heading'] Sub-heading kolom kanan (mis. "Prospek Karier").
 * @param array  $args['cards']         List ['icon','title','desc'] untuk kolom kanan (wajib).
 *
 * @package Jalaversity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label         = $args['label'] ?? '';
$heading       = $args['heading'] ?? '';
$checklist     = $args['checklist'] ?? [];
$cards_heading = $args['cards_heading'] ?? '';
$cards         = $args['cards'] ?? [];

if ( ! $heading || ! $checklist ) {
	return;
}

$checklist_items = array_map(
	static fn( array $row ): array => [
		'icon'  => $row['icon'] ?? 'check-circle',
		'title' => $row['text'] ?? '',
	],
	$checklist
);
?>
<section class="checklist-cards-section section-py" aria-labelledby="checklist-cards-heading">
	<div class="container checklist-cards">

		<div class="checklist-cards__col">
			<?php if ( $label ) : ?>
				<?php jalaversity_section_label( $label ); ?>
			<?php endif; ?>
			<h2 id="checklist-cards-heading" class="checklist-cards__heading text-section"><?php echo esc_html( $heading ); ?></h2>

			<?php get_template_part( 'template-parts/components/icon-list', null, [
				'items'  => $checklist_items,
				'layout' => 'rows',
			] ); ?>
		</div>

		<?php if ( $cards ) : ?>
		<div class="checklist-cards__col">
			<?php if ( $cards_heading ) : ?>
			<h3 class="checklist-cards__sub-heading text-section"><?php echo esc_html( $cards_heading ); ?></h3>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/components/card-grid', null, [
				'items'          => $cards,
				'min_card_width' => '190px',
			] ); ?>
		</div>
		<?php endif; ?>

	</div>
</section>

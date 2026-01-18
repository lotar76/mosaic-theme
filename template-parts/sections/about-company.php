<?php
/**
 * Template Part: About Company Section
 * Переиспользуемый блок "О компании"
 */

declare(strict_types=1);

$aboutData = mosaic_get_about_home();
$company = $aboutData['company'];

$imageUrl = '';
if ($company['image_id'] > 0) {
	$imageUrl = (string) wp_get_attachment_image_url($company['image_id'], 'large');
}
if ($imageUrl === '' && $company['image_url'] !== '') {
	$imageUrl = $company['image_url'];
}
?>

<!-- About Company Section -->
<section class="bg-gray">
	<div class="max-w-[1920px] mx-auto">
		<div class="grid grid-cols-1 lg:grid-cols-2">
			<!-- Left: Studio Photo -->
			<div class="h-[400px] lg:h-[500px] overflow-hidden">
				<img
					src="<?= esc_url($imageUrl); ?>"
					alt="Студия Si Mosaic"
					class="w-full h-full object-cover"
				>
			</div>

			<!-- Right: About Text -->
			<div class="flex flex-col justify-center px-6 md:px-12 lg:px-16 py-12 lg:py-0">
				<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0"><?= esc_html($company['title']); ?></h2>
				<div class="w-[70px] h-[6px] bg-primary mt-6 mb-8"></div>

				<p class="text-white/80 text-base md:text-lg mb-4 leading-relaxed">
					<?= esc_html($company['text_1']); ?>
				</p>

				<p class="text-white/80 text-base md:text-lg mb-8 leading-relaxed">
					<?= esc_html($company['text_2']); ?>
				</p>

				<a
					href="<?= esc_url($company['button_url']); ?>"
					class="inline-flex items-center justify-center gap-[10px] bg-primary hover:bg-opacity-90 transition-colors text-white w-full min-[1280px]:w-fit h-[56px] py-4 px-8 text-base md:text-lg"
					tabindex="0"
					aria-label="<?= esc_attr($company['button_text']); ?>"
				>
					<?= esc_html($company['button_text']); ?>
				</a>
			</div>
		</div>
	</div>
</section>


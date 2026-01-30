<?php
/**
 * Template Part: Founders Section
 * Переиспользуемый блок "Основатели"
 */

declare(strict_types=1);

$aboutData = mosaic_get_about_home();
$founders = $aboutData['founders'];

$imageUrl = '';
if ($founders['image_id'] > 0) {
	$imageUrl = (string) wp_get_attachment_image_url($founders['image_id'], 'large');
}
if ($imageUrl === '' && $founders['image_url'] !== '') {
	$imageUrl = $founders['image_url'];
}
?>

<!-- Founders Section -->
<section class="bg-black">
	<div class="max-w-[1920px] mx-auto">
		<div class="grid grid-cols-1 lg:grid-cols-2">
			<!-- Left: Founders Info -->
			<div class="flex flex-col justify-center bg-gray px-6 md:px-12 lg:px-16 py-12 lg:py-20 order-2 lg:order-1">
				<h2 class="text-white text-2xl md:text-3xl lg:text-4xl font-normal mb-2">
					<?= esc_html($founders['name']); ?>
				</h2>
				<p class="text-white/60 text-lg md:text-xl mb-0"><?= esc_html($founders['title']); ?></p>
				<div class="w-[70px] h-[6px] bg-primary mt-6 mb-8"></div>

				<p class="text-white/80 text-base md:text-lg mb-4 leading-relaxed">
					<?= esc_html($founders['description_1']); ?>
				</p>

				<p class="text-white/80 text-base md:text-lg leading-relaxed">
					<?= esc_html($founders['description_2']); ?>
				</p>
			</div>

			<!-- Right: Founders Photo -->
			<div class="h-[400px] lg:h-[500px] overflow-hidden order-1 lg:order-2">
				<img
					src="<?= esc_url($imageUrl); ?>"
					alt="<?= esc_attr($founders['name']); ?> - <?= esc_attr($founders['title']); ?>"
					class="w-full h-full object-cover"
				>
			</div>
		</div>
	</div>
</section>


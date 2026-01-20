<?php
/**
 * Template Part: Benefits Section
 * Секция "С нами работать комфортно" с адаптивной сеткой
 */

declare(strict_types=1);

$benefitsData = function_exists('mosaic_get_benefits_data') ? mosaic_get_benefits_data() : [
	'title' => 'С нами комфортно работать',
	'items' => [],
];

$title = $benefitsData['title'];
$items = $benefitsData['items'];

// Если элементов меньше 4, заполняем пустыми
while (count($items) < 4) {
	$items[] = ['title' => '', 'text' => '', 'image_id' => 0];
}
?>

<!-- Benefits Section -->
<section class="bg-black" data-benefits>
	<div class="mx-auto w-full max-w-[1722px]">
		<!-- Desktop (>=1920) -->
		<div class="hidden min-[1920px]:block">
			<div class="h-[550px] flex flex-col gap-[30px]">
				<!-- Row 1 -->
				<div class="h-[260px] flex gap-[30px]">
					<!-- 1: Title block -->
					<div class="w-[389px] h-[260px] flex flex-col justify-start">
						<h2 class="text-white font-normal text-[56px] leading-[1] tracking-[-0.01em] mb-0">
							<?= nl2br(esc_html($title)); ?>
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<!-- 2: Item 0 card -->
					<div class="w-[427px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							<?= esc_html($items[0]['title']); ?>
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							<?= nl2br(esc_html($items[0]['text'])); ?>
						</p>
					</div>

					<!-- 3: Item 0 image -->
					<div class="w-[262px] h-[260px] bg-gray overflow-hidden">
						<?php if ($items[0]['image_id'] > 0) : ?>
							<?php $img0 = wp_get_attachment_image_url($items[0]['image_id'], 'large'); ?>
							<img
								src="<?= esc_url($img0); ?>"
								alt="<?= esc_attr($items[0]['title']); ?>"
								class="w-full h-full object-cover"
								loading="lazy"
								decoding="async"
							>
						<?php endif; ?>
					</div>

					<!-- 4: Item 1 card -->
					<div class="w-[554px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							<?= esc_html($items[1]['title']); ?>
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							<?= nl2br(esc_html($items[1]['text'])); ?>
						</p>
					</div>
				</div>

				<!-- Row 2 -->
				<div class="h-[260px] flex gap-[30px]">
					<!-- Item 2 card -->
					<div class="w-[389px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							<?= esc_html($items[2]['title']); ?>
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							<?= nl2br(esc_html($items[2]['text'])); ?>
						</p>
					</div>

					<!-- Item 2 image -->
					<div class="w-[262px] h-[260px] bg-gray overflow-hidden">
						<?php if ($items[2]['image_id'] > 0) : ?>
							<?php $img2 = wp_get_attachment_image_url($items[2]['image_id'], 'large'); ?>
							<img
								src="<?= esc_url($img2); ?>"
								alt="<?= esc_attr($items[2]['title']); ?>"
								class="w-full h-full object-cover"
								loading="lazy"
								decoding="async"
							>
						<?php endif; ?>
					</div>

					<!-- Item 3 card -->
					<div class="w-[554px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							<?= esc_html($items[3]['title']); ?>
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							<?= nl2br(esc_html($items[3]['text'])); ?>
						</p>
					</div>

					<!-- Item 3 image -->
					<div class="w-[427px] h-[260px] bg-gray overflow-hidden">
						<?php if ($items[3]['image_id'] > 0) : ?>
							<?php $img3 = wp_get_attachment_image_url($items[3]['image_id'], 'large'); ?>
							<img
								src="<?= esc_url($img3); ?>"
								alt="<?= esc_attr($items[3]['title']); ?>"
								class="w-full h-full object-cover"
								loading="lazy"
								decoding="async"
							>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Tablet (1280-1919) -->
		<div class="hidden min-[1280px]:block min-[1920px]:hidden">
			<div class="mx-auto w-[1219px] max-w-full">
				<div class="flex flex-col gap-[30px]">
					<!-- Row 1 -->
					<div class="h-[250px] flex gap-[30px]">
						<div class="w-[384px] h-[250px] flex flex-col justify-start">
							<h2 class="text-white font-normal text-[56px] leading-[1] tracking-[-0.01em] mb-0">
								<?= nl2br(esc_html($title)); ?>
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<div class="w-[490px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								<?= esc_html($items[0]['title']); ?>
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								<?= nl2br(esc_html($items[0]['text'])); ?>
							</p>
						</div>

						<div class="w-[282px] h-[250px] bg-gray overflow-hidden">
							<?php if ($items[0]['image_id'] > 0) : ?>
								<?php $img0 = wp_get_attachment_image_url($items[0]['image_id'], 'large'); ?>
								<img
									src="<?= esc_url($img0); ?>"
									alt="<?= esc_attr($items[0]['title']); ?>"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
						</div>
					</div>

					<!-- Row 2 -->
					<div class="h-[250px] flex gap-[30px]">
						<div class="w-[488px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								<?= esc_html($items[3]['title']); ?>
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								<?= nl2br(esc_html($items[3]['text'])); ?>
							</p>
						</div>

						<div class="w-[282px] h-[250px] bg-gray overflow-hidden">
							<?php if ($items[2]['image_id'] > 0) : ?>
								<?php $img2 = wp_get_attachment_image_url($items[2]['image_id'], 'large'); ?>
								<img
									src="<?= esc_url($img2); ?>"
									alt="<?= esc_attr($items[2]['title']); ?>"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
						</div>

						<div class="w-[386px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								<?= esc_html($items[2]['title']); ?>
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								<?= nl2br(esc_html($items[2]['text'])); ?>
							</p>
						</div>
					</div>

					<!-- Row 3 -->
					<div class="h-[250px] flex gap-[30px]">
						<div class="w-[698px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								<?= esc_html($items[1]['title']); ?>
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								<?= nl2br(esc_html($items[1]['text'])); ?>
							</p>
						</div>

						<div class="w-[491px] h-[250px] bg-gray overflow-hidden">
							<?php if ($items[3]['image_id'] > 0) : ?>
								<?php $img3 = wp_get_attachment_image_url($items[3]['image_id'], 'large'); ?>
								<img
									src="<?= esc_url($img3); ?>"
									alt="<?= esc_attr($items[3]['title']); ?>"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Mobile (<=1279) -->
		<div class="min-[1280px]:hidden">
			<div class="space-y-6">
				<div class="p-6">
					<h2 class="text-white text-3xl md:text-4xl font-normal leading-[1.2] mb-0">
						<?= nl2br(esc_html($title)); ?>
					</h2>
					<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
				</div>

				<div data-benefits-carousel>
					<div data-benefits-carousel-track>
						<?php foreach ($items as $item) : ?>
							<?php if ($item['title'] !== '' || $item['text'] !== '') : ?>
								<div data-benefits-slide>
									<div class="bg-gray p-6" data-benefits-slide-item>
										<h3 class="text-white text-lg font-normal mb-0"><?= esc_html($item['title']); ?></h3>
										<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
										<p class="text-white text-sm leading-relaxed">
											<?= nl2br(esc_html($item['text'])); ?>
										</p>
									</div>
									<?php if ($item['image_id'] > 0) : ?>
										<?php $imgUrl = wp_get_attachment_image_url($item['image_id'], 'large'); ?>
										<div class="bg-gray overflow-hidden" data-benefits-slide-item>
											<img
												src="<?= esc_url($imgUrl); ?>"
												alt="<?= esc_attr($item['title']); ?>"
												class="w-full h-full object-cover"
												loading="lazy"
												decoding="async"
											>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


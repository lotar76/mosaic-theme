<?php
/**
 * Template Part: Work Process Section
 * Секция "Процесс работы" с адаптивным слайдером
 */

declare(strict_types=1);

$processOpt = function_exists('mosaic_get_work_process') ? mosaic_get_work_process() : ['blocks' => []];
$processBlocks = is_array($processOpt) ? ($processOpt['blocks'] ?? []) : [];
if (!is_array($processBlocks)) {
	$processBlocks = [];
}
?>

<!-- Work Process Section -->
<section class="bg-gray py-[80px] min-[1280px]:py-[100px]">
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
		<!-- Section Header -->
		<div class="flex items-center justify-between mb-8 md:mb-12">
			<div>
				<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Процесс работы</h2>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>

			<!-- Navigation Arrows -->
			<div class="flex gap-[37px] max-[1279px]:hidden">
				<button
					type="button"
					class="process-prev p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Предыдущий слайд"
					tabindex="0"
				>
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
					</svg>
				</button>
				<button
					type="button"
					class="process-next p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Следующий слайд"
					tabindex="0"
				>
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
					</svg>
				</button>
			</div>
		</div>

		<!-- Process Slider -->
		<div class="process-slider overflow-hidden">
			<div class="process-track flex gap-4 md:gap-6 transition-transform duration-500 ease-out">
				<?php foreach ($processBlocks as $idx => $row) : ?>
					<?php
					if (!is_array($row)) {
						continue;
					}
					$imageId = absint($row['image_id'] ?? 0);
					$imageUrl = trim((string) ($row['image_url'] ?? ''));
					$title = (string) ($row['title'] ?? '');
					$description = (string) ($row['description'] ?? '');

					$stepNumber = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);

					$src = '';
					if ($imageId > 0) {
						$src = (string) wp_get_attachment_image_url($imageId, 'large');
					}
					if ($src === '' && $imageUrl !== '') {
						$src = $imageUrl;
					}

					$altBase = 'Процесс работы - шаг ' . (string) ($idx + 1);
					if ($title !== '') {
						$altBase .= ': ' . $title;
					}
					?>
					<div class="process-slide bg-black flex-shrink-0 w-[280px] md:w-[320px]">
						<div class="p-5 md:p-[30px] pb-0 md:pb-0">
							<div class="text-primary text-4xl md:text-5xl font-normal mb-4"><?= esc_html($stepNumber); ?></div>
							<?php if ($title !== '') : ?>
								<h3 class="text-white text-lg md:text-xl font-normal mb-3 min-h-[56px] line-clamp-2"><?= esc_html($title); ?></h3>
							<?php endif; ?>
							<?php if ($description !== '') : ?>
								<p class="text-white/70 text-sm leading-relaxed min-h-[68px] line-clamp-3">
									<?= esc_html($description); ?>
								</p>
							<?php endif; ?>
						</div>
						<?php if ($src !== '') : ?>
							<div class="p-5 md:p-[30px]">
								<div class="aspect-[4/3] overflow-hidden">
									<img
										src="<?= esc_url($src); ?>"
										alt="<?= esc_attr($altBase); ?>"
										class="w-full h-full object-cover"
										loading="lazy"
										decoding="async"
									>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>


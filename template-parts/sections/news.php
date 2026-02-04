<?php
/**
 * Template Part: News Section
 * Переиспользуемый блок "Новости и блог" на главной странице
 */

declare(strict_types=1);

$newsItems = function_exists('mosaic_get_news_posts') ? mosaic_get_news_posts(10) : [];
$newsArchiveUrl = home_url('/news/');
?>

<!-- News and Blog Section -->
<section class="bg-black">
	<div class="max-w-[1920px] mx-auto pl-4 md:pl-8 min-[1280px]:pl-10 min-[1920px]:pl-[100px] pr-0">
		<!-- Section Header -->
		<div class="mb-8 md:mb-12">
			<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Новости и блог</h2>
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>

		<!-- News Slider -->
		<div class="news-slider relative overflow-hidden">
			<div class="news-track flex gap-4 md:gap-6 transition-transform duration-500 ease-out">
				<?php if (count($newsItems) > 0) : ?>
					<?php foreach ($newsItems as $item) : ?>
						<a href="<?= esc_url($item['url']); ?>" class="news-slide bg-black group flex-shrink-0 w-[340px] min-[1280px]:w-[400px] min-[1920px]:w-[420px]" tabindex="0" aria-label="<?= esc_attr($item['title']); ?>">
							<div class="w-[340px] h-[340px] min-[1280px]:w-[400px] min-[1280px]:h-[400px] min-[1920px]:w-[420px] min-[1920px]:h-[420px] overflow-hidden">
								<?php if ($item['image_url'] !== '') : ?>
									<img
										src="<?= esc_url($item['image_url']); ?>"
										alt="<?= esc_attr($item['title']); ?>"
										class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
										loading="lazy"
										decoding="async"
									>
								<?php else : ?>
									<div class="w-full h-full bg-gray/40 flex items-center justify-center">
										<span class="text-white/40 text-sm">Нет изображения</span>
									</div>
								<?php endif; ?>
							</div>
							<p class="pt-6 text-white text-[18px] min-[1280px]:text-[20px] leading-[145%] text-left">
								<?= esc_html($item['title']); ?>
							</p>
						</a>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="text-white/60 py-8">
						Новости пока не добавлены
					</div>
				<?php endif; ?>
			</div>

			<!-- Navigation Arrows -->
			<button
				type="button"
				class="news-prev absolute left-4 min-[1280px]:left-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-primary hover:brightness-110 hover:scale-110 transition-all text-white z-10 max-[1279px]:hidden"
				aria-label="Предыдущий слайд"
				tabindex="0"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
				</svg>
			</button>
			<button
				type="button"
				class="news-next absolute right-4 min-[1280px]:right-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-primary hover:brightness-110 hover:scale-110 transition-all text-white z-10 max-[1279px]:hidden"
				aria-label="Следующий слайд"
				tabindex="0"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
				</svg>
			</button>
		</div>
	</div>
</section>

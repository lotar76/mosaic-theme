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
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
		<!-- Section Header -->
		<div class="flex items-center justify-between mb-8 md:mb-12">
			<div>
				<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Новости и блог</h2>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>

			<!-- Navigation Arrows -->
			<div class="flex gap-[37px] max-[1279px]:hidden">
				<button
					type="button"
					class="news-prev p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Предыдущий слайд"
					tabindex="0"
				>
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
					</svg>
				</button>
				<button
					type="button"
					class="news-next p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Следующий слайд"
					tabindex="0"
				>
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
					</svg>
				</button>
			</div>
		</div>

		<!-- News Slider -->
		<div class="news-slider overflow-hidden">
			<div class="news-track flex gap-4 md:gap-6 transition-transform duration-500 ease-out">
				<?php if (count($newsItems) > 0) : ?>
					<?php foreach ($newsItems as $item) : ?>
						<a href="<?= esc_url($item['url']); ?>" class="news-slide bg-black group flex-shrink-0 w-[280px] md:w-[320px]" tabindex="0" aria-label="<?= esc_attr($item['title']); ?>">
							<div class="aspect-[4/3] overflow-hidden">
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
							<p class="pt-6 text-white text-[20px] leading-[145%] text-left">
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
		</div>
	</div>
</section>

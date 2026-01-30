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
		<div class="flex items-center justify-between mb-8 md:mb-12">
			<div>
				<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Новости и блог</h2>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>

			<!-- Navigation Arrows -->
			<div class="flex gap-6 max-[1279px]:hidden mr-[99px]">
				<button
					type="button"
					class="news-prev hover:opacity-70 transition-opacity focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Предыдущий слайд"
					tabindex="0"
				>
					<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M26.6667 31.6665L15 19.9998L26.6667 8.33317" stroke="#847575" stroke-width="2" stroke-linecap="square"/>
					</svg>
				</button>
				<button
					type="button"
					class="news-next hover:opacity-70 transition-opacity focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Следующий слайд"
					tabindex="0"
				>
					<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.3333 8.3335L25 20.0002L13.3333 31.6668" stroke="#847575" stroke-width="2" stroke-linecap="square"/>
					</svg>
				</button>
			</div>
		</div>

		<!-- News Slider -->
		<div class="news-slider overflow-hidden">
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
		</div>
	</div>
</section>

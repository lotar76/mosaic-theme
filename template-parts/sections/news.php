<?php
/**
 * Template Part: News Section
 * Переиспользуемый блок "Новости и блог"
 */

declare(strict_types=1);

$newsImgBaseUrl = get_template_directory_uri() . '/img/news';

$newsOpt = function_exists('mosaic_get_news') ? mosaic_get_news() : ['items' => []];
$newsItems = is_array($newsOpt) ? ($newsOpt['items'] ?? []) : [];
if (!is_array($newsItems)) {
	$newsItems = [];
}

$newsPage = function_exists('get_page_by_path') ? get_page_by_path('news') : null;
$newsArchiveUrl = ($newsPage instanceof WP_Post) ? (string) get_permalink($newsPage) : '#';

// Fallback: если новостей нет — показываем старые 5 карточек из темы.
$hasDynamicNews = count($newsItems) > 0;
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
				<?php if ($hasDynamicNews) : ?>
					<?php foreach ($newsItems as $item) : ?>
						<?php
						if (!is_array($item)) {
							continue;
						}
						$title = (string) ($item['title'] ?? '');
						$content = (string) ($item['content'] ?? '');
						$galleryIds = is_array($item['gallery_ids'] ?? null) ? $item['gallery_ids'] : [];
						$galleryUrls = is_array($item['gallery_urls'] ?? null) ? $item['gallery_urls'] : [];

						$thumbUrl = '';
						if (count($galleryIds) > 0) {
							$thumbUrl = (string) wp_get_attachment_image_url((int) $galleryIds[0], 'large');
						} elseif (count($galleryUrls) > 0) {
							$thumbUrl = (string) $galleryUrls[0];
						}

						$textPlain = trim((string) wp_strip_all_tags($content));
						$excerpt = $textPlain !== '' ? wp_trim_words($textPlain, 18, '…') : '';
						$caption = $excerpt !== '' ? $excerpt : ($title !== '' ? $title : '');
						if ($caption === '') {
							$caption = 'Новость';
						}

						$aria = $title !== '' ? $title : $caption;
						?>
						<a href="<?= esc_url($newsArchiveUrl); ?>" class="news-slide bg-black group flex-shrink-0 w-[280px] md:w-[320px]" tabindex="0" aria-label="<?= esc_attr($aria); ?>">
							<div class="aspect-[4/3] overflow-hidden">
								<?php if ($thumbUrl !== '') : ?>
									<img
										src="<?= esc_url($thumbUrl); ?>"
										alt="<?= esc_attr($aria); ?>"
										class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
										loading="lazy"
										decoding="async"
									>
								<?php else : ?>
									<img
										src="<?= esc_url($newsImgBaseUrl . '/1.png'); ?>"
										alt="<?= esc_attr($aria); ?>"
										class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
										loading="lazy"
										decoding="async"
									>
								<?php endif; ?>
							</div>
							<p class="p-6 text-white/70 text-sm leading-relaxed">
								<?= esc_html($caption); ?>
							</p>
						</a>
					<?php endforeach; ?>
				<?php else : ?>
					<?php for ($i = 1; $i <= 5; $i++) : ?>
						<a href="<?= esc_url($newsArchiveUrl); ?>" class="news-slide bg-black group flex-shrink-0 w-[280px] md:w-[320px]" tabindex="0" aria-label="<?= esc_attr('Новость ' . $i); ?>">
							<div class="aspect-[4/3] overflow-hidden">
								<img
									src="<?= esc_url($newsImgBaseUrl . '/' . $i . '.png'); ?>"
									alt="<?= esc_attr('Новость ' . $i); ?>"
									class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
									loading="lazy"
									decoding="async"
								>
							</div>
							<p class="p-6 text-white/70 text-sm leading-relaxed">
								Банальные, но неопровержимые выводы, а также независимые государства
							</p>
						</a>
					<?php endfor; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>


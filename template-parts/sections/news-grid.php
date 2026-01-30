<?php
/**
 * Template Part: News Grid Section
 * Блок новостей для страницы /news/ с сеткой карточек
 */

declare(strict_types=1);

$showTitle = $args['show_title'] ?? true;
$titleTag = $args['title_tag'] ?? 'h2';
$title = $args['title'] ?? 'Новости';
$limit = $args['limit'] ?? -1;
$showLoadMore = $args['show_load_more'] ?? true;

$news = function_exists('mosaic_get_news_posts') ? mosaic_get_news_posts($limit) : [];
?>

<!-- News Grid Section -->
<section class="bg-black" data-news-grid-section>
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px] py-[60px] min-[1280px]:py-[80px]">
		<?php if ($showTitle) : ?>
			<!-- Section Header -->
			<div class="mb-8 md:mb-12">
				<<?= $titleTag; ?> class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">
					<?= esc_html($title); ?>
				</<?= $titleTag; ?>>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>
		<?php endif; ?>

		<!-- News Grid -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-news-grid>
			<?php if (count($news) > 0) : ?>
				<?php foreach ($news as $item) : ?>
					<a
						href="<?= esc_url($item['url']); ?>"
						class="group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
						tabindex="0"
						aria-label="<?= esc_attr($item['title']); ?>"
						data-news-card
					>
						<div class="aspect-square bg-gray/20 overflow-hidden">
							<?php if ($item['image_url'] !== '') : ?>
								<img
									src="<?= esc_url($item['image_url']); ?>"
									alt="<?= esc_attr($item['title']); ?>"
									class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
									loading="lazy"
									decoding="async"
								>
							<?php else : ?>
								<div class="w-full h-full bg-gray/40 flex items-center justify-center">
									<span class="text-white/40 text-sm">Нет изображения</span>
								</div>
							<?php endif; ?>
						</div>
						<div class="mt-3 md:mt-4">
							<div class="text-white text-base font-normal group-hover:text-primary transition-colors">
								<?= esc_html($item['title']); ?>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="col-span-full text-center py-16">
					<p class="text-white/60 text-lg">Новости пока не добавлены</p>
				</div>
			<?php endif; ?>
		</div>

		<?php if ($showLoadMore && count($news) >= 12) : ?>
			<!-- Load More Button -->
			<div class="flex justify-center mt-12">
				<button
					type="button"
					class="inline-flex items-center justify-center border border-primary text-primary hover:bg-primary hover:text-white transition-colors h-[56px] px-10 text-base"
					data-news-load-more
				>
					Показать еще
				</button>
			</div>
		<?php endif; ?>
	</div>
</section>

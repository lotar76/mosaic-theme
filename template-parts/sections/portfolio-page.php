<?php
/**
 * Template Part: Portfolio Page Section
 * Блок портфолио для страницы /portfolio/ с фильтрами по категориям
 */

declare(strict_types=1);

$showTitle = $args['show_title'] ?? true;
$titleTag = $args['title_tag'] ?? 'h2';
$title = $args['title'] ?? 'Портфолио';

$categories = function_exists('mosaic_get_portfolio_categories') ? mosaic_get_portfolio_categories() : [];
$projects = function_exists('mosaic_get_portfolio_projects') ? mosaic_get_portfolio_projects() : [];
?>

<!-- Portfolio Page Section -->
<section class="bg-black" data-portfolio-page>
	<div class="mx-auto w-full" data-portfolio-inner>
		<?php if ($showTitle) : ?>
			<!-- Section Header -->
			<div class="mb-8 md:mb-12">
				<<?= $titleTag; ?> class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">
					<?= esc_html($title); ?>
				</<?= $titleTag; ?>>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>
		<?php endif; ?>

		<?php if (count($categories) > 0) : ?>
			<!-- Filter Tabs -->
			<div class="mb-8 md:mb-10" data-portfolio-filters>
				<div class="flex flex-wrap gap-2 md:gap-3">
					<button
						type="button"
						class="portfolio-filter-btn is-active"
						data-filter="all"
						aria-pressed="true"
					>
						Все
					</button>
					<?php foreach ($categories as $cat) : ?>
						<button
							type="button"
							class="portfolio-filter-btn"
							data-filter="<?= esc_attr($cat['slug']); ?>"
							aria-pressed="false"
						>
							<?= esc_html($cat['name']); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Portfolio Grid -->
		<div data-portfolio-grid>
			<?php if (count($projects) > 0) : ?>
				<?php foreach ($projects as $project) : ?>
					<?php
					$hasPdf = !empty($project['pdf_file_url']);
					$linkUrl = $hasPdf ? $project['pdf_file_url'] : '#';
					$cursorClass = $hasPdf ? 'cursor-pointer' : 'cursor-default';
					?>
					<a
						href="<?= esc_url($linkUrl); ?>"
						<?php if ($hasPdf) : ?>
							target="_blank"
							rel="noopener noreferrer"
						<?php else : ?>
							onclick="return false;"
						<?php endif; ?>
						class="group <?= esc_attr($cursorClass); ?> focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
						tabindex="<?= $hasPdf ? '0' : '-1'; ?>"
						aria-label="<?= esc_attr($project['title']); ?>"
						data-portfolio-card
						data-category="<?= esc_attr($project['category_slug']); ?>"
					>
						<div class="bg-gray/20" data-portfolio-media>
							<?php if ($project['image_url'] !== '') : ?>
								<img
									src="<?= esc_url($project['image_url']); ?>"
									alt="<?= esc_attr($project['title']); ?>"
									class="w-full h-full object-cover transition-transform duration-500 <?= $hasPdf ? 'group-hover:scale-[1.03]' : ''; ?>"
									loading="lazy"
									decoding="async"
								>
							<?php else : ?>
								<div class="w-full h-full bg-gray/40 flex items-center justify-center">
									<span class="text-white/40 text-sm">Нет изображения</span>
								</div>
							<?php endif; ?>
						</div>
						<div class="mt-3 md:mt-4" data-portfolio-meta>
							<div data-portfolio-caption>
								<?= esc_html($project['title']); ?>
							</div>
							<div data-portfolio-category>
								<?= esc_html($project['category'] !== '' ? $project['category'] : '—'); ?>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="col-span-full text-center py-16">
					<p class="text-white/60 text-lg">Проекты пока не добавлены</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<script>
(function() {
	const section = document.querySelector('[data-portfolio-page]');
	if (!section) return;

	const filterBtns = section.querySelectorAll('.portfolio-filter-btn');
	const cards = section.querySelectorAll('[data-portfolio-card]');

	filterBtns.forEach(btn => {
		btn.addEventListener('click', function() {
			const filter = this.dataset.filter;

			// Update active state
			filterBtns.forEach(b => {
				b.classList.remove('is-active');
				b.setAttribute('aria-pressed', 'false');
			});
			this.classList.add('is-active');
			this.setAttribute('aria-pressed', 'true');

			// Filter cards
			cards.forEach(card => {
				if (filter === 'all' || card.dataset.category === filter) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});
		});
	});
})();
</script>

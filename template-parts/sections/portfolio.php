<?php
/**
 * Template Part: Portfolio Section
 * Переиспользуемый блок "Портфолио"
 */

declare(strict_types=1);
?>

<!-- Portfolio Section -->
<section class="bg-black" data-portfolio>
	<div class="max-w-[1920px] mx-auto pl-4 md:pl-8 min-[1280px]:pl-10 min-[1920px]:pl-[100px] pr-0">
		<!-- Section Header -->
		<div class="flex items-center justify-between mb-8 md:mb-12">
			<div>
				<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Портфолио</h2>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>

			<!-- Navigation Arrows -->
			<div class="flex gap-[37px] max-[1279px]:hidden mr-[99px]">
				<button
					type="button"
					class="portfolio-prev p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Предыдущий слайд"
					tabindex="0"
				>
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
					</svg>
				</button>
				<button
					type="button"
					class="portfolio-next p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
					aria-label="Следующий слайд"
					tabindex="0"
				>
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
					</svg>
				</button>
			</div>
		</div>

		<!-- Portfolio Slider -->
		<div class="portfolio-slider overflow-hidden">
			<?php
			$portfolioItems = [
				['id' => 1, 'title' => 'Название проекта', 'subtitle' => 'Коммерческое'],
				['id' => 2, 'title' => 'Название проекта', 'subtitle' => 'Интерьерное'],
				['id' => 3, 'title' => 'Название проекта', 'subtitle' => 'Коммерческое'],
				['id' => 4, 'title' => 'Название проекта', 'subtitle' => 'Интерьерное'],
				['id' => 5, 'title' => 'Название проекта', 'subtitle' => 'Коммерческое'],
			];
			?>
			<div class="portfolio-track flex transition-transform duration-700 ease-in-out" data-portfolio-track>
				<?php foreach ($portfolioItems as $item) : ?>
					<?php
					$id = (int) ($item['id'] ?? 0);
					$title = (string) ($item['title'] ?? '');
					$subtitle = (string) ($item['subtitle'] ?? '');

					if ($id <= 0) {
						continue;
					}

					$imageUrl = esc_url(get_template_directory_uri() . "/img/portfolio/{$id}.jpg");
					$itemUrl = esc_url(home_url("/portfolio/project-{$id}/"));
					?>
					<a
						href="<?= $itemUrl; ?>"
						class="portfolio-slide group flex-shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
						tabindex="0"
						aria-label="<?= esc_attr("{$title} {$subtitle}"); ?>"
						data-portfolio-slide
					>
						<div class="bg-gray overflow-hidden" data-portfolio-media>
							<img
								src="<?= $imageUrl; ?>"
								alt="<?= esc_attr($title); ?>"
								class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
								loading="lazy"
								decoding="async"
							>
						</div>

						<div data-portfolio-caption>
							<div data-portfolio-title><?= esc_html($title); ?></div>
							<div data-portfolio-subtitle><?= esc_html($subtitle); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- All Projects Button -->
		<div class="flex justify-center mt-10 md:mt-12">
			<a
				href="/portfolio"
				class="inline-flex items-center justify-center border border-primary text-primary hover:bg-primary hover:text-white transition-colors h-[56px] px-10 text-base w-full max-w-[300px] min-[1280px]:w-fit min-[1280px]:max-w-none"
				tabindex="0"
				aria-label="Смотреть все проекты"
			>
				Все проекты
			</a>
		</div>
	</div>
</section>


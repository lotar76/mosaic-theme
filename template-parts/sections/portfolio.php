<?php
/**
 * Template Part: Portfolio Section
 * Переиспользуемый блок "Портфолио" (слайдер на главной)
 */

declare(strict_types=1);

// Получаем реальные проекты из CPT, либо используем fallback
$portfolioItems = [];

if (function_exists('mosaic_get_portfolio_projects')) {
	$projects = mosaic_get_portfolio_projects('', 10);
	foreach ($projects as $project) {
		$portfolioItems[] = [
			'id' => $project['id'],
			'title' => $project['title'],
			'subtitle' => $project['category'],
			'image_url' => $project['image_url'],
			'url' => $project['url'],
			'pdf_file_url' => $project['pdf_file_url'] ?? '',
		];
	}
}

// Fallback если проектов нет
if (count($portfolioItems) === 0) {
	for ($i = 1; $i <= 5; $i++) {
		$portfolioItems[] = [
			'id' => $i,
			'title' => 'Название проекта',
			'subtitle' => $i % 2 === 0 ? 'Интерьеры' : 'Коммерческое',
			'image_url' => get_template_directory_uri() . "/img/portfolio/{$i}.jpg",
			'url' => home_url("/portfolio/project-{$i}/"),
			'pdf_file_url' => '',
		];
	}
}
?>

<!-- Portfolio Section -->
<section class="bg-black" data-portfolio>
	<div class="max-w-[1920px] mx-auto pl-4 md:pl-8 min-[1280px]:pl-10 min-[1920px]:pl-[100px] pr-0">
		<!-- Section Header -->
		<div class="mb-8 md:mb-12">
			<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Портфолио</h2>
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>

		<!-- Portfolio Slider -->
		<div class="portfolio-slider relative overflow-hidden">
			<div class="portfolio-track flex transition-transform duration-700 ease-in-out" data-portfolio-track>
				<?php foreach ($portfolioItems as $item) : ?>
					<?php
					$id = (int) ($item['id'] ?? 0);
					$title = (string) ($item['title'] ?? '');
					$subtitle = (string) ($item['subtitle'] ?? '');
					$imageUrl = (string) ($item['image_url'] ?? '');
					$pdfFileUrl = (string) ($item['pdf_file_url'] ?? '');

					if ($id <= 0) {
						continue;
					}

					// Fallback для изображения
					if ($imageUrl === '') {
						$imageUrl = get_template_directory_uri() . "/img/portfolio/{$id}.jpg";
					}

					// Логика PDF: есть PDF - открываем в новой вкладке, нет - не кликабельный
					$hasPdf = $pdfFileUrl !== '';
					$linkUrl = $hasPdf ? $pdfFileUrl : '#';
					$cursorClass = $hasPdf ? '' : 'cursor-default';
					?>
					<a
						href="<?= esc_url($linkUrl); ?>"
						<?php if ($hasPdf) : ?>
							target="_blank"
							rel="noopener noreferrer"
						<?php else : ?>
							onclick="return false;"
						<?php endif; ?>
						class="portfolio-slide group flex-shrink-0 w-[300px] min-[1280px]:w-[408px] <?= esc_attr($cursorClass); ?> focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
						tabindex="<?= $hasPdf ? '0' : '-1'; ?>"
						aria-label="<?= esc_attr("{$title} {$subtitle}"); ?>"
						data-portfolio-slide
					>
						<div
							class="w-[300px] h-[340px] min-[1280px]:w-[408px] min-[1280px]:h-[520px] bg-gray bg-cover bg-center transition-transform duration-500 <?= $hasPdf ? 'group-hover:scale-[1.03]' : ''; ?>"
							style="background-image: url('<?= esc_url($imageUrl); ?>');"
							role="img"
							aria-label="<?= esc_attr($title); ?>"
							data-portfolio-media
						></div>

						<div data-portfolio-caption>
							<div data-portfolio-title><?= esc_html($title); ?></div>
							<div data-portfolio-subtitle><?= esc_html($subtitle); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>

			<!-- Navigation Arrows -->
			<button
				type="button"
				class="portfolio-prev absolute left-4 min-[1280px]:left-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-black/50 hover:bg-primary transition-colors text-white z-10 max-[1279px]:hidden"
				aria-label="Предыдущий слайд"
				tabindex="0"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
				</svg>
			</button>
			<button
				type="button"
				class="portfolio-next absolute right-4 min-[1280px]:right-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-black/50 hover:bg-primary transition-colors text-white z-10 max-[1279px]:hidden"
				aria-label="Следующий слайд"
				tabindex="0"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
				</svg>
			</button>
		</div>

		<!-- All Projects Button -->
		<div class="flex justify-center mt-10 md:mt-12">
			<a
				href="<?= esc_url(home_url('/portfolio/')); ?>"
				class="inline-flex items-center justify-center border border-primary text-primary hover:bg-primary hover:text-white transition-colors h-[56px] px-10 text-base w-full max-w-[300px] min-[1280px]:w-fit min-[1280px]:max-w-none"
				tabindex="0"
				aria-label="Смотреть все проекты"
			>
				Все проекты
			</a>
		</div>
	</div>
</section>

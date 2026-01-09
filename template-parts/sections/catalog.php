<?php
/**
 * Template Part: Catalog Section
 * Переиспользуемый блок каталога с заголовком и сеткой категорий
 */

declare(strict_types=1);

// Параметры блока (можно передать через $args)
$showTitle = $args['show_title'] ?? true;
$titleTag = $args['title_tag'] ?? 'h2';
$title = $args['title'] ?? 'Каталог';
$titleClasses = $args['title_classes'] ?? 'text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0';
?>

<!-- Catalog Section -->
<section class="bg-black" data-catalog>
	<div class="mx-auto w-full" data-catalog-inner>
		<?php if ($showTitle) : ?>
			<!-- Section Header -->
			<div class="mb-8 md:mb-12">
				<<?= $titleTag; ?> class="<?= esc_attr($titleClasses); ?>">
					<?= esc_html($title); ?>
				</<?= $titleTag; ?>>
				<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
			</div>
		<?php endif; ?>

		<!-- Catalog Grid -->
		<?php get_template_part('template-parts/sections/catalog-grid'); ?>
	</div>
</section>


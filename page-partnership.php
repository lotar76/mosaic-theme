<?php
/**
 * Template Name: Partnership Page
 * Template: Страница Партнерская программа
 */

declare(strict_types=1);

get_header();

$data = mosaic_get_partnership_page();
$title = $data['title'];
$content = $data['content'];
?>

<main class="flex-grow">
	<!-- Breadcrumbs -->
	<div class="pt-[30px] min-[1280px]:pt-[40px]">
		<?php get_template_part('template-parts/breadcrumbs'); ?>
	</div>

	<!-- Page Header -->
	<section class="bg-black py-[40px] min-[1280px]:py-[60px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<h1 class="text-white text-[28px] md:text-[40px] min-[1280px]:text-[56px] leading-[110%] tracking-[-0.01em] font-normal mb-0">
				<?= esc_html($title); ?>
			</h1>
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>
	</section>

	<!-- Content Section -->
	<section class="bg-black py-[40px] min-[1280px]:py-[60px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<div class="max-w-[900px] mx-auto">
				<div class="text-white text-base md:text-[18px] leading-[160%] prose prose-invert max-w-none">
					<?= wp_kses_post($content); ?>
				</div>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>

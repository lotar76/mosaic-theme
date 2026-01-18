<?php
/**
 * Template Name: Новости
 */
get_header();
?>

<main class="flex-grow">
	<!-- Page Header: Breadcrumbs + Title -->
	<div class="bg-black pt-[40px]">
		<!-- Breadcrumbs -->
		<?php get_template_part('template-parts/breadcrumbs'); ?>

		<!-- Title Block -->
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px] pb-[40px]">
			<!-- Page Title -->
			<h1 class="text-white text-[70px] leading-[100%] tracking-[-0.01em] font-normal mt-8 mb-0">
				Новости
			</h1>

			<!-- Title Underline -->
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>
	</div>

	<!-- Page Content Block -->
	<div>
		<!-- News Grid Section -->
		<?php get_template_part('template-parts/sections/news-grid', null, [
			'show_title' => false
		]); ?>
	</div>

	<!-- Reusable Blocks Container -->
	<div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
		<!-- Benefits Section -->
		<?php get_template_part('template-parts/sections/benefits'); ?>

		<!-- Contact Form Section -->
		<?php get_template_part('template-parts/sections/contact-form'); ?>
	</div>
</main>

<?php get_footer(); ?>

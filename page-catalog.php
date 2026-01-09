<?php
/**
 * Template Name: Каталог
 */
get_header();
?>

<main class="flex-grow">
    <?php get_template_part('template-parts/breadcrumbs'); ?>

    <!-- Content Sections with uniform spacing -->
    <div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
        <!-- Catalog Section -->
        <?php get_template_part('template-parts/sections/catalog', null, [
            'title_tag' => 'h1',
            'title' => 'Каталог',
            'title_classes' => 'text-white text-4xl md:text-5xl lg:text-6xl font-normal mb-0'
        ]); ?>

        <!-- Benefits Section -->
        <?php get_template_part('template-parts/sections/benefits'); ?>

        <!-- Contact Form Section -->
        <?php get_template_part('template-parts/sections/contact-form'); ?>
    </div>
</main>

<?php get_footer(); ?>





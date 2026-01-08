<?php
/**
 * Базовый шаблон страницы
 */
get_header();
?>

<main class="flex-grow">
    <section class="bg-black py-12 md:py-16 lg:py-20">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-[100px]">
            <?php while (have_posts()) : the_post(); ?>
                <!-- Page Title -->
                <div class="mb-8 md:mb-12">
                    <h1 class="text-white text-4xl md:text-5xl lg:text-6xl font-normal mb-0"><?php the_title(); ?></h1>
                    <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
                </div>

                <!-- Page Content -->
                <div class="prose prose-invert prose-lg max-w-none text-white">
                    <?php the_content(); ?>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>





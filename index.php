<?php
/**
 * Fallback шаблон
 */
get_header();
?>

<main class="flex-grow">
    <section class="bg-black py-12 md:py-16 lg:py-20">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-[100px]">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article class="mb-8">
                        <h2 class="text-white text-2xl md:text-3xl font-normal mb-4">
                            <a href="<?php the_permalink(); ?>" class="hover:text-primary transition-colors">
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        <div class="text-white/70">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p class="text-white">Записи не найдены.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>



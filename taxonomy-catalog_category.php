<?php
/**
 * Template: Раздел каталога (taxonomy: catalog_category)
 */
get_header();

/** @var WP_Term|null $term */
$term = get_queried_object();
if (!($term instanceof WP_Term)) {
	$term = null;
}

$termName = $term ? (string) ($term->name ?? '') : '';
$termName = $termName !== '' ? $termName : 'Каталог';

$paged = max(1, (int) (get_query_var('paged') ?: 1));

$q = new WP_Query([
	'post_type' => 'catalog_item',
	'posts_per_page' => 12,
	'paged' => $paged,
	'tax_query' => $term
		? [
			[
				'taxonomy' => 'catalog_category',
				'field' => 'term_id',
				'terms' => [(int) $term->term_id],
			],
		]
		: [],
]);
?>

<main class="flex-grow">
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
            <div class="mb-8 md:mb-12">
                <h1 class="text-white text-4xl md:text-5xl lg:text-6xl font-normal mb-0">
					<?= esc_html($termName); ?>
                </h1>
                <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
            </div>

			<?php if ($q->have_posts()) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 min-[1280px]:grid-cols-3 gap-6">
					<?php while ($q->have_posts()) : ?>
						<?php
						$q->the_post();
						$postId = (int) get_the_ID();
						$title = (string) get_the_title();
						$url = (string) get_permalink($postId);
						$thumb = get_the_post_thumbnail_url($postId, 'large');
						$thumb = is_string($thumb) ? $thumb : '';
						?>
                        <a
                            href="<?= esc_url($url); ?>"
                            class="group bg-black block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                            tabindex="0"
                            aria-label="<?= esc_attr($title); ?>"
                        >
                            <div class="aspect-[4/3] overflow-hidden bg-gray/20">
								<?php if ($thumb !== '') : ?>
                                    <img
                                        src="<?= esc_url($thumb); ?>"
                                        alt="<?= esc_attr($title); ?>"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        loading="lazy"
                                        decoding="async"
                                    >
								<?php endif; ?>
                            </div>
                            <div class="p-6">
                                <div class="text-white text-lg"><?= esc_html($title); ?></div>
                            </div>
                        </a>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
                </div>
			<?php else : ?>
                <div class="text-white/70">
                    Пока пусто. Добавь элементы в админке: <strong>Каталог</strong>.
                </div>
			<?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>




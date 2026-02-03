<?php
/**
 * Template: Раздел каталога (taxonomy: product_section)
 */
get_header();

/** @var WP_Term|null $term */
$term = get_queried_object();
if (!($term instanceof WP_Term)) {
	$term = null;
}

$termName = $term ? (string) ($term->name ?? '') : '';
$termName = $termName !== '' ? $termName : 'Каталог';

$termDescription = $term ? trim((string) term_description($term->term_id, 'product_section')) : '';

$paged = max(1, (int) (get_query_var('paged') ?: 1));

$q = new WP_Query([
	'post_type' => 'product',
	'posts_per_page' => 16,
	'paged' => $paged,
	'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
	'tax_query' => $term
		? [
			[
				'taxonomy' => 'product_section',
				'field' => 'term_id',
				'terms' => [(int) $term->term_id],
			],
		]
		: [],
]);
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
				<?= esc_html($termName); ?>
            </h1>

            <!-- Title Underline -->
            <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
        </div>
    </div>

	<?php if ($termDescription !== '') : ?>
        <!-- Category Description Block (без фона, белый текст) -->
        <section class="bg-black pb-[40px]">
            <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
                <div class="text3 text-white max-w-none">
					<?= wp_kses_post(wpautop($termDescription)); ?>
                </div>
            </div>
        </section>
	<?php endif; ?>

    <!-- Catalog Items Grid -->
    <section class="bg-black pb-[80px] min-[1280px]:pb-[100px]" data-catalog>
        <div class="mx-auto w-full" data-catalog-inner>
			<?php if ($q->have_posts()) : ?>
                <div data-catalog-grid>
					<?php
					$keys = function_exists('mosaic_catalog_item_meta_keys') ? mosaic_catalog_item_meta_keys() : [];
					$kGallery = (string) ($keys['gallery_ids'] ?? '_mosaic_catalog_gallery_ids');

					while ($q->have_posts()) :
						$q->the_post();
						$postId = (int) get_the_ID();
						$title = (string) get_the_title();
						$url = (string) get_permalink($postId);

						// Получаем первую картинку из галереи товара
						$galleryIds = get_post_meta($postId, $kGallery, true);
						if (!is_array($galleryIds)) {
							$galleryIds = [];
						}
						$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));
						$firstImageId = count($galleryIds) > 0 ? $galleryIds[0] : 0;

						$thumb = $firstImageId > 0 ? (string) wp_get_attachment_image_url($firstImageId, 'large') : '';
						?>
                        <a
                            href="<?= esc_url($url); ?>"
                            class="group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                            tabindex="0"
                            aria-label="<?= esc_attr($title); ?>"
                            data-catalog-card
                        >
                            <div class="bg-gray/20" data-catalog-media>
								<?php if ($thumb !== '') : ?>
                                    <img
                                        src="<?= esc_url($thumb); ?>"
                                        alt="<?= esc_attr($title); ?>"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                        loading="lazy"
                                        decoding="async"
                                    >
								<?php endif; ?>
                            </div>
                            <div class="mt-3 md:mt-4" data-catalog-meta>
                                <span data-catalog-caption><?= esc_html($title); ?></span>
                            </div>
                        </a>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
                </div>

				<?php if ($q->max_num_pages > 1) : ?>
                    <!-- Pagination -->
                    <div class="mt-12">
						<?php
						$paginationLinks = paginate_links([
							'total' => $q->max_num_pages,
							'current' => $paged,
							'format' => '?paged=%#%',
							'prev_text' => '&larr; Назад',
							'next_text' => 'Вперёд &rarr;',
							'type' => 'array',
							'end_size' => 2,
							'mid_size' => 2,
						]);

						if (is_array($paginationLinks) && count($paginationLinks) > 0) :
							?>
                            <nav aria-label="Навигация по страницам" class="flex justify-center">
                                <ul class="flex flex-wrap items-center gap-2">
									<?php foreach ($paginationLinks as $link) : ?>
                                        <li>
											<?= str_replace(
												['<a ', '<span ', 'class="'],
												[
													'<a class="inline-flex items-center justify-center min-w-[44px] h-[44px] px-3 text-white/70 hover:text-white hover:bg-white/10 transition-colors border border-white/20 hover:border-white/40" ',
													'<span class="inline-flex items-center justify-center min-w-[44px] h-[44px] px-3 ',
													'class="inline-flex items-center justify-center min-w-[44px] h-[44px] px-3 ',
												],
												$link
											); ?>
                                        </li>
									<?php endforeach; ?>
                                </ul>
                            </nav>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
			<?php else : ?>
                <div class="text-white/70 text-center py-12">
                    <p class="text-xl">Пока пусто. Добавьте элементы в админке: <strong>Каталог</strong>.</p>
                </div>
			<?php endif; ?>
        </div>
    </section>

    <!-- Reusable Blocks Container -->
    <div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
        <!-- Benefits Section -->
		<?php get_template_part('template-parts/sections/benefits'); ?>

        <!-- Contact Form Section -->
		<?php get_template_part('template-parts/sections/contact-form'); ?>
    </div>
</main>

<?php get_footer(); ?>




<?php
/**
 * Template: Элемент каталога (CPT: catalog_item)
 */
get_header();

$postId = (int) get_the_ID();
$title = (string) get_the_title();
$thumb = get_the_post_thumbnail_url($postId, 'full');
$thumb = is_string($thumb) ? $thumb : '';

$keys = function_exists('mosaic_catalog_item_meta_keys') ? mosaic_catalog_item_meta_keys() : [];
$kGallery = (string) ($keys['gallery_ids'] ?? '_mosaic_catalog_gallery_ids');
$kMaterial = (string) ($keys['material'] ?? '_mosaic_catalog_material');
$kTechnique = (string) ($keys['technique'] ?? '_mosaic_catalog_technique');
$kSizeColor = (string) ($keys['size_color'] ?? '_mosaic_catalog_size_color');
$kRelated = (string) ($keys['related_ids'] ?? '_mosaic_catalog_related_ids');

$galleryIds = get_post_meta($postId, $kGallery, true);
if (!is_array($galleryIds)) {
	$galleryIds = [];
}
$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

$material = trim((string) get_post_meta($postId, $kMaterial, true));
$technique = trim((string) get_post_meta($postId, $kTechnique, true));
$sizeColor = trim((string) get_post_meta($postId, $kSizeColor, true));

$relatedIds = get_post_meta($postId, $kRelated, true);
if (!is_array($relatedIds)) {
	$relatedIds = [];
}
$relatedIds = array_values(array_filter(array_map('absint', $relatedIds), static fn($v) => $v > 0 && $v !== $postId));
?>

<main class="flex-grow">
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
            <div class="mb-8 md:mb-12">
                <h1 class="text-white text-4xl md:text-5xl lg:text-6xl font-normal mb-0">
					<?= esc_html($title); ?>
                </h1>
                <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
            </div>

			<?php if (count($galleryIds) > 0) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 min-[1280px]:grid-cols-3 gap-4 mb-8">
					<?php foreach ($galleryIds as $gid) : ?>
						<?php $src = (string) wp_get_attachment_image_url((int) $gid, 'large'); ?>
						<?php if ($src === '') : ?>
							<?php continue; ?>
						<?php endif; ?>
                        <div class="aspect-[4/3] overflow-hidden bg-gray/20">
                            <img
                                src="<?= esc_url($src); ?>"
                                alt="<?= esc_attr($title); ?>"
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
					<?php endforeach; ?>
                </div>
			<?php elseif ($thumb !== '') : ?>
                <div class="aspect-[16/9] overflow-hidden bg-gray/20 mb-8">
                    <img
                        src="<?= esc_url($thumb); ?>"
                        alt="<?= esc_attr($title); ?>"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
			<?php endif; ?>

			<?php if ($material !== '' || $technique !== '' || $sizeColor !== '') : ?>
                <div class="bg-gray/20 border border-white/10 rounded-xl p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<?php if ($material !== '') : ?>
                            <div>
                                <div class="text-white/60 text-sm mb-2">Материал изделия</div>
                                <div class="text-white"><?= esc_html($material); ?></div>
                            </div>
						<?php endif; ?>
						<?php if ($technique !== '') : ?>
                            <div>
                                <div class="text-white/60 text-sm mb-2">Техника сборки</div>
                                <div class="text-white"><?= esc_html($technique); ?></div>
                            </div>
						<?php endif; ?>
						<?php if ($sizeColor !== '') : ?>
                            <div>
                                <div class="text-white/60 text-sm mb-2">Размер и цветовая гамма</div>
                                <div class="text-white"><?= esc_html($sizeColor); ?></div>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
			<?php endif; ?>

            <article class="prose prose-invert max-w-none text-white/80">
				<?php
				$desc = (string) get_post_field('post_content', $postId);
				$desc = trim($desc);
				echo $desc !== '' ? wp_kses_post(wpautop(esc_html($desc))) : '';
				?>
            </article>

			<?php if (count($relatedIds) > 0) : ?>
				<?php
				$relatedQ = new WP_Query([
					'post_type' => 'catalog_item',
					'post_status' => 'publish',
					'posts_per_page' => min(12, count($relatedIds)),
					'post__in' => $relatedIds,
					'orderby' => 'post__in',
				]);
				?>
				<?php if ($relatedQ->have_posts()) : ?>
                    <section class="mt-12">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h2 class="text-white text-3xl md:text-4xl font-normal mb-0">Похожие товары</h2>
                                <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 min-[1280px]:grid-cols-3 gap-6">
							<?php while ($relatedQ->have_posts()) : ?>
								<?php
								$relatedQ->the_post();
								$rid = (int) get_the_ID();
								$rt = (string) get_the_title();
								$rurl = (string) get_permalink($rid);

								$rGallery = get_post_meta($rid, $kGallery, true);
								if (!is_array($rGallery)) {
									$rGallery = [];
								}
								$rGallery = array_values(array_filter(array_map('absint', $rGallery), static fn($v) => $v > 0));

								$rImg = '';
								if (count($rGallery) > 0) {
									$rImg = (string) wp_get_attachment_image_url((int) $rGallery[0], 'large');
								}
								if ($rImg === '') {
									$maybe = get_the_post_thumbnail_url($rid, 'large');
									$rImg = is_string($maybe) ? $maybe : '';
								}
								?>
                                <a
                                    href="<?= esc_url($rurl); ?>"
                                    class="group bg-black block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                                    tabindex="0"
                                    aria-label="<?= esc_attr($rt); ?>"
                                >
                                    <div class="aspect-[4/3] overflow-hidden bg-gray/20">
										<?php if ($rImg !== '') : ?>
                                            <img
                                                src="<?= esc_url($rImg); ?>"
                                                alt="<?= esc_attr($rt); ?>"
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                loading="lazy"
                                                decoding="async"
                                            >
										<?php endif; ?>
                                    </div>
                                    <div class="p-6">
                                        <div class="text-white text-lg"><?= esc_html($rt); ?></div>
                                    </div>
                                </a>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
                        </div>
                    </section>
				<?php endif; ?>
			<?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>



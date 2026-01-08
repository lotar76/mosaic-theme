<?php
/**
 * Template Name: Каталог
 */
get_header();
?>

<main class="flex-grow">
    <!-- Page Header -->
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]" data-catalog>
        <div class="mx-auto w-full" data-catalog-inner>
            <!-- Page Title -->
            <div class="mb-8 md:mb-12">
                <h1 class="text-white text-4xl md:text-5xl lg:text-6xl font-normal mb-0">Каталог</h1>
                <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
            </div>

            <!-- Catalog Grid -->
			<?php
			$catalogCards = function_exists('mosaic_get_catalog_category_cards') ? mosaic_get_catalog_category_cards() : [];
			$fallbackCatalog = mosaic_get_catalog_categories();
			?>
            <div data-catalog-grid>
				<?php if (is_array($catalogCards) && count($catalogCards) > 0) : ?>
					<?php foreach ($catalogCards as $card) : ?>
						<?php
						$categoryTitle = (string) ($card['title'] ?? '');
						$categoryUrl = (string) ($card['url'] ?? '');
						$imageUrlRaw = (string) ($card['image_url'] ?? '');
						$interiorImageUrlRaw = (string) ($card['interior_image_url'] ?? '');
						$videoUrlRaw = (string) ($card['video_url'] ?? '');

						if ($categoryTitle === '' || $categoryUrl === '') {
							continue;
						}

						$imageUrl = $imageUrlRaw !== '' ? esc_url($imageUrlRaw) : '';
						$interiorImageUrl = $interiorImageUrlRaw !== '' ? esc_url($interiorImageUrlRaw) : '';
						$videoUrl = $videoUrlRaw !== '' ? esc_url($videoUrlRaw) : '';
						$hasInterior = ($videoUrl === '' && $interiorImageUrl !== '');
						?>
                    <a
                        href="<?= esc_url($categoryUrl); ?>"
                        class="group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                        tabindex="0"
                        aria-label="<?= esc_attr($categoryTitle); ?>"
                        data-catalog-card
                        <?php if ($videoUrl !== '') : ?>data-has-video="1"<?php endif; ?>
                        <?php if ($hasInterior) : ?>data-has-interior="1"<?php endif; ?>
                    >
                        <div class="bg-gray/20" data-catalog-media>
                            <img
                                src="<?= $imageUrl !== '' ? $imageUrl : esc_url(get_template_directory_uri() . '/img/catalog/1.png'); ?>"
                                alt="<?= esc_attr($categoryTitle); ?>"
                                class="w-full h-full object-cover transition-opacity duration-500 ease-in-out transition-transform duration-500 group-hover:scale-[1.03]"
                                loading="lazy"
                                decoding="async"
                                data-catalog-image
                            >
							<?php if ($hasInterior) : ?>
                                <img
                                    src="<?= esc_url($interiorImageUrl); ?>"
                                    alt="<?= esc_attr($categoryTitle); ?>"
                                    class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 ease-in-out"
                                    loading="lazy"
                                    decoding="async"
                                    data-catalog-interior-image
                                >
							<?php endif; ?>
							<?php if ($videoUrl !== '') : ?>
                                <video
                                    class="absolute inset-0 w-full h-full object-cover object-top opacity-0 transition-opacity duration-500"
                                    muted
                                    playsinline
                                    preload="auto"
                                    poster="<?= $imageUrl !== '' ? esc_url($imageUrl) : esc_url(get_template_directory_uri() . '/img/catalog/1.png'); ?>"
                                    data-catalog-video
                                >
                                    <source src="<?= $videoUrl; ?>" type="video/mp4">
                                </video>
							<?php endif; ?>
                        </div>
                        <div class="mt-3 min-[1280px]:mt-4 flex items-center justify-between gap-3" data-catalog-meta>
                            <span data-catalog-caption>
                                <?= esc_html($categoryTitle); ?>
                            </span>
                            <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ($fallbackCatalog as $category) : ?>
						<?php
						$categoryTitle = (string) ($category['title'] ?? '');
						$categorySlug = (string) ($category['slug'] ?? '');
						$categoryImage = (string) ($category['image'] ?? '');
						$categoryVideo = (string) ($category['video'] ?? '');

						if ($categoryTitle === '' || $categorySlug === '' || $categoryImage === '') {
							continue;
						}

						$categoryUrl = esc_url(home_url('/catalog/' . $categorySlug . '/'));
						$imageUrl = esc_url(get_template_directory_uri() . $categoryImage);
						?>
                        <a
                            href="<?= $categoryUrl; ?>"
                            class="group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                            tabindex="0"
                            aria-label="<?= esc_attr($categoryTitle); ?>"
                            data-catalog-card
							<?php if ($categoryVideo !== '') : ?>data-has-video="1"<?php endif; ?>
                        >
                            <div class="bg-gray/20" data-catalog-media>
                                <img
                                    src="<?= $imageUrl; ?>"
                                    alt="<?= esc_attr($categoryTitle); ?>"
                                    class="w-full h-full object-cover transition-opacity duration-500 ease-in-out transition-transform duration-500 group-hover:scale-[1.03]"
                                    loading="lazy"
                                    decoding="async"
                                    data-catalog-image
                                >
								<?php if ($categoryVideo !== '') : ?>
									<?php $videoUrl = esc_url(get_template_directory_uri() . $categoryVideo); ?>
                                    <video
                                        class="absolute inset-0 w-full h-full object-cover object-top opacity-0 transition-opacity duration-500"
                                        muted
                                        playsinline
                                        preload="auto"
                                        poster="<?= esc_url($imageUrl); ?>"
                                        data-catalog-video
                                    >
                                        <source src="<?= $videoUrl; ?>" type="video/mp4">
                                    </video>
								<?php endif; ?>
                            </div>
                            <div class="mt-3 min-[1280px]:mt-4 flex items-center justify-between gap-3" data-catalog-meta>
                                <span data-catalog-caption>
									<?= esc_html($categoryTitle); ?>
                                </span>
                                <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
					<?php endforeach; ?>
				<?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>





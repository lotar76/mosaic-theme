<?php
/**
 * Template: Товар (CPT: product)
 */
get_header();

$postId = (int) get_the_ID();
$title = (string) get_the_title();

$keys = function_exists('mosaic_catalog_item_meta_keys') ? mosaic_catalog_item_meta_keys() : [];
$kGallery = (string) ($keys['gallery_ids'] ?? '_mosaic_catalog_gallery_ids');
$kMaterial = (string) ($keys['material'] ?? '_mosaic_catalog_material');
$kTechnique = (string) ($keys['technique'] ?? '_mosaic_catalog_technique');
$kSizeColor = (string) ($keys['size_color'] ?? '_mosaic_catalog_size_color');

$galleryIds = get_post_meta($postId, $kGallery, true);
if (!is_array($galleryIds)) {
	$galleryIds = [];
}
$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

$material = trim((string) get_post_meta($postId, $kMaterial, true));
$technique = trim((string) get_post_meta($postId, $kTechnique, true));
$sizeColor = trim((string) get_post_meta($postId, $kSizeColor, true));

$description = trim((string) get_post_field('post_content', $postId));

// Первое изображение для главного слайда
$mainImageId = count($galleryIds) > 0 ? $galleryIds[0] : 0;
$mainImageUrl = $mainImageId > 0 ? (string) wp_get_attachment_image_url($mainImageId, 'full') : '';
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
				<?= esc_html($title); ?>
            </h1>

            <!-- Title Underline -->
            <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
        </div>
    </div>

    <!-- Product Content: Gallery + Info -->
    <section class="bg-black pb-[80px] min-[1280px]:pb-[100px]">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
            <div class="grid grid-cols-1 lg:grid-cols-[auto_1fr] gap-8 lg:gap-[50px]">
                <!-- Left: Gallery -->
                <div class="product-gallery">
					<?php if (count($galleryIds) > 0) : ?>
                        <!-- Main Image -->
                        <div class="relative mb-4">
                            <div class="relative bg-gray/20 group cursor-pointer w-fit" id="gallery-main-container">
								<?php if ($mainImageUrl !== '') : ?>
                                    <img
                                        id="product-main-image"
                                        src="<?= esc_url($mainImageUrl); ?>"
                                        alt="<?= esc_attr($title); ?>"
                                        class="block w-full max-w-full h-auto aspect-square object-cover min-[1280px]:w-[700px] min-[1280px]:h-[700px]"
                                        loading="eager"
                                        decoding="async"
                                    >
								<?php endif; ?>

                                <!-- Fullscreen Button -->
                                <button
                                    type="button"
                                    id="gallery-fullscreen-btn"
                                    class="absolute top-4 right-4 w-10 h-10 bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors opacity-0 group-hover:opacity-100 focus-visible:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
                                    aria-label="Открыть на весь экран"
                                    tabindex="0"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                    </svg>
                                </button>

                                <!-- Navigation Arrows -->
								<?php if (count($galleryIds) > 1) : ?>
                                    <button
                                        type="button"
                                        id="gallery-prev"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
                                        aria-label="Предыдущее изображение"
                                        tabindex="0"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        id="gallery-next"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
                                        aria-label="Следующее изображение"
                                        tabindex="0"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
								<?php endif; ?>
                            </div>
                        </div>

                        <!-- Thumbnails -->
						<?php if (count($galleryIds) > 1) : ?>
                            <div class="flex gap-2 overflow-x-auto pb-2 justify-center min-[1280px]:justify-start">
								<?php foreach ($galleryIds as $idx => $gid) : ?>
									<?php
									$thumbUrl = (string) wp_get_attachment_image_url((int) $gid, 'medium');
									$fullUrl = (string) wp_get_attachment_image_url((int) $gid, 'full');
									if ($thumbUrl === '') {
										continue;
									}
									$isActive = $idx === 0;
									?>
                                    <button
                                        type="button"
                                        class="gallery-thumb flex-shrink-0 w-16 h-16 md:w-20 md:h-20 min-[1280px]:w-24 min-[1280px]:h-24 overflow-hidden bg-gray/20 border-2 transition-all <?= $isActive ? 'border-primary' : 'border-transparent opacity-60 hover:opacity-100'; ?>"
                                        data-image-url="<?= esc_attr($fullUrl); ?>"
                                        data-index="<?= esc_attr((string) $idx); ?>"
                                        aria-label="<?= esc_attr('Изображение ' . (string) ($idx + 1)); ?>"
                                        tabindex="0"
                                    >
                                        <img
                                            src="<?= esc_url($thumbUrl); ?>"
                                            alt=""
                                            class="w-full h-full object-cover"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </button>
								<?php endforeach; ?>
                            </div>
						<?php endif; ?>

                        <!-- Logo (optional, можно добавить позже) -->
					<?php elseif ($mainImageUrl === '') : ?>
                        <div class="w-full aspect-[4/3] bg-gray/20 flex items-center justify-center">
                            <span class="text-white/40">Нет изображения</span>
                        </div>
					<?php endif; ?>
                </div>

                <!-- Right: Product Info -->
                <div class="product-info">
                    <!-- Title (already in header, but can add here if needed) -->
                    
                    <!-- Price Block -->
                    <div class="bg-gray p-[30px] mb-6">
                        <div class="text-white text-xl mb-2">Стоимость по запросу</div>
                        <p class="text-[#847575] text-sm mb-4">
                            Стоимость каждого проекта уникальна и зависит от габаритов, сложности и сроков
                        </p>
                        <a
                            href="#contact-form"
                            class="inline-block bg-primary hover:bg-opacity-90 transition-colors text-white py-3 px-6 text-base"
                            tabindex="0"
                            aria-label="Получить консультацию"
                        >
                            Получить консультацию
                        </a>
                    </div>

                    <!-- Description -->
					<?php if ($description !== '') : ?>
                        <div class="mb-8">
                            <div class="text-white text-base leading-relaxed">
								<?= wp_kses_post(wpautop($description)); ?>
                            </div>
                        </div>
					<?php endif; ?>

                    <!-- Specifications -->
					<?php if ($material !== '' || $technique !== '' || $sizeColor !== '') : ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<?php if ($material !== '') : ?>
                                <div>
                                    <div class="text-white/60 text-sm mb-1">Материал изделия</div>
                                    <div class="text-white"><?= esc_html($material); ?></div>
                                </div>
							<?php endif; ?>
							<?php if ($technique !== '') : ?>
                                <div>
                                    <div class="text-white/60 text-sm mb-1">Техника сборки</div>
                                    <div class="text-white"><?= esc_html($technique); ?></div>
                                </div>
							<?php endif; ?>
							<?php if ($sizeColor !== '') : ?>
                                <div class="md:col-span-2">
                                    <div class="text-white/60 text-sm mb-1">Размер и цветовая гамма</div>
                                    <div class="text-white"><?= esc_html($sizeColor); ?></div>
                                </div>
							<?php endif; ?>
                        </div>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Work Process Section -->
    <?php get_template_part('template-parts/sections/work-process'); ?>

    <!-- Related Products Section -->
    <?php
    $kRelated = '_mosaic_catalog_related_ids';
    $relatedIdsRaw = get_post_meta($postId, $kRelated, true);

    // Нормализуем - может быть массив или строка через запятую
    if (is_array($relatedIdsRaw)) {
        $relatedIds = $relatedIdsRaw;
    } elseif (is_string($relatedIdsRaw) && $relatedIdsRaw !== '') {
        $relatedIds = array_map('trim', explode(',', $relatedIdsRaw));
    } else {
        $relatedIds = [];
    }
    $relatedIds = array_values(array_filter(array_map('absint', $relatedIds), static fn($v) => $v > 0 && $v !== $postId));

    if (count($relatedIds) > 0) :
        $relatedProducts = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'post__in' => $relatedIds,
            'orderby' => 'post__in',
            'posts_per_page' => -1,
        ]);

        if (!empty($relatedProducts)) :
    ?>
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]" data-related-products>
        <div class="max-w-[1920px] mx-auto pl-4 md:pl-8 min-[1280px]:pl-10 min-[1920px]:pl-[100px] pr-0">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8 md:mb-12">
                <div>
                    <h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Похожие товары</h2>
                    <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
                </div>

                <!-- Navigation Arrows -->
                <div class="flex gap-[37px] max-[1279px]:hidden mr-[99px]">
                    <button
                        type="button"
                        class="related-prev p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
                        aria-label="Предыдущий слайд"
                        tabindex="0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="related-next p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
                        aria-label="Следующий слайд"
                        tabindex="0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Related Products Slider -->
            <div class="related-slider overflow-hidden">
                <div class="related-track flex transition-transform duration-700 ease-in-out" data-related-track>
                    <?php foreach ($relatedProducts as $relatedProduct) : ?>
                        <?php
                        $relId = (int) $relatedProduct->ID;
                        $relTitle = (string) get_the_title($relId);
                        $relUrl = (string) get_permalink($relId);

                        // Получаем первое изображение из галереи
                        $relGalleryIds = get_post_meta($relId, $kGallery, true);
                        $relThumbUrl = '';
                        if (is_array($relGalleryIds) && count($relGalleryIds) > 0) {
                            $firstImgId = absint($relGalleryIds[0]);
                            if ($firstImgId > 0) {
                                $relThumbUrl = (string) wp_get_attachment_image_url($firstImgId, 'medium_large');
                            }
                        }
                        // Fallback на featured image
                        if ($relThumbUrl === '') {
                            $maybe = get_the_post_thumbnail_url($relId, 'medium_large');
                            $relThumbUrl = is_string($maybe) ? $maybe : '';
                        }
                        // Placeholder если нет изображения
                        if ($relThumbUrl === '') {
                            $relThumbUrl = get_template_directory_uri() . '/img/placeholder.jpg';
                        }
                        ?>
                        <a
                            href="<?= esc_url($relUrl); ?>"
                            class="related-slide group flex-shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                            tabindex="0"
                            aria-label="<?= esc_attr($relTitle); ?>"
                            data-related-slide
                        >
                            <div class="bg-gray overflow-hidden" data-related-media>
                                <img
                                    src="<?= esc_url($relThumbUrl); ?>"
                                    alt="<?= esc_attr($relTitle); ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>

                            <div data-related-caption>
                                <div data-related-title><?= esc_html($relTitle); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
        endif;
    endif;
    ?>
</main>

<!-- Fullscreen Gallery Modal -->
<?php if (count($galleryIds) > 0) : ?>
<div id="gallery-fullscreen-modal" class="fixed inset-0 z-[100] bg-black/95 hidden items-center justify-center">
    <button
        type="button"
        id="gallery-fullscreen-close"
        class="absolute top-4 right-4 w-12 h-12 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
        aria-label="Закрыть"
        tabindex="0"
    >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <?php if (count($galleryIds) > 1) : ?>
    <button
        type="button"
        id="gallery-fullscreen-prev"
        class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
        aria-label="Предыдущее изображение"
        tabindex="0"
    >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button
        type="button"
        id="gallery-fullscreen-next"
        class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
        aria-label="Следующее изображение"
        tabindex="0"
    >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <?php endif; ?>

    <div class="w-full h-full flex items-center justify-center p-4">
        <img
            id="gallery-fullscreen-image"
            src=""
            alt="<?= esc_attr($title); ?>"
            class="max-w-full max-h-full object-contain"
            loading="lazy"
            decoding="async"
        >
    </div>
</div>
<?php endif; ?>

<?php
// Gallery navigation script
if (count($galleryIds) > 0) :
	$galleryUrls = [];
	foreach ($galleryIds as $gid) {
		$url = (string) wp_get_attachment_image_url((int) $gid, 'full');
		if ($url !== '') {
			$galleryUrls[] = $url;
		}
	}
	?>
<script>
(function() {
	var currentIndex = 0;
	var galleryUrls = <?= wp_json_encode($galleryUrls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	var mainImg = document.getElementById('product-main-image');
	var thumbs = document.querySelectorAll('.gallery-thumb');
	var prevBtn = document.getElementById('gallery-prev');
	var nextBtn = document.getElementById('gallery-next');
	var fullscreenModal = document.getElementById('gallery-fullscreen-modal');
	var fullscreenImg = document.getElementById('gallery-fullscreen-image');
	var fullscreenBtn = document.getElementById('gallery-fullscreen-btn');
	var fullscreenClose = document.getElementById('gallery-fullscreen-close');
	var fullscreenPrev = document.getElementById('gallery-fullscreen-prev');
	var fullscreenNext = document.getElementById('gallery-fullscreen-next');

	if (!mainImg || galleryUrls.length === 0) return;

	function updateMainImage(index) {
		if (index < 0 || index >= galleryUrls.length) return;
		currentIndex = index;
		mainImg.src = galleryUrls[index];
		
		if (thumbs.length > 0) {
			thumbs.forEach(function(thumb, idx) {
				if (idx === index) {
					thumb.classList.add('border-primary', 'opacity-100');
					thumb.classList.remove('border-transparent', 'opacity-60');
				} else {
					thumb.classList.remove('border-primary', 'opacity-100');
					thumb.classList.add('border-transparent', 'opacity-60');
				}
			});
		}
	}

	function updateFullscreenImage(index) {
		if (index < 0 || index >= galleryUrls.length) return;
		currentIndex = index;
		if (fullscreenImg) {
			fullscreenImg.src = galleryUrls[index];
		}
	}

	function openFullscreen() {
		if (!fullscreenModal || !fullscreenImg) return;
		updateFullscreenImage(currentIndex);
		fullscreenModal.classList.remove('hidden');
		fullscreenModal.classList.add('flex');
		document.body.style.overflow = 'hidden';
	}

	function closeFullscreen() {
		if (!fullscreenModal) return;
		fullscreenModal.classList.add('hidden');
		fullscreenModal.classList.remove('flex');
		document.body.style.overflow = '';
	}

	if (thumbs.length > 0) {
		thumbs.forEach(function(thumb, idx) {
			thumb.addEventListener('click', function() {
				updateMainImage(idx);
			});
		});
	}

	if (prevBtn) {
		prevBtn.addEventListener('click', function() {
			var newIndex = currentIndex - 1;
			if (newIndex < 0) newIndex = galleryUrls.length - 1;
			updateMainImage(newIndex);
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function() {
			var newIndex = currentIndex + 1;
			if (newIndex >= galleryUrls.length) newIndex = 0;
			updateMainImage(newIndex);
		});
	}

	if (fullscreenBtn) {
		fullscreenBtn.addEventListener('click', function(e) {
			e.stopPropagation();
			openFullscreen();
		});
	}

	if (fullscreenClose) {
		fullscreenClose.addEventListener('click', closeFullscreen);
	}

	if (fullscreenModal) {
		fullscreenModal.addEventListener('click', function(e) {
			if (e.target === fullscreenModal) {
				closeFullscreen();
			}
		});
	}

	if (fullscreenPrev) {
		fullscreenPrev.addEventListener('click', function(e) {
			e.stopPropagation();
			var newIndex = currentIndex - 1;
			if (newIndex < 0) newIndex = galleryUrls.length - 1;
			updateFullscreenImage(newIndex);
		});
	}

	if (fullscreenNext) {
		fullscreenNext.addEventListener('click', function(e) {
			e.stopPropagation();
			var newIndex = currentIndex + 1;
			if (newIndex >= galleryUrls.length) newIndex = 0;
			updateFullscreenImage(newIndex);
		});
	}

	// Close on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && fullscreenModal && !fullscreenModal.classList.contains('hidden')) {
			closeFullscreen();
		}
	});
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>

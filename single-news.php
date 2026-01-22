<?php
/**
 * Template: Новость (CPT: news)
 */
get_header();

global $post;
setup_postdata($post);

$postId = (int) get_the_ID();
$title = (string) get_the_title();
$content = apply_filters('the_content', $post->post_content);

// Получаем галерею
$galleryIds = get_post_meta($postId, '_mosaic_news_gallery', true);
if (!is_array($galleryIds)) {
	$galleryIds = [];
}
$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

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

	<!-- News Gallery Slider -->
	<?php if (count($galleryIds) > 0) : ?>
	<section class="bg-black">
		<div class="max-w-[1920px] mx-auto pl-4 md:pl-8 lg:pl-16 min-[1920px]:pl-[100px]">
			<!-- Slider Container -->
			<div class="relative overflow-hidden" id="news-slider-container">
				<!-- Slides Wrapper -->
				<div class="flex gap-4 transition-transform duration-500 ease-out" id="news-slider-track">
					<?php foreach ($galleryIds as $idx => $gid) : ?>
						<?php
						$imageUrl = (string) wp_get_attachment_image_url((int) $gid, 'full');
						if ($imageUrl === '') {
							continue;
						}
						?>
						<div class="flex-shrink-0 w-[calc(25%-12px)]" data-slide-index="<?= esc_attr((string) $idx); ?>">
							<div class="aspect-[4/3] bg-gray/20 overflow-hidden cursor-pointer news-slide-item" data-full-url="<?= esc_attr($imageUrl); ?>">
								<img
									src="<?= esc_url($imageUrl); ?>"
									alt="<?= esc_attr($title); ?>"
									class="w-full h-full object-cover"
									loading="<?= $idx < 4 ? 'eager' : 'lazy'; ?>"
									decoding="async"
								>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Navigation Arrows -->
				<?php if (count($galleryIds) > 4) : ?>
				<button
					type="button"
					id="news-slider-prev"
					class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
					aria-label="Предыдущие изображения"
				>
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
					</svg>
				</button>
				<button
					type="button"
					id="news-slider-next"
					class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
					aria-label="Следующие изображения"
				>
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
					</svg>
				</button>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- News Content -->
	<?php if ($content !== '') : ?>
	<section class="bg-black py-[60px] min-[1280px]:py-[80px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<div class="max-w-[1138px] mx-auto news-content">
				<?= $content; ?>
			</div>
		</div>
		<style>
			.news-content h2 {
				color: #fff;
				font-size: 24px;
				font-weight: 400;
				line-height: 1.3;
				margin: 0 0 16px 0;
				padding-bottom: 24px;
				position: relative;
			}
			.news-content h2::after {
				content: '';
				position: absolute;
				left: 0;
				bottom: 0;
				width: 70px;
				height: 6px;
				background-color: #A36217;
			}
			.news-content h2:not(:first-child) {
				margin-top: 48px;
			}
			.news-content h3 {
				color: #fff;
				font-size: 20px;
				font-weight: 400;
				line-height: 1.3;
				margin: 32px 0 12px 0;
			}
			.news-content p {
				color: rgba(255, 255, 255, 0.7);
				font-size: 20px;
				font-weight: 400;
				line-height: 1.45;
				margin: 0 0 24px 0;
			}
			.news-content ul,
			.news-content ol {
				color: rgba(255, 255, 255, 0.7);
				font-size: 20px;
				line-height: 1.45;
				margin: 0 0 24px 0;
				padding-left: 24px;
			}
			.news-content li {
				margin-bottom: 8px;
			}
			.news-content a {
				color: #A36217;
				text-decoration: none;
			}
			.news-content a:hover {
				text-decoration: underline;
			}
			.news-content strong {
				color: #fff;
				font-weight: 600;
			}
		</style>
	</section>
	<?php endif; ?>

	<!-- Reusable Blocks Container -->
	<div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
		<!-- Benefits Section -->
		<?php get_template_part('template-parts/sections/benefits'); ?>

		<!-- Contact Form Section -->
		<?php get_template_part('template-parts/sections/contact-form'); ?>
	</div>
</main>

<!-- Fullscreen Gallery Modal -->
<?php if (count($galleryIds) > 0) : ?>
<div id="news-fullscreen-modal" class="fixed inset-0 z-[100] bg-black/95 hidden items-center justify-center">
	<button
		type="button"
		id="news-fullscreen-close"
		class="absolute top-4 right-4 w-12 h-12 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
		aria-label="Закрыть"
	>
		<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
		</svg>
	</button>

	<?php if (count($galleryIds) > 1) : ?>
	<button
		type="button"
		id="news-fullscreen-prev"
		class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
		aria-label="Предыдущее изображение"
	>
		<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
		</svg>
	</button>
	<button
		type="button"
		id="news-fullscreen-next"
		class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 text-white hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
		aria-label="Следующее изображение"
	>
		<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
		</svg>
	</button>
	<?php endif; ?>

	<div class="w-full h-full flex items-center justify-center p-4">
		<img
			id="news-fullscreen-image"
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
// Gallery slider script
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
	var galleryUrls = <?= wp_json_encode($galleryUrls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	var currentSlideIndex = 0;
	var currentFullscreenIndex = 0;
	var slidesPerView = 4;
	var totalSlides = galleryUrls.length;
	var maxSlideIndex = Math.max(0, totalSlides - slidesPerView);

	var track = document.getElementById('news-slider-track');
	var prevBtn = document.getElementById('news-slider-prev');
	var nextBtn = document.getElementById('news-slider-next');
	var slideItems = document.querySelectorAll('.news-slide-item');

	var fullscreenModal = document.getElementById('news-fullscreen-modal');
	var fullscreenImg = document.getElementById('news-fullscreen-image');
	var fullscreenClose = document.getElementById('news-fullscreen-close');
	var fullscreenPrev = document.getElementById('news-fullscreen-prev');
	var fullscreenNext = document.getElementById('news-fullscreen-next');

	var gap = 16; // gap-4 = 16px

	// Slider functions
	function updateSlider() {
		if (!track) return;
		var container = document.getElementById('news-slider-container');
		var containerWidth = container ? container.offsetWidth : window.innerWidth;
		var slideWidth = (containerWidth - gap * (slidesPerView - 1)) / slidesPerView;
		var offset = currentSlideIndex * (slideWidth + gap);
		track.style.transform = 'translateX(-' + offset + 'px)';
	}

	function updateSlidesPerView() {
		var width = window.innerWidth;
		if (width < 640) {
			slidesPerView = 1;
		} else if (width < 768) {
			slidesPerView = 2;
		} else if (width < 1024) {
			slidesPerView = 3;
		} else {
			slidesPerView = 4;
		}
		maxSlideIndex = Math.max(0, totalSlides - slidesPerView);
		if (currentSlideIndex > maxSlideIndex) {
			currentSlideIndex = maxSlideIndex;
		}

		// Update slide widths with gap calculation
		var container = document.getElementById('news-slider-container');
		var containerWidth = container ? container.offsetWidth : window.innerWidth;
		var slideWidth = (containerWidth - gap * (slidesPerView - 1)) / slidesPerView;
		var slides = track ? track.children : [];
		for (var i = 0; i < slides.length; i++) {
			slides[i].style.width = slideWidth + 'px';
		}

		updateSlider();
	}

	if (prevBtn) {
		prevBtn.addEventListener('click', function() {
			if (currentSlideIndex > 0) {
				currentSlideIndex--;
				updateSlider();
			}
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function() {
			if (currentSlideIndex < maxSlideIndex) {
				currentSlideIndex++;
				updateSlider();
			}
		});
	}

	// Fullscreen functions
	function openFullscreen(index) {
		if (!fullscreenModal || !fullscreenImg || index < 0 || index >= galleryUrls.length) return;
		currentFullscreenIndex = index;
		fullscreenImg.src = galleryUrls[index];
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

	function updateFullscreenImage(index) {
		if (index < 0 || index >= galleryUrls.length) return;
		currentFullscreenIndex = index;
		if (fullscreenImg) {
			fullscreenImg.src = galleryUrls[index];
		}
	}

	// Click on slide to open fullscreen
	slideItems.forEach(function(item, idx) {
		item.addEventListener('click', function() {
			openFullscreen(idx);
		});
	});

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
			var newIndex = currentFullscreenIndex - 1;
			if (newIndex < 0) newIndex = galleryUrls.length - 1;
			updateFullscreenImage(newIndex);
		});
	}

	if (fullscreenNext) {
		fullscreenNext.addEventListener('click', function(e) {
			e.stopPropagation();
			var newIndex = currentFullscreenIndex + 1;
			if (newIndex >= galleryUrls.length) newIndex = 0;
			updateFullscreenImage(newIndex);
		});
	}

	// Close on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && fullscreenModal && !fullscreenModal.classList.contains('hidden')) {
			closeFullscreen();
		}
		// Arrow keys for fullscreen navigation
		if (fullscreenModal && !fullscreenModal.classList.contains('hidden')) {
			if (e.key === 'ArrowLeft') {
				var newIndex = currentFullscreenIndex - 1;
				if (newIndex < 0) newIndex = galleryUrls.length - 1;
				updateFullscreenImage(newIndex);
			} else if (e.key === 'ArrowRight') {
				var newIndex = currentFullscreenIndex + 1;
				if (newIndex >= galleryUrls.length) newIndex = 0;
				updateFullscreenImage(newIndex);
			}
		}
	});

	// Initialize responsive slides
	window.addEventListener('resize', updateSlidesPerView);
	updateSlidesPerView();
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>

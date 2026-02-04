<?php
/**
 * Template: Проект портфолио (CPT: portfolio)
 */
get_header();

$postId = (int) get_the_ID();
$title = (string) get_the_title();

// Получаем галерею
$galleryIds = get_post_meta($postId, '_mosaic_portfolio_gallery', true);
if (!is_array($galleryIds)) {
	$galleryIds = [];
}
$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

// Первое изображение для главного слайда
$mainImageId = count($galleryIds) > 0 ? $galleryIds[0] : 0;
$mainImageUrl = $mainImageId > 0 ? (string) wp_get_attachment_image_url($mainImageId, 'full') : '';

// Категория проекта
$terms = get_the_terms($postId, 'portfolio_category');
$categoryName = '';
if (!empty($terms) && !is_wp_error($terms)) {
	$term = reset($terms);
	$categoryName = $term->name;
}
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

			<?php if ($categoryName !== '') : ?>
				<p class="text-white/60 text-lg mt-4"><?= esc_html($categoryName); ?></p>
			<?php endif; ?>

			<!-- Title Underline -->
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>
	</div>

	<!-- Project Gallery -->
	<section class="bg-black pb-[80px] min-[1280px]:pb-[100px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<?php if (count($galleryIds) > 0) : ?>
				<!-- Main Image -->
				<div class="relative mb-4">
					<div class="relative bg-gray/20 group cursor-pointer" id="gallery-main-container">
						<?php if ($mainImageUrl !== '') : ?>
							<img
								id="portfolio-main-image"
								src="<?= esc_url($mainImageUrl); ?>"
								alt="<?= esc_attr($title); ?>"
								class="block w-full max-w-[1000px] mx-auto h-auto object-contain"
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
								class="absolute left-4 top-1/2 -translate-y-1/2 text-primary hover:scale-110 hover:brightness-110 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
								aria-label="Предыдущее изображение"
								tabindex="0"
							>
								<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
								</svg>
							</button>
							<button
								type="button"
								id="gallery-next"
								class="absolute right-4 top-1/2 -translate-y-1/2 text-primary hover:scale-110 hover:brightness-110 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4"
								aria-label="Следующее изображение"
								tabindex="0"
							>
								<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
								</svg>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<!-- Thumbnails -->
				<?php if (count($galleryIds) > 1) : ?>
					<div class="flex gap-2 overflow-x-auto pb-2 justify-center">
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
			<?php else : ?>
				<div class="w-full aspect-[4/3] bg-gray/20 flex items-center justify-center max-w-[1000px] mx-auto">
					<span class="text-white/40">Нет изображений</span>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Back to Portfolio Button -->
	<section class="bg-black pb-[80px] min-[1280px]:pb-[100px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<div class="flex justify-center">
				<a
					href="<?= esc_url(home_url('/portfolio/')); ?>"
					class="inline-flex items-center justify-center border border-primary text-primary hover:bg-primary hover:text-white transition-colors h-[56px] px-10 text-base"
					tabindex="0"
					aria-label="Вернуться к портфолио"
				>
					<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
					</svg>
					Все проекты
				</a>
			</div>
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
	var mainImg = document.getElementById('portfolio-main-image');
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

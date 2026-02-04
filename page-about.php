<?php
/**
 * Template Name: About Page
 * Template: Страница О нас
 */

declare(strict_types=1);

get_header();

$data = mosaic_get_about_page();
$hero = $data['hero'];
$gallery = $data['gallery'];
$video = $data['video'];

// Get gallery images
$galleryImages = [];
foreach ($gallery['ids'] as $id) {
	$url = wp_get_attachment_image_url($id, 'large');
	if ($url) {
		$galleryImages[] = [
			'id' => $id,
			'url' => $url,
			'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: 'Si Mosaic',
		];
	}
}

// Parse video URL to get embed URL
$videoEmbedUrl = '';
if ($video['url'] !== '') {
	$videoUrl = $video['url'];
	// YouTube
	if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
		$videoEmbedUrl = 'https://www.youtube.com/embed/' . $matches[1];
	}
	// Vimeo
	elseif (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $videoUrl, $matches)) {
		$videoEmbedUrl = 'https://player.vimeo.com/video/' . $matches[1];
	}
	// Rutube
	elseif (preg_match('/rutube\.ru\/(?:video|play\/embed)\/([a-zA-Z0-9]+)/', $videoUrl, $matches)) {
		$videoEmbedUrl = 'https://rutube.ru/play/embed/' . $matches[1];
	}
}

?>

<main class="flex-grow">
	<!-- Breadcrumbs -->
	<div class="pt-[30px] min-[1280px]:pt-[40px]">
		<?php get_template_part('template-parts/breadcrumbs'); ?>
	</div>

	<!-- Hero Section -->
	<section class="bg-black py-[40px] min-[1280px]:py-[60px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<!-- Mobile: <=1279 -->
			<div class="max-[1279px]:block min-[1280px]:hidden">
				<div class="grid grid-cols-1 gap-6">
					<div>
						<h1 class="text-white text-[28px] leading-[110%] font-normal mb-0">
							<?= esc_html($hero['title']); ?>
						</h1>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<p class="text-white/70 text-base leading-[160%]">
						<?= nl2br(esc_html($hero['text'])); ?>
					</p>

					<a
						href="<?= esc_url($hero['button_url']); ?>"
						class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors w-fit"
					>
						<?= esc_html($hero['button_text']); ?>
					</a>
				</div>
			</div>

			<!-- Tablet: 1280..1919 -->
			<div class="hidden min-[1280px]:max-[1919px]:block">
				<div class="grid grid-cols-[400px_1fr] gap-12 items-start">
					<div>
						<h1 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
							<?= esc_html($hero['title']); ?>
						</h1>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<div class="space-y-6">
						<p class="text-white/70 text-[18px] leading-[160%]">
							<?= nl2br(esc_html($hero['text'])); ?>
						</p>

						<a
							href="<?= esc_url($hero['button_url']); ?>"
							class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors"
						>
							<?= esc_html($hero['button_text']); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Desktop: >=1920 -->
			<div class="hidden min-[1920px]:block">
				<div class="grid grid-cols-[500px_1fr] gap-[100px] items-start">
					<div>
						<h1 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
							<?= esc_html($hero['title']); ?>
						</h1>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<div class="space-y-6">
						<p class="text-white/70 text-[20px] leading-[160%]">
							<?= nl2br(esc_html($hero['text'])); ?>
						</p>

						<a
							href="<?= esc_url($hero['button_url']); ?>"
							class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors"
						>
							<?= esc_html($hero['button_text']); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Gallery Slider Section -->
	<?php if (count($galleryImages) > 0) : ?>
		<section class="bg-black">
			<?php if (count($galleryImages) > 1) : ?>
				<div class="about-gallery-slider relative overflow-hidden" data-about-gallery-slider>
					<div class="about-gallery-track flex transition-transform duration-700 ease-in-out" data-about-gallery-track>
						<?php foreach ($galleryImages as $img) : ?>
							<div class="about-gallery-slide flex-shrink-0 p-2" data-about-gallery-slide>
								<div class="relative overflow-hidden w-[300px] h-[300px] md:w-[395px] md:h-[395px] min-[1280px]:w-[561px] min-[1280px]:h-[561px]">
									<img
										src="<?= esc_url($img['url']); ?>"
										alt="<?= esc_attr($img['alt']); ?>"
										class="w-full h-full object-cover"
										loading="lazy"
										decoding="async"
									>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Navigation Arrows -->
					<button
						type="button"
						class="about-gallery-prev absolute left-4 min-[1280px]:left-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-primary hover:brightness-110 hover:scale-110 transition-all text-white z-10"
						aria-label="Предыдущий слайд"
						data-about-gallery-prev
					>
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
						</svg>
					</button>
					<button
						type="button"
						class="about-gallery-next absolute right-4 min-[1280px]:right-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-primary hover:brightness-110 hover:scale-110 transition-all text-white z-10"
						aria-label="Следующий слайд"
						data-about-gallery-next
					>
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
						</svg>
					</button>
				</div>
			<?php else : ?>
				<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
					<div class="relative w-[300px] h-[300px] md:w-[395px] md:h-[395px] min-[1280px]:w-[561px] min-[1280px]:h-[561px] overflow-hidden">
						<img
							src="<?= esc_url($galleryImages[0]['url']); ?>"
							alt="<?= esc_attr($galleryImages[0]['alt']); ?>"
							class="w-full h-full object-cover"
							loading="lazy"
							decoding="async"
						>
					</div>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- Video Section -->
	<?php if ($videoEmbedUrl !== '') : ?>
		<section class="bg-black py-[60px] min-[1280px]:py-[80px]">
			<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
				<!-- Mobile: <=1279 -->
				<div class="max-[1279px]:block min-[1280px]:hidden">
					<div class="grid grid-cols-1 gap-8">
						<div>
							<h2 class="text-white text-[24px] leading-[110%] font-normal mb-0 whitespace-pre-line">
								<?= esc_html($video['title']); ?>
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<div class="relative w-full aspect-video bg-gray overflow-hidden">
							<iframe
								src="<?= esc_url($videoEmbedUrl); ?>"
								class="absolute inset-0 w-full h-full"
								frameborder="0"
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
								allowfullscreen
								loading="lazy"
							></iframe>
						</div>
					</div>
				</div>

				<!-- Tablet: 1280..1919 -->
				<div class="hidden min-[1280px]:max-[1919px]:block">
					<div class="grid grid-cols-[300px_1fr] gap-12 items-start">
						<div>
							<h2 class="text-white font-century text-[40px] leading-[110%] font-normal mb-0 whitespace-pre-line">
								<?= esc_html($video['title']); ?>
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<div class="relative w-full aspect-video bg-gray overflow-hidden">
							<iframe
								src="<?= esc_url($videoEmbedUrl); ?>"
								class="absolute inset-0 w-full h-full"
								frameborder="0"
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
								allowfullscreen
								loading="lazy"
							></iframe>
						</div>
					</div>
				</div>

				<!-- Desktop: >=1920 -->
				<div class="hidden min-[1920px]:block">
					<div class="grid grid-cols-[400px_1fr] gap-[100px] items-start">
						<div>
							<h2 class="text-white font-century text-[48px] leading-[110%] font-normal mb-0 whitespace-pre-line">
								<?= esc_html($video['title']); ?>
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<div class="relative w-full aspect-video bg-gray overflow-hidden">
							<iframe
								src="<?= esc_url($videoEmbedUrl); ?>"
								class="absolute inset-0 w-full h-full"
								frameborder="0"
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
								allowfullscreen
								loading="lazy"
							></iframe>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Reusable Blocks Container -->
	<div>
		<!-- Benefits Section -->
		<div class="pb-[60px] min-[1280px]:pb-[80px]">
			<?php get_template_part('template-parts/sections/benefits'); ?>
		</div>

		<!-- Contact Form Section -->
		<?php get_template_part('template-parts/sections/contact-form'); ?>

		<!-- Requisites Section -->
		<?php get_template_part('template-parts/requisites'); ?>
	</div>
</main>

<script>
(function() {
	// About Gallery Slider
	const slider = document.querySelector('[data-about-gallery-slider]');
	if (!slider) return;

	const track = slider.querySelector('[data-about-gallery-track]');
	const slides = slider.querySelectorAll('[data-about-gallery-slide]');
	const prevBtn = slider.querySelector('[data-about-gallery-prev]');
	const nextBtn = slider.querySelector('[data-about-gallery-next]');

	if (!track || slides.length <= 1) return;

	let currentIndex = 0;
	let slidesPerView = getSlidesPerView();

	function getSlidesPerView() {
		const containerWidth = slider.offsetWidth;
		let slideWidth = 300; // mobile
		if (window.innerWidth >= 1280) slideWidth = 561;
		else if (window.innerWidth >= 768) slideWidth = 395;

		// Calculate how many slides fit in the container (including padding)
		const slideWithPadding = slideWidth + 16; // 16px = p-2 padding
		return Math.floor(containerWidth / slideWithPadding);
	}

	function updateSlider() {
		const slideWidth = slides[0].offsetWidth;
		track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
	}

	function goNext() {
		const maxIndex = Math.max(0, slides.length - slidesPerView);
		currentIndex = Math.min(currentIndex + 1, maxIndex);
		updateSlider();
	}

	function goPrev() {
		currentIndex = Math.max(currentIndex - 1, 0);
		updateSlider();
	}

	if (prevBtn) prevBtn.addEventListener('click', goPrev);
	if (nextBtn) nextBtn.addEventListener('click', goNext);

	window.addEventListener('resize', function() {
		slidesPerView = getSlidesPerView();
		currentIndex = Math.min(currentIndex, Math.max(0, slides.length - slidesPerView));
		updateSlider();
	});
})();
</script>

<?php get_footer(); ?>

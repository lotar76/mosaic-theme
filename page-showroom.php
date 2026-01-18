<?php
/**
 * Template Name: Showroom Page
 * Template: Страница Шоурум
 */

declare(strict_types=1);

get_header();

$data = mosaic_get_showroom_page();
$hero = $data['hero'];
$blocks = $data['blocks'];
$collections = $data['collections'];
$events = $data['events'];
$map = $data['map'];

// Site settings for contact form
$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
$phoneContact = function_exists('mosaic_get_phone_contact') ? mosaic_get_phone_contact() : ['display' => '+7 (928) 206-07-75', 'href' => 'tel:+79282060775'];
$email = is_array($siteSettings) ? (string) ($siteSettings['email'] ?? 'si.mosaic@yandex.ru') : 'si.mosaic@yandex.ru';
$address = is_array($siteSettings) ? trim((string) ($siteSettings['address'] ?? '')) : '';
$address = $address !== '' ? $address : 'Краснодар, Селезнёва 204';
$workHours = is_array($siteSettings) ? trim((string) ($siteSettings['work_hours'] ?? '')) : '';
$workHours = $workHours !== '' ? $workHours : 'Пн - Пт: 09:00 - 18:00';

// Parse title lines
$titleLines = preg_split("/\r\n|\r|\n/", trim($hero['title'])) ?: [];
$titleLines = array_values(array_filter(array_map('trim', $titleLines), static fn($v) => $v !== ''));
$heroTitleHtml = implode('<br>', array_map('esc_html', $titleLines));

// Get gallery images
$galleryImages = [];
foreach ($hero['gallery_ids'] as $id) {
	$url = wp_get_attachment_image_url($id, 'full');
	if ($url) {
		$galleryImages[] = [
			'id' => $id,
			'url' => $url,
			'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: 'Шоурум Si Mosaic',
		];
	}
}

// Fallback image if no gallery
if (count($galleryImages) === 0) {
	$galleryImages[] = [
		'id' => 0,
		'url' => get_template_directory_uri() . '/img/shaurum.png',
		'alt' => 'Шоурум Si Mosaic',
	];
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
				<div class="grid grid-cols-1 gap-8">
					<!-- Title and Features -->
					<div class="space-y-6">
						<div>
							<h1 class="text-white text-[28px] leading-[110%] font-normal mb-0">
								<?= $heroTitleHtml; ?>
							</h1>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<!-- Features List -->
						<ul class="space-y-3 text-white text-base">
							<?php foreach ($hero['features'] as $feature) : ?>
								<li class="flex items-start gap-3">
									<span class="text-primary mt-1 flex-shrink-0">&#9670;</span>
									<span><?= esc_html($feature); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<!-- Tablet: 1280..1919 -->
			<div class="hidden min-[1280px]:max-[1919px]:block">
				<div class="grid grid-cols-[560px_1fr] gap-8">
					<!-- Left: Title -->
					<div class="flex flex-col gap-6">
						<div>
							<h1 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
								<?= $heroTitleHtml; ?>
							</h1>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>
					</div>

					<!-- Right: Features List -->
					<div class="flex items-start justify-end">
						<ul class="space-y-5 text-white font-century font-normal text-[22px] leading-[145%]">
							<?php foreach ($hero['features'] as $feature) : ?>
								<li class="flex items-start gap-4">
									<span class="text-primary mt-1 flex-shrink-0">&#9670;</span>
									<span><?= esc_html($feature); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<!-- Desktop: >=1920 -->
			<div class="hidden min-[1920px]:block">
				<div class="grid grid-cols-[848px_1fr] gap-8">
					<!-- Left: Title -->
					<div class="flex flex-col gap-6">
						<div>
							<h1 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
								<?= $heroTitleHtml; ?>
							</h1>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>
					</div>

					<!-- Right: Features List -->
					<div class="flex items-start justify-end">
						<ul class="space-y-5 text-white font-century font-normal text-[20px] leading-[145%]">
							<?php foreach ($hero['features'] as $feature) : ?>
								<li class="flex items-start gap-4">
									<span class="text-primary mt-1 flex-shrink-0">&#9670;</span>
									<span><?= esc_html($feature); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<!-- Full-width Gallery Slider -->
		<div class="mt-8 min-[1280px]:mt-12">
			<?php if (count($galleryImages) > 1) : ?>
				<div class="showroom-hero-slider relative overflow-hidden" data-showroom-hero-slider>
					<div class="showroom-hero-track flex transition-transform duration-700 ease-in-out" data-showroom-hero-track>
						<?php foreach ($galleryImages as $img) : ?>
							<div class="showroom-hero-slide flex-shrink-0 w-full" data-showroom-hero-slide>
								<div class="relative w-full h-[400px] max-[1279px]:h-[300px] min-[1920px]:h-[600px]">
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
						class="showroom-hero-prev absolute left-4 min-[1280px]:left-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-black/50 hover:bg-primary transition-colors text-white z-10"
						aria-label="Предыдущий слайд"
						data-showroom-hero-prev
					>
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
						</svg>
					</button>
					<button
						type="button"
						class="showroom-hero-next absolute right-4 min-[1280px]:right-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-black/50 hover:bg-primary transition-colors text-white z-10"
						aria-label="Следующий слайд"
						data-showroom-hero-next
					>
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
						</svg>
					</button>
				</div>
			<?php else : ?>
				<div class="relative w-full h-[400px] max-[1279px]:h-[300px] min-[1920px]:h-[600px]">
					<img
						src="<?= esc_url($galleryImages[0]['url']); ?>"
						alt="<?= esc_attr($galleryImages[0]['alt']); ?>"
						class="w-full h-full object-cover"
						loading="lazy"
						decoding="async"
					>
				</div>
			<?php endif; ?>
		</div>

		<!-- Description and Button -->
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px] mt-8 min-[1280px]:mt-12">
			<div class="flex flex-col min-[1280px]:flex-row min-[1280px]:items-center min-[1280px]:justify-between gap-6">
				<p class="text-white/70 text-base min-[1280px]:text-lg max-w-[800px] min-[1280px]:max-w-[1000px]">
					<?= nl2br(esc_html($hero['description'])); ?>
				</p>

				<button
					type="button"
					class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors flex-shrink-0"
					tabindex="0"
					aria-label="<?= esc_attr($hero['button_text']); ?>"
					data-modal-open="modal-showroom"
				>
					<?= esc_html($hero['button_text']); ?>
				</button>
			</div>
		</div>
	</section>

	<!-- Content Blocks (alternating image + text) -->
	<?php if (count($blocks) > 0) : ?>
		<section class="bg-black py-[60px] min-[1280px]:py-[80px]">
			<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
				<div class="space-y-[60px] min-[1280px]:space-y-[100px]">
					<?php foreach ($blocks as $index => $block) :
						$imageUrl = '';
						if ($block['image_id'] > 0) {
							$imageUrl = (string) wp_get_attachment_image_url($block['image_id'], 'large');
						}
						if ($imageUrl === '') {
							$imageUrl = get_template_directory_uri() . '/img/shaurum.png';
						}

						$isImageLeft = $block['position'] === 'left';
						$textParagraphs = array_filter(array_map('trim', preg_split("/\r\n\r\n|\n\n/", trim($block['text'])) ?: []));
					?>
						<!-- Mobile: <=1279 -->
						<div class="max-[1279px]:block min-[1280px]:hidden">
							<div class="space-y-6">
								<!-- Image -->
								<div class="w-full h-[300px] overflow-hidden">
									<img
										src="<?= esc_url($imageUrl); ?>"
										alt="<?= esc_attr($block['title']); ?>"
										class="w-full h-full object-cover"
										loading="lazy"
										decoding="async"
									>
								</div>

								<!-- Text -->
								<div class="space-y-4">
									<h2 class="text-white text-[24px] leading-[110%] font-normal">
										<?= esc_html($block['title']); ?>
									</h2>
									<div class="space-y-4 text-white/70 text-base leading-[160%]">
										<?php foreach ($textParagraphs as $p) : ?>
											<p><?= esc_html($p); ?></p>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Tablet: 1280..1919 -->
						<div class="hidden min-[1280px]:max-[1919px]:block">
							<div class="grid grid-cols-2 gap-12 items-center <?= $isImageLeft ? '' : 'direction-rtl'; ?>">
								<!-- Image -->
								<div class="w-full h-[450px] overflow-hidden <?= $isImageLeft ? '' : 'order-2'; ?>">
									<img
										src="<?= esc_url($imageUrl); ?>"
										alt="<?= esc_attr($block['title']); ?>"
										class="w-full h-full object-cover"
										loading="lazy"
										decoding="async"
									>
								</div>

								<!-- Text -->
								<div class="space-y-6 <?= $isImageLeft ? '' : 'order-1'; ?>">
									<h2 class="text-white font-century text-[40px] leading-[110%] font-normal">
										<?= esc_html($block['title']); ?>
									</h2>
									<div class="space-y-4 text-white/70 text-[18px] leading-[160%]">
										<?php foreach ($textParagraphs as $p) : ?>
											<p><?= esc_html($p); ?></p>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Desktop: >=1920 -->
						<div class="hidden min-[1920px]:block">
							<div class="grid grid-cols-2 gap-[100px] items-center">
								<!-- Image -->
								<div class="w-full h-[550px] overflow-hidden <?= $isImageLeft ? '' : 'order-2'; ?>">
									<img
										src="<?= esc_url($imageUrl); ?>"
										alt="<?= esc_attr($block['title']); ?>"
										class="w-full h-full object-cover"
										loading="lazy"
										decoding="async"
									>
								</div>

								<!-- Text -->
								<div class="space-y-6 <?= $isImageLeft ? '' : 'order-1'; ?>">
									<h2 class="text-white font-century text-[48px] leading-[110%] font-normal">
										<?= esc_html($block['title']); ?>
									</h2>
									<div class="space-y-4 text-white/70 text-[20px] leading-[160%]">
										<?php foreach ($textParagraphs as $p) : ?>
											<p><?= esc_html($p); ?></p>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Collections Section -->
	<?php if (count($collections['items']) > 0) : ?>
		<section class="bg-black py-[60px] min-[1280px]:py-[80px]" data-showroom-collections>
			<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
				<!-- Section Header -->
				<div class="flex items-center justify-between mb-8 min-[1280px]:mb-12">
					<div>
						<h2 class="text-white text-[24px] min-[1280px]:text-[40px] min-[1920px]:text-[48px] font-normal mb-0">
							<?= esc_html($collections['title']); ?>
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<?php if (count($collections['items']) > 4) : ?>
						<!-- Navigation Arrows (show if more than 4 items) -->
						<div class="flex gap-[37px] max-[1279px]:hidden">
							<button
								type="button"
								class="collections-prev p-2 text-white/60 hover:text-primary transition-colors"
								aria-label="Предыдущий слайд"
								data-collections-prev
							>
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
								</svg>
							</button>
							<button
								type="button"
								class="collections-next p-2 text-white/60 hover:text-primary transition-colors"
								aria-label="Следующий слайд"
								data-collections-next
							>
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
								</svg>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<!-- Collections Grid/Slider -->
				<?php $useSlider = count($collections['items']) > 4; ?>
				<?php if ($useSlider) : ?>
					<div class="collections-slider overflow-hidden" data-collections-slider>
						<div class="collections-track flex gap-6 transition-transform duration-700 ease-in-out" data-collections-track>
							<?php foreach ($collections['items'] as $item) :
								$itemImageUrl = '';
								if ($item['image_id'] > 0) {
									$itemImageUrl = (string) wp_get_attachment_image_url($item['image_id'], 'medium_large');
								}
								if ($itemImageUrl === '') {
									$itemImageUrl = get_template_directory_uri() . '/img/placeholder.jpg';
								}
							?>
								<a
									href="<?= esc_url($item['url'] ?: '#'); ?>"
									class="collections-slide group flex-shrink-0 w-[calc(25%-18px)] max-[1279px]:w-[calc(50%-12px)]"
									data-collections-slide
								>
									<div class="aspect-[4/5] overflow-hidden bg-gray mb-4">
										<img
											src="<?= esc_url($itemImageUrl); ?>"
											alt="<?= esc_attr($item['title']); ?>"
											class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
											loading="lazy"
											decoding="async"
										>
									</div>
									<div class="flex items-center justify-between text-white">
										<span class="text-base min-[1280px]:text-lg"><?= esc_html($item['title']); ?></span>
										<svg class="w-5 h-5 text-white/60 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
										</svg>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php else : ?>
					<div class="grid grid-cols-2 min-[1280px]:grid-cols-4 gap-6">
						<?php foreach ($collections['items'] as $item) :
							$itemImageUrl = '';
							if ($item['image_id'] > 0) {
								$itemImageUrl = (string) wp_get_attachment_image_url($item['image_id'], 'medium_large');
							}
							if ($itemImageUrl === '') {
								$itemImageUrl = get_template_directory_uri() . '/img/placeholder.jpg';
							}
						?>
							<a
								href="<?= esc_url($item['url'] ?: '#'); ?>"
								class="group"
							>
								<div class="aspect-[4/5] overflow-hidden bg-gray mb-4">
									<img
										src="<?= esc_url($itemImageUrl); ?>"
										alt="<?= esc_attr($item['title']); ?>"
										class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
										loading="lazy"
										decoding="async"
									>
								</div>
								<div class="flex items-center justify-between text-white">
									<span class="text-base min-[1280px]:text-lg"><?= esc_html($item['title']); ?></span>
									<svg class="w-5 h-5 text-white/60 group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
									</svg>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Events Section -->
	<?php if (count($events['items']) > 0) : ?>
		<section class="bg-black py-[60px] min-[1280px]:py-[80px]" data-showroom-events>
			<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
				<!-- Section Header -->
				<div class="flex items-center justify-between mb-8 min-[1280px]:mb-12">
					<div>
						<h2 class="text-white text-[24px] min-[1280px]:text-[40px] min-[1920px]:text-[48px] font-normal mb-0">
							<?= esc_html($events['title']); ?>
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<?php if (count($events['items']) > 4) : ?>
						<!-- Navigation Arrows -->
						<div class="flex gap-[37px] max-[1279px]:hidden">
							<button
								type="button"
								class="events-prev p-2 text-white/60 hover:text-primary transition-colors"
								aria-label="Предыдущий слайд"
								data-events-prev
							>
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
								</svg>
							</button>
							<button
								type="button"
								class="events-next p-2 text-white/60 hover:text-primary transition-colors"
								aria-label="Следующий слайд"
								data-events-next
							>
								<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
								</svg>
							</button>
						</div>
					<?php endif; ?>
				</div>

				<!-- Events Grid/Slider -->
				<?php $useEventsSlider = count($events['items']) > 4; ?>
				<?php if ($useEventsSlider) : ?>
					<div class="events-slider overflow-hidden" data-events-slider>
						<div class="events-track flex gap-6 transition-transform duration-700 ease-in-out" data-events-track>
							<?php foreach ($events['items'] as $item) :
								$itemImageUrl = '';
								if ($item['image_id'] > 0) {
									$itemImageUrl = (string) wp_get_attachment_image_url($item['image_id'], 'medium_large');
								}
								if ($itemImageUrl === '') {
									$itemImageUrl = get_template_directory_uri() . '/img/placeholder.jpg';
								}
							?>
								<a
									href="<?= esc_url($item['url'] ?: '#'); ?>"
									class="events-slide group flex-shrink-0 w-[calc(25%-18px)] max-[1279px]:w-[calc(50%-12px)]"
									data-events-slide
								>
									<div class="aspect-[4/3] overflow-hidden bg-gray mb-4">
										<img
											src="<?= esc_url($itemImageUrl); ?>"
											alt="<?= esc_attr($item['title']); ?>"
											class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
											loading="lazy"
											decoding="async"
										>
									</div>
									<span class="text-white text-base min-[1280px]:text-lg"><?= esc_html($item['title']); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php else : ?>
					<div class="grid grid-cols-2 min-[1280px]:grid-cols-4 gap-6">
						<?php foreach ($events['items'] as $item) :
							$itemImageUrl = '';
							if ($item['image_id'] > 0) {
								$itemImageUrl = (string) wp_get_attachment_image_url($item['image_id'], 'medium_large');
							}
							if ($itemImageUrl === '') {
								$itemImageUrl = get_template_directory_uri() . '/img/placeholder.jpg';
							}
						?>
							<a
								href="<?= esc_url($item['url'] ?: '#'); ?>"
								class="group"
							>
								<div class="aspect-[4/3] overflow-hidden bg-gray mb-4">
									<img
										src="<?= esc_url($itemImageUrl); ?>"
										alt="<?= esc_attr($item['title']); ?>"
										class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
										loading="lazy"
										decoding="async"
									>
								</div>
								<span class="text-white text-base min-[1280px]:text-lg"><?= esc_html($item['title']); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Contact Form CTA Section -->
	<section class="bg-gray py-[80px] min-[1280px]:py-[100px]" id="contact-form">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<div class="flex flex-col min-[1280px]:flex-row items-start min-[1280px]:items-center justify-between gap-8">
				<!-- Left: Title and Info -->
				<div class="space-y-6">
					<div>
						<h2 class="text-white font-century font-normal text-[28px] min-[1280px]:text-[56px] leading-[110%] min-[1280px]:leading-[100%] tracking-[-0.01em] mb-0">
							Записаться в шоурум
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<div class="space-y-4 text-white font-century font-normal text-[16px] min-[1280px]:text-[20px] leading-[145%]">
						<div><?= esc_html($address); ?></div>
						<div><?= esc_html($workHours); ?></div>
						<div>
							<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors">
								<?= esc_html((string) $phoneContact['display']); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- Right: CTA Button -->
				<button
					type="button"
					class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors flex-shrink-0"
					data-modal-open="modal-showroom"
					aria-label="Записаться в шоурум"
				>
					Записаться в шоурум
				</button>
			</div>
		</div>
	</section>

	<!-- Map Section -->
	<?php if ($map['latitude'] !== '' && $map['longitude'] !== '') : ?>
		<section class="bg-black">
			<div id="showroom-map" class="w-full h-[400px] min-[1280px]:h-[500px] min-[1920px]:h-[600px]" data-lat="<?= esc_attr($map['latitude']); ?>" data-lng="<?= esc_attr($map['longitude']); ?>" data-zoom="<?= esc_attr((string) $map['zoom']); ?>"></div>
		</section>
	<?php endif; ?>
</main>

<?php get_footer(); ?>

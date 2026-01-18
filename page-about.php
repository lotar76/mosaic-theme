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

// Site settings for contact form
$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
$phoneContact = function_exists('mosaic_get_phone_contact') ? mosaic_get_phone_contact() : ['display' => '+7 (928) 206-07-75', 'href' => 'tel:+79282060775'];
$email = is_array($siteSettings) ? (string) ($siteSettings['email'] ?? 'si.mosaic@yandex.ru') : 'si.mosaic@yandex.ru';
$address = is_array($siteSettings) ? trim((string) ($siteSettings['address'] ?? '')) : '';
$address = $address !== '' ? $address : 'Краснодар, Селезнёва 204';
$workHours = is_array($siteSettings) ? trim((string) ($siteSettings['work_hours'] ?? '')) : '';
$workHours = $workHours !== '' ? $workHours : 'Пн - Пт: 09:00 - 18:00';

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
}

// Social links
$socialLinks = [];
if (function_exists('mosaic_get_site_settings')) {
	$settings = mosaic_get_site_settings();
	$socialLinks = [
		'whatsapp' => $settings['social_whatsapp'] ?? '',
		'vk' => $settings['social_vk'] ?? '',
		'telegram' => $settings['social_telegram'] ?? '',
		'youtube' => $settings['social_youtube'] ?? '',
		'pinterest' => $settings['social_pinterest'] ?? '',
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
							<div class="about-gallery-slide flex-shrink-0 w-full md:w-1/2 lg:w-1/3 xl:w-1/4 p-2" data-about-gallery-slide>
								<div class="relative w-full aspect-[4/3] overflow-hidden">
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
						class="about-gallery-prev absolute left-4 min-[1280px]:left-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-black/50 hover:bg-primary transition-colors text-white z-10"
						aria-label="Предыдущий слайд"
						data-about-gallery-prev
					>
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
						</svg>
					</button>
					<button
						type="button"
						class="about-gallery-next absolute right-4 min-[1280px]:right-8 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-black/50 hover:bg-primary transition-colors text-white z-10"
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
					<div class="relative w-full aspect-[16/9] max-h-[600px] overflow-hidden">
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

	<!-- Benefits Section -->
	<?php get_template_part('template-parts/sections/benefits'); ?>

	<!-- Contact Form Section -->
	<section class="bg-gray py-[80px] min-[1280px]:py-[100px]" id="contact-form">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<!-- Mobile: <=1279 -->
			<div class="max-[1279px]:block min-[1280px]:hidden">
				<div class="grid grid-cols-1 gap-8">
					<!-- Left Column: Contact Info -->
					<div class="space-y-8">
						<!-- Title -->
						<div>
							<h2 class="text-white font-century font-normal text-[28px] leading-[110%] tracking-[-0.01em] mb-0">
								Давайте обсудим ваш проект
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<!-- Social Icons -->
						<div class="flex items-center gap-4">
							<?php if (!empty($socialLinks['whatsapp'])) : ?>
								<a href="<?= esc_url($socialLinks['whatsapp']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="WhatsApp">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['vk'])) : ?>
								<a href="<?= esc_url($socialLinks['vk']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="VK">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.21c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['telegram'])) : ?>
								<a href="<?= esc_url($socialLinks['telegram']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="Telegram">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['youtube'])) : ?>
								<a href="<?= esc_url($socialLinks['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="YouTube">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['pinterest'])) : ?>
								<a href="<?= esc_url($socialLinks['pinterest']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="Pinterest">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>
								</a>
							<?php endif; ?>
						</div>

						<!-- Contact Details -->
						<div class="space-y-3 text-white text-[16px] leading-[145%]">
							<div>
								<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors">
									<?= esc_html((string) $phoneContact['display']); ?>
								</a>
							</div>
							<div>
								<a href="mailto:<?= esc_attr($email); ?>" class="hover:text-primary transition-colors">
									<?= esc_html($email); ?>
								</a>
							</div>
							<div><?= esc_html($address); ?></div>
							<div><?= esc_html($workHours); ?></div>
						</div>
					</div>

					<!-- Right Column: Contact Form -->
					<div>
						<form class="flex flex-col gap-4" method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="contact_form">
							<input type="hidden" name="form_source" value="about">
							<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

							<div>
								<input
									type="text"
									name="name"
									placeholder="Имя"
									required
									class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								>
							</div>

							<div>
								<input
									type="email"
									name="email"
									placeholder="Почта"
									required
									class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								>
							</div>

							<div>
								<input
									type="tel"
									name="phone"
									placeholder="Телефон"
									required
									class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								>
							</div>

							<button
								type="submit"
								class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors"
							>
								Отправить заявку
							</button>

							<p class="text-white/40 text-xs text-left">
								Согласен с обработкой персональных данных
							</p>
						</form>
					</div>
				</div>
			</div>

			<!-- Tablet: 1280..1919 -->
			<div class="hidden min-[1280px]:max-[1919px]:block">
				<div class="flex items-start justify-between">
					<!-- Left -->
					<div class="w-[500px] flex flex-col">
						<h2 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
							Давайте обсудим ваш проект
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>

						<!-- Social Icons -->
						<div class="mt-8 flex items-center gap-4">
							<?php if (!empty($socialLinks['whatsapp'])) : ?>
								<a href="<?= esc_url($socialLinks['whatsapp']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="WhatsApp">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['vk'])) : ?>
								<a href="<?= esc_url($socialLinks['vk']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="VK">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.21c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['telegram'])) : ?>
								<a href="<?= esc_url($socialLinks['telegram']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="Telegram">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['youtube'])) : ?>
								<a href="<?= esc_url($socialLinks['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="YouTube">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['pinterest'])) : ?>
								<a href="<?= esc_url($socialLinks['pinterest']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="Pinterest">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>
								</a>
							<?php endif; ?>
						</div>

						<div class="mt-8 space-y-[20px] text-white font-century font-normal text-[18px] leading-[145%]">
							<div>
								<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors">
									<?= esc_html((string) $phoneContact['display']); ?>
								</a>
							</div>
							<div>
								<a href="mailto:<?= esc_attr($email); ?>" class="hover:text-primary transition-colors">
									<?= esc_html($email); ?>
								</a>
							</div>
							<div><?= esc_html($address); ?></div>
							<div><?= esc_html($workHours); ?></div>
						</div>
					</div>

					<!-- Right -->
					<div class="w-[593px]">
						<form method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="contact_form">
							<input type="hidden" name="form_source" value="about">
							<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

							<div class="flex flex-col gap-5">
								<div>
									<input
										type="text"
										name="name"
										placeholder="Имя"
										required
										class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									>
								</div>

								<div>
									<input
										type="email"
										name="email"
										placeholder="Почта"
										required
										class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									>
								</div>

								<div>
									<input
										type="tel"
										name="phone"
										placeholder="Телефон"
										required
										class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									>
								</div>

								<button
									type="submit"
									class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors"
								>
									Отправить заявку
								</button>

								<p class="text-white/40 text-xs text-left">
									Согласен с обработкой персональных данных
								</p>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- Desktop: >=1920 -->
			<div class="hidden min-[1920px]:block">
				<div class="flex items-start gap-[121px]">
					<!-- Left -->
					<div class="w-[700px] flex flex-col">
						<h2 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
							Давайте обсудим ваш проект
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>

						<!-- Social Icons -->
						<div class="mt-8 flex items-center gap-4">
							<?php if (!empty($socialLinks['whatsapp'])) : ?>
								<a href="<?= esc_url($socialLinks['whatsapp']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="WhatsApp">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['vk'])) : ?>
								<a href="<?= esc_url($socialLinks['vk']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="VK">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.21c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['telegram'])) : ?>
								<a href="<?= esc_url($socialLinks['telegram']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="Telegram">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['youtube'])) : ?>
								<a href="<?= esc_url($socialLinks['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="YouTube">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($socialLinks['pinterest'])) : ?>
								<a href="<?= esc_url($socialLinks['pinterest']); ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center text-white hover:text-primary transition-colors" aria-label="Pinterest">
									<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>
								</a>
							<?php endif; ?>
						</div>

						<div class="mt-8 space-y-[20px] text-white font-century font-normal text-[20px] leading-[145%]">
							<div>
								<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors">
									<?= esc_html((string) $phoneContact['display']); ?>
								</a>
							</div>
							<div>
								<a href="mailto:<?= esc_attr($email); ?>" class="hover:text-primary transition-colors">
									<?= esc_html($email); ?>
								</a>
							</div>
							<div><?= esc_html($address); ?></div>
							<div><?= esc_html($workHours); ?></div>
						</div>
					</div>

					<!-- Right -->
					<div class="w-[658px]">
						<form method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="contact_form">
							<input type="hidden" name="form_source" value="about">
							<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

							<div class="flex flex-col gap-5">
								<div>
									<input
										type="text"
										name="name"
										placeholder="Имя"
										required
										class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									>
								</div>

								<div>
									<input
										type="email"
										name="email"
										placeholder="Почта"
										required
										class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									>
								</div>

								<div>
									<input
										type="tel"
										name="phone"
										placeholder="Телефон"
										required
										class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									>
								</div>

								<button
									type="submit"
									class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors"
								>
									Отправить заявку
								</button>

								<p class="text-white/40 text-xs text-left">
									Согласен с обработкой персональных данных
								</p>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Requisites Section -->
	<?php get_template_part('template-parts/requisites'); ?>
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
		if (window.innerWidth >= 1280) return 4;
		if (window.innerWidth >= 1024) return 3;
		if (window.innerWidth >= 768) return 2;
		return 1;
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

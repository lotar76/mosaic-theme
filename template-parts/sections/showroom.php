<?php
/**
 * Template Part: Showroom Section
 * Переиспользуемый блок "Шоурум"
 */

declare(strict_types=1);
?>

<!-- Showroom Section -->
<section class="bg-black">
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
		<?php
		// Получаем настройки шоурума из админки
		$showroomData = mosaic_get_showroom_page();
		$homepageImageId = $showroomData['homepage_image_id'] ?? 0;
		$features = $showroomData['hero']['features'] ?? [];

		// Если картинка выбрана в админке, используем её, иначе fallback на статическую
		if ($homepageImageId > 0) {
			$showroomImageUrl = wp_get_attachment_image_url($homepageImageId, 'full');
		}
		if (empty($showroomImageUrl)) {
			$showroomImageUrl = get_template_directory_uri() . '/img/shaurum.png';
		}
		?>

		<!-- Mobile: <=1279 -->
		<div class="max-[1279px]:block min-[1280px]:hidden">
			<!-- Top Section: Title, Button, and List -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-8 lg:mb-12">
				<!-- Left: Title and Button -->
				<div class="space-y-6">
					<div>
						<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal leading-tight mb-0">
							Приглашаем в шоурум в Краснодаре
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>
					<a
						href="#" data-modal-open="modal-showroom"
						class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors"
						tabindex="0"
						aria-label="Записаться на посещение шоурума"
					>
						Форма записи
					</a>
				</div>

				<!-- Right: Features List -->
				<div class="flex items-start">
					<ul class="space-y-3 text-white text-base md:text-lg">
						<?php foreach ($features as $feature): ?>
							<li class="flex items-start gap-3">
								<span class="text-primary mt-1 flex-shrink-0">◆</span>
								<span><?= esc_html($feature); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<!-- Bottom Section: Showroom Image -->
			<div class="w-full h-[400px] md:h-[500px] lg:h-[600px] overflow-hidden">
				<img
					src="<?= esc_url($showroomImageUrl); ?>"
					alt="Шоурум Si Mosaic в Краснодаре"
					class="w-full h-full object-cover"
					loading="lazy"
					decoding="async"
				>
			</div>
		</div>

		<!-- Tablet: 1280..1919 -->
		<div class="hidden min-[1280px]:max-[1919px]:block">
			<div class="w-[1218px] h-[1038px] mx-auto grid grid-rows-[342px_696px]">
				<div class="grid grid-cols-[560px_658px] gap-0">
					<!-- Left -->
					<div class="flex flex-col gap-6">
						<div>
							<h2 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
								Приглашаем в шоурум<br>в Краснодаре
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<a
							href="#" data-modal-open="modal-showroom"
							class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors w-fit"
							tabindex="0"
							aria-label="Записаться на посещение шоурума"
						>
							Форма записи
						</a>
					</div>

					<!-- Right -->
					<div class="flex items-start justify-end justify-self-end w-[658px]">
						<ul class="space-y-5 text-white font-century font-normal text-[22px] leading-[145%] tracking-[0] text-left">
							<?php foreach ($features as $feature): ?>
								<li class="flex items-start gap-4">
									<span class="text-primary mt-1 flex-shrink-0">◆</span>
									<span><?= esc_html($feature); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>

				<div class="mt-10 overflow-hidden">
					<img
						src="<?= esc_url($showroomImageUrl); ?>"
						alt="Шоурум Si Mosaic в Краснодаре"
						class="w-[1219px] h-[696px] object-cover"
						loading="lazy"
						decoding="async"
					>
				</div>
			</div>
		</div>

		<!-- Desktop (>=1920): fixed 1772x1111 -->
		<div class="hidden min-[1920px]:block">
			<div class="w-[1772px] h-[1111px] mx-auto grid grid-rows-[auto,1fr]">
				<div class="w-full px-[111px] grid grid-cols-[848px_702px] gap-0">
					<!-- Left -->
					<div class="flex flex-col gap-6">
						<div>
							<h2 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
								Приглашаем в шоурум<br>в Краснодаре
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<a
							href="#" data-modal-open="modal-showroom"
							class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white h-[56px] px-8 text-base transition-colors w-fit"
							tabindex="0"
							aria-label="Записаться на посещение шоурума"
						>
							Форма записи
						</a>
					</div>

					<!-- Right -->
					<div class="flex items-start justify-end justify-self-end w-[702px]">
						<ul class="space-y-5 text-white font-century font-normal text-[20px] leading-[145%] tracking-[0] text-left">
							<?php foreach ($features as $feature): ?>
								<li class="flex items-start gap-4">
									<span class="text-primary mt-1 flex-shrink-0">◆</span>
									<span><?= esc_html($feature); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>

				<div class="mt-10 overflow-hidden">
					<img
						src="<?= esc_url($showroomImageUrl); ?>"
						alt="Шоурум Si Mosaic в Краснодаре"
						class="w-full h-full object-cover"
						loading="lazy"
						decoding="async"
					>
				</div>
			</div>
		</div>
	</div>
</section>


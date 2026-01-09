<?php
/**
 * Template Part: Founders Section
 * Переиспользуемый блок "Основатели"
 */

declare(strict_types=1);
?>

<!-- Founders Section -->
<section class="bg-black">
	<div class="max-w-[1920px] mx-auto">
		<div class="grid grid-cols-1 lg:grid-cols-2">
			<!-- Left: Founders Info -->
			<div class="flex flex-col justify-center bg-gray px-6 md:px-12 lg:px-16 py-12 lg:py-20 order-2 lg:order-1">
				<h2 class="text-white text-2xl md:text-3xl lg:text-4xl font-normal mb-2">
					Алексей и Светлана Исаевы
				</h2>
				<p class="text-white/60 text-lg md:text-xl mb-0">Основатели компании Si Mosaic</p>
				<div class="w-[70px] h-[6px] bg-primary mt-6 mb-8"></div>

				<p class="text-white/80 text-base md:text-lg leading-relaxed">
					Мы верим, что искусство должно быть живым и может передавать эмоции. Каждый проект имеет свою историю, наполненную смыслом. Мы имеем свой уникальный почерк и это отражается в наших работах. Разработали собственную технологию обучения мастеров, благодаря которой команда работает в едином стиле и качестве. Каждый проект проходит через наш личный контроль: от идеи и художественного замысла до финального исполнения.
				</p>
			</div>

			<!-- Right: Founders Photo -->
			<div class="h-[400px] lg:h-[500px] overflow-hidden order-1 lg:order-2">
				<img
					src="<?= get_template_directory_uri(); ?>/img/13.png"
					alt="Алексей и Светлана Исаевы - основатели Si Mosaic"
					class="w-full h-full object-cover"
				>
			</div>
		</div>
	</div>
</section>


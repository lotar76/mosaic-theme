<?php
/**
 * Template Part: About Company Section
 * Переиспользуемый блок "О компании"
 */

declare(strict_types=1);
?>

<!-- About Company Section -->
<section class="bg-gray">
	<div class="max-w-[1920px] mx-auto">
		<div class="grid grid-cols-1 lg:grid-cols-2">
			<!-- Left: Studio Photo -->
			<div class="h-[400px] lg:h-[500px] overflow-hidden">
				<img
					src="<?= get_template_directory_uri(); ?>/img/12.png"
					alt="Студия Si Mosaic"
					class="w-full h-full object-cover"
				>
			</div>

			<!-- Right: About Text -->
			<div class="flex flex-col justify-center px-6 md:px-12 lg:px-16 py-12 lg:py-0">
				<h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">О компании</h2>
				<div class="w-[70px] h-[6px] bg-primary mt-6 mb-8"></div>

				<p class="text-white/80 text-base md:text-lg mb-4 leading-relaxed">
					5 лет мы развиваем своё производство и обучаем специалистов внутри студии, сохраняя высокий стандарт качества в каждой детали.
				</p>

				<p class="text-white/80 text-base md:text-lg mb-8 leading-relaxed">
					Наша студия реализовала свыше 3000 кв. м проектов, где каждая поверхность создавалась вручную и проходила через опыт наших специалистов.
				</p>

				<a
					href="/about"
					class="inline-flex items-center justify-center gap-[10px] bg-primary hover:bg-opacity-90 transition-colors text-white w-full min-[1280px]:w-fit h-[56px] py-4 px-8 text-base md:text-lg"
					tabindex="0"
					aria-label="Узнать больше о нашей истории"
				>
					Больше о нашей истории
				</a>
			</div>
		</div>
	</div>
</section>


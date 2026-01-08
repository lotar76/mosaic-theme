<?php
/**
 * Template Part: Benefits Section
 * Секция "С нами работать комфортно" с адаптивной сеткой
 */

declare(strict_types=1);
?>

<!-- Benefits Section -->
<section class="bg-black py-[80px] min-[1280px]:py-[100px]" data-benefits>
	<div class="mx-auto w-full max-w-[1722px]">
		<!-- Desktop (>=1920) -->
		<div class="hidden min-[1920px]:block">
			<div class="h-[550px] flex flex-col gap-[30px]">
				<!-- Row 1 -->
				<div class="h-[260px] flex gap-[30px]">
					<!-- 1: Title block -->
					<div class="w-[389px] h-[260px] flex flex-col justify-start">
						<h2 class="text-white font-normal text-[56px] leading-[1] tracking-[-0.01em] mb-0">
							С нами<br>комфортно<br>работать
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<!-- 2: Designers card -->
					<div class="w-[427px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							Для дизайнеров интерьера
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							Подключаемся на этапе идеи, подбираем материалы и реализуем уникальные проекты
						</p>
					</div>

					<!-- 3: Designers image -->
					<div class="w-[262px] h-[260px] bg-gray overflow-hidden">
						<img
							src="<?= get_template_directory_uri(); ?>/img/int.jpg"
							alt="Для дизайнеров интерьера"
							class="w-full h-full object-cover"
							loading="lazy"
							decoding="async"
						>
					</div>

					<!-- 4: Business card -->
					<div class="w-[554px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							Для бизнеса
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							Работаем как надежный партнер: соблюдаем сроки и несем ответственность. При соблюдении технологии монтажа предоставляем гарантию
						</p>
					</div>
				</div>

				<!-- Row 2 -->
				<div class="h-[260px] flex gap-[30px]">
					<!-- Individual card -->
					<div class="w-[389px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							Индивидуальные проекты
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							Каждая работа создается специально под пространство и задачу
						</p>
					</div>

					<!-- Individual image -->
					<div class="w-[262px] h-[260px] bg-gray overflow-hidden">
						<img
							src="<?= get_template_directory_uri(); ?>/img/ind.jpg"
							alt="Индивидуальные проекты"
							class="w-full h-full object-cover"
							loading="lazy"
							decoding="async"
						>
					</div>

					<!-- Private card -->
					<div class="w-[554px] h-[260px] bg-gray p-[30px] flex flex-col justify-start">
						<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
							Для частных интерьеров
						</h3>
						<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
						<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
							Берем проект «под ключ» — от эскиза до готового изделия. Соблюдаем сроки и договоренности, сопровождаем на всех этапах реализации
						</p>
					</div>

					<!-- Private image -->
					<div class="w-[427px] h-[260px] bg-gray overflow-hidden">
						<img
							src="<?= get_template_directory_uri(); ?>/img/chast.jpg"
							alt="Для частных интерьеров"
							class="w-full h-full object-cover"
							loading="lazy"
							decoding="async"
						>
					</div>
				</div>
			</div>
		</div>

		<!-- Tablet (1280-1919) -->
		<div class="hidden min-[1280px]:block min-[1920px]:hidden">
			<div class="mx-auto w-[1219px] max-w-full">
				<div class="flex flex-col gap-[30px]">
					<!-- Row 1 -->
					<div class="h-[250px] flex gap-[30px]">
						<div class="w-[384px] h-[250px] flex flex-col justify-start">
							<h2 class="text-white font-normal text-[56px] leading-[1] tracking-[-0.01em] mb-0">
								С нами<br>комфортно<br>работать
							</h2>
							<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
						</div>

						<div class="w-[490px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								Для дизайнеров интерьера
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								Подключаемся на этапе идеи, подбираем материалы и реализуем уникальные проекты
							</p>
						</div>

						<div class="w-[282px] h-[250px] bg-gray overflow-hidden">
							<img
								src="<?= get_template_directory_uri(); ?>/img/int.jpg"
								alt="Для дизайнеров интерьера"
								class="w-full h-full object-cover"
								loading="lazy"
								decoding="async"
							>
						</div>
					</div>

					<!-- Row 2 -->
					<div class="h-[250px] flex gap-[30px]">
						<div class="w-[488px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								Для частных интерьеров
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								Берем проект «под ключ» — от эскиза до готового изделия. Соблюдаем сроки и договоренности, сопровождаем на всех этапах реализации
							</p>
						</div>

						<div class="w-[282px] h-[250px] bg-gray overflow-hidden">
							<img
								src="<?= get_template_directory_uri(); ?>/img/ind.jpg"
								alt="Индивидуальные проекты"
								class="w-full h-full object-cover"
								loading="lazy"
								decoding="async"
							>
						</div>

						<div class="w-[386px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								Индивидуальные проекты
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								Каждая работа создается специально под пространство и задачу
							</p>
						</div>
					</div>

					<!-- Row 3 -->
					<div class="h-[250px] flex gap-[30px]">
						<div class="w-[698px] h-[250px] bg-gray p-[30px] flex flex-col justify-start">
							<h3 class="text-white font-normal text-[28px] leading-[1.15] mb-0">
								Для бизнеса
							</h3>
							<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
							<p class="text-white/60 font-normal text-[20px] leading-[1.45]">
								Работаем как надежный партнер: соблюдаем сроки и несем ответственность. При соблюдении технологии монтажа предоставляем гарантию
							</p>
						</div>

						<div class="w-[491px] h-[250px] bg-gray overflow-hidden">
							<img
								src="<?= get_template_directory_uri(); ?>/img/chast.jpg"
								alt="Для бизнеса"
								class="w-full h-full object-cover"
								loading="lazy"
								decoding="async"
							>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Mobile (<=1279) -->
		<div class="min-[1280px]:hidden">
			<div class="space-y-6">
				<div class="p-6">
					<h2 class="text-white text-3xl md:text-4xl font-normal leading-[1.2] mb-0">
						С нами комфортно<br>работать
					</h2>
					<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
				</div>

				<div data-benefits-carousel>
					<div data-benefits-carousel-track>
						<!-- Slide 1: Designers (text + image) -->
						<div data-benefits-slide>
							<div class="bg-gray p-6" data-benefits-slide-item>
								<h3 class="text-white text-lg font-normal mb-0">Для дизайнеров интерьера</h3>
								<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
								<p class="text-white text-sm leading-relaxed">
									Подключаемся на этапе идеи, подбираем материалы и реализуем уникальные проекты
								</p>
							</div>
							<div class="bg-gray overflow-hidden" data-benefits-slide-item>
								<img
									src="<?= get_template_directory_uri(); ?>/img/int.jpg"
									alt="Для дизайнеров интерьера"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							</div>
						</div>

						<!-- Slide 2: Business (text + image) -->
						<div data-benefits-slide>
							<div class="bg-gray p-6" data-benefits-slide-item>
								<h3 class="text-white text-lg font-normal mb-0">Для бизнеса</h3>
								<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
								<p class="text-white text-sm leading-relaxed">
									Работаем как надежный партнер: соблюдаем сроки и несем ответственность. При соблюдении технологии монтажа предоставляем гарантию
								</p>
							</div>
							<div class="bg-gray overflow-hidden" data-benefits-slide-item>
								<img
									src="<?= get_template_directory_uri(); ?>/img/chast.jpg"
									alt="Для бизнеса"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							</div>
						</div>

						<!-- Slide 3: Individual (text + image) -->
						<div data-benefits-slide>
							<div class="bg-gray p-6" data-benefits-slide-item>
								<h3 class="text-white text-lg font-normal mb-0">Индивидуальные проекты</h3>
								<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
								<p class="text-white text-sm leading-relaxed">
									Каждая работа создается специально под пространство и задачу
								</p>
							</div>
							<div class="bg-gray overflow-hidden" data-benefits-slide-item>
								<img
									src="<?= get_template_directory_uri(); ?>/img/ind.jpg"
									alt="Индивидуальные проекты"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							</div>
						</div>

						<!-- Slide 4: Private (text + image) -->
						<div data-benefits-slide>
							<div class="bg-gray p-6" data-benefits-slide-item>
								<h3 class="text-white text-lg font-normal mb-0">Для частных интерьеров</h3>
								<div class="w-[70px] h-[6px] bg-primary mt-6 mb-3"></div>
								<p class="text-white text-sm leading-relaxed">
									Берем проект «под ключ» — от эскиза до готового изделия. Соблюдаем сроки и договоренности, сопровождаем на всех этапах реализации
								</p>
							</div>
							<div class="bg-gray overflow-hidden" data-benefits-slide-item>
								<img
									src="<?= get_template_directory_uri(); ?>/img/chast.jpg"
									alt="Для частных интерьеров"
									class="w-full h-full object-cover"
									loading="lazy"
									decoding="async"
								>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


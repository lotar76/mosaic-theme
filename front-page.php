<?php
/**
 * Template: Главная страница
 */
get_header();

/** @var array $heroOpt */
$heroOpt = get_option('mosaic_home_hero', mosaic_get_home_hero_defaults());
$heroOpt = mosaic_sanitize_home_hero_option($heroOpt);

$heroImageId = absint($heroOpt['image_id'] ?? 0);
$heroImageUrl = $heroImageId > 0
	? (string) wp_get_attachment_image_url($heroImageId, 'full')
	: (get_template_directory_uri() . '/img/banner/Photo.png');

$defaults = mosaic_get_home_hero_defaults();

$heroTitleRaw = (string) ($heroOpt['title'] ?? '');
$heroTitle = $heroTitleRaw !== '' ? $heroTitleRaw : (string) ($defaults['title'] ?? '');

$heroSubtitleRaw = (string) ($heroOpt['subtitle'] ?? '');
$heroSubtitle = $heroSubtitleRaw !== '' ? $heroSubtitleRaw : (string) ($defaults['subtitle'] ?? '');

$heroButtonTextRaw = (string) ($heroOpt['button_text'] ?? '');
$heroButtonText = $heroButtonTextRaw !== '' ? $heroButtonTextRaw : (string) ($defaults['button_text'] ?? '');

$heroButtonUrlRaw = (string) ($heroOpt['button_url'] ?? '');
$heroButtonUrl = esc_url($heroButtonUrlRaw !== '' ? $heroButtonUrlRaw : (string) ($defaults['button_url'] ?? '/catalog'));

$titleLines = preg_split("/\r\n|\r|\n/", trim($heroTitle)) ?: [];
$titleLines = array_values(array_filter(array_map('trim', $titleLines), static fn($v) => $v !== ''));
$heroTitleHtml = implode('<br class="hidden sm:block">', array_map('esc_html', $titleLines));

$subtitleLines = preg_split("/\r\n|\r|\n/", trim($heroSubtitle)) ?: [];
$subtitleLines = array_values(array_filter(array_map('trim', $subtitleLines), static fn($v) => $v !== ''));
$heroSubtitleHtml = implode('<br class="hidden md:block">', array_map('esc_html', $subtitleLines));
?>

<main class="flex-grow">
    <!-- Hero Section -->
    <section class="relative">
        <!-- Hero Image -->
        <div class="w-full h-[560px] max-[1279px]:h-[360px] overflow-hidden">
            <img src="<?= esc_url($heroImageUrl); ?>"
                 alt="Мозаичное панно из художественного стекла"
                 class="w-full h-full object-cover object-center"
            >
        </div>

        <!-- Hero Content -->
        <div class="bg-gray px-4 lg:px-[100px] py-[40px]">
            <div class="max-w-[1920px] mx-auto">
                <!-- Title -->
                <h1 class="text-white mb-6 md:mb-8">
					<?= $heroTitleHtml; ?>
                </h1>

                <!-- CTA Block -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 md:gap-8">
                    <!-- Description -->
                    <p class="text3 text-white/70 order-1 sm:order-2">
						<?= $heroSubtitleHtml; ?>
                    </p>

                    <!-- Catalog Button -->
                    <a
                        href="<?= $heroButtonUrl; ?>"
                        class="inline-flex items-center justify-center gap-[10px] bg-primary hover:bg-opacity-90 transition-colors text-white h-[64px] py-4 px-[30px] text-[20px] leading-[24px] w-full sm:w-[173px] order-2 sm:order-1"
                        tabindex="0"
                        aria-label="<?= esc_attr('Перейти: ' . $heroButtonText); ?>"
                    >
                        <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 16 16">
                            <rect x="0" y="0" width="7" height="7"/>
                            <rect x="9" y="0" width="7" height="7"/>
                            <rect x="0" y="9" width="7" height="7"/>
                            <rect x="9" y="9" width="7" height="7"/>
                        </svg>
                        <span><?= esc_html($heroButtonText); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Catalog Preview Section -->
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]" data-catalog>
        <div class="mx-auto w-full" data-catalog-inner>
            <div class="mb-8 md:mb-12">
                <h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Каталог</h2>
                <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
            </div>

			<?php
			$catalogCards = function_exists('mosaic_get_catalog_category_cards') ? mosaic_get_catalog_category_cards() : [];
			$fallbackCatalog = mosaic_get_catalog_categories();
			?>
            <div data-catalog-grid>
				<?php if (is_array($catalogCards) && count($catalogCards) > 0) : ?>
					<?php foreach ($catalogCards as $card) : ?>
						<?php
						$categoryTitle = (string) ($card['title'] ?? '');
						$categoryUrl = (string) ($card['url'] ?? '');
						$imageUrlRaw = (string) ($card['image_url'] ?? '');
						$interiorImageUrlRaw = (string) ($card['interior_image_url'] ?? '');
						$videoUrlRaw = (string) ($card['video_url'] ?? '');

						if ($categoryTitle === '' || $categoryUrl === '') {
							continue;
						}

						$imageUrl = $imageUrlRaw !== '' ? esc_url($imageUrlRaw) : '';
						$interiorImageUrl = $interiorImageUrlRaw !== '' ? esc_url($interiorImageUrlRaw) : '';
						$videoUrl = $videoUrlRaw !== '' ? esc_url($videoUrlRaw) : '';
						$hasInterior = ($videoUrl === '' && $interiorImageUrl !== '');
						?>
                    <a
                        href="<?= esc_url($categoryUrl); ?>"
                        class="group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                        tabindex="0"
                        aria-label="<?= esc_attr($categoryTitle); ?>"
                        data-catalog-card
                        <?php if ($videoUrl !== '') : ?>data-has-video="1"<?php endif; ?>
                        <?php if ($hasInterior) : ?>data-has-interior="1"<?php endif; ?>
                    >
                        <div class="bg-gray/20" data-catalog-media>
                            <img
                                src="<?= $imageUrl !== '' ? $imageUrl : esc_url(get_template_directory_uri() . '/img/catalog/1.png'); ?>"
                                alt="<?= esc_attr($categoryTitle); ?>"
                                class="w-full h-full object-cover transition-opacity duration-500 ease-in-out transition-transform duration-500 group-hover:scale-[1.03]"
                                loading="lazy"
                                decoding="async"
                                data-catalog-image
                            >
							<?php if ($hasInterior) : ?>
                                <img
                                    src="<?= esc_url($interiorImageUrl); ?>"
                                    alt="<?= esc_attr($categoryTitle); ?>"
                                    class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-500 ease-in-out"
                                    loading="lazy"
                                    decoding="async"
                                    data-catalog-interior-image
                                >
							<?php endif; ?>
							<?php if ($videoUrl !== '') : ?>
                                <video
                                    class="absolute inset-0 w-full h-full object-cover object-top opacity-0 transition-opacity duration-500"
                                    muted
                                    playsinline
                                    preload="auto"
                                    poster="<?= $imageUrl !== '' ? esc_url($imageUrl) : esc_url(get_template_directory_uri() . '/img/catalog/1.png'); ?>"
                                    data-catalog-video
                                >
                                    <source src="<?= $videoUrl; ?>" type="video/mp4">
                                </video>
							<?php endif; ?>
                        </div>
                        <div class="mt-3 md:mt-4 flex items-center justify-between gap-3" data-catalog-meta>
                            <span data-catalog-caption>
								<?= esc_html($categoryTitle); ?>
                            </span>
                            <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
					<?php endforeach; ?>
				<?php else : ?>
					<?php foreach ($fallbackCatalog as $category) : ?>
						<?php
						$categoryTitle = (string) ($category['title'] ?? '');
						$categorySlug = (string) ($category['slug'] ?? '');
						$categoryImage = (string) ($category['image'] ?? '');
						$categoryVideo = (string) ($category['video'] ?? '');

						if ($categoryTitle === '' || $categorySlug === '' || $categoryImage === '') {
							continue;
						}

						$categoryUrl = esc_url(home_url('/catalog/' . $categorySlug . '/'));
						$imageUrl = esc_url(get_template_directory_uri() . $categoryImage);
						?>
                        <a
                            href="<?= $categoryUrl; ?>"
                            class="group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                            tabindex="0"
                            aria-label="<?= esc_attr($categoryTitle); ?>"
                            data-catalog-card
							<?php if ($categoryVideo !== '') : ?>data-has-video="1"<?php endif; ?>
                        >
                            <div class="bg-gray/20" data-catalog-media>
                                <img
                                    src="<?= $imageUrl; ?>"
                                    alt="<?= esc_attr($categoryTitle); ?>"
                                    class="w-full h-full object-cover transition-opacity duration-500 ease-in-out transition-transform duration-500 group-hover:scale-[1.03]"
                                    loading="lazy"
                                    decoding="async"
                                    data-catalog-image
                                >
								<?php if ($categoryVideo !== '') : ?>
									<?php $videoUrl = esc_url(get_template_directory_uri() . $categoryVideo); ?>
                                    <video
                                        class="absolute inset-0 w-full h-full object-cover object-top opacity-0 transition-opacity duration-500"
                                        muted
                                        playsinline
                                        preload="auto"
                                        poster="<?= esc_url($imageUrl); ?>"
                                        data-catalog-video
                                    >
                                        <source src="<?= $videoUrl; ?>" type="video/mp4">
                                    </video>
								<?php endif; ?>
                            </div>
                            <div class="mt-3 md:mt-4 flex items-center justify-between gap-3" data-catalog-meta>
                                <span data-catalog-caption>
									<?= esc_html($categoryTitle); ?>
                                </span>
                                <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
					<?php endforeach; ?>
				<?php endif; ?>
            </div>
        </div>
    </section>

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

    <?php get_template_part('template-parts/sections/benefits'); ?>

    <!-- Portfolio Section -->
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]" data-portfolio>
        <div class="max-w-[1920px] mx-auto pl-4 md:pl-8 min-[1280px]:pl-10 min-[1920px]:pl-[100px] pr-0">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8 md:mb-12">
                <div>
                    <h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Портфолио</h2>
                    <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
                </div>

                <!-- Navigation Arrows -->
                <div class="flex gap-[37px] max-[1279px]:hidden mr-[99px]">
                    <button
                        type="button"
                        class="portfolio-prev p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
                        aria-label="Предыдущий слайд"
                        tabindex="0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="portfolio-next p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
                        aria-label="Следующий слайд"
                        tabindex="0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Portfolio Slider -->
            <div class="portfolio-slider overflow-hidden">
				<?php
				$portfolioItems = [
					['id' => 1, 'title' => 'Название проекта', 'subtitle' => 'Коммерческое'],
					['id' => 2, 'title' => 'Название проекта', 'subtitle' => 'Интерьерное'],
					['id' => 3, 'title' => 'Название проекта', 'subtitle' => 'Коммерческое'],
					['id' => 4, 'title' => 'Название проекта', 'subtitle' => 'Интерьерное'],
					['id' => 5, 'title' => 'Название проекта', 'subtitle' => 'Коммерческое'],
				];
				?>
                <div class="portfolio-track flex transition-transform duration-700 ease-in-out" data-portfolio-track>
					<?php foreach ($portfolioItems as $item) : ?>
						<?php
						$id = (int) ($item['id'] ?? 0);
						$title = (string) ($item['title'] ?? '');
						$subtitle = (string) ($item['subtitle'] ?? '');

						if ($id <= 0) {
							continue;
						}

						$imageUrl = esc_url(get_template_directory_uri() . "/img/portfolio/{$id}.jpg");
						$itemUrl = esc_url(home_url("/portfolio/project-{$id}/"));
						?>
                        <a
                            href="<?= $itemUrl; ?>"
                            class="portfolio-slide group flex-shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-black"
                            tabindex="0"
                            aria-label="<?= esc_attr("{$title} {$subtitle}"); ?>"
                            data-portfolio-slide
                        >
                            <div class="bg-gray overflow-hidden" data-portfolio-media>
                                <img
                                    src="<?= $imageUrl; ?>"
                                    alt="<?= esc_attr($title); ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>

                            <div data-portfolio-caption>
                                <div data-portfolio-title><?= esc_html($title); ?></div>
                                <div data-portfolio-subtitle><?= esc_html($subtitle); ?></div>
                            </div>
                        </a>
					<?php endforeach; ?>
                </div>
            </div>

            <!-- All Projects Button -->
            <div class="flex justify-center mt-10 md:mt-12">
                <a
                    href="/portfolio"
                    class="inline-flex items-center justify-center border border-primary text-primary hover:bg-primary hover:text-white transition-colors h-[56px] px-10 text-base w-full max-w-[300px] min-[1280px]:w-fit min-[1280px]:max-w-none"
                    tabindex="0"
                    aria-label="Смотреть все проекты"
                >
                    Все проекты
                </a>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/sections/contact-form'); ?>

    <!-- Showroom Section -->
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
			<?php $showroomImageUrl = get_template_directory_uri() . '/img/shaurum.png'; ?>

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
                            href="#contact-form"
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
                            <li class="flex items-start gap-3">
                                <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                <span>Образцы работы</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                <span>Большие готовые панно</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                <span>Каталог материалов</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                <span>Встречи с дизайнером студии</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                <span>Встречи дизайнеров и мастер-классы</span>
                            </li>
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
                                href="#contact-form"
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
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Образцы работы</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Большие готовые панно</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Каталог материалов</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Встречи с дизайнером студии</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Встречи дизайнеров и мастер-классы</span>
                                </li>
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
                                href="#contact-form"
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
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Образцы работы</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Большие готовые панно</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Каталог материалов</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Встречи с дизайнером студии</span>
                                </li>
                                <li class="flex items-start gap-4">
                                    <span class="text-primary mt-1 flex-shrink-0">◆</span>
                                    <span>Встречи дизайнеров и мастер-классы</span>
                                </li>
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

    <?php get_template_part('template-parts/sections/work-process'); ?>

    <!-- News and Blog Section -->
    <section class="bg-black py-[80px] min-[1280px]:py-[100px]">
        <div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16">
			<?php
			$newsImgBaseUrl = get_template_directory_uri() . '/img/news';

			$newsOpt = function_exists('mosaic_get_news') ? mosaic_get_news() : ['items' => []];
			$newsItems = is_array($newsOpt) ? ($newsOpt['items'] ?? []) : [];
			if (!is_array($newsItems)) {
				$newsItems = [];
			}

			$newsPage = function_exists('get_page_by_path') ? get_page_by_path('news') : null;
			$newsArchiveUrl = ($newsPage instanceof WP_Post) ? (string) get_permalink($newsPage) : '#';

			// Fallback: если новостей нет — показываем старые 5 карточек из темы.
			$hasDynamicNews = count($newsItems) > 0;
			?>
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8 md:mb-12">
                <div>
                    <h2 class="text-white text-3xl md:text-4xl lg:text-5xl font-normal mb-0">Новости и блог</h2>
                    <div class="w-[70px] h-[6px] bg-primary mt-6"></div>
                </div>

                <!-- Navigation Arrows -->
                <div class="flex gap-[37px] max-[1279px]:hidden">
                    <button
                        type="button"
                        class="news-prev p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
                        aria-label="Предыдущий слайд"
                        tabindex="0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="news-next p-2 text-white/60 hover:text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
                        aria-label="Следующий слайд"
                        tabindex="0"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- News Slider -->
            <div class="news-slider overflow-hidden">
                <div class="news-track flex gap-4 md:gap-6 transition-transform duration-500 ease-out">
					<?php if ($hasDynamicNews) : ?>
						<?php foreach ($newsItems as $item) : ?>
							<?php
							if (!is_array($item)) {
								continue;
							}
							$title = (string) ($item['title'] ?? '');
							$content = (string) ($item['content'] ?? '');
							$galleryIds = is_array($item['gallery_ids'] ?? null) ? $item['gallery_ids'] : [];
							$galleryUrls = is_array($item['gallery_urls'] ?? null) ? $item['gallery_urls'] : [];

							$thumbUrl = '';
							if (count($galleryIds) > 0) {
								$thumbUrl = (string) wp_get_attachment_image_url((int) $galleryIds[0], 'large');
							} elseif (count($galleryUrls) > 0) {
								$thumbUrl = (string) $galleryUrls[0];
							}

							$textPlain = trim((string) wp_strip_all_tags($content));
							$excerpt = $textPlain !== '' ? wp_trim_words($textPlain, 18, '…') : '';
							$caption = $excerpt !== '' ? $excerpt : ($title !== '' ? $title : '');
							if ($caption === '') {
								$caption = 'Новость';
							}

							$aria = $title !== '' ? $title : $caption;
							?>
                            <a href="<?= esc_url($newsArchiveUrl); ?>" class="news-slide bg-black group flex-shrink-0 w-[280px] md:w-[320px]" tabindex="0" aria-label="<?= esc_attr($aria); ?>">
                                <div class="aspect-[4/3] overflow-hidden">
									<?php if ($thumbUrl !== '') : ?>
                                        <img
                                            src="<?= esc_url($thumbUrl); ?>"
                                            alt="<?= esc_attr($aria); ?>"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            loading="lazy"
                                            decoding="async"
                                        >
									<?php else : ?>
                                        <img
                                            src="<?= esc_url($newsImgBaseUrl . '/1.png'); ?>"
                                            alt="<?= esc_attr($aria); ?>"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            loading="lazy"
                                            decoding="async"
                                        >
									<?php endif; ?>
                                </div>
                                <p class="p-6 text-white/70 text-sm leading-relaxed">
									<?= esc_html($caption); ?>
                                </p>
                            </a>
						<?php endforeach; ?>
					<?php else : ?>
						<?php for ($i = 1; $i <= 5; $i++) : ?>
                            <a href="<?= esc_url($newsArchiveUrl); ?>" class="news-slide bg-black group flex-shrink-0 w-[280px] md:w-[320px]" tabindex="0" aria-label="<?= esc_attr('Новость ' . $i); ?>">
                                <div class="aspect-[4/3] overflow-hidden">
                                    <img
                                        src="<?= esc_url($newsImgBaseUrl . '/' . $i . '.png'); ?>"
                                        alt="<?= esc_attr('Новость ' . $i); ?>"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </div>
                                <p class="p-6 text-white/70 text-sm leading-relaxed">
                                    Банальные, но неопровержимые выводы, а также независимые государства
                                </p>
                            </a>
						<?php endfor; ?>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>



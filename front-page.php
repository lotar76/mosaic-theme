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

    <!-- Content Sections with uniform spacing -->
    <div class="py-[80px] min-[1280px]:py-[100px] space-y-[80px] min-[1280px]:space-y-[100px]">
        <!-- Catalog Preview Section -->
        <?php get_template_part('template-parts/sections/catalog', null, [
            'title_tag' => 'h2',
            'title' => 'Каталог'
        ]); ?>

        <!-- About Section (О компании + Основатели, без отступа между ними) -->
        <?php get_template_part('template-parts/sections/about-section'); ?>

        <!-- Benefits Section -->
        <?php get_template_part('template-parts/sections/benefits'); ?>

        <!-- Portfolio Section -->
        <?php get_template_part('template-parts/sections/portfolio'); ?>

        <!-- Contact Form Section -->
        <?php get_template_part('template-parts/sections/contact-form'); ?>

        <!-- Showroom Section -->
        <?php get_template_part('template-parts/sections/showroom'); ?>

        <!-- Work Process Section -->
        <?php get_template_part('template-parts/sections/work-process'); ?>

        <!-- News and Blog Section -->
        <?php get_template_part('template-parts/sections/news'); ?>
    </div>
</main>

<?php get_footer(); ?>



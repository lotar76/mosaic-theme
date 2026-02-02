<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
    <title><?php wp_title(); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'black': '#0F0303',
                        'gray': '#1C0101',
                        'stroke': '#CBD5E0',
                        'back': '#F6F6F6',
                        'white': '#FFFFFF',
                        'primary': '#A36217',
                        'primary-15': '#A3621726',
                        'primary-25': '#A3621740',
                        'error': '#EF3D47',
                        'success': '#46DF83',
                    },
                    fontFamily: {
                        'century': ['Century Gothic', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-black min-h-screen font-century flex flex-col">
<?php
$phoneContact = function_exists('mosaic_get_phone_contact') ? mosaic_get_phone_contact() : ['display' => '+7 (928) 206-07-75', 'href' => 'tel:+79282060775'];
$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
$telegramUrl = is_array($siteSettings) ? (string) (($siteSettings['socials']['telegram'] ?? '') ?? '') : '';
$telegramUrl = $telegramUrl !== '' ? esc_url($telegramUrl) : 'https://t.me/simosaic';
?>

    <!-- Header -->
    <header class="bg-black fixed top-0 left-0 right-0 z-[60]" data-header-root>
        <div class="max-w-[1920px] mx-auto h-[120px] max-[1279px]:h-[88px] px-4 lg:px-[100px]">
            <nav class="relative flex items-center justify-between h-full gap-4">
                <!-- Left: Desktop menu (>= 1920) -->
                <div class="flex-1 hidden min-[1920px]:block">
                    <?php mosaic_render_menu_zone_with_fallback('desktop_left', [
                        'class' => 'flex items-center gap-6 lg:gap-10 text2',
                    ]); ?>
                </div>

                <!-- Left: 1280..1919 (menu + hamburger) - планшет -->
                <div class="flex-1 hidden min-[1280px]:max-[1919px]:flex items-center justify-start gap-6">
                    <button
                        type="button"
                        class="text-white p-2 hover:text-primary transition-colors"
                        aria-label="Открыть меню"
                        aria-controls="header-dropdown-menu"
                        aria-expanded="false"
                        tabindex="0"
                        data-header-menu-toggle="dropdown"
                    >
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-icon="burger">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-icon="close">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6l-12 12"/>
                        </svg>
                    </button>
                    <?php mosaic_render_menu_zone_with_fallback('tablet_inline', [
                        'class' => 'flex items-center gap-6 text2',
                    ]); ?>
                </div>

                <!-- Left: <= 1279 (icons) - мобилка -->
                <div class="flex-1 hidden max-[1279px]:flex items-center justify-start gap-4">
                    <a href="<?= $telegramUrl; ?>" class="text-white hover:text-primary transition-colors p-2" tabindex="0" aria-label="Telegram" target="_blank" rel="noopener noreferrer">
                        <svg width="21" height="18" viewBox="0 0 21 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M18.0169 0.117465C18.264 0.013456 18.5345 -0.022416 18.8002 0.0135823C19.0659 0.0495806 19.317 0.156133 19.5276 0.32215C19.7381 0.488167 19.9003 0.707579 19.9973 0.957548C20.0942 1.20752 20.1224 1.47891 20.0789 1.74347L17.8109 15.5005C17.5909 16.8275 16.1349 17.5885 14.9179 16.9275C13.8999 16.3745 12.3879 15.5225 11.0279 14.6335C10.3479 14.1885 8.26489 12.7635 8.52089 11.7495C8.74089 10.8825 12.2409 7.62446 14.2409 5.68747C15.0259 4.92647 14.6679 4.48747 13.7409 5.18747C11.4389 6.92547 7.74289 9.56847 6.52089 10.3125C5.44289 10.9685 4.88089 11.0805 4.20889 10.9685C2.98289 10.7645 1.84589 10.4485 0.917888 10.0635C-0.336112 9.54347 -0.275112 7.81947 0.916888 7.31747L18.0169 0.117465Z" fill="white"/>
                        </svg>
                    </a>
                    <a href="<?= esc_url($phoneContact['href']); ?>" class="text-white hover:text-primary transition-colors p-2" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 13.42V16.956C18.0001 17.2092 17.9042 17.453 17.7316 17.6382C17.559 17.8234 17.3226 17.9363 17.07 17.954C16.6333 17.9847 16.2767 18 16 18C7.163 18 0 10.837 0 2C0 1.724 0.0153333 1.36733 0.046 0.93C0.0637224 0.677444 0.176581 0.441011 0.361804 0.268409C0.547026 0.0958068 0.790823 -0.000114433 1.044 2.56579e-07H4.58C4.70404 -0.000125334 4.8237 0.045859 4.91573 0.12902C5.00776 0.212182 5.0656 0.326583 5.078 0.45C5.10067 0.679334 5.122 0.863333 5.142 1.002C5.34072 2.38893 5.74799 3.73784 6.35 5.003C6.445 5.203 6.383 5.442 6.203 5.57L4.045 7.112C5.36471 10.1863 7.81472 12.6363 10.889 13.956L12.429 11.802C12.4917 11.7137 12.5835 11.6503 12.6883 11.6231C12.7932 11.5958 12.9042 11.6064 13.002 11.653C14.267 12.2539 15.6156 12.6601 17.002 12.858C17.1407 12.878 17.324 12.8993 17.552 12.922C17.6752 12.9346 17.7894 12.9926 17.8724 13.0846C17.9553 13.1766 18.0002 13.2961 18 13.42Z" fill="white"/>
                        </svg>
                    </a>
                </div>

                <!-- Logo -->
                <a href="<?= esc_url(home_url('/')); ?>" class="shrink-0 flex items-center justify-center" tabindex="0" aria-label="Si Mosaic - На главную">
                    <img
                        src="<?= get_template_directory_uri(); ?>/img/logo/Logo.svg"
                        alt="Si Mosaic"
                        class="w-[76px] h-[98px] max-[1279px]:w-[50px] max-[1279px]:h-[64px]"
                    >
                </a>

                <!-- Right: Desktop (>= 1920) -->
                <div class="flex-1 hidden min-[1920px]:flex items-center justify-end gap-8">
                    <?php mosaic_render_menu_zone_with_fallback('desktop_right', [
                        'class' => 'flex items-center gap-8 text2',
                    ]); ?>
                    <a
                        href="<?= esc_url($phoneContact['href']); ?>"
                        class="text2 whitespace-nowrap hover:text-primary transition-colors"
                        tabindex="0"
                        aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>"
                    >
                        <?= esc_html((string) $phoneContact['display']); ?>
                    </a>
                    <div class="flex items-center gap-4">
                        <?php get_template_part('template-parts/social-icons'); ?>
                    </div>
                    <?php get_template_part('template-parts/language-switcher'); ?>
                </div>

                <!-- Right: 1280..1919 (phone + socials) - планшет -->
                <div class="flex-1 hidden min-[1280px]:max-[1919px]:flex items-center justify-end gap-6">
                    <a
                        href="<?= esc_url($phoneContact['href']); ?>"
                        class="text2 whitespace-nowrap hover:text-primary transition-colors"
                        tabindex="0"
                        aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>"
                    >
                        <?= esc_html((string) $phoneContact['display']); ?>
                    </a>
                    <div class="flex items-center gap-4">
                        <?php get_template_part('template-parts/social-icons'); ?>
                    </div>
                    <?php get_template_part('template-parts/language-switcher'); ?>
                </div>

                <!-- Right: <= 1279 (hamburger) - мобилка -->
                <div class="flex-1 hidden max-[1279px]:flex items-center justify-end">
                    <button
                        type="button"
                        class="text-white p-2 hover:text-primary transition-colors"
                        aria-label="Открыть меню"
                        aria-controls="header-mobile-menu"
                        aria-expanded="false"
                        tabindex="0"
                        data-header-menu-toggle="mobile"
                    >
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-icon="burger">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-icon="close">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6l-12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Dropdown menu (1280..1919) - планшет -->
                <div
                    id="header-dropdown-menu"
                    class="absolute top-full left-4 right-4 -mt-[38px] hidden z-[70]"
                    data-header-menu-panel="dropdown"
                >
                    <div class="w-full max-w-[320px] bg-gray/95 backdrop-blur-sm shadow-lg p-6">
                        <?php mosaic_render_menu_zone_with_fallback('tablet_dropdown', [
                            'class' => 'flex flex-col gap-6 text2',
                        ]); ?>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Mobile overlay + offcanvas (<= 1279) -->
        <div class="fixed top-[88px] bottom-0 left-0 right-0 z-[65] hidden bg-black/60" data-header-menu-overlay="mobile"></div>
        <aside
            id="header-mobile-menu"
            class="fixed top-[88px] bottom-0 right-0 z-[70] w-full max-w-[360px] translate-x-full bg-gray/95 backdrop-blur-sm transition-transform duration-300 ease-out"
            data-header-menu-panel="mobile"
            aria-label="Мобильное меню"
        >
            <div class="flex h-full flex-col p-6">
                <nav>
                    <?php mosaic_render_menu_zone_with_fallback('mobile_offcanvas', [
                        'class' => 'flex flex-col gap-6 text2',
                    ]); ?>
                </nav>

                <div class="mt-auto pt-8">
                    <a
                        href="<?= esc_url($phoneContact['href']); ?>"
                        class="text2 whitespace-nowrap hover:text-primary transition-colors"
                        tabindex="0"
                        aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>"
                    >
                        <?= esc_html((string) $phoneContact['display']); ?>
                    </a>
                    <div class="mt-6 flex items-center gap-4">
                        <?php get_template_part('template-parts/social-icons'); ?>
                    </div>
                    <div class="mt-6">
                        <?php get_template_part('template-parts/language-switcher', null, ['variant' => 'mobile']); ?>
                    </div>
                </div>
            </div>
        </aside>
    </header>

    <!-- Header spacer (prevents content from going under fixed header) -->
    <div aria-hidden="true" class="h-[120px] max-[1279px]:h-[88px]"></div>
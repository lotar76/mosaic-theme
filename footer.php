<!-- Footer -->
<?php
$homeUrl = esc_url(home_url('/'));
$logoUrl = esc_url(get_template_directory_uri() . '/img/logo/Logo.svg');
$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
$privacyUrlRaw = is_array($siteSettings) ? (string) ($siteSettings['privacy_policy_url'] ?? '') : '';
$privacyUrl = $privacyUrlRaw !== '' ? esc_url(home_url($privacyUrlRaw)) : esc_url(home_url('/privacy-policy-2/'));
$currentYear = (string) wp_date('Y');
$phoneContact = function_exists('mosaic_get_phone_contact') ? mosaic_get_phone_contact() : ['display' => '+7 (928) 206-07-75', 'href' => 'tel:+79282060775'];
?>

<footer class="bg-gray mt-auto">
    <div class="max-w-[1920px] mx-auto px-4 lg:px-[100px] py-10">
        <!-- Mobile footer (<=1279) -->
        <div class="max-[1279px]:block min-[1280px]:hidden">
            <div class="flex flex-col gap-8">
                <a href="<?= $homeUrl; ?>" class="flex items-center w-fit" tabindex="0" aria-label="Si Mosaic - На главную">
                    <img src="<?= $logoUrl; ?>" alt="Si Mosaic" class="w-[76px] h-[98px]">
                </a>

                <a
                    href="<?= esc_url($phoneContact['href']); ?>"
                    class="text-white font-century font-normal text-[22px] leading-[145%] tracking-[0] hover:text-primary transition-colors w-fit"
                    tabindex="0"
                    aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>"
                >
					<?= esc_html((string) $phoneContact['display']); ?>
                </a>

                <div class="flex items-center gap-6">
                    <?php get_template_part('template-parts/social-icons', null, ['icon_class' => 'w-[33px] h-[33px]']); ?>
                </div>

                <nav class="flex flex-col gap-6 text-white font-century font-normal text-[22px] leading-[145%] tracking-[0]" aria-label="Навигация в подвале">
                    <?php
                    $footerItems = mosaic_get_menu_items_for_zone('footer');
                    if (empty($footerItems)) {
                        $footerItems = mosaic_get_menu_fallback()['footer'] ?? [];
                    }
                    foreach ($footerItems as $item):
                        $title = $item['title'] ?? '';
                        $url = $item['url'] ?? '#';
                    ?>
                        <a href="<?= esc_url($url); ?>" class="hover:text-primary transition-colors w-fit" tabindex="0"><?= esc_html($title); ?></a>
                    <?php endforeach; ?>
                </nav>

                <div class="pt-6 border-t border-white/10 flex flex-col gap-3">
                    <span class="text-white/50 font-century font-normal text-[18px] leading-[145%] tracking-[0]">
                        Si Mosaic <?= esc_html($currentYear); ?>
                    </span>
                    <a
                        href="<?= $privacyUrl; ?>"
                        class="text-white/50 font-century font-normal text-[18px] leading-[145%] tracking-[0] hover:text-white transition-colors w-fit"
                        tabindex="0"
                    >
                        Политика конфиденциальности
                    </a>
                </div>
            </div>
        </div>

        <!-- Tablet footer (1280..1919) -->
        <div class="hidden min-[1280px]:max-[1919px]:block">
            <div class="flex flex-col gap-10">
                <!-- Row 1: logo + menu -->
                <div class="flex items-start gap-10">
                    <a href="<?= $homeUrl; ?>" class="flex items-center shrink-0" tabindex="0" aria-label="Si Mosaic - На главную">
                        <img src="<?= $logoUrl; ?>" alt="Si Mosaic" class="w-[76px] h-[98px]">
                    </a>

                    <nav class="flex flex-1 flex-nowrap items-center justify-between text-white font-century font-normal text-[20px] leading-[145%] tracking-[0]" aria-label="Навигация в подвале">
                        <?php foreach ($footerItems as $item): ?>
                            <a href="<?= esc_url($item['url'] ?? '#'); ?>" class="hover:text-primary transition-colors whitespace-nowrap" tabindex="0"><?= esc_html($item['title'] ?? ''); ?></a>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <!-- Row 2: phone + socials -->
                <div class="flex items-center gap-16">
                    <a
                        href="<?= esc_url($phoneContact['href']); ?>"
                        class="text-white font-century font-normal text-[20px] leading-[145%] tracking-[0] hover:text-primary transition-colors whitespace-nowrap"
                        tabindex="0"
                        aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>"
                    >
						<?= esc_html((string) $phoneContact['display']); ?>
                    </a>

                    <div class="flex items-center gap-4">
                        <?php get_template_part('template-parts/social-icons'); ?>
                    </div>
                </div>

                <!-- Row 3: meta -->
                <div class="flex items-center gap-10 text-white/50 font-century font-normal text-[18px] leading-[145%] tracking-[0]">
                    <span>Si Mosaic <?= esc_html($currentYear); ?></span>
                    <a href="<?= $privacyUrl; ?>" class="hover:text-white transition-colors" tabindex="0">Политика конфиденциальности</a>
                </div>
            </div>
        </div>

        <!-- Desktop footer (>=1920) -->
        <div class="hidden min-[1920px]:block">
            <!-- Top Row -->
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8 mb-8">
                <!-- Logo -->
                <a href="<?= $homeUrl; ?>" class="flex items-center" tabindex="0" aria-label="Si Mosaic - На главную">
                    <img src="<?= $logoUrl; ?>" alt="Si Mosaic" class="w-[76px] h-[98px]">
                </a>

                <!-- Menu -->
                <nav class="flex flex-wrap items-center gap-8" aria-label="Навигация в подвале">
                    <?php foreach ($footerItems as $item): ?>
                        <a href="<?= esc_url($item['url'] ?? '#'); ?>" class="text2 hover:text-primary transition-colors" tabindex="0"><?= esc_html($item['title'] ?? ''); ?></a>
                    <?php endforeach; ?>
                </nav>

                <!-- Phone & Socials -->
                <div class="flex items-center gap-8">
                    <a href="<?= esc_url($phoneContact['href']); ?>" class="text2 whitespace-nowrap hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>">
						<?= esc_html((string) $phoneContact['display']); ?>
                    </a>

                    <!-- Social Icons -->
                    <div class="w-[184px] h-[24px] flex items-center gap-4">
                        <?php get_template_part('template-parts/social-icons'); ?>
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-8 text-white/70 text-sm">
                <span>Si Mosaic <?= esc_html($currentYear); ?></span>
                <a href="<?= $privacyUrl; ?>" class="hover:text-white transition-colors" tabindex="0">Политика конфиденциальности</a>
            </div>
        </div>
    </div>
</footer>

<?php get_template_part('template-parts/modal-forms'); ?>

<?php wp_footer(); ?>
</body>
</html>

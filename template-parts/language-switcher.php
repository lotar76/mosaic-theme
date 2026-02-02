<?php
/**
 * Language Switcher Component
 *
 * @param array $args {
 *     @type string $variant 'default' | 'mobile' - controls dropdown direction
 * }
 */
defined('ABSPATH') || exit;

$incomingArgs = [];
if (isset($args) && is_array($args)) {
	$incomingArgs = $args;
}

$variant = (string) ($incomingArgs['variant'] ?? 'default');
$isMobile = $variant === 'mobile';

// Available languages
$languages = [
	'ru' => 'RU',
	'en' => 'ENG',
];

// Current language (default: ru)
$currentLang = 'ru';

// Check for Polylang
if (function_exists('pll_current_language')) {
	$currentLang = pll_current_language('slug') ?: 'ru';
}

// Check for WPML
if (defined('ICL_LANGUAGE_CODE')) {
	$currentLang = ICL_LANGUAGE_CODE;
}

$currentLabel = $languages[$currentLang] ?? 'RU';

// Get language URLs
$langUrls = [];
foreach ($languages as $slug => $label) {
	$url = '#';

	// Polylang
	if (function_exists('pll_the_languages')) {
		$pllLangs = pll_the_languages(['raw' => 1]);
		if (isset($pllLangs[$slug]['url'])) {
			$url = $pllLangs[$slug]['url'];
		}
	}

	// WPML
	if (function_exists('icl_get_languages')) {
		$wpmlLangs = icl_get_languages('skip_missing=0');
		if (isset($wpmlLangs[$slug]['url'])) {
			$url = $wpmlLangs[$slug]['url'];
		}
	}

	$langUrls[$slug] = $url;
}

$panelId = $isMobile ? 'lang-switcher-mobile' : 'lang-switcher-desktop';
$toggleAttr = $isMobile ? 'mobile' : 'desktop';
?>

<div class="relative" data-lang-switcher="<?= esc_attr($toggleAttr); ?>">
	<button
		type="button"
		class="flex items-center gap-1 text2 text-white hover:text-primary transition-colors"
		aria-label="Выбор языка"
		aria-controls="<?= esc_attr($panelId); ?>"
		aria-expanded="false"
		data-lang-toggle="<?= esc_attr($toggleAttr); ?>"
	>
		<span data-lang-current><?= esc_html($currentLabel); ?></span>
		<svg
			class="w-4 h-4 transition-transform duration-200"
			fill="none"
			stroke="currentColor"
			viewBox="0 0 24 24"
			data-lang-chevron
		>
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
		</svg>
	</button>

	<div
		id="<?= esc_attr($panelId); ?>"
		class="absolute <?= $isMobile ? 'bottom-full mb-3' : 'top-full mt-2'; ?> <?= $isMobile ? 'left-0' : 'right-0'; ?> min-w-[140px] bg-black shadow-lg hidden z-[80]"
		data-lang-panel="<?= esc_attr($toggleAttr); ?>"
	>
		<div class="py-4 px-2">
			<?php foreach ($languages as $slug => $label): ?>
				<?php
				$isCurrent = $slug === $currentLang;
				$url = $langUrls[$slug];
				$itemClass = $isCurrent
					? 'block w-full px-5 py-2 text-left text2 text-primary bg-gray'
					: 'block w-full px-5 py-2 text-left text2 text-white hover:text-primary hover:bg-gray transition-colors';
				?>
				<a
					href="<?= esc_url($url); ?>"
					class="<?= esc_attr($itemClass); ?>"
					<?= $isCurrent ? 'aria-current="true"' : ''; ?>
					data-lang="<?= esc_attr($slug); ?>"
				>
					<?= esc_html($label); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>

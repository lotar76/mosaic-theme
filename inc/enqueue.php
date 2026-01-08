<?php

declare(strict_types=1);

add_action('wp_enqueue_scripts', static function (): void {
	$breakpoints = function_exists('mosaic_get_breakpoints') ? mosaic_get_breakpoints() : [];

	$mainCssPath = get_template_directory() . '/assets/css/main.css';
	$mainCssVersion = file_exists($mainCssPath) ? (string) filemtime($mainCssPath) : '1.0';

	wp_enqueue_style(
		'mosaic-main',
		get_template_directory_uri() . '/assets/css/main.css',
		[],
		$mainCssVersion
	);

	/**
	 * Inline responsive CSS (чтобы брейкпоинты менялись в одном месте).
	 * Нативно использовать CSS var() внутри @media нельзя, поэтому генерируем @media здесь.
	 */
	wp_register_style('mosaic-responsive', false, ['mosaic-main'], $mainCssVersion);
	wp_enqueue_style('mosaic-responsive');

	$mobileMax = (int) ($breakpoints['MOBILE_MAX'] ?? 1279);

	$responsiveCss = implode("\n", [
		'/* Mosaic responsive rules (generated from PHP breakpoints) */',
		"@media (max-width: {$mobileMax}px) {",
		'  h1, .h1 {',
		'    font-size: 30px !important;',
		'    line-height: 116% !important;',
		'    letter-spacing: -0.01em !important;',
		'  }',
		'}',
		'',
	]);

	wp_add_inline_style('mosaic-responsive', $responsiveCss);

	$mainJsPath = get_template_directory() . '/assets/js/main.js';
	$mainJsVersion = file_exists($mainJsPath) ? (string) filemtime($mainJsPath) : '1.0';

	wp_enqueue_script(
		'mosaic-main',
		get_template_directory_uri() . '/assets/js/main.js',
		[],
		$mainJsVersion,
		true
	);

	// Прокидываем брейкпоинты в JS одним объектом.
	wp_add_inline_script(
		'mosaic-main',
		'window.MOSAIC_BREAKPOINTS = ' . wp_json_encode($breakpoints, JSON_UNESCAPED_UNICODE) . ';',
		'before'
	);
});



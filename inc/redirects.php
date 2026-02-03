<?php
/**
 * 301 Redirects: frontend logic
 * Checks current request URI against saved redirect rules and performs 301 redirect if matched.
 */

defined('ABSPATH') || exit;

add_action('template_redirect', static function (): void {
	$redirects = get_option('mosaic_redirects', []);
	if (!is_array($redirects) || count($redirects) === 0) {
		return;
	}

	$requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
	if ($requestUri === '') {
		return;
	}

	// Normalize: strip query string for matching, keep path only
	$requestPath = strtok($requestUri, '?');
	if (!is_string($requestPath)) {
		return;
	}
	$requestPath = rtrim($requestPath, '/') . '/';

	foreach ($redirects as $rule) {
		if (!is_array($rule)) {
			continue;
		}
		$from = trim((string) ($rule['from'] ?? ''));
		$to = trim((string) ($rule['to'] ?? ''));
		if ($from === '' || $to === '') {
			continue;
		}

		$fromNormalized = rtrim($from, '/') . '/';

		if ($requestPath === $fromNormalized) {
			wp_redirect(esc_url_raw($to), 301);
			exit;
		}
	}
}, 1);

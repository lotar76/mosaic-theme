<?php
/**
 * Admin page: Redirects (301)
 * Таблица соответствий старых URL → новых для 301 редиректов.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// ── Sanitizer ────────────────────────────────────────────────────────────────

function mosaic_sanitize_redirects_option($value): array {
	if (!is_array($value)) {
		return [];
	}

	$result = [];
	$fromList = isset($value['from']) && is_array($value['from']) ? $value['from'] : [];
	$toList = isset($value['to']) && is_array($value['to']) ? $value['to'] : [];

	$count = max(count($fromList), count($toList));

	for ($i = 0; $i < $count; $i++) {
		$from = trim(sanitize_text_field((string) ($fromList[$i] ?? '')));
		$toRaw = trim((string) ($toList[$i] ?? ''));

		if ($from === '' || $toRaw === '') {
			continue;
		}

		// Normalize from: ensure starts with /
		if (strpos($from, '/') !== 0) {
			$from = '/' . $from;
		}

		// Sanitize to: allow relative and absolute URLs
		if (preg_match('~^(\/|#|\?)~', $toRaw) === 1) {
			$to = sanitize_text_field($toRaw);
		} else {
			$to = esc_url_raw($toRaw);
		}

		if ($to === '') {
			continue;
		}

		$result[] = [
			'from' => $from,
			'to' => $to,
		];
	}

	return $result;
}

// ── Admin menu + settings registration ───────────────────────────────────────

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		add_menu_page(
			'Редиректы',
			'Редиректы',
			'edit_theme_options',
			'mosaic-redirects',
			'mosaic_render_redirects_page',
			'dashicons-randomize',
			60
		);
	});

	add_action('admin_init', static function (): void {
		register_setting(
			'mosaic_redirects_group',
			'mosaic_redirects',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_redirects_option',
			]
		);
	});
}

// ── Render ────────────────────────────────────────────────────────────────────

function mosaic_render_redirects_page(): void {
	$redirects = get_option('mosaic_redirects', []);
	if (!is_array($redirects)) {
		$redirects = [];
	}
	?>
	<div class="wrap">
		<h1>Редиректы (301)</h1>
		<p class="description">Таблица соответствий старых URL → новых. При заходе по старому пути — автоматический 301 редирект.</p>

		<form method="post" action="options.php">
			<?php settings_fields('mosaic_redirects_group'); ?>

			<table class="widefat striped" id="mosaic-redirects-table" style="max-width:900px; margin-top:20px;">
				<thead>
					<tr>
						<th style="width:40px;">#</th>
						<th>Старый путь</th>
						<th>Новый URL</th>
						<th style="width:60px;"></th>
					</tr>
				</thead>
				<tbody id="mosaic-redirects-tbody">
					<?php if (count($redirects) > 0) : ?>
						<?php foreach ($redirects as $idx => $rule) : ?>
							<tr>
								<td class="mosaic-redirect-num"><?= esc_html((string) ($idx + 1)); ?></td>
								<td>
									<input
										type="text"
										name="mosaic_redirects[from][]"
										value="<?= esc_attr((string) ($rule['from'] ?? '')); ?>"
										class="regular-text"
										placeholder="/product/dragonfly/"
										style="width:100%;"
									>
								</td>
								<td>
									<input
										type="text"
										name="mosaic_redirects[to][]"
										value="<?= esc_attr((string) ($rule['to'] ?? '')); ?>"
										class="regular-text"
										placeholder="/catalog/classic/nazvanie/"
										style="width:100%;"
									>
								</td>
								<td>
									<button type="button" class="button mosaic-redirect-remove" title="Удалить">&times;</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<p style="margin-top:12px;">
				<button type="button" class="button" id="mosaic-redirect-add">+ Добавить строку</button>
			</p>

			<?php submit_button('Сохранить'); ?>
		</form>
	</div>

	<script>
	(function() {
		var tbody = document.getElementById('mosaic-redirects-tbody');
		var addBtn = document.getElementById('mosaic-redirect-add');

		function renumberRows() {
			var rows = tbody.querySelectorAll('tr');
			for (var i = 0; i < rows.length; i++) {
				var num = rows[i].querySelector('.mosaic-redirect-num');
				if (num) num.textContent = String(i + 1);
			}
		}

		function addRow() {
			var count = tbody.querySelectorAll('tr').length;
			var tr = document.createElement('tr');
			tr.innerHTML =
				'<td class="mosaic-redirect-num">' + (count + 1) + '</td>' +
				'<td><input type="text" name="mosaic_redirects[from][]" value="" class="regular-text" placeholder="/product/dragonfly/" style="width:100%;"></td>' +
				'<td><input type="text" name="mosaic_redirects[to][]" value="" class="regular-text" placeholder="/catalog/classic/nazvanie/" style="width:100%;"></td>' +
				'<td><button type="button" class="button mosaic-redirect-remove" title="Удалить">&times;</button></td>';
			tbody.appendChild(tr);
		}

		addBtn.addEventListener('click', addRow);

		tbody.addEventListener('click', function(e) {
			if (e.target.classList.contains('mosaic-redirect-remove')) {
				e.target.closest('tr').remove();
				renumberRows();
			}
		});
	})();
	</script>
	<?php
}

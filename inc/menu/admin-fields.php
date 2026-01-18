<?php

declare(strict_types=1);

/**
 * Кастомные поля для пунктов меню в админке.
 * Добавляет чекбоксы "Зоны отображения" к каждому пункту.
 */

if (!is_admin()) {
	return;
}

/**
 * Добавляем кастомные поля к пунктам меню.
 * Используем хук wp_nav_menu_item_custom_fields (WP 5.4+).
 *
 * @param int      $item_id Menu item ID.
 * @param WP_Post  $menu_item Menu item data object.
 * @param int      $depth Depth of menu item.
 * @param stdClass $args An object of menu item arguments.
 */
add_action('wp_nav_menu_item_custom_fields', static function (int $item_id, $menu_item, int $depth, $args): void {
	$zones = mosaic_get_menu_zones();
	$savedZones = get_post_meta($item_id, '_mosaic_menu_zones', true);
	$savedZones = is_array($savedZones) ? $savedZones : [];

	$fieldId = 'mosaic-menu-zones-' . $item_id;
	?>
	<p class="field-mosaic-zones description description-wide">
		<label style="font-weight: 600; display: block; margin-bottom: 8px;">
			Зоны отображения
		</label>
		<span class="mosaic-zones-checkboxes" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 16px;">
			<?php foreach ($zones as $zoneKey => $zoneData): ?>
				<?php
				$checked = in_array($zoneKey, $savedZones, true) ? 'checked' : '';
				$inputId = $fieldId . '-' . $zoneKey;
				?>
				<label for="<?php echo esc_attr($inputId); ?>" style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
					<input
						type="checkbox"
						id="<?php echo esc_attr($inputId); ?>"
						name="mosaic_menu_zones[<?php echo esc_attr((string) $item_id); ?>][]"
						value="<?php echo esc_attr($zoneKey); ?>"
						<?php echo $checked; ?>
					>
					<span title="<?php echo esc_attr($zoneData['description']); ?>">
						<?php echo esc_html($zoneData['label']); ?>
					</span>
				</label>
			<?php endforeach; ?>
		</span>
	</p>
	<?php
}, 10, 4);

/**
 * Сохраняем кастомные поля при сохранении меню.
 *
 * @param int $menu_id ID of the updated menu.
 * @param int $menu_item_db_id ID of the updated menu item.
 */
add_action('wp_update_nav_menu_item', static function (int $menu_id, int $menu_item_db_id): void {
	// Проверяем nonce (WordPress делает это автоматически для меню, но проверим ещё раз)
	if (!current_user_can('edit_theme_options')) {
		return;
	}

	$zoneKeys = mosaic_get_menu_zone_keys();

	// Получаем отправленные зоны для этого пункта
	$submittedZones = [];
	if (
		isset($_POST['mosaic_menu_zones']) &&
		is_array($_POST['mosaic_menu_zones']) &&
		isset($_POST['mosaic_menu_zones'][$menu_item_db_id])
	) {
		$raw = $_POST['mosaic_menu_zones'][$menu_item_db_id];
		if (is_array($raw)) {
			// Санитизируем и валидируем
			foreach ($raw as $zone) {
				$zone = sanitize_key($zone);
				if (in_array($zone, $zoneKeys, true)) {
					$submittedZones[] = $zone;
				}
			}
		}
	}

	// Сохраняем или удаляем мету
	if (!empty($submittedZones)) {
		update_post_meta($menu_item_db_id, '_mosaic_menu_zones', $submittedZones);
	} else {
		delete_post_meta($menu_item_db_id, '_mosaic_menu_zones');
	}
}, 10, 2);

/**
 * Добавляем стили для улучшения UI в редакторе меню.
 */
add_action('admin_head-nav-menus.php', static function (): void {
	?>
	<style>
		.field-mosaic-zones {
			margin-top: 12px;
			padding-top: 12px;
			border-top: 1px solid #dcdcde;
		}

		.field-mosaic-zones label[for*="mosaic-menu-zones"] {
			font-size: 13px;
		}

		.field-mosaic-zones input[type="checkbox"] {
			margin: 0;
		}

		.mosaic-zones-checkboxes label:hover span {
			color: #2271b1;
		}
	</style>
	<?php
});

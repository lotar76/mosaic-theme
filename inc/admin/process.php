<?php

declare(strict_types=1);

/**
 * Процесс работы (Админка -> Процесс работы).
 * Поддержка мультиязычности через Polylang.
 */

/**
 * Получить список доступных языков из Polylang.
 *
 * @return array<string, string> Массив [slug => name], например ['ru' => 'Русский', 'en' => 'English']
 */
function mosaic_get_available_languages(): array {
	if (!function_exists('pll_languages_list')) {
		return ['ru' => 'Русский'];
	}

	$languages = pll_languages_list(['fields' => []]);
	if (!is_array($languages) || count($languages) === 0) {
		return ['ru' => 'Русский'];
	}

	$result = [];
	foreach ($languages as $lang) {
		if (is_object($lang) && isset($lang->slug, $lang->name)) {
			$result[$lang->slug] = $lang->name;
		}
	}

	return count($result) > 0 ? $result : ['ru' => 'Русский'];
}

/**
 * Получить язык по умолчанию из Polylang.
 */
function mosaic_get_default_language(): string {
	if (function_exists('pll_default_language')) {
		$default = pll_default_language();
		if (is_string($default) && $default !== '') {
			return $default;
		}
	}
	return 'ru';
}

/**
 * Получить текущий язык для фронтенда.
 */
function mosaic_get_current_frontend_language(): string {
	if (function_exists('pll_current_language')) {
		$current = pll_current_language();
		if (is_string($current) && $current !== '') {
			return $current;
		}
	}
	return mosaic_get_default_language();
}

/**
 * Получить название опции для конкретного языка.
 */
function mosaic_get_work_process_option_name(string $lang = ''): string {
	if ($lang === '') {
		$lang = mosaic_get_default_language();
	}
	return 'mosaic_work_process_' . $lang;
}

/**
 * @return array{blocks:array<int,array{image_id:int,image_url:string,title:string,description:string}>}
 */
function mosaic_get_work_process_defaults(): array {
	$base = get_template_directory_uri() . '/img/process';

	$defaultTitle = 'Название пункта';
	$defaultDescription = 'Создадим сложный уникальный дизайн панно или мозаики по вашему запросу';

	return [
		'blocks' => [
			[
				'image_id' => 0,
				'image_url' => $base . '/1.png',
				'title' => $defaultTitle,
				'description' => $defaultDescription,
			],
			[
				'image_id' => 0,
				'image_url' => $base . '/2.png',
				'title' => $defaultTitle,
				'description' => $defaultDescription,
			],
			[
				'image_id' => 0,
				'image_url' => $base . '/3.png',
				'title' => $defaultTitle,
				'description' => $defaultDescription,
			],
			[
				'image_id' => 0,
				'image_url' => $base . '/4.png',
				'title' => $defaultTitle,
				'description' => $defaultDescription,
			],
			[
				'image_id' => 0,
				'image_url' => $base . '/5.png',
				'title' => $defaultTitle,
				'description' => $defaultDescription,
			],
		],
	];
}

/**
 * @param mixed $value
 * @return array{blocks:array<int,array{image_id:int,image_url:string,title:string,description:string}>}
 */
function mosaic_sanitize_work_process_option($value): array {
	$defaults = mosaic_get_work_process_defaults();

	if (!is_array($value)) {
		return $defaults;
	}

	$blocksIn = $value['blocks'] ?? [];
	if (!is_array($blocksIn)) {
		$blocksIn = [];
	}

	$blocks = [];
	foreach ($blocksIn as $row) {
		$block = mosaic_sanitize_work_process_block($row);
		if ($block === null) {
			continue;
		}
		$blocks[] = $block;
	}

	if (count($blocks) > 30) {
		$blocks = array_slice($blocks, 0, 30);
	}

	if (count($blocks) === 0) {
		return $defaults;
	}

	return ['blocks' => $blocks];
}

/**
 * Получить данные Процесса работы для конкретного языка.
 *
 * @param string $lang Код языка (ru, en). Если пусто — текущий язык фронтенда.
 * @return array{blocks:array<int,array{image_id:int,image_url:string,title:string,description:string}>}
 */
function mosaic_get_work_process(string $lang = ''): array {
	if ($lang === '') {
		$lang = mosaic_get_current_frontend_language();
	}

	$optionName = mosaic_get_work_process_option_name($lang);
	$opt = get_option($optionName, null);

	// Если для данного языка нет данных, пробуем получить данные языка по умолчанию
	if ($opt === null || $opt === false) {
		$defaultLang = mosaic_get_default_language();
		if ($lang !== $defaultLang) {
			$optionName = mosaic_get_work_process_option_name($defaultLang);
			$opt = get_option($optionName, null);
		}
	}

	// Если данных нет вообще, возвращаем дефолты
	if ($opt === null || $opt === false) {
		return mosaic_get_work_process_defaults();
	}

	return mosaic_sanitize_work_process_option($opt);
}

/**
 * @param mixed $row
 * @return array{image_id:int,image_url:string,title:string,description:string}|null
 */
function mosaic_sanitize_work_process_block($row): ?array {
	if (!is_array($row)) {
		return null;
	}

	$imageId = absint($row['image_id'] ?? 0);
	$imageUrlRaw = trim((string) ($row['image_url'] ?? ''));
	$imageUrlRaw = sanitize_text_field($imageUrlRaw);
	$imageUrl = $imageUrlRaw !== '' ? esc_url_raw($imageUrlRaw) : '';

	$title = sanitize_text_field((string) ($row['title'] ?? ''));
	$description = sanitize_textarea_field((string) ($row['description'] ?? ''));

	$hasAny = ($imageId > 0) || ($imageUrl !== '') || ($title !== '') || ($description !== '');
	if (!$hasAny) {
		return null;
	}

	return [
		'image_id' => $imageId,
		'image_url' => $imageUrl,
		'title' => $title,
		'description' => $description,
	];
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$hook = add_menu_page(
			'Процесс работы',
			'Процесс работы',
			'edit_theme_options',
			'mosaic-work-process',
			'mosaic_render_work_process_page',
			'dashicons-editor-ol',
			58.5 // После Меню (58)
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			wp_enqueue_media();
			wp_enqueue_script('jquery-ui-sortable');

			wp_register_script('mosaic-process-admin', false, ['jquery', 'jquery-ui-sortable'], '1.0', true);
			wp_enqueue_script('mosaic-process-admin');

			// Получаем текущий язык из URL
			$currentLang = isset($_GET['lang']) ? sanitize_key((string) $_GET['lang']) : mosaic_get_default_language();

			$cfg = wp_json_encode(
				[
					'postUrl' => admin_url('admin-post.php'),
					'reorderNonce' => wp_create_nonce('mosaic_work_process_reorder'),
					'lang' => $currentLang,
				],
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			);

			$js = <<<'JS'
(function($){
  var CFG = __MOSAIC_CFG__;
  var frame;

  function openMedia(onSelect){
    if (frame) {
      frame.off('select');
      frame.on('select', onSelect);
      frame.open();
      return;
    }
    frame = wp.media({
      title: 'Выбрать картинку',
      button: { text: 'Использовать' },
      multiple: false,
      library: { type: 'image' }
    });
    frame.on('select', onSelect);
    frame.open();
  }

  function setPreview(url){
    var $img = $('#mosaic-process-image-preview');
    if (url) {
      $img.attr('src', url).show();
      $('#mosaic-process-image-remove').show();
    } else {
      $img.attr('src', '').hide();
      $('#mosaic-process-image-remove').hide();
    }
  }

  $(document).on('click', '#mosaic-process-image-select', function(e){
    e.preventDefault();
    openMedia(function(){
      var attachment = frame.state().get('selection').first().toJSON();
      $('#mosaic_process_image_id').val(attachment.id || 0);
      $('#mosaic_process_image_url').val('');
      setPreview(attachment.url || '');
    });
  });

  $(document).on('click', '#mosaic-process-image-remove', function(e){
    e.preventDefault();
    $('#mosaic_process_image_id').val(0);
    $('#mosaic_process_image_url').val('');
    setPreview('');
  });

  function showNotice(type, text){
    var $box = $('#mosaic-process-notices');
    if ($box.length === 0) return;
    var cls = type === 'success' ? 'notice-success' : 'notice-error';
    var $n = $('<div class="notice is-dismissible ' + cls + '"><p></p></div>');
    $n.find('p').text(text);
    $box.empty().append($n);
  }

  function initSortable(){
    var $tbody = $('#mosaic-process-sortable');
    if ($tbody.length === 0 || typeof $tbody.sortable !== 'function') return;

    var isSaving = false;
    $tbody.sortable({
      axis: 'y',
      handle: '.mosaic-process-drag',
      helper: function(e, tr){
        var $tr = $(tr);
        var $helper = $tr.clone();
        $helper.children().each(function(index){
          $(this).width($tr.children().eq(index).width());
        });
        return $helper;
      },
      start: function(){ $tbody.addClass('is-reordering'); },
      stop: function(){ $tbody.removeClass('is-reordering'); },
      update: function(){
        // Собираем текущий порядок индексов
        var order = [];
        $tbody.find('tr').each(function(){
          var raw = $(this).data('index');
          // Важно: raw может быть 0, поэтому нельзя использовать || (0 - falsy)
          if (raw === undefined || raw === null || raw === '') return;
          var idx = parseInt(String(raw), 10);
          if (!Number.isFinite(idx) || idx < 0) return;
          order.push(idx);
        });

        console.log('=== REORDER DEBUG ===');
        console.log('Order to send:', order);

        // Сразу обновляем data-index на новые позиции (0, 1, 2, ...)
        // чтобы следующий drag работал корректно
        $tbody.find('tr').each(function(newIdx){
          $(this).attr('data-index', newIdx).data('index', newIdx);
        });

        // Обновляем номера строк
        $tbody.find('[data-mosaic-process-row-num]').each(function(i){
          $(this).text(String(i + 1));
        });

        // Отправляем на сервер
        $.post(CFG.postUrl, {
          action: 'mosaic_reorder_work_process_blocks',
          mosaic_work_process_nonce: CFG.reorderNonce,
          lang: CFG.lang,
          order: order
        }).done(function(resp){
          console.log('Response:', resp);
          if (resp && resp.success) {
            showNotice('success', 'Порядок сохранён.');
          } else {
            showNotice('error', 'Не удалось сохранить порядок.');
          }
        }).fail(function(xhr, status, error){
          console.log('FAIL:', status, error, xhr.responseText);
          showNotice('error', 'Не удалось сохранить порядок.');
        });
      }
    });
  }

  $(function(){
    var url = String($('#mosaic-process-image-preview').attr('src') || '').trim();
    setPreview(url);
    initSortable();
  });
})(jQuery);
JS;

			$js = str_replace('__MOSAIC_CFG__', $cfg, $js);
			wp_add_inline_script('mosaic-process-admin', $js);

			echo '<style>
				body.toplevel_page_mosaic-work-process .mosaic-process-table-wrap {
					width: 100%;
					max-width: none;
					border-radius: 12px;
					overflow: hidden;
					box-shadow: 0 10px 30px rgba(0,0,0,0.06);
					border: 1px solid #dcdcde;
					background: #fff;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-table {
					width: 100%;
					max-width: none;
					border: 0;
					margin: 0;
					table-layout: fixed;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-table thead th {
					background: linear-gradient(180deg, #101010 0%, #0b0b0b 100%);
					color: #fff;
					border-bottom: 1px solid rgba(255,255,255,0.12);
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-table td,
				body.toplevel_page_mosaic-work-process .mosaic-process-table th {
					padding: 14px 14px;
					vertical-align: middle;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-table tbody tr:hover { background: #f6f7ff; }
				body.toplevel_page_mosaic-work-process .mosaic-process-thumb { width:120px; height:90px; object-fit:cover; border-radius: 8px; border: 1px solid #dcdcde; background:#f6f7f7; display:block; }
				body.toplevel_page_mosaic-work-process .mosaic-process-actions { display:flex; gap:8px; flex-wrap:wrap; }
				body.toplevel_page_mosaic-work-process .mosaic-process-actions .button { border-radius: 8px; }
				body.toplevel_page_mosaic-work-process .mosaic-process-actions .button.button-link-delete { color: #b32d2e; }
				body.toplevel_page_mosaic-work-process .mosaic-process-muted { color: #7a7a7a; }
				body.toplevel_page_mosaic-work-process .mosaic-process-drag {
					display:inline-flex; align-items:center; justify-content:center;
					width: 28px; height: 28px;
					border-radius: 8px;
					border: 1px solid #dcdcde;
					background: #fff;
					color: #3c434a;
					cursor: grab;
					user-select: none;
				}
				body.toplevel_page_mosaic-work-process #mosaic-process-sortable.is-reordering tr { background: #fffdf2; }

				body.toplevel_page_mosaic-work-process .mosaic-process-card {
					max-width: 1100px;
					background: #fff;
					border: 1px solid #dcdcde;
					border-radius: 14px;
					box-shadow: 0 10px 30px rgba(0,0,0,0.06);
					overflow: hidden;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-card-header {
					display:flex; align-items:center; justify-content:space-between; gap: 12px;
					padding: 16px 18px;
					background: linear-gradient(180deg, #101010 0%, #0b0b0b 100%);
					color: #fff;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-badge {
					display:inline-flex; align-items:center; justify-content:center;
					min-width: 56px; height: 32px; padding: 0 10px;
					border-radius: 999px;
					background: rgba(255,255,255,0.10);
					border: 1px solid rgba(255,255,255,0.14);
					font-weight: 600;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-card-body { padding: 18px; }
				body.toplevel_page_mosaic-work-process .mosaic-process-edit-grid { display:grid; grid-template-columns: 320px 1fr; gap:18px; align-items:start; }
				@media (max-width: 980px) { body.toplevel_page_mosaic-work-process .mosaic-process-edit-grid { grid-template-columns: 1fr; } }
				body.toplevel_page_mosaic-work-process .mosaic-process-uploader { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; }
				body.toplevel_page_mosaic-work-process #mosaic-process-image-preview {
					width: 100%; height: 220px; object-fit: cover;
					border-radius: 10px; border: 1px solid #dcdcde; background: #f6f7f7; display: none;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-edit-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
				body.toplevel_page_mosaic-work-process .mosaic-process-edit-actions .button { border-radius: 10px; }
				body.toplevel_page_mosaic-work-process .mosaic-process-input,
				body.toplevel_page_mosaic-work-process .mosaic-process-textarea {
					width: 100%;
					border-radius: 12px;
					padding: 10px 12px;
					border-color: #dcdcde;
				}
				body.toplevel_page_mosaic-work-process .mosaic-process-textarea { min-height: 140px; }
			</style>';
		});
	});

	add_action('admin_init', static function (): void {
		// Миграция: переносим данные из старой опции mosaic_work_process в новую mosaic_work_process_{lang}
		$migrationKey = 'mosaic_work_process_i18n_migrated';
		if (get_option($migrationKey) !== '1') {
			$oldData = get_option('mosaic_work_process', null);
			if ($oldData !== null && $oldData !== false && is_array($oldData)) {
				$defaultLang = mosaic_get_default_language();
				$newOptionName = mosaic_get_work_process_option_name($defaultLang);

				// Проверяем, что новая опция ещё не существует
				$newData = get_option($newOptionName, null);
				if ($newData === null || $newData === false) {
					update_option($newOptionName, $oldData, false);
				}
			}
			update_option($migrationKey, '1', false);
		}

		// Регистрируем настройки для каждого языка
		$languages = mosaic_get_available_languages();
		foreach (array_keys($languages) as $lang) {
			$optionName = mosaic_get_work_process_option_name($lang);
			$existing = get_option($optionName, null);
			if ($existing === false) {
				add_option($optionName, mosaic_get_work_process_defaults(), '', false);
			}

			register_setting(
				'mosaic_work_process_group_' . $lang,
				$optionName,
				[
					'type' => 'array',
					'sanitize_callback' => 'mosaic_sanitize_work_process_option',
					'default' => [],
				]
			);
		}
	});
}

add_action('admin_post_mosaic_save_work_process_block', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	check_admin_referer('mosaic_work_process_save', 'mosaic_work_process_nonce');

	$lang = isset($_POST['lang']) ? sanitize_key((string) $_POST['lang']) : mosaic_get_default_language();
	$index = isset($_POST['index']) ? absint($_POST['index']) : -1;
	$isNew = isset($_POST['is_new']) ? absint($_POST['is_new']) === 1 : false;

	$imageId = isset($_POST['image_id']) ? absint($_POST['image_id']) : 0;
	$imageUrl = isset($_POST['image_url']) ? (string) $_POST['image_url'] : '';
	$title = isset($_POST['title']) ? (string) $_POST['title'] : '';
	$description = isset($_POST['description']) ? (string) $_POST['description'] : '';

	$block = mosaic_sanitize_work_process_block([
		'image_id' => $imageId,
		'image_url' => $imageUrl,
		'title' => $title,
		'description' => $description,
	]);

	if ($block === null) {
		$redirect = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $lang, 'error' => 'empty'], admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	$opt = mosaic_get_work_process($lang);
	$blocks = is_array($opt['blocks'] ?? null) ? $opt['blocks'] : [];

	if (($block['image_id'] ?? 0) > 0) {
		$block['image_url'] = '';
	}

	if ($isNew) {
		$blocks[] = $block;
	} else {
		if ($index < 0 || !array_key_exists($index, $blocks)) {
			$redirect = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $lang, 'error' => 'not_found'], admin_url('admin.php'));
			wp_safe_redirect($redirect);
			exit;
		}
		$blocks[$index] = $block;
	}

	$next = mosaic_sanitize_work_process_option(['blocks' => $blocks]);
	$optionName = mosaic_get_work_process_option_name($lang);
	update_option($optionName, $next, false);

	$redirect = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $lang, 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

add_action('admin_post_mosaic_delete_work_process_block', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	check_admin_referer('mosaic_work_process_delete', 'mosaic_work_process_nonce');

	$lang = isset($_POST['lang']) ? sanitize_key((string) $_POST['lang']) : mosaic_get_default_language();
	$index = isset($_POST['index']) ? absint($_POST['index']) : -1;
	$opt = mosaic_get_work_process($lang);
	$blocks = is_array($opt['blocks'] ?? null) ? $opt['blocks'] : [];

	if ($index < 0 || !array_key_exists($index, $blocks)) {
		$redirect = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $lang, 'error' => 'not_found'], admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	unset($blocks[$index]);
	$blocks = array_values($blocks);

	$next = mosaic_sanitize_work_process_option(['blocks' => $blocks]);
	$optionName = mosaic_get_work_process_option_name($lang);
	update_option($optionName, $next, false);

	$redirect = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $lang, 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

add_action('admin_post_mosaic_reorder_work_process_blocks', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_send_json_error(['message' => 'forbidden'], 403);
	}

	check_admin_referer('mosaic_work_process_reorder', 'mosaic_work_process_nonce');

	$lang = isset($_POST['lang']) ? sanitize_key((string) $_POST['lang']) : mosaic_get_default_language();
	$orderIn = $_POST['order'] ?? [];
	if (!is_array($orderIn)) {
		wp_send_json_error(['message' => 'bad_request'], 400);
	}

	// order содержит индексы элементов в новом порядке
	// например [2, 0, 1] означает: элемент с индекса 2 теперь первый, с 0 - второй, с 1 - третий
	$order = array_map('absint', $orderIn);

	$opt = mosaic_get_work_process($lang);
	$blocks = is_array($opt['blocks'] ?? null) ? $opt['blocks'] : [];
	$blocksCount = count($blocks);

	// Переупорядочиваем блоки согласно order
	$nextBlocks = [];
	$used = [];
	foreach ($order as $idx) {
		// Проверяем что индекс валидный и не использован
		if ($idx < $blocksCount && !isset($used[$idx]) && isset($blocks[$idx])) {
			$nextBlocks[] = $blocks[$idx];
			$used[$idx] = true;
		}
	}

	// Добавляем оставшиеся блоки (на случай если order неполный)
	for ($i = 0; $i < $blocksCount; $i++) {
		if (!isset($used[$i]) && isset($blocks[$i])) {
			$nextBlocks[] = $blocks[$i];
		}
	}

	$next = mosaic_sanitize_work_process_option(['blocks' => $nextBlocks]);
	$optionName = mosaic_get_work_process_option_name($lang);
	update_option($optionName, $next, false);

	wp_send_json_success(['ok' => true]);
});

function mosaic_render_work_process_page(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	$action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : '';
	$index = isset($_GET['index']) ? absint($_GET['index']) : -1;

	// Определяем текущий язык
	$languages = mosaic_get_available_languages();
	$defaultLang = mosaic_get_default_language();
	$currentLang = isset($_GET['lang']) ? sanitize_key((string) $_GET['lang']) : $defaultLang;

	// Если язык не в списке доступных, используем дефолт
	if (!array_key_exists($currentLang, $languages)) {
		$currentLang = $defaultLang;
	}

	$opt = mosaic_get_work_process($currentLang);
	$blocks = is_array($opt['blocks'] ?? null) ? $opt['blocks'] : [];

	echo '<div class="wrap">';
	echo '<h1>Процесс работы</h1>';

	// Языковые табы
	if (count($languages) > 1) {
		echo '<style>
			.mosaic-lang-tabs { display: flex; gap: 0; margin: 16px 0 20px; border-bottom: 1px solid #c3c4c7; }
			.mosaic-lang-tab {
				padding: 10px 20px;
				background: #f0f0f1;
				border: 1px solid #c3c4c7;
				border-bottom: none;
				margin-bottom: -1px;
				text-decoration: none;
				color: #50575e;
				font-weight: 500;
				border-radius: 4px 4px 0 0;
				margin-right: 4px;
				transition: background-color 0.2s;
			}
			.mosaic-lang-tab:hover { background: #fff; color: #2271b1; }
			.mosaic-lang-tab.active {
				background: #fff;
				border-bottom-color: #fff;
				color: #1d2327;
				font-weight: 600;
			}
			.mosaic-lang-tab .dashicons { margin-right: 4px; vertical-align: text-bottom; }
		</style>';

		echo '<div class="mosaic-lang-tabs">';
		foreach ($languages as $langSlug => $langName) {
			$tabUrl = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $langSlug], admin_url('admin.php'));
			$isActive = ($langSlug === $currentLang);
			$activeClass = $isActive ? ' active' : '';
			$flag = '';
			if ($langSlug === 'ru') {
				$flag = '🇷🇺 ';
			} elseif ($langSlug === 'en') {
				$flag = '🇬🇧 ';
			} elseif ($langSlug === 'de') {
				$flag = '🇩🇪 ';
			} elseif ($langSlug === 'fr') {
				$flag = '🇫🇷 ';
			}
			echo '<a href="' . esc_url($tabUrl) . '" class="mosaic-lang-tab' . $activeClass . '">' . $flag . esc_html($langName) . '</a>';
		}
		echo '</div>';

		echo '<p class="description" style="margin-top: -10px; margin-bottom: 16px;">Редактируете контент для языка: <strong>' . esc_html($languages[$currentLang] ?? $currentLang) . '</strong></p>';
	}

	if (isset($_GET['updated']) && (string) $_GET['updated'] === '1') {
		echo '<div class="notice notice-success is-dismissible"><p>Сохранено.</p></div>';
	}
	if (isset($_GET['error'])) {
		$err = (string) $_GET['error'];
		if ($err === 'empty') {
			echo '<div class="notice notice-error is-dismissible"><p>Нельзя сохранить пустой шаг.</p></div>';
		} elseif ($err === 'not_found') {
			echo '<div class="notice notice-error is-dismissible"><p>Шаг не найден.</p></div>';
		}
	}

	$isEdit = ($action === 'edit');
	$isNew = ($action === 'new');

	if ($isEdit || $isNew) {
		$row = ['image_id' => 0, 'image_url' => '', 'title' => '', 'description' => ''];
		if ($isEdit) {
			if ($index < 0 || !array_key_exists($index, $blocks) || !is_array($blocks[$index])) {
				echo '<div class="notice notice-error is-dismissible"><p>Шаг не найден.</p></div>';
				$isEdit = false;
				$isNew = false;
			} else {
				$row = $blocks[$index];
			}
		}

		if ($isEdit || $isNew) {
			$imageId = absint($row['image_id'] ?? 0);
			$imageUrl = trim((string) ($row['image_url'] ?? ''));
			$title = (string) ($row['title'] ?? '');
			$description = (string) ($row['description'] ?? '');

			$previewUrl = '';
			if ($imageId > 0) {
				$previewUrl = (string) wp_get_attachment_image_url($imageId, 'medium');
			}
			if ($previewUrl === '' && $imageUrl !== '') {
				$previewUrl = $imageUrl;
			}

			$backUrl = add_query_arg(['page' => 'mosaic-work-process', 'lang' => $currentLang], admin_url('admin.php'));
			echo '<p><a href="' . esc_url($backUrl) . '" class="button">← Назад к списку</a></p>';

			$badge = $isNew ? 'NEW' : ('Шаг ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT));

			echo '<div class="mosaic-process-card">';
			echo '<div class="mosaic-process-card-header">';
			echo '<div>';
			echo '<p style="margin:0; font-size:16px; font-weight:600;">' . esc_html($isNew ? 'Добавить шаг' : 'Редактировать шаг') . '</p>';
			echo '<p style="margin:4px 0 0; opacity:.75;">Изменения сразу идут в секцию "Процесс работы" на главной.</p>';
			echo '</div>';
			echo '<div class="mosaic-process-badge">' . esc_html($badge) . '</div>';
			echo '</div>';
			echo '<div class="mosaic-process-card-body">';
			echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
			echo '<input type="hidden" name="action" value="mosaic_save_work_process_block">';
			echo '<input type="hidden" name="lang" value="' . esc_attr($currentLang) . '">';
			echo '<input type="hidden" name="index" value="' . esc_attr((string) $index) . '">';
			echo '<input type="hidden" name="is_new" value="' . esc_attr($isNew ? '1' : '0') . '">';
			wp_nonce_field('mosaic_work_process_save', 'mosaic_work_process_nonce');

			echo '<div class="mosaic-process-edit-grid">';
			echo '<div class="mosaic-process-uploader">';
			echo '<img id="mosaic-process-image-preview" src="' . esc_url($previewUrl) . '" alt="">';
			echo '<input type="hidden" id="mosaic_process_image_id" name="image_id" value="' . esc_attr((string) $imageId) . '">';
			echo '<input type="hidden" id="mosaic_process_image_url" name="image_url" value="' . esc_attr($imageUrl) . '">';
			echo '<div class="mosaic-process-edit-actions">';
			echo '<button type="button" class="button" id="mosaic-process-image-select">Выбрать</button>';
			echo '<button type="button" class="button" id="mosaic-process-image-remove">Удалить</button>';
			echo '</div>';
			echo '<p class="description">Картинка шага (лучше одинаковый формат у всех).</p>';
			echo '</div>';

			echo '<div>';
			echo '<p class="mosaic-process-field"><label class="mosaic-process-label">Наименование</label>';
			echo '<input type="text" class="mosaic-process-input" name="title" value="' . esc_attr($title) . '" placeholder="Например: Дизайн"></p>';
			echo '<p class="mosaic-process-field"><label class="mosaic-process-label">Описание</label>';
			echo '<textarea class="mosaic-process-textarea" name="description" placeholder="Текст">' . esc_textarea($description) . '</textarea></p>';
			echo '</div>';
			echo '</div>';

			submit_button('Сохранить');
			echo '</form>';
			echo '</div></div></div>';
			echo '</div>';
			return;
		}
	}

	echo '<p class="description">Сначала список шагов (таблица), потом редактирование каждого шага отдельно.</p>';
	$newUrl = add_query_arg(['page' => 'mosaic-work-process', 'action' => 'new', 'lang' => $currentLang], admin_url('admin.php'));
	echo '<p><a href="' . esc_url($newUrl) . '" class="button button-primary">+ Добавить шаг</a></p>';

	echo '<div id="mosaic-process-notices"></div>';
	echo '<div class="mosaic-process-table-wrap">';
	echo '<table class="widefat striped mosaic-process-table">';
	echo '<thead><tr>';
	echo '<th style="width:52px;"></th>';
	echo '<th style="width:70px;">#</th>';
	echo '<th style="width:140px;">Картинка</th>';
	echo '<th>Наименование</th>';
	echo '<th>Описание</th>';
	echo '<th style="width:240px;">Действия</th>';
	echo '</tr></thead>';
	echo '<tbody id="mosaic-process-sortable">';

	foreach ($blocks as $i => $row) {
		if (!is_array($row)) {
			continue;
		}
		$imageId = absint($row['image_id'] ?? 0);
		$imageUrl = trim((string) ($row['image_url'] ?? ''));
		$title = (string) ($row['title'] ?? '');
		$description = (string) ($row['description'] ?? '');

		$previewUrl = '';
		if ($imageId > 0) {
			$previewUrl = (string) wp_get_attachment_image_url($imageId, 'thumbnail');
		}
		if ($previewUrl === '' && $imageUrl !== '') {
			$previewUrl = $imageUrl;
		}

		$editUrl = add_query_arg(
			[
				'page' => 'mosaic-work-process',
				'action' => 'edit',
				'index' => (string) $i,
				'lang' => $currentLang,
			],
			admin_url('admin.php')
		);

		echo '<tr data-index="' . esc_attr((string) $i) . '">';
		echo '<td><span class="dashicons dashicons-menu mosaic-process-drag" aria-hidden="true"></span></td>';
		echo '<td><span data-mosaic-process-row-num>' . esc_html((string) ($i + 1)) . '</span></td>';
		echo '<td>';
		if ($previewUrl !== '') {
			echo '<img src="' . esc_url($previewUrl) . '" alt="" class="mosaic-process-thumb">';
		} else {
			echo '<span class="mosaic-process-muted">—</span>';
		}
		echo '</td>';
		echo '<td>' . ($title !== '' ? esc_html($title) : '<span class="mosaic-process-muted">—</span>') . '</td>';
		echo '<td>' . ($description !== '' ? esc_html(wp_trim_words($description, 18, '…')) : '<span class="mosaic-process-muted">—</span>') . '</td>';
		echo '<td><div class="mosaic-process-actions">';
		echo '<a class="button" href="' . esc_url($editUrl) . '">Редактировать</a>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block; margin-left:6px;" onsubmit="return confirm(\'Удалить шаг?\');">';
		echo '<input type="hidden" name="action" value="mosaic_delete_work_process_block">';
		echo '<input type="hidden" name="lang" value="' . esc_attr($currentLang) . '">';
		echo '<input type="hidden" name="index" value="' . esc_attr((string) $i) . '">';
		wp_nonce_field('mosaic_work_process_delete', 'mosaic_work_process_nonce');
		echo '<button type="submit" class="button button-link-delete">Удалить</button>';
		echo '</form>';
		echo '</div></td>';
		echo '</tr>';
	}

	echo '</tbody></table></div></div>';
}



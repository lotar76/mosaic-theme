<?php

declare(strict_types=1);

/**
 * Новости (Админка -> Новости).
 *
 * @return array{items:array<int,array{id:int,title:string,gallery_ids:array<int,int>,gallery_urls:array<int,string>,content:string,updated_at:int}>}
 */
function mosaic_get_news_defaults(): array {
	$base = get_template_directory_uri() . '/img/news';
	$now = time();
	$defaultText = 'Банальные, но неопровержимые выводы, а также независимые государства';

	$items = [];
	for ($i = 1; $i <= 5; $i++) {
		$img = $i === 5 ? 4 : $i;
		$items[] = [
			'id' => $i,
			'title' => 'Новость ' . $i,
			'gallery_ids' => [],
			'gallery_urls' => [$base . '/' . $img . '.png'],
			'content' => $defaultText,
			'updated_at' => $now,
		];
	}

	return ['items' => $items];
}

/**
 * @param mixed $row
 * @return array{id:int,title:string,gallery_ids:array<int,int>,gallery_urls:array<int,string>,content:string,updated_at:int}|null
 */
function mosaic_sanitize_news_item($row): ?array {
	if (!is_array($row)) {
		return null;
	}

	$id = absint($row['id'] ?? 0);
	if ($id <= 0) {
		return null;
	}

	$title = sanitize_text_field((string) ($row['title'] ?? ''));
	$contentRaw = (string) ($row['content'] ?? '');
	$content = wp_kses_post($contentRaw);

	$galleryIdsIn = $row['gallery_ids'] ?? [];
	if (!is_array($galleryIdsIn)) {
		$galleryIdsIn = [];
	}
	$galleryIds = [];
	foreach ($galleryIdsIn as $v) {
		$vid = absint($v);
		if ($vid > 0) {
			$galleryIds[] = $vid;
		}
	}
	$galleryIds = array_values(array_unique($galleryIds));

	$galleryUrlsIn = $row['gallery_urls'] ?? [];
	if (!is_array($galleryUrlsIn)) {
		$galleryUrlsIn = [];
	}
	$galleryUrls = [];
	foreach ($galleryUrlsIn as $v) {
		$url = trim((string) $v);
		$url = sanitize_text_field($url);
		$url = $url !== '' ? esc_url_raw($url) : '';
		if ($url !== '') {
			$galleryUrls[] = $url;
		}
	}
	$galleryUrls = array_values(array_unique($galleryUrls));

	$updatedAt = absint($row['updated_at'] ?? 0);
	if ($updatedAt <= 0) {
		$updatedAt = time();
	}

	$hasAny = ($title !== '') || ($content !== '') || (count($galleryIds) > 0) || (count($galleryUrls) > 0);
	if (!$hasAny) {
		return null;
	}

	return [
		'id' => $id,
		'title' => $title,
		'gallery_ids' => $galleryIds,
		'gallery_urls' => $galleryUrls,
		'content' => $content,
		'updated_at' => $updatedAt,
	];
}

/**
 * @param mixed $value
 * @return array{items:array<int,array{id:int,title:string,gallery_ids:array<int,int>,gallery_urls:array<int,string>,content:string,updated_at:int}>}
 */
function mosaic_sanitize_news_option($value): array {
	$defaults = mosaic_get_news_defaults();
	if (!is_array($value)) {
		return $defaults;
	}

	$itemsIn = $value['items'] ?? [];
	if (!is_array($itemsIn)) {
		return $defaults;
	}

	$items = [];
	$seen = [];
	foreach ($itemsIn as $row) {
		$item = mosaic_sanitize_news_item($row);
		if ($item === null) {
			continue;
		}
		$id = (int) ($item['id'] ?? 0);
		if ($id <= 0 || isset($seen[$id])) {
			continue;
		}
		$seen[$id] = true;
		$items[] = $item;
	}

	if (count($items) > 200) {
		$items = array_slice($items, 0, 200);
	}

	return ['items' => $items];
}

function mosaic_get_news(): array {
	$opt = get_option('mosaic_news', mosaic_get_news_defaults());
	return mosaic_sanitize_news_option($opt);
}

function mosaic_find_news_index_by_id(array $items, int $id): int {
	foreach ($items as $i => $item) {
		if ((int) ($item['id'] ?? 0) === $id) {
			return (int) $i;
		}
	}
	return -1;
}

function mosaic_generate_news_id(): int {
	return (int) (time() * 10000 + wp_rand(1000, 9999));
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$hook = add_menu_page(
			'Новости',
			'Новости',
			'edit_theme_options',
			'mosaic-news',
			'mosaic_render_news_page',
			'dashicons-megaphone',
			56
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			wp_enqueue_media();
			wp_register_script('mosaic-news-admin', false, ['jquery'], '1.0', true);
			wp_enqueue_script('mosaic-news-admin');

			$js = <<<'JS'
(function($){
  var frame;

  function parseIds(val){
    if (!val) return [];
    return String(val)
      .split(',')
      .map(function(x){ return parseInt(String(x).trim(), 10); })
      .filter(function(x){ return Number.isFinite(x) && x > 0; });
  }

  function setIds(ids){
    $('#mosaic_news_gallery_ids').val(ids.join(','));
    $('#mosaic-news-gallery-count').text(String(ids.length));
  }

  function setUrls(urls){
    $('#mosaic_news_gallery_urls').val(urls.join(','));
  }

  function setPreviewHtml(html){
    $('#mosaic-news-gallery-preview').html(html || '');
  }

  function openGallery(existingIds){
    frame = wp.media({
      title: 'Выбрать изображения галереи',
      button: { text: 'Использовать' },
      multiple: true,
      library: { type: 'image' }
    });

    frame.on('open', function(){
      var selection = frame.state().get('selection');
      existingIds.forEach(function(id){
        var att = wp.media.attachment(id);
        att.fetch();
        selection.add(att);
      });
    });

    frame.on('select', function(){
      var selection = frame.state().get('selection').toArray();
      var ids = selection.map(function(m){ return m.id; }).filter(Boolean);
      setIds(ids);
      setUrls([]); // выбранные из медиатеки -> только IDs

      var html = '';
      selection.forEach(function(m){
        var json = m.toJSON();
        var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
        if (!url) return;
        html += '<img src="' + url + '" alt="" class="mosaic-news-thumb">';
      });
      setPreviewHtml(html);
    });

    frame.open();
  }

  $(document).on('click', '#mosaic-news-gallery-select', function(e){
    e.preventDefault();
    var ids = parseIds($('#mosaic_news_gallery_ids').val());
    openGallery(ids);
  });

  $(document).on('click', '#mosaic-news-gallery-clear', function(e){
    e.preventDefault();
    setIds([]);
    setUrls([]);
    setPreviewHtml('');
  });
})(jQuery);
JS;

			wp_add_inline_script('mosaic-news-admin', $js);

			echo '<style>
				body.toplevel_page_mosaic-news .mosaic-news-card { max-width: 1200px; background:#fff; border:1px solid #dcdcde; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.06); overflow:hidden; }
				body.toplevel_page_mosaic-news .mosaic-news-card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 18px; background:linear-gradient(180deg,#101010 0%,#0b0b0b 100%); color:#fff; }
				body.toplevel_page_mosaic-news .mosaic-news-card-title { font-size:16px; font-weight:600; margin:0; }
				body.toplevel_page_mosaic-news .mosaic-news-card-subtitle { margin:4px 0 0; opacity:.75; }
				body.toplevel_page_mosaic-news .mosaic-news-badge { display:inline-flex; align-items:center; justify-content:center; min-width:56px; height:32px; padding:0 10px; border-radius:999px; background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.14); font-weight:600; letter-spacing:.02em; }
				body.toplevel_page_mosaic-news .mosaic-news-card-body { padding:18px; }
				body.toplevel_page_mosaic-news .mosaic-news-field { margin:0 0 12px; }
				body.toplevel_page_mosaic-news .mosaic-news-label { display:block; font-weight:600; margin:0 0 6px; }
				body.toplevel_page_mosaic-news .mosaic-news-input { width:100%; border-radius:12px; padding:10px 12px; border-color:#dcdcde; }
				body.toplevel_page_mosaic-news .mosaic-news-gallery { border:1px dashed #c3c4c7; border-radius:12px; padding:12px; background:#fafafa; }
				body.toplevel_page_mosaic-news .mosaic-news-gallery-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
				body.toplevel_page_mosaic-news .mosaic-news-gallery-actions .button { border-radius:10px; }
				body.toplevel_page_mosaic-news .mosaic-news-thumb { width:74px; height:74px; object-fit:cover; border-radius:10px; border:1px solid #dcdcde; background:#f6f7f7; margin:0 8px 8px 0; display:inline-block; }
				body.toplevel_page_mosaic-news .mosaic-news-table-wrap { width:100%; max-width:none; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.06); border:1px solid #dcdcde; background:#fff; }
				body.toplevel_page_mosaic-news .mosaic-news-table { width:100%; max-width:none; border:0; margin:0; table-layout:fixed; }
				body.toplevel_page_mosaic-news .mosaic-news-table thead th { background:linear-gradient(180deg,#101010 0%,#0b0b0b 100%); color:#fff; border-bottom:1px solid rgba(255,255,255,0.12); }
				body.toplevel_page_mosaic-news .mosaic-news-table td, body.toplevel_page_mosaic-news .mosaic-news-table th { padding:14px 14px; vertical-align:middle; }
				body.toplevel_page_mosaic-news .mosaic-news-table tbody tr:hover { background:#f6f7ff; }
				body.toplevel_page_mosaic-news .mosaic-news-actions { display:flex; gap:8px; flex-wrap:wrap; }
				body.toplevel_page_mosaic-news .mosaic-news-actions .button { border-radius:8px; }
				body.toplevel_page_mosaic-news .mosaic-news-actions .button.button-link-delete { color:#b32d2e; }
				body.toplevel_page_mosaic-news .mosaic-news-muted { color:#7a7a7a; }
			</style>';
		});
	});

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_news', null);
		if ($existing === false) {
			add_option('mosaic_news', mosaic_get_news_defaults(), '', false);
		}

		$seeded = get_option('mosaic_news_seeded_v1', null);
		if ($seeded !== '1') {
			$opt = mosaic_get_news();
			$items = is_array($opt['items'] ?? null) ? $opt['items'] : [];
			if (count($items) === 0) {
				update_option('mosaic_news', mosaic_get_news_defaults(), false);
			}
			add_option('mosaic_news_seeded_v1', '1', '', false);
		}
	});
}

add_action('admin_post_mosaic_save_news_item', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}
	check_admin_referer('mosaic_news_save', 'mosaic_news_nonce');

	$isNew = isset($_POST['is_new']) ? absint($_POST['is_new']) === 1 : false;
	$id = isset($_POST['id']) ? absint($_POST['id']) : 0;
	if ($isNew || $id <= 0) {
		$id = mosaic_generate_news_id();
	}

	$title = isset($_POST['title']) ? (string) $_POST['title'] : '';
	$content = isset($_POST['content']) ? (string) $_POST['content'] : '';
	$galleryIdsRaw = isset($_POST['gallery_ids']) ? (string) $_POST['gallery_ids'] : '';
	$galleryUrlsRaw = isset($_POST['gallery_urls']) ? (string) $_POST['gallery_urls'] : '';

	$galleryIds = [];
	foreach (preg_split('~\s*,\s*~', trim($galleryIdsRaw)) ?: [] as $part) {
		$vid = absint($part);
		if ($vid > 0) {
			$galleryIds[] = $vid;
		}
	}
	$galleryIds = array_values(array_unique($galleryIds));

	$galleryUrls = [];
	foreach (preg_split('~\s*,\s*~', trim($galleryUrlsRaw)) ?: [] as $part) {
		$url = trim((string) $part);
		if ($url === '') {
			continue;
		}
		$url = esc_url_raw(sanitize_text_field($url));
		if ($url !== '') {
			$galleryUrls[] = $url;
		}
	}
	$galleryUrls = array_values(array_unique($galleryUrls));

	$item = mosaic_sanitize_news_item([
		'id' => $id,
		'title' => $title,
		'gallery_ids' => $galleryIds,
		'gallery_urls' => $galleryUrls,
		'content' => $content,
		'updated_at' => time(),
	]);

	if ($item === null) {
		$redirect = add_query_arg(['page' => 'mosaic-news', 'error' => 'empty'], admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	$opt = mosaic_get_news();
	$items = is_array($opt['items'] ?? null) ? $opt['items'] : [];
	$idx = mosaic_find_news_index_by_id($items, $id);
	if ($idx >= 0) {
		$items[$idx] = $item;
	} else {
		$items[] = $item;
	}

	update_option('mosaic_news', mosaic_sanitize_news_option(['items' => $items]), false);

	$redirect = add_query_arg(['page' => 'mosaic-news', 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

add_action('admin_post_mosaic_delete_news_item', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}
	check_admin_referer('mosaic_news_delete', 'mosaic_news_nonce');

	$id = isset($_POST['id']) ? absint($_POST['id']) : 0;
	if ($id <= 0) {
		$redirect = add_query_arg(['page' => 'mosaic-news', 'error' => 'not_found'], admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	$opt = mosaic_get_news();
	$items = is_array($opt['items'] ?? null) ? $opt['items'] : [];
	$idx = mosaic_find_news_index_by_id($items, $id);
	if ($idx < 0) {
		$redirect = add_query_arg(['page' => 'mosaic-news', 'error' => 'not_found'], admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit;
	}

	unset($items[$idx]);
	$items = array_values($items);
	update_option('mosaic_news', mosaic_sanitize_news_option(['items' => $items]), false);

	$redirect = add_query_arg(['page' => 'mosaic-news', 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

function mosaic_render_news_page(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	$action = isset($_GET['action']) ? sanitize_key((string) $_GET['action']) : '';
	$id = isset($_GET['id']) ? absint($_GET['id']) : 0;

	$opt = mosaic_get_news();
	$items = is_array($opt['items'] ?? null) ? $opt['items'] : [];

	echo '<div class="wrap">';
	echo '<h1>Новости</h1>';

	if (isset($_GET['updated']) && (string) $_GET['updated'] === '1') {
		echo '<div class="notice notice-success is-dismissible"><p>Сохранено.</p></div>';
	}
	if (isset($_GET['error'])) {
		$err = (string) $_GET['error'];
		if ($err === 'empty') {
			echo '<div class="notice notice-error is-dismissible"><p>Нельзя сохранить пустую новость.</p></div>';
		} elseif ($err === 'not_found') {
			echo '<div class="notice notice-error is-dismissible"><p>Новость не найдена.</p></div>';
		}
	}

	$isEdit = ($action === 'edit' && $id > 0);
	$isNew = ($action === 'new');

	if ($isEdit || $isNew) {
		$item = ['id' => 0, 'title' => '', 'gallery_ids' => [], 'gallery_urls' => [], 'content' => '', 'updated_at' => time()];
		if ($isEdit) {
			$idx = mosaic_find_news_index_by_id($items, $id);
			if ($idx < 0) {
				echo '<div class="notice notice-error is-dismissible"><p>Новость не найдена.</p></div>';
				$isEdit = false;
				$isNew = false;
			} else {
				$item = $items[$idx];
			}
		}

		if ($isEdit || $isNew) {
			$title = (string) ($item['title'] ?? '');
			$content = (string) ($item['content'] ?? '');
			$galleryIds = is_array($item['gallery_ids'] ?? null) ? $item['gallery_ids'] : [];
			$galleryIdsStr = implode(',', array_map('intval', $galleryIds));
			$galleryUrls = is_array($item['gallery_urls'] ?? null) ? $item['gallery_urls'] : [];
			$galleryUrlsStr = implode(',', array_map('strval', $galleryUrls));
			$galleryCount = count($galleryIds) > 0 ? count($galleryIds) : count($galleryUrls);

			$backUrl = add_query_arg(['page' => 'mosaic-news'], admin_url('admin.php'));
			echo '<p><a href="' . esc_url($backUrl) . '" class="button">← Назад к списку</a></p>';

			echo '<div class="mosaic-news-card">';
			echo '<div class="mosaic-news-card-header">';
			echo '<div>';
			echo '<p class="mosaic-news-card-title">' . esc_html($isNew ? 'Добавить новость' : 'Редактировать новость') . '</p>';
			echo '<p class="mosaic-news-card-subtitle">Название + галерея + текст (редактор).</p>';
			echo '</div>';
			echo '<div class="mosaic-news-badge">' . esc_html($isNew ? 'NEW' : ('ID ' . (string) $id)) . '</div>';
			echo '</div><div class="mosaic-news-card-body">';

			echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
			echo '<input type="hidden" name="action" value="mosaic_save_news_item">';
			echo '<input type="hidden" name="is_new" value="' . esc_attr($isNew ? '1' : '0') . '">';
			echo '<input type="hidden" name="id" value="' . esc_attr((string) $id) . '">';
			wp_nonce_field('mosaic_news_save', 'mosaic_news_nonce');

			echo '<p class="mosaic-news-field"><label class="mosaic-news-label">Название</label>';
			echo '<input type="text" class="mosaic-news-input" name="title" value="' . esc_attr($title) . '" placeholder="Например: Новая коллекция"></p>';

			echo '<div class="mosaic-news-gallery">';
			echo '<input type="hidden" id="mosaic_news_gallery_ids" name="gallery_ids" value="' . esc_attr($galleryIdsStr) . '">';
			echo '<input type="hidden" id="mosaic_news_gallery_urls" name="gallery_urls" value="' . esc_attr($galleryUrlsStr) . '">';
			echo '<div><strong>Галерея</strong> <span class="mosaic-news-muted">(изображений: <span id="mosaic-news-gallery-count">' . esc_html((string) $galleryCount) . '</span>)</span></div>';
			echo '<div id="mosaic-news-gallery-preview" style="margin-top:10px;">';
			if (count($galleryIds) > 0) {
				foreach ($galleryIds as $gid) {
					$src = (string) wp_get_attachment_image_url((int) $gid, 'thumbnail');
					if ($src === '') {
						continue;
					}
					echo '<img src="' . esc_url($src) . '" alt="" class="mosaic-news-thumb">';
				}
			} else {
				foreach ($galleryUrls as $src) {
					$src = (string) $src;
					if ($src === '') {
						continue;
					}
					echo '<img src="' . esc_url($src) . '" alt="" class="mosaic-news-thumb">';
				}
			}
			echo '</div>';
			echo '<div class="mosaic-news-gallery-actions">';
			echo '<button type="button" class="button" id="mosaic-news-gallery-select">Выбрать галерею</button>';
			echo '<button type="button" class="button" id="mosaic-news-gallery-clear">Очистить</button>';
			echo '</div>';
			echo '<p class="description" style="margin-top:8px;">Можно выбрать несколько изображений. Порядок сохранится как выбран.</p>';
			echo '</div>';

			echo '<div style="margin-top:14px;">';
			echo '<label class="mosaic-news-label">Текст</label>';
			wp_editor(
				$content,
				'mosaic_news_content',
				[
					'textarea_name' => 'content',
					'media_buttons' => true,
					'teeny' => false,
					'quicktags' => true,
					'textarea_rows' => 12,
				]
			);
			echo '</div>';

			submit_button('Сохранить');
			echo '</form></div></div></div>';
			echo '</div>';
			return;
		}
	}

	echo '<p class="description">Список новостей (таблица), редактирование — отдельной карточкой.</p>';
	$newUrl = add_query_arg(['page' => 'mosaic-news', 'action' => 'new'], admin_url('admin.php'));
	echo '<p><a href="' . esc_url($newUrl) . '" class="button button-primary">+ Добавить новость</a></p>';

	echo '<div class="mosaic-news-table-wrap">';
	echo '<table class="widefat striped mosaic-news-table">';
	echo '<thead><tr>';
	echo '<th style="width:140px;">Превью</th>';
	echo '<th style="width:280px;">Название</th>';
	echo '<th>Текст</th>';
	echo '<th style="width:140px;">Галерея</th>';
	echo '<th style="width:240px;">Действия</th>';
	echo '</tr></thead><tbody>';

	foreach ($items as $it) {
		if (!is_array($it)) {
			continue;
		}
		$nid = (int) ($it['id'] ?? 0);
		$title = (string) ($it['title'] ?? '');
		$content = (string) ($it['content'] ?? '');
		$galleryIds = is_array($it['gallery_ids'] ?? null) ? $it['gallery_ids'] : [];
		$galleryUrls = is_array($it['gallery_urls'] ?? null) ? $it['gallery_urls'] : [];

		$thumb = '';
		if (count($galleryIds) > 0) {
			$thumb = (string) wp_get_attachment_image_url((int) $galleryIds[0], 'thumbnail');
		} elseif (count($galleryUrls) > 0) {
			$thumb = (string) $galleryUrls[0];
		}

		$galleryCount = count($galleryIds) > 0 ? count($galleryIds) : count($galleryUrls);

		$editUrl = add_query_arg(['page' => 'mosaic-news', 'action' => 'edit', 'id' => (string) $nid], admin_url('admin.php'));

		echo '<tr>';
		echo '<td>' . ($thumb !== '' ? '<img src="' . esc_url($thumb) . '" alt="" class="mosaic-news-thumb" style="width:120px; height:90px;">' : '<span class="mosaic-news-muted">—</span>') . '</td>';
		echo '<td>' . ($title !== '' ? esc_html($title) : '<span class="mosaic-news-muted">—</span>') . '</td>';
		echo '<td>' . ($content !== '' ? esc_html(wp_trim_words(wp_strip_all_tags($content), 22, '…')) : '<span class="mosaic-news-muted">—</span>') . '</td>';
		echo '<td>' . esc_html((string) $galleryCount) . '</td>';
		echo '<td><div class="mosaic-news-actions">';
		echo '<a class="button" href="' . esc_url($editUrl) . '">Редактировать</a>';
		echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;" onsubmit="return confirm(\'Удалить новость?\');">';
		echo '<input type="hidden" name="action" value="mosaic_delete_news_item">';
		echo '<input type="hidden" name="id" value="' . esc_attr((string) $nid) . '">';
		wp_nonce_field('mosaic_news_delete', 'mosaic_news_nonce');
		echo '<button type="submit" class="button button-link-delete">Удалить</button>';
		echo '</form>';
		echo '</div></td>';
		echo '</tr>';
	}

	echo '</tbody></table></div></div>';
}



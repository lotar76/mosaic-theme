<?php

declare(strict_types=1);

// Полностью отключаем Gutenberg для catalog_item, чтобы не было "главного редактора" вообще.
add_filter('use_block_editor_for_post_type', static function (bool $useBlockEditor, string $postType): bool {
	if ($postType === 'catalog_item') {
		return false;
	}
	return $useBlockEditor;
}, 10, 2);

/**
 * Catalog item meta keys.
 */
function mosaic_catalog_item_meta_keys(): array {
	return [
		'gallery_ids' => '_mosaic_catalog_gallery_ids',
		'material' => '_mosaic_catalog_material',
		'technique' => '_mosaic_catalog_technique',
		'size_color' => '_mosaic_catalog_size_color',
		'related_ids' => '_mosaic_catalog_related_ids',
	];
}

add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_catalog_item_details',
		'Параметры товара',
		static function (WP_Post $post): void {
			if ($post->post_type !== 'catalog_item') {
				return;
			}

			$k = mosaic_catalog_item_meta_keys();
			$title = (string) get_the_title($post);
			$description = (string) ($post->post_content ?? '');

			$galleryIds = get_post_meta($post->ID, $k['gallery_ids'], true);
			if (!is_array($galleryIds)) {
				$galleryIds = [];
			}
			$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

			$material = (string) get_post_meta($post->ID, $k['material'], true);
			$technique = (string) get_post_meta($post->ID, $k['technique'], true);
			$sizeColor = (string) get_post_meta($post->ID, $k['size_color'], true);

			$relatedIds = get_post_meta($post->ID, $k['related_ids'], true);
			if (!is_array($relatedIds)) {
				$relatedIds = [];
			}
			$relatedIds = array_values(array_filter(array_map('absint', $relatedIds), static fn($v) => $v > 0 && $v !== (int) $post->ID));

			wp_nonce_field('mosaic_catalog_item_save', 'mosaic_catalog_item_nonce');

			echo '<style>
				.mosaic-ci-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
				@media (max-width: 1020px){ .mosaic-ci-grid { grid-template-columns: 1fr; } }
				.mosaic-ci-field { margin: 0 0 12px; }
				.mosaic-ci-label { display:block; font-weight: 600; margin: 0 0 6px; }
				.mosaic-ci-input { width:100%; border-radius: 10px; padding: 8px 10px; }
				.mosaic-ci-gallery { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; }
				.mosaic-ci-thumbs { margin-top: 10px; display:flex; flex-wrap:wrap; gap: 8px; }
				.mosaic-ci-thumb { width:74px; height:74px; object-fit:cover; border-radius: 10px; border:1px solid #dcdcde; background:#f6f7f7; }
				.mosaic-ci-actions { margin-top: 10px; display:flex; gap: 8px; flex-wrap:wrap; }
				.mosaic-ci-actions .button { border-radius: 10px; }
				.mosaic-ci-related { border: 1px solid #dcdcde; border-radius: 12px; padding: 12px; background: #fff; }
				.mosaic-ci-chips { display:flex; flex-wrap:wrap; gap: 8px; margin-top: 10px; }
				.mosaic-ci-chip { display:inline-flex; align-items:center; gap: 8px; padding: 6px 10px; border-radius: 999px; border: 1px solid #dcdcde; background: #f6f7ff; }
				.mosaic-ci-chip button { border: 0; background: transparent; cursor: pointer; color: #b32d2e; font-weight: 700; }
				.mosaic-ci-search-results { margin-top: 10px; border: 1px solid #dcdcde; border-radius: 10px; overflow:hidden; }
				.mosaic-ci-search-item { padding: 10px 12px; border-top: 1px solid #eee; cursor:pointer; background:#fff; }
				.mosaic-ci-search-item:first-child { border-top: 0; }
				.mosaic-ci-search-item:hover { background: #f0f0f1; }
				.mosaic-ci-muted { color: #7a7a7a; }
			</style>';

			echo '<div class="mosaic-ci-field">';
			echo '<label class="mosaic-ci-label">Заголовок</label>';
			echo '<input class="mosaic-ci-input" type="text" name="mosaic_ci_title" value="' . esc_attr($title) . '" placeholder="Название товара">';
			echo '</div>';

			echo '<div class="mosaic-ci-field">';
			echo '<label class="mosaic-ci-label">Описание</label>';
			echo '<textarea class="mosaic-ci-input" name="mosaic_ci_description" rows="6" placeholder="Описание (обычный текст)">' . esc_textarea($description) . '</textarea>';
			echo '<p class="description">Это обычный текст (без Gutenberg/блоков).</p>';
			echo '</div>';

			echo '<div class="mosaic-ci-grid">';

			// Left: Gallery
			echo '<div>';
			echo '<div class="mosaic-ci-gallery">';
			echo '<div><strong>Галерея</strong> <span class="mosaic-ci-muted">(изображений: <span id="mosaic-ci-gallery-count">' . esc_html((string) count($galleryIds)) . '</span>)</span></div>';
			echo '<input type="hidden" id="mosaic_ci_gallery_ids" name="mosaic_ci_gallery_ids" value="' . esc_attr(implode(',', array_map('intval', $galleryIds))) . '">';
			echo '<div class="mosaic-ci-thumbs" id="mosaic-ci-gallery-thumbs">';
			foreach ($galleryIds as $gid) {
				$src = (string) wp_get_attachment_image_url((int) $gid, 'thumbnail');
				if ($src === '') {
					continue;
				}
				echo '<img src="' . esc_url($src) . '" alt="" class="mosaic-ci-thumb">';
			}
			echo '</div>';
			echo '<div class="mosaic-ci-actions">';
			echo '<button type="button" class="button" id="mosaic-ci-gallery-select">Выбрать галерею</button>';
			echo '<button type="button" class="button" id="mosaic-ci-gallery-clear">Очистить</button>';
			echo '</div>';
			echo '<p class="description" style="margin-top:8px;">Выбирай несколько изображений. Первая будет превью в списках/на главной.</p>';
			echo '</div>';
			echo '</div>';

			// Right: Fields + related
			echo '<div>';
			echo '<p class="mosaic-ci-field"><label class="mosaic-ci-label">Материал изделия</label>';
			echo '<input class="mosaic-ci-input" type="text" name="mosaic_ci_material" value="' . esc_attr($material) . '" placeholder="Например: художественное стекло"></p>';
			echo '<p class="mosaic-ci-field"><label class="mosaic-ci-label">Техника сборки</label>';
			echo '<input class="mosaic-ci-input" type="text" name="mosaic_ci_technique" value="' . esc_attr($technique) . '" placeholder="Например: наборная мозаика"></p>';
			echo '<p class="mosaic-ci-field"><label class="mosaic-ci-label">Размер и цветовая гамма</label>';
			echo '<input class="mosaic-ci-input" type="text" name="mosaic_ci_size_color" value="' . esc_attr($sizeColor) . '" placeholder="Например: 120×80 см, тёплая палитра"></p>';

			echo '<div class="mosaic-ci-related">';
			echo '<div><strong>Похожие товары</strong> <span class="mosaic-ci-muted">(выбери элементы, текущий исключается)</span></div>';
			echo '<input type="hidden" id="mosaic_ci_related_ids" name="mosaic_ci_related_ids" value="' . esc_attr(implode(',', array_map('intval', $relatedIds))) . '">';
			echo '<input type="hidden" id="mosaic_ci_post_id" value="' . esc_attr((string) $post->ID) . '">';
			echo '<input type="text" class="mosaic-ci-input" id="mosaic-ci-related-search" placeholder="Начни вводить название...">';
			echo '<div id="mosaic-ci-related-results" class="mosaic-ci-search-results" style="display:none;"></div>';
			echo '<div id="mosaic-ci-related-chips" class="mosaic-ci-chips">';
			foreach ($relatedIds as $rid) {
				$t = get_the_title($rid);
				if (!is_string($t) || $t === '') {
					continue;
				}
				echo '<span class="mosaic-ci-chip" data-id="' . esc_attr((string) $rid) . '">';
				echo '<span>' . esc_html($t) . '</span>';
				echo '<button type="button" aria-label="Удалить">×</button>';
				echo '</span>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';

			echo '</div>';
		},
		'catalog_item',
		'normal',
		'high'
	);
});

add_action('save_post_catalog_item', static function (int $postId): void {
	static $isUpdating = false;
	if ($isUpdating) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $postId)) {
		return;
	}
	if (!isset($_POST['mosaic_catalog_item_nonce']) || !wp_verify_nonce((string) $_POST['mosaic_catalog_item_nonce'], 'mosaic_catalog_item_save')) {
		return;
	}

	$k = mosaic_catalog_item_meta_keys();

	$title = isset($_POST['mosaic_ci_title']) ? sanitize_text_field((string) $_POST['mosaic_ci_title']) : '';
	$description = isset($_POST['mosaic_ci_description']) ? sanitize_textarea_field((string) $_POST['mosaic_ci_description']) : '';

	$isUpdating = true;
	wp_update_post(
		[
			'ID' => $postId,
			'post_title' => $title,
			'post_content' => $description,
		]
	);
	$isUpdating = false;

	$material = isset($_POST['mosaic_ci_material']) ? sanitize_text_field((string) $_POST['mosaic_ci_material']) : '';
	$technique = isset($_POST['mosaic_ci_technique']) ? sanitize_text_field((string) $_POST['mosaic_ci_technique']) : '';
	$sizeColor = isset($_POST['mosaic_ci_size_color']) ? sanitize_text_field((string) $_POST['mosaic_ci_size_color']) : '';

	update_post_meta($postId, $k['material'], $material);
	update_post_meta($postId, $k['technique'], $technique);
	update_post_meta($postId, $k['size_color'], $sizeColor);

	$galleryRaw = isset($_POST['mosaic_ci_gallery_ids']) ? (string) $_POST['mosaic_ci_gallery_ids'] : '';
	$gallery = [];
	foreach (preg_split('~\s*,\s*~', trim($galleryRaw)) ?: [] as $part) {
		$vid = absint($part);
		if ($vid > 0) {
			$gallery[] = $vid;
		}
	}
	$gallery = array_values(array_unique($gallery));
	update_post_meta($postId, $k['gallery_ids'], $gallery);

	$relatedRaw = isset($_POST['mosaic_ci_related_ids']) ? (string) $_POST['mosaic_ci_related_ids'] : '';
	$related = [];
	foreach (preg_split('~\s*,\s*~', trim($relatedRaw)) ?: [] as $part) {
		$rid = absint($part);
		if ($rid > 0 && $rid !== $postId) {
			$related[] = $rid;
		}
	}
	$related = array_values(array_unique($related));
	update_post_meta($postId, $k['related_ids'], $related);
});

add_action('wp_ajax_mosaic_catalog_search_items', static function (): void {
	if (!current_user_can('edit_posts')) {
		wp_send_json_error(['message' => 'forbidden'], 403);
	}
	check_ajax_referer('mosaic_catalog_search', 'nonce');

	$q = isset($_POST['q']) ? sanitize_text_field((string) $_POST['q']) : '';
	$exclude = isset($_POST['exclude']) ? absint($_POST['exclude']) : 0;

	if ($q === '' || mb_strlen($q) < 2) {
		wp_send_json_success(['items' => []]);
	}

	$posts = get_posts([
		'post_type' => 'catalog_item',
		'post_status' => 'publish',
		's' => $q,
		'posts_per_page' => 10,
		'exclude' => $exclude > 0 ? [$exclude] : [],
		'fields' => 'ids',
	]);

	$out = [];
	foreach ($posts as $pid) {
		$title = get_the_title((int) $pid);
		if (!is_string($title) || $title === '') {
			continue;
		}
		$out[] = ['id' => (int) $pid, 'title' => $title];
	}

	wp_send_json_success(['items' => $out]);
});

add_action('admin_enqueue_scripts', static function (string $hook): void {
	if ($hook !== 'post.php' && $hook !== 'post-new.php') {
		return;
	}
	$postType = '';
	if (isset($_GET['post_type'])) {
		$postType = sanitize_key((string) $_GET['post_type']);
	} elseif (isset($_GET['post'])) {
		$post = get_post(absint($_GET['post']));
		$postType = $post instanceof WP_Post ? (string) $post->post_type : '';
	}
	if ($postType !== 'catalog_item') {
		return;
	}

	wp_enqueue_media();
	wp_register_script('mosaic-catalog-item-admin', false, ['jquery'], '1.0', true);
	wp_enqueue_script('mosaic-catalog-item-admin');

	$cfg = wp_json_encode(
		[
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('mosaic_catalog_search'),
		],
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);

	$js = <<<'JS'
(function($){
  var CFG = __MOSAIC_CFG__;
  var frame;

  function parseIds(val){
    if (!val) return [];
    return String(val).split(',').map(function(x){ return parseInt(String(x).trim(), 10); }).filter(function(x){ return Number.isFinite(x) && x > 0; });
  }

  function setGallery(ids, selection){
    $('#mosaic_ci_gallery_ids').val(ids.join(','));
    $('#mosaic-ci-gallery-count').text(String(ids.length));

    var html = '';
    if (selection && selection.length) {
      selection.forEach(function(m){
        var json = m.toJSON();
        var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
        if (!url) return;
        html += '<img src="' + url + '" alt="" class="mosaic-ci-thumb">';
      });
    }
    $('#mosaic-ci-gallery-thumbs').html(html);
  }

  $(document).on('click', '#mosaic-ci-gallery-select', function(e){
    e.preventDefault();
    var existingIds = parseIds($('#mosaic_ci_gallery_ids').val());
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
      setGallery(ids, selection);
    });
    frame.open();
  });

  $(document).on('click', '#mosaic-ci-gallery-clear', function(e){
    e.preventDefault();
    setGallery([], []);
  });

  // Related items
  function getSelectedRelated(){
    return parseIds($('#mosaic_ci_related_ids').val());
  }
  function setSelectedRelated(ids){
    ids = ids.filter(function(id){ return id > 0; });
    ids = Array.from(new Set(ids));
    $('#mosaic_ci_related_ids').val(ids.join(','));
  }

  function addChip(id, title){
    var $chips = $('#mosaic-ci-related-chips');
    if ($chips.find('[data-id="' + id + '"]').length) return;
    var $chip = $('<span class="mosaic-ci-chip" data-id="'+id+'"><span></span><button type="button" aria-label="Удалить">×</button></span>');
    $chip.find('span').first().text(title);
    $chips.append($chip);
  }

  function removeChip(id){
    $('#mosaic-ci-related-chips').find('[data-id="' + id + '"]').remove();
    var ids = getSelectedRelated().filter(function(x){ return x !== id; });
    setSelectedRelated(ids);
  }

  $(document).on('click', '#mosaic-ci-related-chips .mosaic-ci-chip button', function(){
    var $chip = $(this).closest('.mosaic-ci-chip');
    var id = parseInt(String($chip.data('id') || ''), 10);
    if (!Number.isFinite(id)) return;
    removeChip(id);
  });

  var searchTimer = null;
  $(document).on('input', '#mosaic-ci-related-search', function(){
    var q = String($(this).val() || '').trim();
    var $results = $('#mosaic-ci-related-results');
    window.clearTimeout(searchTimer);
    if (q.length < 2) {
      $results.hide().empty();
      return;
    }
    searchTimer = window.setTimeout(function(){
      var exclude = parseInt(String($('#mosaic_ci_post_id').val() || ''), 10) || 0;
      $.post(CFG.ajaxUrl, { action: 'mosaic_catalog_search_items', nonce: CFG.nonce, q: q, exclude: exclude })
        .done(function(resp){
          if (!resp || !resp.success) { $results.hide().empty(); return; }
          var items = (resp.data && resp.data.items) ? resp.data.items : [];
          if (!items.length) { $results.hide().empty(); return; }
          var selected = new Set(getSelectedRelated());
          var html = '';
          items.forEach(function(it){
            if (!it || !it.id) return;
            if (selected.has(it.id)) return;
            html += '<div class="mosaic-ci-search-item" data-id="'+it.id+'" data-title="'+String(it.title||'').replace(/"/g,'&quot;')+'">'+it.title+'</div>';
          });
          if (!html) { $results.hide().empty(); return; }
          $results.html(html).show();
        })
        .fail(function(){ $results.hide().empty(); });
    }, 220);
  });

  $(document).on('click', '#mosaic-ci-related-results .mosaic-ci-search-item', function(){
    var id = parseInt(String($(this).data('id') || ''), 10);
    var title = String($(this).data('title') || '');
    if (!Number.isFinite(id) || !title) return;
    var ids = getSelectedRelated();
    ids.push(id);
    setSelectedRelated(ids);
    addChip(id, title);
    $('#mosaic-ci-related-results').hide().empty();
    $('#mosaic-ci-related-search').val('');
  });
})(jQuery);
JS;

	$js = str_replace('__MOSAIC_CFG__', $cfg, $js);
	wp_add_inline_script('mosaic-catalog-item-admin', $js);
});



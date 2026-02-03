<?php

declare(strict_types=1);

// Полностью отключаем Gutenberg для product, чтобы не было "главного редактора" вообще.
add_filter('use_block_editor_for_post_type', static function (bool $useBlockEditor, string $postType): bool {
	if ($postType === 'product') {
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
		'3dmax_url' => '_mosaic_catalog_3dmax_url',
	];
}

add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_catalog_item_details',
		'Параметры товара',
		static function (WP_Post $post): void {
			if ($post->post_type !== 'product') {
				return;
			}

			$k = mosaic_catalog_item_meta_keys();
			$description = (string) ($post->post_content ?? '');

			$galleryIds = get_post_meta($post->ID, $k['gallery_ids'], true);
			if (!is_array($galleryIds)) {
				$galleryIds = [];
			}
			$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

			$material = (string) get_post_meta($post->ID, $k['material'], true);
			$technique = (string) get_post_meta($post->ID, $k['technique'], true);
			$sizeColor = (string) get_post_meta($post->ID, $k['size_color'], true);
			$maxUrl = (string) get_post_meta($post->ID, $k['3dmax_url'], true);

			wp_nonce_field('mosaic_catalog_item_save', 'mosaic_catalog_item_nonce');

			echo '<style>
				.mosaic-ci-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
				@media (max-width: 1020px){ .mosaic-ci-grid { grid-template-columns: 1fr; } }
				.mosaic-ci-field { margin: 0 0 12px; }
				.mosaic-ci-label { display:block; font-weight: 600; margin: 0 0 6px; }
				.mosaic-ci-input { width:100%; border-radius: 10px; padding: 8px 10px; }
				.mosaic-ci-gallery { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; }
				.mosaic-ci-thumbs { margin-top: 10px; margin-bottom: 12px; display:flex; flex-wrap:wrap; gap: 8px; max-height:200px; overflow-y:auto; padding-right:4px; }
				.mosaic-ci-thumb { width:74px; height:74px; border-radius: 10px; border:1px solid #dcdcde; background:#f6f7f7; cursor:move; position:relative; overflow:hidden; flex-shrink:0; }
				.mosaic-ci-thumb:hover { border-color:#A36217; }
				.mosaic-ci-thumb.ui-sortable-helper { opacity:0.5; transform:scale(1.1); z-index:1000; }
				.mosaic-ci-thumb.ui-sortable-placeholder { border:2px dashed #A36217; background:#fffdf2; }
				.mosaic-ci-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
				.mosaic-ci-thumb-order { position:absolute; top:2px; left:2px; background:#A36217; color:#fff; font-size:10px; font-weight:bold; padding:2px 4px; border-radius:3px; line-height:1; z-index:10; pointer-events:none; }
				.mosaic-ci-thumb-remove { position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; font-size:14px; font-weight:bold; width:20px; height:20px; border-radius:50%; line-height:18px; text-align:center; cursor:pointer; z-index:10; border:none; padding:0; }
				.mosaic-ci-thumb-remove:hover { background:#d63638; }
				.mosaic-ci-actions { margin-top: 0; display:flex; gap: 8px; flex-wrap:wrap; }
				.mosaic-ci-actions .button { border-radius: 10px; }
				.mosaic-ci-muted { color: #7a7a7a; }
			</style>';

			echo '<div class="mosaic-ci-field">';
			echo '<label class="mosaic-ci-label">Описание</label>';
			echo '<textarea class="mosaic-ci-input" name="mosaic_ci_description" rows="6" placeholder="Описание (обычный текст)">' . esc_textarea($description) . '</textarea>';
			echo '<p class="description">Это обычный текст (без Gutenberg/блоков).</p>';
			echo '</div>';

			echo '<div class="mosaic-ci-grid">';

			// Left: Gallery
			// Фильтруем мёртвые ID (удалённые вложения)
			$validGalleryIds = [];
			foreach ($galleryIds as $gid) {
				$src = (string) wp_get_attachment_image_url((int) $gid, 'thumbnail');
				if ($src !== '') {
					$validGalleryIds[$gid] = $src;
				}
			}

			echo '<div>';
			echo '<div class="mosaic-ci-gallery">';
			echo '<div><strong>Галерея</strong> <span class="mosaic-ci-muted">(изображений: <span id="mosaic-ci-gallery-count">' . esc_html((string) count($validGalleryIds)) . '</span>)</span></div>';
			echo '<input type="hidden" id="mosaic_ci_gallery_ids" name="mosaic_ci_gallery_ids" value="' . esc_attr(implode(',', array_map('intval', array_keys($validGalleryIds)))) . '">';
			echo '<div class="mosaic-ci-thumbs" id="mosaic-ci-gallery-thumbs">';
			$orderNum = 0;
			foreach ($validGalleryIds as $gid => $src) {
				$orderNum++;
				echo '<div class="mosaic-ci-thumb" data-id="' . esc_attr((string) $gid) . '">';
				echo '<span class="mosaic-ci-thumb-order">' . esc_html((string) $orderNum) . '</span>';
				echo '<button type="button" class="mosaic-ci-thumb-remove" title="Удалить">&times;</button>';
				echo '<img src="' . esc_url($src) . '" alt="">';
				echo '</div>';
			}
			echo '</div>';
			echo '<div class="mosaic-ci-actions">';
			echo '<button type="button" class="button button-primary" id="mosaic-ci-gallery-add">Добавить изображения</button>';
			echo '<button type="button" class="button" id="mosaic-ci-gallery-select">Заменить всю галерею</button>';
			echo '<button type="button" class="button" id="mosaic-ci-gallery-clear">Очистить</button>';
			echo '</div>';
			echo '<p class="description" style="margin-top:8px;">Первое изображение будет превью в списках/на главной. Перетаскивайте для изменения порядка.</p>';
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
			echo '<p class="mosaic-ci-field"><label class="mosaic-ci-label">Ссылка на 3D Max</label>';
			echo '<input class="mosaic-ci-input" type="url" name="mosaic_ci_3dmax_url" value="' . esc_attr($maxUrl) . '" placeholder="https://example.com/3dmax-file"></p>';
			echo '</div>';

			echo '</div>';
		},
		'product',
		'normal',
		'high'
	);

	// Отдельный мета-бокс для похожих товаров
	add_meta_box(
		'mosaic_catalog_item_related',
		'Похожие товары',
		static function (WP_Post $post): void {
			if ($post->post_type !== 'product') {
				return;
			}

			$k = mosaic_catalog_item_meta_keys();
			$relatedIds = get_post_meta($post->ID, $k['related_ids'], true);
			if (!is_array($relatedIds)) {
				$relatedIds = [];
			}
			$relatedIds = array_values(array_filter(array_map('absint', $relatedIds), static fn($v) => $v > 0 && $v !== (int) $post->ID));

			echo '<style>
				.mosaic-related-box { }
				.mosaic-related-search { width:100%; border-radius: 8px; padding: 10px 12px; border: 1px solid #dcdcde; margin-bottom: 10px; }
				.mosaic-related-search:focus { border-color: #A36217; outline: none; box-shadow: 0 0 0 1px #A36217; }
				.mosaic-related-list { max-height: 300px; overflow-y: auto; border: 1px solid #dcdcde; border-radius: 10px; padding: 8px; display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; background: #fafafa; }
				.mosaic-related-item { border: 1px solid #dcdcde; border-radius: 8px; padding: 6px; cursor: pointer; background: #fff; transition: all 0.2s; }
				.mosaic-related-item:hover { border-color: #A36217; background: #fffdf2; }
				.mosaic-related-item.selected { border-color: #A36217; background: #fffdf2; }
				.mosaic-related-item img { width: 100%; height: 80px; object-fit: cover; border-radius: 6px; margin-bottom: 4px; background: #f0f0f1; }
				.mosaic-related-item-title { font-size: 11px; line-height: 1.3; color: #1d2327; text-align: center; word-break: break-word; }
				.mosaic-related-item-noimg { width: 100%; height: 80px; background: #f0f0f1; border-radius: 6px; margin-bottom: 4px; display: flex; align-items: center; justify-content: center; color: #7a7a7a; font-size: 10px; }
				.mosaic-related-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #dcdcde; }
				.mosaic-related-chips:empty { display: none; }
				.mosaic-related-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px 4px 4px; border-radius: 999px; border: 1px solid #A36217; background: #fffdf2; }
				.mosaic-related-chip-img { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; background: #e0e0e0; flex-shrink: 0; }
				.mosaic-related-chip-title { font-size: 12px; line-height: 1.2; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1d2327; }
				.mosaic-related-chip button { border: 0; background: transparent; cursor: pointer; color: #b32d2e; font-weight: 700; font-size: 14px; line-height: 1; padding: 0; margin-left: 2px; }
				.mosaic-related-loading { text-align: center; padding: 10px; color: #7a7a7a; display: none; }
				.mosaic-related-hint { color: #7a7a7a; font-size: 12px; margin-bottom: 10px; }
			</style>';

			echo '<div class="mosaic-related-box">';
			echo '<p class="mosaic-related-hint">Выбери товары, которые будут показаны как похожие. Текущий товар исключается автоматически.</p>';
			echo '<input type="hidden" id="mosaic_ci_related_ids" name="mosaic_ci_related_ids" value="' . esc_attr(implode(',', array_map('intval', $relatedIds))) . '">';
			echo '<input type="hidden" id="mosaic_ci_post_id" value="' . esc_attr((string) $post->ID) . '">';
			echo '<input type="text" class="mosaic-related-search" id="mosaic-ci-related-search" placeholder="Поиск по названию...">';
			echo '<div id="mosaic-ci-related-results" class="mosaic-ci-search-results" style="display:none;"></div>';
			echo '<div id="mosaic-ci-related-list" class="mosaic-related-list"></div>';
			echo '<div id="mosaic-ci-related-loading" class="mosaic-related-loading">Загрузка...</div>';
			echo '<div id="mosaic-ci-related-chips" class="mosaic-related-chips">';

			foreach ($relatedIds as $rid) {
				$t = get_the_title($rid);
				if (!is_string($t) || $t === '') {
					continue;
				}
				$chipThumb = '';
				$ridGallery = get_post_meta($rid, $k['gallery_ids'], true);
				if (is_array($ridGallery) && count($ridGallery) > 0) {
					$chipThumb = (string) wp_get_attachment_image_url((int) $ridGallery[0], 'thumbnail');
				}
				if ($chipThumb === '') {
					$maybe = get_the_post_thumbnail_url($rid, 'thumbnail');
					$chipThumb = is_string($maybe) ? $maybe : '';
				}

				echo '<span class="mosaic-related-chip" data-id="' . esc_attr((string) $rid) . '">';
				if ($chipThumb !== '') {
					echo '<img class="mosaic-related-chip-img" src="' . esc_url($chipThumb) . '" alt="">';
				} else {
					echo '<span class="mosaic-related-chip-img"></span>';
				}
				echo '<span class="mosaic-related-chip-title">' . esc_html($t) . '</span>';
				echo '<button type="button" aria-label="Удалить">×</button>';
				echo '</span>';
			}

			echo '</div>';
			echo '</div>';
		},
		'product',
		'normal',
		'default'
	);
});

add_action('save_post_product', static function (int $postId): void {
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

	$description = isset($_POST['mosaic_ci_description']) ? sanitize_textarea_field((string) $_POST['mosaic_ci_description']) : '';

	$isUpdating = true;
	wp_update_post(
		[
			'ID' => $postId,
			'post_content' => $description,
		]
	);
	$isUpdating = false;

	$material = isset($_POST['mosaic_ci_material']) ? sanitize_text_field((string) $_POST['mosaic_ci_material']) : '';
	$technique = isset($_POST['mosaic_ci_technique']) ? sanitize_text_field((string) $_POST['mosaic_ci_technique']) : '';
	$sizeColor = isset($_POST['mosaic_ci_size_color']) ? sanitize_text_field((string) $_POST['mosaic_ci_size_color']) : '';
	$maxUrl = isset($_POST['mosaic_ci_3dmax_url']) ? esc_url_raw((string) $_POST['mosaic_ci_3dmax_url']) : '';

	update_post_meta($postId, $k['material'], $material);
	update_post_meta($postId, $k['technique'], $technique);
	update_post_meta($postId, $k['size_color'], $sizeColor);
	update_post_meta($postId, $k['3dmax_url'], $maxUrl);

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
		'post_type' => 'product',
		'post_status' => 'publish',
		's' => $q,
		'posts_per_page' => 10,
		'exclude' => $exclude > 0 ? [$exclude] : [],
		'fields' => 'ids',
	]);

	$k = mosaic_catalog_item_meta_keys();
	$kGallery = (string) ($k['gallery_ids'] ?? '_mosaic_catalog_gallery_ids');

	$out = [];
	foreach ($posts as $pid) {
		$title = get_the_title((int) $pid);
		if (!is_string($title) || $title === '') {
			continue;
		}

		// Получаем картинку товара
		$thumbUrl = '';
		$galleryIds = get_post_meta((int) $pid, $kGallery, true);
		if (is_array($galleryIds) && count($galleryIds) > 0) {
			$thumbUrl = (string) wp_get_attachment_image_url((int) $galleryIds[0], 'thumbnail');
		}
		if ($thumbUrl === '') {
			$maybe = get_the_post_thumbnail_url((int) $pid, 'thumbnail');
			$thumbUrl = is_string($maybe) ? $maybe : '';
		}

		$out[] = ['id' => (int) $pid, 'title' => $title, 'thumb' => $thumbUrl];
	}

	wp_send_json_success(['items' => $out]);
});

add_action('wp_ajax_mosaic_catalog_list_items', static function (): void {
	if (!current_user_can('edit_posts')) {
		wp_send_json_error(['message' => 'forbidden'], 403);
	}
	check_ajax_referer('mosaic_catalog_search', 'nonce');

	$exclude = isset($_POST['exclude']) ? absint($_POST['exclude']) : 0;
	$page = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
	$perPage = 20;

	$posts = get_posts([
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => $perPage,
		'paged' => $page,
		'exclude' => $exclude > 0 ? [$exclude] : [],
		'orderby' => 'title',
		'order' => 'ASC',
		'fields' => 'ids',
	]);

	$total = wp_count_posts('product');
	$totalCount = (int) ($total->publish ?? 0);
	if ($exclude > 0) {
		$totalCount = max(0, $totalCount - 1);
	}

	$k = mosaic_catalog_item_meta_keys();
	$kGallery = (string) ($k['gallery_ids'] ?? '_mosaic_catalog_gallery_ids');

	$out = [];
	foreach ($posts as $pid) {
		$title = get_the_title((int) $pid);
		if (!is_string($title) || $title === '') {
			continue;
		}

		// Получаем первую картинку из галереи
		$galleryIds = get_post_meta((int) $pid, $kGallery, true);
		if (!is_array($galleryIds)) {
			$galleryIds = [];
		}
		$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));
		
		$thumbUrl = '';
		if (count($galleryIds) > 0) {
			$thumbUrl = (string) wp_get_attachment_image_url((int) $galleryIds[0], 'thumbnail');
		}
		if ($thumbUrl === '') {
			$maybe = get_the_post_thumbnail_url((int) $pid, 'thumbnail');
			$thumbUrl = is_string($maybe) ? $maybe : '';
		}

		$out[] = [
			'id' => (int) $pid,
			'title' => $title,
			'thumb' => $thumbUrl,
		];
	}

	wp_send_json_success([
		'items' => $out,
		'page' => $page,
		'perPage' => $perPage,
		'total' => $totalCount,
		'hasMore' => ($page * $perPage) < $totalCount,
	]);
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
	if ($postType !== 'product') {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script('jquery-ui-sortable');
	wp_register_script('mosaic-catalog-item-admin', false, ['jquery', 'jquery-ui-sortable'], '1.0', true);
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

  function updateGalleryOrder(){
    var $thumbs = $('#mosaic-ci-gallery-thumbs .mosaic-ci-thumb');
    var ids = [];
    $thumbs.each(function(idx){
      var id = parseInt(String($(this).data('id') || ''), 10);
      if (Number.isFinite(id) && id > 0) {
        ids.push(id);
        $(this).find('.mosaic-ci-thumb-order').text(String(idx + 1));
      }
    });
    $('#mosaic_ci_gallery_ids').val(ids.join(','));
    $('#mosaic-ci-gallery-count').text(String(ids.length));
  }

  function initGallerySortable(){
    $('#mosaic-ci-gallery-thumbs').sortable({
      items: '.mosaic-ci-thumb',
      cursor: 'move',
      opacity: 0.6,
      placeholder: 'mosaic-ci-thumb ui-sortable-placeholder',
      tolerance: 'pointer',
      update: function(){
        updateGalleryOrder();
      }
    });
  }

  function setGallery(ids, selection){
    $('#mosaic_ci_gallery_ids').val(ids.join(','));
    $('#mosaic-ci-gallery-count').text(String(ids.length));

    var html = '';
    if (selection && selection.length) {
      selection.forEach(function(m, idx){
        var json = m.toJSON();
        var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
        if (!url) return;
        var orderNum = idx + 1;
        html += '<div class="mosaic-ci-thumb" data-id="'+m.id+'">';
        html += '<span class="mosaic-ci-thumb-order">'+orderNum+'</span>';
        html += '<button type="button" class="mosaic-ci-thumb-remove" title="Удалить">&times;</button>';
        html += '<img src="' + url + '" alt="">';
        html += '</div>';
      });
    }
    $('#mosaic-ci-gallery-thumbs').html(html);
    initGallerySortable();
    updateGalleryOrder();
  }

  function addToGallery(newIds, newSelection){
    var existingIds = parseIds($('#mosaic_ci_gallery_ids').val());
    var $thumbs = $('#mosaic-ci-gallery-thumbs');

    newSelection.forEach(function(m){
      if (existingIds.indexOf(m.id) !== -1) return;
      existingIds.push(m.id);
      var json = m.toJSON();
      var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
      if (!url) return;
      var orderNum = existingIds.length;
      var html = '<div class="mosaic-ci-thumb" data-id="'+m.id+'">';
      html += '<span class="mosaic-ci-thumb-order">'+orderNum+'</span>';
      html += '<button type="button" class="mosaic-ci-thumb-remove" title="Удалить">&times;</button>';
      html += '<img src="' + url + '" alt="">';
      html += '</div>';
      $thumbs.append(html);
    });

    $('#mosaic_ci_gallery_ids').val(existingIds.join(','));
    $('#mosaic-ci-gallery-count').text(String(existingIds.length));
    initGallerySortable();
  }

  // Add images (append to existing)
  $(document).on('click', '#mosaic-ci-gallery-add', function(e){
    e.preventDefault();
    var existingIds = parseIds($('#mosaic_ci_gallery_ids').val());
    var addFrame = wp.media({
      title: 'Добавить изображения в галерею',
      button: { text: 'Добавить' },
      multiple: true,
      library: { type: 'image' }
    });
    addFrame.on('open', function(){
      var selection = addFrame.state().get('selection');
      existingIds.forEach(function(id){
        if (id > 0) {
          var att = wp.media.attachment(id);
          att.fetch();
          selection.add(att);
        }
      });
    });
    addFrame.on('select', function(){
      var selection = addFrame.state().get('selection').toArray();
      var ids = selection.map(function(m){ return m.id; }).filter(Boolean);
      addToGallery(ids, selection);
    });
    addFrame.open();
  });

  // Replace entire gallery
  $(document).on('click', '#mosaic-ci-gallery-select', function(e){
    e.preventDefault();
    frame = wp.media({
      title: 'Выбрать изображения галереи',
      button: { text: 'Использовать' },
      multiple: true,
      library: { type: 'image' }
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
    if (!confirm('Очистить всю галерею?')) return;
    setGallery([], []);
  });

  // Remove single image
  $(document).on('click', '.mosaic-ci-thumb-remove', function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).closest('.mosaic-ci-thumb').remove();
    updateGalleryOrder();
  });

  // Initialize sortable on page load
  $(document).ready(function(){
    if ($('#mosaic-ci-gallery-thumbs .mosaic-ci-thumb').length > 0) {
      initGallerySortable();
    }
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

  function addChip(id, title, thumb){
    var $chips = $('#mosaic-ci-related-chips');
    if ($chips.find('[data-id="' + id + '"]').length) return;
    var imgHtml = thumb
      ? '<img class="mosaic-related-chip-img" src="'+thumb+'" alt="">'
      : '<span class="mosaic-related-chip-img"></span>';
    var $chip = $('<span class="mosaic-related-chip" data-id="'+id+'">'+imgHtml+'<span class="mosaic-related-chip-title"></span><button type="button" aria-label="Удалить">×</button></span>');
    $chip.find('.mosaic-related-chip-title').text(title);
    $chips.append($chip);
    // Скрываем выбранный товар в списке
    $('#mosaic-ci-related-list').find('[data-id="' + id + '"]').hide();
  }

  function removeChip(id){
    $('#mosaic-ci-related-chips').find('[data-id="' + id + '"]').remove();
    var ids = getSelectedRelated().filter(function(x){ return x !== id; });
    setSelectedRelated(ids);
    // Показываем товар обратно в списке
    $('#mosaic-ci-related-list').find('[data-id="' + id + '"]').show();
  }

  $(document).on('click', '#mosaic-ci-related-chips .mosaic-related-chip button', function(){
    var $chip = $(this).closest('.mosaic-related-chip');
    var id = parseInt(String($chip.data('id') || ''), 10);
    if (!Number.isFinite(id)) return;
    removeChip(id);
  });

  // Load items list
  var currentListPage = 1;
  var isLoadingList = false;
  var hasMoreListItems = true;

  function loadItemsList(page, append){
    if (isLoadingList || !hasMoreListItems) return;
    isLoadingList = true;
    var exclude = parseInt(String($('#mosaic_ci_post_id').val() || ''), 10) || 0;
    var $list = $('#mosaic-ci-related-list');
    var $loading = $('#mosaic-ci-related-loading');
    
    if (!append) {
      $list.empty();
      currentListPage = 1;
    }
    
    $loading.show();
    
    $.post(CFG.ajaxUrl, { action: 'mosaic_catalog_list_items', nonce: CFG.nonce, exclude: exclude, page: page })
      .done(function(resp){
        if (!resp || !resp.success) {
          $loading.hide();
          isLoadingList = false;
          return;
        }
        var items = (resp.data && resp.data.items) ? resp.data.items : [];
        var selected = new Set(getSelectedRelated());
        var html = '';
        
        items.forEach(function(it){
          if (!it || !it.id) return;
          if (selected.has(it.id)) return;
          var thumb = it.thumb || '';
          var thumbHtml = thumb ? '<img src="'+thumb+'" alt="">' : '<div class="mosaic-related-item-noimg">Нет фото</div>';
          html += '<div class="mosaic-related-item" data-id="'+it.id+'" data-title="'+String(it.title||'').replace(/"/g,'&quot;')+'" data-thumb="'+String(thumb||'').replace(/"/g,'&quot;')+'">';
          html += thumbHtml;
          html += '<div class="mosaic-related-item-title">'+it.title+'</div>';
          html += '</div>';
        });
        
        if (html) {
          $list.append(html);
        }
        
        hasMoreListItems = resp.data.hasMore || false;
        currentListPage = page;
        $loading.hide();
        isLoadingList = false;
      })
      .fail(function(){
        $loading.hide();
        isLoadingList = false;
      });
  }

  // Load initial list
  loadItemsList(1, false);

  // Infinite scroll for list
  $('#mosaic-ci-related-list').on('scroll', function(){
    var $el = $(this);
    if ($el.scrollTop() + $el.innerHeight() >= $el[0].scrollHeight - 50) {
      if (!isLoadingList && hasMoreListItems) {
        loadItemsList(currentListPage + 1, true);
      }
    }
  });

  // Click on list item
  $(document).on('click', '#mosaic-ci-related-list .mosaic-related-item', function(){
    var id = parseInt(String($(this).data('id') || ''), 10);
    var title = String($(this).data('title') || '');
    var thumb = String($(this).data('thumb') || '');
    if (!Number.isFinite(id) || !title) return;
    var ids = getSelectedRelated();
    ids.push(id);
    setSelectedRelated(ids);
    addChip(id, title, thumb);
    $(this).addClass('selected');
    setTimeout(function(){
      $(this).removeClass('selected');
    }.bind(this), 500);
  });

  var searchTimer = null;
  $(document).on('input', '#mosaic-ci-related-search', function(){
    var q = String($(this).val() || '').trim();
    var $results = $('#mosaic-ci-related-results');
    var $list = $('#mosaic-ci-related-list');
    window.clearTimeout(searchTimer);
    
    if (q.length < 2) {
      $results.hide().empty();
      $list.show();
      return;
    }
    
    $list.hide();
    searchTimer = window.setTimeout(function(){
      var exclude = parseInt(String($('#mosaic_ci_post_id').val() || ''), 10) || 0;
      $.post(CFG.ajaxUrl, { action: 'mosaic_catalog_search_items', nonce: CFG.nonce, q: q, exclude: exclude })
        .done(function(resp){
          if (!resp || !resp.success) { $results.hide().empty(); $list.show(); return; }
          var items = (resp.data && resp.data.items) ? resp.data.items : [];
          if (!items.length) { $results.hide().empty(); $list.show(); return; }
          var selected = new Set(getSelectedRelated());
          var html = '';
          items.forEach(function(it){
            if (!it || !it.id) return;
            if (selected.has(it.id)) return;
            var thumb = it.thumb || '';
            html += '<div class="mosaic-ci-search-item" data-id="'+it.id+'" data-title="'+String(it.title||'').replace(/"/g,'&quot;')+'" data-thumb="'+String(thumb||'').replace(/"/g,'&quot;')+'">'+it.title+'</div>';
          });
          if (!html) { $results.hide().empty(); $list.show(); return; }
          $results.html(html).show();
        })
        .fail(function(){ $results.hide().empty(); $list.show(); });
    }, 220);
  });

  $(document).on('click', '#mosaic-ci-related-results .mosaic-ci-search-item', function(){
    var id = parseInt(String($(this).data('id') || ''), 10);
    var title = String($(this).data('title') || '');
    var thumb = String($(this).data('thumb') || '');
    if (!Number.isFinite(id) || !title) return;
    var ids = getSelectedRelated();
    ids.push(id);
    setSelectedRelated(ids);
    addChip(id, title, thumb);
    $('#mosaic-ci-related-results').hide().empty();
    $('#mosaic-ci-related-search').val('');
    $('#mosaic-ci-related-list').show();
  });
})(jQuery);
JS;

	$js = str_replace('__MOSAIC_CFG__', $cfg, $js);
	wp_add_inline_script('mosaic-catalog-item-admin', $js);
});

/**
 * Настройка колонок в таблице товаров
 */
add_filter('manage_product_posts_columns', static function (array $columns): array {
	// Создаём новый массив колонок в нужном порядке
	$newColumns = [];
	$newColumns['cb'] = $columns['cb'] ?? '<input type="checkbox" />';
	$newColumns['mosaic_thumb'] = 'Фото';
	$newColumns['title'] = 'Название';
	$newColumns['mosaic_slug'] = 'Slug';
	$newColumns['taxonomy-product_section'] = 'Раздел';
	$newColumns['date'] = $columns['date'] ?? 'Дата';

	return $newColumns;
});

/**
 * Рендер кастомных колонок
 */
add_action('manage_product_posts_custom_column', static function (string $column, int $postId): void {
	switch ($column) {
		case 'mosaic_thumb':
			$k = mosaic_catalog_item_meta_keys();
			$galleryIds = get_post_meta($postId, $k['gallery_ids'], true);
			$thumbUrl = '';

			if (is_array($galleryIds) && count($galleryIds) > 0) {
				$firstId = absint($galleryIds[0]);
				if ($firstId > 0) {
					$thumbUrl = (string) wp_get_attachment_image_url($firstId, 'thumbnail');
				}
			}

			// Fallback на featured image
			if ($thumbUrl === '') {
				$maybe = get_the_post_thumbnail_url($postId, 'thumbnail');
				$thumbUrl = is_string($maybe) ? $maybe : '';
			}

			if ($thumbUrl !== '') {
				echo '<img src="' . esc_url($thumbUrl) . '" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid #dcdcde;">';
			} else {
				echo '<span style="display:inline-block;width:50px;height:50px;background:#f0f0f1;border-radius:6px;border:1px solid #dcdcde;"></span>';
			}
			break;

		case 'mosaic_slug':
			$post = get_post($postId);
			if ($post instanceof WP_Post) {
				echo '<code style="background:#f6f7f7;padding:2px 6px;border-radius:3px;font-size:12px;">' . esc_html($post->post_name) . '</code>';
			}
			break;
	}
}, 10, 2);

/**
 * Делаем колонки сортируемыми
 */
add_filter('manage_edit-product_sortable_columns', static function (array $columns): array {
	$columns['mosaic_slug'] = 'name'; // Сортировка по post_name
	return $columns;
});

/**
 * Стили для таблицы товаров
 */
add_action('admin_head', static function (): void {
	$screen = get_current_screen();
	if (!$screen || $screen->id !== 'edit-product') {
		return;
	}

	echo '<style>
		.column-mosaic_thumb {
			width: 60px !important;
		}
		.column-mosaic_slug {
			width: 180px;
		}
		.column-taxonomy-product_section {
			width: 150px;
		}
		/* Выравниваем изображение по центру в ячейке */
		.wp-list-table .column-mosaic_thumb {
			vertical-align: middle;
		}
		/* Красивее отображаем название */
		.wp-list-table .column-title .row-title {
			font-weight: 600;
		}
	</style>';
});


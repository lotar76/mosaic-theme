<?php

declare(strict_types=1);

// Отключаем Gutenberg для news
add_filter('use_block_editor_for_post_type', static function (bool $useBlockEditor, string $postType): bool {
	if ($postType === 'news') {
		return false;
	}
	return $useBlockEditor;
}, 10, 2);

/**
 * News item meta keys.
 */
function mosaic_news_item_meta_keys(): array {
	return [
		'gallery_ids' => '_mosaic_news_gallery',
	];
}

add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_news_gallery',
		'Галерея новости',
		static function (WP_Post $post): void {
			if ($post->post_type !== 'news') {
				return;
			}

			$k = mosaic_news_item_meta_keys();

			$galleryIds = get_post_meta($post->ID, $k['gallery_ids'], true);
			if (!is_array($galleryIds)) {
				$galleryIds = [];
			}
			$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

			wp_nonce_field('mosaic_news_item_save', 'mosaic_news_item_nonce');

			echo '<style>
				.mosaic-news-gallery { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; }
				.mosaic-news-thumbs { margin-top: 10px; margin-bottom: 12px; display:flex; flex-wrap:wrap; gap: 8px; max-height:300px; overflow-y:auto; padding-right:4px; }
				.mosaic-news-thumb { width:100px; height:100px; border-radius: 10px; border:1px solid #dcdcde; background:#f6f7f7; cursor:move; position:relative; overflow:hidden; flex-shrink:0; }
				.mosaic-news-thumb:hover { border-color:#A36217; }
				.mosaic-news-thumb.ui-sortable-helper { opacity:0.5; transform:scale(1.1); z-index:1000; }
				.mosaic-news-thumb.ui-sortable-placeholder { border:2px dashed #A36217; background:#fffdf2; }
				.mosaic-news-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
				.mosaic-news-thumb-order { position:absolute; top:2px; left:2px; background:#A36217; color:#fff; font-size:10px; font-weight:bold; padding:2px 4px; border-radius:3px; line-height:1; z-index:10; pointer-events:none; }
				.mosaic-news-thumb-remove { position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; font-size:14px; font-weight:bold; width:20px; height:20px; border-radius:50%; line-height:18px; text-align:center; cursor:pointer; z-index:10; border:none; padding:0; }
				.mosaic-news-thumb-remove:hover { background:#d63638; }
				.mosaic-news-actions { margin-top: 0; display:flex; gap: 8px; flex-wrap:wrap; }
				.mosaic-news-actions .button { border-radius: 10px; }
				.mosaic-news-muted { color: #7a7a7a; }
			</style>';

			echo '<div class="mosaic-news-gallery">';
			echo '<div><strong>Галерея</strong> <span class="mosaic-news-muted">(изображений: <span id="mosaic-news-gallery-count">' . esc_html((string) count($galleryIds)) . '</span>)</span></div>';
			echo '<input type="hidden" id="mosaic_news_gallery_ids" name="mosaic_news_gallery_ids" value="' . esc_attr(implode(',', array_map('intval', $galleryIds))) . '">';
			echo '<div class="mosaic-news-thumbs" id="mosaic-news-gallery-thumbs">';
			foreach ($galleryIds as $idx => $gid) {
				$src = (string) wp_get_attachment_image_url((int) $gid, 'thumbnail');
				if ($src === '') {
					continue;
				}
				$orderNum = $idx + 1;
				echo '<div class="mosaic-news-thumb" data-id="' . esc_attr((string) $gid) . '">';
				echo '<span class="mosaic-news-thumb-order">' . esc_html((string) $orderNum) . '</span>';
				echo '<button type="button" class="mosaic-news-thumb-remove" title="Удалить">&times;</button>';
				echo '<img src="' . esc_url($src) . '" alt="">';
				echo '</div>';
			}
			echo '</div>';
			echo '<div class="mosaic-news-actions">';
			echo '<button type="button" class="button button-primary" id="mosaic-news-gallery-add">Добавить изображения</button>';
			echo '<button type="button" class="button" id="mosaic-news-gallery-select">Заменить всю галерею</button>';
			echo '<button type="button" class="button" id="mosaic-news-gallery-clear">Очистить</button>';
			echo '</div>';
			echo '<p class="description" style="margin-top:8px;">Перетаскивайте изображения для изменения порядка. Первое изображение будет превью в списке новостей.</p>';
			echo '</div>';
		},
		'news',
		'normal',
		'high'
	);
});

add_action('save_post_news', static function (int $postId): void {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $postId)) {
		return;
	}
	if (!isset($_POST['mosaic_news_item_nonce']) || !wp_verify_nonce((string) $_POST['mosaic_news_item_nonce'], 'mosaic_news_item_save')) {
		return;
	}

	$k = mosaic_news_item_meta_keys();

	$galleryRaw = isset($_POST['mosaic_news_gallery_ids']) ? (string) $_POST['mosaic_news_gallery_ids'] : '';
	$gallery = [];
	foreach (preg_split('~\s*,\s*~', trim($galleryRaw)) ?: [] as $part) {
		$vid = absint($part);
		if ($vid > 0) {
			$gallery[] = $vid;
		}
	}
	$gallery = array_values(array_unique($gallery));
	update_post_meta($postId, $k['gallery_ids'], $gallery);

	// Устанавливаем первое изображение как миниатюру
	if (count($gallery) > 0 && !has_post_thumbnail($postId)) {
		set_post_thumbnail($postId, $gallery[0]);
	}
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
	if ($postType !== 'news') {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script('jquery-ui-sortable');
	wp_register_script('mosaic-news-item-admin', false, ['jquery', 'jquery-ui-sortable'], '1.0', true);
	wp_enqueue_script('mosaic-news-item-admin');

	$js = <<<'JS'
(function($){
  var frame;
  var addFrame;

  function parseIds(val){
    if (!val) return [];
    return String(val).split(',').map(function(x){ return parseInt(String(x).trim(), 10); }).filter(function(x){ return Number.isFinite(x) && x > 0; });
  }

  function updateGalleryOrder(){
    var $thumbs = $('#mosaic-news-gallery-thumbs .mosaic-news-thumb');
    var ids = [];
    $thumbs.each(function(idx){
      var id = parseInt(String($(this).data('id') || ''), 10);
      if (Number.isFinite(id) && id > 0) {
        ids.push(id);
        $(this).find('.mosaic-news-thumb-order').text(String(idx + 1));
      }
    });
    $('#mosaic_news_gallery_ids').val(ids.join(','));
    $('#mosaic-news-gallery-count').text(String(ids.length));
  }

  function initGallerySortable(){
    $('#mosaic-news-gallery-thumbs').sortable({
      items: '.mosaic-news-thumb',
      cursor: 'move',
      opacity: 0.6,
      placeholder: 'mosaic-news-thumb ui-sortable-placeholder',
      tolerance: 'pointer',
      update: function(){
        updateGalleryOrder();
      }
    });
  }

  function setGallery(ids, selection){
    $('#mosaic_news_gallery_ids').val(ids.join(','));
    $('#mosaic-news-gallery-count').text(String(ids.length));

    var html = '';
    if (selection && selection.length) {
      selection.forEach(function(m, idx){
        var json = m.toJSON();
        var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
        if (!url) return;
        var orderNum = idx + 1;
        html += '<div class="mosaic-news-thumb" data-id="'+m.id+'">';
        html += '<span class="mosaic-news-thumb-order">'+orderNum+'</span>';
        html += '<button type="button" class="mosaic-news-thumb-remove" title="Удалить">&times;</button>';
        html += '<img src="' + url + '" alt="">';
        html += '</div>';
      });
    }
    $('#mosaic-news-gallery-thumbs').html(html);
    initGallerySortable();
  }

  function addToGallery(newIds, newSelection){
    var existingIds = parseIds($('#mosaic_news_gallery_ids').val());
    var $thumbs = $('#mosaic-news-gallery-thumbs');

    newSelection.forEach(function(m, idx){
      if (existingIds.indexOf(m.id) !== -1) return; // Skip duplicates
      existingIds.push(m.id);
      var json = m.toJSON();
      var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
      if (!url) return;
      var orderNum = existingIds.length;
      var html = '<div class="mosaic-news-thumb" data-id="'+m.id+'">';
      html += '<span class="mosaic-news-thumb-order">'+orderNum+'</span>';
      html += '<button type="button" class="mosaic-news-thumb-remove" title="Удалить">&times;</button>';
      html += '<img src="' + url + '" alt="">';
      html += '</div>';
      $thumbs.append(html);
    });

    $('#mosaic_news_gallery_ids').val(existingIds.join(','));
    $('#mosaic-news-gallery-count').text(String(existingIds.length));
    initGallerySortable();
  }

  // Add images (append to existing)
  $(document).on('click', '#mosaic-news-gallery-add', function(e){
    e.preventDefault();
    addFrame = wp.media({
      title: 'Добавить изображения в галерею',
      button: { text: 'Добавить' },
      multiple: true,
      library: { type: 'image' }
    });
    addFrame.on('select', function(){
      var selection = addFrame.state().get('selection').toArray();
      var ids = selection.map(function(m){ return m.id; }).filter(Boolean);
      addToGallery(ids, selection);
    });
    addFrame.open();
  });

  // Replace entire gallery
  $(document).on('click', '#mosaic-news-gallery-select', function(e){
    e.preventDefault();
    var existingIds = parseIds($('#mosaic_news_gallery_ids').val());
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

  // Clear gallery
  $(document).on('click', '#mosaic-news-gallery-clear', function(e){
    e.preventDefault();
    if (!confirm('Очистить всю галерею?')) return;
    setGallery([], []);
  });

  // Remove single image
  $(document).on('click', '.mosaic-news-thumb-remove', function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).closest('.mosaic-news-thumb').remove();
    updateGalleryOrder();
  });

  // Initialize sortable on page load
  $(document).ready(function(){
    if ($('#mosaic-news-gallery-thumbs .mosaic-news-thumb').length > 0) {
      initGallerySortable();
    }
  });
})(jQuery);
JS;

	wp_add_inline_script('mosaic-news-item-admin', $js);
});

/**
 * Настройка колонок в таблице новостей
 */
add_filter('manage_news_posts_columns', static function (array $columns): array {
	$newColumns = [];
	$newColumns['cb'] = $columns['cb'] ?? '<input type="checkbox" />';
	$newColumns['mosaic_thumb'] = 'Фото';
	$newColumns['title'] = 'Название';
	$newColumns['mosaic_images'] = 'Фото';
	$newColumns['date'] = $columns['date'] ?? 'Дата';

	return $newColumns;
});

/**
 * Рендер кастомных колонок
 */
add_action('manage_news_posts_custom_column', static function (string $column, int $postId): void {
	switch ($column) {
		case 'mosaic_thumb':
			$k = mosaic_news_item_meta_keys();
			$galleryIds = get_post_meta($postId, $k['gallery_ids'], true);
			$thumbUrl = '';

			if (is_array($galleryIds) && count($galleryIds) > 0) {
				$firstId = absint($galleryIds[0]);
				if ($firstId > 0) {
					$thumbUrl = (string) wp_get_attachment_image_url($firstId, 'thumbnail');
				}
			}

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

		case 'mosaic_images':
			$k = mosaic_news_item_meta_keys();
			$galleryIds = get_post_meta($postId, $k['gallery_ids'], true);
			$count = is_array($galleryIds) ? count($galleryIds) : 0;
			echo '<span style="color:#7a7a7a;">' . esc_html((string) $count) . '</span>';
			break;
	}
}, 10, 2);

/**
 * Стили для таблицы новостей
 */
add_action('admin_head', static function (): void {
	$screen = get_current_screen();
	if (!$screen || $screen->id !== 'edit-news') {
		return;
	}

	echo '<style>
		.column-mosaic_thumb {
			width: 60px !important;
		}
		.column-mosaic_images {
			width: 60px;
		}
		.wp-list-table .column-mosaic_thumb {
			vertical-align: middle;
		}
		.wp-list-table .column-title .row-title {
			font-weight: 600;
		}
	</style>';
});

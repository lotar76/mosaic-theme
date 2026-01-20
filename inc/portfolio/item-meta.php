<?php

declare(strict_types=1);

// Отключаем Gutenberg для portfolio
add_filter('use_block_editor_for_post_type', static function (bool $useBlockEditor, string $postType): bool {
	if ($postType === 'portfolio') {
		return false;
	}
	return $useBlockEditor;
}, 10, 2);

/**
 * Portfolio item meta keys.
 */
function mosaic_portfolio_item_meta_keys(): array {
	return [
		'gallery_ids' => '_mosaic_portfolio_gallery',
		'pdf_file_id' => '_mosaic_portfolio_pdf_file',
	];
}

add_action('add_meta_boxes', static function (): void {
	add_meta_box(
		'mosaic_portfolio_gallery',
		'Галерея проекта',
		static function (WP_Post $post): void {
			if ($post->post_type !== 'portfolio') {
				return;
			}

			$k = mosaic_portfolio_item_meta_keys();

			$galleryIds = get_post_meta($post->ID, $k['gallery_ids'], true);
			if (!is_array($galleryIds)) {
				$galleryIds = [];
			}
			$galleryIds = array_values(array_filter(array_map('absint', $galleryIds), static fn($v) => $v > 0));

			// PDF файл
			$pdfFileId = (int) get_post_meta($post->ID, $k['pdf_file_id'], true);
			$pdfUrl = '';
			$pdfFilename = '';

			if ($pdfFileId > 0) {
				$pdfUrl = wp_get_attachment_url($pdfFileId);
				$pdfFilename = basename(get_attached_file($pdfFileId));
			}

			wp_nonce_field('mosaic_portfolio_item_save', 'mosaic_portfolio_item_nonce');

			echo '<style>
				.mosaic-pf-container { display: flex; flex-direction: column; gap: 16px; }
				.mosaic-pf-gallery { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; }
				.mosaic-pf-thumbs { margin-top: 10px; margin-bottom: 12px; display:flex; flex-wrap:wrap; gap: 8px; max-height:300px; overflow-y:auto; padding-right:4px; }
				.mosaic-pf-thumb { width:100px; height:100px; border-radius: 10px; border:1px solid #dcdcde; background:#f6f7f7; cursor:move; position:relative; overflow:hidden; flex-shrink:0; }
				.mosaic-pf-thumb:hover { border-color:#A36217; }
				.mosaic-pf-thumb.ui-sortable-helper { opacity:0.5; transform:scale(1.1); z-index:1000; }
				.mosaic-pf-thumb.ui-sortable-placeholder { border:2px dashed #A36217; background:#fffdf2; }
				.mosaic-pf-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
				.mosaic-pf-thumb-order { position:absolute; top:2px; left:2px; background:#A36217; color:#fff; font-size:10px; font-weight:bold; padding:2px 4px; border-radius:3px; line-height:1; z-index:10; pointer-events:none; }
				.mosaic-pf-thumb-remove { position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; font-size:14px; font-weight:bold; width:20px; height:20px; border-radius:50%; line-height:18px; text-align:center; cursor:pointer; z-index:10; border:none; padding:0; }
				.mosaic-pf-thumb-remove:hover { background:#d63638; }
				.mosaic-pf-actions { margin-top: 0; display:flex; gap: 8px; flex-wrap:wrap; }
				.mosaic-pf-actions .button { border-radius: 10px; }
				.mosaic-pf-muted { color: #7a7a7a; }
				.mosaic-pf-pdf-box { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; }
				.mosaic-pf-pdf-preview { margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; display: none; }
				.mosaic-pf-pdf-preview.has-file { display: block; }
				.mosaic-pf-pdf-info { display: flex; align-items: center; gap: 10px; }
				.mosaic-pf-pdf-icon { width: 40px; height: 40px; background: #dc3232; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 12px; }
				.mosaic-pf-pdf-details { flex: 1; }
				.mosaic-pf-pdf-name { font-weight: 600; color: #2c3338; margin-bottom: 2px; }
			</style>';

			echo '<div class="mosaic-pf-container">';

			// PDF файл
			echo '<div class="mosaic-pf-pdf-box">';
			echo '<div><strong>PDF файл проекта</strong></div>';
			echo '<input type="hidden" id="mosaic_pf_pdf_file_id" name="mosaic_pf_pdf_file_id" value="' . esc_attr((string) $pdfFileId) . '">';

			echo '<div class="mosaic-pf-pdf-preview' . ($pdfFileId > 0 ? ' has-file' : '') . '" id="mosaic-pf-pdf-preview">';
			echo '<div class="mosaic-pf-pdf-info">';
			echo '<div class="mosaic-pf-pdf-icon">PDF</div>';
			echo '<div class="mosaic-pf-pdf-details">';
			echo '<div class="mosaic-pf-pdf-name" id="mosaic-pf-pdf-name">' . esc_html($pdfFilename) . '</div>';
			echo '<a href="' . esc_url($pdfUrl) . '" target="_blank" id="mosaic-pf-pdf-link" style="font-size:12px;">Открыть файл</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';

			echo '<div class="mosaic-pf-actions">';
			echo '<button type="button" class="button button-primary" id="mosaic-pf-pdf-upload">Загрузить PDF</button>';
			echo '<button type="button" class="button" id="mosaic-pf-pdf-remove" style="' . ($pdfFileId > 0 ? '' : 'display:none;') . '">Удалить</button>';
			echo '</div>';
			echo '<p class="description" style="margin-top:8px;">Загрузите PDF файл. При клике на проект на странице портфолио файл откроется в новой вкладке.</p>';
			echo '</div>';

			// Галерея
			echo '<div class="mosaic-pf-gallery">';
			echo '<div><strong>Галерея</strong> <span class="mosaic-pf-muted">(изображений: <span id="mosaic-pf-gallery-count">' . esc_html((string) count($galleryIds)) . '</span>)</span></div>';
			echo '<input type="hidden" id="mosaic_pf_gallery_ids" name="mosaic_pf_gallery_ids" value="' . esc_attr(implode(',', array_map('intval', $galleryIds))) . '">';
			echo '<div class="mosaic-pf-thumbs" id="mosaic-pf-gallery-thumbs">';
			foreach ($galleryIds as $idx => $gid) {
				$src = (string) wp_get_attachment_image_url((int) $gid, 'thumbnail');
				if ($src === '') {
					continue;
				}
				$orderNum = $idx + 1;
				echo '<div class="mosaic-pf-thumb" data-id="' . esc_attr((string) $gid) . '">';
				echo '<span class="mosaic-pf-thumb-order">' . esc_html((string) $orderNum) . '</span>';
				echo '<button type="button" class="mosaic-pf-thumb-remove" title="Удалить">&times;</button>';
				echo '<img src="' . esc_url($src) . '" alt="">';
				echo '</div>';
			}
			echo '</div>';
			echo '<div class="mosaic-pf-actions">';
			echo '<button type="button" class="button button-primary" id="mosaic-pf-gallery-add">Добавить изображения</button>';
			echo '<button type="button" class="button" id="mosaic-pf-gallery-select">Заменить всю галерею</button>';
			echo '<button type="button" class="button" id="mosaic-pf-gallery-clear">Очистить</button>';
			echo '</div>';
			echo '<p class="description" style="margin-top:8px;">Перетаскивайте изображения для изменения порядка. Первое изображение будет превью в списке портфолио.</p>';
			echo '</div>';

			echo '</div>';
		},
		'portfolio',
		'normal',
		'high'
	);
});

add_action('save_post_portfolio', static function (int $postId): void {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $postId)) {
		return;
	}
	if (!isset($_POST['mosaic_portfolio_item_nonce']) || !wp_verify_nonce((string) $_POST['mosaic_portfolio_item_nonce'], 'mosaic_portfolio_item_save')) {
		return;
	}

	$k = mosaic_portfolio_item_meta_keys();

	// Сохранение PDF файла
	$pdfFileId = isset($_POST['mosaic_pf_pdf_file_id']) ? absint($_POST['mosaic_pf_pdf_file_id']) : 0;
	if ($pdfFileId > 0) {
		// Проверяем, что это действительно PDF файл
		$filePath = get_attached_file($pdfFileId);
		if ($filePath && file_exists($filePath)) {
			$fileType = wp_check_filetype($filePath);
			if ($fileType['type'] === 'application/pdf') {
				update_post_meta($postId, $k['pdf_file_id'], $pdfFileId);
			}
		}
	} else {
		delete_post_meta($postId, $k['pdf_file_id']);
	}

	$galleryRaw = isset($_POST['mosaic_pf_gallery_ids']) ? (string) $_POST['mosaic_pf_gallery_ids'] : '';
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
	if ($postType !== 'portfolio') {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script('jquery-ui-sortable');
	wp_register_script('mosaic-portfolio-item-admin', false, ['jquery', 'jquery-ui-sortable'], '1.0', true);
	wp_enqueue_script('mosaic-portfolio-item-admin');

	$js = <<<'JS'
(function($){
  var frame;
  var addFrame;
  var pdfFrame;

  function parseIds(val){
    if (!val) return [];
    return String(val).split(',').map(function(x){ return parseInt(String(x).trim(), 10); }).filter(function(x){ return Number.isFinite(x) && x > 0; });
  }

  function updateGalleryOrder(){
    var $thumbs = $('#mosaic-pf-gallery-thumbs .mosaic-pf-thumb');
    var ids = [];
    $thumbs.each(function(idx){
      var id = parseInt(String($(this).data('id') || ''), 10);
      if (Number.isFinite(id) && id > 0) {
        ids.push(id);
        $(this).find('.mosaic-pf-thumb-order').text(String(idx + 1));
      }
    });
    $('#mosaic_pf_gallery_ids').val(ids.join(','));
    $('#mosaic-pf-gallery-count').text(String(ids.length));
  }

  function initGallerySortable(){
    $('#mosaic-pf-gallery-thumbs').sortable({
      items: '.mosaic-pf-thumb',
      cursor: 'move',
      opacity: 0.6,
      placeholder: 'mosaic-pf-thumb ui-sortable-placeholder',
      tolerance: 'pointer',
      update: function(){
        updateGalleryOrder();
      }
    });
  }

  function setGallery(ids, selection){
    $('#mosaic_pf_gallery_ids').val(ids.join(','));
    $('#mosaic-pf-gallery-count').text(String(ids.length));

    var html = '';
    if (selection && selection.length) {
      selection.forEach(function(m, idx){
        var json = m.toJSON();
        var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
        if (!url) return;
        var orderNum = idx + 1;
        html += '<div class="mosaic-pf-thumb" data-id="'+m.id+'">';
        html += '<span class="mosaic-pf-thumb-order">'+orderNum+'</span>';
        html += '<button type="button" class="mosaic-pf-thumb-remove" title="Удалить">&times;</button>';
        html += '<img src="' + url + '" alt="">';
        html += '</div>';
      });
    }
    $('#mosaic-pf-gallery-thumbs').html(html);
    initGallerySortable();
  }

  function addToGallery(newIds, newSelection){
    var existingIds = parseIds($('#mosaic_pf_gallery_ids').val());
    var $thumbs = $('#mosaic-pf-gallery-thumbs');

    newSelection.forEach(function(m, idx){
      if (existingIds.indexOf(m.id) !== -1) return; // Skip duplicates
      existingIds.push(m.id);
      var json = m.toJSON();
      var url = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) ? json.sizes.thumbnail.url : (json.url || '');
      if (!url) return;
      var orderNum = existingIds.length;
      var html = '<div class="mosaic-pf-thumb" data-id="'+m.id+'">';
      html += '<span class="mosaic-pf-thumb-order">'+orderNum+'</span>';
      html += '<button type="button" class="mosaic-pf-thumb-remove" title="Удалить">&times;</button>';
      html += '<img src="' + url + '" alt="">';
      html += '</div>';
      $thumbs.append(html);
    });

    $('#mosaic_pf_gallery_ids').val(existingIds.join(','));
    $('#mosaic-pf-gallery-count').text(String(existingIds.length));
    initGallerySortable();
  }

  // Add images (append to existing)
  $(document).on('click', '#mosaic-pf-gallery-add', function(e){
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
  $(document).on('click', '#mosaic-pf-gallery-select', function(e){
    e.preventDefault();
    var existingIds = parseIds($('#mosaic_pf_gallery_ids').val());
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
  $(document).on('click', '#mosaic-pf-gallery-clear', function(e){
    e.preventDefault();
    if (!confirm('Очистить всю галерею?')) return;
    setGallery([], []);
  });

  // Remove single image
  $(document).on('click', '.mosaic-pf-thumb-remove', function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).closest('.mosaic-pf-thumb').remove();
    updateGalleryOrder();
  });

  // PDF file upload
  $(document).on('click', '#mosaic-pf-pdf-upload', function(e){
    e.preventDefault();
    pdfFrame = wp.media({
      title: 'Выбрать PDF файл',
      button: { text: 'Использовать' },
      multiple: false,
      library: { type: 'application/pdf' }
    });
    pdfFrame.on('select', function(){
      var attachment = pdfFrame.state().get('selection').first().toJSON();
      if (attachment.id && attachment.subtype === 'pdf') {
        $('#mosaic_pf_pdf_file_id').val(attachment.id);
        $('#mosaic-pf-pdf-name').text(attachment.filename || 'file.pdf');
        $('#mosaic-pf-pdf-link').attr('href', attachment.url);
        $('#mosaic-pf-pdf-preview').addClass('has-file');
        $('#mosaic-pf-pdf-remove').show();
      }
    });
    pdfFrame.open();
  });

  // PDF file remove
  $(document).on('click', '#mosaic-pf-pdf-remove', function(e){
    e.preventDefault();
    if (!confirm('Удалить PDF файл?')) return;
    $('#mosaic_pf_pdf_file_id').val('');
    $('#mosaic-pf-pdf-preview').removeClass('has-file');
    $('#mosaic-pf-pdf-remove').hide();
  });

  // Initialize sortable on page load
  $(document).ready(function(){
    if ($('#mosaic-pf-gallery-thumbs .mosaic-pf-thumb').length > 0) {
      initGallerySortable();
    }
  });
})(jQuery);
JS;

	wp_add_inline_script('mosaic-portfolio-item-admin', $js);
});

/**
 * Настройка колонок в таблице портфолио
 */
add_filter('manage_portfolio_posts_columns', static function (array $columns): array {
	$newColumns = [];
	$newColumns['cb'] = $columns['cb'] ?? '<input type="checkbox" />';
	$newColumns['mosaic_thumb'] = 'Фото';
	$newColumns['title'] = 'Название';
	$newColumns['taxonomy-portfolio_category'] = 'Раздел';
	$newColumns['mosaic_images'] = 'Фото';
	$newColumns['date'] = $columns['date'] ?? 'Дата';

	return $newColumns;
});

/**
 * Рендер кастомных колонок
 */
add_action('manage_portfolio_posts_custom_column', static function (string $column, int $postId): void {
	switch ($column) {
		case 'mosaic_thumb':
			$k = mosaic_portfolio_item_meta_keys();
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
			$k = mosaic_portfolio_item_meta_keys();
			$galleryIds = get_post_meta($postId, $k['gallery_ids'], true);
			$count = is_array($galleryIds) ? count($galleryIds) : 0;
			echo '<span style="color:#7a7a7a;">' . esc_html((string) $count) . '</span>';
			break;
	}
}, 10, 2);

/**
 * Стили для таблицы портфолио
 */
add_action('admin_head', static function (): void {
	$screen = get_current_screen();
	if (!$screen || $screen->id !== 'edit-portfolio') {
		return;
	}

	echo '<style>
		.column-mosaic_thumb {
			width: 60px !important;
		}
		.column-mosaic_images {
			width: 60px;
		}
		.column-taxonomy-portfolio_category {
			width: 150px;
		}
		.wp-list-table .column-mosaic_thumb {
			vertical-align: middle;
		}
		.wp-list-table .column-title .row-title {
			font-weight: 600;
		}
	</style>';
});

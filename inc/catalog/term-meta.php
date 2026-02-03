<?php

declare(strict_types=1);

/**
 * Максимальный размер видео для разделов каталога (байты).
 * 8MB — чтобы не раздувать загрузки/ховер-эффект на фронте.
 */
function mosaic_catalog_term_video_max_bytes(): int {
	return 8 * 1024 * 1024;
}

/**
 * Фактический лимит: не можем превышать серверный лимит загрузки WP/PHP.
 */
function mosaic_catalog_term_video_effective_max_bytes(): int {
	$serverMax = function_exists('wp_max_upload_size') ? (int) wp_max_upload_size() : 0;
	if ($serverMax <= 0) {
		return mosaic_catalog_term_video_max_bytes();
	}

	return min(mosaic_catalog_term_video_max_bytes(), $serverMax);
}

function mosaic_catalog_term_image_meta_key(): string {
	return 'mosaic_cat_image_id';
}

function mosaic_catalog_term_video_meta_key(): string {
	return 'mosaic_cat_video_url';
}

function mosaic_catalog_term_video_id_meta_key(): string {
	return 'mosaic_cat_video_id';
}

function mosaic_catalog_term_interior_image_meta_key(): string {
	return 'mosaic_cat_interior_image_id';
}

/**
 * Проверка размера выбранного видео-attachment.
 */
function mosaic_catalog_video_attachment_is_valid(int $attachmentId): bool {
	if ($attachmentId <= 0) {
		return false;
	}

	$mime = (string) get_post_mime_type($attachmentId);
	if ($mime === '' || strpos($mime, 'video/') !== 0) {
		return false;
	}

	$file = (string) get_attached_file($attachmentId);
	if ($file === '' || !is_file($file)) {
		return false;
	}

	$size = @filesize($file);
	if (!is_int($size) || $size <= 0) {
		return false;
	}

	return $size <= mosaic_catalog_term_video_effective_max_bytes();
}

if (is_admin()) {
	/**
	 * Уведомления по валидации term meta.
	 */
	add_action('admin_notices', static function (): void {
		if (!current_user_can('manage_categories')) {
			return;
		}
		$tax = isset($_GET['taxonomy']) ? sanitize_key((string) $_GET['taxonomy']) : '';
		if ($tax !== 'product_section') {
			return;
		}
		$userId = get_current_user_id();
		if ($userId <= 0) {
			return;
		}
		$key = 'mosaic_cat_video_notice_' . $userId;
		$notice = get_transient($key);
		if (!is_string($notice) || $notice === '') {
			return;
		}
		delete_transient($key);

		echo '<div class="notice notice-error is-dismissible"><p>' . wp_kses_post($notice) . '</p></div>';
	});

	add_action('product_section_add_form_fields', static function (): void {
		$maxMb = (int) ceil(mosaic_catalog_term_video_effective_max_bytes() / (1024 * 1024));
		$serverMb = function_exists('wp_max_upload_size') ? (int) ceil(((int) wp_max_upload_size()) / (1024 * 1024)) : 0;

		echo '<div class="form-field">';
		echo '<label>Картинка раздела</label>';
		echo '<input type="hidden" id="mosaic_cat_image_id" name="mosaic_cat_image_id" value="0">';
		echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
		echo '<img id="mosaic-cat-image-preview" src="" style="width:180px; height:120px; object-fit:cover; border:1px solid #dcdcde; background:#f6f7f7; display:none;" alt="">';
		echo '<div style="display:flex; flex-direction:column; gap:8px;">';
		echo '<button type="button" class="button" id="mosaic-cat-image-select">Выбрать</button>';
		echo '<button type="button" class="button" id="mosaic-cat-image-remove" style="display:none;">Удалить</button>';
		echo '</div></div>';
		echo '<p class="description">Картинка для карточки раздела каталога.</p>';
		echo '</div>';

		echo '<div class="form-field">';
		echo '<label>Картинка в интерьере</label>';
		echo '<input type="hidden" id="mosaic_cat_interior_image_id" name="mosaic_cat_interior_image_id" value="0">';
		echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
		echo '<img id="mosaic-cat-interior-image-preview" src="" style="width:180px; height:120px; object-fit:cover; border:1px solid #dcdcde; background:#f6f7f7; display:none;" alt="">';
		echo '<div style="display:flex; flex-direction:column; gap:8px;">';
		echo '<button type="button" class="button" id="mosaic-cat-interior-image-select">Выбрать</button>';
		echo '<button type="button" class="button" id="mosaic-cat-interior-image-remove" style="display:none;">Удалить</button>';
		echo '</div></div>';
		echo '<p class="description">Если у раздела нет видео — на hover будет показываться эта картинка.</p>';
		echo '</div>';

		echo '<div class="form-field">';
		echo '<label>Видео (mp4 из медиатеки)</label>';
		echo '<input type="hidden" id="mosaic_cat_video_id" name="mosaic_cat_video_id" value="0">';
		echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
		echo '<input type="text" class="regular-text" id="mosaic-cat-video-url-preview" value="" readonly placeholder="Видео не выбрано" style="max-width: 420px;">';
		echo '<div style="display:flex; flex-direction:column; gap:8px;">';
		echo '<button type="button" class="button" id="mosaic-cat-video-select">Выбрать видео</button>';
		echo '<button type="button" class="button" id="mosaic-cat-video-remove" style="display:none;">Удалить</button>';
		echo '</div></div>';
		echo '<p class="description">Опционально. Лимит: <strong>' . esc_html((string) $maxMb) . 'MB</strong>' . ($serverMb > 0 ? ' (сервер: ' . esc_html((string) $serverMb) . 'MB)' : '') . '. Если видео выбрано — на hover показываем видео. Если нет видео и выбрана “картинка в интерьере” — показываем её.</p>';
		echo '</div>';
	});

	add_action('product_section_edit_form_fields', static function ($term): void {
		if (!($term instanceof WP_Term)) {
			return;
		}

		$maxMb = (int) ceil(mosaic_catalog_term_video_effective_max_bytes() / (1024 * 1024));
		$serverMb = function_exists('wp_max_upload_size') ? (int) ceil(((int) wp_max_upload_size()) / (1024 * 1024)) : 0;

		$imageId = (int) get_term_meta($term->term_id, mosaic_catalog_term_image_meta_key(), true);
		$videoId = (int) get_term_meta($term->term_id, mosaic_catalog_term_video_id_meta_key(), true);
		$interiorImageId = (int) get_term_meta($term->term_id, mosaic_catalog_term_interior_image_meta_key(), true);
		$img = $imageId > 0 ? (string) wp_get_attachment_image_url($imageId, 'medium') : '';
		$interiorImg = $interiorImageId > 0 ? (string) wp_get_attachment_image_url($interiorImageId, 'medium') : '';
		$videoUrl = $videoId > 0 ? (string) wp_get_attachment_url($videoId) : '';

		echo '<tr class="form-field"><th scope="row"><label>Картинка раздела</label></th><td>';
		echo '<input type="hidden" id="mosaic_cat_image_id" name="mosaic_cat_image_id" value="' . esc_attr((string) $imageId) . '">';
		echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
		echo '<img id="mosaic-cat-image-preview" src="' . esc_url($img) . '" style="width:180px; height:120px; object-fit:cover; border:1px solid #dcdcde; background:#f6f7f7;' . ($img !== '' ? '' : 'display:none;') . '" alt="">';
		echo '<div style="display:flex; flex-direction:column; gap:8px;">';
		echo '<button type="button" class="button" id="mosaic-cat-image-select">Выбрать</button>';
		echo '<button type="button" class="button" id="mosaic-cat-image-remove" style="' . ($img !== '' ? '' : 'display:none;') . '">Удалить</button>';
		echo '</div></div>';
		echo '<p class="description">Картинка для карточки раздела каталога.</p>';
		echo '</td></tr>';

		echo '<tr class="form-field"><th scope="row"><label>Картинка в интерьере</label></th><td>';
		echo '<input type="hidden" id="mosaic_cat_interior_image_id" name="mosaic_cat_interior_image_id" value="' . esc_attr((string) $interiorImageId) . '">';
		echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
		echo '<img id="mosaic-cat-interior-image-preview" src="' . esc_url($interiorImg) . '" style="width:180px; height:120px; object-fit:cover; border:1px solid #dcdcde; background:#f6f7f7;' . ($interiorImg !== '' ? '' : 'display:none;') . '" alt="">';
		echo '<div style="display:flex; flex-direction:column; gap:8px;">';
		echo '<button type="button" class="button" id="mosaic-cat-interior-image-select">Выбрать</button>';
		echo '<button type="button" class="button" id="mosaic-cat-interior-image-remove" style="' . ($interiorImg !== '' ? '' : 'display:none;') . '">Удалить</button>';
		echo '</div></div>';
		echo '<p class="description">Если у раздела нет видео — на hover будет показываться эта картинка.</p>';
		echo '</td></tr>';

		echo '<tr class="form-field"><th scope="row"><label>Видео (mp4 из медиатеки)</label></th><td>';
		echo '<input type="hidden" id="mosaic_cat_video_id" name="mosaic_cat_video_id" value="' . esc_attr((string) $videoId) . '">';
		echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
		echo '<input type="text" class="regular-text" id="mosaic-cat-video-url-preview" value="' . esc_attr($videoUrl) . '" readonly placeholder="Видео не выбрано" style="max-width: 420px;">';
		echo '<div style="display:flex; flex-direction:column; gap:8px;">';
		echo '<button type="button" class="button" id="mosaic-cat-video-select">Выбрать видео</button>';
		echo '<button type="button" class="button" id="mosaic-cat-video-remove" style="' . ($videoUrl !== '' ? '' : 'display:none;') . '">Удалить</button>';
		echo '</div></div>';
		echo '<p class="description">Опционально. Лимит: <strong>' . esc_html((string) $maxMb) . 'MB</strong>' . ($serverMb > 0 ? ' (сервер: ' . esc_html((string) $serverMb) . 'MB)' : '') . '. Если видео выбрано — на hover показываем видео. Если нет видео и выбрана "картинка в интерьере" — показываем её.</p>';
		echo '</td></tr>';
	});

	add_action('created_product_section', static function (int $termId): void {
		if (isset($_POST['mosaic_cat_image_id'])) {
			update_term_meta($termId, mosaic_catalog_term_image_meta_key(), absint($_POST['mosaic_cat_image_id']));
		}
		if (isset($_POST['mosaic_cat_interior_image_id'])) {
			update_term_meta($termId, mosaic_catalog_term_interior_image_meta_key(), absint($_POST['mosaic_cat_interior_image_id']));
		}
		if (isset($_POST['mosaic_cat_video_id'])) {
			$videoId = absint($_POST['mosaic_cat_video_id']);
			if ($videoId > 0 && !mosaic_catalog_video_attachment_is_valid($videoId)) {
				delete_term_meta($termId, mosaic_catalog_term_video_id_meta_key());
				$userId = get_current_user_id();
				if ($userId > 0) {
					$maxMb = (int) ceil(mosaic_catalog_term_video_effective_max_bytes() / (1024 * 1024));
					set_transient(
						'mosaic_cat_video_notice_' . $userId,
						'Видео слишком большое или не является видеофайлом. Максимум: <strong>' . esc_html((string) $maxMb) . 'MB</strong>.',
						60
					);
				}
			} else {
				update_term_meta($termId, mosaic_catalog_term_video_id_meta_key(), $videoId);
			}
		}
	});

	add_action('edited_product_section', static function (int $termId): void {
		if (isset($_POST['mosaic_cat_image_id'])) {
			update_term_meta($termId, mosaic_catalog_term_image_meta_key(), absint($_POST['mosaic_cat_image_id']));
		}
		if (isset($_POST['mosaic_cat_interior_image_id'])) {
			update_term_meta($termId, mosaic_catalog_term_interior_image_meta_key(), absint($_POST['mosaic_cat_interior_image_id']));
		}
		if (isset($_POST['mosaic_cat_video_id'])) {
			$videoId = absint($_POST['mosaic_cat_video_id']);
			if ($videoId > 0 && !mosaic_catalog_video_attachment_is_valid($videoId)) {
				delete_term_meta($termId, mosaic_catalog_term_video_id_meta_key());
				$userId = get_current_user_id();
				if ($userId > 0) {
					$maxMb = (int) ceil(mosaic_catalog_term_video_effective_max_bytes() / (1024 * 1024));
					set_transient(
						'mosaic_cat_video_notice_' . $userId,
						'Видео слишком большое или не является видеофайлом. Максимум: <strong>' . esc_html((string) $maxMb) . 'MB</strong>.',
						60
					);
				}
			} else {
				update_term_meta($termId, mosaic_catalog_term_video_id_meta_key(), $videoId);
			}
		}
	});

	add_action('admin_enqueue_scripts', static function (string $hook): void {
		if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
			return;
		}
		$tax = isset($_GET['taxonomy']) ? sanitize_key((string) $_GET['taxonomy']) : '';
		if ($tax !== 'product_section') {
			return;
		}

		wp_enqueue_media();

		$maxBytes = mosaic_catalog_term_video_effective_max_bytes();
		$js = <<<'JS'
(function($){
  var frameImage;
  var frameInterior;
  var frameVideo;

  function setImagePreview(url){
    var $img = $('#mosaic-cat-image-preview');
    if (url) {
      $img.attr('src', url).show();
      $('#mosaic-cat-image-remove').show();
    } else {
      $img.attr('src','').hide();
      $('#mosaic-cat-image-remove').hide();
    }
  }

  function setInteriorPreview(url){
    var $img = $('#mosaic-cat-interior-image-preview');
    if (url) {
      $img.attr('src', url).show();
      $('#mosaic-cat-interior-image-remove').show();
    } else {
      $img.attr('src','').hide();
      $('#mosaic-cat-interior-image-remove').hide();
    }
  }

  function setVideoPreview(url){
    var $input = $('#mosaic-cat-video-url-preview');
    $input.val(url || '');
    if (url) {
      $('#mosaic-cat-video-remove').show();
    } else {
      $('#mosaic-cat-video-remove').hide();
    }
  }

  $(document).on('click', '#mosaic-cat-image-select', function(e){
    e.preventDefault();
    frameImage = wp.media({ title: 'Выбрать картинку раздела', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
    frameImage.on('open', function(){
      var selection = frameImage.state().get('selection');
      var existingId = parseInt($('#mosaic_cat_image_id').val());
      if (existingId > 0) {
        var att = wp.media.attachment(existingId);
        att.fetch();
        selection.reset([att]);
      } else {
        selection.reset([]);
      }
    });
    frameImage.on('select', function(){
      var a = frameImage.state().get('selection').first().toJSON();
      $('#mosaic_cat_image_id').val(a.id || 0);
      setImagePreview(a.url || '');
    });
    frameImage.open();
  });
  $(document).on('click', '#mosaic-cat-image-remove', function(e){
    e.preventDefault();
    $('#mosaic_cat_image_id').val(0);
    setImagePreview('');
  });

  $(document).on('click', '#mosaic-cat-interior-image-select', function(e){
    e.preventDefault();
    frameInterior = wp.media({ title: 'Выбрать картинку в интерьере', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
    frameInterior.on('open', function(){
      var selection = frameInterior.state().get('selection');
      var existingId = parseInt($('#mosaic_cat_interior_image_id').val());
      if (existingId > 0) {
        var att = wp.media.attachment(existingId);
        att.fetch();
        selection.reset([att]);
      } else {
        selection.reset([]);
      }
    });
    frameInterior.on('select', function(){
      var a = frameInterior.state().get('selection').first().toJSON();
      $('#mosaic_cat_interior_image_id').val(a.id || 0);
      setInteriorPreview(a.url || '');
    });
    frameInterior.open();
  });
  $(document).on('click', '#mosaic-cat-interior-image-remove', function(e){
    e.preventDefault();
    $('#mosaic_cat_interior_image_id').val(0);
    setInteriorPreview('');
  });

  $(document).on('click', '#mosaic-cat-video-select', function(e){
    e.preventDefault();
    frameVideo = wp.media({ title: 'Выбрать видео (mp4)', button: { text: 'Использовать' }, multiple: false, library: { type: 'video' } });
    frameVideo.on('open', function(){
      var selection = frameVideo.state().get('selection');
      var existingId = parseInt($('#mosaic_cat_video_id').val());
      if (existingId > 0) {
        var att = wp.media.attachment(existingId);
        att.fetch();
        selection.reset([att]);
      } else {
        selection.reset([]);
      }
    });
    frameVideo.on('select', function(){
      var a = frameVideo.state().get('selection').first().toJSON();
      var maxBytes = __MOSAIC_MAX_VIDEO_BYTES__;
      var size = Number(a.filesizeInBytes || 0);
      if (size && size > maxBytes) {
        var mb = Math.ceil(maxBytes / (1024 * 1024));
        alert('Видео слишком большое. Максимум: ' + mb + 'MB.');
        $('#mosaic_cat_video_id').val(0);
        setVideoPreview('');
        return;
      }
      $('#mosaic_cat_video_id').val(a.id || 0);
      setVideoPreview(a.url || '');
    });
    frameVideo.open();
  });
  $(document).on('click', '#mosaic-cat-video-remove', function(e){
    e.preventDefault();
    $('#mosaic_cat_video_id').val(0);
    setVideoPreview('');
  });

  $(function(){
    // init state for remove buttons on edit screen
    var url = String($('#mosaic-cat-video-url-preview').val() || '').trim();
    setVideoPreview(url);
  });
})(jQuery);
JS;
		$js = str_replace('__MOSAIC_MAX_VIDEO_BYTES__', (string) $maxBytes, $js);
		wp_add_inline_script('jquery', $js, 'after');
	});

	// Одноразовая миграция: удаляем legacy video URL meta для всех разделов каталога.
	add_action('admin_init', static function (): void {
		$optionKey = 'mosaic_cat_video_legacy_cleanup_done';
		if (get_option($optionKey) === '1') {
			return;
		}

		$terms = get_terms(['taxonomy' => 'product_section', 'hide_empty' => false]);
		if (!is_wp_error($terms) && is_array($terms)) {
			foreach ($terms as $term) {
				if ($term instanceof WP_Term) {
					delete_term_meta($term->term_id, mosaic_catalog_term_video_meta_key());
				}
			}
		}
		update_option($optionKey, '1', false);
	});
}

/**
 * Карточки разделов каталога для фронта (главная/страница каталога).
 *
 * @return array<int, array{title:string,url:string,slug:string,image_url:string,interior_image_url:string,video_url:string}>
 */
function mosaic_get_catalog_category_cards(): array {
	$terms = get_terms([
		'taxonomy' => 'product_section',
		'hide_empty' => false,
	]);
	if (is_wp_error($terms) || !is_array($terms) || count($terms) === 0) {
		return [];
	}

	// Для fallback-изображений по слагу используем текущий хардкод.
	$legacyMap = [];
	if (function_exists('mosaic_get_catalog_categories')) {
		foreach (mosaic_get_catalog_categories() as $row) {
			$slug = (string) ($row['slug'] ?? '');
			$image = (string) ($row['image'] ?? '');
			if ($slug !== '' && $image !== '') {
				$legacyMap[$slug] = (string) get_template_directory_uri() . $image;
			}
		}
	}

	$cards = [];
	foreach ($terms as $term) {
		if (!($term instanceof WP_Term)) {
			continue;
		}
		$link = get_term_link($term);
		if (is_wp_error($link)) {
			continue;
		}

		$imageId = (int) get_term_meta($term->term_id, mosaic_catalog_term_image_meta_key(), true);
		$imageUrl = $imageId > 0 ? (string) wp_get_attachment_image_url($imageId, 'large') : '';

		$videoId = (int) get_term_meta($term->term_id, mosaic_catalog_term_video_id_meta_key(), true);
		$videoUrl = $videoId > 0 ? (string) wp_get_attachment_url($videoId) : '';
		$interiorImageId = (int) get_term_meta($term->term_id, mosaic_catalog_term_interior_image_meta_key(), true);
		$interiorImageUrl = $interiorImageId > 0 ? (string) wp_get_attachment_image_url($interiorImageId, 'large') : '';

		$slug = (string) ($term->slug ?? '');
		if ($imageUrl === '' && $slug !== '' && isset($legacyMap[$slug])) {
			$imageUrl = (string) $legacyMap[$slug];
		}

		$cards[] = [
			'title' => (string) $term->name,
			'url' => (string) $link,
			'slug' => $slug,
			'image_url' => $imageUrl,
			'interior_image_url' => $interiorImageUrl,
			'video_url' => $videoUrl,
		];
	}

	return $cards;
}

/**
 * Сид разделов каталога из legacy хардкода (1 раз).
 */
add_action('admin_init', static function (): void {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (get_option('mosaic_catalog_seeded_v1', null) === '1') {
		return;
	}

	$existing = get_terms([
		'taxonomy' => 'product_section',
		'hide_empty' => false,
	]);
	if (!is_wp_error($existing) && is_array($existing) && count($existing) > 0) {
		add_option('mosaic_catalog_seeded_v1', '1', '', false);
		return;
	}

	if (!function_exists('mosaic_get_catalog_categories')) {
		return;
	}

	foreach (mosaic_get_catalog_categories() as $row) {
		$slug = (string) ($row['slug'] ?? '');
		$title = (string) ($row['title'] ?? '');
		$videoRel = (string) ($row['video'] ?? '');

		if ($slug === '' || $title === '') {
			continue;
		}

		$insert = wp_insert_term($title, 'product_section', ['slug' => $slug]);
		if (is_wp_error($insert)) {
			continue;
		}
		$termId = (int) ($insert['term_id'] ?? 0);
		if ($termId <= 0) {
			continue;
		}

		$videoUrl = $videoRel !== '' ? (string) get_template_directory_uri() . $videoRel : '';
		if ($videoUrl !== '') {
			update_term_meta($termId, mosaic_catalog_term_video_meta_key(), esc_url_raw($videoUrl));
		}

		update_term_meta($termId, mosaic_catalog_term_image_meta_key(), 0);
		update_term_meta($termId, mosaic_catalog_term_interior_image_meta_key(), 0);
	}

	add_option('mosaic_catalog_seeded_v1', '1', '', false);
});



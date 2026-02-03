<?php

declare(strict_types=1);

/**
 * Страница "О нас" (Админка -> Страница О нас).
 * Управление контентом страницы /about/
 */

/**
 * @return array{
 *   hero: array{
 *     title: string,
 *     text: string,
 *     button_text: string,
 *     button_url: string
 *   },
 *   gallery: array{
 *     ids: array<int>
 *   },
 *   video: array{
 *     title: string,
 *     url: string
 *   },
 *   requisites: array{
 *     legal_address: string,
 *     actual_address: string,
 *     inn: string,
 *     okved: string
 *   }
 * }
 */
function mosaic_get_about_page_defaults(): array {
	return [
		'hero' => [
			'title' => 'О нас',
			'text' => 'Si mosaic — место, где искусство становится ближе. Здесь проходят выставки, встречи и вдохновляющие вечера для дизайнеров, архитекторов и ценителей ручной работы.',
			'button_text' => 'Посмотреть проекты',
			'button_url' => '/portfolio',
		],
		'gallery' => [
			'ids' => [],
		],
		'video' => [
			'title' => 'Видео о нашей компании',
			'url' => '',
		],
		'requisites' => [
			'legal_address' => 'Краснодар, Селезнёва 204',
			'actual_address' => 'Краснодар, Селезнёва 204',
			'inn' => '',
			'okved' => '',
		],
	];
}

/**
 * @param mixed $value
 * @return array
 */
function mosaic_sanitize_about_page_option(mixed $value): array {
	$defaults = mosaic_get_about_page_defaults();

	if (!is_array($value)) {
		return $defaults;
	}

	// Hero section
	$hero = $value['hero'] ?? [];
	if (!is_array($hero)) {
		$hero = [];
	}

	$heroTitle = sanitize_text_field((string) ($hero['title'] ?? ''));
	$heroText = sanitize_textarea_field((string) ($hero['text'] ?? ''));
	$heroButtonText = sanitize_text_field((string) ($hero['button_text'] ?? ''));
	$heroButtonUrl = esc_url_raw(trim((string) ($hero['button_url'] ?? '')));

	// Gallery
	$gallery = $value['gallery'] ?? [];
	if (!is_array($gallery)) {
		$gallery = [];
	}

	$galleryIds = [];
	if (isset($gallery['ids']) && is_array($gallery['ids'])) {
		foreach ($gallery['ids'] as $id) {
			$id = absint($id);
			if ($id > 0) {
				$galleryIds[] = $id;
			}
		}
	}

	// Video
	$video = $value['video'] ?? [];
	if (!is_array($video)) {
		$video = [];
	}

	$videoTitle = sanitize_text_field((string) ($video['title'] ?? ''));
	$videoUrl = esc_url_raw(trim((string) ($video['url'] ?? '')));

	// Requisites
	$requisites = $value['requisites'] ?? [];
	if (!is_array($requisites)) {
		$requisites = [];
	}

	$legalAddress = sanitize_text_field((string) ($requisites['legal_address'] ?? ''));
	$actualAddress = sanitize_text_field((string) ($requisites['actual_address'] ?? ''));
	$inn = sanitize_text_field((string) ($requisites['inn'] ?? ''));
	$okved = sanitize_text_field((string) ($requisites['okved'] ?? ''));

	return [
		'hero' => [
			'title' => $heroTitle !== '' ? $heroTitle : $defaults['hero']['title'],
			'text' => $heroText !== '' ? $heroText : $defaults['hero']['text'],
			'button_text' => $heroButtonText !== '' ? $heroButtonText : $defaults['hero']['button_text'],
			'button_url' => $heroButtonUrl !== '' ? $heroButtonUrl : $defaults['hero']['button_url'],
		],
		'gallery' => [
			'ids' => $galleryIds,
		],
		'video' => [
			'title' => $videoTitle !== '' ? $videoTitle : $defaults['video']['title'],
			'url' => $videoUrl,
		],
		'requisites' => [
			'legal_address' => $legalAddress,
			'actual_address' => $actualAddress,
			'inn' => $inn,
			'okved' => $okved,
		],
	];
}

/**
 * @return array
 */
function mosaic_get_about_page(): array {
	$opt = get_option('mosaic_about_page', mosaic_get_about_page_defaults());
	return mosaic_sanitize_about_page_option($opt);
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$hook = add_menu_page(
			'Страница О нас',
			'Страница О нас',
			'edit_theme_options',
			'mosaic-about-page',
			'mosaic_render_about_page_admin',
			'dashicons-groups',
			56
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			wp_enqueue_media();
			wp_register_script('mosaic-about-page-admin', false, ['jquery', 'jquery-ui-sortable'], '1.0', true);
			wp_enqueue_script('mosaic-about-page-admin');

			$js = <<<'JS'
(function($){
  var frame;
  var currentTarget = null;
  var currentMode = 'single';

  function makeThumbHtml(id, url, target) {
    return '<div class="mosaic-gallery-thumb" data-id="' + id + '"><img src="' + url + '"><button type="button" class="remove-thumb" data-target="' + target + '">&times;</button></div>';
  }

  function openMedia(targetId, mode){
    currentTarget = targetId;
    currentMode = mode || 'single';

    frame = wp.media({
      title: mode === 'gallery' ? 'Выбрать изображения' : 'Выбрать изображение',
      button: { text: 'Использовать' },
      multiple: mode === 'gallery',
      library: { type: 'image' }
    });

    frame.on('select', function(){
      if (!currentTarget) return;

      if (currentMode === 'gallery') {
        var selection = frame.state().get('selection').toJSON();
        var ids = selection.map(function(a) { return a.id; });
        var urls = selection.map(function(a) { return a.url; });

        $('#' + currentTarget + '_ids').val(ids.join(','));

        var $preview = $('#' + currentTarget + '_preview');
        $preview.empty();
        urls.forEach(function(url, i) {
          $preview.append(makeThumbHtml(ids[i], url, currentTarget));
        });
        $preview.show();
      } else {
        var attachment = frame.state().get('selection').first().toJSON();
        $('#' + currentTarget + '_id').val(attachment.id || 0);
        var $img = $('#' + currentTarget + '_preview');
        if (attachment.url) {
          $img.attr('src', attachment.url).show();
          $('#' + currentTarget + '_remove').show();
        }
      }
    });

    frame.open();
  }

  function addToGallery(targetId){
    var existingIds = $('#' + targetId + '_ids').val()
      ? $('#' + targetId + '_ids').val().split(',').map(Number).filter(Boolean)
      : [];

    var addFrame = wp.media({
      title: 'Добавить изображения',
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
      var selection = addFrame.state().get('selection').toJSON();
      var $input = $('#' + targetId + '_ids');
      var $preview = $('#' + targetId + '_preview');
      var currentIds = $input.val() ? $input.val().split(',').map(Number).filter(Boolean) : [];

      selection.forEach(function(a) {
        if (currentIds.indexOf(a.id) === -1) {
          currentIds.push(a.id);
          $preview.append(makeThumbHtml(a.id, a.url, targetId));
        }
      });

      $input.val(currentIds.join(','));
      $preview.show();
    });

    addFrame.open();
  }

  $(document).on('click', '.mosaic-image-select', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    var mode = $(this).data('mode') || 'single';
    openMedia(prefix, mode);
  });

  $(document).on('click', '.mosaic-gallery-add', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    addToGallery(prefix);
  });

  $(document).on('click', '.mosaic-gallery-clear', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    if (!confirm('Очистить всю галерею?')) return;
    $('#' + prefix + '_ids').val('');
    $('#' + prefix + '_preview').empty();
  });

  $(document).on('click', '.mosaic-image-remove', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    $('#' + prefix + '_id').val(0);
    $('#' + prefix + '_preview').attr('src', '').hide();
    $(this).hide();
  });

  $(document).on('click', '.remove-thumb', function(e){
    e.preventDefault();
    var target = $(this).data('target');
    var $thumb = $(this).parent();
    var removedId = $thumb.data('id');
    $thumb.remove();

    var $input = $('#' + target + '_ids');
    var ids = $input.val().split(',').filter(function(id) {
      return id && parseInt(id) !== removedId;
    });
    $input.val(ids.join(','));
  });

  // Sortable gallery
  $(function(){
    $('.mosaic-gallery-preview').sortable({
      items: '.mosaic-gallery-thumb',
      cursor: 'move',
      update: function(e, ui) {
        var target = $(this).attr('id').replace('_preview', '');
        var ids = [];
        $(this).find('.mosaic-gallery-thumb').each(function(){
          ids.push($(this).data('id'));
        });
        $('#' + target + '_ids').val(ids.join(','));
      }
    });
  });
})(jQuery);
JS;

			wp_add_inline_script('mosaic-about-page-admin', $js);

			echo '<style>
				.mosaic-about-page-wrap { max-width: 1400px; }
				.mosaic-section { background: #fff; border: 1px solid #dcdcde; border-radius: 14px; margin-bottom: 24px; overflow: hidden; }
				.mosaic-section-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; background: linear-gradient(180deg, #101010 0%, #0b0b0b 100%); color: #fff; }
				.mosaic-section-title { font-size: 16px; font-weight: 600; margin: 0; }
				.mosaic-section-body { padding: 18px; }
				.mosaic-field { margin-bottom: 14px; }
				.mosaic-label { display: block; font-weight: 600; margin-bottom: 6px; }
				.mosaic-input { width: 100%; border-radius: 8px; padding: 10px 12px; border: 1px solid #dcdcde; }
				.mosaic-textarea { width: 100%; min-height: 100px; border-radius: 8px; padding: 10px 12px; border: 1px solid #dcdcde; }
				.mosaic-uploader { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; margin-bottom: 14px; }
				.mosaic-gallery-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; min-height: 50px; }
				.mosaic-gallery-thumb { position: relative; width: 120px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #dcdcde; }
				.mosaic-gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }
				.mosaic-gallery-thumb .remove-thumb { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 14px; line-height: 1; }
				.mosaic-actions { display: flex; gap: 8px; flex-wrap: wrap; }
				.mosaic-actions .button { border-radius: 8px; }
				.mosaic-muted { color: #7a7a7a; font-size: 12px; margin-top: 6px; }
				.two-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
				@media (max-width: 782px) { .two-cols { grid-template-columns: 1fr; } }
			</style>';
		});
	});

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_about_page', null);
		if ($existing === false) {
			add_option('mosaic_about_page', mosaic_get_about_page_defaults(), '', false);
		}

		register_setting(
			'mosaic_about_page_group',
			'mosaic_about_page',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_about_page_option',
				'default' => [],
			]
		);
	});
}

add_action('admin_post_mosaic_save_about_page', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}
	check_admin_referer('mosaic_about_page_save', 'mosaic_about_page_nonce');

	// Hero
	$hero = [
		'title' => isset($_POST['hero_title']) ? (string) $_POST['hero_title'] : '',
		'text' => isset($_POST['hero_text']) ? (string) $_POST['hero_text'] : '',
		'button_text' => isset($_POST['hero_button_text']) ? (string) $_POST['hero_button_text'] : '',
		'button_url' => isset($_POST['hero_button_url']) ? (string) $_POST['hero_button_url'] : '',
	];

	// Gallery
	$galleryIds = [];
	if (isset($_POST['gallery_ids']) && $_POST['gallery_ids'] !== '') {
		$galleryIds = array_map('absint', explode(',', (string) $_POST['gallery_ids']));
	}
	$gallery = [
		'ids' => $galleryIds,
	];

	// Video
	$video = [
		'title' => isset($_POST['video_title']) ? (string) $_POST['video_title'] : '',
		'url' => isset($_POST['video_url']) ? (string) $_POST['video_url'] : '',
	];

	// Requisites
	$requisites = [
		'legal_address' => isset($_POST['requisites_legal_address']) ? (string) $_POST['requisites_legal_address'] : '',
		'actual_address' => isset($_POST['requisites_actual_address']) ? (string) $_POST['requisites_actual_address'] : '',
		'inn' => isset($_POST['requisites_inn']) ? (string) $_POST['requisites_inn'] : '',
		'okved' => isset($_POST['requisites_okved']) ? (string) $_POST['requisites_okved'] : '',
	];

	$data = mosaic_sanitize_about_page_option([
		'hero' => $hero,
		'gallery' => $gallery,
		'video' => $video,
		'requisites' => $requisites,
	]);

	update_option('mosaic_about_page', $data, false);

	$redirect = add_query_arg(['page' => 'mosaic-about-page', 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

function mosaic_render_about_page_admin(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	$data = mosaic_get_about_page();
	$hero = $data['hero'];
	$gallery = $data['gallery'];
	$video = $data['video'];
	$requisites = $data['requisites'];

	// Get gallery previews
	$galleryPreviews = [];
	foreach ($gallery['ids'] as $id) {
		$url = wp_get_attachment_image_url($id, 'thumbnail');
		if ($url) {
			$galleryPreviews[$id] = $url;
		}
	}

	echo '<div class="wrap mosaic-about-page-wrap">';
	echo '<h1>Страница О нас</h1>';
	echo '<p class="description">Настройка контента страницы /about/</p>';

	if (isset($_GET['updated']) && (string) $_GET['updated'] === '1') {
		echo '<div class="notice notice-success is-dismissible"><p>Сохранено.</p></div>';
	}

	echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
	echo '<input type="hidden" name="action" value="mosaic_save_about_page">';
	wp_nonce_field('mosaic_about_page_save', 'mosaic_about_page_nonce');

	// Hero Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Hero-секция</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок</label>';
	echo '<input type="text" class="mosaic-input" name="hero_title" value="' . esc_attr($hero['title']) . '"></p>';

	echo '<p class="mosaic-field"><label class="mosaic-label">Текст</label>';
	echo '<textarea class="mosaic-textarea" name="hero_text">' . esc_textarea($hero['text']) . '</textarea></p>';

	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Текст кнопки</label>';
	echo '<input type="text" class="mosaic-input" name="hero_button_text" value="' . esc_attr($hero['button_text']) . '"></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка кнопки</label>';
	echo '<input type="text" class="mosaic-input" name="hero_button_url" value="' . esc_attr($hero['button_url']) . '"></p>';
	echo '</div>';

	echo '</div></div>';

	// Gallery Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Галерея (слайдер)</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<div class="mosaic-uploader">';
	echo '<input type="hidden" id="gallery_ids" name="gallery_ids" value="' . esc_attr(implode(',', $gallery['ids'])) . '">';
	echo '<div id="gallery_preview" class="mosaic-gallery-preview">';
	foreach ($galleryPreviews as $id => $url) {
		echo '<div class="mosaic-gallery-thumb" data-id="' . esc_attr((string) $id) . '"><img src="' . esc_url($url) . '"><button type="button" class="remove-thumb" data-target="gallery">&times;</button></div>';
	}
	echo '</div>';
	echo '<div class="mosaic-actions">';
	echo '<button type="button" class="button mosaic-gallery-add" data-prefix="gallery">Добавить изображения</button>';
	echo '<button type="button" class="button mosaic-image-select" data-prefix="gallery" data-mode="gallery">Заменить всю галерею</button>';
	echo '<button type="button" class="button mosaic-gallery-clear" data-prefix="gallery">Очистить</button>';
	echo '</div>';
	echo '<p class="mosaic-muted">Изображения можно перетаскивать для изменения порядка. Удалить отдельное фото — кнопка &times; на миниатюре.</p>';
	echo '</div>';

	echo '</div></div>';

	// Video Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Видео</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок секции</label>';
	echo '<input type="text" class="mosaic-input" name="video_title" value="' . esc_attr($video['title']) . '"></p>';

	echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка на видео (YouTube, Vimeo или Rutube)</label>';
	echo '<input type="url" class="mosaic-input" name="video_url" value="' . esc_attr($video['url']) . '" placeholder="https://www.youtube.com/watch?v=..."></p>';
	echo '<p class="mosaic-muted">Вставьте ссылку на видео YouTube, Vimeo или Rutube. Например: https://www.youtube.com/watch?v=xxxxx или https://rutube.ru/video/xxxxx</p>';

	echo '</div></div>';

	// Requisites Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Реквизиты</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Наименование</label>';
	echo '<input type="text" class="mosaic-input" name="requisites_okved" value="' . esc_attr($requisites['okved']) . '"></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">ИНН</label>';
	echo '<input type="text" class="mosaic-input" name="requisites_inn" value="' . esc_attr($requisites['inn']) . '"></p>';
	echo '</div>';

	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Юридический адрес</label>';
	echo '<input type="text" class="mosaic-input" name="requisites_legal_address" value="' . esc_attr($requisites['legal_address']) . '"></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Фактический адрес</label>';
	echo '<input type="text" class="mosaic-input" name="requisites_actual_address" value="' . esc_attr($requisites['actual_address']) . '"></p>';
	echo '</div>';

	echo '<p class="mosaic-muted">Эти данные отображаются в блоке "Реквизиты" на странице О нас.</p>';

	echo '</div></div>';

	submit_button('Сохранить');
	echo '</form></div>';
}

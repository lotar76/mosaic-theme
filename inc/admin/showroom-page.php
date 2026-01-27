<?php

declare(strict_types=1);

/**
 * Страница "Шоурум" (Админка -> Страница Шоурум).
 * Управление контентом страницы /showroom/
 */

/**
 * @return array{
 *   hero: array{
 *     title: string,
 *     features: array<string>,
 *     gallery_ids: array<int>,
 *     description: string,
 *     button_text: string,
 *     button_url: string
 *   },
 *   blocks: array<array{
 *     image_id: int,
 *     title: string,
 *     text: string,
 *     position: string
 *   }>,
 *   collections: array{
 *     title: string,
 *     items: array<array{image_id: int, title: string, url: string}>
 *   },
 *   events: array{
 *     title: string,
 *     items: array<array{image_id: int, title: string, url: string}>
 *   },
 *   map: array{
 *     latitude: string,
 *     longitude: string,
 *     zoom: int
 *   },
 *   homepage_image_id: int
 * }
 */
function mosaic_get_showroom_page_defaults(): array {
	$base = get_template_directory_uri() . '/img';

	return [
		'hero' => [
			'title' => "Приглашаем в шоурум\nв Краснодаре",
			'features' => [
				'Образцы работы',
				'Большие готовые панно',
				'Каталог материалов',
				'Встречи с дизайнером студии',
				'Встречи дизайнеров и мастер-классы',
			],
			'gallery_ids' => [],
			'description' => 'Si Mosaic Showroom — место, где искусство становится ближе. Здесь проходят выставки, встречи и вдохновляющие вечера для дизайнеров, архитекторов и ценителей ручной работы.',
			'button_text' => 'Записаться в шоурум',
			'button_url' => '#contact-form',
		],
		'blocks' => [
			[
				'image_id' => 0,
				'title' => 'О шоуруме',
				'text' => "5 лет мы развиваем своё производство и обучаем специалистов внутри студии, сохраняя высокий стандарт качества в каждой детали.\n\nНаша студия реализовала свыше 3000 кв. м проектов, где каждая поверхность создавалась вручную и проходила через опыт наших специалистов.",
				'position' => 'left',
			],
			[
				'image_id' => 0,
				'title' => 'Подзаголовок',
				'text' => "5 лет мы развиваем своё производство и обучаем специалистов внутри студии, сохраняя высокий стандарт качества в каждой детали.\n\nНаша студия реализовала свыше 3000 кв. м проектов, где каждая поверхность создавалась вручную и проходила через опыт наших специалистов.",
				'position' => 'right',
			],
		],
		'collections' => [
			'title' => 'Коллекции, представленные в шоуруме',
			'items' => [],
		],
		'events' => [
			'title' => 'Афиша мероприятий',
			'items' => [],
		],
		'map' => [
			'latitude' => '45.0355',
			'longitude' => '38.9753',
			'zoom' => 15,
		],
		'homepage_image_id' => 0,
	];
}

/**
 * @param mixed $value
 * @return array
 */
function mosaic_sanitize_showroom_page_option(mixed $value): array {
	$defaults = mosaic_get_showroom_page_defaults();

	if (!is_array($value)) {
		return $defaults;
	}

	// Hero section
	$hero = $value['hero'] ?? [];
	if (!is_array($hero)) {
		$hero = [];
	}

	$heroTitle = sanitize_textarea_field((string) ($hero['title'] ?? ''));
	$heroFeatures = [];
	if (isset($hero['features']) && is_array($hero['features'])) {
		foreach ($hero['features'] as $feature) {
			$f = sanitize_text_field((string) $feature);
			if ($f !== '') {
				$heroFeatures[] = $f;
			}
		}
	}
	if (count($heroFeatures) === 0) {
		$heroFeatures = $defaults['hero']['features'];
	}

	$heroGalleryIds = [];
	if (isset($hero['gallery_ids']) && is_array($hero['gallery_ids'])) {
		foreach ($hero['gallery_ids'] as $id) {
			$id = absint($id);
			if ($id > 0) {
				$heroGalleryIds[] = $id;
			}
		}
	}

	$heroDescription = sanitize_textarea_field((string) ($hero['description'] ?? ''));
	$heroButtonText = sanitize_text_field((string) ($hero['button_text'] ?? ''));
	$heroButtonUrl = esc_url_raw(trim((string) ($hero['button_url'] ?? '')));

	// Blocks
	$blocks = [];
	if (isset($value['blocks']) && is_array($value['blocks'])) {
		foreach ($value['blocks'] as $block) {
			if (!is_array($block)) {
				continue;
			}
			$blocks[] = [
				'image_id' => absint($block['image_id'] ?? 0),
				'title' => sanitize_text_field((string) ($block['title'] ?? '')),
				'text' => sanitize_textarea_field((string) ($block['text'] ?? '')),
				'position' => in_array(($block['position'] ?? ''), ['left', 'right'], true) ? $block['position'] : 'right',
			];
		}
	}
	if (count($blocks) === 0) {
		$blocks = $defaults['blocks'];
	}

	// Collections
	$collections = $value['collections'] ?? [];
	if (!is_array($collections)) {
		$collections = [];
	}
	$collectionsTitle = sanitize_text_field((string) ($collections['title'] ?? ''));
	$collectionsItems = [];
	if (isset($collections['items']) && is_array($collections['items'])) {
		foreach ($collections['items'] as $item) {
			if (!is_array($item)) {
				continue;
			}
			$collectionsItems[] = [
				'image_id' => absint($item['image_id'] ?? 0),
				'title' => sanitize_text_field((string) ($item['title'] ?? '')),
				'url' => esc_url_raw(trim((string) ($item['url'] ?? ''))),
			];
		}
	}

	// Events
	$events = $value['events'] ?? [];
	if (!is_array($events)) {
		$events = [];
	}
	$eventsTitle = sanitize_text_field((string) ($events['title'] ?? ''));
	$eventsItems = [];
	if (isset($events['items']) && is_array($events['items'])) {
		foreach ($events['items'] as $item) {
			if (!is_array($item)) {
				continue;
			}
			$eventsItems[] = [
				'image_id' => absint($item['image_id'] ?? 0),
				'title' => sanitize_text_field((string) ($item['title'] ?? '')),
				'url' => esc_url_raw(trim((string) ($item['url'] ?? ''))),
			];
		}
	}

	// Map
	$map = $value['map'] ?? [];
	if (!is_array($map)) {
		$map = [];
	}
	$mapLat = sanitize_text_field((string) ($map['latitude'] ?? ''));
	$mapLng = sanitize_text_field((string) ($map['longitude'] ?? ''));
	$mapZoom = absint($map['zoom'] ?? 15);
	if ($mapZoom < 1 || $mapZoom > 20) {
		$mapZoom = 15;
	}

	// Homepage image
	$homepageImageId = absint($value['homepage_image_id'] ?? 0);

	return [
		'hero' => [
			'title' => $heroTitle !== '' ? $heroTitle : $defaults['hero']['title'],
			'features' => $heroFeatures,
			'gallery_ids' => $heroGalleryIds,
			'description' => $heroDescription !== '' ? $heroDescription : $defaults['hero']['description'],
			'button_text' => $heroButtonText !== '' ? $heroButtonText : $defaults['hero']['button_text'],
			'button_url' => $heroButtonUrl !== '' ? $heroButtonUrl : $defaults['hero']['button_url'],
		],
		'blocks' => $blocks,
		'collections' => [
			'title' => $collectionsTitle !== '' ? $collectionsTitle : $defaults['collections']['title'],
			'items' => $collectionsItems,
		],
		'events' => [
			'title' => $eventsTitle !== '' ? $eventsTitle : $defaults['events']['title'],
			'items' => $eventsItems,
		],
		'map' => [
			'latitude' => $mapLat !== '' ? $mapLat : $defaults['map']['latitude'],
			'longitude' => $mapLng !== '' ? $mapLng : $defaults['map']['longitude'],
			'zoom' => $mapZoom,
		],
		'homepage_image_id' => $homepageImageId,
	];
}

/**
 * @return array
 */
function mosaic_get_showroom_page(): array {
	$opt = get_option('mosaic_showroom_page', mosaic_get_showroom_page_defaults());
	return mosaic_sanitize_showroom_page_option($opt);
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$hook = add_menu_page(
			'Страница Шоурум',
			'Страница Шоурум',
			'edit_theme_options',
			'mosaic-showroom-page',
			'mosaic_render_showroom_page_admin',
			'dashicons-store',
			58
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			wp_enqueue_media();
			wp_register_script('mosaic-showroom-page-admin', false, ['jquery', 'jquery-ui-sortable'], '1.0', true);
			wp_enqueue_script('mosaic-showroom-page-admin');

			$js = <<<'JS'
(function($){
  var frame;
  var currentTarget = null;
  var currentMode = 'single'; // 'single' or 'gallery'

  function openMedia(targetId, mode){
    currentTarget = targetId;
    currentMode = mode || 'single';

    if (frame) {
      frame.dispose();
    }

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
          $preview.append('<div class="mosaic-gallery-thumb" data-id="' + ids[i] + '"><img src="' + url + '"><button type="button" class="remove-thumb" data-target="' + currentTarget + '">&times;</button></div>');
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

  $(document).on('click', '.mosaic-image-select', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    var mode = $(this).data('mode') || 'single';
    openMedia(prefix, mode);
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

    // Sortable blocks
    $('#showroom-blocks-list').sortable({
      handle: '.block-handle',
      items: '.showroom-block-item',
      cursor: 'move'
    });

    // Sortable items (collections/events)
    $('.sortable-items').sortable({
      handle: '.item-handle',
      items: '.carousel-item',
      cursor: 'move'
    });
  });

  // Add block
  $(document).on('click', '#add-showroom-block', function(e){
    e.preventDefault();
    var index = $('#showroom-blocks-list .showroom-block-item').length;
    var html = $('#block-template').html().replace(/__INDEX__/g, index);
    $('#showroom-blocks-list').append(html);
  });

  // Remove block
  $(document).on('click', '.remove-block', function(e){
    e.preventDefault();
    $(this).closest('.showroom-block-item').remove();
  });

  // Add carousel item
  $(document).on('click', '.add-carousel-item', function(e){
    e.preventDefault();
    var section = $(this).data('section');
    var $list = $('#' + section + '-items-list');
    var index = $list.find('.carousel-item').length;
    var html = $('#' + section + '-item-template').html().replace(/__INDEX__/g, index);
    $list.append(html);
  });

  // Remove carousel item
  $(document).on('click', '.remove-carousel-item', function(e){
    e.preventDefault();
    $(this).closest('.carousel-item').remove();
  });

  // Init previews
  $(function(){
    $('.mosaic-single-preview').each(function(){
      var $img = $(this);
      var url = String($img.attr('src') || '').trim();
      if (url) {
        $img.show();
        $img.closest('.mosaic-uploader').find('.mosaic-image-remove').show();
      } else {
        $img.hide();
        $img.closest('.mosaic-uploader').find('.mosaic-image-remove').hide();
      }
    });
  });
})(jQuery);
JS;

			wp_add_inline_script('mosaic-showroom-page-admin', $js);

			echo '<style>
				.mosaic-showroom-wrap { max-width: 1400px; }
				.mosaic-section { background: #fff; border: 1px solid #dcdcde; border-radius: 14px; margin-bottom: 24px; overflow: hidden; }
				.mosaic-section-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; background: linear-gradient(180deg, #101010 0%, #0b0b0b 100%); color: #fff; }
				.mosaic-section-title { font-size: 16px; font-weight: 600; margin: 0; }
				.mosaic-section-body { padding: 18px; }
				.mosaic-field { margin-bottom: 14px; }
				.mosaic-label { display: block; font-weight: 600; margin-bottom: 6px; }
				.mosaic-input { width: 100%; border-radius: 8px; padding: 10px 12px; border: 1px solid #dcdcde; }
				.mosaic-textarea { width: 100%; min-height: 100px; border-radius: 8px; padding: 10px 12px; border: 1px solid #dcdcde; }
				.mosaic-uploader { border: 1px dashed #c3c4c7; border-radius: 12px; padding: 12px; background: #fafafa; margin-bottom: 14px; }
				.mosaic-single-preview { max-width: 300px; max-height: 180px; object-fit: cover; border-radius: 10px; border: 1px solid #dcdcde; display: none; margin-bottom: 10px; }
				.mosaic-gallery-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; min-height: 50px; }
				.mosaic-gallery-thumb { position: relative; width: 120px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #dcdcde; }
				.mosaic-gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }
				.mosaic-gallery-thumb .remove-thumb { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 14px; line-height: 1; }
				.mosaic-actions { display: flex; gap: 8px; flex-wrap: wrap; }
				.mosaic-actions .button { border-radius: 8px; }
				.mosaic-muted { color: #7a7a7a; font-size: 12px; margin-top: 6px; }
				.showroom-block-item, .carousel-item { background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 10px; padding: 14px; margin-bottom: 12px; position: relative; }
				.block-handle, .item-handle { cursor: move; color: #666; margin-right: 10px; }
				.remove-block, .remove-carousel-item { position: absolute; top: 10px; right: 10px; background: #d63638; color: #fff; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 12px; }
				.two-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
				@media (max-width: 782px) { .two-cols { grid-template-columns: 1fr; } }
			</style>';
		});
	});

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_showroom_page', null);
		if ($existing === false) {
			add_option('mosaic_showroom_page', mosaic_get_showroom_page_defaults(), '', false);
		}

		register_setting(
			'mosaic_showroom_page_group',
			'mosaic_showroom_page',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_showroom_page_option',
				'default' => [],
			]
		);
	});
}

add_action('admin_post_mosaic_save_showroom_page', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}
	check_admin_referer('mosaic_showroom_page_save', 'mosaic_showroom_page_nonce');

	// Hero
	$hero = [
		'title' => isset($_POST['hero_title']) ? (string) $_POST['hero_title'] : '',
		'features' => isset($_POST['hero_features']) ? array_filter(array_map('trim', explode("\n", (string) $_POST['hero_features']))) : [],
		'gallery_ids' => isset($_POST['hero_gallery_ids']) && $_POST['hero_gallery_ids'] !== ''
			? array_map('absint', explode(',', (string) $_POST['hero_gallery_ids']))
			: [],
		'description' => isset($_POST['hero_description']) ? (string) $_POST['hero_description'] : '',
		'button_text' => isset($_POST['hero_button_text']) ? (string) $_POST['hero_button_text'] : '',
		'button_url' => isset($_POST['hero_button_url']) ? (string) $_POST['hero_button_url'] : '',
	];

	// Blocks
	$blocks = [];
	if (isset($_POST['blocks']) && is_array($_POST['blocks'])) {
		foreach ($_POST['blocks'] as $block) {
			if (!is_array($block)) {
				continue;
			}
			$blocks[] = [
				'image_id' => absint($block['image_id'] ?? 0),
				'title' => (string) ($block['title'] ?? ''),
				'text' => (string) ($block['text'] ?? ''),
				'position' => (string) ($block['position'] ?? 'right'),
			];
		}
	}

	// Collections
	$collectionsItems = [];
	if (isset($_POST['collections_items']) && is_array($_POST['collections_items'])) {
		foreach ($_POST['collections_items'] as $item) {
			if (!is_array($item)) {
				continue;
			}
			$collectionsItems[] = [
				'image_id' => absint($item['image_id'] ?? 0),
				'title' => (string) ($item['title'] ?? ''),
				'url' => (string) ($item['url'] ?? ''),
			];
		}
	}

	$collections = [
		'title' => isset($_POST['collections_title']) ? (string) $_POST['collections_title'] : '',
		'items' => $collectionsItems,
	];

	// Events
	$eventsItems = [];
	if (isset($_POST['events_items']) && is_array($_POST['events_items'])) {
		foreach ($_POST['events_items'] as $item) {
			if (!is_array($item)) {
				continue;
			}
			$eventsItems[] = [
				'image_id' => absint($item['image_id'] ?? 0),
				'title' => (string) ($item['title'] ?? ''),
				'url' => (string) ($item['url'] ?? ''),
			];
		}
	}

	$events = [
		'title' => isset($_POST['events_title']) ? (string) $_POST['events_title'] : '',
		'items' => $eventsItems,
	];

	// Map
	$map = [
		'latitude' => isset($_POST['map_latitude']) ? (string) $_POST['map_latitude'] : '',
		'longitude' => isset($_POST['map_longitude']) ? (string) $_POST['map_longitude'] : '',
		'zoom' => isset($_POST['map_zoom']) ? absint($_POST['map_zoom']) : 15,
	];

	// Homepage image
	$homepageImageId = isset($_POST['homepage_image_id']) ? absint($_POST['homepage_image_id']) : 0;

	$data = mosaic_sanitize_showroom_page_option([
		'hero' => $hero,
		'blocks' => $blocks,
		'collections' => $collections,
		'events' => $events,
		'map' => $map,
		'homepage_image_id' => $homepageImageId,
	]);

	update_option('mosaic_showroom_page', $data, false);

	$redirect = add_query_arg(['page' => 'mosaic-showroom-page', 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

function mosaic_render_showroom_page_admin(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	$data = mosaic_get_showroom_page();
	$hero = $data['hero'];
	$blocks = $data['blocks'];
	$collections = $data['collections'];
	$events = $data['events'];
	$map = $data['map'];
	$homepageImageId = $data['homepage_image_id'];

	// Get gallery previews
	$galleryPreviews = [];
	foreach ($hero['gallery_ids'] as $id) {
		$url = wp_get_attachment_image_url($id, 'thumbnail');
		if ($url) {
			$galleryPreviews[$id] = $url;
		}
	}

	echo '<div class="wrap mosaic-showroom-wrap">';
	echo '<h1>Страница Шоурум</h1>';
	echo '<p class="description">Настройка контента страницы /showroom/</p>';

	if (isset($_GET['updated']) && (string) $_GET['updated'] === '1') {
		echo '<div class="notice notice-success is-dismissible"><p>Сохранено.</p></div>';
	}

	echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
	echo '<input type="hidden" name="action" value="mosaic_save_showroom_page">';
	wp_nonce_field('mosaic_showroom_page_save', 'mosaic_showroom_page_nonce');

	// Hero Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Hero-секция</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок (каждая строка — новая строка на сайте)</label>';
	echo '<textarea class="mosaic-textarea" name="hero_title" rows="2">' . esc_textarea($hero['title']) . '</textarea></p>';

	echo '<p class="mosaic-field"><label class="mosaic-label">Список преимуществ (каждая строка — новый пункт)</label>';
	echo '<textarea class="mosaic-textarea" name="hero_features" rows="5">' . esc_textarea(implode("\n", $hero['features'])) . '</textarea></p>';

	echo '<div class="mosaic-uploader">';
	echo '<label class="mosaic-label">Галерея (слайдер на всю ширину)</label>';
	echo '<input type="hidden" id="hero_gallery_ids" name="hero_gallery_ids" value="' . esc_attr(implode(',', $hero['gallery_ids'])) . '">';
	echo '<div id="hero_gallery_preview" class="mosaic-gallery-preview">';
	foreach ($galleryPreviews as $id => $url) {
		echo '<div class="mosaic-gallery-thumb" data-id="' . esc_attr((string) $id) . '"><img src="' . esc_url($url) . '"><button type="button" class="remove-thumb" data-target="hero_gallery">&times;</button></div>';
	}
	echo '</div>';
	echo '<div class="mosaic-actions">';
	echo '<button type="button" class="button mosaic-image-select" data-prefix="hero_gallery" data-mode="gallery">Добавить изображения</button>';
	echo '</div>';
	echo '<p class="mosaic-muted">Рекомендуемый размер: 1920×600px</p>';
	echo '</div>';

	echo '<p class="mosaic-field"><label class="mosaic-label">Описание под слайдером</label>';
	echo '<textarea class="mosaic-textarea" name="hero_description" rows="3">' . esc_textarea($hero['description']) . '</textarea></p>';

	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Текст кнопки</label>';
	echo '<input type="text" class="mosaic-input" name="hero_button_text" value="' . esc_attr($hero['button_text']) . '"></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка кнопки</label>';
	echo '<input type="text" class="mosaic-input" name="hero_button_url" value="' . esc_attr($hero['button_url']) . '"></p>';
	echo '</div>';

	echo '</div></div>';

	// Blocks Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Блоки с картинкой и текстом</p></div>';
	echo '<div class="mosaic-section-body">';
	echo '<div id="showroom-blocks-list">';

	foreach ($blocks as $i => $block) {
		$blockPreview = '';
		if ($block['image_id'] > 0) {
			$blockPreview = (string) wp_get_attachment_image_url($block['image_id'], 'medium');
		}

		echo '<div class="showroom-block-item">';
		echo '<span class="block-handle dashicons dashicons-move"></span>';
		echo '<button type="button" class="remove-block">Удалить</button>';

		echo '<input type="hidden" name="blocks[' . $i . '][image_id]" id="block_' . $i . '_id" value="' . esc_attr((string) $block['image_id']) . '">';
		echo '<div class="mosaic-uploader">';
		echo '<img id="block_' . $i . '_preview" class="mosaic-single-preview" src="' . esc_url($blockPreview) . '">';
		echo '<div class="mosaic-actions">';
		echo '<button type="button" class="button mosaic-image-select" data-prefix="block_' . $i . '">Выбрать фото</button>';
		echo '<button type="button" class="button mosaic-image-remove" id="block_' . $i . '_remove" data-prefix="block_' . $i . '" style="display:none;">Удалить</button>';
		echo '</div></div>';

		echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок</label>';
		echo '<input type="text" class="mosaic-input" name="blocks[' . $i . '][title]" value="' . esc_attr($block['title']) . '"></p>';

		echo '<p class="mosaic-field"><label class="mosaic-label">Текст</label>';
		echo '<textarea class="mosaic-textarea" name="blocks[' . $i . '][text]">' . esc_textarea($block['text']) . '</textarea></p>';

		echo '<p class="mosaic-field"><label class="mosaic-label">Позиция картинки</label>';
		echo '<select class="mosaic-input" name="blocks[' . $i . '][position]" style="width:auto;">';
		echo '<option value="left"' . selected($block['position'], 'left', false) . '>Слева</option>';
		echo '<option value="right"' . selected($block['position'], 'right', false) . '>Справа</option>';
		echo '</select></p>';

		echo '</div>';
	}

	echo '</div>';
	echo '<button type="button" id="add-showroom-block" class="button">+ Добавить блок</button>';
	echo '</div></div>';

	// Block template (hidden)
	echo '<script type="text/html" id="block-template">';
	echo '<div class="showroom-block-item">';
	echo '<span class="block-handle dashicons dashicons-move"></span>';
	echo '<button type="button" class="remove-block">Удалить</button>';
	echo '<input type="hidden" name="blocks[__INDEX__][image_id]" id="block___INDEX___id" value="0">';
	echo '<div class="mosaic-uploader">';
	echo '<img id="block___INDEX___preview" class="mosaic-single-preview" src="">';
	echo '<div class="mosaic-actions">';
	echo '<button type="button" class="button mosaic-image-select" data-prefix="block___INDEX__">Выбрать фото</button>';
	echo '<button type="button" class="button mosaic-image-remove" id="block___INDEX___remove" data-prefix="block___INDEX__" style="display:none;">Удалить</button>';
	echo '</div></div>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок</label>';
	echo '<input type="text" class="mosaic-input" name="blocks[__INDEX__][title]" value=""></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Текст</label>';
	echo '<textarea class="mosaic-textarea" name="blocks[__INDEX__][text]"></textarea></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Позиция картинки</label>';
	echo '<select class="mosaic-input" name="blocks[__INDEX__][position]" style="width:auto;">';
	echo '<option value="left">Слева</option>';
	echo '<option value="right" selected>Справа</option>';
	echo '</select></p>';
	echo '</div>';
	echo '</script>';

	// Collections Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Коллекции в шоуруме</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок секции</label>';
	echo '<input type="text" class="mosaic-input" name="collections_title" value="' . esc_attr($collections['title']) . '"></p>';

	echo '<div id="collections-items-list" class="sortable-items">';
	foreach ($collections['items'] as $i => $item) {
		$itemPreview = '';
		if ($item['image_id'] > 0) {
			$itemPreview = (string) wp_get_attachment_image_url($item['image_id'], 'thumbnail');
		}

		echo '<div class="carousel-item">';
		echo '<span class="item-handle dashicons dashicons-move"></span>';
		echo '<button type="button" class="remove-carousel-item">Удалить</button>';

		echo '<input type="hidden" name="collections_items[' . $i . '][image_id]" id="collections_' . $i . '_id" value="' . esc_attr((string) $item['image_id']) . '">';
		echo '<div class="mosaic-uploader">';
		echo '<img id="collections_' . $i . '_preview" class="mosaic-single-preview" src="' . esc_url($itemPreview) . '">';
		echo '<div class="mosaic-actions">';
		echo '<button type="button" class="button mosaic-image-select" data-prefix="collections_' . $i . '">Выбрать фото</button>';
		echo '<button type="button" class="button mosaic-image-remove" id="collections_' . $i . '_remove" data-prefix="collections_' . $i . '" style="display:none;">Удалить</button>';
		echo '</div></div>';

		echo '<div class="two-cols">';
		echo '<p class="mosaic-field"><label class="mosaic-label">Название</label>';
		echo '<input type="text" class="mosaic-input" name="collections_items[' . $i . '][title]" value="' . esc_attr($item['title']) . '"></p>';
		echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка</label>';
		echo '<input type="text" class="mosaic-input" name="collections_items[' . $i . '][url]" value="' . esc_attr($item['url']) . '"></p>';
		echo '</div>';

		echo '</div>';
	}
	echo '</div>';
	echo '<button type="button" class="button add-carousel-item" data-section="collections">+ Добавить коллекцию</button>';

	echo '</div></div>';

	// Collections item template
	echo '<script type="text/html" id="collections-item-template">';
	echo '<div class="carousel-item">';
	echo '<span class="item-handle dashicons dashicons-move"></span>';
	echo '<button type="button" class="remove-carousel-item">Удалить</button>';
	echo '<input type="hidden" name="collections_items[__INDEX__][image_id]" id="collections___INDEX___id" value="0">';
	echo '<div class="mosaic-uploader">';
	echo '<img id="collections___INDEX___preview" class="mosaic-single-preview" src="">';
	echo '<div class="mosaic-actions">';
	echo '<button type="button" class="button mosaic-image-select" data-prefix="collections___INDEX__">Выбрать фото</button>';
	echo '<button type="button" class="button mosaic-image-remove" id="collections___INDEX___remove" data-prefix="collections___INDEX__" style="display:none;">Удалить</button>';
	echo '</div></div>';
	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Название</label>';
	echo '<input type="text" class="mosaic-input" name="collections_items[__INDEX__][title]" value=""></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка</label>';
	echo '<input type="text" class="mosaic-input" name="collections_items[__INDEX__][url]" value=""></p>';
	echo '</div>';
	echo '</div>';
	echo '</script>';

	// Events Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Афиша мероприятий</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<p class="mosaic-field"><label class="mosaic-label">Заголовок секции</label>';
	echo '<input type="text" class="mosaic-input" name="events_title" value="' . esc_attr($events['title']) . '"></p>';

	echo '<div id="events-items-list" class="sortable-items">';
	foreach ($events['items'] as $i => $item) {
		$itemPreview = '';
		if ($item['image_id'] > 0) {
			$itemPreview = (string) wp_get_attachment_image_url($item['image_id'], 'thumbnail');
		}

		echo '<div class="carousel-item">';
		echo '<span class="item-handle dashicons dashicons-move"></span>';
		echo '<button type="button" class="remove-carousel-item">Удалить</button>';

		echo '<input type="hidden" name="events_items[' . $i . '][image_id]" id="events_' . $i . '_id" value="' . esc_attr((string) $item['image_id']) . '">';
		echo '<div class="mosaic-uploader">';
		echo '<img id="events_' . $i . '_preview" class="mosaic-single-preview" src="' . esc_url($itemPreview) . '">';
		echo '<div class="mosaic-actions">';
		echo '<button type="button" class="button mosaic-image-select" data-prefix="events_' . $i . '">Выбрать фото</button>';
		echo '<button type="button" class="button mosaic-image-remove" id="events_' . $i . '_remove" data-prefix="events_' . $i . '" style="display:none;">Удалить</button>';
		echo '</div></div>';

		echo '<div class="two-cols">';
		echo '<p class="mosaic-field"><label class="mosaic-label">Название</label>';
		echo '<input type="text" class="mosaic-input" name="events_items[' . $i . '][title]" value="' . esc_attr($item['title']) . '"></p>';
		echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка</label>';
		echo '<input type="text" class="mosaic-input" name="events_items[' . $i . '][url]" value="' . esc_attr($item['url']) . '"></p>';
		echo '</div>';

		echo '</div>';
	}
	echo '</div>';
	echo '<button type="button" class="button add-carousel-item" data-section="events">+ Добавить мероприятие</button>';

	echo '</div></div>';

	// Events item template
	echo '<script type="text/html" id="events-item-template">';
	echo '<div class="carousel-item">';
	echo '<span class="item-handle dashicons dashicons-move"></span>';
	echo '<button type="button" class="remove-carousel-item">Удалить</button>';
	echo '<input type="hidden" name="events_items[__INDEX__][image_id]" id="events___INDEX___id" value="0">';
	echo '<div class="mosaic-uploader">';
	echo '<img id="events___INDEX___preview" class="mosaic-single-preview" src="">';
	echo '<div class="mosaic-actions">';
	echo '<button type="button" class="button mosaic-image-select" data-prefix="events___INDEX__">Выбрать фото</button>';
	echo '<button type="button" class="button mosaic-image-remove" id="events___INDEX___remove" data-prefix="events___INDEX__" style="display:none;">Удалить</button>';
	echo '</div></div>';
	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Название</label>';
	echo '<input type="text" class="mosaic-input" name="events_items[__INDEX__][title]" value=""></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Ссылка</label>';
	echo '<input type="text" class="mosaic-input" name="events_items[__INDEX__][url]" value=""></p>';
	echo '</div>';
	echo '</div>';
	echo '</script>';

	// Map Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Карта</p></div>';
	echo '<div class="mosaic-section-body">';

	echo '<div class="two-cols">';
	echo '<p class="mosaic-field"><label class="mosaic-label">Широта (latitude)</label>';
	echo '<input type="text" class="mosaic-input" name="map_latitude" value="' . esc_attr($map['latitude']) . '" placeholder="45.0355"></p>';
	echo '<p class="mosaic-field"><label class="mosaic-label">Долгота (longitude)</label>';
	echo '<input type="text" class="mosaic-input" name="map_longitude" value="' . esc_attr($map['longitude']) . '" placeholder="38.9753"></p>';
	echo '</div>';

	echo '<p class="mosaic-field"><label class="mosaic-label">Масштаб (zoom, 1-20)</label>';
	echo '<input type="number" class="mosaic-input" name="map_zoom" value="' . esc_attr((string) $map['zoom']) . '" min="1" max="20" style="width:100px;"></p>';

	echo '<p class="mosaic-muted">Координаты можно найти на Яндекс.Картах или Google Maps. Рекомендуемый zoom: 15-16.</p>';

	echo '</div></div>';

	// Homepage Image Section
	echo '<div class="mosaic-section">';
	echo '<div class="mosaic-section-header"><p class="mosaic-section-title">Картинка на главной</p></div>';
	echo '<div class="mosaic-section-body">';

	$homepageImagePreview = '';
	if ($homepageImageId > 0) {
		$homepageImagePreview = (string) wp_get_attachment_image_url($homepageImageId, 'medium');
	}

	echo '<input type="hidden" name="homepage_image_id" id="homepage_image_id" value="' . esc_attr((string) $homepageImageId) . '">';
	echo '<div class="mosaic-uploader">';
	echo '<img id="homepage_image_preview" class="mosaic-single-preview" src="' . esc_url($homepageImagePreview) . '">';
	echo '<div class="mosaic-actions">';
	echo '<button type="button" class="button mosaic-image-select" data-prefix="homepage_image">Выбрать фото</button>';
	echo '<button type="button" class="button mosaic-image-remove" id="homepage_image_remove" data-prefix="homepage_image" style="display:none;">Удалить</button>';
	echo '</div>';
	echo '<p class="mosaic-muted">Изображение для секции "Приглашаем в шоурум" на главной странице. Рекомендуемый размер: 1920×700px</p>';
	echo '</div>';

	echo '</div></div>';

	submit_button('Сохранить');
	echo '</form></div>';
}

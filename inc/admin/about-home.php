<?php

declare(strict_types=1);

/**
 * О нас на главной (Админка -> О нас (главная)).
 * Два блока: "О компании" и "Основатели".
 *
 * @return array{company:array{image_id:int,image_url:string,title:string,text_1:string,text_2:string,button_text:string,button_url:string},founders:array{image_id:int,image_url:string,name:string,title:string,description_1:string,description_2:string}}
 */
function mosaic_get_about_home_defaults(): array {
	$base = get_template_directory_uri() . '/img';

	return [
		'company' => [
			'image_id' => 0,
			'image_url' => $base . '/12.png',
			'title' => 'О компании',
			'text_1' => '5 лет мы развиваем своё производство и обучаем специалистов внутри студии, сохраняя высокий стандарт качества в каждой детали.',
			'text_2' => 'Наша студия реализовала свыше 3000 кв. м проектов, где каждая поверхность создавалась вручную и проходила через опыт наших специалистов.',
			'button_text' => 'Больше о нашей истории',
			'button_url' => '/about',
		],
		'founders' => [
			'image_id' => 0,
			'image_url' => $base . '/13.png',
			'name' => 'Алексей и Светлана Исаевы',
			'title' => 'Основатели компании Si Mosaic',
			'description_1' => 'Мы верим, что искусство должно быть живым и может передавать эмоции. Каждый проект имеет свою историю, наполненную смыслом. Мы имеем свой уникальный почерк и это отражается в наших работах.',
			'description_2' => 'Разработали собственную технологию обучения мастеров, благодаря которой команда работает в едином стиле и качестве. Каждый проект проходит через наш личный контроль: от идеи и художественного замысла до финального исполнения.',
		],
	];
}

/**
 * @param mixed $row
 * @return array{image_id:int,image_url:string,title:string,text_1:string,text_2:string,button_text:string,button_url:string}
 */
function mosaic_sanitize_about_home_company(mixed $row): array {
	$defaults = mosaic_get_about_home_defaults()['company'];

	if (!is_array($row)) {
		return $defaults;
	}

	$imageId = absint($row['image_id'] ?? 0);
	$imageUrlRaw = trim((string) ($row['image_url'] ?? ''));
	$imageUrlRaw = sanitize_text_field($imageUrlRaw);
	$imageUrl = $imageUrlRaw !== '' ? esc_url_raw($imageUrlRaw) : '';

	$title = sanitize_text_field((string) ($row['title'] ?? ''));
	$text1 = sanitize_textarea_field((string) ($row['text_1'] ?? ''));
	$text2 = sanitize_textarea_field((string) ($row['text_2'] ?? ''));
	$buttonText = sanitize_text_field((string) ($row['button_text'] ?? ''));
	$buttonUrlRaw = trim((string) ($row['button_url'] ?? ''));
	$buttonUrl = $buttonUrlRaw !== '' ? esc_url_raw($buttonUrlRaw) : '';

	return [
		'image_id' => $imageId,
		'image_url' => $imageUrl,
		'title' => $title !== '' ? $title : $defaults['title'],
		'text_1' => $text1 !== '' ? $text1 : $defaults['text_1'],
		'text_2' => $text2 !== '' ? $text2 : $defaults['text_2'],
		'button_text' => $buttonText !== '' ? $buttonText : $defaults['button_text'],
		'button_url' => $buttonUrl !== '' ? $buttonUrl : $defaults['button_url'],
	];
}

/**
 * @param mixed $row
 * @return array{image_id:int,image_url:string,name:string,title:string,description_1:string,description_2:string}
 */
function mosaic_sanitize_about_home_founders(mixed $row): array {
	$defaults = mosaic_get_about_home_defaults()['founders'];

	if (!is_array($row)) {
		return $defaults;
	}

	$imageId = absint($row['image_id'] ?? 0);
	$imageUrlRaw = trim((string) ($row['image_url'] ?? ''));
	$imageUrlRaw = sanitize_text_field($imageUrlRaw);
	$imageUrl = $imageUrlRaw !== '' ? esc_url_raw($imageUrlRaw) : '';

	$name = sanitize_text_field((string) ($row['name'] ?? ''));
	$title = sanitize_text_field((string) ($row['title'] ?? ''));
	$description1 = sanitize_textarea_field((string) ($row['description_1'] ?? ''));
	$description2 = sanitize_textarea_field((string) ($row['description_2'] ?? ''));

	return [
		'image_id' => $imageId,
		'image_url' => $imageUrl,
		'name' => $name !== '' ? $name : $defaults['name'],
		'title' => $title !== '' ? $title : $defaults['title'],
		'description_1' => $description1 !== '' ? $description1 : $defaults['description_1'],
		'description_2' => $description2 !== '' ? $description2 : $defaults['description_2'],
	];
}

/**
 * @param mixed $value
 * @return array{company:array{image_id:int,image_url:string,title:string,text_1:string,text_2:string,button_text:string,button_url:string},founders:array{image_id:int,image_url:string,name:string,title:string,description_1:string,description_2:string}}
 */
function mosaic_sanitize_about_home_option(mixed $value): array {
	$defaults = mosaic_get_about_home_defaults();

	if (!is_array($value)) {
		return $defaults;
	}

	return [
		'company' => mosaic_sanitize_about_home_company($value['company'] ?? []),
		'founders' => mosaic_sanitize_about_home_founders($value['founders'] ?? []),
	];
}

/**
 * @return array{company:array{image_id:int,image_url:string,title:string,text_1:string,text_2:string,button_text:string,button_url:string},founders:array{image_id:int,image_url:string,name:string,title:string,description_1:string,description_2:string}}
 */
function mosaic_get_about_home(): array {
	$opt = get_option('mosaic_about_home', mosaic_get_about_home_defaults());
	return mosaic_sanitize_about_home_option($opt);
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		$hook = add_menu_page(
			'О нас (главная)',
			'О нас (главная)',
			'edit_theme_options',
			'mosaic-about-home',
			'mosaic_render_about_home_page',
			'dashicons-info',
			57
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			wp_enqueue_media();
			wp_register_script('mosaic-about-home-admin', false, ['jquery'], '1.0', true);
			wp_enqueue_script('mosaic-about-home-admin');

			$js = <<<'JS'
(function($){
  var frame;
  var currentTarget = null;

  function openMedia(targetPrefix){
    currentTarget = targetPrefix;
    if (!frame) {
      frame = wp.media({
        title: 'Выбрать изображение',
        button: { text: 'Использовать' },
        multiple: false,
        library: { type: 'image' }
      });
      frame.on('select', function(){
        if (!currentTarget) return;
        var attachment = frame.state().get('selection').first().toJSON();
        $('#' + currentTarget + '_image_id').val(attachment.id || 0);
        $('#' + currentTarget + '_image_url').val('');
        var url = attachment.url || '';
        var $img = $('#' + currentTarget + '_preview');
        if (url) {
          $img.attr('src', url).show();
          $('#' + currentTarget + '_remove').show();
        }
      });
    }
    frame.open();
  }

  $(document).on('click', '.mosaic-about-image-select', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    openMedia(prefix);
  });

  $(document).on('click', '.mosaic-about-image-remove', function(e){
    e.preventDefault();
    var prefix = $(this).data('prefix');
    $('#' + prefix + '_image_id').val(0);
    $('#' + prefix + '_image_url').val('');
    $('#' + prefix + '_preview').attr('src', '').hide();
    $(this).hide();
  });

  $(function(){
    $('.mosaic-about-preview').each(function(){
      var $img = $(this);
      var url = String($img.attr('src') || '').trim();
      if (url) {
        $img.show();
        $img.closest('.mosaic-about-uploader').find('.mosaic-about-image-remove').show();
      } else {
        $img.hide();
        $img.closest('.mosaic-about-uploader').find('.mosaic-about-image-remove').hide();
      }
    });
  });
})(jQuery);
JS;

			wp_add_inline_script('mosaic-about-home-admin', $js);

			echo '<style>
				body.toplevel_page_mosaic-about-home .mosaic-about-cards { display:grid; grid-template-columns:1fr 1fr; gap:24px; max-width:1600px; }
				@media (max-width:1200px) { body.toplevel_page_mosaic-about-home .mosaic-about-cards { grid-template-columns:1fr; } }
				body.toplevel_page_mosaic-about-home .mosaic-about-card { background:#fff; border:1px solid #dcdcde; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.06); overflow:hidden; }
				body.toplevel_page_mosaic-about-home .mosaic-about-card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 18px; background:linear-gradient(180deg,#101010 0%,#0b0b0b 100%); color:#fff; }
				body.toplevel_page_mosaic-about-home .mosaic-about-card-title { font-size:16px; font-weight:600; margin:0; }
				body.toplevel_page_mosaic-about-home .mosaic-about-card-subtitle { margin:4px 0 0; opacity:.75; font-size:13px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-badge { display:inline-flex; align-items:center; justify-content:center; min-width:56px; height:32px; padding:0 10px; border-radius:999px; background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.14); font-weight:600; letter-spacing:.02em; }
				body.toplevel_page_mosaic-about-home .mosaic-about-card-body { padding:18px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-field { margin:0 0 14px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-label { display:block; font-weight:600; margin:0 0 6px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-input { width:100%; border-radius:12px; padding:10px 12px; border-color:#dcdcde; }
				body.toplevel_page_mosaic-about-home .mosaic-about-textarea { width:100%; min-height:100px; border-radius:12px; padding:10px 12px; border-color:#dcdcde; }
				body.toplevel_page_mosaic-about-home .mosaic-about-uploader { border:1px dashed #c3c4c7; border-radius:12px; padding:12px; background:#fafafa; margin-bottom:14px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-preview { width:100%; height:180px; object-fit:cover; border-radius:10px; border:1px solid #dcdcde; background:#f6f7f7; display:none; margin-bottom:10px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-actions { display:flex; gap:8px; flex-wrap:wrap; }
				body.toplevel_page_mosaic-about-home .mosaic-about-actions .button { border-radius:10px; }
				body.toplevel_page_mosaic-about-home .mosaic-about-muted { color:#7a7a7a; font-size:12px; margin-top:6px; }
			</style>';
		});
	});

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_about_home', null);
		if ($existing === false) {
			add_option('mosaic_about_home', mosaic_get_about_home_defaults(), '', false);
		}

		register_setting(
			'mosaic_about_home_group',
			'mosaic_about_home',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_about_home_option',
				'default' => [],
			]
		);
	});
}

add_action('admin_post_mosaic_save_about_home', static function (): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}
	check_admin_referer('mosaic_about_home_save', 'mosaic_about_home_nonce');

	$company = [
		'image_id' => isset($_POST['company_image_id']) ? absint($_POST['company_image_id']) : 0,
		'image_url' => isset($_POST['company_image_url']) ? (string) $_POST['company_image_url'] : '',
		'title' => isset($_POST['company_title']) ? (string) $_POST['company_title'] : '',
		'text_1' => isset($_POST['company_text_1']) ? (string) $_POST['company_text_1'] : '',
		'text_2' => isset($_POST['company_text_2']) ? (string) $_POST['company_text_2'] : '',
		'button_text' => isset($_POST['company_button_text']) ? (string) $_POST['company_button_text'] : '',
		'button_url' => isset($_POST['company_button_url']) ? (string) $_POST['company_button_url'] : '',
	];

	if ($company['image_id'] > 0) {
		$company['image_url'] = '';
	}

	$founders = [
		'image_id' => isset($_POST['founders_image_id']) ? absint($_POST['founders_image_id']) : 0,
		'image_url' => isset($_POST['founders_image_url']) ? (string) $_POST['founders_image_url'] : '',
		'name' => isset($_POST['founders_name']) ? (string) $_POST['founders_name'] : '',
		'title' => isset($_POST['founders_title']) ? (string) $_POST['founders_title'] : '',
		'description_1' => isset($_POST['founders_description_1']) ? (string) $_POST['founders_description_1'] : '',
		'description_2' => isset($_POST['founders_description_2']) ? (string) $_POST['founders_description_2'] : '',
	];

	if ($founders['image_id'] > 0) {
		$founders['image_url'] = '';
	}

	$data = mosaic_sanitize_about_home_option([
		'company' => $company,
		'founders' => $founders,
	]);

	update_option('mosaic_about_home', $data, false);

	$redirect = add_query_arg(['page' => 'mosaic-about-home', 'updated' => '1'], admin_url('admin.php'));
	wp_safe_redirect($redirect);
	exit;
});

function mosaic_render_about_home_page(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	$data = mosaic_get_about_home();
	$company = $data['company'];
	$founders = $data['founders'];

	$companyPreview = '';
	if ($company['image_id'] > 0) {
		$companyPreview = (string) wp_get_attachment_image_url($company['image_id'], 'medium');
	}
	if ($companyPreview === '' && $company['image_url'] !== '') {
		$companyPreview = $company['image_url'];
	}

	$foundersPreview = '';
	if ($founders['image_id'] > 0) {
		$foundersPreview = (string) wp_get_attachment_image_url($founders['image_id'], 'medium');
	}
	if ($foundersPreview === '' && $founders['image_url'] !== '') {
		$foundersPreview = $founders['image_url'];
	}

	echo '<div class="wrap">';
	echo '<h1>О нас (главная)</h1>';
	echo '<p class="description">Настройка блока "О нас" на главной странице. Две секции: "О компании" и "Основатели".</p>';

	if (isset($_GET['updated']) && (string) $_GET['updated'] === '1') {
		echo '<div class="notice notice-success is-dismissible"><p>Сохранено.</p></div>';
	}

	echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
	echo '<input type="hidden" name="action" value="mosaic_save_about_home">';
	wp_nonce_field('mosaic_about_home_save', 'mosaic_about_home_nonce');

	echo '<div class="mosaic-about-cards">';

	// Card 1: О компании
	echo '<div class="mosaic-about-card">';
	echo '<div class="mosaic-about-card-header">';
	echo '<div>';
	echo '<p class="mosaic-about-card-title">О компании</p>';
	echo '<p class="mosaic-about-card-subtitle">Левый блок с фото студии и текстом</p>';
	echo '</div>';
	echo '<div class="mosaic-about-badge">1</div>';
	echo '</div>';
	echo '<div class="mosaic-about-card-body">';

	echo '<div class="mosaic-about-uploader">';
	echo '<img id="company_preview" class="mosaic-about-preview" src="' . esc_url($companyPreview) . '" alt="">';
	echo '<input type="hidden" id="company_image_id" name="company_image_id" value="' . esc_attr((string) $company['image_id']) . '">';
	echo '<input type="hidden" id="company_image_url" name="company_image_url" value="' . esc_attr($company['image_url']) . '">';
	echo '<div class="mosaic-about-actions">';
	echo '<button type="button" class="button mosaic-about-image-select" data-prefix="company">Выбрать фото</button>';
	echo '<button type="button" class="button mosaic-about-image-remove" id="company_remove" data-prefix="company" style="display:none;">Удалить</button>';
	echo '</div>';
	echo '<p class="mosaic-about-muted">Фото студии (рекомендуется 800×500px)</p>';
	echo '</div>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Заголовок</label>';
	echo '<input type="text" class="mosaic-about-input" name="company_title" value="' . esc_attr($company['title']) . '" placeholder="О компании"></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Текст (абзац 1)</label>';
	echo '<textarea class="mosaic-about-textarea" name="company_text_1" placeholder="Первый абзац...">' . esc_textarea($company['text_1']) . '</textarea></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Текст (абзац 2)</label>';
	echo '<textarea class="mosaic-about-textarea" name="company_text_2" placeholder="Второй абзац...">' . esc_textarea($company['text_2']) . '</textarea></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Текст кнопки</label>';
	echo '<input type="text" class="mosaic-about-input" name="company_button_text" value="' . esc_attr($company['button_text']) . '" placeholder="Больше о нашей истории"></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Ссылка кнопки</label>';
	echo '<input type="text" class="mosaic-about-input" name="company_button_url" value="' . esc_attr($company['button_url']) . '" placeholder="/about"></p>';

	echo '</div></div>';

	// Card 2: Основатели
	echo '<div class="mosaic-about-card">';
	echo '<div class="mosaic-about-card-header">';
	echo '<div>';
	echo '<p class="mosaic-about-card-title">Основатели</p>';
	echo '<p class="mosaic-about-card-subtitle">Правый блок с фото и описанием основателей</p>';
	echo '</div>';
	echo '<div class="mosaic-about-badge">2</div>';
	echo '</div>';
	echo '<div class="mosaic-about-card-body">';

	echo '<div class="mosaic-about-uploader">';
	echo '<img id="founders_preview" class="mosaic-about-preview" src="' . esc_url($foundersPreview) . '" alt="">';
	echo '<input type="hidden" id="founders_image_id" name="founders_image_id" value="' . esc_attr((string) $founders['image_id']) . '">';
	echo '<input type="hidden" id="founders_image_url" name="founders_image_url" value="' . esc_attr($founders['image_url']) . '">';
	echo '<div class="mosaic-about-actions">';
	echo '<button type="button" class="button mosaic-about-image-select" data-prefix="founders">Выбрать фото</button>';
	echo '<button type="button" class="button mosaic-about-image-remove" id="founders_remove" data-prefix="founders" style="display:none;">Удалить</button>';
	echo '</div>';
	echo '<p class="mosaic-about-muted">Фото основателей (рекомендуется 800×500px)</p>';
	echo '</div>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Имена</label>';
	echo '<input type="text" class="mosaic-about-input" name="founders_name" value="' . esc_attr($founders['name']) . '" placeholder="Алексей и Светлана Исаевы"></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Должность / подпись</label>';
	echo '<input type="text" class="mosaic-about-input" name="founders_title" value="' . esc_attr($founders['title']) . '" placeholder="Основатели компании Si Mosaic"></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Текст (абзац 1)</label>';
	echo '<textarea class="mosaic-about-textarea" name="founders_description_1" placeholder="Первый абзац...">' . esc_textarea($founders['description_1']) . '</textarea></p>';

	echo '<p class="mosaic-about-field"><label class="mosaic-about-label">Текст (абзац 2)</label>';
	echo '<textarea class="mosaic-about-textarea" name="founders_description_2" placeholder="Второй абзац...">' . esc_textarea($founders['description_2']) . '</textarea></p>';

	echo '</div></div>';

	echo '</div>'; // .mosaic-about-cards

	submit_button('Сохранить');
	echo '</form></div>';
}

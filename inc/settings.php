<?php

declare(strict_types=1);

/**
 * Настройки главной страницы (Админка -> Баннер на главной).
 *
 * @param mixed $value
 * @return array{image_id:int,title:string,subtitle:string,button_text:string,button_url:string}
 */
function mosaic_get_home_hero_defaults(): array {
	return [
		'image_id' => 0,
		'title' => "Si Mosaic - Создаем авторские\nпанно из художественного стекла",
		'subtitle' => "Каждая работа — это вручную созданная фактура, игра\nсвета и объём, который оживает на стене.",
		'button_text' => 'Каталог',
		'button_url' => '/catalog',
	];
}

function mosaic_sanitize_home_hero_option($value): array {
	if (!is_array($value)) {
		return [
			'image_id' => 0,
			'title' => '',
			'subtitle' => '',
			'button_text' => '',
			'button_url' => '',
		];
	}

	$imageId = absint($value['image_id'] ?? 0);
	$title = sanitize_textarea_field((string) ($value['title'] ?? ''));
	$subtitle = sanitize_textarea_field((string) ($value['subtitle'] ?? ''));
	$buttonText = sanitize_text_field((string) ($value['button_text'] ?? ''));
	$buttonUrlRaw = trim((string) ($value['button_url'] ?? ''));
	$buttonUrlRaw = sanitize_text_field($buttonUrlRaw);
	$buttonUrl = '';
	if ($buttonUrlRaw !== '') {
		// Разрешаем относительные URL ("/catalog", "?q=1", "#anchor") и абсолютные.
		if (preg_match('~^(\/|#|\?)~', $buttonUrlRaw) === 1) {
			$buttonUrl = $buttonUrlRaw;
		} else {
			$buttonUrl = esc_url_raw($buttonUrlRaw);
		}
	}

	return [
		'image_id' => $imageId,
		'title' => $title,
		'subtitle' => $subtitle,
		'button_text' => $buttonText,
		'button_url' => $buttonUrl,
	];
}

/**
 * Глобальные настройки сайта (контакты/соцсети/адрес/график).
 *
 * @return array{phone:string,phone2:string,email:string,address:string,work_hours:string,socials:array<string,string>}
 */
function mosaic_get_site_settings_defaults(): array {
	return [
		'phone' => '+7 (928) 206-07-75',
		'phone2' => '+7 (928) 400-32-55',
		'email' => 'si.mosaic@yandex.ru',
		'address' => 'Краснодар, Селезнёва 204',
		'work_hours' => 'Пн - Пт: 09:00 - 18:00',
		'socials' => [
			'whatsapp' => 'https://wa.me/79282060775',
			'vk' => 'https://vk.com/simosaic',
			'telegram' => 'https://t.me/simosaic',
			'youtube' => 'https://youtube.com/@simosaic',
			'pinterest' => 'https://pinterest.com/simosaic',
		],
	];
}

/**
 * @param mixed $value
 * @return array{phone:string,phone2:string,email:string,address:string,work_hours:string,socials:array<string,string>}
 */
function mosaic_sanitize_site_settings_option($value): array {
	$defaults = mosaic_get_site_settings_defaults();
	$defaultsSocials = $defaults['socials'];

	if (!is_array($value)) {
		return $defaults;
	}

	$phone = sanitize_text_field((string) ($value['phone'] ?? $defaults['phone']));
	$phone2 = sanitize_text_field((string) ($value['phone2'] ?? $defaults['phone2']));
	$emailCandidate = (string) ($value['email'] ?? $defaults['email']);
	$email = sanitize_email($emailCandidate);
	$address = sanitize_text_field((string) ($value['address'] ?? $defaults['address']));
	$workHours = sanitize_text_field((string) ($value['work_hours'] ?? $defaults['work_hours']));

	$socialsIn = $value['socials'] ?? [];
	$socials = [];
	foreach ($defaultsSocials as $key => $defaultUrl) {
		$raw = '';
		if (is_array($socialsIn) && array_key_exists($key, $socialsIn)) {
			$raw = trim((string) $socialsIn[$key]);
		}
		$raw = sanitize_text_field($raw);
		$socials[$key] = $raw !== '' ? esc_url_raw($raw) : '';
	}

	return [
		'phone' => $phone,
		'phone2' => $phone2,
		'email' => $email !== '' ? $email : $defaults['email'],
		'address' => $address !== '' ? $address : $defaults['address'],
		'work_hours' => $workHours !== '' ? $workHours : $defaults['work_hours'],
		'socials' => $socials,
	];
}

/**
 * Возвращает настройки сайта (с дефолтами и санитайзом).
 *
 * @return array{phone:string,phone2:string,email:string,address:string,work_hours:string,socials:array<string,string>}
 */
function mosaic_get_site_settings(): array {
	$opt = get_option('mosaic_site_settings', mosaic_get_site_settings_defaults());
	return mosaic_sanitize_site_settings_option($opt);
}

/**
 * @return array{display:string,href:string}
 */
function mosaic_get_phone_contact(): array {
	$settings = mosaic_get_site_settings();
	$display = trim((string) ($settings['phone'] ?? ''));
	if ($display === '') {
		$display = (string) (mosaic_get_site_settings_defaults()['phone'] ?? '');
	}

	$tel = preg_replace('~[^\d+]~', '', $display) ?? '';
	$tel = preg_replace('~^\+?8~', '+7', (string) $tel) ?? (string) $tel;
	if ($tel !== '' && $tel[0] !== '+') {
		$tel = '+' . $tel;
	}

	return [
		'display' => $display,
		'href' => $tel !== '' ? 'tel:' . $tel : '#',
	];
}

/**
 * @return array{display:string,href:string}
 */
function mosaic_get_phone2_contact(): array {
	$settings = mosaic_get_site_settings();
	$display = trim((string) ($settings['phone2'] ?? ''));
	if ($display === '') {
		$display = (string) (mosaic_get_site_settings_defaults()['phone2'] ?? '');
	}

	$tel = preg_replace('~[^\d+]~', '', $display) ?? '';
	$tel = preg_replace('~^\+?8~', '+7', (string) $tel) ?? (string) $tel;
	if ($tel !== '' && $tel[0] !== '+') {
		$tel = '+' . $tel;
	}

	return [
		'display' => $display,
		'href' => $tel !== '' ? 'tel:' . $tel : '#',
	];
}

/**
 * Рендер страницы "Баннер на главной" в админке.
 */
function mosaic_render_home_settings_page(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	echo '<div class="wrap">';
	echo '<h1>Баннер на главной</h1>';
	echo '<form method="post" action="options.php">';
	settings_fields('mosaic_home_settings');
	do_settings_sections('mosaic-home-settings');
	submit_button('Сохранить');
	echo '</form>';
	echo '</div>';
}

function mosaic_render_site_settings_page(): void {
	if (!current_user_can('edit_theme_options')) {
		wp_die('Недостаточно прав.');
	}

	echo '<div class="wrap">';
	echo '<h1>Настройки</h1>';
	echo '<form method="post" action="options.php">';
	settings_fields('mosaic_site_settings_group');
	do_settings_sections('mosaic-site-settings');
	submit_button('Сохранить');
	echo '</form>';
	echo '</div>';
}

if (is_admin()) {
	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		// Скрываем стандартные пункты меню WP (Записи, Комментарии, Консоль).
		if (function_exists('mosaic_hide_default_admin_menu_items')) {
			mosaic_hide_default_admin_menu_items();
		}

		// Разделитель с подписью "Мозаика" перед блоком настроек темы.
		if (function_exists('mosaic_add_admin_menu_separator')) {
			mosaic_add_admin_menu_separator('55.9', 'Мозаика');
			mosaic_add_admin_menu_separator('59.99');
		}

		$hook = add_menu_page(
			'Баннер на главной',
			'Баннер',
			'edit_theme_options',
			'mosaic-home-settings',
			'mosaic_render_home_settings_page',
			'dashicons-cover-image',
			57
		);

		add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($hook): void {
			if ($hookSuffix !== $hook) {
				return;
			}

			wp_enqueue_media();
			wp_register_script('mosaic-home-admin', false, ['jquery'], '1.0', true);
			wp_enqueue_script('mosaic-home-admin');

			$js = <<<'JS'
(function($){
  var frame;

  function setPreview(url){
    var $img = $('#mosaic-home-hero-image-preview');
    if (url) {
      $img.attr('src', url).removeClass('hidden');
      $('#mosaic-home-hero-image-remove').removeClass('hidden');
    } else {
      $img.attr('src', '').addClass('hidden');
      $('#mosaic-home-hero-image-remove').addClass('hidden');
    }
  }

  $('#mosaic-home-hero-image-select').on('click', function(e){
    e.preventDefault();
    if (frame) {
      frame.open();
      return;
    }
    frame = wp.media({
      title: 'Выбрать картинку баннера',
      button: { text: 'Использовать' },
      multiple: false,
      library: { type: 'image' }
    });
    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      $('#mosaic_home_hero_image_id').val(attachment.id || 0);
      setPreview(attachment.url || '');
    });
    frame.open();
  });

  $('#mosaic-home-hero-image-remove').on('click', function(e){
    e.preventDefault();
    $('#mosaic_home_hero_image_id').val(0);
    setPreview('');
  });
})(jQuery);
JS;

			wp_add_inline_script('mosaic-home-admin', $js);
		});
	});

	add_action('admin_head', static function (): void {
		// Стили для разделителей в админ-меню.
		echo '<style>
			#adminmenu li#separator-mosaic-55-9,
			#adminmenu li#separator-mosaic-59-99 {
				margin: 8px 0;
			}
			#adminmenu li#separator-mosaic-55-9 div.separator,
			#adminmenu li#separator-mosaic-59-99 div.separator {
				border-top-color: rgba(255, 255, 255, 0.55) !important;
			}
			/* Разделитель с подписью "Мозаика" */
			#adminmenu li.mosaic-labeled-separator {
				pointer-events: none;
				margin: 12px 0 4px 0;
			}
			#adminmenu li.mosaic-labeled-separator a.menu-top {
				color: rgba(240, 246, 252, 0.5) !important;
				font-size: 11px;
				font-weight: 400;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				padding: 4px 12px;
				background: transparent !important;
				cursor: default;
			}
			#adminmenu li.mosaic-labeled-separator a.menu-top:before {
				content: "";
				display: block;
				border-top: 1px solid rgba(255, 255, 255, 0.25);
				margin-bottom: 8px;
			}
			#adminmenu li.mosaic-labeled-separator a.menu-top .wp-menu-image,
			#adminmenu li.mosaic-labeled-separator a.menu-top .wp-menu-arrow {
				display: none;
			}
		</style>';
	});

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_home_hero', null);
		if ($existing === false) {
			add_option('mosaic_home_hero', mosaic_get_home_hero_defaults(), '', false);
		}

		register_setting(
			'mosaic_home_settings',
			'mosaic_home_hero',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_home_hero_option',
				'default' => [],
			]
		);

		add_settings_section(
			'mosaic_home_hero_section',
			'Большой баннер',
			'__return_false',
			'mosaic-home-settings'
		);

		add_settings_field(
			'mosaic_home_hero_image',
			'Картинка',
			static function (): void {
				$opt = get_option('mosaic_home_hero', mosaic_get_home_hero_defaults());
				$opt = mosaic_sanitize_home_hero_option($opt);
				$imageId = absint($opt['image_id'] ?? 0);
				$imageUrl = $imageId > 0
					? (string) wp_get_attachment_image_url($imageId, 'large')
					: (get_template_directory_uri() . '/img/banner/Photo.png');

				echo '<input type="hidden" id="mosaic_home_hero_image_id" name="mosaic_home_hero[image_id]" value="' . esc_attr((string) $imageId) . '">';
				echo '<div style="display:flex; gap:12px; align-items:flex-start;">';
				echo '<div>';
				echo '<img id="mosaic-home-hero-image-preview" src="' . esc_url($imageUrl) . '" style="max-width:360px; height:auto; border:1px solid #dcdcde; padding:4px; background:#fff;" alt="">';
				echo '</div>';
				echo '<div style="display:flex; flex-direction:column; gap:8px;">';
				echo '<button type="button" class="button" id="mosaic-home-hero-image-select">Выбрать</button>';
				echo '<button type="button" class="button ' . ($imageId > 0 ? '' : 'hidden') . '" id="mosaic-home-hero-image-remove">Удалить</button>';
				echo '<p class="description">Рекомендуемо: широкая картинка (примерно 1920×560).</p>';
				echo '</div>';
				echo '</div>';
			},
			'mosaic-home-settings',
			'mosaic_home_hero_section'
		);

		add_settings_field(
			'mosaic_home_hero_title',
			'Заголовок',
			static function (): void {
				$opt = get_option('mosaic_home_hero', mosaic_get_home_hero_defaults());
				$opt = mosaic_sanitize_home_hero_option($opt);
				$value = (string) ($opt['title'] ?? '');
				echo '<textarea name="mosaic_home_hero[title]" rows="3" class="large-text" placeholder="Заголовок">' . esc_textarea($value) . '</textarea>';
				echo '<p class="description">Можно переносы строк — они станут &lt;br&gt; в заголовке.</p>';
			},
			'mosaic-home-settings',
			'mosaic_home_hero_section'
		);

		add_settings_field(
			'mosaic_home_hero_subtitle',
			'Подзаголовок',
			static function (): void {
				$opt = get_option('mosaic_home_hero', mosaic_get_home_hero_defaults());
				$opt = mosaic_sanitize_home_hero_option($opt);
				$value = (string) ($opt['subtitle'] ?? '');
				echo '<textarea name="mosaic_home_hero[subtitle]" rows="3" class="large-text" placeholder="Подзаголовок">' . esc_textarea($value) . '</textarea>';
				echo '<p class="description">Можно переносы строк — они станут &lt;br&gt; в подзаголовке.</p>';
			},
			'mosaic-home-settings',
			'mosaic_home_hero_section'
		);

		add_settings_field(
			'mosaic_home_hero_button_text',
			'Название кнопки',
			static function (): void {
				$opt = get_option('mosaic_home_hero', mosaic_get_home_hero_defaults());
				$opt = mosaic_sanitize_home_hero_option($opt);
				$value = (string) ($opt['button_text'] ?? '');
				echo '<input type="text" name="mosaic_home_hero[button_text]" value="' . esc_attr($value) . '" class="regular-text" placeholder="Каталог">';
			},
			'mosaic-home-settings',
			'mosaic_home_hero_section'
		);

		add_settings_field(
			'mosaic_home_hero_button_url',
			'Ссылка кнопки',
			static function (): void {
				$opt = get_option('mosaic_home_hero', mosaic_get_home_hero_defaults());
				$opt = mosaic_sanitize_home_hero_option($opt);
				$value = (string) ($opt['button_url'] ?? '');
				echo '<input type="text" name="mosaic_home_hero[button_url]" value="' . esc_attr($value) . '" class="regular-text" placeholder="/catalog">';
				echo '<p class="description">Можно относительную (<code>/catalog</code>, <code>#contact</code>, <code>?q=1</code>) или полную ссылку.</p>';
			},
			'mosaic-home-settings',
			'mosaic_home_hero_section'
		);
	});

	add_action('admin_menu', static function (): void {
		if (!current_user_can('edit_theme_options')) {
			return;
		}

		add_menu_page(
			'Настройки',
			'Настройки',
			'edit_theme_options',
			'mosaic-site-settings',
			'mosaic_render_site_settings_page',
			'dashicons-admin-generic',
			59
		);
	}, 11);

	add_action('admin_init', static function (): void {
		$existing = get_option('mosaic_site_settings', null);
		if ($existing === false) {
			add_option('mosaic_site_settings', mosaic_get_site_settings_defaults(), '', false);
		}

		register_setting(
			'mosaic_site_settings_group',
			'mosaic_site_settings',
			[
				'type' => 'array',
				'sanitize_callback' => 'mosaic_sanitize_site_settings_option',
				'default' => [],
			]
		);

		add_settings_section(
			'mosaic_site_settings_section',
			'Контакты',
			'__return_false',
			'mosaic-site-settings'
		);

		add_settings_field(
			'mosaic_site_phone',
			'Телефон',
			static function (): void {
				$opt = mosaic_get_site_settings();
				$value = (string) ($opt['phone'] ?? '');
				echo '<input type="text" name="mosaic_site_settings[phone]" value="' . esc_attr($value) . '" class="regular-text" placeholder="+7 (___) ___-__-__">';
				echo '<p class="description">Показывается на сайте как есть. Ссылка <code>tel:</code> собирается автоматически.</p>';
			},
			'mosaic-site-settings',
			'mosaic_site_settings_section'
		);

		add_settings_field(
			'mosaic_site_phone2',
			'Телефон 2',
			static function (): void {
				$opt = mosaic_get_site_settings();
				$value = (string) ($opt['phone2'] ?? '');
				echo '<input type="text" name="mosaic_site_settings[phone2]" value="' . esc_attr($value) . '" class="regular-text" placeholder="+7 (___) ___-__-__">';
				echo '<p class="description">Второй номер телефона. Показывается на сайте рядом с первым.</p>';
			},
			'mosaic-site-settings',
			'mosaic_site_settings_section'
		);

		add_settings_field(
			'mosaic_site_email',
			'Почта',
			static function (): void {
				$opt = mosaic_get_site_settings();
				$value = (string) ($opt['email'] ?? '');
				echo '<input type="text" name="mosaic_site_settings[email]" value="' . esc_attr($value) . '" class="regular-text" placeholder="name@domain.com">';
				echo '<p class="description">На эту почту будут приходить заявки с сайта</p>';
			},
			'mosaic-site-settings',
			'mosaic_site_settings_section'
		);

		add_settings_field(
			'mosaic_site_address',
			'Адрес',
			static function (): void {
				$opt = mosaic_get_site_settings();
				$value = (string) ($opt['address'] ?? '');
				echo '<input type="text" name="mosaic_site_settings[address]" value="' . esc_attr($value) . '" class="regular-text" placeholder="Краснодар, Селезнёва 204">';
			},
			'mosaic-site-settings',
			'mosaic_site_settings_section'
		);

		add_settings_field(
			'mosaic_site_work_hours',
			'График',
			static function (): void {
				$opt = mosaic_get_site_settings();
				$value = (string) ($opt['work_hours'] ?? '');
				echo '<input type="text" name="mosaic_site_settings[work_hours]" value="' . esc_attr($value) . '" class="regular-text" placeholder="Пн - Пт: 09:00 - 18:00">';
			},
			'mosaic-site-settings',
			'mosaic_site_settings_section'
		);

		add_settings_section(
			'mosaic_site_socials_section',
			'Соцсети',
			'__return_false',
			'mosaic-site-settings'
		);

		$socialLabels = [
			'telegram' => 'Telegram',
			'whatsapp' => 'WhatsApp',
			'vk' => 'VK',
			'youtube' => 'YouTube',
			'pinterest' => 'Pinterest',
		];

		foreach ($socialLabels as $key => $label) {
			add_settings_field(
				'mosaic_site_social_' . $key,
				$label,
				static function () use ($key): void {
					$opt = mosaic_get_site_settings();
					$value = (string) (($opt['socials'][$key] ?? '') ?? '');
					echo '<input type="text" name="mosaic_site_settings[socials][' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://...">';
					echo '<p class="description">Оставь пустым — иконка не будет показываться.</p>';
				},
				'mosaic-site-settings',
				'mosaic_site_socials_section'
			);
		}
	});
}



<?php

declare(strict_types=1);

/**
 * Обработка контактной формы:
 * - Сохранение в БД
 * - Отправка в Telegram
 * - Админка для просмотра заявок
 */

// Создание таблицы при активации темы
add_action('after_switch_theme', 'mosaic_create_contacts_table');

// Создание таблицы при первом заходе в админку (если не создалась)
add_action('admin_init', 'mosaic_ensure_contacts_table_exists');

/**
 * Создаёт таблицу для хранения заявок
 */
function mosaic_create_contacts_table(): void
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mosaic_contacts';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		email varchar(255) NOT NULL,
		phone varchar(50) NOT NULL,
		form_type varchar(50) NOT NULL DEFAULT 'project',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		ip_address varchar(45) DEFAULT NULL,
		user_agent text DEFAULT NULL,
		PRIMARY KEY (id),
		KEY created_at (created_at),
		KEY email (email),
		KEY form_type (form_type)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);
}

/**
 * Проверяет и создаёт таблицу если её нет (однократно при первом заходе в админку)
 */
function mosaic_ensure_contacts_table_exists(): void
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mosaic_contacts';
	$option_key = 'mosaic_contacts_table_created';

	// Проверяем флаг создания таблицы
	if (get_option($option_key)) {
		return;
	}

	// Проверяем существование таблицы
	$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

	if (!$table_exists) {
		mosaic_create_contacts_table();
	}

	// Устанавливаем флаг, чтобы не проверять каждый раз
	update_option($option_key, true);
}

// Миграция: добавление колонки form_type если её нет
add_action('admin_init', 'mosaic_migrate_contacts_table');

/**
 * Добавляет колонку form_type в существующую таблицу
 */
function mosaic_migrate_contacts_table(): void
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mosaic_contacts';
	$migration_key = 'mosaic_contacts_form_type_added';

	if (get_option($migration_key)) {
		return;
	}

	// Проверяем существование колонки
	$column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'form_type'");

	if (empty($column_exists)) {
		$wpdb->query("ALTER TABLE {$table_name} ADD COLUMN form_type varchar(50) NOT NULL DEFAULT 'project' AFTER phone");
		$wpdb->query("ALTER TABLE {$table_name} ADD INDEX form_type (form_type)");
	}

	update_option($migration_key, true);
}

// Обработчики формы (стандартный POST)
add_action('admin_post_nopriv_contact_form', 'mosaic_handle_contact_form');
add_action('admin_post_contact_form', 'mosaic_handle_contact_form');

// AJAX обработчики (для модальных форм)
add_action('wp_ajax_contact_form_ajax', 'mosaic_handle_contact_form_ajax');
add_action('wp_ajax_nopriv_contact_form_ajax', 'mosaic_handle_contact_form_ajax');

/**
 * AJAX обработчик контактной формы
 */
function mosaic_handle_contact_form_ajax(): void
{
	// Проверка nonce
	if (
		!isset($_POST['contact_nonce']) ||
		!wp_verify_nonce((string) $_POST['contact_nonce'], 'contact_form_nonce')
	) {
		wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу.']);
	}

	// Валидация
	$name = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
	$email = isset($_POST['email']) ? sanitize_email((string) $_POST['email']) : '';
	$phone = isset($_POST['phone']) ? sanitize_text_field((string) $_POST['phone']) : '';
	$form_type = isset($_POST['form_type']) ? sanitize_key((string) $_POST['form_type']) : 'project';

	$allowed_types = ['project', 'showroom', 'consultation'];
	if (!in_array($form_type, $allowed_types, true)) {
		$form_type = 'project';
	}

	$errors = [];
	if (empty($name)) {
		$errors[] = 'Укажите имя';
	}
	if (empty($email) || !is_email($email)) {
		$errors[] = 'Укажите корректный email';
	}
	if (empty($phone)) {
		$errors[] = 'Укажите телефон';
	}

	if (!empty($errors)) {
		wp_send_json_error(['message' => implode('. ', $errors)]);
	}

	// Сохранение и отправка
	$contact_id = mosaic_save_contact($name, $email, $phone, $form_type);
	$telegram_sent = mosaic_send_to_telegram($name, $email, $phone, $form_type, $contact_id);
	$email_sent = mosaic_send_email_notification($name, $email, $phone, $form_type);

	if ($contact_id || $telegram_sent || $email_sent) {
		wp_send_json_success(['message' => 'Заявка отправлена! Мы свяжемся с вами в ближайшее время.']);
	} else {
		wp_send_json_error(['message' => 'Ошибка отправки. Попробуйте позже.']);
	}
}

/**
 * Обработчик отправки контактной формы
 */
function mosaic_handle_contact_form(): void
{
	// 1. Проверка nonce
	if (
		!isset($_POST['contact_nonce']) ||
		!wp_verify_nonce((string) $_POST['contact_nonce'], 'contact_form_nonce')
	) {
		wp_die('Ошибка безопасности. Обновите страницу и попробуйте снова.');
	}

	// 2. Валидация и санитизация
	$name = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
	$email = isset($_POST['email']) ? sanitize_email((string) $_POST['email']) : '';
	$phone = isset($_POST['phone']) ? sanitize_text_field((string) $_POST['phone']) : '';
	$form_type = isset($_POST['form_type']) ? sanitize_key((string) $_POST['form_type']) : 'project';

	// Допустимые типы форм
	$allowed_types = ['project', 'showroom', 'consultation'];
	if (!in_array($form_type, $allowed_types, true)) {
		$form_type = 'project';
	}

	$errors = [];

	if (empty($name)) {
		$errors[] = 'Укажите ваше имя';
	}

	if (empty($email)) {
		$errors[] = 'Укажите email';
	} elseif (!is_email($email)) {
		$errors[] = 'Неверный формат email';
	}

	if (empty($phone)) {
		$errors[] = 'Укажите телефон';
	}

	if (!empty($errors)) {
		wp_die(esc_html(implode('. ', $errors)));
	}

	// 3. Сохранение в БД
	$contact_id = mosaic_save_contact($name, $email, $phone, $form_type);

	// 4. Отправка в Telegram
	$telegram_sent = mosaic_send_to_telegram($name, $email, $phone, $form_type, $contact_id);

	// 5. Отправка на email
	$email_sent = mosaic_send_email_notification($name, $email, $phone, $form_type);

	// 6. Редирект с результатом
	$referer = wp_get_referer();

	// Если referer пустой или указывает на admin-post.php, используем HTTP_REFERER напрямую
	if (empty($referer) || strpos($referer, 'admin-post.php') !== false) {
		$referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
	}

	// Fallback на главную если всё ещё пусто
	if (empty($referer)) {
		$referer = home_url('/');
	}

	// Успех если хотя бы одно из действий прошло (БД, Telegram или Email)
	$status = ($contact_id || $telegram_sent || $email_sent) ? 'success' : 'error';

	// Определяем источник формы (modal = модальное окно, inline = обычная форма на странице)
	$form_source = isset($_POST['form_source']) ? sanitize_key((string) $_POST['form_source']) : 'inline';

	// Убираем существующий якорь из referer (если есть)
	$referer_no_hash = preg_replace('/#.*$/', '', $referer);

	// Для модальных форм - не добавляем якорь (страница товара, любая страница с модальной формой)
	// Для inline форм - добавляем якорь #contact-form чтобы проскроллить к форме
	if ($form_source === 'modal') {
		$redirect_url = add_query_arg('contact', $status, $referer_no_hash);
	} else {
		$redirect_url = add_query_arg('contact', $status, $referer_no_hash) . '#contact-form';
	}

	wp_safe_redirect($redirect_url);
	exit;
}

/**
 * Сохраняет контакт в БД
 *
 * @param string $name      Имя
 * @param string $email     Email
 * @param string $phone     Телефон
 * @param string $form_type Тип формы (project, showroom, consultation)
 * @return int|false ID записи или false при ошибке
 */
function mosaic_save_contact(string $name, string $email, string $phone, string $form_type = 'project')
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mosaic_contacts';

	$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

	$result = $wpdb->insert(
		$table_name,
		[
			'name' => $name,
			'email' => $email,
			'phone' => $phone,
			'form_type' => $form_type,
			'ip_address' => sanitize_text_field($ip_address),
			'user_agent' => sanitize_text_field($user_agent),
		],
		['%s', '%s', '%s', '%s', '%s', '%s']
	);

	return $result ? (int) $wpdb->insert_id : false;
}

/**
 * Возвращает человекочитаемое название типа формы
 *
 * @param string $form_type Тип формы
 * @return string Название формы
 */
function mosaic_get_form_type_label(string $form_type): string
{
	$labels = [
		'project' => 'Обсудить проект',
		'showroom' => 'Записаться в шоурум',
		'consultation' => 'Получить консультацию',
	];

	return $labels[$form_type] ?? $form_type;
}

/**
 * Отправляет сообщение в Telegram
 *
 * @param string    $name       Имя
 * @param string    $email      Email
 * @param string    $phone      Телефон
 * @param string    $form_type  Тип формы
 * @param int|false $contact_id ID заявки в БД или false при ошибке
 * @return bool True если отправлено успешно
 */
function mosaic_send_to_telegram(string $name, string $email, string $phone, string $form_type = 'project', $contact_id = 0): bool
{
	$bot_token = defined('MOSAIC_TELEGRAM_BOT_TOKEN') ? MOSAIC_TELEGRAM_BOT_TOKEN : '';
	$chat_id = defined('MOSAIC_TELEGRAM_CHAT_ID') ? MOSAIC_TELEGRAM_CHAT_ID : '';

	if (empty($bot_token) || empty($chat_id)) {
		error_log('Mosaic: Telegram настройки не заданы в wp-config.php');
		return false;
	}

	// Получаем название формы
	$form_label = mosaic_get_form_type_label($form_type);

	// Формируем сообщение
	$message = "🔔 <b>Новая заявка с сайта</b>\n\n";
	$message .= "📋 <b>Форма:</b> " . esc_html($form_label) . "\n";
	$message .= "👤 <b>Имя:</b> " . esc_html($name) . "\n";
	$message .= "📧 <b>Email:</b> " . esc_html($email) . "\n";
	$message .= "📱 <b>Телефон:</b> " . esc_html($phone) . "\n";
	$message .= "\n⏰ <i>" . current_time('d.m.Y H:i') . "</i>";

	$api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

	error_log('Mosaic Telegram: отправка запроса к API');

	$response = wp_remote_post($api_url, [
		'body' => [
			'chat_id' => $chat_id,
			'text' => $message,
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true,
		],
		'timeout' => 15,
	]);

	if (is_wp_error($response)) {
		error_log('Mosaic Telegram ERROR: ' . $response->get_error_message());
		return false;
	}

	$code = wp_remote_retrieve_response_code($response);
	$body = json_decode(wp_remote_retrieve_body($response), true);

	error_log('Mosaic Telegram: HTTP code=' . $code);
	error_log('Mosaic Telegram: response=' . wp_remote_retrieve_body($response));

	if ($code !== 200 || !isset($body['ok']) || !$body['ok']) {
		error_log('Mosaic Telegram API FAILED: ' . wp_remote_retrieve_body($response));
		return false;
	}

	error_log('Mosaic Telegram: успешно отправлено!');
	return true;
}

/**
 * Отправляет уведомление о заявке на email из настроек
 *
 * @param string $name      Имя клиента
 * @param string $email     Email клиента
 * @param string $phone     Телефон клиента
 * @param string $form_type Тип формы
 * @return bool True если отправлено успешно
 */
function mosaic_send_email_notification(string $name, string $email, string $phone, string $form_type = 'project'): bool
{
	$settings = mosaic_get_site_settings();
	$to_email = $settings['email'] ?? '';

	if (empty($to_email) || !is_email($to_email)) {
		error_log('Mosaic Email: email не указан в настройках или некорректен');
		return false;
	}

	$form_label = mosaic_get_form_type_label($form_type);
	$site_name = get_bloginfo('name');
	$date_time = current_time('d.m.Y H:i');

	$subject = "Новая заявка с сайта: {$form_label}";

	$message = "Новая заявка с сайта {$site_name}\n\n";
	$message .= "Форма: {$form_label}\n";
	$message .= "Имя: {$name}\n";
	$message .= "Email: {$email}\n";
	$message .= "Телефон: {$phone}\n\n";
	$message .= "Дата: {$date_time}\n";

	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		"Reply-To: {$name} <{$email}>",
	];

	$sent = wp_mail($to_email, $subject, $message, $headers);

	if ($sent) {
		error_log("Mosaic Email: успешно отправлено на {$to_email}");
	} else {
		error_log("Mosaic Email: ошибка отправки на {$to_email}");
	}

	return $sent;
}

// Уведомления пользователю
add_action('wp_footer', function (): void {
	if (!isset($_GET['contact'])) {
		return;
	}

	$status = sanitize_key((string) $_GET['contact']);
	?>
	<style>
		.mosaic-form-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 9999;
			animation: fadeIn 0.3s ease-out;
		}
		.mosaic-form-notice {
			position: relative;
			max-width: 500px;
			width: 90%;
			padding: 30px 50px 30px 30px;
			border-radius: 8px;
			color: #FFFFFF;
			font-size: 18px;
			font-weight: 600;
			line-height: 1.5;
			box-shadow: 0 8px 32px rgba(0,0,0,0.4);
			animation: scaleIn 0.3s ease-out;
		}
		.mosaic-form-notice.success {
			background: #A36217;
			border: 2px solid #C77A1F;
		}
		.mosaic-form-notice.error {
			background: #1C0101;
			border: 2px solid #A36217;
		}
		.mosaic-form-notice-close {
			position: absolute;
			top: 15px;
			right: 15px;
			width: 24px;
			height: 24px;
			cursor: pointer;
			opacity: 0.7;
			transition: opacity 0.2s;
		}
		.mosaic-form-notice-close:hover {
			opacity: 1;
		}
		.mosaic-form-notice-close:before,
		.mosaic-form-notice-close:after {
			content: '';
			position: absolute;
			top: 50%;
			left: 50%;
			width: 16px;
			height: 2px;
			background: white;
		}
		.mosaic-form-notice-close:before {
			transform: translate(-50%, -50%) rotate(45deg);
		}
		.mosaic-form-notice-close:after {
			transform: translate(-50%, -50%) rotate(-45deg);
		}
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		@keyframes scaleIn {
			from {
				transform: scale(0.8);
				opacity: 0;
			}
			to {
				transform: scale(1);
				opacity: 1;
			}
		}
		@keyframes fadeOut {
			from { opacity: 1; }
			to { opacity: 0; }
		}
	</style>
	<script>
	(function() {
		const overlay = document.createElement('div');
		overlay.className = 'mosaic-form-overlay';
		
		const notice = document.createElement('div');
		notice.className = 'mosaic-form-notice <?php echo $status === 'success' ? 'success' : 'error'; ?>';
		
		<?php if ($status === 'success') : ?>
			notice.textContent = '✅ Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.';
		<?php else : ?>
			notice.textContent = '❌ Ошибка отправки. Попробуйте позже или свяжитесь напрямую.';
		<?php endif; ?>
		
		const closeBtn = document.createElement('div');
		closeBtn.className = 'mosaic-form-notice-close';
		notice.appendChild(closeBtn);
		
		overlay.appendChild(notice);
		document.body.appendChild(overlay);
		
		function closeNotice() {
			overlay.style.animation = 'fadeOut 0.3s ease-in';
			setTimeout(() => overlay.remove(), 300);
		}
		
		closeBtn.addEventListener('click', closeNotice);
		overlay.addEventListener('click', (e) => {
			if (e.target === overlay) closeNotice();
		});
		
		setTimeout(closeNotice, 5000);
		
		const url = new URL(window.location);
		url.searchParams.delete('contact');
		window.history.replaceState({}, '', url);
	})();
	</script>
	<?php
});

// ========== АДМИНКА ==========

// Добавляем страницу в меню админки
add_action('admin_menu', 'mosaic_add_contacts_admin_page');

/**
 * Регистрирует страницу заявок в меню админки
 */
function mosaic_add_contacts_admin_page(): void
{
	add_menu_page(
		'Заявки',                          // Page title
		'Заявки',                          // Menu title
		'manage_options',                  // Capability
		'mosaic-contacts',                 // Menu slug
		'mosaic_render_contacts_page',     // Callback
		'dashicons-email-alt',             // Icon
		56.1                               // Position (после Каталога = 56, перед Баннером = 57)
	);
}

/**
 * Рендерит страницу со списком заявок
 */
function mosaic_render_contacts_page(): void
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mosaic_contacts';

	// Обработка действий
	if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
		check_admin_referer('delete_contact_' . (int) $_GET['id']);
		$wpdb->delete($table_name, ['id' => (int) $_GET['id']], ['%d']);
		echo '<div class="notice notice-success"><p>Заявка удалена</p></div>';
	}

	// Экспорт в CSV
	if (isset($_GET['action']) && $_GET['action'] === 'export') {
		check_admin_referer('export_contacts');
		mosaic_export_contacts_csv();
		exit;
	}

	// Получаем данные
	$per_page = 20;
	$page = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
	$offset = ($page - 1) * $per_page;

	$total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
	$contacts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);

	$total_pages = ceil($total / $per_page);
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Заявки с сайта</h1>

		<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=mosaic-contacts&action=export'), 'export_contacts')); ?>" class="page-title-action">
			Экспорт в CSV
		</a>

		<hr class="wp-header-end">

		<p>Всего заявок: <strong><?php echo esc_html((string) $total); ?></strong></p>

		<?php if (!empty($contacts)) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 50px;">ID</th>
						<th style="width: 180px;">Форма</th>
						<th>Имя</th>
						<th>Email</th>
						<th>Телефон</th>
						<th>Дата</th>
						<th>IP</th>
						<th style="width: 100px;">Действия</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($contacts as $contact) : ?>
						<?php
						$form_type = isset($contact->form_type) ? $contact->form_type : 'project';
						$form_label = mosaic_get_form_type_label($form_type);
						$badge_class = match ($form_type) {
							'showroom' => 'background: #2271b1; color: #fff;',
							'consultation' => 'background: #135e96; color: #fff;',
							default => 'background: #787c82; color: #fff;',
						};
						?>
						<tr>
							<td><?php echo esc_html((string) $contact->id); ?></td>
							<td>
								<span style="display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; <?php echo $badge_class; ?>">
									<?php echo esc_html($form_label); ?>
								</span>
							</td>
							<td><strong><?php echo esc_html($contact->name); ?></strong></td>
							<td>
								<a href="mailto:<?php echo esc_attr($contact->email); ?>">
									<?php echo esc_html($contact->email); ?>
								</a>
							</td>
							<td>
								<a href="tel:<?php echo esc_attr($contact->phone); ?>">
									<?php echo esc_html($contact->phone); ?>
								</a>
							</td>
							<td>
								<?php
								$date = new DateTime($contact->created_at);
								echo esc_html($date->format('d.m.Y H:i'));
								?>
							</td>
							<td><small><?php echo esc_html((string) $contact->ip_address); ?></small></td>
							<td>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=mosaic-contacts&action=delete&id=' . $contact->id), 'delete_contact_' . $contact->id)); ?>"
								   class="button button-small"
								   onclick="return confirm('Удалить эту заявку?');">
									Удалить
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<div class="tablenav">
					<div class="tablenav-pages">
						<?php
						echo paginate_links([
							'base' => add_query_arg('paged', '%#%'),
							'format' => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total' => $total_pages,
							'current' => $page,
						]);
						?>
					</div>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<p>Заявок пока нет.</p>
		<?php endif; ?>
	</div>

	<style>
		.wp-list-table th { background: #f0f0f1; font-weight: 600; }
		.tablenav-pages { margin: 20px 0; }
		.tablenav-pages .pagination-links { font-size: 14px; }
		.tablenav-pages a { padding: 5px 10px; text-decoration: none; }
	</style>
	<?php
}

/**
 * Экспортирует заявки в CSV
 */
function mosaic_export_contacts_csv(): void
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'mosaic_contacts';

	$contacts = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC");

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=contacts-' . date('Y-m-d') . '.csv');

	$output = fopen('php://output', 'w');

	// BOM для корректного отображения кириллицы в Excel
	fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

	// Заголовки
	fputcsv($output, ['ID', 'Форма', 'Имя', 'Email', 'Телефон', 'Дата', 'IP']);

	// Данные
	foreach ($contacts as $contact) {
		$form_type = isset($contact->form_type) ? $contact->form_type : 'project';
		fputcsv($output, [
			$contact->id,
			mosaic_get_form_type_label($form_type),
			$contact->name,
			$contact->email,
			$contact->phone,
			$contact->created_at,
			$contact->ip_address,
		]);
	}

	fclose($output);
}


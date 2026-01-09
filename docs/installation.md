# Установка и развёртывание

Руководство по установке темы Mosaic на production-сервер.

## Требования

- **WordPress**: 6.0+
- **PHP**: 7.4+
- **MySQL/MariaDB**: 5.7+
- **Веб-сервер**: Nginx или Apache
- **Плагины**: не требуются (тема автономна)

## 1. Клонирование репозитория

На сервере перейди в `wp-content/themes/` и клонируй репозиторий:

```bash
cd /path/to/wordpress/wp-content/themes/
git clone https://github.com/lotar76/mosaic-theme.git mosaic
```

## 2. Активация темы

В админке WordPress:
**Внешний вид → Темы → Mosaic → Активировать**

При активации автоматически:
- Создаётся таблица `wp_mosaic_contacts` для заявок
- Регистрируются CPT и таксономии каталога
- Инициализируются базовые настройки

## 3. Настройка сервера

### PHP настройки

Убедись, что серверные лимиты позволяют загружать видео до 8MB.

**php.ini** (или php-fpm pool):
```ini
upload_max_filesize = 8M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 300
```

### Nginx настройки

```nginx
server {
    # ...
    client_max_body_size 12m;
    
    # Опционально: кеширование статики
    location ~* \.(jpg|jpeg|gif|png|svg|webp|ico|css|js|woff|woff2|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache настройки

```apache
# .htaccess или httpd.conf
php_value upload_max_filesize 8M
php_value post_max_size 12M
php_value memory_limit 256M
```

После изменений перезапусти PHP/веб-сервер:
```bash
# Nginx + PHP-FPM
sudo systemctl restart php8.1-fpm nginx

# Apache
sudo systemctl restart apache2
```

## 4. Настройка Telegram бота

Подробная инструкция в [telegram.md](telegram.md).

**Кратко:**
1. Создай бота через @BotFather
2. Получи Bot Token
3. Получи Chat ID (личный или группы)
4. Добавь в `wp-config.php`:

```php
// Telegram Bot для контактной формы
define('MOSAIC_TELEGRAM_BOT_TOKEN', '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz');
define('MOSAIC_TELEGRAM_CHAT_ID', '-100123456789');
```

**⚠️ Важно:** Никогда не коммить `wp-config.php` в Git!

## 5. Первичная настройка контента

После активации темы обязательно:

### 5.1. Постоянные ссылки
**Настройки → Постоянные ссылки → Сохранить**

Это обновит rewrite rules для каталога.

### 5.2. Каталог
1. **Каталог → Разделы каталога** → добавить термины
2. Для каждого раздела:
   - Выбрать изображение карточки
   - Выбрать изображение "в интерьере"
   - Загрузить видео через медиатеку (до 8MB)

3. **Каталог → Все товары** → создать товары
   - Заполнить галерею
   - Указать материал, технику, размер/цвет
   - Выбрать похожие товары

### 5.3. Контент главной страницы
1. **Новости** → добавить новости
   - Заголовок, галерея (первое фото = превью), текст
2. **Процесс работы** → добавить блоки
   - Картинка, название, описание
   - Drag&drop для сортировки
3. **Настройки** → заполнить:
   - Контакты (телефон, email, адрес, график)
   - Соцсети (Telegram, WhatsApp, VK, YouTube, Pinterest)

### 5.4. Проверка заявок
**Заявки** → проверить что таблица создана

После первой заявки:
- Уведомление придёт в Telegram
- Запись появится в админке

## 6. Миграция с локалки

### Вариант A: Та же база (или синхронизируется)

```bash
# На локалке
cd wp-content/themes/mosaic/
git push origin main

# На проде
cd /path/to/wordpress/wp-content/themes/mosaic/
git pull origin main
```

**Важно:** Legacy video URL будут автоматически удалены при первом заходе в админку. Нужно заново выбрать видео через медиатеку.

### Вариант B: Другая база

Если на проде другая БД — нужно заново создать весь контент через админку (см. п.5).

**Альтернатива:** Экспорт/импорт через WP CLI или плагины миграции.

## 7. Обновление на проде

```bash
cd /path/to/wordpress/wp-content/themes/mosaic/
git pull origin main
```

После `git pull`:
- Зайди в админку — миграции (если есть) запустятся автоматически
- Проверь `/wp-admin/` на наличие новых опций/полей
- Очисти кеш (если используется кеширование)

## 8. Проверка работоспособности

### Чеклист после установки:

- [ ] Тема активирована
- [ ] Постоянные ссылки сохранены
- [ ] Разделы каталога созданы и видны на странице `/catalog/`
- [ ] Товары отображаются в разделах
- [ ] Главная страница корректно отображает все секции
- [ ] Контактная форма отправляет заявки
- [ ] Уведомления приходят в Telegram
- [ ] Заявки сохраняются в админке
- [ ] Видео в разделах каталога проигрываются
- [ ] Адаптивность работает на мобильных устройствах

### Проверка производительности:

```bash
# Проверка размера загруженных медиа
du -sh wp-content/uploads/

# Проверка логов ошибок
tail -f wp-content/debug.log

# Проверка PHP ошибок
tail -f /var/log/php8.1-fpm.log
```

## 9. Рекомендации для продакшн

### Безопасность

```php
// wp-config.php
define('DISALLOW_FILE_EDIT', true);  // Отключить редактор тем/плагинов
define('WP_DEBUG', false);           // Выключить debug на проде
define('WP_DEBUG_DISPLAY', false);
```

### Производительность

1. **Кеширование:**
   - Используй Redis/Memcached для object cache
   - Настрой nginx кеш для статики

2. **Оптимизация БД:**
   ```sql
   -- Периодически оптимизируй таблицы
   OPTIMIZE TABLE wp_mosaic_contacts;
   OPTIMIZE TABLE wp_posts;
   OPTIMIZE TABLE wp_postmeta;
   ```

3. **CDN:**
   - Выгрузи статику (CSS/JS/images) на CDN
   - Настрой CORS для видео

### Бэкапы

```bash
# Бэкап БД
mysqldump -u user -p db_name > backup_$(date +%Y%m%d).sql

# Бэкап uploads
tar -czf uploads_$(date +%Y%m%d).tar.gz wp-content/uploads/

# Бэкап темы (хотя она в git)
tar -czf mosaic_$(date +%Y%m%d).tar.gz wp-content/themes/mosaic/
```

Автоматизируй через cron.

## 10. Troubleshooting

### Проблема: "Заявка не отправлена"

**Решение:**
1. Включи debug:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
2. Проверь `wp-content/debug.log`
3. Проверь что таблица `wp_mosaic_contacts` существует
4. Проверь nonce и AJAX endpoint

### Проблема: "Telegram не получает сообщения"

**Решение:**
1. Проверь токен и Chat ID в `wp-config.php`
2. Убедись что бот запущен (`/start`)
3. Для групп — бот должен быть администратором
4. Проверь `https://api.telegram.org/bot<TOKEN>/getUpdates`

### Проблема: "Видео не загружаются"

**Решение:**
1. Проверь `upload_max_filesize` и `post_max_size` в PHP
2. Проверь `client_max_body_size` в Nginx
3. Проверь права на `wp-content/uploads/`
4. Лимит: 8MB, формат: MP4

### Проблема: "404 на страницах каталога"

**Решение:**
1. **Настройки → Постоянные ссылки → Сохранить**
2. Проверь что тема активирована
3. Проверь `.htaccess` или nginx конфиг

---

**Следующий шаг:** [Настройка Telegram →](telegram.md)


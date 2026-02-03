<?php
/**
 * Template Name: Partnership Page
 * Template: Страница Партнерская программа
 */

declare(strict_types=1);

get_header();

$data = mosaic_get_partnership_page();
$title = $data['title'];
$content = $data['content'];
?>

<main class="flex-grow">
	<!-- Breadcrumbs -->
	<div class="pt-[30px] min-[1280px]:pt-[40px]">
		<?php get_template_part('template-parts/breadcrumbs'); ?>
	</div>

	<!-- Page Header -->
	<section class="bg-black py-[40px] min-[1280px]:py-[60px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<h1 class="text-white text-[28px] md:text-[40px] min-[1280px]:text-[56px] leading-[110%] tracking-[-0.01em] font-normal mb-0">
				<?= esc_html($title); ?>
			</h1>
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>
	</section>

	<!-- Content Section -->
	<section class="bg-black py-[40px] min-[1280px]:py-[60px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<div class="max-w-[900px] mx-auto">
				<div class="text-white text-base md:text-[18px] leading-[160%] prose prose-invert max-w-none">
					<?= wp_kses_post($content); ?>
				</div>
			</div>
		</div>
	</section>

	<!-- Consultation Form Section -->
	<section class="bg-black pb-[60px] min-[1280px]:pb-[80px]">
		<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
			<div class="max-w-[900px] mx-auto">
				<div class="bg-gray p-8 min-[1280px]:p-12">
					<div class="mb-8">
						<h2 class="text-white text-[24px] md:text-[32px] min-[1280px]:text-[40px] leading-[110%] tracking-[-0.01em] font-normal mb-0">
							Обсудить<br>партнерство
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<!-- Form -->
					<form id="partnership-consultation-form" class="flex flex-col gap-4">
						<input type="hidden" name="action" value="contact_form_ajax">
						<input type="hidden" name="form_type" value="partnership">
						<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

						<div>
							<input
								type="text"
								name="name"
								placeholder="Имя"
								required
								class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								aria-label="Ваше имя"
							>
						</div>

						<div>
							<input
								type="email"
								name="email"
								placeholder="Почта"
								required
								class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								aria-label="Ваш email"
							>
						</div>

						<div>
							<input
								type="tel"
								name="phone"
								placeholder="Телефон"
								required
								class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								aria-label="Ваш телефон"
							>
						</div>

						<button
							type="submit"
							class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors disabled:opacity-50"
							aria-label="Отправить заявку"
						>
							<span class="btn-text">Отправить заявку</span>
							<span class="btn-loading hidden">Отправка...</span>
						</button>

						<!-- Consent Checkboxes -->
						<?php
						$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
						$privacyPolicyUrl = is_array($siteSettings) ? trim((string) ($siteSettings['privacy_policy_url'] ?? '/privacy-policy/')) : '/privacy-policy/';
						$newsletterPolicyUrl = is_array($siteSettings) ? trim((string) ($siteSettings['newsletter_policy_url'] ?? '/newsletter-policy/')) : '/newsletter-policy/';
						?>
						<div class="space-y-3">
							<label class="flex items-start gap-3 cursor-pointer group">
								<input type="checkbox" name="consent_privacy" value="1" checked required class="mt-0.5 w-4 h-4 accent-primary cursor-pointer">
								<span class="text-white/60 text-sm text-left group-hover:text-white/80 transition-colors">
									Согласен с <a href="<?= esc_url(home_url($privacyPolicyUrl)); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" onclick="event.stopPropagation();">обработкой персональных данных</a>
								</span>
							</label>
							<label class="flex items-start gap-3 cursor-pointer group">
								<input type="checkbox" name="consent_newsletter" value="1" class="mt-0.5 w-4 h-4 accent-primary cursor-pointer">
								<span class="text-white/60 text-sm text-left group-hover:text-white/80 transition-colors">
									Согласен на <a href="<?= esc_url(home_url($newsletterPolicyUrl)); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" onclick="event.stopPropagation();">получение рассылок</a>
								</span>
							</label>
						</div>

						<!-- Сообщение результата -->
						<div id="partnership-consultation-result" class="hidden p-4 text-center"></div>
					</form>
				</div>
			</div>
		</div>
	</section>
</main>

<script>
(function() {
	var consultForm = document.getElementById('partnership-consultation-form');
	if (!consultForm) return;

	consultForm.addEventListener('submit', function(e) {
		e.preventDefault();

		var form = this;
		var btn = form.querySelector('button[type="submit"]');
		var btnText = btn.querySelector('.btn-text');
		var btnLoading = btn.querySelector('.btn-loading');
		var result = document.getElementById('partnership-consultation-result');

		// Показываем загрузку
		btn.disabled = true;
		btnText.classList.add('hidden');
		btnLoading.classList.remove('hidden');
		result.classList.add('hidden');

		// Собираем данные формы
		var formData = new FormData(form);

		// Отправляем AJAX
		fetch('<?= esc_url(admin_url('admin-ajax.php')); ?>', {
			method: 'POST',
			body: formData
		})
		.then(function(response) { return response.json(); })
		.then(function(data) {
			// Восстанавливаем кнопку
			btn.disabled = false;
			btnText.classList.remove('hidden');
			btnLoading.classList.add('hidden');

			// Показываем результат
			result.classList.remove('hidden');
			if (data.success) {
				// Скрываем все поля формы, оставляем только результат
				var fields = form.querySelectorAll('input, button, label');
				fields.forEach(function(el) { el.style.display = 'none'; });
				result.className = 'p-6 text-center bg-primary/20 text-white text-lg';
				result.innerHTML = '<div class="mb-2 text-2xl">✓</div>' + data.data.message;
				form.reset();
				// Восстановить форму через 5 сек
				setTimeout(function() {
					fields.forEach(function(el) { el.style.display = ''; });
					result.classList.add('hidden');
				}, 5000);
			} else {
				result.className = 'p-4 text-center bg-red-500/20 text-white';
				result.textContent = data.data.message || 'Ошибка отправки';
			}
		})
		.catch(function() {
			btn.disabled = false;
			btnText.classList.remove('hidden');
			btnLoading.classList.add('hidden');
			result.classList.remove('hidden');
			result.className = 'p-4 text-center bg-red-500/20 text-white';
			result.textContent = 'Ошибка сети. Попробуйте позже.';
		});
	});
})();
</script>

<?php get_footer(); ?>

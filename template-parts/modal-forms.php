<?php
/**
 * Template Part: Modal Forms
 * Модальные окна с формами: Шоурум и Консультация
 */

declare(strict_types=1);

// Получаем контактную информацию
$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
$phoneContact = function_exists('mosaic_get_phone_contact') ? mosaic_get_phone_contact() : ['display' => '+7 (928) 206-07-75', 'href' => 'tel:+79282060775'];
$phone2Contact = function_exists('mosaic_get_phone2_contact') ? mosaic_get_phone2_contact() : ['display' => '+7 (928) 400-32-55', 'href' => 'tel:+79284003255'];
$address = is_array($siteSettings) ? trim((string) ($siteSettings['address'] ?? '')) : '';
$address = $address !== '' ? $address : 'Краснодар, Селезнёва 204';
$workHours = is_array($siteSettings) ? trim((string) ($siteSettings['work_hours'] ?? '')) : '';
$workHours = $workHours !== '' ? $workHours : 'Пн - Пт: 09:00 - 18:00';

// URL для чекбоксов согласий
$privacyPolicyUrl = is_array($siteSettings) ? trim((string) ($siteSettings['privacy_policy_url'] ?? '/privacy-policy/')) : '/privacy-policy/';
$newsletterPolicyUrl = is_array($siteSettings) ? trim((string) ($siteSettings['newsletter_policy_url'] ?? '/newsletter-policy/')) : '/newsletter-policy/';
?>

<!-- Modal: Записаться в шоурум -->
<div id="modal-showroom" class="mosaic-modal fixed inset-0 z-[100] hidden" aria-hidden="true" role="dialog" aria-labelledby="modal-showroom-title">
	<div class="mosaic-modal-overlay absolute inset-0 bg-black/80" data-modal-close></div>
	<div class="mosaic-modal-container relative z-10 flex items-center justify-center min-h-screen p-4">
		<div class="mosaic-modal-content bg-gray relative w-full max-w-[900px] p-8 min-[1280px]:p-12">
			<!-- Close Button -->
			<button
				type="button"
				class="absolute top-4 right-4 w-8 h-8 text-white/60 hover:text-white transition-colors"
				data-modal-close
				aria-label="Закрыть"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>

			<div class="grid grid-cols-1 min-[768px]:grid-cols-2 gap-8 min-[1280px]:gap-12">
				<!-- Left: Info -->
				<div class="space-y-6">
					<div>
						<h2 id="modal-showroom-title" class="text-white font-century font-normal text-[28px] min-[1280px]:text-[40px] leading-[110%] tracking-[-0.01em] mb-0">
							Записаться<br>в шоурум
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<div class="space-y-4 text-white font-century font-normal text-[16px] min-[1280px]:text-[18px] leading-[145%]">
						<div><?= esc_html($address); ?></div>
						<div><?= esc_html($workHours); ?></div>
						<div class="flex flex-wrap gap-x-3 gap-y-2">
							<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors">
								<?= esc_html((string) $phoneContact['display']); ?>
							</a>
							<a href="<?= esc_url($phone2Contact['href']); ?>" class="hover:text-primary transition-colors">
								<?= esc_html((string) $phone2Contact['display']); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- Right: Form -->
				<div>
					<form class="flex flex-col gap-4" method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="contact_form">
						<input type="hidden" name="form_type" value="showroom">
						<input type="hidden" name="form_source" value="modal">
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

						<!-- Consent Checkboxes -->
						<div class="space-y-3">
							<label class="flex items-start gap-3 cursor-pointer group">
								<input type="checkbox" name="consent_privacy" value="1" checked required class="mt-0.5 w-4 h-4 accent-primary cursor-pointer">
								<span class="text-white/60 text-xs text-left group-hover:text-white/80 transition-colors">
									Согласен с <a href="<?= esc_url(home_url($privacyPolicyUrl)); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" onclick="event.stopPropagation();">обработкой персональных данных</a>
								</span>
							</label>
							<label class="flex items-start gap-3 cursor-pointer group">
								<input type="checkbox" name="consent_newsletter" value="1" class="mt-0.5 w-4 h-4 accent-primary cursor-pointer">
								<span class="text-white/60 text-xs text-left group-hover:text-white/80 transition-colors">
									Согласен на <a href="<?= esc_url(home_url($newsletterPolicyUrl)); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" onclick="event.stopPropagation();">получение рассылок</a>
								</span>
							</label>
						</div>

						<button
							type="submit"
							class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors"
							aria-label="Отправить заявку"
						>
							Отправить заявку
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Получить консультацию -->
<div id="modal-consultation" class="mosaic-modal fixed inset-0 z-[100] hidden" aria-hidden="true" role="dialog" aria-labelledby="modal-consultation-title">
	<div class="mosaic-modal-overlay absolute inset-0 bg-black/80" data-modal-close></div>
	<div class="mosaic-modal-container relative z-10 flex items-center justify-center min-h-screen p-4">
		<div class="mosaic-modal-content bg-gray relative w-full max-w-[900px] p-8 min-[1280px]:p-12">
			<!-- Close Button -->
			<button
				type="button"
				class="absolute top-4 right-4 w-8 h-8 text-white/60 hover:text-white transition-colors"
				data-modal-close
				aria-label="Закрыть"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>

			<div class="grid grid-cols-1 min-[768px]:grid-cols-2 gap-8 min-[1280px]:gap-12">
				<!-- Left: Info -->
				<div class="space-y-6">
					<div>
						<h2 id="modal-consultation-title" class="text-white font-century font-normal text-[28px] min-[1280px]:text-[40px] leading-[110%] tracking-[-0.01em] mb-0">
							Получить<br>консультацию
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<div class="space-y-4 text-white font-century font-normal text-[16px] min-[1280px]:text-[18px] leading-[145%]">
						<div><?= esc_html($address); ?></div>
						<div><?= esc_html($workHours); ?></div>
						<div class="flex flex-wrap gap-x-3 gap-y-2">
							<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors">
								<?= esc_html((string) $phoneContact['display']); ?>
							</a>
							<a href="<?= esc_url($phone2Contact['href']); ?>" class="hover:text-primary transition-colors">
								<?= esc_html((string) $phone2Contact['display']); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- Right: Form -->
				<div>
					<form id="modal-consultation-form" class="flex flex-col gap-4">
						<input type="hidden" name="action" value="contact_form_ajax">
						<input type="hidden" name="form_type" value="consultation">
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

						<!-- Consent Checkboxes -->
						<div class="space-y-3">
							<label class="flex items-start gap-3 cursor-pointer group">
								<input type="checkbox" name="consent_privacy" value="1" checked required class="mt-0.5 w-4 h-4 accent-primary cursor-pointer">
								<span class="text-white/60 text-xs text-left group-hover:text-white/80 transition-colors">
									Согласен с <a href="<?= esc_url(home_url($privacyPolicyUrl)); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" onclick="event.stopPropagation();">обработкой персональных данных</a>
								</span>
							</label>
							<label class="flex items-start gap-3 cursor-pointer group">
								<input type="checkbox" name="consent_newsletter" value="1" class="mt-0.5 w-4 h-4 accent-primary cursor-pointer">
								<span class="text-white/60 text-xs text-left group-hover:text-white/80 transition-colors">
									Согласен на <a href="<?= esc_url(home_url($newsletterPolicyUrl)); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline" onclick="event.stopPropagation();">получение рассылок</a>
								</span>
							</label>
						</div>

						<button
							type="submit"
							class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors disabled:opacity-50"
							aria-label="Отправить заявку"
						>
							<span class="btn-text">Отправить заявку</span>
							<span class="btn-loading hidden">Отправка...</span>
						</button>

						<!-- Сообщение результата -->
						<div id="modal-consultation-result" class="hidden p-4 text-center"></div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.mosaic-modal {
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.3s ease, visibility 0.3s ease;
}
.mosaic-modal.is-open {
	opacity: 1;
	visibility: visible;
}
.mosaic-modal.is-open .mosaic-modal-content {
	transform: scale(1);
}
.mosaic-modal-content {
	transform: scale(0.95);
	transition: transform 0.3s ease;
}
</style>

<script>
(function() {
	function openModal(modalId) {
		var modal = document.getElementById(modalId);
		if (!modal) return;

		modal.classList.remove('hidden');
		// Force reflow
		modal.offsetHeight;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';

		// Focus first input
		var firstInput = modal.querySelector('input[type="text"]');
		if (firstInput) {
			setTimeout(function() { firstInput.focus(); }, 100);
		}
	}

	function closeModal(modal) {
		if (!modal) return;

		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');

		setTimeout(function() {
			modal.classList.add('hidden');
			document.body.style.overflow = '';
		}, 300);
	}

	// Global function for opening modals
	window.mosaicOpenModal = openModal;

	// Click handlers for close buttons and overlay
	document.addEventListener('click', function(e) {
		var closeBtn = e.target.closest('[data-modal-close]');
		if (closeBtn) {
			var modal = closeBtn.closest('.mosaic-modal');
			closeModal(modal);
		}

		// Open modal by data attribute
		var openBtn = e.target.closest('[data-modal-open]');
		if (openBtn) {
			e.preventDefault();
			var modalId = openBtn.getAttribute('data-modal-open');
			openModal(modalId);
		}
	});

	// Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			var openModal = document.querySelector('.mosaic-modal.is-open');
			if (openModal) {
				closeModal(openModal);
			}
		}
	});

	// AJAX форма консультации
	var consultForm = document.getElementById('modal-consultation-form');
	if (consultForm) {
		consultForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var form = this;
			var btn = form.querySelector('button[type="submit"]');
			var btnText = btn.querySelector('.btn-text');
			var btnLoading = btn.querySelector('.btn-loading');
			var result = document.getElementById('modal-consultation-result');

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
					var fields = form.querySelectorAll('input, button, p');
					fields.forEach(function(el) { el.style.display = 'none'; });
					result.className = 'p-6 text-center bg-primary/20 text-white text-lg';
					result.innerHTML = '<div class="mb-2 text-2xl">✓</div>' + data.data.message;
					form.reset();
					// Закрыть модалку через 3 сек
					setTimeout(function() {
						var modal = form.closest('.mosaic-modal');
						closeModal(modal);
						// Восстановить форму после закрытия
						setTimeout(function() {
							fields.forEach(function(el) { el.style.display = ''; });
							result.classList.add('hidden');
						}, 300);
					}, 3000);
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
	}
})();
</script>

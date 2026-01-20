<?php
/**
 * Template Part: Contact Form Section
 * Секция "Давайте обсудим ваш проект" с формой и контактами
 */

declare(strict_types=1);

// Получаем контактную информацию
$phoneContact = function_exists('mosaic_get_phone_contact') ? mosaic_get_phone_contact() : ['display' => '+7 (928) 206-07-75', 'href' => 'tel:+79282060775'];
$phone2Contact = function_exists('mosaic_get_phone2_contact') ? mosaic_get_phone2_contact() : ['display' => '+7 (928) 400-32-55', 'href' => 'tel:+79284003255'];
$siteSettings = function_exists('mosaic_get_site_settings') ? mosaic_get_site_settings() : [];
$email = is_array($siteSettings) ? (string) ($siteSettings['email'] ?? 'si.mosaic@yandex.ru') : 'si.mosaic@yandex.ru';
$email = $email !== '' ? $email : 'si.mosaic@yandex.ru';
$emailHref = 'mailto:' . $email;
$address = is_array($siteSettings) ? trim((string) ($siteSettings['address'] ?? '')) : '';
$address = $address !== '' ? $address : 'Краснодар, Селезнёва 204';
$workHours = is_array($siteSettings) ? trim((string) ($siteSettings['work_hours'] ?? '')) : '';
$workHours = $workHours !== '' ? $workHours : 'Пн - Пт: 09:00 - 18:00';

// URL для чекбоксов согласий
$privacyPolicyUrl = is_array($siteSettings) ? trim((string) ($siteSettings['privacy_policy_url'] ?? '/privacy-policy/')) : '/privacy-policy/';
$newsletterPolicyUrl = is_array($siteSettings) ? trim((string) ($siteSettings['newsletter_policy_url'] ?? '/newsletter-policy/')) : '/newsletter-policy/';
?>

<!-- Contact Section -->
<section class="bg-gray py-[80px] min-[1280px]:py-[100px]" data-contact id="contact-form">
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
		<!-- Mobile: <=1279 -->
		<div class="max-[1279px]:block min-[1280px]:hidden">
			<div class="grid grid-cols-1 gap-8">
				<!-- Left Column: Contact Info -->
				<div class="space-y-8">
					<!-- Title -->
					<div>
						<h2 class="text-white font-century font-normal text-[28px] leading-[110%] tracking-[-0.01em] mb-0">
							Давайте обсудим ваш проект
						</h2>
						<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
					</div>

					<!-- Social Media Icons -->
					<div class="flex gap-4">
						<?php get_template_part('template-parts/social-icons'); ?>
					</div>

					<!-- Contact Details -->
					<div class="space-y-5 text-white font-century font-normal text-[18px] leading-[145%] tracking-[0]">
						<div class="flex flex-wrap gap-x-4 gap-y-2">
							<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>">
								<?= esc_html((string) $phoneContact['display']); ?>
							</a>
							<a href="<?= esc_url($phone2Contact['href']); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phone2Contact['display']); ?>">
								<?= esc_html((string) $phone2Contact['display']); ?>
							</a>
						</div>
						<div>
							<a href="<?= esc_url($emailHref); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Написать на почту ' . $email); ?>">
								<?= esc_html($email); ?>
							</a>
						</div>
						<div><?= esc_html($address); ?></div>
						<div><?= esc_html($workHours); ?></div>
					</div>
				</div>

				<!-- Right Column: Contact Form -->
				<div>
					<form class="flex flex-col gap-4" method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="contact_form">
						<input type="hidden" name="form_type" value="project">
						<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

						<!-- Name Field -->
						<div>
							<input
								type="text"
								name="name"
								placeholder="Имя"
								required
								class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								tabindex="0"
								aria-label="Ваше имя"
							>
						</div>

						<!-- Email Field -->
						<div>
							<input
								type="email"
								name="email"
								placeholder="Почта"
								required
								class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								tabindex="0"
								aria-label="Ваш email"
							>
						</div>

						<!-- Phone Field -->
						<div>
							<input
								type="tel"
								name="phone"
								placeholder="Телефон"
								required
								class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
								tabindex="0"
								aria-label="Ваш телефон"
							>
						</div>

						<!-- Submit Button -->
						<button
							type="submit"
							class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors"
							tabindex="0"
							aria-label="Отправить заявку"
						>
							Отправить заявку
						</button>

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
					</form>
				</div>
			</div>
		</div>

		<!-- Tablet: 1280..1919 -->
		<div class="hidden min-[1280px]:max-[1919px]:block">
			<div class="flex items-start justify-between">
				<!-- Left -->
				<div class="w-[596px] h-[488px] flex flex-col">
					<h2 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
						Давайте обсудим ваш проект
					</h2>
					<div class="w-[70px] h-[6px] bg-primary mt-6"></div>

					<div class="mt-8 flex flex-col gap-[30px]">
						<div class="flex gap-4">
							<?php get_template_part('template-parts/social-icons', null, ['icon_class' => 'w-[33px] h-[33px]']); ?>
						</div>

						<div class="space-y-[30px] text-white font-century font-normal text-[20px] leading-[145%] tracking-[0]">
							<div class="flex flex-wrap gap-x-4 gap-y-2">
								<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>">
									<?= esc_html((string) $phoneContact['display']); ?>
								</a>
								<a href="<?= esc_url($phone2Contact['href']); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phone2Contact['display']); ?>">
									<?= esc_html((string) $phone2Contact['display']); ?>
								</a>
							</div>
							<div>
								<a href="<?= esc_url($emailHref); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Написать на почту ' . $email); ?>">
									<?= esc_html($email); ?>
								</a>
							</div>
							<div><?= esc_html($address); ?></div>
							<div><?= esc_html($workHours); ?></div>
						</div>
					</div>
				</div>

				<!-- Right -->
				<div class="w-[593px] h-[336px]">
					<form class="h-full" method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="contact_form">
						<input type="hidden" name="form_type" value="project">
						<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

						<div class="flex flex-col gap-5">
							<div>
								<input
									type="text"
									name="name"
									placeholder="Имя"
									required
									class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									tabindex="0"
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
									tabindex="0"
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
									tabindex="0"
									aria-label="Ваш телефон"
								>
							</div>

							<button
								type="submit"
								class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
								tabindex="0"
								aria-label="Отправить заявку"
							>
								Отправить заявку
							</button>

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
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- >=1920 desktop layout per reference -->
		<div class="hidden min-[1920px]:block">
			<div class="flex items-start gap-[121px]">
				<!-- Left: text block -->
				<div class="w-[900px] h-[404px] flex flex-col">
					<h2 class="text-white font-century font-normal text-[56px] leading-[100%] tracking-[-0.01em] mb-0">
						Давайте обсудим ваш проект
					</h2>
					<div class="w-[70px] h-[6px] bg-primary mt-6"></div>

					<div class="mt-8 flex flex-col gap-[30px]">
						<div class="flex gap-4">
							<?php get_template_part('template-parts/social-icons', null, ['icon_class' => 'w-[33px] h-[33px]']); ?>
						</div>

						<div class="space-y-[30px] text-white font-century font-normal text-[20px] leading-[145%] tracking-[0]">
							<div class="flex flex-wrap gap-x-4 gap-y-2">
								<a href="<?= esc_url($phoneContact['href']); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phoneContact['display']); ?>">
									<?= esc_html((string) $phoneContact['display']); ?>
								</a>
								<a href="<?= esc_url($phone2Contact['href']); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Позвонить ' . (string) $phone2Contact['display']); ?>">
									<?= esc_html((string) $phone2Contact['display']); ?>
								</a>
							</div>
							<div>
								<a href="<?= esc_url($emailHref); ?>" class="hover:text-primary transition-colors" tabindex="0" aria-label="<?= esc_attr('Написать на почту ' . $email); ?>">
									<?= esc_html($email); ?>
								</a>
							</div>
							<div><?= esc_html($address); ?></div>
							<div><?= esc_html($workHours); ?></div>
						</div>
					</div>
				</div>

				<!-- Right: form block -->
				<div class="w-[658px] h-[336px]">
					<form class="h-full flex flex-col" method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
						<input type="hidden" name="action" value="contact_form">
						<input type="hidden" name="form_type" value="project">
						<?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>

						<div class="flex flex-col gap-5">
							<div>
								<input
									type="text"
									name="name"
									placeholder="Имя"
									required
									class="w-full h-[56px] bg-white/5 border-0 border-b border-primary px-6 text-white placeholder:text-white/40 focus:outline-none focus:border-primary"
									tabindex="0"
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
									tabindex="0"
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
									tabindex="0"
									aria-label="Ваш телефон"
								>
							</div>

								<button
								type="submit"
								class="w-full bg-primary hover:bg-opacity-90 text-white h-[56px] px-6 text-base font-normal transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-gray"
								tabindex="0"
								aria-label="Отправить заявку"
							>
								Отправить заявку
							</button>

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
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>


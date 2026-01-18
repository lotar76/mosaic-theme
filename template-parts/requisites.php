<?php
/**
 * Template Part: Requisites Section
 * Блок "Реквизиты" для страницы О нас
 */

declare(strict_types=1);

$data = mosaic_get_about_page();
$requisites = $data['requisites'];

// Проверяем, есть ли хотя бы одно заполненное поле
$hasData = $requisites['legal_address'] !== ''
	|| $requisites['actual_address'] !== ''
	|| $requisites['inn'] !== ''
	|| $requisites['okved'] !== '';

if (!$hasData) {
	return;
}
?>

<!-- Requisites Section -->
<section class="bg-black pt-[40px] pb-[60px] min-[1280px]:pt-[40px] min-[1280px]:pb-[80px]">
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
		<!-- Title -->
		<div class="mb-8 min-[1280px]:mb-12">
			<h2 class="text-white text-[24px] min-[1280px]:text-[40px] min-[1920px]:text-[48px] font-normal mb-0">
				Реквизиты
			</h2>
			<div class="w-[70px] h-[6px] bg-primary mt-6"></div>
		</div>

		<!-- Requisites Grid -->
		<div class="grid grid-cols-2 min-[1280px]:grid-cols-4 gap-6 min-[1280px]:gap-8">
			<?php if ($requisites['legal_address'] !== '') : ?>
				<div>
					<p class="text-white/40 text-sm min-[1280px]:text-base mb-2">Юридический адрес</p>
					<p class="text-white text-base min-[1280px]:text-lg"><?= esc_html($requisites['legal_address']); ?></p>
				</div>
			<?php endif; ?>

			<?php if ($requisites['actual_address'] !== '') : ?>
				<div>
					<p class="text-white/40 text-sm min-[1280px]:text-base mb-2">Фактический адрес</p>
					<p class="text-white text-base min-[1280px]:text-lg"><?= esc_html($requisites['actual_address']); ?></p>
				</div>
			<?php endif; ?>

			<?php if ($requisites['inn'] !== '') : ?>
				<div>
					<p class="text-white/40 text-sm min-[1280px]:text-base mb-2">ИНН</p>
					<p class="text-white text-base min-[1280px]:text-lg"><?= esc_html($requisites['inn']); ?></p>
				</div>
			<?php endif; ?>

			<?php if ($requisites['okved'] !== '') : ?>
				<div>
					<p class="text-white/40 text-sm min-[1280px]:text-base mb-2">Основной код ОКВЭД</p>
					<p class="text-white text-base min-[1280px]:text-lg"><?= esc_html($requisites['okved']); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

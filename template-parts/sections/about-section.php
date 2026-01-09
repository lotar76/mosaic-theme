<?php
/**
 * Template Part: About Section
 * Объединённый блок "О компании" + "Основатели"
 * (между ними нет отступа — это один логический блок)
 */

declare(strict_types=1);
?>

<!-- About Section: wrapper для объединения двух блоков без gap -->
<div>
	<!-- About Company -->
	<?php get_template_part('template-parts/sections/about-company'); ?>

	<!-- Founders -->
	<?php get_template_part('template-parts/sections/founders'); ?>
</div>


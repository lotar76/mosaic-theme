<?php
/**
 * Template Part: Breadcrumbs
 * Универсальный компонент хлебных крошек для навигации
 */

declare(strict_types=1);

// Получаем текущую страницу для генерации цепочки
$breadcrumbs = [];

// Главная всегда первая
$breadcrumbs[] = [
	'url' => home_url('/'),
	'title' => 'Главная',
	'is_current' => is_front_page(),
];

// Определяем текущую страницу
if (get_query_var('mosaic_catalog_root')) {
	// Корневая страница каталога /catalog/
	$breadcrumbs[] = [
		'url' => '',
		'title' => 'Каталог',
		'is_current' => true,
	];
} elseif (get_query_var('mosaic_portfolio_root')) {
	// Корневая страница портфолио /portfolio/
	$breadcrumbs[] = [
		'url' => '',
		'title' => 'Портфолио',
		'is_current' => true,
	];
} elseif (get_query_var('mosaic_news_root')) {
	// Корневая страница новостей /news/
	$breadcrumbs[] = [
		'url' => '',
		'title' => 'Новости',
		'is_current' => true,
	];
} elseif (is_page()) {
	$page = get_queried_object();
	if ($page instanceof WP_Post) {
		// Если есть родительские страницы, добавляем их
		if ($page->post_parent) {
			$ancestors = array_reverse(get_post_ancestors($page->ID));
			foreach ($ancestors as $ancestor_id) {
				$ancestor = get_post($ancestor_id);
				if ($ancestor instanceof WP_Post) {
					$breadcrumbs[] = [
						'url' => get_permalink($ancestor),
						'title' => get_the_title($ancestor),
						'is_current' => false,
					];
				}
			}
		}
		
		// Текущая страница
		$breadcrumbs[] = [
			'url' => '',
			'title' => get_the_title($page),
			'is_current' => true,
		];
	}
} elseif (is_single()) {
	$post = get_queried_object();
	if ($post instanceof WP_Post) {
		// Для проектов портфолио
		if ($post->post_type === 'portfolio') {
			$breadcrumbs[] = [
				'url' => home_url('/portfolio/'),
				'title' => 'Портфолио',
				'is_current' => false,
			];
		}
		// Для новостей
		elseif ($post->post_type === 'news') {
			$breadcrumbs[] = [
				'url' => home_url('/news/'),
				'title' => 'Новости',
				'is_current' => false,
			];
		}
		// Для товаров (product) добавляем раздел каталога в breadcrumbs
		elseif ($post->post_type === 'product') {
			$terms = get_the_terms($post->ID, 'product_section');
			if (!empty($terms) && !is_wp_error($terms)) {
				// Берём первый терм
				$term = reset($terms);
				if ($term instanceof WP_Term) {
					// Добавляем родительские разделы
					$current = $term;
					$termPath = [];
					while ($current && !is_wp_error($current)) {
						array_unshift($termPath, $current);
						if ($current->parent > 0) {
							$current = get_term((int) $current->parent, 'product_section');
						} else {
							break;
						}
					}
					
					// Добавляем все разделы в breadcrumbs
					foreach ($termPath as $t) {
						$termLink = get_term_link($t);
						if (!is_wp_error($termLink)) {
							$breadcrumbs[] = [
								'url' => (string) $termLink,
								'title' => $t->name,
								'is_current' => false,
							];
						}
					}
				}
			} else {
				// Если нет раздела, добавляем ссылку на каталог
				$breadcrumbs[] = [
					'url' => home_url('/catalog/'),
					'title' => 'Каталог',
					'is_current' => false,
				];
			}
		} else {
			// Для других типов записей добавляем архив
			$post_type = get_post_type_object($post->post_type);
			if ($post_type && $post_type->has_archive) {
				$breadcrumbs[] = [
					'url' => get_post_type_archive_link($post->post_type),
					'title' => $post_type->labels->name ?? 'Архив',
					'is_current' => false,
				];
			}
		}
		
		// Текущая запись
		$breadcrumbs[] = [
			'url' => '',
			'title' => get_the_title($post),
			'is_current' => true,
		];
	}
} elseif (is_post_type_archive()) {
	$post_type = get_queried_object();
	if ($post_type instanceof WP_Post_Type) {
		$breadcrumbs[] = [
			'url' => '',
			'title' => $post_type->labels->name ?? 'Архив',
			'is_current' => true,
		];
	}
} elseif (is_tax() || is_category() || is_tag()) {
	$term = get_queried_object();
	if ($term instanceof WP_Term) {
		// Для portfolio_category добавляем "Портфолио"
		if ($term->taxonomy === 'portfolio_category') {
			$breadcrumbs[] = [
				'url' => home_url('/portfolio/'),
				'title' => 'Портфолио',
				'is_current' => false,
			];
		}
		// Для product_section добавляем "Каталог" и родительские термы
		elseif ($term->taxonomy === 'product_section') {
			$breadcrumbs[] = [
				'url' => home_url('/catalog/'),
				'title' => 'Каталог',
				'is_current' => false,
			];

			// Добавляем родительские термы
			if ($term->parent > 0) {
				$ancestors = get_ancestors($term->term_id, 'product_section', 'taxonomy');
				$ancestors = array_reverse($ancestors);
				foreach ($ancestors as $ancestorId) {
					$ancestor = get_term($ancestorId, 'product_section');
					if ($ancestor instanceof WP_Term) {
						$ancestorLink = get_term_link($ancestor);
						if (!is_wp_error($ancestorLink)) {
							$breadcrumbs[] = [
								'url' => (string) $ancestorLink,
								'title' => $ancestor->name,
								'is_current' => false,
							];
						}
					}
				}
			}
		} else {
			// Для других таксономий — архив post type если есть
			$taxonomy = get_taxonomy($term->taxonomy);
			if ($taxonomy && !empty($taxonomy->object_type)) {
				$post_type = get_post_type_object($taxonomy->object_type[0]);
				if ($post_type && $post_type->has_archive) {
					$breadcrumbs[] = [
						'url' => get_post_type_archive_link($taxonomy->object_type[0]),
						'title' => $post_type->labels->name ?? 'Архив',
						'is_current' => false,
					];
				}
			}
		}

		// Текущая категория/термин
		$breadcrumbs[] = [
			'url' => '',
			'title' => $term->name,
			'is_current' => true,
		];
	}
} elseif (is_404()) {
	$breadcrumbs[] = [
		'url' => '',
		'title' => 'Страница не найдена',
		'is_current' => true,
	];
} elseif (is_search()) {
	$breadcrumbs[] = [
		'url' => '',
		'title' => 'Поиск: ' . get_search_query(),
		'is_current' => true,
	];
}

// Если хлебные крошки состоят только из главной - не выводим
if (count($breadcrumbs) <= 1) {
	return;
}
?>

<nav aria-label="Breadcrumbs" class="bg-black">
	<div class="max-w-[1920px] mx-auto px-4 md:px-8 lg:px-16 min-[1920px]:px-[100px]">
		<ol class="flex flex-wrap items-center gap-2 text-[15px] leading-[16px]">
			<?php foreach ($breadcrumbs as $index => $crumb) : ?>
				<?php $isLast = ($index === count($breadcrumbs) - 1); ?>
				<li class="flex items-center gap-2">
					<?php if (!$isLast && $crumb['url'] !== '') : ?>
						<a 
							href="<?= esc_url($crumb['url']); ?>" 
							class="text-[#847575] hover:text-primary transition-colors"
							tabindex="0"
							aria-label="<?= esc_attr('Перейти: ' . $crumb['title']); ?>"
						>
							<?= esc_html($crumb['title']); ?>
						</a>
						<span class="text-[#847575]" aria-hidden="true">●</span>
					<?php else : ?>
						<span class="text-[#847575]" aria-current="page">
							<?= esc_html($crumb['title']); ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</nav>


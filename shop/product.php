<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

/* Slug из URL */
$slug = $_GET['slug'] ?? '';

/* Ищем товар в БД */
$stmt = $pdo->prepare('SELECT * FROM products WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$product = $stmt->fetch();

/* 404 если не найден */
if (!$product) {
	http_response_code(404);
	include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
	exit;
}

/* Проверяем куплен ли товар */
$hasAccess = auth_has_access($pdo, $product['id']);

/* Похожие товары — все кроме текущего, максимум 3 */
$stmt = $pdo->prepare('SELECT * FROM products WHERE slug != ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3');
$stmt->execute([$slug]);
$related = $stmt->fetchAll();

/* SEO-заголовок */
$pageTitle = $product['seo_title'] ?: $product['title'];

/* Форматируем цены */
$price    = number_format($product['price'], 0, '', ' ');
$oldPrice = $product['old_price'] ? number_format($product['old_price'], 0, '', ' ') : null;
$badge    = ($product['old_price'] && $product['old_price'] > 0)
	? round((1 - $product['price'] / $product['old_price']) * 100)
	: null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($product['meta_desc'] ?? '') ?>">
	<meta name="keywords"    content="<?= htmlspecialchars($product['meta_keywords'] ?? '') ?>">
	<meta name="author"      content="ПрайсСмета">
	<!-- OG -->
	<meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($product['meta_desc'] ?? '') ?>">
	<meta property="og:type"        content="product">
	<meta property="og:url"         content="https://прайслистмастера.рф/shop/<?= htmlspecialchars($slug) ?>">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/product_page.css">
</head>
<body>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'; ?>

	<!-- ===== MAIN ===== -->
	<main>
		<div class="product-page">
			<div class="container">

				<!-- хлебные крошки -->
				<nav class="product-page__breadcrumbs" aria-label="Навигация">
					<a href="/">Главная</a>
					<span class="product-page__breadcrumbs-sep">/</span>
					<a href="/shop">Сметы</a>
					<span class="product-page__breadcrumbs-sep">/</span>
					<span><?= htmlspecialchars($product['title']) ?></span>
				</nav>

				<!-- главный блок -->
				<div class="product-page__main">

					<!-- картинка -->
					<div class="product-page__img-wrap">
						<img
							src="<?= htmlspecialchars($product['image'] ?: 'https://placehold.co/800x600/e8eaf0/6b7280?text=Превью+товара') ?>"
							alt="<?= htmlspecialchars($product['title']) ?>"
							class="product-page__img"
						>
					</div>

					<!-- инфо -->
					<div class="product-page__info">

						<h1 class="product-page__title"><?= htmlspecialchars($product['title']) ?></h1>

						<!-- цена -->
						<div class="product-page__price-row">
							<span class="product-page__price"><?= $price ?> ₽</span>
							<?php if ($oldPrice): ?>
								<span class="product-page__old-price"><?= $oldPrice ?> ₽</span>
								<span class="product-page__badge">−<?= $badge ?>%</span>
							<?php endif; ?>
						</div>

						<!-- кнопка: скачать если куплено, иначе купить -->
						<?php if ($hasAccess): ?>
							<a href="/download/<?= htmlspecialchars($slug) ?>" class="product-page__btn">Скачать</a>
						<?php else: ?>
							<a href="#" class="product-page__btn js-buy" data-id="<?= $product['id'] ?>">Получить доступ</a>
						<?php endif; ?>

						<div class="product-page__delivery">
							<span class="product-page__delivery-dot"></span>
							Мгновенный доступ после оплаты — файл откроется сразу
						</div>

					</div>
				</div>

				<!-- описание — HTML из БД, редактируется в админке -->
				<?php if ($product['description']): ?>
				<section class="product-desc">
					<?= $product['description'] ?>
				</section>
				<?php endif; ?>

				<!-- похожие товары -->
				<?php if ($related): ?>
				<section class="product-related">
					<h2 class="product-related__title">Похожие товары</h2>

					<div class="product-related__grid">
						<?php foreach ($related as $rel): ?>
						<article class="related-card">
							<a href="/shop/<?= htmlspecialchars($rel['slug']) ?>" class="related-card__img-wrap">
								<img
									src="<?= htmlspecialchars($rel['image'] ?: 'https://placehold.co/400x400/e8eaf0/6b7280?text=Товар') ?>"
									alt="<?= htmlspecialchars($rel['title']) ?>"
									class="related-card__img"
									loading="lazy"
								>
							</a>
							<div class="related-card__body">
								<h3 class="related-card__name"><?= htmlspecialchars($rel['title']) ?></h3>
								<div class="related-card__price-row">
									<span class="related-card__price"><?= number_format($rel['price'], 0, '', ' ') ?> ₽</span>
									<?php if ($rel['old_price']): ?>
										<span class="related-card__old-price"><?= number_format($rel['old_price'], 0, '', ' ') ?> ₽</span>
									<?php endif; ?>
								</div>
								<a href="/shop/<?= htmlspecialchars($rel['slug']) ?>" class="related-card__btn">Получить доступ</a>
							</div>
						</article>
						<?php endforeach; ?>
					</div>
				</section>
				<?php endif; ?>

			</div>
		</div>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>

	<script>
		/* FAQ аккордеон — работает с HTML который выводится из description */
		document.querySelectorAll('.faq-item__question').forEach(btn => {
			btn.addEventListener('click', () => {
				const item = btn.closest('.faq-item');
				const isOpen = item.classList.contains('is-open');
				document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('is-open'));
				if (!isOpen) item.classList.add('is-open');
			});
		});

		/* Кнопка покупки */
		document.querySelectorAll('.js-buy').forEach(btn => {
			btn.addEventListener('click', e => {
				e.preventDefault();
				<?php if (!auth_check()): ?>
					document.dispatchEvent(new CustomEvent('open-login'));
				<?php else: ?>
					document.dispatchEvent(new CustomEvent('open-buy', {
						detail: { productId: btn.dataset.id }
					}));
				<?php endif; ?>
			});
		});
	</script>
</body>
</html>
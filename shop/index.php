<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Сметы для мастеров — каталог цифровых товаров | ПрайсСмета</title>
	<meta name="description" content="Готовые автосметы и прайс-листы в Excel для электриков, сантехников и плиточников. Скачайте и используйте сразу — без настройки.">
	<meta name="keywords" content="смета электрика купить, прайс-лист сантехника, смета плиточника Excel, цифровая смета мастера 2026">
	<meta name="author" content="ПрайсСмета">
	<!-- OG -->
	<meta property="og:title" content="Сметы для мастеров — каталог цифровых товаров | ПрайсСмета">
	<meta property="og:description" content="Готовые автосметы и прайс-листы в Excel для электриков, сантехников и плиточников.">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://прайслистмастера.рф/shop">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

	/* Все опубликованные товары, сначала новые */
	$products = $pdo->query('SELECT * FROM products WHERE is_published = 1 ORDER BY created_at DESC')->fetchAll();

	include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php';
	?>

	<!-- ===== MAIN ===== -->
	<main>
		<section class="shop">
			<div class="container">

				<!-- хлебные крошки -->
				<nav class="shop__breadcrumbs" aria-label="Навигация">
					<a href="/">Главная</a>
					<span class="shop__breadcrumbs-sep">/</span>
					<span>Сметы</span>
				</nav>

				<h1 class="shop__title">Сметы</h1>

				<!-- сетка товаров -->
				<div class="shop__grid">
					<?php foreach ($products as $product): ?>
					<article class="shop-card">
						<a href="/shop/<?= htmlspecialchars($product['slug']) ?>" class="shop-card__img-wrap">
							<img
								src="<?= htmlspecialchars($product['image'] ?: 'https://placehold.co/400x400/e8eaf0/6b7280?text=Товар') ?>"
								alt="<?= htmlspecialchars($product['title']) ?>"
								class="shop-card__img"
								loading="lazy"
							>
						</a>
						<div class="shop-card__body">
							<h2 class="shop-card__name"><?= htmlspecialchars($product['title']) ?></h2>
							<div class="shop-card__price-row">
								<span class="shop-card__price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
								<?php if ($product['old_price']): ?>
									<span class="shop-card__old-price"><?= number_format($product['old_price'], 0, '', ' ') ?> ₽</span>
								<?php endif; ?>
							</div>
							<a href="/shop/<?= htmlspecialchars($product['slug']) ?>" class="shop-card__btn">Получить доступ</a>
						</div>
					</article>
					<?php endforeach; ?>

					<?php if (empty($products)): ?>
					<p style="color: var(--color-muted); grid-column: 1/-1;">Товары скоро появятся.</p>
					<?php endif; ?>
				</div>

			</div>
		</section>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
</body>
</html>
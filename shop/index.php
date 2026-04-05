<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Каталог смет и прайс-листов в Excel для мастеров | ПрайсСмета</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="description" content="Выберите смету или прайс-лист в Excel под своё направление: электромонтажные, сантехнические, отопительные и плиточные работы. Отдельные файлы и комплектные решения в одном каталоге.">
	<meta name="keywords" content="каталог смет excel, смета электромонтажных работ, смета сантехнических работ, смета отопительных работ, смета плиточных работ, комплект смет для мастера">
	<meta name="author" content="ПрайсСмета">
	<!-- OG -->
	<meta property="og:title" content="Каталог смет и прайс-листов в Excel для мастеров | ПрайсСмета">
	<meta property="og:description" content="Каталог Excel-смет по направлениям: электрика, сантехника, отопление, плитка и комплекты для нескольких видов работ.">
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
							<h2 class="shop-card__name"><?= htmlspecialchars($product['title']) ?></ы>
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
		<!-- seo-текст -->
		<section class="seotext">
			<div class="container">
				<h2 class="seotext__title">Автоматические сметы и прайс-листы в Excel для мастеров</h2>
				<p class="seotext__p">В каталоге ПрайсСмета собраны автоматические сметы и прайс-листы в Excel для мастеров, бригад и небольших строительных компаний. Эти файлы помогают быстро считать стоимость работ, подготавливать коммерческие предложения и вести расчёты по объектам без ручного пересчёта каждой позиции. Если нужна автоматическая смета в Excel для повседневной работы, здесь можно выбрать готовое решение под своё направление.</p>

				<div class="seotext__more" id="seoMore">
					<p class="seotext__p">В каталоге доступны файлы для электромонтажных, сантехнических, отопительных и плиточных работ. Отдельные сметы удобно использовать, когда мастер работает в одном направлении и хочет держать под рукой готовый прайс и понятную структуру расчёта. Если нужны сразу несколько разделов, можно выбрать комплект для электрики и сантехники, чтобы вести расчёты в одном рабочем наборе.</p>
					<p class="seotext__p">Каждая смета редактируется под свои расценки, объёмы и формат работы. Файлы подходят для расчёта квартир, частных домов, таунхаусов и небольших коммерческих помещений. Таблицы можно открыть на компьютере, сохранить в PDF для клиента или использовать через онлайн-таблицы, если нужна онлайн смета без сложной настройки и лишнего функционала.</p>
					<p class="seotext__p">Такой формат особенно удобен для мастеров, которым важно быстро считать стоимость работ, не тратить время на создание сметы с нуля и держать все основные позиции в одном месте. Каталог ПрайсСмета помогает выбрать подходящий файл под конкретную задачу: электрика, сантехника и отопление, плитка или комплект для нескольких видов работ сразу.</p>
				</div>

				<button class="seotext__toggle" id="seoToggle">
					Читать далее <span class="seotext__toggle-icon">↓</span>
				</button>
			</div>
		</section>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>

	<script>
		/* seo-текст — раскрыть/скрыть */
		const seoToggle = document.getElementById('seoToggle');
		const seoMore   = document.getElementById('seoMore');

		seoToggle.addEventListener('click', () => {
			const isOpen = seoMore.classList.toggle('is-open');
			seoToggle.innerHTML = isOpen
				? 'Свернуть <span class="seotext__toggle-icon">↑</span>'
				: 'Читать далее <span class="seotext__toggle-icon">↓</span>';
		});
	</script>
</body>
</html>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Оплата и доставка | ПрайсСмета</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="description" content="Условия оплаты и получения товара на сайте ПрайсСмета. Безналичная оплата, мгновенный доступ после оплаты, электронная доставка.">
	<meta name="keywords" content="оплата, доставка, получение товара, прайссмета">
	<meta name="author" content="ПрайсСмета">
	<meta name="robots" content="noindex, follow">
	<!-- OG -->
	<meta property="og:title" content="Оплата и доставка | ПрайсСмета">
	<meta property="og:description" content="Условия оплаты и получения товара на сайте ПрайсСмета.">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://прайслистмастера.рф/payment_delivery">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>
	<?php include ($_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'); ?>

	<!-- ===== MAIN ===== -->
	<main>
		<div class="oferta">
			<div class="container oferta__inner">

				<!-- хлебные крошки -->
				<nav class="shop__breadcrumbs" aria-label="Навигация">
					<a href="/">Главная</a>
					<span class="shop__breadcrumbs-sep">/</span>
					<span>Оплата и доставка</span>
				</nav>

				<h1 class="oferta__title">Оплата и доставка</h1>

				<!-- оплата -->
				<div class="oferta__section">
					<h2 class="oferta__section-title">Оплата</h2>
					<p class="oferta__text">Оплата заказов осуществляется в безналичной форме через интернет-эквайринг на сайте. Все доступные материалы размещены в <a href="/shop" style="color: var(--color-accent); border-bottom: 1px solid rgba(37,99,235,.3);">каталоге</a>.</p>
				</div>

				<!-- получение доступа -->
				<div class="oferta__section">
					<h2 class="oferta__section-title">Получение товара</h2>
					<p class="oferta__text">После подтверждения оплаты доступ к выбранному товару открывается автоматически. Обычно это происходит в течение 1 минуты.</p>
					<p class="oferta__text">Ссылка на оплаченный товар отправляется на электронную почту, указанную при оформлении заказа. Также доступ к купленному товару сохраняется в профиле пользователя на сайте и остаётся доступен там после оплаты.</p>
				</div>

				<!-- доставка -->
				<div class="oferta__section">
					<h2 class="oferta__section-title">Доставка</h2>
					<p class="oferta__text">Физическая доставка не осуществляется — товар предоставляется в электронном виде.</p>
				</div>

				<!-- помощь -->
				<div class="oferta__section">
					<h2 class="oferta__section-title">Если что-то пошло не так</h2>
					<p class="oferta__text">Если после оплаты доступ не появился или письмо не пришло, свяжитесь с нами по контактам, указанным на сайте.</p>
				</div>

			</div>
		</div>
	</main>

	<?php include ($_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'); ?>
</body>
</html>
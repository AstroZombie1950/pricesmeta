<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Спасибо за покупку — ПрайсСмета</title>
	<meta name="robots" content="noindex">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/thankyou_page.css">
</head>
<body>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'; ?>

	<main>
		<div class="container">
			<div class="thankyou">

				<div class="thankyou__icon">✓</div>

				<h1 class="thankyou__title">Спасибо за покупку!</h1>

				<p class="thankyou__text">
					Оплата прошла успешно. Файл уже доступен в вашем личном кабинете.
				</p>

				<div class="thankyou__actions">
					<a href="/account/" class="thankyou__btn thankyou__btn--primary">
						Перейти в кабинет →
					</a>
					<a href="/shop/" class="thankyou__btn thankyou__btn--outline">
						Вернуться в магазин
					</a>
				</div>

			</div>
		</div>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
</body>
</html>
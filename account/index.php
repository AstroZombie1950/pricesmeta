<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

/* Только для авторизованных */
auth_require();

/* Выход */
if (isset($_GET['logout'])) {
	auth_logout();
	header('Location: /');
	exit;
}

/* Купленные товары */
$stmt = $pdo->prepare('
	SELECT p.id, p.title, p.slug, p.image, p.price, pu.created_at AS bought_at
	FROM purchases pu
	JOIN products p ON p.id = pu.product_id
	WHERE pu.user_id = ?
	ORDER BY pu.created_at DESC
');
$stmt->execute([auth_user_id()]);
$purchases = $stmt->fetchAll();

/* Русские месяцы */
$months = [
	1=>'января',2=>'февраля',3=>'марта',4=>'апреля',
	5=>'мая',6=>'июня',7=>'июля',8=>'августа',
	9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Личный кабинет — ПрайсСмета</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="robots" content="noindex, nofollow">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/account_page.css">
</head>
<body>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'; ?>

	<main>
		<section class="account">
			<div class="container">

				<!-- Шапка -->
				<div class="account__head">
					<div>
						<h1 class="account__title">Личный кабинет</h1>
						<p class="account__email"><?= htmlspecialchars(auth_user_email()) ?></p>
					</div>
					<a href="/account/?logout=1" class="account__logout">Выйти</a>
				</div>

				<!-- Купленные товары -->
				<h2 class="account__section-title">Мои покупки</h2>

				<?php if ($purchases): ?>
				<div class="account__list">
					<?php foreach ($purchases as $p):
						$ts   = strtotime($p['bought_at']);
						$date = date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
					?>
					<div class="account__item">
						<img
							src="<?= htmlspecialchars($p['image'] ?: 'https://placehold.co/72x72/e8eaf0/6b7280?text=📦') ?>"
							alt="<?= htmlspecialchars($p['title']) ?>"
							class="account__item-img"
						>
						<div class="account__item-info">
							<span class="account__item-title"><?= htmlspecialchars($p['title']) ?></span>
							<span class="account__item-date">Куплено <?= $date ?></span>
						</div>
						<a href="/download/<?= htmlspecialchars($p['slug']) ?>" class="account__item-btn">
							↓ Скачать
						</a>
					</div>
					<?php endforeach; ?>
				</div>

				<?php else: ?>
				<div class="account__empty">
					<span class="account__empty-icon">📂</span>
					<h2 class="account__empty-title">Покупок пока нет</h2>
					<p class="account__empty-text">Здесь появятся все ваши приобретённые сметы</p>
					<a href="/shop" class="account__empty-btn">Перейти в каталог →</a>
				</div>
				<?php endif; ?>

			</div>
		</section>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/popups.php'; ?>
</body>
</html>
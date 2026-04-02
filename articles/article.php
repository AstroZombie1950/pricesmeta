<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

/* Slug из URL */
$slug = $_GET['slug'] ?? '';

/* Ищем статью в БД */
$stmt = $pdo->prepare('SELECT * FROM articles WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$article = $stmt->fetch();

/* 404 если не найдена */
if (!$article) {
	http_response_code(404);
	include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
	exit;
}

/* SEO-заголовок */
$pageTitle = $article['seo_title'] ?: $article['title'];

/* Форматируем дату по-русски */
$months = [
	1=>'января',2=>'февраля',3=>'марта',4=>'апреля',
	5=>'мая',6=>'июня',7=>'июля',8=>'августа',
	9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'
];
$ts   = strtotime($article['created_at']);
$date = date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="description" content="<?= htmlspecialchars($article['meta_desc'] ?? '') ?>">
	<meta name="keywords"    content="<?= htmlspecialchars($article['meta_keywords'] ?? '') ?>">
	<meta name="author"      content="ПрайсСмета">
	<!-- OG -->
	<meta property="og:title"       content="<?= htmlspecialchars($pageTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($article['meta_desc'] ?? '') ?>">
	<meta property="og:type"        content="article">
	<meta property="og:url"         content="https://прайслистмастера.рф/articles/<?= htmlspecialchars($slug) ?>">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/article_page.css">
</head>
<body>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'; ?>

	<!-- ===== MAIN ===== -->
	<main>
		<article class="article">
			<div class="container">
				<div class="article__inner">

					<!-- хлебные крошки -->
					<nav class="article__breadcrumbs" aria-label="Навигация">
						<a href="/">Главная</a>
						<span class="article__breadcrumbs-sep">›</span>
						<a href="/articles">Статьи</a>
						<span class="article__breadcrumbs-sep">›</span>
						<span><?= htmlspecialchars($article['title']) ?></span>
					</nav>

					<!-- шапка -->
					<header class="article__header">
						<p class="article__meta"><?= $date ?></p>
						<h1 class="article__title"><?= htmlspecialchars($article['title']) ?></h1>
					</header>

					<!-- обложка -->
					<?php if ($article['image']): ?>
					<img
						src="<?= htmlspecialchars($article['image']) ?>"
						alt="<?= htmlspecialchars($article['title']) ?>"
						class="article__cover"
					>
					<?php endif; ?>

					<!-- контент — HTML из БД -->
					<div class="article__content">
						<?= $article['content'] ?>
					</div>

					<!-- низ статьи -->
					<footer class="article__footer">
						<a href="/articles" class="article__back">← Все статьи</a>
					</footer>

				</div>
			</div>
		</article>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
</body>
</html>
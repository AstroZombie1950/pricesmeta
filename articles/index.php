<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Статьи и инструкции — ПрайсСмета</title>
	<meta name="description" content="Полезные статьи и инструкции для мастеров: как составить смету, расценки на работы, советы по электрике и сантехнике.">
	<meta name="keywords" content="статьи для мастеров, смета электрика, смета сантехника, инструкции по ремонту, расценки на работы">
	<meta name="author" content="ПрайсСмета">
	<!-- OG -->
	<meta property="og:title" content="Статьи и инструкции — ПрайсСмета">
	<meta property="og:description" content="Полезные статьи и инструкции для мастеров: как составить смету, расценки на работы, советы по электрике и сантехнике.">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://прайслистмастера.рф/articles">
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

	/* Пагинация — 6 статей на страницу */
	$perPage = 6;
	$page    = max(1, (int)($_GET['page'] ?? 1));
	$offset  = ($page - 1) * $perPage;

	/* Общее количество статей для пагинации */
	$total = $pdo->query('SELECT COUNT(*) FROM articles WHERE is_published = 1')->fetchColumn();
	$pages = (int) ceil($total / $perPage);

	/* Статьи текущей страницы */
	$stmt = $pdo->prepare('SELECT * FROM articles WHERE is_published = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?');
	$stmt->execute([$perPage, $offset]);
	$articles = $stmt->fetchAll();

	/* Русские месяцы для даты */
	$months = [
		1=>'января',2=>'февраля',3=>'марта',4=>'апреля',
		5=>'мая',6=>'июня',7=>'июля',8=>'августа',
		9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'
	];

	include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php';
	?>

	<!-- ===== MAIN ===== -->
	<main>
		<section class="articles-page">
			<div class="container">

				<!-- хлебные крошки -->
				<nav class="shop__breadcrumbs" aria-label="Навигация">
					<a href="/">Главная</a>
					<span class="shop__breadcrumbs-sep">/</span>
					<span>Статьи</span>
				</nav>

				<!-- заголовок -->
				<div class="articles-page__head">
					<h1 class="articles-page__title">Статьи и инструкции</h1>
					<p class="articles-page__sub">Советы мастерам, разбор расценок и готовые решения для вашей работы</p>
				</div>

				<!-- сетка карточек -->
				<div class="articles-page__grid">
					<?php foreach ($articles as $a):
						$ts   = strtotime($a['created_at']);
						$date = date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
					?>
					<article class="article-item">
						<a href="/articles/<?= htmlspecialchars($a['slug']) ?>" class="article-item__img-wrap">
							<img
								src="<?= htmlspecialchars($a['image'] ?: 'https://placehold.co/600x400/e8eaf0/6b7280?text=Статья') ?>"
								alt="<?= htmlspecialchars($a['title']) ?>"
								class="article-item__img"
								loading="lazy"
							>
						</a>
						<div class="article-item__body">
							<span class="article-item__date"><?= $date ?></span>
							<h2 class="article-item__title"><?= htmlspecialchars($a['title']) ?></h2>
							<?php if ($a['excerpt']): ?>
								<p class="article-item__excerpt"><?= htmlspecialchars($a['excerpt']) ?></p>
							<?php endif; ?>
							<a href="/articles/<?= htmlspecialchars($a['slug']) ?>" class="article-item__btn">Читать далее →</a>
						</div>
					</article>
					<?php endforeach; ?>

					<?php if (empty($articles)): ?>
					<p style="color: var(--color-muted); grid-column: 1/-1;">Статьи скоро появятся.</p>
					<?php endif; ?>
				</div>

				<!-- пагинация — показываем только если страниц больше одной -->
				<?php if ($pages > 1): ?>
				<nav class="pagination" aria-label="Страницы">
					<!-- стрелка назад -->
					<?php if ($page > 1): ?>
						<a href="?page=<?= $page - 1 ?>" class="pagination__btn pagination__btn--arrow" aria-label="Предыдущая">‹</a>
					<?php else: ?>
						<span class="pagination__btn pagination__btn--arrow" style="opacity:.35; pointer-events:none;">‹</span>
					<?php endif; ?>

					<!-- номера страниц -->
					<?php for ($i = 1; $i <= $pages; $i++): ?>
						<a
							href="?page=<?= $i ?>"
							class="pagination__btn <?= $i === $page ? 'pagination__btn--active' : '' ?>"
							<?= $i === $page ? 'aria-current="page"' : '' ?>
						><?= $i ?></a>
					<?php endfor; ?>

					<!-- стрелка вперёд -->
					<?php if ($page < $pages): ?>
						<a href="?page=<?= $page + 1 ?>" class="pagination__btn pagination__btn--arrow" aria-label="Следующая">›</a>
					<?php else: ?>
						<span class="pagination__btn pagination__btn--arrow" style="opacity:.35; pointer-events:none;">›</span>
					<?php endif; ?>
				</nav>
				<?php endif; ?>

			</div>
		</section>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
</body>
</html>
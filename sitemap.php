<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';

/* Отдаём как XML */
header('Content-Type: application/xml; charset=utf-8');

$domain = 'https://прайслистмастера.рф';

/* Опубликованные товары */
$products = $pdo->query('SELECT slug, created_at FROM products WHERE is_published = 1')->fetchAll();

/* Опубликованные статьи */
$articles = $pdo->query('SELECT slug, created_at FROM articles WHERE is_published = 1')->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

	<!-- Статичные страницы -->
	<url>
		<loc><?= $domain ?>/</loc>
		<changefreq>weekly</changefreq>
		<priority>1.0</priority>
	</url>
	<url>
		<loc><?= $domain ?>/shop</loc>
		<changefreq>weekly</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc><?= $domain ?>/articles</loc>
		<changefreq>weekly</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc><?= $domain ?>/faq</loc>
		<changefreq>monthly</changefreq>
		<priority>0.5</priority>
	</url>

	<!-- Товары -->
	<?php foreach ($products as $p): ?>
	<url>
		<loc><?= $domain ?>/shop/<?= htmlspecialchars($p['slug']) ?></loc>
		<lastmod><?= date('Y-m-d', strtotime($p['created_at'])) ?></lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.8</priority>
	</url>
	<?php endforeach; ?>

	<!-- Статьи -->
	<?php foreach ($articles as $a): ?>
	<url>
		<loc><?= $domain ?>/articles/<?= htmlspecialchars($a['slug']) ?></loc>
		<lastmod><?= date('Y-m-d', strtotime($a['created_at'])) ?></lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.7</priority>
	</url>
	<?php endforeach; ?>

</urlset>
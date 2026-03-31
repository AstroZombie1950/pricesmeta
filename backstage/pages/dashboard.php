<?php
/* Считаем статистику */
$stats = [
	'products' => $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
	'articles' => $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
	'users'    => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
	'purchases'=> $pdo->query('SELECT COUNT(*) FROM purchases')->fetchColumn(),
];

/* Последние 5 статей */
$lastArticles = $pdo->query('SELECT title, slug, is_published, created_at FROM articles ORDER BY created_at DESC LIMIT 5')->fetchAll();

/* Последние 5 товаров */
$lastProducts = $pdo->query('SELECT title, slug, is_published, created_at FROM products ORDER BY created_at DESC LIMIT 5')->fetchAll();

$pageTitle = 'Дашборд';
$route     = 'dashboard';

ob_start();
?>

<!-- Статистика -->
<div class="bs-stats">
	<div class="bs-stat">
		<span class="bs-stat__label">Товаров</span>
		<span class="bs-stat__value"><?= $stats['products'] ?></span>
	</div>
	<div class="bs-stat">
		<span class="bs-stat__label">Статей</span>
		<span class="bs-stat__value"><?= $stats['articles'] ?></span>
	</div>
	<div class="bs-stat">
		<span class="bs-stat__label">Пользователей</span>
		<span class="bs-stat__value"><?= $stats['users'] ?></span>
	</div>
	<div class="bs-stat">
		<span class="bs-stat__label">Покупок</span>
		<span class="bs-stat__value"><?= $stats['purchases'] ?></span>
	</div>
</div>

<!-- Последние записи -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

	<!-- Последние статьи -->
	<div>
		<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
			<h2 style="font-family:var(--font-head); font-size:16px; font-weight:700;">Последние статьи</h2>
			<a href="/backstage/?route=articles/create" class="btn btn--primary btn--sm">+ Новая</a>
		</div>
		<div class="bs-list">
			<?php foreach ($lastArticles as $a): ?>
			<div class="bs-item">
				<div class="bs-item__info">
					<span class="bs-item__title"><?= htmlspecialchars($a['title']) ?></span>
					<span class="bs-item__meta"><?= date('d.m.Y', strtotime($a['created_at'])) ?></span>
				</div>
				<div class="bs-item__actions">
					<span class="bs-badge <?= $a['is_published'] ? 'bs-badge--published' : 'bs-badge--draft' ?>">
						<?= $a['is_published'] ? 'Опубл.' : 'Черновик' ?>
					</span>
					<a href="/backstage/?route=articles/edit&slug=<?= urlencode($a['slug']) ?>" class="btn btn--outline btn--sm">Ред.</a>
				</div>
			</div>
			<?php endforeach; ?>
			<?php if (empty($lastArticles)): ?>
				<p style="color:var(--color-muted); font-size:14px;">Статей пока нет</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Последние товары -->
	<div>
		<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
			<h2 style="font-family:var(--font-head); font-size:16px; font-weight:700;">Последние товары</h2>
			<a href="/backstage/?route=products/create" class="btn btn--primary btn--sm">+ Новый</a>
		</div>
		<div class="bs-list">
			<?php foreach ($lastProducts as $p): ?>
			<div class="bs-item">
				<div class="bs-item__info">
					<span class="bs-item__title"><?= htmlspecialchars($p['title']) ?></span>
					<span class="bs-item__meta"><?= date('d.m.Y', strtotime($p['created_at'])) ?></span>
				</div>
				<div class="bs-item__actions">
					<span class="bs-badge <?= $p['is_published'] ? 'bs-badge--published' : 'bs-badge--draft' ?>">
						<?= $p['is_published'] ? 'Опубл.' : 'Черновик' ?>
					</span>
					<a href="/backstage/?route=products/edit&slug=<?= urlencode($p['slug']) ?>" class="btn btn--outline btn--sm">Ред.</a>
				</div>
			</div>
			<?php endforeach; ?>
			<?php if (empty($lastProducts)): ?>
				<p style="color:var(--color-muted); font-size:14px;">Товаров пока нет</p>
			<?php endif; ?>
		</div>
	</div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
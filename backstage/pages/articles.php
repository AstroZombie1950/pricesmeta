<?php
/* Удаление статьи */
$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
	$id = (int)($_POST['id'] ?? 0);
	if ($id) {
		/* Получаем картинку чтобы удалить файл */
		$row = $pdo->prepare('SELECT image FROM articles WHERE id = ?');
		$row->execute([$id]);
		$row = $row->fetch();

		/* Удаляем файл картинки если есть */
		if (!empty($row['image'])) {
			$imgPath = $_SERVER['DOCUMENT_ROOT'] . $row['image'];
			if (file_exists($imgPath)) unlink($imgPath);
		}

		$pdo->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
		$alert = 'ok:Статья удалена';
	}
}

/* Все статьи, сначала новые */
$articles = $pdo->query('SELECT id, title, slug, is_published, created_at FROM articles ORDER BY created_at DESC')->fetchAll();

$pageTitle    = 'Статьи';
$route        = 'articles';
$topbarActions = '<a href="/backstage/?route=articles/create" class="btn btn--primary">+ Новая статья</a>';

ob_start();
?>

<?php if ($alert): ?>
	<?php [$type, $msg] = explode(':', $alert, 2); ?>
	<div class="bs-alert bs-alert--<?= $type === 'ok' ? 'ok' : 'err' ?>" style="margin-bottom:20px;">
		<?= htmlspecialchars($msg) ?>
	</div>
<?php endif; ?>

<!-- Поиск -->
<div style="margin-bottom:16px;">
	<input
		type="text"
		class="bs-search"
		placeholder="Поиск по названию или slug..."
		oninput="filterItems(this.value)"
	>
</div>

<!-- Список статей -->
<div class="bs-list">
	<?php foreach ($articles as $a): ?>
	<div class="bs-item" data-search="<?= htmlspecialchars(mb_strtolower($a['title'] . ' ' . $a['slug'])) ?>">
		<div class="bs-item__info">
			<span class="bs-item__title"><?= htmlspecialchars($a['title']) ?></span>
			<span class="bs-item__meta">
				<?= htmlspecialchars($a['slug']) ?> · <?= date('d.m.Y', strtotime($a['created_at'])) ?>
			</span>
		</div>
		<div class="bs-item__actions">
			<span class="bs-badge <?= $a['is_published'] ? 'bs-badge--published' : 'bs-badge--draft' ?>">
				<?= $a['is_published'] ? 'Опубликована' : 'Черновик' ?>
			</span>
			<a
				href="/articles/<?= htmlspecialchars($a['slug']) ?>"
				class="btn btn--outline btn--sm"
				target="_blank"
			>↗</a>
			<a
				href="/backstage/?route=articles/edit&slug=<?= urlencode($a['slug']) ?>"
				class="btn btn--outline btn--sm"
			>Редактировать</a>
			<button
				type="button"
				class="btn btn--danger btn--sm"
				onclick="openModal(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['title'])) ?>')"
			>Удалить</button>
		</div>
	</div>
	<?php endforeach; ?>

	<?php if (empty($articles)): ?>
		<p style="color:var(--color-muted); font-size:14px; padding:8px 0;">Статей пока нет. <a href="/backstage/?route=articles/create" style="color:var(--color-accent);">Создать первую →</a></p>
	<?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
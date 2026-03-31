<?php
/* Удаление товара */
$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
	$id = (int)($_POST['id'] ?? 0);
	if ($id) {
		/* Получаем картинку чтобы удалить файл */
		$row = $pdo->prepare('SELECT image FROM products WHERE id = ?');
		$row->execute([$id]);
		$row = $row->fetch();

		if (!empty($row['image'])) {
			$imgPath = $_SERVER['DOCUMENT_ROOT'] . $row['image'];
			if (file_exists($imgPath)) unlink($imgPath);
		}

		$pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
		$alert = 'ok:Товар удалён';
	}
}

/* Все товары, сначала новые */
$products = $pdo->query('SELECT id, title, slug, price, old_price, is_published, created_at FROM products ORDER BY created_at DESC')->fetchAll();

$pageTitle     = 'Товары';
$route         = 'products';
$topbarActions = '<a href="/backstage/?route=products/create" class="btn btn--primary">+ Новый товар</a>';

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

<!-- Список товаров -->
<div class="bs-list">
	<?php foreach ($products as $p): ?>
	<div class="bs-item" data-search="<?= htmlspecialchars(mb_strtolower($p['title'] . ' ' . $p['slug'])) ?>">
		<div class="bs-item__info">
			<span class="bs-item__title"><?= htmlspecialchars($p['title']) ?></span>
			<span class="bs-item__meta">
				<?= htmlspecialchars($p['slug']) ?> ·
				<?= number_format($p['price'], 0, '', ' ') ?> ₽
				<?php if ($p['old_price']): ?>
					<span style="text-decoration:line-through; color:var(--color-muted);">
						<?= number_format($p['old_price'], 0, '', ' ') ?> ₽
					</span>
				<?php endif; ?>
				· <?= date('d.m.Y', strtotime($p['created_at'])) ?>
			</span>
		</div>
		<div class="bs-item__actions">
			<span class="bs-badge <?= $p['is_published'] ? 'bs-badge--published' : 'bs-badge--draft' ?>">
				<?= $p['is_published'] ? 'Опубликован' : 'Черновик' ?>
			</span>
			<a
				href="/shop/<?= htmlspecialchars($p['slug']) ?>"
				class="btn btn--outline btn--sm"
				target="_blank"
			>↗</a>
			<a
				href="/backstage/?route=products/edit&slug=<?= urlencode($p['slug']) ?>"
				class="btn btn--outline btn--sm"
			>Редактировать</a>
			<button
				type="button"
				class="btn btn--danger btn--sm"
				onclick="openModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>')"
			>Удалить</button>
		</div>
	</div>
	<?php endforeach; ?>

	<?php if (empty($products)): ?>
		<p style="color:var(--color-muted); font-size:14px; padding:8px 0;">
			Товаров пока нет. <a href="/backstage/?route=products/create" style="color:var(--color-accent);">Создать первый →</a>
		</p>
	<?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
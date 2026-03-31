<?php
$alert = '';

/* ─── Смена пароля ────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
	$id       = (int)($_POST['id'] ?? 0);
	$password = $_POST['password'] ?? '';

	if (mb_strlen($password) < 6) {
		$alert = 'err:Пароль должен быть не менее 6 символов';
	} else {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $id]);
		$alert = 'ok:Пароль обновлён';
	}
}

/* ─── Смена роли ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
	$id   = (int)($_POST['id'] ?? 0);
	$role = $_POST['role'] ?? 'user';

	/* Нельзя снять роль с самого себя */
	if ($id === auth_user_id()) {
		$alert = 'err:Нельзя изменить роль своей учётной записи';
	} else {
		$pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
		$alert = 'ok:Роль обновлена';
	}
}

/* ─── Удаление пользователя ───────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
	$id = (int)($_POST['id'] ?? 0);

	if ($id === auth_user_id()) {
		$alert = 'err:Нельзя удалить свою учётную запись';
	} else {
		$pdo->prepare('DELETE FROM purchases WHERE user_id = ?')->execute([$id]);
		$pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
		$alert = 'ok:Пользователь удалён';
	}
}

/* Все пользователи */
$users = $pdo->query('SELECT id, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Пользователи';
$route     = 'users';

ob_start();
?>

<?php if ($alert): ?>
	<?php [$type, $msg] = explode(':', $alert, 2); ?>
	<div class="bs-alert bs-alert--<?= $type === 'ok' ? 'ok' : 'err' ?>" style="margin-bottom:20px;">
		<?= htmlspecialchars($msg) ?>
	</div>
<?php endif; ?>

<div style="margin-bottom:16px;">
	<input type="text" class="bs-search" placeholder="Поиск по email..." oninput="filterItems(this.value)">
</div>

<div class="bs-list">
	<?php foreach ($users as $u):
		$isSelf = ($u['id'] === auth_user_id());
	?>
	<div class="bs-item" data-search="<?= htmlspecialchars(mb_strtolower($u['email'])) ?>">

		<div class="bs-item__info">
			<span class="bs-item__title">
				<?= htmlspecialchars($u['email']) ?>
				<?php if ($isSelf): ?>
					<span style="font-size:11px; color:var(--color-muted); font-weight:400;"> (вы)</span>
				<?php endif; ?>
			</span>
			<span class="bs-item__meta">Зарегистрирован <?= date('d.m.Y', strtotime($u['created_at'])) ?></span>
		</div>

		<div class="bs-item__actions">
			<span class="bs-badge <?= $u['role'] === 'admin' ? 'bs-badge--published' : 'bs-badge--draft' ?>">
				<?= $u['role'] === 'admin' ? 'Админ' : 'Пользователь' ?>
			</span>
			<button type="button" class="btn btn--outline btn--sm"
				onclick="openUserPanel(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['email'])) ?>', '<?= $u['role'] ?>')">
				Редактировать
			</button>
			<?php if (!$isSelf): ?>
			<button type="button" class="btn btn--danger btn--sm"
				onclick="openModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['email'])) ?>')">
				Удалить
			</button>
			<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>

	<?php if (empty($users)): ?>
		<p style="color:var(--color-muted); font-size:14px; padding:8px 0;">Пользователей пока нет.</p>
	<?php endif; ?>
</div>

<!-- Панель редактирования пользователя -->
<div class="modal-overlay" id="userPanel">
	<div class="modal" style="max-width:460px;">

		<h2 class="modal__title">Редактировать пользователя</h2>
		<p class="modal__text" id="userPanelEmail" style="color:var(--color-text); font-weight:600;"></p>

		<form method="POST" style="display:flex; flex-direction:column; gap:10px;">
			<input type="hidden" name="id" id="userPanelRoleId">
			<div class="field">
				<label for="userPanelRole">Роль</label>
				<select id="userPanelRole" name="role" class="bs-input">
					<option value="user">Пользователь</option>
					<option value="admin">Администратор</option>
				</select>
			</div>
			<button type="submit" name="change_role" class="btn btn--outline">Сохранить роль</button>
		</form>

		<hr style="border:none; border-top:1px solid var(--color-border); margin:8px 0;">

		<form method="POST" style="display:flex; flex-direction:column; gap:10px;">
			<input type="hidden" name="id" id="userPanelPwdId">
			<div class="field">
				<label for="userPanelPwd">Новый пароль</label>
				<input type="password" id="userPanelPwd" name="password" class="bs-input" placeholder="Минимум 6 символов">
			</div>
			<button type="submit" name="change_password" class="btn btn--outline">Сменить пароль</button>
		</form>

		<button type="button" class="btn btn--outline" onclick="closeUserPanel()" style="margin-top:4px;">Закрыть</button>
	</div>
</div>

<script>
function openUserPanel(id, email, role) {
	document.getElementById('userPanelEmail').textContent = email;
	document.getElementById('userPanelRoleId').value      = id;
	document.getElementById('userPanelPwdId').value       = id;
	document.getElementById('userPanelRole').value        = role;
	document.getElementById('userPanelPwd').value         = '';
	document.getElementById('userPanel').classList.add('is-open');
}

function closeUserPanel() {
	document.getElementById('userPanel').classList.remove('is-open');
}

document.getElementById('userPanel')?.addEventListener('click', function(e) {
	if (e.target === this) closeUserPanel();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
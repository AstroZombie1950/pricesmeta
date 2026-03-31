<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle ?? 'Панель управления') ?> — Backstage</title>
	<meta name="robots" content="noindex, nofollow">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/source/css/adminpanel.css">
    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body>
<div class="bs">

	<!-- Сайдбар -->
	<aside class="bs-sidebar">
		<div class="bs-sidebar__logo">Прайс<span>Смета</span> <span style="color:var(--color-muted);font-weight:400;">/ backstage</span></div>

		<nav class="bs-nav">
			<span class="bs-nav__label">Контент</span>
			<a href="/backstage/?route=articles"  class="bs-nav__link <?= str_starts_with($route ?? '', 'articles') ? 'is-active' : '' ?>">
				<span class="bs-nav__icon">📝</span> Статьи
			</a>
			<a href="/backstage/?route=products"  class="bs-nav__link <?= str_starts_with($route ?? '', 'products') ? 'is-active' : '' ?>">
				<span class="bs-nav__icon">📦</span> Товары
			</a>

			<span class="bs-nav__label">Система</span>
			<a href="/backstage/?route=users"     class="bs-nav__link <?= ($route ?? '') === 'users' ? 'is-active' : '' ?>">
				<span class="bs-nav__icon">👥</span> Пользователи
			</a>
			<a href="/backstage/"                 class="bs-nav__link <?= ($route ?? '') === 'dashboard' ? 'is-active' : '' ?>">
				<span class="bs-nav__icon">◻</span> Дашборд
			</a>

			<span class="bs-nav__label">Сайт</span>
			<a href="/shop"     class="bs-nav__link" target="_blank"><span class="bs-nav__icon">🛍</span> Магазин ↗</a>
			<a href="/articles" class="bs-nav__link" target="_blank"><span class="bs-nav__icon">📰</span> Статьи ↗</a>
		</nav>

		<div class="bs-sidebar__footer">
			<div class="bs-sidebar__user"><?= htmlspecialchars(auth_user_email()) ?></div>
			<a href="/backstage/?logout=1" class="bs-sidebar__logout">Выйти</a>
		</div>
	</aside>

	<!-- Основной контент -->
	<main class="bs-main">
		<div class="bs-topbar">
			<h1 class="bs-topbar__title"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
			<div class="bs-topbar__actions">
				<?php if (!empty($topbarActions)) echo $topbarActions; ?>
			</div>
		</div>

		<div class="bs-content">
			<?php echo $content ?? ''; ?>
		</div>
	</main>

</div>

<!-- Модалка удаления — общая для всех страниц -->
<div class="modal-overlay" id="deleteModal">
	<div class="modal">
		<h2 class="modal__title">Удалить?</h2>
		<p class="modal__text">Вы удаляете: <span class="modal__slug" id="modalLabel"></span>. Это действие нельзя отменить.</p>
		<div class="modal__actions">
			<form method="POST" style="display:contents;">
				<input type="hidden" name="delete" value="1">
				<input type="hidden" name="id" id="modalId">
				<button type="submit" class="btn btn--danger">Удалить</button>
			</form>
			<button type="button" class="btn btn--outline" onclick="closeModal()">Отмена</button>
		</div>
	</div>
</div>

<script>
/* Выход */
document.querySelector('a[href*="logout"]')?.addEventListener('click', e => {
	e.preventDefault();
	auth_logout_redirect(e.currentTarget.href);
});

function auth_logout_redirect(url) {
	window.location.href = url;
}

/* Модалка удаления */
function openModal(id, label) {
	document.getElementById('modalLabel').textContent = label;
	document.getElementById('modalId').value = id;
	document.getElementById('deleteModal').classList.add('is-open');
}

function closeModal() {
	document.getElementById('deleteModal').classList.remove('is-open');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
	if (e.target === this) closeModal();
});

/* ─── WYSIWYG ───────────────────────────────────── */
function switchTab(name, btn) {
	document.querySelectorAll('.tab').forEach(t => t.classList.remove('is-active'));
	document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('is-active'));
	btn.classList.add('is-active');
	document.getElementById('panel-' + name).classList.add('is-active');
	const current = document.getElementById('contentInput')?.value || '';
	if (name === 'wysiwyg') document.getElementById('editor').innerHTML = current;
	else                    document.getElementById('htmlEditor').value  = current;
}

function syncFromWysiwyg() {
	const html = document.getElementById('editor').innerHTML;
	document.getElementById('contentInput').value = html;
	if (document.getElementById('htmlEditor'))
		document.getElementById('htmlEditor').value = html;
}

function syncFromHtml() {
	const html = document.getElementById('htmlEditor').value;
	document.getElementById('contentInput').value = html;
	if (document.getElementById('editor'))
		document.getElementById('editor').innerHTML = html;
}

function fmt(cmd) {
	document.getElementById('editor').focus();
	document.execCommand(cmd, false, null);
	syncFromWysiwyg();
	updatePreview();
}

function fmtBlock(tag) {
	document.getElementById('editor').focus();
	document.execCommand('formatBlock', false, tag);
	syncFromWysiwyg();
	updatePreview();
}

function insertLink() {
	const url = prompt('Введите URL:');
	if (url) {
		document.getElementById('editor').focus();
		document.execCommand('createLink', false, url);
		syncFromWysiwyg();
		updatePreview();
	}
}

function updatePreview() {
	const content = document.getElementById('contentInput')?.value || '';
	const pc = document.getElementById('prev_content');
	if (pc) pc.innerHTML = content;
}

/* Загрузка картинки — превью */
function previewImage(input) {
	const file = input.files[0];
	if (!file) return;
	const reader = new FileReader();
	reader.onload = e => {
		const prev = document.getElementById('imagePreview');
		if (prev) { prev.src = e.target.result; prev.style.display = 'block'; }
		const cover = document.getElementById('prev_cover');
		const pimg  = document.getElementById('prev_img');
		if (cover) cover.style.display = 'block';
		if (pimg)  pimg.src = e.target.result;
	};
	reader.readAsDataURL(file);
}

/* Автогенерация slug из заголовка */
function syncSlug(val) {
	if (document.getElementById('slug')?.dataset.manual) return;
	const map = {'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'yo','ж':'zh','з':'z','и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'kh','ц':'ts','ч':'ch','ш':'sh','щ':'shch','ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya'};
	const slug = val.toLowerCase()
		.replace(/[а-яёА-ЯЁ]/g, c => map[c] ?? '')
		.replace(/[^a-z0-9]+/g, '-')
		.replace(/^-+|-+$/g, '');
	document.getElementById('slug').value = slug;
}

document.getElementById('slug')?.addEventListener('input', function() {
	if (!this.readOnly) this.dataset.manual = '1';
});

/* Синхронизация контента перед отправкой формы */
document.querySelector('form[data-editor]')?.addEventListener('submit', () => {
	if (!document.getElementById('contentInput')?.value) {
		const ed = document.getElementById('editor');
		if (ed) document.getElementById('contentInput').value = ed.innerHTML;
	}
});

/* Фильтр поиска в списках */
function filterItems(q) {
	const term = q.toLowerCase();
	document.querySelectorAll('.bs-item[data-search]').forEach(el => {
		el.style.display = el.dataset.search.includes(term) ? '' : 'none';
	});
}
</script>
</body>
</html>
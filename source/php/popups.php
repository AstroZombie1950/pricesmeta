<?php
/* Попапы подключаются в footer.php — доступны на всех страницах */
?>

<!-- ===== ПОПАП АВТОРИЗАЦИИ ===== -->
<div class="popup-overlay" id="popupAuth">
	<div class="popup">

		<button type="button" class="popup__close" onclick="closePopup('popupAuth')">✕</button>

		<!-- Шаг 1: ввод email -->
		<div class="popup__step" id="authStep1">
			<h2 class="popup__title">Войти или зарегистрироваться</h2>
			<p class="popup__sub">Введите email — мы всё сделаем сами</p>

			<div class="popup__alert" id="authAlert1" style="display:none;"></div>

			<div class="popup__field">
				<label for="authEmail">Email</label>
				<input type="email" id="authEmail" class="popup__input" placeholder="your@email.com"
					onkeydown="if(event.key==='Enter') submitCheckEmail()">
			</div>

			<button type="button" class="popup__btn" id="btnCheckEmail" onclick="submitCheckEmail()">
				Продолжить →
			</button>
		</div>

		<!-- Шаг 2: ввод пароля (только для существующих пользователей) -->
		<div class="popup__step" id="authStep2" style="display:none;">
			<button type="button" class="popup__back" onclick="authGoBack()">← Назад</button>
			<h2 class="popup__title">Добро пожаловать</h2>
			<p class="popup__sub" id="authStep2Email"></p>

			<div class="popup__alert" id="authAlert2" style="display:none;"></div>

			<div class="popup__field">
				<label for="authPassword">Пароль</label>
				<input type="password" id="authPassword" class="popup__input" placeholder="••••••••"
					onkeydown="if(event.key==='Enter') submitLogin()">
			</div>

			<a href="#" class="popup__forgot" onclick="event.preventDefault(); document.getElementById('resetEmail').value = document.getElementById('authEmail').value; showStep(4)">Восстановить пароль</a>

			<button type="button" class="popup__btn" id="btnLogin" onclick="submitLogin()">
				Войти →
			</button>
		</div>

		<!-- Шаг 3: успех / переход к оплате -->
		<div class="popup__step" id="authStep3" style="display:none;">
			<div class="popup__success-icon">✓</div>
			<h2 class="popup__title" id="authStep3Title">Вы вошли</h2>
			<p class="popup__sub" id="authStep3Sub"></p>

			<div class="popup__alert" id="authAlert3" style="display:none;"></div>

			<!-- кнопка меняется в зависимости от контекста -->
			<button type="button" class="popup__btn" id="btnAuthFinal" onclick="authFinalAction()">
				Перейти в кабинет →
			</button>
		</div>

		<!-- Шаг 4: запрос сброса пароля -->
		<div class="popup__step" id="authStep4" style="display:none;">
			<button type="button" class="popup__back" onclick="showStep(2)">← Назад</button>
			<h2 class="popup__title">Восстановить пароль</h2>
			<p class="popup__sub">Пришлём ссылку на указанный email</p>

			<div class="popup__alert" id="authAlert4" style="display:none;"></div>

			<div class="popup__field">
				<label for="resetEmail">Email</label>
				<input type="email" id="resetEmail" class="popup__input" placeholder="your@email.com"
					onkeydown="if(event.key==='Enter') submitRequestReset()">
			</div>

			<button type="button" class="popup__btn" id="btnRequestReset" onclick="submitRequestReset()">
				Отправить письмо →
			</button>
		</div>

		<!-- Шаг 5: письмо отправлено -->
		<div class="popup__step" id="authStep5" style="display:none;">
			<div class="popup__success-icon">✉</div>
			<h2 class="popup__title">Письмо отправлено</h2>
			<p class="popup__sub" id="authStep5Sub"></p>
			<p style="font-size:13px; color:var(--color-muted);">Перейдите по ссылке в письме чтобы задать новый пароль. Ссылка действует 1 час.</p>
			<button type="button" class="popup__btn" onclick="closePopup('popupAuth')">Закрыть</button>
		</div>

	</div>
</div>

<!-- ===== ПОПАП ПОКУПКИ ===== -->
<div class="popup-overlay" id="popupBuy">
	<div class="popup">

		<button type="button" class="popup__close" onclick="closePopup('popupBuy')">✕</button>

		<div class="popup__buy-icon">📦</div>
		<h2 class="popup__buy-title" id="buyTitle">Получить доступ</h2>
		<p class="popup__buy-price" id="buyPrice"></p>
		<p class="popup__buy-desc">Бессрочный доступ к файлу сразу после оплаты</p>

		<div class="popup__alert" id="buyAlert" style="display:none;"></div>

		<button type="button" class="popup__btn" id="buyBtn" onclick="submitBuy()">
			Перейти к оплате →
		</button>

		<p style="font-size:12px; color:var(--color-muted); text-align:center; margin-top:8px;">
			Защищённая оплата · Мгновенный доступ
		</p>

	</div>
</div>

<style>
/* ===== ПОПАПЫ ===== */
.popup-overlay {
	position: fixed;
	inset: 0;
	background: rgba(0,0,0,.45);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 500;
	opacity: 0;
	pointer-events: none;
	transition: opacity .2s;
	padding: 16px;
	visibility: hidden;
}

.popup-overlay.is-open {
	opacity: 1;
	pointer-events: auto;
	visibility: visible;
}

.popup {
	background: var(--color-surface);
	border: 1px solid var(--color-border);
	border-radius: var(--radius-md);
	padding: 32px;
	width: 100%;
	max-width: 400px;
	position: relative;
	box-shadow: 0 20px 60px rgba(0,0,0,.15);
	display: flex;
	flex-direction: column;
	gap: 16px;
}

/* Закрыть */
.popup__close {
	position: absolute;
	top: 16px;
	right: 16px;
	background: none;
	border: none;
	font-size: 18px;
	color: var(--color-muted);
	cursor: pointer;
	line-height: 1;
	transition: color .2s;
}

.popup__close:hover { color: var(--color-text); }

/* Заголовки */
.popup__title {
	font-family: var(--font-head);
	font-size: 20px;
	font-weight: 700;
	color: var(--color-text);
}

.popup__sub {
	font-size: 14px;
	color: var(--color-muted);
	margin-top: -8px;
}

/* Назад */
.popup__back {
	background: none;
	border: none;
	font-family: var(--font-head);
	font-size: 13px;
	font-weight: 500;
	color: var(--color-muted);
	cursor: pointer;
	padding: 0;
	margin-bottom: -4px;
	transition: color .2s;
}

.popup__back:hover { color: var(--color-accent); }

/* Шаг */
.popup__step {
	display: flex;
	flex-direction: column;
	gap: 14px;
}

/* Поля */
.popup__field {
	display: flex;
	flex-direction: column;
	gap: 6px;
}

.popup__field label {
	font-family: var(--font-head);
	font-size: 13px;
	font-weight: 600;
	color: var(--color-text);
}

.popup__input {
	width: 100%;
	background: var(--color-bg);
	color: var(--color-text);
	border: 1px solid var(--color-border);
	border-radius: var(--radius-sm);
	font-family: var(--font-body);
	font-size: 15px;
	padding: 11px 14px;
	outline: none;
	transition: border-color .2s, box-shadow .2s;
}

.popup__input:focus {
	border-color: var(--color-accent);
	box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.popup__input::placeholder { color: var(--color-muted); }

/* Ссылка восстановления */
.popup__forgot {
	font-size: 13px;
	color: var(--color-muted);
	text-decoration: underline;
	text-underline-offset: 3px;
	align-self: flex-start;
	margin-top: -6px;
	transition: color .2s;
}

.popup__forgot:hover { color: var(--color-accent); }

/* Кнопка */
.popup__btn {
	width: 100%;
	height: 48px;
	background: var(--color-text);
	color: #fff;
	border: none;
	border-radius: var(--radius-sm);
	font-family: var(--font-head);
	font-size: 15px;
	font-weight: 600;
	cursor: pointer;
	transition: background .2s;
	margin-top: 4px;
}

.popup__btn:hover { background: var(--color-accent); }
.popup__btn:disabled { opacity: .6; cursor: not-allowed; }

/* Иконка успеха */
.popup__success-icon {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	background: rgba(22,163,74,.1);
	border: 1px solid rgba(22,163,74,.25);
	color: #16A34A;
	font-size: 22px;
	display: flex;
	align-items: center;
	justify-content: center;
	align-self: flex-start;
}

/* Алерт */
.popup__alert {
	padding: 10px 14px;
	border-radius: var(--radius-sm);
	font-size: 14px;
	font-weight: 500;
}

.popup__alert--ok  { background: rgba(22,163,74,.08); border: 1px solid rgba(22,163,74,.25); color: #15803D; }
.popup__alert--err { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25); color: #B91C1C; }

/* Попап покупки */
.popup__buy-icon  { font-size: 40px; text-align: center; }
.popup__buy-title { font-family: var(--font-head); font-size: 20px; font-weight: 700; text-align: center; }
.popup__buy-price { font-family: var(--font-head); font-size: 28px; font-weight: 700; text-align: center; color: var(--color-accent); }
.popup__buy-desc  { font-size: 14px; color: var(--color-muted); text-align: center; }
</style>

<script>
/* ─── Открыть / закрыть ─────────────────────────────────── */
function openPopup(id) {
	document.getElementById(id).classList.add('is-open');
	document.body.style.overflow = 'hidden';
}

function closePopup(id) {
	document.getElementById(id).classList.remove('is-open');
	document.body.style.overflow = '';
}

/* Клик по фону закрывает */
document.querySelectorAll('.popup-overlay').forEach(overlay => {
	overlay.addEventListener('click', function(e) {
		if (e.target === this) closePopup(this.id);
	});
});

/* Escape закрывает */
document.addEventListener('keydown', e => {
	if (e.key === 'Escape') {
		closePopup('popupAuth');
		closePopup('popupBuy');
	}
});

/* ─── Состояние попапа авторизации ──────────────────────── */
let _authContext = null; /* { productId, title, price } или null если просто хедер */

/* Слушаем события */
document.addEventListener('open-login', () => {
	_authContext = null;
	openAuthPopup();
});

document.addEventListener('open-buy', e => {
	/* Если не авторизован — сначала авторизация, потом оплата */
	<?php if (empty($_SESSION['user_id'])): ?>
		_authContext = e.detail;
		openAuthPopup();
	<?php else: ?>
		/* Уже авторизован — сразу попап покупки */
		openBuyPopup(e.detail);
	<?php endif; ?>
});

/* ─── Открыть попап авторизации с чистым состоянием ─────── */
function openAuthPopup() {
	/* Сбрасываем все шаги */
	showStep(1);
	document.getElementById('authEmail').value = '';
	document.getElementById('authPassword').value = '';
	document.getElementById('resetEmail').value = '';
	hideAlert('authAlert1');
	hideAlert('authAlert2');
	hideAlert('authAlert3');
	openPopup('popupAuth');
	setTimeout(() => document.getElementById('authEmail').focus(), 100);
}

/* ─── Переключение шагов ─────────────────────────────────── */
function showStep(n) {
	[1,2,3,4,5].forEach(i => {
		document.getElementById('authStep' + i).style.display = i === n ? 'flex' : 'none';
	});
}

function authGoBack() {
	showStep(1);
	hideAlert('authAlert2');
	setTimeout(() => document.getElementById('authEmail').focus(), 100);
}

/* ─── Алерты ────────────────────────────────────────────── */
function showAlert(id, type, msg) {
	const el = document.getElementById(id);
	el.className = 'popup__alert popup__alert--' + type;
	el.textContent = msg;
	el.style.display = 'block';
}

function hideAlert(id) {
	const el = document.getElementById(id);
	el.style.display = 'none';
	el.textContent = '';
}

/* ─── Шаг 1: проверяем email ─────────────────────────────── */
async function submitCheckEmail() {
	const email = document.getElementById('authEmail').value.trim();

	if (!email) {
		showAlert('authAlert1', 'err', 'Введите email');
		return;
	}

	const btn = document.getElementById('btnCheckEmail');
	btn.disabled = true;
	btn.textContent = 'Проверяем...';

	try {
		const res  = await fetch('/api/auth.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ action: 'check_email', email })
		});
		const data = await res.json();

		if (!data.ok) {
			showAlert('authAlert1', 'err', data.error || 'Ошибка');
			return;
		}

		if (data.exists) {
			/* Пользователь есть — просим пароль */
			document.getElementById('authStep2Email').textContent = email;
			showStep(2);
			setTimeout(() => document.getElementById('authPassword').focus(), 100);
		} else {
			/* Нового пользователя регистрируем, шлём пароль на почту */
			await autoRegister(email);
		}

	} catch {
		showAlert('authAlert1', 'err', 'Ошибка сети. Попробуйте ещё раз.');
	} finally {
		btn.disabled = false;
		btn.textContent = 'Продолжить →';
	}
}

/* ─── Авторегистрация ────────────────────────────────────── */
async function autoRegister(email) {
	try {
		const res  = await fetch('/api/auth.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ action: 'auto_register', email })
		});
		const data = await res.json();

		if (data.ok) {
			authShowSuccess('Аккаунт создан', 'Пароль отправлен на ' + email);
		} else {
			showAlert('authAlert1', 'err', data.error || 'Ошибка регистрации');
		}
	} catch {
		showAlert('authAlert1', 'err', 'Ошибка сети. Попробуйте ещё раз.');
	}
}

/* ─── Шаг 2: вход по паролю ─────────────────────────────── */
async function submitLogin() {
	const email    = document.getElementById('authEmail').value.trim();
	const password = document.getElementById('authPassword').value;

	if (!password) {
		showAlert('authAlert2', 'err', 'Введите пароль');
		return;
	}

	const btn = document.getElementById('btnLogin');
	btn.disabled = true;
	btn.textContent = 'Входим...';

	try {
		const res  = await fetch('/api/auth.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ action: 'login', email, password })
		});
		const data = await res.json();

		if (data.ok) {
			authShowSuccess('Вы вошли', '');
		} else {
			showAlert('authAlert2', 'err', data.error || 'Неверный пароль');
			btn.disabled = false;
			btn.textContent = 'Войти →';
		}
	} catch {
		showAlert('authAlert2', 'err', 'Ошибка сети. Попробуйте ещё раз.');
		btn.disabled = false;
		btn.textContent = 'Войти →';
	}
}

/* ─── Шаг 3: финальный экран ─────────────────────────────── */
function authShowSuccess(title, sub) {
	document.getElementById('authStep3Title').textContent = title;
	document.getElementById('authStep3Sub').textContent   = sub;
	document.getElementById('btnAuthFinal').textContent   = 'Продолжить →';
	showStep(3);
}

/* ─── Действие финальной кнопки — всегда перезагрузка ───── */
function authFinalAction() {
	location.reload();
}

/* ─── Шаг 4: запрос сброса пароля ───────────────────────── */
async function submitRequestReset() {
	const email = document.getElementById('resetEmail').value.trim();

	if (!email) {
		showAlert('authAlert4', 'err', 'Введите email');
		return;
	}

	const btn = document.getElementById('btnRequestReset');
	btn.disabled = true;
	btn.textContent = 'Отправляем...';

	try {
		const res  = await fetch('/api/auth.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ action: 'request_reset', email })
		});
		const data = await res.json();

		if (data.ok) {
			document.getElementById('authStep5Sub').textContent = 'Письмо отправлено на ' + email;
			showStep(5);
		} else {
			showAlert('authAlert4', 'err', data.error || 'Ошибка');
		}
	} catch {
		showAlert('authAlert4', 'err', 'Ошибка сети. Попробуйте ещё раз.');
	} finally {
		btn.disabled = false;
		btn.textContent = 'Отправить письмо →';
	}
}

/* ─── Попап покупки ─────────────────────────────────────── */
let _buyProductId = null;

function openBuyPopup(detail) {
	_buyProductId = detail.productId;
	document.getElementById('buyTitle').textContent = detail.title || 'Получить доступ';
	document.getElementById('buyPrice').textContent = detail.price ? detail.price + ' ₽' : '';
	document.getElementById('buyAlert').style.display = 'none';
	const btn = document.getElementById('buyBtn');
	btn.disabled = false;
	btn.textContent = 'Перейти к оплате →';
	openPopup('popupBuy');
}

async function submitBuy() {
	const btn = document.getElementById('buyBtn');
	btn.disabled = true;
	btn.textContent = 'Подождите...';

	try {
		const res  = await fetch('/api/auth.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ action: 'test_purchase', product_id: _buyProductId })
		});
		const data = await res.json();

		if (data.ok) {
			showAlert('buyAlert', 'ok', 'Доступ открыт! Обновляем страницу...');
			setTimeout(() => location.reload(), 800);
		} else {
			showAlert('buyAlert', 'err', data.error || 'Ошибка');
			btn.disabled = false;
			btn.textContent = 'Перейти к оплате →';
		}
	} catch {
		showAlert('buyAlert', 'err', 'Ошибка сети. Попробуйте ещё раз.');
		btn.disabled = false;
		btn.textContent = 'Перейти к оплате →';
	}
}
</script>
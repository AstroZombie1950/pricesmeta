<?php
/* Попапы подключаются в footer.php — доступны на всех страницах */
/* $pdo и auth_check() уже доступны так как db.php и auth.php подключены раньше */
?>

<!-- ===== ПОПАП АВТОРИЗАЦИИ ===== -->
<div class="popup-overlay" id="popupAuth">
	<div class="popup">

		<button type="button" class="popup__close" onclick="closePopup('popupAuth')">✕</button>

		<!-- Вкладки -->
		<div class="popup__tabs">
			<button type="button" class="popup__tab is-active" onclick="switchPopupTab('login', this)">Войти</button>
			<button type="button" class="popup__tab"           onclick="switchPopupTab('register', this)">Зарегистрироваться</button>
		</div>

		<!-- Сообщение об ошибке / успехе -->
		<div class="popup__alert" id="authAlert" style="display:none;"></div>

		<!-- Форма входа -->
		<div class="popup__panel is-active" id="popup-panel-login">
			<div class="popup__field">
				<label for="loginEmail">Email</label>
				<input type="email" id="loginEmail" class="popup__input" placeholder="your@email.com">
			</div>
			<div class="popup__field">
				<label for="loginPassword">Пароль</label>
				<input type="password" id="loginPassword" class="popup__input" placeholder="••••••••">
			</div>
			<button type="button" class="popup__btn" onclick="submitLogin()">Войти →</button>
		</div>

		<!-- Форма регистрации -->
		<div class="popup__panel" id="popup-panel-register">
			<div class="popup__field">
				<label for="regEmail">Email</label>
				<input type="email" id="regEmail" class="popup__input" placeholder="your@email.com">
			</div>
			<div class="popup__field">
				<label for="regPassword">Пароль</label>
				<input type="password" id="regPassword" class="popup__input" placeholder="Минимум 6 символов">
			</div>
			<div class="popup__field">
				<label for="regPassword2">Повторите пароль</label>
				<input type="password" id="regPassword2" class="popup__input" placeholder="••••••••">
			</div>
			<button type="button" class="popup__btn" onclick="submitRegister()">Создать аккаунт →</button>
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

		<!-- Кнопка оплаты — пока заглушка, потом подключим эквайринг -->
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
}

.popup-overlay.is-open {
	opacity: 1;
	pointer-events: auto;
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

/* Вкладки */
.popup__tabs {
	display: flex;
	gap: 4px;
	border-bottom: 1px solid var(--color-border);
	margin-bottom: 4px;
}

.popup__tab {
	padding: 8px 16px;
	background: none;
	border: none;
	border-bottom: 2px solid transparent;
	font-family: var(--font-head);
	font-size: 14px;
	font-weight: 600;
	color: var(--color-muted);
	cursor: pointer;
	transition: color .2s, border-color .2s;
	margin-bottom: -1px;
}

.popup__tab:hover { color: var(--color-text); }

.popup__tab.is-active {
	color: var(--color-accent);
	border-bottom-color: var(--color-accent);
}

/* Панели */
.popup__panel { display: none; flex-direction: column; gap: 14px; }
.popup__panel.is-active { display: flex; }

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

/* ─── Слушаем события от кнопок товара ─────────────────── */
document.addEventListener('open-login', () => {
	clearAuthAlert();
	openPopup('popupAuth');
});

document.addEventListener('open-buy', e => {
	openBuyPopup(e.detail);
});

/* ─── Вкладки авторизации ───────────────────────────────── */
function switchPopupTab(name, btn) {
	document.querySelectorAll('.popup__tab').forEach(t => t.classList.remove('is-active'));
	document.querySelectorAll('.popup__panel').forEach(p => p.classList.remove('is-active'));
	btn.classList.add('is-active');
	document.getElementById('popup-panel-' + name).classList.add('is-active');
	clearAuthAlert();
}

/* ─── Алерты ────────────────────────────────────────────── */
function showAlert(id, type, msg) {
	const el = document.getElementById(id);
	el.className = 'popup__alert popup__alert--' + type;
	el.textContent = msg;
	el.style.display = 'block';
}

function clearAuthAlert() {
	const el = document.getElementById('authAlert');
	el.style.display = 'none';
	el.textContent = '';
}

/* ─── Вход ──────────────────────────────────────────────── */
async function submitLogin() {
	const email    = document.getElementById('loginEmail').value.trim();
	const password = document.getElementById('loginPassword').value;

	if (!email || !password) {
		showAlert('authAlert', 'err', 'Заполните все поля');
		return;
	}

	const btn = document.querySelector('#popup-panel-login .popup__btn');
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
			showAlert('authAlert', 'ok', 'Вход выполнен. Обновляем страницу...');
			setTimeout(() => location.reload(), 800);
		} else {
			showAlert('authAlert', 'err', data.error || 'Ошибка входа');
			btn.disabled = false;
			btn.textContent = 'Войти →';
		}
	} catch {
		showAlert('authAlert', 'err', 'Ошибка сети. Попробуйте ещё раз.');
		btn.disabled = false;
		btn.textContent = 'Войти →';
	}
}

/* ─── Регистрация ───────────────────────────────────────── */
async function submitRegister() {
	const email     = document.getElementById('regEmail').value.trim();
	const password  = document.getElementById('regPassword').value;
	const password2 = document.getElementById('regPassword2').value;

	if (!email || !password || !password2) {
		showAlert('authAlert', 'err', 'Заполните все поля');
		return;
	}

	if (password !== password2) {
		showAlert('authAlert', 'err', 'Пароли не совпадают');
		return;
	}

	if (password.length < 6) {
		showAlert('authAlert', 'err', 'Пароль должен быть не менее 6 символов');
		return;
	}

	const btn = document.querySelector('#popup-panel-register .popup__btn');
	btn.disabled = true;
	btn.textContent = 'Создаём аккаунт...';

	try {
		const res  = await fetch('/api/auth.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ action: 'register', email, password })
		});
		const data = await res.json();

		if (data.ok) {
			showAlert('authAlert', 'ok', 'Аккаунт создан. Обновляем страницу...');
			setTimeout(() => location.reload(), 800);
		} else {
			showAlert('authAlert', 'err', data.error || 'Ошибка регистрации');
			btn.disabled = false;
			btn.textContent = 'Создать аккаунт →';
		}
	} catch {
		showAlert('authAlert', 'err', 'Ошибка сети. Попробуйте ещё раз.');
		btn.disabled = false;
		btn.textContent = 'Создать аккаунт →';
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
	btn.textContent = 'Получить доступ →';
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
			btn.textContent = 'Получить доступ →';
		}
	} catch {
		showAlert('buyAlert', 'err', 'Ошибка сети. Попробуйте ещё раз.');
		btn.disabled = false;
		btn.textContent = 'Получить доступ →';
	}
}
</script>
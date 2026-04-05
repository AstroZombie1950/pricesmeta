<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

/* Если уже авторизован — в кабинет */
if (auth_check()) {
	header('Location: /account/');
	exit;
}

$token = trim($_GET['token'] ?? '');

/* Если токена нет — редиректим на главную */
if (!$token) {
	header('Location: /');
	exit;
}

/* Проверяем токен заранее чтобы показать ошибку до сабмита */
$stmt = $pdo->prepare("
	SELECT email FROM password_resets
	WHERE token = ?
	AND created_at > datetime('now', '-1 hour')
");
$stmt->execute([$token]);
$tokenValid = (bool) $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Новый пароль — ПрайсСмета</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
	<style>
		/* Минималистичная страница — только форма по центру */
		.reset-page {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
		}

		.reset-card {
			background: var(--color-surface);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-md);
			padding: 40px;
			width: 100%;
			max-width: 400px;
			box-shadow: 0 20px 60px rgba(0,0,0,.08);
			display: flex;
			flex-direction: column;
			gap: 20px;
		}

		.reset-card__logo {
			display: flex;
			justify-content: center;
			margin-bottom: 4px;
		}

		.reset-card__title {
			font-family: var(--font-head);
			font-size: 22px;
			font-weight: 700;
			color: var(--color-text);
		}

		.reset-card__sub {
			font-size: 14px;
			color: var(--color-muted);
			margin-top: -10px;
		}

		.reset-field {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}

		.reset-field label {
			font-family: var(--font-head);
			font-size: 13px;
			font-weight: 600;
			color: var(--color-text);
		}

		.reset-input {
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

		.reset-input:focus {
			border-color: var(--color-accent);
			box-shadow: 0 0 0 3px rgba(37,99,235,.1);
		}

		.reset-input::placeholder { color: var(--color-muted); }

		.reset-btn {
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
		}

		.reset-btn:hover { background: var(--color-accent); }
		.reset-btn:disabled { opacity: .6; cursor: not-allowed; }

		.reset-alert {
			padding: 10px 14px;
			border-radius: var(--radius-sm);
			font-size: 14px;
			font-weight: 500;
			display: none;
		}

		.reset-alert--ok  { background: rgba(22,163,74,.08); border: 1px solid rgba(22,163,74,.25); color: #15803D; }
		.reset-alert--err { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25); color: #B91C1C; }

		.reset-card__error-icon { font-size: 40px; text-align: center; }
	</style>
</head>
<body>
	<div class="reset-page">
		<div class="reset-card">

			<!-- логотип -->
			<div class="reset-card__logo">
				<a href="/"><img src="/logo.png" alt="ПрайсСмета" height="36"></a>
			</div>

			<?php if (!$tokenValid): ?>

				<!-- Токен недействителен -->
				<div class="reset-card__error-icon">⚠️</div>
				<h1 class="reset-card__title">Ссылка недействительна</h1>
				<p class="reset-card__sub">Ссылка устарела или уже была использована. Запросите сброс пароля повторно.</p>
				<a href="/" class="reset-btn" style="display:flex; align-items:center; justify-content:center; text-decoration:none;">
					На главную
				</a>

			<?php else: ?>

				<!-- Форма нового пароля -->
				<h1 class="reset-card__title">Новый пароль</h1>
				<p class="reset-card__sub">Придумайте пароль не менее 6 символов</p>

				<div class="reset-alert" id="resetAlert"></div>

				<div class="reset-field">
					<label for="newPassword">Новый пароль</label>
					<input type="password" id="newPassword" class="reset-input" placeholder="Минимум 6 символов"
						onkeydown="if(event.key==='Enter') submitNewPassword()">
				</div>

				<div class="reset-field">
					<label for="newPassword2">Повторите пароль</label>
					<input type="password" id="newPassword2" class="reset-input" placeholder="••••••••"
						onkeydown="if(event.key==='Enter') submitNewPassword()">
				</div>

				<button type="button" class="reset-btn" id="resetBtn" onclick="submitNewPassword()">
					Сохранить пароль →
				</button>

			<?php endif; ?>

		</div>
	</div>

	<script>
		async function submitNewPassword() {
			const password  = document.getElementById('newPassword').value;
			const password2 = document.getElementById('newPassword2').value;
			const alert     = document.getElementById('resetAlert');

			/* Валидация на клиенте */
			if (!password) {
				showAlert('err', 'Введите пароль');
				return;
			}

			if (password.length < 6) {
				showAlert('err', 'Пароль должен быть не менее 6 символов');
				return;
			}

			if (password !== password2) {
				showAlert('err', 'Пароли не совпадают');
				return;
			}

			const btn = document.getElementById('resetBtn');
			btn.disabled = true;
			btn.textContent = 'Сохраняем...';

			try {
				const res  = await fetch('/api/auth.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						action:   'confirm_reset',
						token:    '<?= htmlspecialchars($token) ?>',
						password: password
					})
				});
				const data = await res.json();

				if (data.ok) {
					showAlert('ok', 'Пароль сохранён. Переходим в кабинет...');
					setTimeout(() => { location.href = '/account/'; }, 1200);
				} else {
					showAlert('err', data.error || 'Ошибка');
					btn.disabled = false;
					btn.textContent = 'Сохранить пароль →';
				}
			} catch {
				showAlert('err', 'Ошибка сети. Попробуйте ещё раз.');
				btn.disabled = false;
				btn.textContent = 'Сохранить пароль →';
			}
		}

		function showAlert(type, msg) {
			const el = document.getElementById('resetAlert');
			el.className = 'reset-alert reset-alert--' + type;
			el.textContent = msg;
			el.style.display = 'block';
		}
	</script>
</body>
</html>
<?php
/* Обрабатываем форму входа */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email    = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$result   = auth_login($pdo, $email, $password);

	if ($result['ok']) {
		/* После входа проверяем роль */
		if (!auth_is_admin()) {
			auth_logout();
			$error = 'Нет доступа к панели управления';
		} else {
			header('Location: /backstage/');
			exit;
		}
	} else {
		$error = $result['error'];
	}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Вход — Панель управления</title>
	<meta name="robots" content="noindex, nofollow">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<style>
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

		/* Переменные основного сайта */
		:root {
			--color-bg:       #F7F8FA;
			--color-surface:  #FFFFFF;
			--color-border:   #E8EAF0;
			--color-text:     #1A1D23;
			--color-muted:    #6B7280;
			--color-accent:   #2563EB;
			--color-accent-h: #1D4ED8;
			--color-sale:     #EF4444;
			--font-head:      'Geologica', sans-serif;
			--font-body:      'Noto Sans', sans-serif;
			--radius-sm:      8px;
			--radius-md:      14px;
			--shadow-card:    0 2px 12px rgba(0,0,0,.07);
		}

		body {
			font-family: var(--font-body);
			background: var(--color-bg);
			color: var(--color-text);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		/* Карточка входа */
		.login {
			background: var(--color-surface);
			border: 1px solid var(--color-border);
			border-radius: var(--radius-md);
			box-shadow: var(--shadow-card);
			padding: 40px;
			width: 100%;
			max-width: 380px;
			display: flex;
			flex-direction: column;
			gap: 24px;
		}

		.login__head { display: flex; flex-direction: column; gap: 6px; }

		.login__title {
			font-family: var(--font-head);
			font-size: 22px;
			font-weight: 700;
			color: var(--color-text);
		}

		.login__sub {
			font-size: 14px;
			color: var(--color-muted);
		}

		/* Поля */
		.field { display: flex; flex-direction: column; gap: 6px; }

		label {
			font-family: var(--font-head);
			font-size: 13px;
			font-weight: 600;
			color: var(--color-text);
		}

		input {
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

		input:focus {
			border-color: var(--color-accent);
			box-shadow: 0 0 0 3px rgba(37,99,235,.12);
		}

		input::placeholder { color: var(--color-muted); }

		/* Ошибка */
		.error {
			font-size: 14px;
			color: var(--color-sale);
			background: rgba(239,68,68,.07);
			border: 1px solid rgba(239,68,68,.2);
			border-radius: var(--radius-sm);
			padding: 10px 14px;
		}

		/* Кнопка */
		.btn {
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

		.btn:hover { background: var(--color-accent); }
	</style>
</head>
<body>
	<form class="login" method="POST">

		<div class="login__head">
			<h1 class="login__title">Панель управления</h1>
			<p class="login__sub">Войдите чтобы продолжить</p>
		</div>

		<?php if ($error): ?>
			<div class="error"><?= htmlspecialchars($error) ?></div>
		<?php endif; ?>

		<div class="field">
			<label for="email">Email</label>
			<input
				type="email"
				id="email"
				name="email"
				placeholder="admin@example.com"
				value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
				required
				autofocus
			>
		</div>

		<div class="field">
			<label for="password">Пароль</label>
			<input
				type="password"
				id="password"
				name="password"
				placeholder="••••••••"
				required
			>
		</div>

		<button type="submit" class="btn">Войти →</button>

	</form>
</body>
</html>
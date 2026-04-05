<?php
/* Запускаем сессию если ещё не запущена */
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

/* ─── Регистрация ─────────────────────────────────────────────────────────── */
function auth_register(PDO $pdo, string $email, string $password): array
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return ['ok' => false, 'error' => 'Некорректный email'];
	}

	if (mb_strlen($password) < 6) {
		return ['ok' => false, 'error' => 'Пароль должен быть не менее 6 символов'];
	}

	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
	$stmt->execute([$email]);
	if ($stmt->fetch()) {
		return ['ok' => false, 'error' => 'Этот email уже зарегистрирован'];
	}

	$hash = password_hash($password, PASSWORD_DEFAULT);

	$stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
	$stmt->execute([$email, $hash]);

	auth_set_session((int) $pdo->lastInsertId(), $email, 'user');

	return ['ok' => true];
}

/* ─── Авторегистрация: генерируем пароль, шлём на почту ──────────────────── */
function auth_auto_register(PDO $pdo, string $email): array
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return ['ok' => false, 'error' => 'Некорректный email'];
	}

	/* Проверяем — вдруг за время запроса кто-то уже создал такой аккаунт */
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
	$stmt->execute([$email]);
	if ($stmt->fetch()) {
		return ['ok' => false, 'error' => 'Этот email уже зарегистрирован'];
	}

	/* Генерируем читаемый пароль: 3 слова + цифры */
	$password = auth_generate_password();
	$hash     = password_hash($password, PASSWORD_DEFAULT);

	$stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
	$stmt->execute([$email, $hash]);

	$userId = (int) $pdo->lastInsertId();

	/* Отправляем пароль на почту */
	$sent = auth_send_password_email($email, $password);

	/* Авторизуем сразу */
	auth_set_session($userId, $email, 'user');

	return ['ok' => true, 'email_sent' => $sent];
}

/* ─── Генератор пароля ────────────────────────────────────────────────────── */
function auth_generate_password(): string
{
	/* Формат: Слово + цифры, например Falcon7291 */
	$words = [
		'Falcon','Tiger','Storm','Eagle','River',
		'Stone','Silver','Forest','Delta','Laser',
		'Spark','Ocean','Scout','Brave','Prime',
	];
	$word   = $words[array_rand($words)];
	$digits = random_int(1000, 9999);
	return $word . $digits;
}

/* ─── Отправка пароля на почту ───────────────────────────────────────────── */
function auth_send_password_email(string $email, string $password): bool
{
	$subject = 'Ваш пароль — ПрайсСмета';
	$body    = "Здравствуйте!\n\n"
	         . "Вы зарегистрировались на сайте ПрайсСмета.\n\n"
	         . "Ваш пароль: {$password}\n\n"
	         . "Сохраните его — он понадобится для следующего входа.\n"
	         . "Изменить пароль можно в личном кабинете.\n\n"
	         . "— Команда ПрайсСмета\n"
	         . "https://прайслистмастера.рф";

	$headers = "From: noreply@прайслистмастера.рф\r\n"
	         . "Content-Type: text/plain; charset=UTF-8\r\n";

	return mail($email, $subject, $body, $headers);
}

/* ─── Запрос сброса пароля: генерим токен и шлём письмо ──────────────────── */
function auth_request_reset(PDO $pdo, string $email): array
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return ['ok' => false, 'error' => 'Некорректный email'];
	}

	/* Проверяем что пользователь существует — но не говорим об этом прямо */
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
	$stmt->execute([$email]);
	if (!$stmt->fetch()) {
		/* Возвращаем ok чтобы не раскрывать какие email зарегистрированы */
		return ['ok' => true];
	}

	/* Удаляем старые токены для этого email */
	$pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

	/* Генерируем безопасный токен */
	$token = bin2hex(random_bytes(32));

	$pdo->prepare('INSERT INTO password_resets (email, token) VALUES (?, ?)')
		->execute([$email, $token]);

	/* Шлём письмо */
	auth_send_reset_email($email, $token);

	return ['ok' => true];
}

/* ─── Подтверждение сброса: проверяем токен и меняем пароль ──────────────── */
function auth_confirm_reset(PDO $pdo, string $token, string $password): array
{
	if (mb_strlen($password) < 6) {
		return ['ok' => false, 'error' => 'Пароль должен быть не менее 6 символов'];
	}

	/* Ищем токен — не старше 1 часа */
	$stmt = $pdo->prepare("
		SELECT email FROM password_resets
		WHERE token = ?
		AND created_at > datetime('now', '-1 hour')
	");
	$stmt->execute([$token]);
	$row = $stmt->fetch();

	if (!$row) {
		return ['ok' => false, 'error' => 'Ссылка недействительна или устарела'];
	}

	$email = $row['email'];

	/* Обновляем пароль */
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$pdo->prepare('UPDATE users SET password = ? WHERE email = ?')
		->execute([$hash, $email]);

	/* Удаляем использованный токен */
	$pdo->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);

	/* Авторизуем пользователя */
	$stmt = $pdo->prepare('SELECT id, role FROM users WHERE email = ?');
	$stmt->execute([$email]);
	$user = $stmt->fetch();

	auth_set_session($user['id'], $email, $user['role']);

	return ['ok' => true];
}

/* ─── Письмо со ссылкой сброса ───────────────────────────────────────────── */
function auth_send_reset_email(string $email, string $token): void
{
	$link    = 'https://прайслистмастера.рф/reset-password?token=' . $token;
	$subject = 'Сброс пароля — ПрайсСмета';
	$body    = "Здравствуйте!\n\n"
	         . "Вы запросили сброс пароля на сайте ПрайсСмета.\n\n"
	         . "Перейдите по ссылке чтобы задать новый пароль:\n"
	         . $link . "\n\n"
	         . "Ссылка действует 1 час.\n"
	         . "Если вы не запрашивали сброс — просто проигнорируйте это письмо.\n\n"
	         . "— Команда ПрайсСмета";

	$headers = "From: noreply@прайслистмастера.рф\r\n"
	         . "Content-Type: text/plain; charset=UTF-8\r\n";

	mail($email, $subject, $body, $headers);
}

/* ─── Проверка существования email ───────────────────────────────────────── */
function auth_email_exists(PDO $pdo, string $email): bool
{
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
	$stmt->execute([$email]);
	return (bool) $stmt->fetch();
}

/* ─── Вход ────────────────────────────────────────────────────────────────── */
function auth_login(PDO $pdo, string $email, string $password): array
{
	$stmt = $pdo->prepare('SELECT id, email, password, role FROM users WHERE email = ?');
	$stmt->execute([$email]);
	$user = $stmt->fetch();

	if (!$user || !password_verify($password, $user['password'])) {
		return ['ok' => false, 'error' => 'Неверный email или пароль'];
	}

	auth_set_session($user['id'], $user['email'], $user['role']);

	return ['ok' => true];
}

/* ─── Выход ───────────────────────────────────────────────────────────────── */
function auth_logout(): void
{
	$_SESSION = [];
	session_destroy();
}

/* ─── Записываем данные пользователя в сессию ─────────────────────────────── */
function auth_set_session(int $id, string $email, string $role): void
{
	$_SESSION['user_id']    = $id;
	$_SESSION['user_email'] = $email;
	$_SESSION['user_role']  = $role;
}

/* ─── Проверки ────────────────────────────────────────────────────────────── */

function auth_check(): bool
{
	return !empty($_SESSION['user_id']);
}

function auth_is_admin(): bool
{
	return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function auth_user_id(): ?int
{
	return $_SESSION['user_id'] ?? null;
}

function auth_user_email(): ?string
{
	return $_SESSION['user_email'] ?? null;
}

function auth_require(): void
{
	if (!auth_check()) {
		header('Location: /?login=1');
		exit;
	}
}

function auth_require_admin(): void
{
	if (!auth_is_admin()) {
		header('Location: /');
		exit;
	}
}

/* ─── Проверка покупки ────────────────────────────────────────────────────── */
function auth_has_access(PDO $pdo, int $productId): bool
{
	if (!auth_check()) return false;

	$stmt = $pdo->prepare(
		'SELECT id FROM purchases WHERE user_id = ? AND product_id = ?'
	);
	$stmt->execute([auth_user_id(), $productId]);

	return (bool) $stmt->fetch();
}
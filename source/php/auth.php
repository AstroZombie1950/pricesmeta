<?php
/* Запускаем сессию если ещё не запущена */
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

/* ─── Регистрация ─────────────────────────────────────────────────────────── */
function auth_register(PDO $pdo, string $email, string $password): array
{
	/* Проверяем формат email */
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return ['ok' => false, 'error' => 'Некорректный email'];
	}

	/* Минимальная длина пароля */
	if (mb_strlen($password) < 6) {
		return ['ok' => false, 'error' => 'Пароль должен быть не менее 6 символов'];
	}

	/* Проверяем что такой email ещё не занят */
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
	$stmt->execute([$email]);
	if ($stmt->fetch()) {
		return ['ok' => false, 'error' => 'Этот email уже зарегистрирован'];
	}

	/* Хешируем пароль — никогда не храним пароль в открытом виде */
	$hash = password_hash($password, PASSWORD_DEFAULT);

	/* Записываем пользователя в БД */
	$stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
	$stmt->execute([$email, $hash]);

	$userId = (int) $pdo->lastInsertId();

	/* Сразу авторизуем после регистрации */
	auth_set_session($userId, $email, 'user');

	return ['ok' => true];
}

/* ─── Вход ────────────────────────────────────────────────────────────────── */
function auth_login(PDO $pdo, string $email, string $password): array
{
	/* Ищем пользователя по email */
	$stmt = $pdo->prepare('SELECT id, email, password, role FROM users WHERE email = ?');
	$stmt->execute([$email]);
	$user = $stmt->fetch();

	/* Проверяем пароль — password_verify сравнивает с хешем из БД */
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

/* Авторизован ли пользователь */
function auth_check(): bool
{
	return !empty($_SESSION['user_id']);
}

/* Является ли пользователь администратором */
function auth_is_admin(): bool
{
	return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/* Текущий ID пользователя или null */
function auth_user_id(): ?int
{
	return $_SESSION['user_id'] ?? null;
}

/* Текущий email пользователя или null */
function auth_user_email(): ?string
{
	return $_SESSION['user_email'] ?? null;
}

/* Редирект если не авторизован */
function auth_require(): void
{
	if (!auth_check()) {
		header('Location: /?login=1');
		exit;
	}
}

/* Редирект если не администратор */
function auth_require_admin(): void
{
	if (!auth_is_admin()) {
		header('Location: /');
		exit;
	}
}

/* ─── Проверка покупки ────────────────────────────────────────────────────── */

/* Куплен ли товар у текущего пользователя */
function auth_has_access(PDO $pdo, int $productId): bool
{
	if (!auth_check()) return false;

	$stmt = $pdo->prepare(
		'SELECT id FROM purchases WHERE user_id = ? AND product_id = ?'
	);
	$stmt->execute([auth_user_id(), $productId]);

	return (bool) $stmt->fetch();
}
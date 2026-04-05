<?php
/* API для авторизации, регистрации и покупок */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
	exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
	echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
	exit;
}

$action = $body['action'] ?? '';

/* Проверяем существует ли email */
if ($action === 'check_email') {
	$email = trim($body['email'] ?? '');
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo json_encode(['ok' => false, 'error' => 'Некорректный email']);
		exit;
	}
	echo json_encode([
		'ok'     => true,
		'exists' => auth_email_exists($pdo, $email),
	]);
	exit;
}

/* Авторегистрация — создаём аккаунт и шлём пароль на почту */
if ($action === 'auto_register') {
	$result = auth_auto_register($pdo, trim($body['email'] ?? ''));
	echo json_encode($result);
	exit;
}

/* Проверяем есть ли у текущего пользователя доступ к товару */
if ($action === 'check_access') {
	$productId = (int)($body['product_id'] ?? 0);
	echo json_encode([
		'ok'         => true,
		'has_access' => auth_has_access($pdo, $productId),
	]);
	exit;
}

/* Запрос сброса пароля — шлём письмо с токеном */
if ($action === 'request_reset') {
	$result = auth_request_reset($pdo, trim($body['email'] ?? ''));
	echo json_encode($result);
	exit;
}

/* Подтверждение сброса — меняем пароль по токену */
if ($action === 'confirm_reset') {
	$result = auth_confirm_reset($pdo, trim($body['token'] ?? ''), $body['password'] ?? '');
	echo json_encode($result);
	exit;
}

/* Вход */
if ($action === 'login') {
	$result = auth_login($pdo, trim($body['email'] ?? ''), $body['password'] ?? '');
	echo json_encode($result);
	exit;
}

/* Регистрация */
if ($action === 'register') {
	$result = auth_register($pdo, trim($body['email'] ?? ''), $body['password'] ?? '');
	echo json_encode($result);
	exit;
}

/* Выход */
if ($action === 'logout') {
	auth_logout();
	echo json_encode(['ok' => true]);
	exit;
}

/* Тестовая покупка — добавляет доступ без оплаты */
if ($action === 'test_purchase') {
	if (!auth_check()) {
		echo json_encode(['ok' => false, 'error' => 'Необходима авторизация']);
		exit;
	}

	$productId = (int)($body['product_id'] ?? 0);
	if (!$productId) {
		echo json_encode(['ok' => false, 'error' => 'Не указан товар']);
		exit;
	}

	/* Проверяем что товар существует */
	$stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_published = 1');
	$stmt->execute([$productId]);
	if (!$stmt->fetch()) {
		echo json_encode(['ok' => false, 'error' => 'Товар не найден']);
		exit;
	}

	/* Проверяем что доступ ещё не куплен */
	if (auth_has_access($pdo, $productId)) {
		echo json_encode(['ok' => true, 'already' => true]);
		exit;
	}

	/* Добавляем покупку */
	$pdo->prepare('INSERT INTO purchases (user_id, product_id) VALUES (?, ?)')
		->execute([auth_user_id(), $productId]);

	echo json_encode(['ok' => true]);
	exit;
}

echo json_encode(['ok' => false, 'error' => 'Unknown action']);
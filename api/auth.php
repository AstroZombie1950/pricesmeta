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
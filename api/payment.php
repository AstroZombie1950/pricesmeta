<?php
/* Генерирует данные для POST-формы на Prodamus */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/Hmac.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
	exit;
}

if (!auth_check()) {
	echo json_encode(['ok' => false, 'error' => 'Необходима авторизация']);
	exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
	echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
	exit;
}

$productId = (int)($body['product_id'] ?? 0);
if (!$productId) {
	echo json_encode(['ok' => false, 'error' => 'Не указан товар']);
	exit;
}

$stmt = $pdo->prepare('SELECT id, title, price FROM products WHERE id = ? AND is_published = 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
	echo json_encode(['ok' => false, 'error' => 'Товар не найден']);
	exit;
}

if (auth_has_access($pdo, $productId)) {
	echo json_encode(['ok' => true, 'already' => true]);
	exit;
}

$orderId   = auth_user_id() . '_' . $productId . '_' . time();
$userEmail = auth_user_email();

/* Вложенный массив — по нему считается подпись */
$data = [
	'order_id'          => $orderId,
	'customer_email'    => $userEmail,
	'do'                => 'pay',
	'products'          => [
		[
			'sku'      => (string) $productId,
			'name'     => $product['title'],
			'price'    => (string) $product['price'],
			'quantity' => '1',
			'type'     => 'goods',
		],
	],
	'_param_user_id'    => (string) auth_user_id(),
	'_param_product_id' => (string) $productId,
	'urlSuccess'        => SITE_URL . '/thank-you/',
	'urlReturn'         => SITE_URL . '/shop/',
];

/* Считаем подпись */
$signature = Hmac::create($data, PRODAMUS_KEY);

/* Разворачиваем для POST-формы */
$fields = [
	'order_id'              => $data['order_id'],
	'customer_email'        => $userEmail,
	/* Prodamus также принимает просто email */
	'email'                 => $userEmail,
	'do'                    => $data['do'],
	'products[0][sku]'      => $data['products'][0]['sku'],
	'products[0][name]'     => $data['products'][0]['name'],
	'products[0][price]'    => $data['products'][0]['price'],
	'products[0][quantity]' => $data['products'][0]['quantity'],
	'products[0][type]'     => $data['products'][0]['type'],
	'_param_user_id'        => $data['_param_user_id'],
	'_param_product_id'     => $data['_param_product_id'],
	'urlSuccess'            => $data['urlSuccess'],
	'urlReturn'             => $data['urlReturn'],
	'signature'             => $signature,
];

echo json_encode([
	'ok'     => true,
	'action' => PRODAMUS_URL,
	'fields' => $fields,
]);
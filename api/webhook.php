<?php
/* Принимает POST-вебхук от Prodamus, верифицирует подпись, выдаёт доступ и шлёт письмо */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/Hmac.php';

/* ─── Отправка письма покупателю ──────────────────────────────────────────── */
function send_purchase_email(PDO $pdo, int $userId, int $productId): void
{
	/* Получаем данные пользователя */
	$stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
	$stmt->execute([$userId]);
	$user = $stmt->fetch();
	if (!$user) return;

	/* Получаем данные товара */
	$stmt = $pdo->prepare('SELECT title, slug, files FROM products WHERE id = ?');
	$stmt->execute([$productId]);
	$product = $stmt->fetch();
	if (!$product) return;

	$toEmail     = $user['email'];
	$productName = $product['title'];
	$slug        = $product['slug'];
	$files       = json_decode($product['files'] ?? '[]', true) ?: [];
	$filesDir    = $_SERVER['DOCUMENT_ROOT'] . '/files/products/' . $slug . '/';
	$siteUrl     = 'https://прайслистмастера.рф';
	$fromEmail   = 'noreply@xn--80aaatnfojwkhgccmd.xn--p1ai';

	/* Граница для multipart */
	$boundary = md5(uniqid(time()));

	/* Заголовки */
	$headers  = 'From: ПрайсСмета <' . $fromEmail . '>' . "\r\n";
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . "\r\n";

	/* Тема */
	$subject = '=?UTF-8?B?' . base64_encode('Ваша покупка: ' . $productName) . '?=';

	/* Текстовая часть письма */
	$text  = "Здравствуйте!\n\n";
	$text .= "Спасибо за покупку «{$productName}».\n\n";
	$text .= "Файлы приложены к этому письму.\n";
	$text .= "Также они всегда доступны в вашем личном кабинете:\n";
	$text .= "{$siteUrl}/account/\n\n";
	$text .= "— Команда ПрайсСмета\n";
	$text .= $siteUrl;

	/* Тело письма */
	$body  = '--' . $boundary . "\r\n";
	$body .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
	$body .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
	$body .= chunk_split(base64_encode($text)) . "\r\n";

	/* Прикладываем файлы */
	foreach ($files as $filename) {
		$filepath = $filesDir . $filename;
		if (!file_exists($filepath)) continue;

		$fileData = file_get_contents($filepath);
		$mimeType = 'application/octet-stream';

		/* Определяем MIME по расширению */
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$mimeMap = [
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xls'  => 'application/vnd.ms-excel',
			'pdf'  => 'application/pdf',
			'zip'  => 'application/zip',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		];
		if (isset($mimeMap[$ext])) $mimeType = $mimeMap[$ext];

		/* Кодируем имя файла для заголовка */
		$encodedName = '=?UTF-8?B?' . base64_encode($filename) . '?=';

		$body .= '--' . $boundary . "\r\n";
		$body .= 'Content-Type: ' . $mimeType . '; name="' . $encodedName . '"' . "\r\n";
		$body .= 'Content-Transfer-Encoding: base64' . "\r\n";
		$body .= 'Content-Disposition: attachment; filename="' . $encodedName . '"' . "\r\n\r\n";
		$body .= chunk_split(base64_encode($fileData)) . "\r\n";
	}

	$body .= '--' . $boundary . '--';

	mail($toEmail, $subject, $body, $headers);
}

/* ─── Обработка вебхука ───────────────────────────────────────────────────── */
try {
	if (empty($_POST)) {
		throw new Exception('$_POST is empty', 400);
	}

	$headers = apache_request_headers();

	if (empty($headers['Sign'])) {
		throw new Exception('Signature not found', 400);
	}

	if (!Hmac::verify($_POST, PRODAMUS_KEY, $headers['Sign'])) {
		throw new Exception('Signature incorrect', 403);
	}

	/* Извлекаем сквозные параметры */
	$userId    = (int)($_POST['_param_user_id']    ?? 0);
	$productId = (int)($_POST['_param_product_id'] ?? 0);

	if (!$userId || !$productId) {
		throw new Exception('Missing user_id or product_id params', 400);
	}

	/* Проверяем товар */
	$stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_published = 1');
	$stmt->execute([$productId]);
	if (!$stmt->fetch()) {
		throw new Exception('Product not found', 400);
	}

	/* Проверяем пользователя */
	$stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
	$stmt->execute([$userId]);
	if (!$stmt->fetch()) {
		throw new Exception('User not found', 400);
	}

	/* Идемпотентность — не дублируем покупку */
	$stmt = $pdo->prepare('SELECT id FROM purchases WHERE user_id = ? AND product_id = ?');
	$stmt->execute([$userId, $productId]);
	if ($stmt->fetch()) {
		http_response_code(200);
		echo 'already purchased';
		exit;
	}

	/* Записываем покупку */
	$pdo->prepare('INSERT INTO purchases (user_id, product_id) VALUES (?, ?)')
		->execute([$userId, $productId]);

	/* Шлём письмо покупателю */
	send_purchase_email($pdo, $userId, $productId);

	http_response_code(200);
	echo 'success';

} catch (Exception $e) {
	http_response_code($e->getCode() ?: 400);
	echo 'error: ' . $e->getMessage();
}
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

$slug = $_GET['slug'] ?? '';

/* Ищем товар */
$stmt = $pdo->prepare('SELECT * FROM products WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
	http_response_code(404);
	include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
	exit;
}

/* Проверяем авторизацию */
if (!auth_check()) {
	header('Location: /shop/' . urlencode($slug) . '?login=1');
	exit;
}

/* Проверяем покупку */
if (!auth_has_access($pdo, $product['id'])) {
	header('Location: /shop/' . urlencode($slug) . '?buy=1');
	exit;
}

/* Получаем список файлов */
$files = json_decode($product['files'] ?? '[]', true) ?: [];

if (empty($files)) {
	header('Location: /shop/' . urlencode($slug));
	exit;
}

$filesDir = $_SERVER['DOCUMENT_ROOT'] . '/files/products/' . $slug . '/';

/* Один файл — отдаём напрямую */
if (count($files) === 1) {
	$filename = basename($files[0]); /* защита от path traversal */
	$filepath = $filesDir . $filename;

	if (!file_exists($filepath)) {
		http_response_code(404);
		include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
		exit;
	}

	$mime = mime_content_type($filepath) ?: 'application/octet-stream';

	header('Content-Type: ' . $mime);
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Length: ' . filesize($filepath));
	header('Cache-Control: no-cache, no-store');
	readfile($filepath);
	exit;
}

/* Несколько файлов — упаковываем в zip */
$zipName = $slug . '.zip';
$zipPath = sys_get_temp_dir() . '/' . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
	http_response_code(500);
	echo 'Не удалось создать архив';
	exit;
}

foreach ($files as $fname) {
	$fname    = basename($fname);
	$filepath = $filesDir . $fname;
	if (file_exists($filepath)) {
		$zip->addFile($filepath, $fname);
	}
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
header('Cache-Control: no-cache, no-store');
readfile($zipPath);

/* Удаляем временный архив */
unlink($zipPath);
exit;
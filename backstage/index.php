<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

/* Если не авторизован — на страницу входа */
if (!auth_check()) {
	include __DIR__ . '/login.php';
	exit;
}

/* Только для админов */
if (!auth_is_admin()) {
	header('Location: /');
	exit;
}

/* Роутинг по параметру ?route= */
$route = $_GET['route'] ?? 'dashboard';

$pages = [
	'dashboard'       => '/backstage/pages/dashboard.php',
	'articles'        => '/backstage/pages/articles.php',
	'articles/create' => '/backstage/pages/article_edit.php',
	'articles/edit'   => '/backstage/pages/article_edit.php',
	'products'        => '/backstage/pages/products.php',
	'products/create' => '/backstage/pages/product_edit.php',
	'products/edit'   => '/backstage/pages/product_edit.php',
	'users'           => '/backstage/pages/users.php',
];

$file = isset($pages[$route])
	? $_SERVER['DOCUMENT_ROOT'] . $pages[$route]
	: null;

if (!$file || !file_exists($file)) {
	http_response_code(404);
	echo 'Страница не найдена';
	exit;
}

include $file;
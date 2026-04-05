<?php
/* Путь к файлу базы данных — лежит вне папки сайта */
define('DB_PATH', __DIR__ . '/../../data/database.db');

/* Создаём папку /data если её нет */
if (!is_dir(dirname(DB_PATH))) {
	mkdir(dirname(DB_PATH), 0755, true);
}

/* Подключаемся к SQLite. PDO — стандартный способ работы с БД в PHP.
   Если файла базы нет — SQLite создаст его автоматически. */
try {
	$pdo = new PDO('sqlite:' . DB_PATH);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	/* Включаем WAL-режим — ускоряет запись, безопаснее при параллельных запросах */
	$pdo->exec('PRAGMA journal_mode=WAL');
} catch (Exception $e) {
	die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

/* Создаём таблицы если их ещё нет (IF NOT EXISTS — безопасно запускать каждый раз) */
$pdo->exec("
	/* Пользователи сайта */
	CREATE TABLE IF NOT EXISTS users (
		id        INTEGER PRIMARY KEY AUTOINCREMENT,
		email     TEXT    NOT NULL UNIQUE,
		password  TEXT    NOT NULL,
		role      TEXT    NOT NULL DEFAULT 'user', /* user | admin */
		created_at TEXT   NOT NULL DEFAULT (datetime('now'))
	);

	/* Товары магазина */
	CREATE TABLE IF NOT EXISTS products (
		id            INTEGER PRIMARY KEY AUTOINCREMENT,
		slug          TEXT    NOT NULL UNIQUE,   /* URL: /shop/elektrika */
		title         TEXT    NOT NULL,
		description   TEXT,                      /* HTML-контент описания */
		price         INTEGER NOT NULL DEFAULT 0,
		old_price     INTEGER,
		image         TEXT,                      /* путь к картинке */
		files         TEXT,                      /* JSON-массив файлов для скачивания */
		seo_title     TEXT,
		meta_desc     TEXT,
		meta_keywords TEXT,
		is_published  INTEGER NOT NULL DEFAULT 0, /* 0 = черновик, 1 = опубликован */
		created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
	);

	/* Статьи блога */
	CREATE TABLE IF NOT EXISTS articles (
		id            INTEGER PRIMARY KEY AUTOINCREMENT,
		slug          TEXT    NOT NULL UNIQUE,   /* URL: /articles/kak-sdelat-smetu */
		title         TEXT    NOT NULL,
		excerpt       TEXT,                      /* короткое описание для превью */
		content       TEXT,                      /* HTML-контент статьи */
		image         TEXT,                      /* путь к обложке */
		seo_title     TEXT,
		meta_desc     TEXT,
		meta_keywords TEXT,
		is_published  INTEGER NOT NULL DEFAULT 0,
		created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
	);

	/* Токены сброса пароля — живут 1 час */
	CREATE TABLE IF NOT EXISTS password_resets (
		id         INTEGER PRIMARY KEY AUTOINCREMENT,
		email      TEXT NOT NULL,
		token      TEXT NOT NULL UNIQUE,
		created_at TEXT NOT NULL DEFAULT (datetime('now'))
	);

	/* Покупки — связка пользователь → товар */
	CREATE TABLE IF NOT EXISTS purchases (
		id         INTEGER PRIMARY KEY AUTOINCREMENT,
		user_id    INTEGER NOT NULL REFERENCES users(id),
		product_id INTEGER NOT NULL REFERENCES products(id),
		created_at TEXT    NOT NULL DEFAULT (datetime('now'))
	);
");
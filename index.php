<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/auth.php';

/* Товары — последние 4 опубликованных */
$products = $pdo->query('SELECT * FROM products WHERE is_published = 1 ORDER BY created_at DESC LIMIT 4')->fetchAll();

/* Статьи — последние 6 опубликованных */
$articles = $pdo->query('SELECT * FROM articles WHERE is_published = 1 ORDER BY created_at DESC LIMIT 6')->fetchAll();

/* Русские месяцы */
$months = [
	1=>'января',2=>'февраля',3=>'марта',4=>'апреля',
	5=>'мая',6=>'июня',7=>'июля',8=>'августа',
	9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Автоматические сметы в Excel для ремонта и строительства | ПрайсСмета</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="description" content="ПрайсСмета — автоматические сметы и прайс-листы в Excel для мастеров, бригад и строительных компаний. Расчёт электрики, сантехники, отопления, плитки и отделки в удобных таблицах.">
	<meta name="keywords" content="автоматические сметы в excel, сметы для ремонта и строительства, прайс-листы для мастеров, сметы для бригад, расчет электрики сантехники плитки">
	<meta name="author" content="ПрайсСмета">
	<!-- OG -->
	<meta property="og:title" content="ПрайсСмета — прайс-листы для мастеров">
	<meta property="og:description" content="Автоматизированные сметы в Excel для электриков, сантехников и плиточников.">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://прайслистмастера.рф/">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">



</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'; ?>
	
	<!-- ===== HERO ===== -->
	<section class="hero">
		<div class="container">
			<div class="hero__card">
				<img src="/source/img/hero.jpg" alt="" class="hero__bg" aria-hidden="true">
				<div class="hero__overlay"></div>
				<div class="hero__content">
					<h1 class="hero__title">Автоматические сметы и прайс-листы в Excel для ремонта и строительства 2026</h1>
					<p class="hero__sub">Автоматические файлы для расчёта электромонтажных, сантехнических, отопительных, плиточных и отделочных работ</p>
					<a href="/shop" class="hero__btn">Смотреть сметы</a>
				</div>
			</div>
		</div>
	</section>

	<!-- ===== ABOUT ===== -->
	<section class="about">
		<div class="container about__inner">
			<div class="about__text-col">
				<p class="about__text">ПрайсСмета — это автоматические сметы и прайс-листы в формате Excel для мастеров, бригад и небольших строительных компаний.
				<br><br>
				В таблицах уже настроены позиции, категории и формулы, поэтому автоматический расчет смет занимает минимум времени. Вы быстро считаете стоимость работ, подготавливаете коммерческое предложение и получаете понятный расчёт по объекту без ручного пересчёта каждой позиции.
				<br><br>
				Файлы подходят для расчёта электромонтажных работ, сантехники и отопления, плитки, а также черновой и чистовой отделки. Это удобная автоматическая смета в Excel для повседневной работы на объекте, в офисе и при согласовании цены с заказчиком.
				<br><br>
				Каждый файл легко редактируется, сохраняется в PDF и подходит как для компьютера, так и для работы через онлайн-таблицы. Если нужна онлайн смета без сложной настройки, достаточно открыть файл, подставить свои объёмы и расценки, а затем сразу получить итоговую стоимость работ.
				</p>
			</div>
			<ul class="about__list">
				<li class="about__item">Автоматические сметы и прайс-листы по основным видам работ</li>
				<li class="about__item">Подробные позиции для расчёта ремонта и строительства</li>
				<li class="about__item">Автоматический расчёт итоговой стоимости по объекту</li>
				<li class="about__item">Удобно для частных мастеров, бригад и компаний</li>
				<li class="about__item">Экономия времени на подготовку смет и коммерческих предложений</li>
			</ul>
		</div>
	</section>

	<!-- ===== PRODUCTS ===== -->
	<section class="products">
		<div class="container">

			<div class="products__head">
				<h2 class="products__title">Автоматические сметы и прайс-листы для мастеров и строительных компаний</h2>
				<p class="products__sub">Отличные товары по выгодной цене</p>
			</div>

			<div class="products__list">
				<?php if ($products): ?>
					<?php foreach ($products as $p): ?>
					<article class="product-card">
						<a href="/shop/<?= htmlspecialchars($p['slug']) ?>" class="product-card__img-wrap">
							<img
								src="<?= htmlspecialchars($p['image'] ?: 'https://placehold.co/280x200/e8eaf0/6b7280?text=Товар') ?>"
								alt="<?= htmlspecialchars($p['title']) ?>"
								class="product-card__img"
							>
						</a>
						<div class="product-card__body">
							<h3 class="product-card__name"><?= htmlspecialchars($p['title']) ?></h3>
							<div class="product-card__price-row">
								<span class="product-card__price"><?= number_format($p['price'], 0, '', ' ') ?> ₽</span>
								<?php if ($p['old_price']): ?>
									<span class="product-card__old-price"><?= number_format($p['old_price'], 0, '', ' ') ?> ₽</span>
								<?php endif; ?>
							</div>
							<a href="/shop/<?= htmlspecialchars($p['slug']) ?>" class="product-card__btn">Получить доступ</a>
						</div>
					</article>
					<?php endforeach; ?>
				<?php endif; ?>

				<div class="products__footer">
					<a href="/shop" class="products__all-btn">Все товары →</a>
				</div>
			</div>

		</div>
	</section>

	<!-- ===== SUPPORT ===== -->
	<section class="support">
		<div class="container support__inner">
			<div class="support__content">
				<h2 class="support__title">Поддержка всегда рядом!</h2>
				<p class="support__text">Пишите нам, мы всегда готовы вас проконсультировать или помочь</p>
				<a href="https://t.me/montag_system" target="_blank" rel="noopener" class="support__tg">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="12" cy="12" r="12" fill="#29B6F6"/>
						<path d="M17.5 7L5 11.5l4 1.5 1.5 4.5 2-2.5 3.5 2.5 1.5-10z" fill="white"/>
					</svg>
					Telegram
				</a>
			</div>
			<div class="support__img-wrap">
				<svg width="120" height="110" viewBox="0 0 120 110" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="8" y="8" width="80" height="56" rx="14" fill="#E3F2FD"/>
					<rect x="8" y="8" width="80" height="56" rx="14" stroke="#90CAF9" stroke-width="1.5"/>
					<circle cx="30" cy="36" r="5" fill="#29B6F6"/>
					<circle cx="48" cy="36" r="5" fill="#29B6F6" opacity=".6"/>
					<circle cx="66" cy="36" r="5" fill="#29B6F6" opacity=".3"/>
					<path d="M20 64 L14 76 L34 68" fill="#E3F2FD" stroke="#90CAF9" stroke-width="1.5" stroke-linejoin="round"/>
					<rect x="62" y="48" width="50" height="38" rx="10" fill="#FFF3E0"/>
					<rect x="62" y="48" width="50" height="38" rx="10" stroke="#FFCC80" stroke-width="1.5"/>
					<rect x="72" y="60" width="30" height="4" rx="2" fill="#FFB74D"/>
					<rect x="72" y="70" width="20" height="4" rx="2" fill="#FFB74D" opacity=".5"/>
					<circle cx="108" cy="50" r="7" fill="#EF5350"/>
					<text x="108" y="54" text-anchor="middle" fill="white" font-size="9" font-weight="700">1</text>
				</svg>
			</div>
		</div>
	</section>

	<!-- ===== ARTICLES ===== -->
	<section class="articles">
		<div class="container">

			<div class="articles__head">
				<h2 class="articles__title">Статьи</h2>
				<p class="articles__sub">Пишем хорошие статьи</p>
			</div>

			<div class="articles__slider-wrap">
				<button class="articles__arrow articles__arrow--prev" aria-label="Назад">&#8592;</button>
				<button class="articles__arrow articles__arrow--next" aria-label="Вперёд">&#8594;</button>

				<div class="articles__track" id="articlesTrack">
					<?php if ($articles): ?>
						<?php foreach ($articles as $a):
							$ts   = strtotime($a['created_at']);
							$date = date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
						?>
						<article class="article-card">
							<a href="/articles/<?= htmlspecialchars($a['slug']) ?>" class="article-card__img-wrap">
								<img
									src="<?= htmlspecialchars($a['image'] ?: 'https://placehold.co/400x200/e8eaf0/6b7280?text=Статья') ?>"
									alt="<?= htmlspecialchars($a['title']) ?>"
									class="article-card__img"
								>
							</a>
							<div class="article-card__body">
								<span class="article-card__meta">Статьи / <?= $date ?></span>
								<h3 class="article-card__title"><?= htmlspecialchars($a['title']) ?></h3>
							</div>
						</article>
						<?php endforeach; ?>
					<?php else: ?>
						<p style="color:var(--color-muted); font-size:14px; padding:8px 0;">Статьи скоро появятся.</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="articles__footer">
				<a href="/articles" class="articles__all-btn">Все публикации →</a>
			</div>

		</div>
	</section>

	<!-- ===== SEO TEXT ===== -->
	<section class="seotext">
		<div class="container">
			<h2 class="seotext__title">Готовые сметы и прайс-листы для мастеров и строительных компаний</h2>
			<div class="seotext__body">
				<p class="seotext__p">Составлять смету вручную долго, особенно когда нужно быстро назвать цену клиенту или подготовить расчёт по объекту. ПрайсСмета помогает решить эту задачу проще: вы получаете готовые файлы с формулами, категориями и уже собранной структурой работ.</p>
				<div class="seotext__more" id="seotextMore">
					<p class="seotext__p">Это удобный вариант для тех, кому нужна автоматическая смета для ремонта квартир, частных домов и небольших коммерческих помещений. Файлы подходят мастерам, которые хотят быстрее считать объёмы, не тратить время на ручной пересчёт и держать все расценки в одном месте.<br><br>Если вам нужна смета работ онлайн, файл можно открыть в Excel или в совместимом онлайн-сервисе, внести свои данные и сразу получить итоговую стоимость. Такой формат особенно удобен для электрики, сантехники, отопления, плитки и отделочных работ.<br><br>Для разных направлений вы можете выбрать отдельные решения в каталог.</p>
				</div>
			</div>
			<button class="seotext__toggle" id="seotextToggle">
				Читать полностью <span class="seotext__toggle-icon">∨</span>
			</button>
		</div>
	</section>

	<!-- ===== VIDEO ===== -->
	<section class="video-section">
		<div class="container">
			<h2 class="video-section__title">Работа со сметой</h2>
			<div class="video-section__wrap">
				<iframe
					class="video-section__iframe"
					src="https://rutube.ru/play/embed/a827ccd7b35fae7a6e861bf33b23109d"
					frameborder="0"
					allow="clipboard-write; autoplay"
					webkitAllowFullScreen
					mozallowfullscreen
					allowfullscreen
				></iframe>
			</div>
		</div>
	</section>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
	<script src="/source/js/main_page.js"></script>
</body>
</html>
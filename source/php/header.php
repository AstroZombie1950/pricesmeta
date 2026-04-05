<?php
/* Запускаем сессию если ещё не запущена */
if (session_status() === PHP_SESSION_NONE) session_start();
?>
	<!-- ===== HEADER ===== -->
	<header class="header">
		<div class="container header__inner">
			<a href="/" class="header__logo">
				<img src="/logo.png" alt="ПрайсСмета" width="auto" height="40">
			</a>

			<!-- десктоп-навигация -->
			<nav class="header__nav">
				<a href="/shop" class="header__nav-btn">
					<span class="header__nav-btn-icon">☰</span>
					Сметы
				</a>

				<a href="/" class="header__nav-link">Главная</a>

				<a href="/faq" class="header__nav-link">
					<span class="header__nav-link-full">Вопросы и ответы</span>
					<span class="header__nav-link-short">FAQ</span>
				</a>

				<a href="/articles" class="header__nav-link">Статьи</a>

				<!-- дропдаун "Покупателям" -->
				<div class="header__dropdown">
					<button type="button" class="header__nav-link header__dropdown-toggle">
						Покупателям
						<svg class="header__dropdown-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none">
							<path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<div class="header__dropdown-menu">
						<a href="/payment_delivery" class="header__dropdown-link">Оплата и доставка</a>
						<a href="/about" class="header__dropdown-link">О продавце</a>
					</div>
				</div>

				<!-- кабинет / войти -->
				<?php if (!empty($_SESSION['user_id'])): ?>
					<a href="/account/" class="header__nav-link header__nav-account">
						<svg class="header__nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="12" cy="8" r="4"/>
							<path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
							<polyline points="9 12 11 14 15 10" stroke-width="2"/>
						</svg>
						<span class="header__nav-label">Кабинет</span>
					</a>
				<?php else: ?>
					<button type="button" class="header__nav-link header__nav-account" style="background:none; border:none; cursor:pointer; font-family:inherit; font-weight:500;" onclick="document.dispatchEvent(new CustomEvent('open-login'))">
						<svg class="header__nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="10" cy="8" r="4"/>
							<path d="M4 20c0-4 3.2-7 7-7"/>
							<polyline points="17 14 21 14"/>
							<polyline points="19 12 21 14 19 16"/>
						</svg>
						<span class="header__nav-label">Войти</span>
					</button>
				<?php endif; ?>
			</nav>

			<!-- бургер-кнопка (только мобиле) -->
			<button type="button" class="header__burger" aria-label="Меню" aria-expanded="false">
				<span></span>
				<span></span>
				<span></span>
			</button>
		</div>

		<!-- мобильное меню -->
		<div class="header__mobile-menu">
			<nav class="header__mobile-nav">
				<a href="/shop" class="header__mobile-link header__mobile-link--accent">☰ Сметы</a>
				<a href="/" class="header__mobile-link">Главная</a>
				<a href="/faq" class="header__mobile-link">Вопросы и ответы</a>
				<a href="/articles" class="header__mobile-link">Статьи</a>

				<!-- дропдаун "Покупателям" в мобиле -->
				<div class="header__mobile-dropdown">
					<button type="button" class="header__mobile-link header__mobile-dropdown-toggle">
						Покупателям
						<svg class="header__dropdown-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none">
							<path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<div class="header__mobile-dropdown-menu">
						<a href="/payment_delivery" class="header__mobile-sublink">Оплата и доставка</a>
						<a href="/about" class="header__mobile-sublink">О продавце</a>
					</div>
				</div>

				<!-- кабинет / войти -->
				<?php if (!empty($_SESSION['user_id'])): ?>
					<a href="/account/" class="header__mobile-link">Кабинет</a>
				<?php else: ?>
					<button type="button" class="header__mobile-link header__mobile-link--login" onclick="document.dispatchEvent(new CustomEvent('open-login')); document.querySelector('.header').classList.remove('menu-open');">Войти</button>
				<?php endif; ?>
			</nav>
		</div>
	</header>

	<script>
		/* дропдаун десктоп */
		document.querySelectorAll('.header__dropdown-toggle').forEach(btn => {
			btn.addEventListener('click', e => {
				e.stopPropagation();
				btn.closest('.header__dropdown').classList.toggle('is-open');
			});
		});

		document.addEventListener('click', () => {
			document.querySelectorAll('.header__dropdown.is-open')
				.forEach(el => el.classList.remove('is-open'));
		});

		/* бургер */
		const burger = document.querySelector('.header__burger');
		const header = document.querySelector('.header');

		burger.addEventListener('click', () => {
			const isOpen = header.classList.toggle('menu-open');
			burger.setAttribute('aria-expanded', isOpen);
		});

		/* дропдаун в мобильном меню */
		document.querySelectorAll('.header__mobile-dropdown-toggle').forEach(btn => {
			btn.addEventListener('click', () => {
				btn.closest('.header__mobile-dropdown').classList.toggle('is-open');
			});
		});
	</script>
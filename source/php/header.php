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
			<nav class="header__nav">
				<!-- кнопка Сметы -->
				<a href="/shop" class="header__nav-btn">
					<span class="header__nav-btn-icon">☰</span>
					Сметы
				</a>

				<a href="/" class="header__nav-link">Главная</a>

				<!-- на десктопе "Вопросы и ответы", на мобиле "FAQ" -->
				<a href="/faq" class="header__nav-link">
					<span class="header__nav-link-full">Вопросы и ответы</span>
					<span class="header__nav-link-short">FAQ</span>
				</a>

				<a href="/articles" class="header__nav-link">Статьи</a>

				<!-- кабинет / войти -->
				<?php if (!empty($_SESSION['user_id'])): ?>
					<a href="/account/" class="header__nav-link header__nav-account">
						<!-- иконка кабинета (залогинен) — десктоп скрывает иконку, показывает текст -->
						<svg class="header__nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<!-- силуэт пользователя с галочкой -->
							<circle cx="12" cy="8" r="4"/>
							<path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
							<polyline points="9 12 11 14 15 10" stroke-width="2"/>
						</svg>
						<span class="header__nav-label">Кабинет</span>
					</a>
				<?php else: ?>
					<button
						type="button"
						class="header__nav-link header__nav-account"
						style="background:none; border:none; cursor:pointer; font-family:inherit; font-weight:500;"
						onclick="document.dispatchEvent(new CustomEvent('open-login'))"
					>
						<!-- иконка входа (не залогинен) -->
						<svg class="header__nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<!-- силуэт пользователя со стрелкой входа -->
							<circle cx="10" cy="8" r="4"/>
							<path d="M4 20c0-4 3.2-7 7-7"/>
							<polyline points="17 14 21 14"/>
							<polyline points="19 12 21 14 19 16"/>
						</svg>
						<span class="header__nav-label">Войти</span>
					</button>
				<?php endif; ?>
			</nav>
		</div>
	</header>
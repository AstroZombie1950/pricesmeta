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
				<a href="/shop" class="header__nav-btn">
					<span class="header__nav-btn-icon">☰</span>
					Сметы
				</a>
				<a href="/" class="header__nav-link">Главная</a>
				<a href="/faq" class="header__nav-link">Вопросы и ответы</a>
				<a href="/articles" class="header__nav-link">Статьи</a>

				<?php if (!empty($_SESSION['user_id'])): ?>
					<a href="/account/" class="header__nav-link">Кабинет</a>
				<?php else: ?>
				<button
					type="button"
					class="header__nav-link"
					style="background:none; border:none; cursor:pointer; font-family:inherit; font-weight:400;"
					onclick="document.dispatchEvent(new CustomEvent('open-login'))"
				>Войти</button>
				<?php endif; ?>
			</nav>
		</div>
	</header>
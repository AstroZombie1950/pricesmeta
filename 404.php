<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Страница не найдена — ПрайсСмета</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="robots" content="noindex, nofollow">
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Geologica:wght@300;400;500;600;700;800&family=Noto+Sans:wght@400;500&display=swap" rel="stylesheet">
	<!-- Styles -->
	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/header.php'; ?>

	<main>
		<div class="container">
			<div style="
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				text-align: center;
				padding: 100px 24px;
				gap: 20px;
			">
				<span style="font-family:var(--font-head); font-size:96px; font-weight:800; color:var(--color-border); line-height:1;">404</span>

				<h1 style="font-family:var(--font-head); font-size:clamp(22px,3vw,32px); font-weight:700;">
					Страница не найдена
				</h1>

				<p style="font-size:16px; color:var(--color-muted); max-width:400px; line-height:1.7;">
					Возможно, ссылка устарела или страница была удалена.
				</p>

				<div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center; margin-top:8px;">
					<a href="/" style="
						display: inline-block;
						background: var(--color-text);
						color: #fff;
						font-family: var(--font-head);
						font-size: 15px;
						font-weight: 600;
						padding: 12px 28px;
						border-radius: var(--radius-sm);
						transition: background .2s;
					" onmouseover="this.style.background='var(--color-accent)'" onmouseout="this.style.background='var(--color-text)'">
						На главную
					</a>
					<a href="/shop" style="
						display: inline-block;
						color: var(--color-text);
						font-family: var(--font-head);
						font-size: 15px;
						font-weight: 500;
						padding: 12px 28px;
						border-radius: var(--radius-sm);
						border: 1px solid var(--color-border);
						transition: border-color .2s, color .2s;
					" onmouseover="this.style.borderColor='var(--color-accent)';this.style.color='var(--color-accent)'" onmouseout="this.style.borderColor='var(--color-border)';this.style.color='var(--color-text)'">
						Все сметы
					</a>
				</div>
			</div>
		</div>
	</main>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/php/footer.php'; ?>
</body>
</html>
<?php
$alert   = '';
$isEdit  = ($route === 'articles/edit');

/* Загружаем статью для редактирования */
$article = null;
if ($isEdit) {
	$slug = $_GET['slug'] ?? '';
	$stmt = $pdo->prepare('SELECT * FROM articles WHERE slug = ?');
	$stmt->execute([$slug]);
	$article = $stmt->fetch();
	if (!$article) {
		header('Location: /backstage/?route=articles');
		exit;
	}
}

/* ─── Сохранение ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {

	$id           = (int)($_POST['id'] ?? 0);
	$fSlug        = trim($_POST['slug'] ?? '');
	$fTitle       = trim($_POST['title'] ?? '');
	$fExcerpt     = trim($_POST['excerpt'] ?? '');
	$fContent     = trim($_POST['content'] ?? '');
	$fSeoTitle    = trim($_POST['seo_title'] ?? '');
	$fMetaDesc    = trim($_POST['meta_desc'] ?? '');
	$fMetaKw      = trim($_POST['meta_keywords'] ?? '');
	$fPublished   = isset($_POST['is_published']) ? 1 : 0;
	$fImage       = $article['image'] ?? '';

	/* Валидация */
	if (!$fSlug || !$fTitle || !$fContent) {
		$alert = 'err:Заполните slug, заголовок и контент';
	} elseif (!preg_match('/^[a-z0-9\-]+$/', $fSlug)) {
		$alert = 'err:Slug — только латиница, цифры и дефис';
	} else {

		/* Проверяем уникальность slug при создании */
		if (!$isEdit) {
			$check = $pdo->prepare('SELECT id FROM articles WHERE slug = ?');
			$check->execute([$fSlug]);
			if ($check->fetch()) $alert = 'err:Такой slug уже занят';
		}

		if (!$alert) {

			/* Загрузка картинки */
			if (!empty($_FILES['image']['tmp_name'])) {
				$ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
				$allowed = ['jpg', 'jpeg', 'png', 'webp'];

				if (!in_array($ext, $allowed)) {
					$alert = 'err:Допустимые форматы: jpg, png, webp';
				} else {
					$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/articles/';
					if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

					$filename = $fSlug . '-' . time() . '.' . $ext;
					if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
						/* Удаляем старую картинку */
						if ($fImage && file_exists($_SERVER['DOCUMENT_ROOT'] . $fImage)) {
							unlink($_SERVER['DOCUMENT_ROOT'] . $fImage);
						}
						$fImage = '/uploads/articles/' . $filename;
					} else {
						$alert = 'err:Не удалось сохранить изображение';
					}
				}
			}

			/* Также принимаем URL картинки если загрузка пустая */
			if (!$alert && !empty($_POST['image_url']) && empty($_FILES['image']['tmp_name'])) {
				$fImage = trim($_POST['image_url']);
			}
		}

		if (!$alert) {
			if ($isEdit) {
				/* Обновляем существующую статью */
				$stmt = $pdo->prepare('
					UPDATE articles SET
						title = ?, excerpt = ?, content = ?, image = ?,
						seo_title = ?, meta_desc = ?, meta_keywords = ?,
						is_published = ?
					WHERE id = ?
				');
				$stmt->execute([$fTitle, $fExcerpt, $fContent, $fImage, $fSeoTitle, $fMetaDesc, $fMetaKw, $fPublished, $id]);
				$alert = 'ok:Статья сохранена';

				/* Обновляем $article для отображения */
				$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
				$stmt->execute([$id]);
				$article = $stmt->fetch();

			} else {
				/* Создаём новую */
				$stmt = $pdo->prepare('
					INSERT INTO articles (slug, title, excerpt, content, image, seo_title, meta_desc, meta_keywords, is_published)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
				');
				$stmt->execute([$fSlug, $fTitle, $fExcerpt, $fContent, $fImage, $fSeoTitle, $fMetaDesc, $fMetaKw, $fPublished]);

				$alert  = 'ok:Статья создана';
				$isEdit = true;

				/* Переходим в режим редактирования */
				header("Location: /backstage/?route=articles/edit&slug={$fSlug}&saved=1");
				exit;
			}
		}
	}
}

/* Значения полей — из БД или из POST после ошибки */
$fSlug      = $_POST['slug']         ?? $article['slug']          ?? '';
$fTitle     = $_POST['title']        ?? $article['title']         ?? '';
$fExcerpt   = $_POST['excerpt']      ?? $article['excerpt']       ?? '';
$fContent   = $_POST['content']      ?? $article['content']       ?? '';
$fSeoTitle  = $_POST['seo_title']    ?? $article['seo_title']     ?? '';
$fMetaDesc  = $_POST['meta_desc']    ?? $article['meta_desc']     ?? '';
$fMetaKw    = $_POST['meta_keywords']?? $article['meta_keywords'] ?? '';
$fPublished = $_POST['is_published'] ?? $article['is_published']  ?? 0;
$fImage     = $article['image']      ?? '';

/* Флаг из редиректа после создания */
if (isset($_GET['saved'])) $alert = 'ok:Статья создана';

$pageTitle    = $isEdit ? 'Редактировать статью' : 'Новая статья';
$route        = $isEdit ? 'articles/edit' : 'articles/create';
$topbarActions = '<a href="/backstage/?route=articles" class="btn btn--outline">← Все статьи</a>'
	. ($isEdit ? ' <a href="/articles/' . htmlspecialchars($fSlug) . '" class="btn btn--outline" target="_blank">Открыть ↗</a>' : '');

ob_start();
?>

<?php if ($alert): ?>
	<?php [$type, $msg] = explode(':', $alert, 2); ?>
	<div class="bs-alert bs-alert--<?= $type === 'ok' ? 'ok' : 'err' ?>" style="margin-bottom:24px;">
		<?= htmlspecialchars($msg) ?>
	</div>
<?php endif; ?>

<!-- Сетка: форма слева, превью справа -->
<div style="display:grid; grid-template-columns:1fr 400px; gap:24px; align-items:start;">

	<!-- Форма -->
	<form method="POST" enctype="multipart/form-data" data-editor>

		<?php if ($isEdit): ?>
			<input type="hidden" name="id" value="<?= $article['id'] ?>">
		<?php endif; ?>

		<!-- Заголовок и slug -->
		<div class="field__row" style="margin-bottom:16px;">
			<div class="field">
				<label for="title">Заголовок</label>
				<input
					type="text" id="title" name="title" class="bs-input"
					placeholder="Как составить смету для электрика"
					value="<?= htmlspecialchars($fTitle) ?>"
					oninput="<?= !$isEdit ? 'syncSlug(this.value);' : '' ?> updatePreview()"
					required
				>
			</div>
			<div class="field">
				<label for="slug">Slug</label>
				<input
					type="text" id="slug" name="slug" class="bs-input"
					placeholder="kak-sostavit-smetu"
					value="<?= htmlspecialchars($fSlug) ?>"
					<?= $isEdit ? 'readonly title="Slug нельзя менять при редактировании"' : '' ?>
				>
			</div>
		</div>

		<!-- SEO -->
		<div class="field" style="margin-bottom:16px;">
			<label for="seo_title">SEO title <span style="font-weight:400;color:var(--color-muted);">(если отличается от заголовка)</span></label>
			<input type="text" id="seo_title" name="seo_title" class="bs-input"
				placeholder="Оставьте пустым — будет использован заголовок"
				value="<?= htmlspecialchars($fSeoTitle) ?>">
		</div>

		<div class="field__row" style="margin-bottom:16px;">
			<div class="field">
				<label for="meta_desc">Meta description</label>
				<input type="text" id="meta_desc" name="meta_desc" class="bs-input"
					placeholder="Описание для поисковиков, 150–160 символов"
					value="<?= htmlspecialchars($fMetaDesc) ?>">
			</div>
			<div class="field">
				<label for="meta_keywords">Meta keywords</label>
				<input type="text" id="meta_keywords" name="meta_keywords" class="bs-input"
					placeholder="ключевое слово, ещё одно"
					value="<?= htmlspecialchars($fMetaKw) ?>">
			</div>
		</div>

		<!-- Контент — вкладки редактор / HTML -->
		<div class="field" style="margin-bottom:16px;">
			<label>Контент</label>
			<div class="tabs">
				<button type="button" class="tab is-active" onclick="switchTab('wysiwyg', this)">Редактор</button>
				<button type="button" class="tab"           onclick="switchTab('html', this)">HTML</button>
			</div>

			<div class="tab-panel is-active" id="panel-wysiwyg">
				<div class="wysiwyg">
					<div class="wysiwyg__toolbar">
						<button type="button" class="wysiwyg__btn" onclick="fmt('bold')"                title="Жирный"><b>B</b></button>
						<button type="button" class="wysiwyg__btn" onclick="fmt('italic')"              title="Курсив"><i>I</i></button>
						<div class="wysiwyg__btn wysiwyg__btn--sep"></div>
						<button type="button" class="wysiwyg__btn" onclick="fmtBlock('h2')">H2</button>
						<button type="button" class="wysiwyg__btn" onclick="fmtBlock('h3')">H3</button>
						<div class="wysiwyg__btn wysiwyg__btn--sep"></div>
						<button type="button" class="wysiwyg__btn" onclick="fmt('insertUnorderedList')" title="Маркированный список">• —</button>
						<button type="button" class="wysiwyg__btn" onclick="fmt('insertOrderedList')"   title="Нумерованный список">1.</button>
						<div class="wysiwyg__btn wysiwyg__btn--sep"></div>
						<button type="button" class="wysiwyg__btn" onclick="insertLink()"               title="Ссылка">🔗</button>
						<button type="button" class="wysiwyg__btn" onclick="fmt('removeFormat')"        title="Сбросить форматирование">✕</button>
					</div>
					<div
						class="wysiwyg__area"
						id="editor"
						contenteditable="true"
						oninput="syncFromWysiwyg(); updatePreview()"
					><?= $fContent ?></div>
				</div>
			</div>

			<div class="tab-panel" id="panel-html">
				<textarea
					class="html-editor"
					id="htmlEditor"
					rows="20"
					placeholder="<h2>Заголовок раздела</h2>&#10;<p>Текст статьи...</p>"
					oninput="syncFromHtml(); updatePreview()"
				><?= htmlspecialchars($fContent) ?></textarea>
			</div>

			<!-- Скрытое поле — туда попадает финальный HTML перед отправкой -->
			<textarea id="contentInput" name="content"><?= htmlspecialchars($fContent) ?></textarea>
		</div>

		<!-- Excerpt -->
		<div class="field" style="margin-bottom:16px;">
			<label for="excerpt">Excerpt <span style="font-weight:400;color:var(--color-muted);">(краткое описание для карточки)</span></label>
			<textarea id="excerpt" name="excerpt" class="bs-textarea" rows="3"
				placeholder="Разбираем, из каких пунктов состоит грамотная смета..."><?= htmlspecialchars($fExcerpt) ?></textarea>
		</div>

		<!-- Обложка -->
		<div class="field" style="margin-bottom:16px;">
			<label>Обложка</label>

			<!-- Загрузка файла -->
			<div class="upload">
				<input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(this)">
				<p class="upload__text">
					<?= $fImage ? 'Загрузить другое изображение' : 'Нажмите или перетащите файл (jpg, png, webp)' ?>
				</p>
				<?php if ($fImage): ?>
					<img src="<?= htmlspecialchars($fImage) ?>" class="upload__preview" alt="">
				<?php endif; ?>
				<img class="upload__preview" id="imagePreview" alt="" style="display:none;">
			</div>

			<!-- Или URL -->
			<div style="margin-top:10px;">
				<input type="text" name="image_url" class="bs-input"
					placeholder="Или вставьте URL картинки"
					value="<?= !$fImage || str_starts_with($fImage, 'http') ? htmlspecialchars($fImage) : '' ?>">
			</div>
		</div>

		<!-- Статус публикации -->
		<div style="display:flex; align-items:center; gap:10px; margin-bottom:24px;">
			<input type="checkbox" id="is_published" name="is_published" value="1" <?= $fPublished ? 'checked' : '' ?>>
			<label for="is_published" style="font-size:15px; font-weight:500; text-transform:none; letter-spacing:0;">Опубликовать</label>
		</div>

		<button type="submit" name="save" class="btn btn--primary" style="height:48px; font-size:15px; padding:0 32px;">
			<?= $isEdit ? 'Сохранить изменения →' : 'Создать статью →' ?>
		</button>

	</form>

	<!-- Превью -->
	<div class="bs-preview" style="position:sticky; top:80px;">
		<p class="bs-preview__label">Превью</p>
		<div class="article">
			<div class="bs-preview__cover" id="prev_cover" style="<?= $fImage ? '' : 'display:none;' ?> margin-bottom:16px; border-radius:8px; overflow:hidden;">
				<img id="prev_img" src="<?= htmlspecialchars($fImage) ?>" alt="" style="width:100%; display:block;">
			</div>
			<div class="article__content" id="prev_content"><?= $fContent ?></div>
		</div>
	</div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
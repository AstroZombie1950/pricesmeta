<?php
$alert  = '';
$isEdit = ($route === 'products/edit');

/* Загружаем товар для редактирования */
$product = null;
if ($isEdit) {
	$slug = $_GET['slug'] ?? '';
	$stmt = $pdo->prepare('SELECT * FROM products WHERE slug = ?');
	$stmt->execute([$slug]);
	$product = $stmt->fetch();
	if (!$product) {
		header('Location: /backstage/?route=products');
		exit;
	}
}

/* ─── Сохранение ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {

	$id         = (int)($_POST['id'] ?? 0);
	$fSlug      = trim($_POST['slug'] ?? '');
	$fTitle     = trim($_POST['title'] ?? '');
	$fDesc      = trim($_POST['content'] ?? '');
	$fPrice     = (int)($_POST['price'] ?? 0);
	$fOldPrice  = (int)($_POST['old_price'] ?? 0) ?: null;
	$fSeoTitle  = trim($_POST['seo_title'] ?? '');
	$fMetaDesc  = trim($_POST['meta_desc'] ?? '');
	$fMetaKw    = trim($_POST['meta_keywords'] ?? '');
	$fPublished = isset($_POST['is_published']) ? 1 : 0;
	$fImage     = $product['image'] ?? '';
	$fFilesJson = $product['files'] ?? '[]';

	/* Валидация */
	if (!$fSlug || !$fTitle) {
		$alert = 'err:Заполните slug и заголовок';
	} elseif (!preg_match('/^[a-z0-9\-]+$/', $fSlug)) {
		$alert = 'err:Slug — только латиница, цифры и дефис';
	} elseif ($fPrice <= 0) {
		$alert = 'err:Укажите цену';
	} else {

		/* Проверяем уникальность slug при создании */
		if (!$isEdit) {
			$check = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
			$check->execute([$fSlug]);
			if ($check->fetch()) $alert = 'err:Такой slug уже занят';
		}

		if (!$alert) {

			/* Загрузка картинки файлом */
			if (!empty($_FILES['image']['tmp_name'])) {
				$ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
				$allowed = ['jpg', 'jpeg', 'png', 'webp'];
				if (!in_array($ext, $allowed)) {
					$alert = 'err:Допустимые форматы картинки: jpg, png, webp';
				} else {
					$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';
					if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
					$filename = $fSlug . '-' . time() . '.' . $ext;
					if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
						if ($fImage && file_exists($_SERVER['DOCUMENT_ROOT'] . $fImage)) unlink($_SERVER['DOCUMENT_ROOT'] . $fImage);
						$fImage = '/uploads/products/' . $filename;
					} else {
						$alert = 'err:Не удалось сохранить изображение';
					}
				}
			}

			/* Или URL картинки */
			if (!$alert && !empty($_POST['image_url']) && empty($_FILES['image']['tmp_name'])) {
				$fImage = trim($_POST['image_url']);
			}
		}

		if (!$alert) {

			/* Файлы товара для скачивания */
			$filesDir = $_SERVER['DOCUMENT_ROOT'] . '/files/products/' . $fSlug . '/';
			$fFiles   = json_decode($fFilesJson, true) ?: [];

			/* Удаляем отмеченные файлы */
			foreach ($_POST['delete_file'] ?? [] as $fname) {
				$fname  = basename($fname); /* защита от path traversal */
				$fFiles = array_values(array_filter($fFiles, fn($f) => $f !== $fname));
				if (file_exists($filesDir . $fname)) unlink($filesDir . $fname);
			}

			/* Загружаем новые файлы */
			if (!empty($_FILES['product_files']['name'][0])) {
				$allowedExt = ['xlsx', 'xls', 'pdf', 'zip', 'docx'];
				if (!is_dir($filesDir)) mkdir($filesDir, 0755, true);

				foreach ($_FILES['product_files']['tmp_name'] as $i => $tmp) {
					if (!$tmp) continue;
					$origName = $_FILES['product_files']['name'][$i];
					$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
					if (!in_array($ext, $allowedExt)) continue;

					/* Безопасное имя — оставляем кириллицу, латиницу, цифры, точку, дефис */
					$safeName = preg_replace('/[^a-zA-Zа-яА-ЯёЁ0-9._\- ]/u', '', $origName);
					$safeName = trim($safeName);
					if (!$safeName) continue;

					if (move_uploaded_file($tmp, $filesDir . $safeName)) {
						if (!in_array($safeName, $fFiles)) $fFiles[] = $safeName;
					}
				}
			}

			$fFilesJson = json_encode($fFiles, JSON_UNESCAPED_UNICODE);

			if ($isEdit) {
				$stmt = $pdo->prepare('
					UPDATE products SET
						title = ?, description = ?, price = ?, old_price = ?,
						image = ?, files = ?, seo_title = ?, meta_desc = ?, meta_keywords = ?,
						is_published = ?
					WHERE id = ?
				');
				$stmt->execute([$fTitle, $fDesc, $fPrice, $fOldPrice, $fImage, $fFilesJson, $fSeoTitle, $fMetaDesc, $fMetaKw, $fPublished, $id]);
				$alert = 'ok:Товар сохранён';

				$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
				$stmt->execute([$id]);
				$product = $stmt->fetch();

			} else {
				$stmt = $pdo->prepare('
					INSERT INTO products (slug, title, description, price, old_price, image, files, seo_title, meta_desc, meta_keywords, is_published)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				');
				$stmt->execute([$fSlug, $fTitle, $fDesc, $fPrice, $fOldPrice, $fImage, '[]', $fSeoTitle, $fMetaDesc, $fMetaKw, $fPublished]);

				header("Location: /backstage/?route=products/edit&slug={$fSlug}&saved=1");
				exit;
			}
		}
	}
}

/* Значения полей */
$fSlug      = $_POST['slug']          ?? $product['slug']          ?? '';
$fTitle     = $_POST['title']         ?? $product['title']         ?? '';
$fDesc      = $_POST['content']       ?? $product['description']   ?? '';
$fPrice     = $_POST['price']         ?? $product['price']         ?? '';
$fOldPrice  = $_POST['old_price']     ?? $product['old_price']     ?? '';
$fSeoTitle  = $_POST['seo_title']     ?? $product['seo_title']     ?? '';
$fMetaDesc  = $_POST['meta_desc']     ?? $product['meta_desc']     ?? '';
$fMetaKw    = $_POST['meta_keywords'] ?? $product['meta_keywords'] ?? '';
$fPublished = $_POST['is_published']  ?? $product['is_published']  ?? 0;
$fImage     = $product['image']       ?? '';
$fFiles     = json_decode($product['files'] ?? '[]', true) ?: [];

if (isset($_GET['saved'])) $alert = 'ok:Товар создан';

$pageTitle     = $isEdit ? 'Редактировать товар' : 'Новый товар';
$route         = $isEdit ? 'products/edit' : 'products/create';
$topbarActions = '<a href="/backstage/?route=products" class="btn btn--outline">← Все товары</a>'
	. ($isEdit ? ' <a href="/shop/' . htmlspecialchars($fSlug) . '" class="btn btn--outline" target="_blank">Открыть ↗</a>' : '');

ob_start();
?>

<?php if ($alert): ?>
	<?php [$type, $msg] = explode(':', $alert, 2); ?>
	<div class="bs-alert bs-alert--<?= $type === 'ok' ? 'ok' : 'err' ?>" style="margin-bottom:24px;">
		<?= htmlspecialchars($msg) ?>
	</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" data-editor>

	<?php if ($isEdit): ?>
		<input type="hidden" name="id" value="<?= $product['id'] ?>">
	<?php endif; ?>

	<!-- Заголовок и slug -->
	<div class="field__row" style="margin-bottom:16px;">
		<div class="field">
			<label for="title">Название товара</label>
			<input type="text" id="title" name="title" class="bs-input"
				placeholder="Автосмета на электромонтажные работы"
				value="<?= htmlspecialchars($fTitle) ?>"
				oninput="<?= !$isEdit ? 'syncSlug(this.value)' : '' ?>"
				required>
		</div>
		<div class="field">
			<label for="slug">Slug</label>
			<input type="text" id="slug" name="slug" class="bs-input"
				placeholder="elektrika"
				value="<?= htmlspecialchars($fSlug) ?>"
				<?= $isEdit ? 'readonly title="Slug нельзя менять при редактировании"' : '' ?>>
		</div>
	</div>

	<!-- Цены -->
	<div class="field__row" style="margin-bottom:16px;">
		<div class="field">
			<label for="price">Цена, ₽</label>
			<input type="number" id="price" name="price" class="bs-input"
				placeholder="2500" value="<?= htmlspecialchars($fPrice) ?>" min="0" required>
		</div>
		<div class="field">
			<label for="old_price">Старая цена, ₽ <span style="font-weight:400;color:var(--color-muted);">(необязательно)</span></label>
			<input type="number" id="old_price" name="old_price" class="bs-input"
				placeholder="4000" value="<?= htmlspecialchars($fOldPrice) ?>" min="0">
		</div>
	</div>

	<!-- SEO -->
	<div class="field" style="margin-bottom:16px;">
		<label for="seo_title">SEO title</label>
		<input type="text" id="seo_title" name="seo_title" class="bs-input"
			placeholder="Оставьте пустым — будет использовано название"
			value="<?= htmlspecialchars($fSeoTitle) ?>">
	</div>

	<div class="field__row" style="margin-bottom:16px;">
		<div class="field">
			<label for="meta_desc">Meta description</label>
			<input type="text" id="meta_desc" name="meta_desc" class="bs-input"
				placeholder="Описание для поисковиков"
				value="<?= htmlspecialchars($fMetaDesc) ?>">
		</div>
		<div class="field">
			<label for="meta_keywords">Meta keywords</label>
			<input type="text" id="meta_keywords" name="meta_keywords" class="bs-input"
				placeholder="смета электрика, прайс электромонтаж"
				value="<?= htmlspecialchars($fMetaKw) ?>">
		</div>
	</div>

	<!-- Описание — WYSIWYG -->
	<div class="field" style="margin-bottom:16px;">
		<label>Описание товара <span style="font-weight:400;color:var(--color-muted);">(HTML — файлы, разделы, FAQ)</span></label>
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
					<button type="button" class="wysiwyg__btn" onclick="fmt('removeFormat')"        title="Сбросить">✕</button>
				</div>
				<div class="wysiwyg__area" id="editor" contenteditable="true"
					oninput="syncFromWysiwyg()"><?= $fDesc ?></div>
			</div>
		</div>

		<div class="tab-panel" id="panel-html">
			<textarea class="html-editor" id="htmlEditor" rows="20"
				placeholder="<section class=&quot;product-desc&quot;>..."
				oninput="syncFromHtml()"><?= htmlspecialchars($fDesc) ?></textarea>
		</div>

		<textarea id="contentInput" name="content"><?= htmlspecialchars($fDesc) ?></textarea>
	</div>

	<!-- Картинка -->
	<div class="field" style="margin-bottom:16px;">
		<label>Картинка товара</label>
		<div class="upload">
			<input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(this)">
			<p class="upload__text"><?= $fImage ? 'Загрузить другое изображение' : 'Нажмите или перетащите файл (jpg, png, webp)' ?></p>
			<?php if ($fImage): ?>
				<img src="<?= htmlspecialchars($fImage) ?>" class="upload__preview" alt="">
			<?php endif; ?>
			<img class="upload__preview" id="imagePreview" alt="" style="display:none;">
		</div>
		<div style="margin-top:10px;">
			<input type="text" name="image_url" class="bs-input"
				placeholder="Или вставьте URL картинки"
				value="<?= !$fImage || str_starts_with($fImage, 'http') ? htmlspecialchars($fImage) : '' ?>">
		</div>
	</div>

	<!-- Файлы товара -->
	<div class="field" style="margin-bottom:16px;">
		<label>Файлы для скачивания <span style="font-weight:400;color:var(--color-muted);">(xlsx, pdf, zip, docx)</span></label>

		<!-- Уже загруженные файлы -->
		<?php if ($fFiles): ?>
		<div style="display:flex; flex-direction:column; gap:6px; margin-bottom:10px;">
			<?php foreach ($fFiles as $fname): ?>
			<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:var(--color-bg); border:1px solid var(--color-border); border-radius:var(--radius-sm); padding:10px 14px;">
				<span style="font-size:14px; font-family:var(--font-head); font-weight:500;">
					📄 <?= htmlspecialchars($fname) ?>
				</span>
				<label style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--color-sale); cursor:pointer; text-transform:none; letter-spacing:0; font-weight:400;">
					<input type="checkbox" name="delete_file[]" value="<?= htmlspecialchars($fname) ?>">
					Удалить
				</label>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Загрузка новых файлов (multiple) -->
		<div class="upload">
			<input type="file" name="product_files[]" multiple
				accept=".xlsx,.xls,.pdf,.zip,.docx">
			<p class="upload__text">Нажмите или перетащите файлы — можно несколько сразу</p>
		</div>
	</div>

	<!-- Публикация -->
	<div style="display:flex; align-items:center; gap:10px; margin-bottom:24px;">
		<input type="checkbox" id="is_published" name="is_published" value="1" <?= $fPublished ? 'checked' : '' ?>>
		<label for="is_published" style="font-size:15px; font-weight:500; text-transform:none; letter-spacing:0;">Опубликовать</label>
	</div>

	<button type="submit" name="save" class="btn btn--primary" style="height:48px; font-size:15px; padding:0 32px;">
		<?= $isEdit ? 'Сохранить изменения →' : 'Создать товар →' ?>
	</button>

</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
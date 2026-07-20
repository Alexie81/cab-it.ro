<?php
declare(strict_types=1);

require_once __DIR__ . '/../cabit-cms/bootstrap.php';

cms_start_session();
$pdo = cms_db();
$base = cms_web_base();

function admin_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function admin_is_ajax(): bool
{
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
        || (string) ($_POST['_ajax'] ?? '') === '1';
}

function admin_redirect(string $section = 'dashboard', array $params = []): never
{
    $query = http_build_query(array_merge(['section' => $section], $params));
    if (admin_is_ajax()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => true, 'redirect' => 'index.php?' . $query], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    header('Location: index.php?' . $query);
    exit;
}

function admin_post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function admin_delete_upload(?string $publicPath): void
{
    if (!$publicPath || !str_starts_with($publicPath, '/uploads/')) {
        return;
    }
    $path = CABIT_PUBLIC_ROOT . str_replace('/', DIRECTORY_SEPARATOR, $publicPath);
    $uploads = realpath(CABIT_UPLOADS_DIR);
    $resolved = realpath($path);
    if ($uploads && $resolved && str_starts_with($resolved, $uploads . DIRECTORY_SEPARATOR) && is_file($resolved)) {
        unlink($resolved);
    }
}

function admin_article_by_id(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
    $statement->execute([$id]);
    return $statement->fetch() ?: null;
}

function admin_work_by_id(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare('SELECT w.*, c.name AS category_name FROM works w LEFT JOIN categories c ON c.id = w.category_id WHERE w.id = ?');
    $statement->execute([$id]);
    return $statement->fetch() ?: null;
}

function admin_icon(string $name): string
{
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'audits' => '<path d="M9 5h6"/><path d="M9 3h6a2 2 0 0 1 2 2v1h2a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2V5a2 2 0 0 1 2-2Z"/><path d="m8 14 2.2 2.2L16.5 10"/>',
        'articles' => '<path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v5h5M8 12h7M8 16h7"/>',
        'portfolio' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>',
        'categories' => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z"/><path d="M17 14v6M14 17h6"/>',
        'subscribers' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/><path d="M18 3v4M16 5h4"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>',
    ];
    return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['dashboard']) . '</svg>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_post('action') === 'login') {
    try {
        cms_check_csrf(admin_post('csrf'));
        if (!cms_login(admin_post('username'), (string) ($_POST['password'] ?? ''), $_SERVER['REMOTE_ADDR'] ?? '')) {
            throw new RuntimeException('Utilizator sau parolă incorectă.');
        }
        cms_refresh_indexes($pdo);
        admin_flash('Autentificare reușită.');
        admin_redirect();
    } catch (Throwable $error) {
        $loginError = $error->getMessage();
    }
}

if (!cms_is_admin()) {
    ?><!doctype html>
    <html lang="ro-RO">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <meta name="robots" content="noindex,nofollow">
      <meta name="theme-color" content="#008f84">
      <title>Autentificare CMS | Cab-IT Expert</title>
      <link rel="icon" type="image/png" href="<?= cms_e($base . '/img/logo.png?v=20260719-1') ?>">
      <link rel="apple-touch-icon" href="<?= cms_e($base . '/img/logo.png?v=20260719-1') ?>">
      <link rel="stylesheet" href="styles.css?v=20260719-4">
    </head>
    <body class="admin-login-page">
      <main class="admin-login-shell">
        <section class="admin-login-intro" aria-label="Cab-IT Expert CMS">
          <div class="admin-login-orbit" aria-hidden="true">
            <span></span><span></span><span></span>
            <img src="<?= cms_e($base . '/img/logo.png') ?>" alt="">
          </div>
          <span class="admin-login-badge"><i></i> Panou de administrare securizat</span>
          <h2>Tot conținutul website-ului, într-un singur loc.</h2>
          <p>Administrezi articolele, proiectele, auditurile și abonații într-o interfață clară, construită în identitatea CAB-IT.</p>
          <div class="admin-login-features" aria-hidden="true">
            <span>Articole &amp; SEO</span>
            <span>Proiecte</span>
            <span>Audituri</span>
          </div>
        </section>
        <section class="admin-login-card">
          <div class="admin-login-brand">
            <img src="<?= cms_e($base . '/img/logo_home.png') ?>" alt="Cab-IT Expert">
            <span>CMS</span>
          </div>
          <span class="admin-kicker">Administrare website</span>
          <h1>Bine ai revenit.</h1>
          <p>Autentifică-te pentru a administra în siguranță website-ul Cab-IT Expert.</p>
          <?php if (!empty($loginError)): ?><div class="admin-alert is-error"><?= cms_e($loginError) ?></div><?php endif; ?>
          <form method="post">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf" value="<?= cms_e(cms_csrf()) ?>">
            <label>Utilizator
              <span class="admin-login-input"><i aria-hidden="true">◎</i><input name="username" autocomplete="username" placeholder="Numele de utilizator" required autofocus></span>
            </label>
            <label>Parolă
              <span class="admin-login-input"><i aria-hidden="true">◇</i><input type="password" name="password" autocomplete="current-password" placeholder="Parola contului" required></span>
            </label>
            <button type="submit">Intră în panou <span aria-hidden="true">→</span></button>
          </form>
          <div class="admin-login-foot"><span><i></i> Conexiune protejată</span><a class="admin-back" href="<?= cms_e($base . '/') ?>">Înapoi la site ↗</a></div>
        </section>
      </main>
    </body>
    </html><?php
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'subscribers') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="abonati-cabit-' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'wb');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Email', 'Sursă', 'Data abonării']);
    foreach ($pdo->query('SELECT email, source, created_at FROM subscribers ORDER BY created_at DESC') as $subscriber) {
        fputcsv($output, [$subscriber['email'], $subscriber['source'], $subscriber['created_at']]);
    }
    fclose($output);
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'audits') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="audituri-cabit-' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'wb');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID', 'Nume', 'Email', 'Telefon', 'Website', 'Status', 'Notițe', 'Primit', 'Livrat']);
    foreach ($pdo->query('SELECT id, name, email, phone, website_url, status, notes, created_at, delivered_at FROM audit_requests ORDER BY created_at DESC') as $audit) {
        fputcsv($output, [$audit['id'], $audit['name'], $audit['email'], $audit['phone'], $audit['website_url'], $audit['status'], $audit['notes'], $audit['created_at'], $audit['delivered_at']]);
    }
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        cms_check_csrf(admin_post('csrf'));
        $action = admin_post('action');

        if ($action === 'logout') {
            cms_logout();
            header('Location: index.php');
            exit;
        }

        if ($action === 'upload_editor_image') {
            $path = cms_upload($_FILES['editor_image'] ?? [], 'editor-content');
            if (!$path) {
                throw new RuntimeException('Selectează o imagine pentru încărcare.');
            }
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => true, 'url' => $path], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        if ($action === 'save_article') {
            $id = (int) ($_POST['id'] ?? 0);
            $old = $id ? admin_article_by_id($pdo, $id) : null;
            $title = admin_post('title');
            $seoTitle = admin_post('seo_title');
            $description = admin_post('meta_description');
            $slug = cms_slug(admin_post('slug') ?: $title);
            $excerpt = admin_post('excerpt');
            $content = cms_sanitize_html((string) ($_POST['content'] ?? ''));
            $datePublished = admin_post('date_published') ?: date('Y-m-d');
            if (mb_strlen($title) < 3 || mb_strlen($seoTitle) < 10 || mb_strlen($description) < 50 || mb_strlen($excerpt) < 20 || $content === '' || !cms_valid_slug($slug)) {
                throw new RuntimeException('Completează toate câmpurile. Descrierea SEO trebuie să aibă cel puțin 50 de caractere.');
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePublished)) {
                throw new RuntimeException('Data articolului nu este validă.');
            }
            $cover = $old['cover_image'] ?? '/assets/img/hero/hero-3.webp';
            if (!empty($_FILES['cover_image'])) {
                $uploaded = cms_upload($_FILES['cover_image'], 'blog-' . $slug);
                if ($uploaded) {
                    if ($old && $old['cover_image'] !== $uploaded) {
                        admin_delete_upload($old['cover_image']);
                    }
                    $cover = $uploaded;
                }
            }
            $now = date('c');
            $pdo->beginTransaction();
            if ($old) {
                $statement = $pdo->prepare('UPDATE articles SET title=?, seo_title=?, meta_description=?, slug=?, excerpt=?, content=?, cover_image=?, date_published=?, updated_at=? WHERE id=?');
                $statement->execute([$title, $seoTitle, $description, $slug, $excerpt, $content, $cover, $datePublished, $now, $id]);
            } else {
                $statement = $pdo->prepare('INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $statement->execute([$title, $seoTitle, $description, $slug, $excerpt, $content, $cover, $datePublished, $now, $now]);
                $id = (int) $pdo->lastInsertId();
            }
            $article = admin_article_by_id($pdo, $id);
            if ($old && $old['slug'] !== $slug) {
                cms_remove_generated_page('blog', $old['slug']);
            }
            cms_generate_article($article);
            cms_update_blog_index($pdo);
            cms_update_sitemap($pdo);
            $pdo->commit();
            admin_flash('Articolul a fost salvat și pagina indexabilă a fost generată.');
            admin_redirect('articles', ['edit' => $id]);
        }

        if ($action === 'delete_article') {
            $id = (int) ($_POST['id'] ?? 0);
            $article = admin_article_by_id($pdo, $id);
            if (!$article) {
                throw new RuntimeException('Articolul nu există.');
            }
            $pdo->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
            cms_remove_generated_page('blog', $article['slug']);
            admin_delete_upload($article['cover_image']);
            cms_update_blog_index($pdo);
            cms_update_sitemap($pdo);
            admin_flash('Articolul și pagina lui au fost șterse.');
            admin_redirect('articles');
        }

        if ($action === 'save_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = admin_post('name');
            $slug = cms_slug(admin_post('slug') ?: $name);
            if (mb_strlen($name) < 2 || !cms_valid_slug($slug)) {
                throw new RuntimeException('Numele categoriei nu este valid.');
            }
            if ($id) {
                $pdo->prepare('UPDATE categories SET name=?, slug=? WHERE id=?')->execute([$name, $slug, $id]);
            } else {
                $pdo->prepare('INSERT INTO categories (name, slug, created_at) VALUES (?, ?, ?)')->execute([$name, $slug, date('c')]);
            }
            cms_update_portfolio_index($pdo);
            admin_flash('Categoria a fost salvată.');
            admin_redirect('categories');
        }

        if ($action === 'delete_category') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            cms_update_portfolio_index($pdo);
            admin_flash('Categoria a fost ștearsă. Lucrările asociate au rămas fără categorie.');
            admin_redirect('categories');
        }

        if ($action === 'save_work') {
            $id = (int) ($_POST['id'] ?? 0);
            $old = $id ? admin_work_by_id($pdo, $id) : null;
            $title = admin_post('title');
            $seoTitle = admin_post('seo_title');
            $description = admin_post('meta_description');
            $slug = cms_slug(admin_post('slug') ?: $title);
            $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
            $objective = cms_sanitize_html((string) ($_POST['objective'] ?? ''));
            $workDone = cms_sanitize_html((string) ($_POST['work_done'] ?? ''));
            $results = cms_sanitize_html((string) ($_POST['results'] ?? ''));
            $testimonial = cms_sanitize_html((string) ($_POST['testimonial'] ?? ''));
            $externalUrl = admin_post('external_url');
            $dateAdded = admin_post('date_added') ?: date('Y-m-d');
            if (mb_strlen($title) < 3 || mb_strlen($seoTitle) < 10 || mb_strlen($description) < 50 || mb_strlen(strip_tags($objective)) < 20 || mb_strlen(strip_tags($workDone)) < 20 || mb_strlen(strip_tags($results)) < 20 || !cms_valid_slug($slug)) {
                throw new RuntimeException('Completează titlul, câmpurile SEO, obiectivul, lucrările și rezultatele.');
            }
            if ($externalUrl !== '' && !filter_var($externalUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('URL-ul extern nu este valid.');
            }
            $cover = $old['cover_image'] ?? '/assets/img/hero/hero-3.webp';
            if (!empty($_FILES['cover_image'])) {
                $uploaded = cms_upload($_FILES['cover_image'], 'portfolio-' . $slug);
                if ($uploaded) {
                    if ($old && $old['cover_image'] !== $uploaded) {
                        admin_delete_upload($old['cover_image']);
                    }
                    $cover = $uploaded;
                }
            }
            $now = date('c');
            $pdo->beginTransaction();
            if ($old) {
                $statement = $pdo->prepare('UPDATE works SET title=?, seo_title=?, meta_description=?, slug=?, category_id=?, objective=?, work_done=?, results=?, testimonial=?, external_url=?, cover_image=?, date_added=?, updated_at=? WHERE id=?');
                $statement->execute([$title, $seoTitle, $description, $slug, $categoryId, $objective, $workDone, $results, $testimonial, $externalUrl, $cover, $dateAdded, $now, $id]);
            } else {
                $statement = $pdo->prepare('INSERT INTO works (title, seo_title, meta_description, slug, category_id, objective, work_done, results, testimonial, external_url, cover_image, date_added, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $statement->execute([$title, $seoTitle, $description, $slug, $categoryId, $objective, $workDone, $results, $testimonial, $externalUrl, $cover, $dateAdded, $now, $now]);
                $id = (int) $pdo->lastInsertId();
            }
            if (!empty($_FILES['gallery_images'])) {
                $paths = cms_multiple_uploads($_FILES['gallery_images'], 'portfolio-' . $slug);
                $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM work_images WHERE work_id = ' . $id)->fetchColumn();
                $insertImage = $pdo->prepare('INSERT INTO work_images (work_id, path, alt_text, sort_order) VALUES (?, ?, ?, ?)');
                foreach ($paths as $path) {
                    $insertImage->execute([$id, $path, $title, ++$sort]);
                }
            }
            $work = admin_work_by_id($pdo, $id);
            if ($old && $old['slug'] !== $slug) {
                cms_remove_generated_page('portofoliu', $old['slug']);
            }
            cms_generate_work($pdo, $work);
            cms_update_portfolio_index($pdo);
            cms_update_sitemap($pdo);
            $pdo->commit();
            admin_flash('Lucrarea a fost salvată, galeria actualizată și pagina indexabilă generată.');
            admin_redirect('portfolio', ['edit' => $id]);
        }

        if ($action === 'delete_work') {
            $id = (int) ($_POST['id'] ?? 0);
            $work = admin_work_by_id($pdo, $id);
            if (!$work) {
                throw new RuntimeException('Lucrarea nu există.');
            }
            foreach (cms_work_images($pdo, $id) as $image) {
                admin_delete_upload($image['path']);
            }
            admin_delete_upload($work['cover_image']);
            $pdo->prepare('DELETE FROM works WHERE id = ?')->execute([$id]);
            cms_remove_generated_page('portofoliu', $work['slug']);
            cms_update_portfolio_index($pdo);
            cms_update_sitemap($pdo);
            admin_flash('Lucrarea și pagina ei au fost șterse.');
            admin_redirect('portfolio');
        }

        if ($action === 'delete_work_image') {
            $imageId = (int) ($_POST['image_id'] ?? 0);
            $statement = $pdo->prepare('SELECT wi.*, w.id AS work_id FROM work_images wi JOIN works w ON w.id = wi.work_id WHERE wi.id = ?');
            $statement->execute([$imageId]);
            $image = $statement->fetch();
            if (!$image) {
                throw new RuntimeException('Imaginea nu există.');
            }
            admin_delete_upload($image['path']);
            $pdo->prepare('DELETE FROM work_images WHERE id = ?')->execute([$imageId]);
            $work = admin_work_by_id($pdo, (int) $image['work_id']);
            cms_generate_work($pdo, $work);
            admin_flash('Imaginea a fost eliminată din galerie.');
            admin_redirect('portfolio', ['edit' => (int) $image['work_id']]);
        }

        if ($action === 'delete_subscriber') {
            $pdo->prepare('DELETE FROM subscribers WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
            admin_flash('Abonatul a fost șters.');
            admin_redirect('subscribers');
        }

        if ($action === 'update_audit') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = admin_post('status');
            $notes = mb_substr(admin_post('notes'), 0, 3000);
            if (!in_array($status, ['new', 'in_progress', 'delivered'], true)) {
                throw new RuntimeException('Statusul auditului nu este valid.');
            }
            $deliveredAt = $status === 'delivered' ? date('c') : null;
            $statement = $pdo->prepare('UPDATE audit_requests SET status = ?, notes = ?, updated_at = ?, delivered_at = ? WHERE id = ?');
            $statement->execute([$status, $notes, date('c'), $deliveredAt, $id]);
            admin_flash('Solicitarea de audit a fost actualizată.');
            admin_redirect('audits');
        }

        if ($action === 'delete_audit') {
            $pdo->prepare('DELETE FROM audit_requests WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
            admin_flash('Solicitarea de audit a fost ștearsă.');
            admin_redirect('audits');
        }

        if ($action === 'change_password') {
            $adminId = (int) $_SESSION['admin_id'];
            $statement = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
            $statement->execute([$adminId]);
            $hash = (string) $statement->fetchColumn();
            $current = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $confirmation = (string) ($_POST['confirm_password'] ?? '');
            if (!password_verify($current, $hash) || strlen($new) < 12 || $new !== $confirmation) {
                throw new RuntimeException('Parola curentă sau confirmarea nu este corectă. Parola nouă trebuie să aibă minimum 12 caractere.');
            }
            $pdo->prepare('UPDATE admins SET password_hash=?, updated_at=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), date('c'), $adminId]);
            admin_flash('Parola a fost schimbată.');
            admin_redirect('settings');
        }
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errorMessage = $error instanceof PDOException && str_contains($error->getMessage(), 'UNIQUE') ? 'Slugul sau numele există deja.' : $error->getMessage();
        if (admin_is_ajax()) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'message' => $errorMessage], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        $failedAction = admin_post('action');
        if (in_array($failedAction, ['save_article', 'save_work', 'save_category'], true)) {
            $_SESSION['form_recovery'] = ['action' => $failedAction, 'values' => $_POST];
        }
        admin_flash($errorMessage, 'error');
        if ($failedAction === 'save_article') {
            $articleId = (int) ($_POST['id'] ?? 0);
            admin_redirect('articles', $articleId ? ['edit' => $articleId] : ['new' => 1]);
        }
        if ($failedAction === 'save_work') {
            $workId = (int) ($_POST['id'] ?? 0);
            admin_redirect('portfolio', $workId ? ['edit' => $workId] : ['new' => 1]);
        }
        if ($failedAction === 'save_category') {
            $categoryId = (int) ($_POST['id'] ?? 0);
            admin_redirect('categories', $categoryId ? ['edit' => $categoryId] : []);
        }
        admin_redirect($_GET['section'] ?? admin_post('return_section', 'dashboard'));
    }
}

$section = (string) ($_GET['section'] ?? 'dashboard');
$allowedSections = ['dashboard', 'audits', 'articles', 'portfolio', 'categories', 'subscribers', 'settings'];
if (!in_array($section, $allowedSections, true)) {
    $section = 'dashboard';
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$formRecovery = $_SESSION['form_recovery'] ?? null;
unset($_SESSION['form_recovery']);
$counts = [
    'articles' => (int) $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
    'works' => (int) $pdo->query('SELECT COUNT(*) FROM works')->fetchColumn(),
    'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'subscribers' => (int) $pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn(),
    'audits' => (int) $pdo->query('SELECT COUNT(*) FROM audit_requests')->fetchColumn(),
    'new_audits' => (int) $pdo->query('SELECT COUNT(*) FROM audit_requests WHERE status = "new"')->fetchColumn(),
];
$csrf = cms_csrf();
?><!doctype html>
<html lang="ro-RO">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
  <title>Admin Cab-IT Expert</title><link rel="icon" type="image/png" href="<?= cms_e($base . '/img/logo.png?v=20260719-1') ?>"><link rel="apple-touch-icon" href="<?= cms_e($base . '/img/logo.png?v=20260719-1') ?>"><link rel="stylesheet" href="styles.css?v=20260719-4"><link rel="stylesheet" href="editor.css?v=20260719-4">
</head>
<body class="admin-app">
  <aside class="admin-sidebar">
    <a class="admin-logo" href="index.php"><img src="<?= cms_e($base . '/img/logo_home.png') ?>" alt="Cab-IT Expert"></a>
    <nav>
      <a class="<?= $section === 'dashboard' ? 'is-active' : '' ?>" href="?section=dashboard"><?= admin_icon('dashboard') ?><b>Dashboard</b></a>
      <a class="<?= $section === 'audits' ? 'is-active' : '' ?>" href="?section=audits"><?= admin_icon('audits') ?><b>Audituri</b><span><?= $counts['new_audits'] ?></span></a>
      <a class="<?= $section === 'articles' ? 'is-active' : '' ?>" href="?section=articles"><?= admin_icon('articles') ?><b>Articole</b><span><?= $counts['articles'] ?></span></a>
      <a class="<?= $section === 'portfolio' ? 'is-active' : '' ?>" href="?section=portfolio"><?= admin_icon('portfolio') ?><b>Portofoliu</b><span><?= $counts['works'] ?></span></a>
      <a class="<?= $section === 'categories' ? 'is-active' : '' ?>" href="?section=categories"><?= admin_icon('categories') ?><b>Categorii</b><span><?= $counts['categories'] ?></span></a>
      <a class="<?= $section === 'subscribers' ? 'is-active' : '' ?>" href="?section=subscribers"><?= admin_icon('subscribers') ?><b>Abonați</b><span><?= $counts['subscribers'] ?></span></a>
      <a class="<?= $section === 'settings' ? 'is-active' : '' ?>" href="?section=settings"><?= admin_icon('settings') ?><b>Securitate</b></a>
    </nav>
    <form method="post"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><button class="admin-logout" type="submit">Ieșire din cont</button></form>
  </aside>
  <button class="admin-menu-backdrop" type="button" aria-label="Închide meniul" tabindex="-1"></button>
  <div class="admin-shell">
    <header class="admin-topbar"><button class="admin-menu-button" type="button" aria-label="Deschide meniul">☰</button><div><strong>Cab-IT Expert CMS</strong><span>Conectat ca <?= cms_e((string) $_SESSION['admin_username']) ?></span></div><a href="<?= cms_e($base . '/') ?>" target="_blank" rel="noopener">Vezi site-ul ↗</a></header>
    <main class="admin-main">
      <?php if ($flash && $flash['type'] !== 'error'): ?><div class="admin-alert"><?= cms_e($flash['message']) ?></div><?php endif; ?>
      <div class="admin-popup" id="admin-error-popup" <?= $flash && $flash['type'] === 'error' ? '' : 'hidden' ?> aria-hidden="<?= $flash && $flash['type'] === 'error' ? 'false' : 'true' ?>">
        <div class="admin-popup__backdrop" data-popup-close></div><section class="admin-popup__dialog" role="alertdialog" aria-modal="true" aria-labelledby="admin-popup-title"><span class="admin-popup__icon" aria-hidden="true">!</span><div><span class="admin-kicker">Verifică formularul</span><h2 id="admin-popup-title">Modificările tale sunt păstrate</h2><p data-popup-message><?= $flash && $flash['type'] === 'error' ? cms_e($flash['message']) : '' ?></p></div><button class="admin-popup__close" type="button" data-popup-close>Închide și continuă editarea</button></section>
      </div>

      <?php if ($section === 'dashboard'): ?>
        <div class="admin-heading"><div><span class="admin-kicker">Prezentare generală</span><h1>Dashboard</h1></div></div>
        <div class="admin-stats"><a href="?section=audits"><strong><?= $counts['new_audits'] ?></strong><span>Audituri noi</span></a><a href="?section=articles"><strong><?= $counts['articles'] ?></strong><span>Articole indexabile</span></a><a href="?section=portfolio"><strong><?= $counts['works'] ?></strong><span>Lucrări în portofoliu</span></a><a href="?section=subscribers"><strong><?= $counts['subscribers'] ?></strong><span>Abonați newsletter</span></a></div>
        <section class="admin-panel"><h2>Ce actualizează automat CMS-ul</h2><div class="admin-help-grid"><p><strong>Blog:</strong> creează URL separat, canonical, schema BlogPosting și afișează pe homepage ultimele 6 articole după data publicării.</p><p><strong>Portofoliu:</strong> creează studiul de caz, galeria, filtrul de categorie și cardul public.</p><p><strong>SEO și AI:</strong> actualizează sitemap-ul complet, sitemap-ul articolelor/proiectelor, datele <code>lastmod</code> și fișierele <code>llms.txt</code> după fiecare adăugare, editare sau ștergere.</p></div></section>
      <?php endif; ?>

      <?php if ($section === 'articles'):
          $editArticle = isset($_GET['edit']) ? admin_article_by_id($pdo, (int) $_GET['edit']) : null;
          $articles = $pdo->query('SELECT * FROM articles ORDER BY created_at DESC, id DESC')->fetchAll(); ?>
        <div class="admin-heading"><div><span class="admin-kicker">Blog</span><h1><?= $editArticle ? 'Editează articolul' : 'Articole' ?></h1></div><a class="admin-primary-link" href="?section=articles&new=1">Articol nou</a></div>
        <?php if ($editArticle || isset($_GET['new'])): $article = $editArticle ?: ['id'=>'','title'=>'','seo_title'=>'','meta_description'=>'','slug'=>'','excerpt'=>'','content'=>'','cover_image'=>'','date_published'=>date('Y-m-d')]; if (($formRecovery['action'] ?? '') === 'save_article') { $article = array_replace($article, $formRecovery['values']); } ?>
          <form class="admin-panel admin-form" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save_article"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $article['id'] ?>">
            <div class="admin-form-grid"><label>Titlul articolului<input name="title" value="<?= cms_e($article['title']) ?>" required></label><label>Data publicării<input type="date" name="date_published" value="<?= cms_e($article['date_published']) ?>" required></label></div>
            <label>SEO Title <small>recomandat maximum 60 caractere</small><input name="seo_title" maxlength="80" value="<?= cms_e($article['seo_title']) ?>" required></label>
            <label>Meta description <small>recomandat 140–160 caractere</small><textarea name="meta_description" rows="3" maxlength="190" required><?= cms_e($article['meta_description']) ?></textarea></label>
            <label>Slug URL<input name="slug" pattern="[a-z0-9-]+" value="<?= cms_e($article['slug']) ?>" placeholder="titlul-articolului"></label>
            <label>Rezumat pentru card<textarea name="excerpt" rows="3" required><?= cms_e($article['excerpt']) ?></textarea></label>
            <div class="admin-field"><label for="article-content">Conținut <small>Editor vizual avansat, cod HTML, previzualizare, tabele, imagini și blocuri reutilizabile</small></label><textarea id="article-content" class="admin-editor" name="content" rows="18" data-rich-editor data-editor-title="Conținut articol" required><?= cms_e($article['content']) ?></textarea></div>
            <label>Imagine principală<input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label>
            <?php if ($article['cover_image']): ?><img class="admin-preview" src="<?= cms_e($base . $article['cover_image']) ?>" alt="Imagine articol"><?php endif; ?>
            <div class="admin-form-actions"><button type="submit">Salvează și generează pagina</button><a href="?section=articles">Anulează</a></div>
          </form>
        <?php endif; ?>
        <section class="admin-panel"><div class="admin-panel-head"><h2>Toate articolele</h2></div><div class="admin-table-wrap"><table><thead><tr><th>Articol</th><th>Data</th><th>Slug</th><th>Acțiuni</th></tr></thead><tbody><?php foreach ($articles as $article): ?><tr><td><strong><?= cms_e($article['title']) ?></strong><small><?= cms_e($article['seo_title']) ?></small></td><td><?= cms_e($article['date_published']) ?></td><td><code>/blog/<?= cms_e($article['slug']) ?>/</code></td><td class="admin-row-actions"><a href="?section=articles&edit=<?= (int) $article['id'] ?>">Editează</a><a href="<?= cms_e($base . '/blog/' . $article['slug'] . '/') ?>" target="_blank">Vezi</a><form method="post" onsubmit="return confirm('Ștergi articolul și pagina publică?')"><input type="hidden" name="action" value="delete_article"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $article['id'] ?>"><button class="is-danger" type="submit">Șterge</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
      <?php endif; ?>

      <?php if ($section === 'portfolio'):
          $editWork = isset($_GET['edit']) ? admin_work_by_id($pdo, (int) $_GET['edit']) : null;
          $works = $pdo->query('SELECT w.*, c.name AS category_name FROM works w LEFT JOIN categories c ON c.id=w.category_id ORDER BY w.date_added DESC, w.id DESC')->fetchAll();
          $categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll(); ?>
        <div class="admin-heading"><div><span class="admin-kicker">Studii de caz</span><h1><?= $editWork ? 'Editează lucrarea' : 'Portofoliu' ?></h1></div><a class="admin-primary-link" href="?section=portfolio&new=1">Lucrare nouă</a></div>
        <?php if ($editWork || isset($_GET['new'])): $work = $editWork ?: ['id'=>'','title'=>'','seo_title'=>'','meta_description'=>'','slug'=>'','category_id'=>'','objective'=>'','work_done'=>'','results'=>'','testimonial'=>'','external_url'=>'','cover_image'=>'','date_added'=>date('Y-m-d')]; if (($formRecovery['action'] ?? '') === 'save_work') { $work = array_replace($work, $formRecovery['values']); } ?>
          <form class="admin-panel admin-form" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save_work"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $work['id'] ?>">
            <div class="admin-form-grid"><label>Numele lucrării<input name="title" value="<?= cms_e($work['title']) ?>" required></label><label>Data adăugării<input type="date" name="date_added" value="<?= cms_e($work['date_added']) ?>" required></label></div>
            <div class="admin-form-grid"><label>Categorie<select name="category_id"><option value="">Fără categorie</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $work['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= cms_e($category['name']) ?></option><?php endforeach; ?></select></label><label>Slug URL<input name="slug" pattern="[a-z0-9-]+" value="<?= cms_e($work['slug']) ?>"></label></div>
            <label>SEO Title<input name="seo_title" maxlength="80" value="<?= cms_e($work['seo_title']) ?>" required></label><label>Meta description<textarea name="meta_description" rows="3" maxlength="190" required><?= cms_e($work['meta_description']) ?></textarea></label>
            <div class="admin-field"><label for="work-objective">Obiectivul inițial</label><textarea id="work-objective" class="admin-editor" name="objective" rows="5" data-rich-editor data-editor-title="Obiectivul proiectului" required><?= cms_e($work['objective']) ?></textarea></div>
            <div class="admin-field"><label for="work-done">Ce am făcut</label><textarea id="work-done" class="admin-editor" name="work_done" rows="7" data-rich-editor data-editor-title="Implementare" required><?= cms_e($work['work_done']) ?></textarea></div>
            <div class="admin-field"><label for="work-results">Rezultate și măsurare</label><textarea id="work-results" class="admin-editor" name="results" rows="5" data-rich-editor data-editor-title="Rezultate și măsurare" required><?= cms_e($work['results']) ?></textarea></div>
            <div class="admin-field"><label for="work-testimonial">Testimonial client <small>opțional; publică doar citate verificabile</small></label><textarea id="work-testimonial" class="admin-editor" name="testimonial" rows="4" data-rich-editor data-editor-title="Testimonial client"><?= cms_e($work['testimonial']) ?></textarea></div>
            <div class="admin-form-grid"><label>Website extern<input type="url" name="external_url" value="<?= cms_e($work['external_url']) ?>" placeholder="https://"></label><label>Imagine copertă<input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label></div>
            <label>Galerie proiect <small>poți selecta mai multe imagini JPG, PNG sau WebP</small><input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple></label>
            <?php if ($work['cover_image']): ?><img class="admin-preview" src="<?= cms_e($base . $work['cover_image']) ?>" alt="Copertă proiect"><?php endif; ?>
            <div class="admin-form-actions"><button type="submit">Salvează și generează studiul de caz</button><a href="?section=portfolio">Anulează</a></div>
          </form>
          <?php if ($editWork): $gallery = cms_work_images($pdo, (int) $editWork['id']); if ($gallery): ?><section class="admin-panel"><h2>Imagini în galerie</h2><div class="admin-gallery"><?php foreach ($gallery as $image): ?><figure><img src="<?= cms_e($base . $image['path']) ?>" alt="<?= cms_e($image['alt_text']) ?>"><form method="post" onsubmit="return confirm('Ștergi imaginea?')"><input type="hidden" name="action" value="delete_work_image"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>"><button class="is-danger" type="submit">Șterge</button></form></figure><?php endforeach; ?></div></section><?php endif; endif; ?>
        <?php endif; ?>
        <section class="admin-panel"><h2>Toate lucrările</h2><div class="admin-table-wrap"><table><thead><tr><th>Lucrare</th><th>Categorie</th><th>Data</th><th>Acțiuni</th></tr></thead><tbody><?php foreach ($works as $work): ?><tr><td><strong><?= cms_e($work['title']) ?></strong><small><code>/portofoliu/<?= cms_e($work['slug']) ?>/</code></small></td><td><?= cms_e($work['category_name'] ?: '—') ?></td><td><?= cms_e($work['date_added']) ?></td><td class="admin-row-actions"><a href="?section=portfolio&edit=<?= (int) $work['id'] ?>">Editează</a><a href="<?= cms_e($base . '/portofoliu/' . $work['slug'] . '/') ?>" target="_blank">Vezi</a><form method="post" onsubmit="return confirm('Ștergi lucrarea, imaginile și pagina publică?')"><input type="hidden" name="action" value="delete_work"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $work['id'] ?>"><button class="is-danger" type="submit">Șterge</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
      <?php endif; ?>

      <?php if ($section === 'categories'): $categories = $pdo->query('SELECT c.*, COUNT(w.id) AS works_count FROM categories c LEFT JOIN works w ON w.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetchAll(); $editCategory = null; if (isset($_GET['edit'])) { foreach ($categories as $candidate) { if ((int) $candidate['id'] === (int) $_GET['edit']) $editCategory = $candidate; } } if (($formRecovery['action'] ?? '') === 'save_category') { $editCategory = array_replace($editCategory ?: ['id'=>'','name'=>'','slug'=>''], $formRecovery['values']); } ?>
        <div class="admin-heading"><div><span class="admin-kicker">Portofoliu</span><h1>Categorii lucrări</h1></div></div><div class="admin-two-columns"><form class="admin-panel admin-form" method="post"><input type="hidden" name="action" value="save_category"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) ($editCategory['id'] ?? 0) ?>"><h2><?= $editCategory ? 'Editează categoria' : 'Categorie nouă' ?></h2><label>Nume<input name="name" value="<?= cms_e($editCategory['name'] ?? '') ?>" required></label><label>Slug<input name="slug" pattern="[a-z0-9-]+" value="<?= cms_e($editCategory['slug'] ?? '') ?>"></label><button type="submit">Salvează categoria</button></form><section class="admin-panel"><h2>Categorii existente</h2><div class="admin-table-wrap"><table><thead><tr><th>Nume</th><th>Lucrări</th><th>Acțiuni</th></tr></thead><tbody><?php foreach ($categories as $category): ?><tr><td><?= cms_e($category['name']) ?><small><code><?= cms_e($category['slug']) ?></code></small></td><td><?= (int) $category['works_count'] ?></td><td class="admin-row-actions"><a href="?section=categories&edit=<?= (int) $category['id'] ?>">Editează</a><form method="post" onsubmit="return confirm('Ștergi categoria? Lucrările vor rămâne fără categorie.')"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="is-danger" type="submit">Șterge</button></form></td></tr><?php endforeach; ?></tbody></table></div></section></div>
      <?php endif; ?>

      <?php if ($section === 'subscribers'): $subscribers = $pdo->query('SELECT * FROM subscribers ORDER BY created_at DESC')->fetchAll(); ?>
        <div class="admin-heading"><div><span class="admin-kicker">Newsletter CRM</span><h1>Abonați</h1></div><a class="admin-primary-link" href="?export=subscribers">Exportă CSV</a></div><section class="admin-panel"><p>Lista este completată de formularele „Subscribe Newsletter” conectate la endpointul securizat al site-ului.</p><div class="admin-table-wrap"><table><thead><tr><th>Email</th><th>Sursă</th><th>Data abonării</th><th>Acțiuni</th></tr></thead><tbody><?php foreach ($subscribers as $subscriber): ?><tr><td><strong><?= cms_e($subscriber['email']) ?></strong></td><td><?= cms_e($subscriber['source']) ?></td><td><?= cms_e($subscriber['created_at']) ?></td><td><form method="post" onsubmit="return confirm('Ștergi abonatul?')"><input type="hidden" name="action" value="delete_subscriber"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $subscriber['id'] ?>"><button class="is-danger" type="submit">Șterge</button></form></td></tr><?php endforeach; ?><?php if (!$subscribers): ?><tr><td colspan="4">Nu există încă abonați.</td></tr><?php endif; ?></tbody></table></div></section>
      <?php endif; ?>

      <?php if ($section === 'audits'):
          $audits = $pdo->query('SELECT * FROM audit_requests ORDER BY CASE status WHEN "new" THEN 0 WHEN "in_progress" THEN 1 ELSE 2 END, created_at DESC')->fetchAll();
          $auditStats = ['new' => 0, 'in_progress' => 0, 'delivered' => 0];
          foreach ($audits as $auditItem) {
              if (isset($auditStats[$auditItem['status']])) {
                  $auditStats[$auditItem['status']]++;
              }
          }
      ?>
        <div class="admin-heading"><div><span class="admin-kicker">Lead-uri cu termen de 30 minute</span><h1>Solicitări audit</h1></div><a class="admin-primary-link" href="?export=audits">Exportă CSV</a></div>
        <div class="admin-audit-stats">
          <article><span class="is-new">●</span><div><strong><?= $auditStats['new'] ?></strong><small>Solicitări noi</small></div></article>
          <article><span class="is-progress">◔</span><div><strong><?= $auditStats['in_progress'] ?></strong><small>În lucru</small></div></article>
          <article><span class="is-delivered">✓</span><div><strong><?= $auditStats['delivered'] ?></strong><small>Audituri livrate</small></div></article>
          <article><span class="is-total">↗</span><div><strong><?= count($audits) ?></strong><small>Total solicitări</small></div></article>
        </div>
        <section class="admin-panel admin-audits-panel"><div class="admin-audits-intro"><div><strong>Coada de audituri</strong><p>Solicitările noi sunt primele. După trimiterea evaluării pe email, marchează solicitarea drept „Livrat”.</p></div><span>Actualizare manuală, control total</span></div><div class="admin-audit-list">
          <?php foreach ($audits as $audit): ?>
            <article class="admin-audit-card is-<?= cms_e($audit['status']) ?>">
              <div class="admin-audit-head">
                <div><span class="admin-status"><?= $audit['status'] === 'new' ? 'Nou' : ($audit['status'] === 'in_progress' ? 'În lucru' : 'Livrat') ?></span><h2>#<?= (int) $audit['id'] ?> · <?= cms_e($audit['name']) ?></h2><small>Primit <?= cms_e($audit['created_at']) ?></small></div>
                <a class="admin-audit-site" href="<?= cms_e($audit['website_url']) ?>" target="_blank" rel="noopener">Deschide website <span>↗</span></a>
              </div>
              <dl>
                <div><dt>Email</dt><dd><a href="mailto:<?= cms_e($audit['email']) ?>"><?= cms_e($audit['email']) ?></a></dd></div>
                <div><dt>Telefon</dt><dd><?= cms_e($audit['phone'] ?: '—') ?></dd></div>
                <div class="is-wide"><dt>Website analizat</dt><dd><?= cms_e($audit['website_url']) ?></dd></div>
                <div><dt>Livrat</dt><dd><?= cms_e($audit['delivered_at'] ?: 'Nelivrat încă') ?></dd></div>
              </dl>
              <form class="admin-form admin-audit-form" method="post">
                <input type="hidden" name="action" value="update_audit"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $audit['id'] ?>">
                <label>Status<select name="status"><option value="new" <?= $audit['status'] === 'new' ? 'selected' : '' ?>>Nou</option><option value="in_progress" <?= $audit['status'] === 'in_progress' ? 'selected' : '' ?>>În lucru</option><option value="delivered" <?= $audit['status'] === 'delivered' ? 'selected' : '' ?>>Livrat</option></select></label>
                <label>Notițe interne<textarea name="notes" rows="3" placeholder="Observații, verificări și pașii următori…"><?= cms_e($audit['notes']) ?></textarea></label>
                <button type="submit">Salvează starea <span>→</span></button>
              </form>
              <form class="admin-audit-delete" method="post" onsubmit="return confirm('Ștergi definitiv solicitarea?')"><input type="hidden" name="action" value="delete_audit"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><input type="hidden" name="id" value="<?= (int) $audit['id'] ?>"><button class="is-danger" type="submit">Șterge solicitarea</button></form>
            </article>
          <?php endforeach; ?>
          <?php if (!$audits): ?><p>Nu există încă solicitări de audit.</p><?php endif; ?>
        </div></section>
      <?php endif; ?>

      <?php if ($section === 'settings'): ?>
        <div class="admin-heading"><div><span class="admin-kicker">Securitate</span><h1>Schimbă parola</h1></div></div><form class="admin-panel admin-form admin-narrow" method="post"><input type="hidden" name="action" value="change_password"><input type="hidden" name="csrf" value="<?= cms_e($csrf) ?>"><label>Parola curentă<input type="password" name="current_password" autocomplete="current-password" required></label><label>Parola nouă <small>minimum 12 caractere</small><input type="password" name="new_password" autocomplete="new-password" minlength="12" required></label><label>Confirmă parola nouă<input type="password" name="confirm_password" autocomplete="new-password" minlength="12" required></label><button type="submit">Actualizează parola</button></form>
      <?php endif; ?>
    </main>
  </div>
  <script src="admin.js?v=20260719-5"></script>
</body>
</html>

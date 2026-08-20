<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

const CABIT_SEO_RELEASE_DATE = '2026-08-19';

function cms_ensure_directories(): void
{
    foreach ([CABIT_STORAGE_DIR, CABIT_UPLOADS_DIR, CABIT_UPLOADS_DIR . '/blog', CABIT_UPLOADS_DIR . '/portfolio'] as $directory) {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Nu pot crea directorul: ' . $directory);
        }
    }
}

function cms_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    cms_ensure_directories();
    $pdo = new PDO('sqlite:' . CABIT_DATABASE_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    $pdo->exec('CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        seo_title TEXT NOT NULL,
        meta_description TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        excerpt TEXT NOT NULL,
        content TEXT NOT NULL,
        cover_image TEXT,
        date_published TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $articleColumns = [];
    foreach ($pdo->query('PRAGMA table_info(articles)') as $column) {
        $articleColumns[(string) $column['name']] = true;
    }
    if (!isset($articleColumns['seo_metadata'])) {
        $pdo->exec('ALTER TABLE articles ADD COLUMN seo_metadata TEXT NOT NULL DEFAULT "{}"');
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        slug TEXT NOT NULL UNIQUE,
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS works (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        seo_title TEXT NOT NULL,
        meta_description TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        category_id INTEGER,
        objective TEXT NOT NULL,
        work_done TEXT NOT NULL,
        results TEXT NOT NULL,
        testimonial TEXT NOT NULL DEFAULT "",
        external_url TEXT NOT NULL DEFAULT "",
        cover_image TEXT,
        date_added TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS work_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        work_id INTEGER NOT NULL,
        path TEXT NOT NULL,
        alt_text TEXT NOT NULL DEFAULT "",
        sort_order INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY(work_id) REFERENCES works(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS subscribers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        source TEXT NOT NULL DEFAULT "footer",
        ip_hash TEXT NOT NULL DEFAULT "",
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS audit_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL DEFAULT "",
        website_url TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "new",
        notes TEXT NOT NULL DEFAULT "",
        ip_hash TEXT NOT NULL DEFAULT "",
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        delivered_at TEXT
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_hash TEXT NOT NULL,
        succeeded INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL
    )');

    cms_seed($pdo);
    return $pdo;
}

function cms_seed(PDO $pdo): void
{
    $now = date('c');
    $admin = $pdo->prepare('INSERT OR IGNORE INTO admins (username, password_hash, updated_at) VALUES (?, ?, ?)');
    $admin->execute([CABIT_ADMIN_USERNAME, CABIT_INITIAL_PASSWORD_HASH, $now]);

    if ((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 0) {
        $insert = $pdo->prepare('INSERT INTO categories (name, slug, created_at) VALUES (?, ?, ?)');
        foreach ([['Web design', 'web-design'], ['SEO și reclame plătite', 'seo-reclame'], ['Social media', 'social-media'], ['E-commerce', 'e-commerce']] as $category) {
            $insert->execute([$category[0], $category[1], $now]);
        }
    }

    // Articolele sunt administrate exclusiv din CMS. Conținutul demonstrativ
    // rămâne dezactivat inclusiv după ștergerea tuturor articolelor.
    $seedDemoArticles = false;
    if ($seedDemoArticles && (int) $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn() === 0) {
        $articles = [
            [
                'Cum stabilești prioritățile SEO pentru un IMM',
                'Priorități SEO pentru IMM-uri | Ghid Cab-IT',
                'Ghid practic pentru prioritizarea SEO într-un IMM: indexare, intenție de căutare, conținut, linkuri interne și măsurare.',
                'prioritati-seo-pentru-imm',
                'Tehnic, conținut, intenție de căutare și măsurare într-un plan SEO care poate fi executat.',
                '<h2>Pornește de la paginile importante pentru afacere</h2><p>Stabilește ce produse sau servicii trebuie găsite și ce acțiune vrei să facă vizitatorul. Verifică indexarea, canonical-ul, intenția de căutare și claritatea informației.</p><h2>Ordinea recomandată</h2><ol><li>verifică indexarea și erorile tehnice;</li><li>mapează intenția principală pe fiecare pagină;</li><li>îmbunătățește informația și heading-urile;</li><li>creează legături interne relevante;</li><li>urmărește impresii, clickuri și conversii.</li></ol><h2>Măsoară progresul, nu un singur cuvânt</h2><p>Compară grupuri de interogări, pagini și acțiuni comerciale în perioade similare.</p>',
                '/assets/img/case-studies/case-1.webp',
            ],
            [
                'Ce indicatori măsori într-o campanie de promovare online',
                'Promovare Online: Ce Indicatori Măsori | Cab-IT',
                'Află ce indicatori contează într-o campanie de promovare online: CTR, clickuri, leaduri, cost per conversie, venit și profitabilitate.',
                'indicatori-campanie-promovare-online',
                'Diferența dintre click, lead și vânzare și de ce ROAS nu spune singur toată povestea.',
                '<h2>Definește conversia înainte de lansare</h2><p>Stabilește ce acțiune are valoare: formular, apel, rezervare sau comandă. Testează măsurarea înainte de a porni bugetul.</p><h2>Indicatori pe niveluri</h2><ul><li><strong>Vizibilitate:</strong> afișări, acoperire și frecvență;</li><li><strong>Interes:</strong> clickuri, CTR și cost per click;</li><li><strong>Acțiune:</strong> leaduri și cost per conversie;</li><li><strong>Business:</strong> venit, marjă și profitabilitate.</li></ul><h2>ROAS în context</h2><p>Analizează și costurile, marja, calitatea leadurilor și durata ciclului de vânzare.</p>',
                '/assets/img/case-studies/case-5.webp',
            ],
            [
                'Core Web Vitals: LCP, CLS și INP, explicate simplu',
                'Core Web Vitals: LCP, CLS și INP | Cab-IT',
                'Ghid Core Web Vitals despre LCP, CLS și INP: ce măsoară, praguri recomandate și optimizări cu impact pe mobil.',
                'core-web-vitals-lcp-cls-inp',
                'Cum influențează LCP, CLS și INP experiența vizitatorilor și ce merită optimizat întâi.',
                '<h2>Ce măsoară fiecare indicator</h2><p><strong>LCP</strong> descrie afișarea elementului principal, <strong>CLS</strong> stabilitatea vizuală, iar <strong>INP</strong> răspunsul la interacțiuni.</p><h2>Praguri și optimizări</h2><p>Țintele uzuale sunt LCP sub 2,5 secunde, CLS sub 0,1 și INP sub 200 ms. Comprimă imaginile, setează dimensiuni explicite, redu CSS-ul critic și elimină JavaScript-ul nefolosit.</p><h2>Verifică datele reale</h2><p>Testele de laborator ajută diagnosticul, dar hostingul, conexiunea și dispozitivele vizitatorilor influențează experiența reală.</p>',
                '/assets/img/hero/hero-3.webp',
            ],
        ];
        $insert = $pdo->prepare('INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($articles as $article) {
            $insert->execute([$article[0], $article[1], $article[2], $article[3], $article[4], $article[5], $article[6], '2026-07-18', $now, $now]);
        }
    }

    $additionalArticles = [
        [
            'Cât costă un website profesional în 2026?',
            'Cât costă un website profesional în 2026? | Cab-IT',
            'Află ce influențează costul unui website profesional în 2026: structură, design, funcții, SEO, mentenanță și întrebările utile înainte de ofertă.',
            'cat-costa-website-profesional-2026',
            'Un ghid transparent despre elementele care formează bugetul unui website și diferența dintre o pagină simplă și o platformă pregătită pentru creștere.',
            '<h2>Prețul pornește de la obiective, nu de la numărul de pagini</h2><p>Un website de prezentare, un magazin online și o platformă cu automatizări pot avea dimensiuni apropiate, dar niveluri de complexitate foarte diferite. Înainte de estimare trebuie clarificate publicul, acțiunea principală dorită, funcțiile, integrările și modul în care rezultatele vor fi măsurate.</p><h2>Ce intră într-o estimare profesionistă</h2><ul><li><strong>Strategia și arhitectura:</strong> pagini, trasee de conversie și priorități de conținut;</li><li><strong>Designul responsive:</strong> interfață adaptată brandului și testată pe mobil;</li><li><strong>Dezvoltarea:</strong> formulare, catalog, plăți, conturi sau integrări;</li><li><strong>SEO tehnic:</strong> structură semantică, metadata, canonical, sitemap și performanță;</li><li><strong>Lansarea și suportul:</strong> testare, securitate, copii de siguranță și mentenanță.</li></ul><h2>De ce ofertele foarte ieftine nu sunt întotdeauna comparabile</h2><p>Două oferte pot folosi aceeași etichetă, dar să includă livrabile diferite. Un preț redus poate exclude redactarea, optimizarea imaginilor, măsurarea conversiilor, configurarea cookie-urilor sau suportul după lansare. Cere o listă clară de livrabile și află cine răspunde de fiecare etapă.</p><h2>Întrebări utile înainte să ceri oferta</h2><ol><li>Care este obiectivul comercial principal al website-ului?</li><li>Ce trebuie să poată face vizitatorul?</li><li>Cine furnizează textele și imaginile?</li><li>Ce sisteme trebuie conectate?</li><li>Cum vor fi măsurate cererile, apelurile sau vânzările?</li></ol><h2>Bugetul corect este cel legat de utilizare</h2><p>O estimare serioasă separă costul inițial de costurile recurente și explică ipotezele. La Cab-IT Expert evaluăm proiectul după scop, complexitate și responsabilități, apoi propunem o etapizare ușor de urmărit. Astfel poți compara soluțiile pe valoare și risc, nu doar pe suma de pornire.</p>',
            '/assets/img/case-studies/case-6.webp',
        ],
        [
            'Promovare online în București: cum alegi canalele potrivite',
            'Promovare online București: ghid pentru firme | Cab-IT',
            'Descoperă cum alegi între Google Ads, Meta Ads, TikTok și SEO pentru promovare online în București, cu obiective, măsurare și pași practici.',
            'promovare-online-bucuresti-ghid-firme',
            'Google Ads, Meta, TikTok sau SEO? Alegerea corectă pornește de la intenția clientului, ofertă, buget și modul în care măsori rezultatele.',
            '<h2>Începe cu obiectivul comercial</h2><p>Promovarea online nu înseamnă să fii prezent peste tot. Pentru o firmă din București, obiectivul poate fi obținerea de apeluri, cereri de ofertă, programări, vizite în locație sau vânzări online. Canalul se alege după comportamentul clientului și după cât de repede trebuie validată oferta.</p><h2>Când alegi Google Ads</h2><p>Google Ads este potrivit atunci când oamenii caută deja produsul sau serviciul. Campaniile trebuie împărțite pe intenții clare, conectate la pagini relevante și măsurate prin acțiuni reale. Clickurile singure nu arată dacă promovarea este profitabilă.</p><h2>Când alegi Meta sau TikTok</h2><p>Facebook, Instagram și TikTok ajută la descoperire, educare și remarketing. Sunt utile pentru oferte vizuale, audiențe bine definite și mesaje care pot fi testate rapid. Creativul, frecvența și pagina de destinație influențează rezultatul la fel de mult ca setarea audienței.</p><h2>Rolul SEO în planul pe termen lung</h2><p>SEO construiește vizibilitate pentru căutări relevante și reduce dependența de plata fiecărui click, însă are nevoie de fundație tehnică, conținut util și timp de evaluare. Pentru căutări locale sunt importante paginile serviciilor, informațiile consecvente despre firmă și profilul Google Business.</p><h2>Un mix simplu pentru început</h2><ol><li>definește oferta și conversia principală;</li><li>configurează măsurarea înainte de lansare;</li><li>alege un canal cu intenție ridicată;</li><li>testează mesajele și pagina de destinație;</li><li>compară leadurile și vânzările, nu doar traficul;</li><li>investește constant în paginile organice importante.</li></ol><h2>Cum evaluăm performanța</h2><p>Urmărim costul per cerere, calitatea leadurilor, rata de conversie și valoarea comercială. Rezultatele nu pot fi garantate înainte de testare, dar procesul poate fi transparent: ipoteze clare, tracking verificat, rapoarte ușor de înțeles și decizii documentate.</p><h2>Promovare locală, fără mesaje generice</h2><p>În București concurența și costurile diferă mult între industrii. De aceea, strategia trebuie construită în jurul zonei deservite, programului, capacității echipei și avantajului real al firmei. Cab-IT Expert combină promovarea plătită, SEO și optimizarea website-ului într-un plan măsurabil, adaptat fiecărui proiect.</p>',
            '/assets/img/case-studies/case-3.webp',
        ],
    ];
    $insertAdditionalArticle = $pdo->prepare('INSERT OR IGNORE INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if ($seedDemoArticles) {
        foreach ($additionalArticles as $article) {
            $insertAdditionalArticle->execute([$article[0], $article[1], $article[2], $article[3], $article[4], $article[5], $article[6], '2026-07-18', $now, $now]);
        }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM works')->fetchColumn() === 0) {
        $categoryIds = [];
        foreach ($pdo->query('SELECT id, slug FROM categories') as $category) {
            $categoryIds[$category['slug']] = (int) $category['id'];
        }
        $works = [
            ['Bilka Sistem', 'Studiu de caz Bilka Sistem | SEO și Google Ads', 'Proiect Bilka Sistem: audit, optimizare SEO și structurarea campaniilor Google Ads.', 'bilka-sistem', 'seo-reclame', 'Creșterea vizibilității organice și organizarea mai clară a campaniilor plătite.', 'Audit SEO, recomandări tehnice, structură de cuvinte cheie, campanii și măsurare.', 'Rezultatele cantitative trebuie completate cu datele validate de client.', '', 'https://bilka-sistem.ro', '/assets/img/case-studies/case-1.webp'],
            ['Lael Fashion', 'Studiu de caz Lael Fashion | Website și promovare', 'Website și promovare digitală pentru Lael Fashion.', 'lael-fashion', 'e-commerce', 'Construirea unei prezențe online coerente pentru un brand de fashion.', 'Website, structură de produse, SEO și recomandări pentru campanii.', 'Pagina documentează livrabilele; cifrele se publică numai după validare.', '', 'https://laelfashion.ro', '/assets/img/case-studies/case-2.webp'],
            ['Spălătoria Ozana', 'Studiu de caz Spălătoria Ozana | Website și SEO', 'Website și optimizare SEO locală pentru Spălătoria Ozana.', 'spalatoria-ozana', 'web-design', 'O prezentare clară a serviciilor și o bază tehnică pentru vizibilitate locală.', 'Website responsive, structură servicii și optimizări SEO de bază.', 'Performanța este urmărită prin datele de trafic și solicitări validate.', '', '', '/assets/img/case-studies/case-3.webp'],
            ['Best TKD', 'Studiu de caz Best TKD | Web Design', 'Website și administrare personalizată pentru Best TKD.', 'best-tkd', 'web-design', 'Un website ușor de administrat pentru prezentarea activității și programelor.', 'Design responsive, dezvoltare și panou de administrare personalizat.', 'Livrabilele și funcțiile sunt prezentate transparent în pagina proiectului.', '', 'https://best-tkd.ro', '/assets/img/case-studies/case-4.webp'],
            ['Traffic Restaurant & Lounge', 'Studiu de caz Traffic Restaurant | Website și Ads', 'Website, SEO și promovare online pentru Traffic Restaurant & Lounge.', 'traffic-restaurant', 'seo-reclame', 'Conectarea prezenței digitale cu rezervările și promovarea locală.', 'Website, SEO, Google Ads și campanii Meta, cu tracking pentru acțiuni relevante.', 'Cifrele comerciale se publică doar după aprobarea și validarea clientului.', '', 'https://trafficpub.ro', '/assets/img/case-studies/case-5.webp'],
            ['Nanu Events', 'Studiu de caz Nanu Events | Web Design', 'Website de prezentare pentru Nanu Events.', 'nanu-events', 'web-design', 'Prezentarea clară a serviciilor și proiectelor într-o interfață responsive.', 'Arhitectură de conținut, design și dezvoltare web.', 'Pagina proiectului descrie procesul și livrabilele realizate.', '', 'https://nanuevents.ro', '/assets/img/case-studies/case-6.webp'],
            ['Auto La Domiciliu', 'Studiu de caz Auto La Domiciliu | Website și SEO', 'Website, SEO și promovare plătită pentru Auto La Domiciliu.', 'auto-la-domiciliu', 'seo-reclame', 'Crearea unui traseu clar de la căutare la solicitarea serviciului.', 'Website, structură servicii, SEO și configurarea campaniilor plătite.', 'Datele cantitative trebuie completate din surse validate înainte de publicare.', '', 'https://autoladomiciliu.ro', '/img/case_study_1.webp'],
        ];
        $insert = $pdo->prepare('INSERT INTO works (title, seo_title, meta_description, slug, category_id, objective, work_done, results, testimonial, external_url, cover_image, date_added, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($works as $work) {
            $insert->execute([$work[0], $work[1], $work[2], $work[3], $categoryIds[$work[4]] ?? null, $work[5], $work[6], $work[7], $work[8], $work[9], $work[10], '2026-07-18', $now, $now]);
        }
    }
}

function cms_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cms_slug(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $value = strtr($value, ['ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't']);
    $value = preg_replace('/[^a-z0-9]+/u', '-', $value) ?? '';
    return trim($value, '-');
}

function cms_valid_slug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
}

function cms_sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    if ($html === strip_tags($html)) {
        return '<p>' . nl2br(cms_e($html)) . '</p>';
    }
    $allowed = '<p><h2><h3><h4><ul><ol><li><strong><em><u><s><blockquote><a><br><hr><pre><code><table><thead><tbody><tr><th><td><figure><figcaption><img><div><span><nav><section><details><summary><time>';
    $clean = strip_tags($html, $allowed);
    $document = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="utf-8" ?><div id="cms-root">' . $clean . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $xpath = new DOMXPath($document);
    $allowedClasses = ['cabit-rich-note', 'cabit-rich-cta', 'cabit-rich-columns', 'cabit-rich-button', 'cabit-rich-lead', 'cabit-answer-box', 'cabit-article-toc', 'cabit-article-faq', 'cabit-article-sources', 'cabit-related-guides', 'text-center'];
    foreach ($xpath->query('//*[@*]') ?: [] as $node) {
        $remove = [];
        foreach ($node->attributes as $attribute) {
            $name = strtolower($attribute->name);
            $tag = strtolower($node->nodeName);
            $value = trim($attribute->value);
            $allowedAttribute = false;
            if ($name === 'id' && (($tag === 'div' && $value === 'cms-root') || (in_array($tag, ['h2', 'h3', 'section'], true) && preg_match('/^[a-z0-9][a-z0-9-]*$/', $value)))) {
                $allowedAttribute = true;
            } elseif ($name === 'class') {
                $classes = array_values(array_intersect(preg_split('/\s+/', $value) ?: [], $allowedClasses));
                if ($classes) {
                    $node->setAttribute('class', implode(' ', $classes));
                    $allowedAttribute = true;
                }
            } elseif ($tag === 'a' && in_array($name, ['href', 'title', 'target', 'rel'], true)) {
                $allowedAttribute = $name !== 'href' || (bool) preg_match('~^(?:https?://|mailto:|tel:|/|#)~i', $value);
            } elseif ($tag === 'img' && in_array($name, ['src', 'alt', 'title', 'width', 'height', 'loading', 'decoding', 'fetchpriority'], true)) {
                $allowedAttribute = $name !== 'src' || (bool) preg_match('~^(?:https?://|/)~i', $value);
            } elseif ($tag === 'time' && $name === 'datetime') {
                $allowedAttribute = true;
            } elseif (in_array($tag, ['nav', 'section'], true) && $name === 'aria-label') {
                $allowedAttribute = true;
            } elseif (in_array($tag, ['th', 'td'], true) && in_array($name, ['colspan', 'rowspan', 'scope'], true)) {
                $allowedAttribute = true;
            }
            if (!$allowedAttribute) {
                $remove[] = $attribute->name;
            }
        }
        foreach ($remove as $attributeName) {
            $node->removeAttribute($attributeName);
        }
        if (strtolower($node->nodeName) === 'a' && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
        if (strtolower($node->nodeName) === 'img') {
            $node->setAttribute('loading', 'lazy');
            if ($node->getAttribute('alt') === '') {
                $node->setAttribute('alt', '');
            }
        }
    }
    $root = $document->getElementById('cms-root');
    if (!$root) {
        return '';
    }
    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $document->saveHTML($child);
    }
    return trim($result);
}

function cms_write_file(string $path, string $content): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Nu pot crea directorul pentru fișier.');
    }
    $handle = fopen($path, 'c+b');
    if ($handle === false) {
        throw new RuntimeException('Nu pot deschide fișierul pentru scriere: ' . $path);
    }
    try {
        if (!flock($handle, LOCK_EX) || !ftruncate($handle, 0) || !rewind($handle)) {
            throw new RuntimeException('Nu pot pregăti fișierul pentru scriere: ' . $path);
        }
        $remaining = $content;
        while ($remaining !== '') {
            $written = fwrite($handle, $remaining);
            if ($written === false || $written === 0) {
                throw new RuntimeException('Nu pot scrie fișierul: ' . $path);
            }
            $remaining = (string) substr($remaining, $written);
        }
        fflush($handle);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
    cms_gzip_file($path);
}

function cms_gzip_file(string $path): void
{
    if (!is_file($path) || !preg_match('/\.(?:html|xml|json|css|js)$/', $path)) {
        return;
    }
    $content = file_get_contents($path);
    if ($content !== false) {
        file_put_contents($path . '.gz', gzencode($content, 9), LOCK_EX);
    }
}

function cms_relative_asset(?string $path, int $depth = 2): string
{
    if (!$path) {
        return str_repeat('../', $depth) . 'assets/img/hero/hero-3.webp';
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return str_repeat('../', $depth) . ltrim($path, '/');
}

function cms_json(array $data): string
{
    return (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function cms_article_datetime(string $value): DateTimeImmutable
{
    $timezone = new DateTimeZone('Europe/Bucharest');
    try {
        return (new DateTimeImmutable($value, $timezone))->setTimezone($timezone);
    } catch (Throwable) {
        return new DateTimeImmutable('now', $timezone);
    }
}

function cms_article_date_label(string $value): string
{
    $format = preg_match('/T\d{2}:\d{2}/', $value) ? 'd.m.Y, H:i' : 'd.m.Y';
    return cms_article_datetime($value)->format($format);
}

function cms_google_tag_head(): string
{
    return '<script>window.__cabitGoogleTagLoaded=true;window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config","G-QPKXFL2GW9");gtag("config","AW-18343038330");gtag("config","AW-11509007584");gtag("config","AW-11509007584/6OY0CIeMyfsZEOCJ9u8q",{phone_conversion_number:"+40 771 532 949"});window.__cabitLoadGoogleTag=function(){if(window.__cabitGoogleTagRequested)return;window.__cabitGoogleTagRequested=true;var s=document.createElement("script");s.async=true;s.src="https://www.googletagmanager.com/gtag/js?id=AW-18343038330";document.head.appendChild(s)};["pointerdown","keydown","touchstart"].forEach(function(e){window.addEventListener(e,window.__cabitLoadGoogleTag,{once:true,passive:true})});window.addEventListener("load",function(){setTimeout(window.__cabitLoadGoogleTag,5000)},{once:true});window.gtag_report_conversion=function(url){window.__cabitLoadGoogleTag();var done=false;var go=function(){if(!done&&url){done=true;window.location.href=url}};gtag("event","conversion",{send_to:"AW-11509007584/GqVQCOudyvsZEOCJ9u8q",event_callback:go});setTimeout(go,1200);return false};</script>';
}

function cms_google_tag_body(): string
{
    return '';
}

function cms_agent_discovery_head(): string
{
    return '<link rel="alternate" type="text/plain" href="' . CABIT_SITE_URL . '/llms.txt" title="CAB-IT Expert — informații pentru agenți AI">';
}

function cms_rich_content(string $content): string
{
    $content = trim($content);
    if ($content === '') {
        return '';
    }
    if ($content === strip_tags($content)) {
        return '<p>' . nl2br(cms_e($content)) . '</p>';
    }
    return cms_sanitize_html($content);
}

function cms_related_links(string $heading, array $items, string $label): string
{
    $links = '';
    foreach ($items as [$eyebrow, $title, $url]) {
        $links .= '<a href="' . cms_e($url) . '"><span>' . cms_e($eyebrow) . '</span><strong>' . cms_e($title) . '</strong></a>';
    }
    return '<section class="cabit-content-section is-soft"><div class="container"><div class="cabit-service-section-head"><span class="cabit-eyebrow">Resurse utile</span><h2>' . cms_e($heading) . '</h2></div><nav class="cabit-service-related" aria-label="' . cms_e($label) . '">' . $links . '</nav></div></section>';
}

function cms_article_related(string $slug): string
{
    $maps = [
        'administrare-facebook-instagram-pentru-firme' => [['Serviciu', 'Administrare social media', '/servicii/social-media/'], ['Ghid', 'Cum alegi canalele de promovare', '/blog/promovare-online-romania-canale-potrivite/'], ['Proiect', 'Traffic Restaurant & Lounge', '/portofoliu/traffic-restaurant/']],
        'agenti-ai-pentru-firme' => [['Serviciu', 'Automatizări și agenți AI', '/servicii/integrari-digitale/'], ['Ghid', 'Automatizarea proceselor într-o firmă', '/blog/automatizarea-proceselor-intr-o-firma/'], ['Analiză', 'Audit și analiză digitală', '/servicii/analiza-digitala/']],
        'automatizarea-proceselor-intr-o-firma' => [['Serviciu', 'Automatizări și integrări', '/servicii/integrari-digitale/'], ['Ghid', 'Agenți AI pentru firme', '/blog/agenti-ai-pentru-firme/'], ['Analiză', 'Identifică procesele prioritare', '/servicii/analiza-digitala/']],
        'cat-costa-un-site-web-profesional' => [['Serviciu', 'Creare site web București', '/servicii/creare-site-web/'], ['Ghid', 'Ce trebuie să conțină site-ul unei firme', '/blog/creare-site-web-pentru-firme/'], ['Prețuri', 'Pachete și puncte de pornire', '/preturi/']],
        'creare-magazin-online-functionalitati-cost' => [['Serviciu', 'Creare website și magazin online', '/servicii/creare-site-web/'], ['Ghid', 'Cât costă un site web profesional', '/blog/cat-costa-un-site-web-profesional/'], ['Proiect', 'Magazinul online IFY.ro', '/portofoliu/ify-ro/']],
        'creare-site-web-pentru-firme' => [['Serviciu', 'Creare site web București', '/servicii/creare-site-web/'], ['Ghid', 'Semne că website-ul are nevoie de redesign', '/blog/redesign-website-semne-pierde-clienti/'], ['Proiect', 'Website-ul Nanu Events', '/portofoliu/nanu-events/']],
        'optimizare-seo-pentru-site' => [['Serviciu', 'Servicii SEO București', '/servicii/seo/'], ['Ghid', 'SEO local în București', '/blog/seo-local-bucuresti/'], ['Proiect', 'SEO și Google Ads pentru Bilka Sistem', '/portofoliu/bilka-sistem/']],
        'promovare-online-romania-canale-potrivite' => [['Serviciu', 'Google, Meta și TikTok Ads', '/servicii/reclame-platite/'], ['Ghid', 'SEO sau Google Ads?', '/blog/seo-sau-google-ads/'], ['Proiect', 'Promovarea Traffic Restaurant', '/portofoliu/traffic-restaurant/']],
        'promovare-pe-google-campanii-care-aduc-clienti' => [['Serviciu', 'Administrare Google Ads', '/servicii/reclame-platite/'], ['Ghid', 'SEO sau Google Ads?', '/blog/seo-sau-google-ads/'], ['Proiect', 'Campanii pentru Auto La Domiciliu', '/portofoliu/auto-la-domiciliu/']],
        'redesign-website-semne-pierde-clienti' => [['Serviciu', 'Optimizarea conversiilor', '/servicii/optimizare-conversii/'], ['Serviciu', 'Creare website', '/servicii/creare-site-web/'], ['Ghid', 'Ce trebuie să conțină site-ul unei firme', '/blog/creare-site-web-pentru-firme/']],
        'seo-local-bucuresti' => [['Serviciu', 'SEO local București și Ilfov', '/servicii/seo-local/'], ['Ghid', 'Optimizare SEO pentru site', '/blog/optimizare-seo-pentru-site/'], ['Proiect', 'SEO local pentru Spălătoria Ozana', '/portofoliu/spalatoria-ozana/']],
        'seo-sau-google-ads' => [['Serviciu', 'Optimizare SEO', '/servicii/seo/'], ['Serviciu', 'Administrare Google Ads', '/servicii/reclame-platite/'], ['Proiect', 'SEO și Ads pentru Bilka Sistem', '/portofoliu/bilka-sistem/']],
    ];
    $items = $maps[$slug] ?? [['Servicii', 'Toate serviciile digitale', '/servicii/'], ['Ghiduri', 'Articole de marketing digital', '/blog/'], ['Proiecte', 'Studii de caz CAB-IT', '/portofoliu/']];
    return cms_related_links('Continuă cu următorul pas', $items, 'Articole, servicii și proiecte conexe');
}

function cms_work_related(string $category): string
{
    if (str_contains($category, 'E-commerce')) {
        $items = [['Serviciu', 'Creare magazin online', '/servicii/creare-site-web/'], ['Ghid', 'Funcții și cost pentru un magazin online', '/blog/creare-magazin-online-functionalitati-cost/'], ['Prețuri', 'Pachete pentru website și e-commerce', '/preturi/']];
    } elseif (str_contains($category, 'SEO')) {
        $items = [['Serviciu', 'Optimizare SEO', '/servicii/seo/'], ['Serviciu', 'Google și Social Ads', '/servicii/reclame-platite/'], ['Ghid', 'SEO sau Google Ads?', '/blog/seo-sau-google-ads/']];
    } else {
        $items = [['Serviciu', 'Creare website', '/servicii/creare-site-web/'], ['Serviciu', 'Optimizare SEO', '/servicii/seo/'], ['Ghid', 'Ce trebuie să conțină site-ul unei firme', '/blog/creare-site-web-pentru-firme/']];
    }
    return cms_related_links('Servicii și resurse folosite în proiecte similare', $items, 'Servicii și ghiduri conexe proiectului');
}

function cms_article_metadata(array $article): array
{
    $metadata = json_decode((string) ($article['seo_metadata'] ?? '{}'), true);
    return is_array($metadata) ? $metadata : [];
}

function cms_article_has_cover_image(array $article): bool
{
    $path = trim((string) ($article['cover_image'] ?? ''));
    if ($path === '') {
        return false;
    }
    if (preg_match('~^https?://~i', $path)) {
        return true;
    }
    return is_file(CABIT_PUBLIC_ROOT . '/' . ltrim($path, '/'));
}

function cms_article_image_url(array $article): string
{
    if (!cms_article_has_cover_image($article)) {
        return '';
    }
    $path = (string) $article['cover_image'];
    return preg_match('~^https?://~i', $path) ? $path : CABIT_SITE_URL . '/' . ltrim($path, '/');
}

function cms_article_visual_theme(array $article, array $metadata = []): string
{
    $parts = [
        (string) ($article['title'] ?? ''),
        (string) ($metadata['cluster'] ?? ''),
        (string) ($metadata['pillar'] ?? ''),
        (string) ($metadata['primary_keyword'] ?? ''),
        implode(' ', array_map('strval', is_array($metadata['secondary_keywords'] ?? null) ? $metadata['secondary_keywords'] : [])),
    ];
    $text = mb_strtolower(implode(' ', $parts), 'UTF-8');
    if (preg_match('/magazin|e[ -]?commerce|shopify|woocommerce|shopping|checkout|produs/u', $text)) {
        return 'commerce';
    }
    if (preg_match('/seo local|google business|google maps|căutări locale|cautari locale|recenzii locale/u', $text)) {
        return 'local';
    }
    if (preg_match('/google ads|meta ads|facebook ads|tiktok|reclame|promovare online|paid media|performance max/u', $text)) {
        return 'ads';
    }
    if (preg_match('/inteligen|\bai\b|chatbot|automatizare|agent ai|machine learning/u', $text)) {
        return 'ai';
    }
    if (preg_match('/analytics|tracking|măsur|masur|conversi|crm|lead|atribuire|tag manager/u', $text)) {
        return 'analytics';
    }
    if (preg_match('/\bseo\b|search|indexare|cuvinte.cheie|link building|organic/u', $text)) {
        return 'seo';
    }
    if (preg_match('/website|site web|wordpress|web design|ux|ui|landing page|redesign/u', $text)) {
        return 'web';
    }
    return 'strategy';
}

function cms_article_visual_icon(string $theme): string
{
    return match ($theme) {
        'commerce' => '<svg viewBox="0 0 96 72" aria-hidden="true"><path d="M25 24h48l-4 37H29z"/><path d="M36 27c0-11 5-17 12-17s12 6 12 17"/><path d="M37 43h22M48 34v18"/></svg>',
        'local' => '<svg viewBox="0 0 96 72" aria-hidden="true"><path d="M18 58h60M24 58V31l24-13 24 13v27"/><path d="M34 39h8m12 0h8M34 49h8m12 0h8"/><circle cx="69" cy="18" r="9"/><path d="m76 25 8 8"/></svg>',
        'ads' => '<svg viewBox="0 0 96 72" aria-hidden="true"><circle cx="43" cy="36" r="24"/><circle cx="43" cy="36" r="14"/><circle cx="43" cy="36" r="4"/><path d="m61 20 18-10-7 20-11-10Z"/></svg>',
        'ai' => '<svg viewBox="0 0 96 72" aria-hidden="true"><rect x="24" y="15" width="48" height="42" rx="12"/><circle cx="39" cy="35" r="4"/><circle cx="57" cy="35" r="4"/><path d="M37 47h22M48 15V7M18 28h6m48 0h6M18 45h6m48 0h6"/></svg>',
        'analytics' => '<svg viewBox="0 0 96 72" aria-hidden="true"><path d="M18 58h62M24 54V41h12v13m8 0V29h12v25m8 0V17h12v37"/><path d="m24 33 15-10 14 4 22-17"/></svg>',
        'seo' => '<svg viewBox="0 0 96 72" aria-hidden="true"><circle cx="39" cy="32" r="20"/><path d="m54 47 21 17M29 36l8-8 7 6 12-14"/></svg>',
        'web' => '<svg viewBox="0 0 96 72" aria-hidden="true"><rect x="12" y="12" width="62" height="43" rx="7"/><path d="M12 23h62M22 18h1m7 0h1m7 0h1M31 62h24M43 55v7"/><rect x="63" y="31" width="22" height="35" rx="5"/></svg>',
        default => '<svg viewBox="0 0 96 72" aria-hidden="true"><circle cx="24" cy="36" r="8"/><circle cx="48" cy="18" r="8"/><circle cx="72" cy="36" r="8"/><circle cx="48" cy="56" r="8"/><path d="m30 31 12-9m12 0 12 9m0 10-12 11m-12 0L30 41"/></svg>',
    };
}

function cms_article_visual_markup(array $article, array $metadata, int $depth, string $context = 'card'): string
{
    $imageAlt = trim((string) ($metadata['image_alt'] ?? $article['title'] ?? 'Articol CAB-IT'));
    if (cms_article_has_cover_image($article)) {
        return '<div class="cabit-article-visual cabit-article-visual--' . cms_e($context) . ' has-image"><img src="' . cms_e(cms_relative_asset((string) $article['cover_image'], $depth)) . '" alt="' . cms_e($imageAlt) . '" width="1200" height="630" loading="' . ($context === 'hero' ? 'eager' : 'lazy') . '" decoding="async"></div>';
    }
    $theme = cms_article_visual_theme($article, $metadata);
    $labels = ['commerce' => 'E-commerce', 'local' => 'SEO local', 'ads' => 'Promovare', 'ai' => 'AI & automatizare', 'analytics' => 'Date & conversii', 'seo' => 'SEO', 'web' => 'Web design', 'strategy' => 'Strategie digitală'];
    $label = $labels[$theme] ?? $labels['strategy'];
    return '<div class="cabit-article-visual cabit-article-visual--' . cms_e($context) . ' is-fallback is-theme-' . cms_e($theme) . '" role="img" aria-label="Reprezentare vizuală CAB-IT pentru ' . cms_e((string) ($article['title'] ?? $label)) . '"><span>Ghid CAB-IT</span>' . cms_article_visual_icon($theme) . '<strong>' . cms_e($label) . '</strong><i aria-hidden="true"></i><b aria-hidden="true"></b></div>';
}

function cms_article_card_markup(array $article, int $depth = 1, bool $priority = false): string
{
    $metadata = cms_article_metadata($article);
    $label = trim((string) ($metadata['cluster'] ?? 'Articol'));
    $city = trim((string) ($metadata['city'] ?? ''));
    if ($city !== '' && mb_stripos($label, $city, 0, 'UTF-8') === false) {
        $label .= ' · ' . $city;
    }
    $date = cms_article_date_label((string) $article['date_published']);
    $classes = 'cabit-blog-card' . ($priority ? ' is-home-priority' : '') . (cms_article_has_cover_image($article) ? ' has-cover-image' : ' has-fallback-visual');
    $priorityBadge = $priority ? '<span class="cabit-blog-card__priority">Recomandat CAB-IT</span>' : '';
    return '<article class="' . $classes . '"><a class="cabit-blog-card__link" href="/blog/' . cms_e($article['slug']) . '/">' . cms_article_visual_markup($article, $metadata, $depth, 'card') . '<div class="cabit-blog-card__body">' . $priorityBadge . '<div class="cabit-blog-card__meta"><span>' . cms_e($label) . '</span><time datetime="' . cms_e($article['date_published']) . '">' . cms_e($date) . '</time></div><h3>' . cms_e($article['title']) . '</h3><p>' . cms_e($article['excerpt']) . '</p><span class="cabit-text-link">Citește articolul <span aria-hidden="true">→</span></span></div></a></article>';
}

function cms_article_metadata_related(array $metadata): string
{
    $items = [];
    foreach (($metadata['related_articles'] ?? []) as $item) {
        if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
            continue;
        }
        $items[] = [
            (string) ($item['label'] ?? 'Ghid conex'),
            (string) $item['title'],
            (string) $item['url'],
        ];
    }
    return $items ? cms_related_links('Continuă cu ghidurile din același subiect', array_slice($items, 0, 3), 'Ghiduri conexe din același cluster editorial') : '';
}

function cms_recommendation_tokens(string $value): array
{
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $value = strtr($value, ['ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't']);
    $tokens = preg_split('/[^a-z0-9]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $stopWords = ['acest', 'aceasta', 'aceste', 'acesti', 'care', 'cand', 'cum', 'din', 'intr', 'pentru', 'prin', 'sau', 'este', 'sunt', 'unui', 'unei', 'fara', 'ghid', 'romania', '2026'];
    return array_values(array_unique(array_filter($tokens, static fn(string $token): bool => strlen($token) >= 3 && !in_array($token, $stopWords, true))));
}

function cms_article_pillar(array $metadata): array
{
    $context = function_exists('mb_strtolower')
        ? mb_strtolower((string) ($metadata['cluster'] ?? '') . ' ' . (string) ($metadata['primary_keyword'] ?? ''), 'UTF-8')
        : strtolower((string) ($metadata['cluster'] ?? '') . ' ' . (string) ($metadata['primary_keyword'] ?? ''));
    if (preg_match('/seo local|google maps|business profile/u', $context)) {
        return ['/servicii/seo-local/', 'Vezi serviciile de SEO local'];
    }
    if (preg_match('/seo|organic|search|conținut|continut|ai overview|geo|aeo/u', $context)) {
        return ['/servicii/seo/', 'Vezi serviciile SEO'];
    }
    if (preg_match('/google ads|meta ads|tiktok|paid|reclam|promovare/u', $context)) {
        return ['/servicii/reclame-platite/', 'Vezi serviciile de promovare online'];
    }
    if (preg_match('/automatiz|agent ai|crm|tracking|analytics|consent/u', $context)) {
        return ['/servicii/integrari-digitale/', 'Vezi automatizările și integrările digitale'];
    }
    if (preg_match('/website|web design|e-commerce|magazin|shopify|woocommerce|cms|landing|merchant|shopping|catalog|produs|furnizor/u', $context)) {
        return ['/servicii/creare-site-web/', 'Vezi serviciile de web design și creare website'];
    }
    return ['/servicii/', 'Vezi serviciile agenției de marketing online'];
}

function cms_smart_article_recommendations(array $article, array $metadata): string
{
    $statement = cms_db()->prepare('SELECT * FROM articles WHERE id <> ? ORDER BY date_published DESC, created_at DESC, id DESC');
    $statement->execute([(int) $article['id']]);
    $candidates = $statement->fetchAll();
    $cluster = trim((string) ($metadata['cluster'] ?? ''));
    $sourceKeywords = array_values(array_filter(array_merge(
        [(string) ($metadata['primary_keyword'] ?? '')],
        array_map('strval', is_array($metadata['secondary_keywords'] ?? null) ? $metadata['secondary_keywords'] : []),
        array_map('strval', is_array($metadata['queries'] ?? null) ? $metadata['queries'] : []),
        array_map('strval', is_array($metadata['entities'] ?? null) ? $metadata['entities'] : [])
    )));
    $sourceTokens = cms_recommendation_tokens((string) $article['title'] . ' ' . implode(' ', $sourceKeywords));
    $curatedUrls = [];
    foreach (($metadata['related_articles'] ?? []) as $position => $item) {
        if (is_array($item) && !empty($item['url'])) {
            $curatedUrls[(string) parse_url((string) $item['url'], PHP_URL_PATH)] = max(0, 3 - (int) $position);
        }
    }

    $ranked = [];
    foreach ($candidates as $candidate) {
        $candidateMetadata = cms_article_metadata($candidate);
        $candidateCluster = trim((string) ($candidateMetadata['cluster'] ?? ''));
        $candidateKeywords = array_values(array_filter(array_merge(
            [(string) ($candidateMetadata['primary_keyword'] ?? '')],
            array_map('strval', is_array($candidateMetadata['secondary_keywords'] ?? null) ? $candidateMetadata['secondary_keywords'] : []),
            array_map('strval', is_array($candidateMetadata['queries'] ?? null) ? $candidateMetadata['queries'] : []),
            array_map('strval', is_array($candidateMetadata['entities'] ?? null) ? $candidateMetadata['entities'] : [])
        )));
        $candidateTokens = cms_recommendation_tokens((string) $candidate['title'] . ' ' . implode(' ', $candidateKeywords));
        $candidateUrl = '/blog/' . $candidate['slug'] . '/';
        $score = ($cluster !== '' && $cluster === $candidateCluster) ? 60 : 0;
        if (($metadata['city'] ?? '') !== '' && ($metadata['city'] ?? '') === ($candidateMetadata['city'] ?? '')) {
            $score += 120;
        }
        if (($metadata['industry'] ?? '') !== '' && ($metadata['industry'] ?? '') === ($candidateMetadata['industry'] ?? '')) {
            $score += 70;
        }
        if (($metadata['angle'] ?? '') !== '' && ($metadata['angle'] ?? '') === ($candidateMetadata['angle'] ?? '')) {
            $score += 35;
        }
        $score += count(array_intersect($sourceTokens, $candidateTokens)) * 14;
        if (isset($curatedUrls[$candidateUrl])) {
            $score += 180 + ($curatedUrls[$candidateUrl] * 10);
        }
        if ($score <= 0) {
            continue;
        }
        $ranked[] = ['score' => $score, 'article' => $candidate, 'metadata' => $candidateMetadata];
    }

    usort($ranked, static function (array $left, array $right): int {
        return $right['score'] <=> $left['score']
            ?: strcmp((string) $right['article']['date_published'], (string) $left['article']['date_published'])
            ?: ((int) $right['article']['id'] <=> (int) $left['article']['id']);
    });

    $cards = '';
    foreach (array_slice($ranked, 0, 3) as $recommendation) {
        $candidate = $recommendation['article'];
        $candidateMetadata = $recommendation['metadata'];
        $candidateCluster = (string) ($candidateMetadata['cluster'] ?? 'Ghid CAB-IT');
        $cards .= '<a class="cabit-smart-related-card' . (cms_article_has_cover_image($candidate) ? ' has-cover-image' : ' has-fallback-visual') . '" href="/blog/' . cms_e($candidate['slug']) . '/"><figure>' . cms_article_visual_markup($candidate, $candidateMetadata, 2, 'related') . '</figure><span>' . cms_e($candidateCluster) . '</span><strong>' . cms_e($candidate['title']) . '</strong><small>Citește ghidul <b aria-hidden="true">→</b></small></a>';
    }

    if ($cards === '') {
        return cms_article_related((string) $article['slug']);
    }

    [$pillarUrl, $pillarLabel] = cms_article_pillar($metadata);
    return '<section class="cabit-content-section is-soft cabit-smart-related-section"><div class="container"><div class="cabit-service-section-head"><span class="cabit-eyebrow">Recomandări pentru tine</span><h2>Continuă cu ghidurile potrivite întrebării tale</h2><p>Selectate după subiect, intenție și cuvintele-cheie comune cu articolul citit.</p><a class="cabit-smart-related-pillar" href="' . cms_e($pillarUrl) . '">' . cms_e($pillarLabel) . ' <span aria-hidden="true">→</span></a></div><nav class="cabit-smart-related-grid" aria-label="Articole recomandate inteligent">' . $cards . '</nav></div></section>';
}

function cms_article_page(array $article): string
{
    $metadata = cms_article_metadata($article);
    $title = cms_e($article['title']);
    $seoTitle = cms_e($article['seo_title']);
    $description = cms_e($article['meta_description']);
    $slug = cms_e($article['slug']);
    $hasImage = cms_article_has_cover_image($article);
    $imageUrl = cms_article_image_url($article);
    $imageAlt = (string) ($metadata['image_alt'] ?? $article['title']);
    $cluster = trim((string) ($metadata['cluster'] ?? 'Ghid CAB-IT Expert'));
    $articleUrl = CABIT_SITE_URL . '/blog/' . $article['slug'] . '/';
    $publishedDate = cms_article_date_label((string) $article['date_published']);
    $modifiedIso = substr((string) $article['updated_at'], 0, 10) >= CABIT_SEO_RELEASE_DATE
        ? (string) $article['updated_at']
        : CABIT_SEO_RELEASE_DATE;
    $modifiedDate = date('d.m.Y', strtotime($modifiedIso));
    $keywords = array_values(array_unique(array_filter(array_merge(
        [(string) ($metadata['primary_keyword'] ?? '')],
        array_map('strval', is_array($metadata['secondary_keywords'] ?? null) ? $metadata['secondary_keywords'] : [])
    ))));
    $authorMetadata = is_array($metadata['author'] ?? null) ? $metadata['author'] : [];
    $hasNamedAuthor = trim((string) ($authorMetadata['name'] ?? '')) !== '';
    $authorName = $hasNamedAuthor ? trim((string) $authorMetadata['name']) : 'Echipa CAB-IT Expert';
    $authorRole = trim((string) ($authorMetadata['role'] ?? 'Coordonator editorial CAB-IT Expert'));
    $authorBio = trim((string) ($authorMetadata['bio'] ?? 'Documentează ghiduri despre website-uri, SEO, promovare online și măsurarea rezultatelor pentru firme.'));
    $authorUrl = CABIT_SITE_URL . '/despre-noi/' . ($hasNamedAuthor ? '#alexie-popescu' : '');
    $authorSchema = $hasNamedAuthor
        ? ['@type' => 'Person', '@id' => CABIT_SITE_URL . '/despre-noi/#alexie-popescu', 'name' => $authorName, 'url' => $authorUrl, 'jobTitle' => $authorRole, 'worksFor' => ['@id' => CABIT_SITE_URL . '/#organization']]
        : ['@type' => 'Organization', '@id' => CABIT_SITE_URL . '/#organization', 'name' => $authorName, 'url' => CABIT_SITE_URL . '/despre-noi/'];
    preg_match_all('/\p{L}[\p{L}\p{N}’-]*/u', strip_tags((string) $article['content']), $wordMatches);
    $articleSchema = [
        '@type' => 'BlogPosting', '@id' => $articleUrl . '#article', 'headline' => $article['title'],
        'description' => $article['meta_description'], 'url' => $articleUrl,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $articleUrl],
        'datePublished' => $article['date_published'], 'dateModified' => $modifiedIso,
        'author' => $authorSchema,
        'publisher' => ['@type' => 'Organization', '@id' => CABIT_SITE_URL . '/#organization', 'name' => 'CAB-IT Expert', 'url' => CABIT_SITE_URL . '/', 'logo' => ['@type' => 'ImageObject', 'url' => CABIT_SITE_URL . '/assets/img/brand/cab-it-c-symbol-app-v7.png', 'width' => 192, 'height' => 192]],
        'isPartOf' => ['@type' => 'Blog', '@id' => CABIT_SITE_URL . '/blog/'],
        'articleSection' => $cluster,
        'wordCount' => count($wordMatches[0]),
        'inLanguage' => 'ro-RO',
    ];
    if ($hasImage) {
        $articleSchema['image'] = ['@type' => 'ImageObject', '@id' => $articleUrl . '#primaryimage', 'url' => $imageUrl, 'contentUrl' => $imageUrl, 'width' => 1200, 'height' => 630, 'caption' => $imageAlt];
    }
    if ($keywords) {
        $articleSchema['keywords'] = $keywords;
        $articleSchema['about'] = array_map(static fn(string $keyword): array => ['@type' => 'Thing', 'name' => $keyword], array_slice($keywords, 0, 6));
    }
    if (trim((string) ($metadata['city'] ?? '')) !== '') {
        $articleSchema['spatialCoverage'] = [
            '@type' => 'Place',
            'name' => trim((string) $metadata['city']) . ', România',
        ];
    }
    $schemaGraph = [
        $articleSchema,
        [
            '@type' => 'BreadcrumbList', '@id' => $articleUrl . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Acasă', 'item' => CABIT_SITE_URL . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => CABIT_SITE_URL . '/blog/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $article['title'], 'item' => $articleUrl],
            ],
        ],
    ];
    $faqs = [];
    foreach (($metadata['faqs'] ?? []) as $faq) {
        $question = trim((string) ($faq['q'] ?? ''));
        $answer = trim((string) ($faq['a'] ?? ''));
        if ($question !== '' && $answer !== '') {
            $faqs[] = ['@type' => 'Question', 'name' => $question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer]];
        }
    }
    if ($faqs) {
        $schemaGraph[] = ['@type' => 'FAQPage', '@id' => $articleUrl . '#faq', 'mainEntity' => $faqs];
    }
    $schema = cms_json(['@context' => 'https://schema.org', '@graph' => $schemaGraph]);
    $authorCard = $hasNamedAuthor
        ? '<section class="cabit-author-card" id="autor"><span class="cabit-author-card__avatar" aria-hidden="true">AP</span><div><span>Autor și revizie editorială</span><h2>' . cms_e($authorName) . '</h2><p><strong>' . cms_e($authorRole) . '.</strong> ' . cms_e($authorBio) . '</p><a href="/despre-noi/#alexie-popescu">Despre autor și metodologia editorială →</a></div></section>'
        : '';
    $related = cms_smart_article_recommendations($article, $metadata);
    $socialImageMeta = $hasImage
        ? '<meta property="og:image" content="' . cms_e($imageUrl) . '"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta property="og:image:alt" content="' . cms_e($imageAlt) . '"><meta name="twitter:card" content="summary_large_image">'
        : '<meta name="twitter:card" content="summary">';
    return '<!doctype html><html lang="ro-RO"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">' .
        '<title>' . $seoTitle . '</title><meta name="description" content="' . $description . '"><meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">' .
        '<link rel="canonical" href="' . $articleUrl . '"><link rel="alternate" type="application/rss+xml" title="Blog CAB-IT Expert" href="' . CABIT_SITE_URL . '/feed.xml">' . cms_agent_discovery_head() . '<meta property="og:type" content="article"><meta property="og:title" content="' . $seoTitle . '"><meta property="og:description" content="' . $description . '"><meta property="og:url" content="' . $articleUrl . '">' . $socialImageMeta . '<meta property="article:published_time" content="' . cms_e($article['date_published']) . '"><meta property="article:modified_time" content="' . cms_e($modifiedIso) . '">' .
        '<link rel="shortcut icon" type="image/png" href="../../assets/img/brand/cab-it-c-symbol-tab-v7.png"><link rel="icon" type="image/png" sizes="48x48" href="../../assets/img/brand/cab-it-c-symbol-tab-v7.png"><link rel="icon" type="image/png" sizes="192x192" href="../../assets/img/brand/cab-it-c-symbol-app-v7.png"><link rel="apple-touch-icon" sizes="192x192" href="../../assets/img/brand/cab-it-c-symbol-app-v7.png"><link rel="manifest" href="../../site.webmanifest"><link rel="stylesheet" href="../../assets/css/site.min.css?v=20260819-4"><link rel="stylesheet" href="../../assets/css/cabit-next.min.css?v=20260820-1"><script type="application/ld+json">' . $schema . '</script>' . cms_google_tag_head() . '</head><body class="cabit-theme-2026 cabit-page-article cabit-inner-page">' . cms_google_tag_body() .
        '<main><article><nav class="cabit-article-breadcrumb" aria-label="Breadcrumb"><a href="/">Acasă</a><span aria-hidden="true">/</span><a href="/blog/">Blog</a><span aria-hidden="true">/</span><span>' . $title . '</span></nav><header class="cabit-page-header cabit-editorial-hero"><div class="container"><div><span class="cabit-eyebrow">' . cms_e($cluster) . '</span><h1>' . $title . '</h1><p>' . cms_e($article['excerpt']) . '</p><p class="cabit-article-date">Publicat la <time datetime="' . cms_e($article['date_published']) . '">' . cms_e($publishedDate) . '</time> · actualizat la <time datetime="' . cms_e($modifiedIso) . '">' . cms_e($modifiedDate) . '</time> · scris și revizuit de <a href="' . cms_e(parse_url($authorUrl, PHP_URL_PATH) . (parse_url($authorUrl, PHP_URL_FRAGMENT) ? '#' . parse_url($authorUrl, PHP_URL_FRAGMENT) : '')) . '">' . cms_e($authorName) . '</a></p></div><figure class="' . ($hasImage ? 'has-cover-image' : 'has-fallback-visual') . '">' . cms_article_visual_markup($article, $metadata, 2, 'hero') . '</figure></div></header>' .
        '<section class="cabit-content-section"><div class="container cabit-case-layout"><div><div class="cabit-content-card cabit-article-content">' . $article['content'] . '</div>' . $authorCard . '</div><aside class="cabit-sticky-aside"><div class="cabit-note"><strong>Ai nevoie de o strategie aplicată?</strong><p>Analizăm obiectivul, website-ul și canalele potrivite afacerii tale.</p><a class="button button-primary" href="/contact/">Hai să discutăm</a></div><a class="cabit-text-link" href="/blog/">← Toate articolele</a></aside></div></section>' . $related . '<section class="cabit-inner-cta section-shell cabit-article-final-cta"><div class="cabit-article-final-cta__copy"><span>Ai găsit răspunsul?</span><h2>Transformă ideea într-un plan clar.</h2><p>Discutăm obiectivul, alegem pașii potriviți și stabilim ce merită măsurat.</p><a class="button button-primary" href="/contact/">Hai să construim planul <b aria-hidden="true">→</b></a></div><div class="cabit-article-final-cta__visual" aria-hidden="true"><i></i><i></i><i></i><strong>?</strong><b>✓</b></div></section></article></main>' .
        '<script src="../../assets/js/site-enhancements.js?v=20260819-4"></script><script defer src="../../assets/js/cabit-next.min.js?v=20260819-20"></script></body></html>';
}

function cms_generate_article(array $article): void
{
    $path = CABIT_PUBLIC_ROOT . '/blog/' . $article['slug'] . '/index.html';
    cms_write_file($path, cms_article_page($article));
}

function cms_work_page(PDO $pdo, array $work): string
{
    $images = cms_work_images($pdo, (int) $work['id']);
    $allImages = [];
    if (!empty($work['cover_image'])) {
        $allImages[] = ['path' => $work['cover_image'], 'alt_text' => $work['title']];
    }
    foreach ($images as $image) {
        if (!in_array($image['path'], array_column($allImages, 'path'), true)) {
            $allImages[] = $image;
        }
    }
    $gallery = '';
    foreach ($allImages as $image) {
        $gallery .= '<figure><img src="' . cms_e(cms_relative_asset($image['path'], 2)) . '" alt="' . cms_e($image['alt_text'] ?: $work['title']) . '" loading="lazy" decoding="async"></figure>';
    }
    $schema = cms_json([
        '@context' => 'https://schema.org', '@type' => 'CreativeWork', 'name' => $work['title'],
        'description' => $work['meta_description'], 'url' => CABIT_SITE_URL . '/portofoliu/' . $work['slug'] . '/',
        'dateCreated' => $work['date_added'], 'dateModified' => max(substr((string) $work['updated_at'], 0, 10), CABIT_SEO_RELEASE_DATE), 'creator' => ['@id' => CABIT_SITE_URL . '/#organization'],
        'image' => array_map(fn($image) => str_starts_with($image['path'], 'http') ? $image['path'] : CABIT_SITE_URL . '/' . ltrim($image['path'], '/'), $allImages),
        'inLanguage' => 'ro-RO',
    ]);
    $external = $work['external_url'] !== '' ? '<a class="cabit-text-link" href="' . cms_e($work['external_url']) . '" rel="noopener" target="_blank">Vezi website-ul proiectului →</a>' : '';
    $testimonial = $work['testimonial'] !== '' ? '<section class="cabit-content-section is-soft"><div class="container"><div class="cabit-content-card cabit-testimonial-card"><span class="cabit-eyebrow">Recenzie client</span><h2>Experiența colaborării</h2><blockquote>' . cms_rich_content($work['testimonial']) . '</blockquote></div></div></section>' : '';
    return '<!doctype html><html lang="ro-RO"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">' .
        '<title>' . cms_e($work['seo_title']) . '</title><meta name="description" content="' . cms_e($work['meta_description']) . '"><meta name="robots" content="index, follow, max-image-preview:large"><link rel="canonical" href="' . CABIT_SITE_URL . '/portofoliu/' . cms_e($work['slug']) . '/">' . cms_agent_discovery_head() .
        '<link rel="shortcut icon" type="image/png" href="../../assets/img/brand/cab-it-c-symbol-tab-v7.png"><link rel="icon" type="image/png" sizes="48x48" href="../../assets/img/brand/cab-it-c-symbol-tab-v7.png"><link rel="icon" type="image/png" sizes="192x192" href="../../assets/img/brand/cab-it-c-symbol-app-v7.png"><link rel="apple-touch-icon" sizes="192x192" href="../../assets/img/brand/cab-it-c-symbol-app-v7.png"><link rel="manifest" href="../../site.webmanifest"><link rel="stylesheet" href="../../assets/css/site.min.css?v=20260819-4"><link rel="stylesheet" href="../../assets/css/cabit-next.min.css?v=20260819-13"><script type="application/ld+json">' . $schema . '</script>' . cms_google_tag_head() . '</head><body class="cabit-theme-2026 cabit-page-case cabit-inner-page">' . cms_google_tag_body() .
        '<main><section class="cabit-page-header cabit-case-hero"><div class="container"><div><span class="cabit-eyebrow">Studiu de caz · ' . cms_e($work['category_name'] ?? 'Portofoliu') . '</span><h1>' . cms_e($work['title']) . '</h1><p>' . cms_e($work['meta_description']) . '</p><div class="cabit-service-hero-actions"><a class="cabit-service-primary" href="/contact/">Vreau un proiect similar</a><a class="cabit-service-secondary" href="/portofoliu/">Toate proiectele</a></div></div><figure><img src="' . cms_e(cms_relative_asset($work['cover_image'], 2)) . '" alt="Proiectul ' . cms_e($work['title']) . '" width="1000" height="700" fetchpriority="high" decoding="async"></figure></div></section>' .
        '<section class="cabit-content-section"><div class="container cabit-case-layout"><article class="cabit-case-story"><div class="cabit-content-card"><span>01</span><h2>Obiectivul inițial</h2>' . cms_rich_content($work['objective']) . '</div><div class="cabit-content-card"><span>02</span><h2>Ce am construit</h2>' . cms_rich_content($work['work_done']) . '</div><div class="cabit-content-card"><span>03</span><h2>Rezultate și măsurare</h2>' . cms_rich_content($work['results']) . '</div></article><aside class="cabit-sticky-aside"><div class="cabit-note"><strong>Proiect publicat</strong><br><time datetime="' . cms_e($work['date_added']) . '">' . cms_e($work['date_added']) . '</time></div>' . $external . '</aside></div></section>' .
        ($gallery !== '' ? '<section class="cabit-content-section is-soft"><div class="container"><div class="cabit-section-heading"><span class="cabit-eyebrow">Detalii vizuale</span><h2>Galeria proiectului</h2></div><div class="cabit-gallery">' . $gallery . '</div></div></section>' : '') . $testimonial . cms_work_related((string) ($work['category_name'] ?? '')) . '<section class="cabit-inner-cta section-shell"><span>Ai un proiect în plan?</span><h2>Construim o soluție potrivită obiectivului tău.</h2><a class="button button-primary" href="/contact/">Hai să discutăm →</a></section></main>' .
        '<script src="../../assets/js/site-enhancements.js?v=20260819-4"></script><script defer src="../../assets/js/cabit-next.min.js?v=20260819-20"></script></body></html>';
}

function cms_generate_work(PDO $pdo, array $work): void
{
    $path = CABIT_PUBLIC_ROOT . '/portofoliu/' . $work['slug'] . '/index.html';
    cms_write_file($path, cms_work_page($pdo, $work));
}

function cms_work_images(PDO $pdo, int $workId): array
{
    $statement = $pdo->prepare('SELECT * FROM work_images WHERE work_id = ? ORDER BY sort_order, id');
    $statement->execute([$workId]);
    return $statement->fetchAll();
}

function cms_replace_marked_content(string $path, string $startMarker, string $endMarker, string $content): void
{
    $html = file_get_contents($path);
    if ($html === false) {
        throw new RuntimeException('Nu pot citi pagina index.');
    }
    $pattern = '~' . preg_quote($startMarker, '~') . '.*?' . preg_quote($endMarker, '~') . '~s';
    $replacement = $startMarker . "\n" . $content . "\n" . $endMarker;
    $updated = preg_replace($pattern, $replacement, $html, 1, $count);
    if ($updated === null || $count !== 1) {
        throw new RuntimeException('Marcajele CMS lipsesc din ' . basename(dirname($path)) . '.');
    }
    cms_write_file($path, $updated);
}

function cms_update_blog_index(PDO $pdo): void
{
    $articles = $pdo->query('SELECT * FROM articles ORDER BY date_published DESC, created_at DESC, id DESC')->fetchAll();
    $pinnedBlogSlug = 'site-custom-vs-wordpress-in-2026-avantaje-si-riscuri';
    $displayArticles = $articles;
    usort($displayArticles, static function (array $left, array $right) use ($pinnedBlogSlug): int {
        $leftPinned = (string) $left['slug'] === $pinnedBlogSlug ? 1 : 0;
        $rightPinned = (string) $right['slug'] === $pinnedBlogSlug ? 1 : 0;
        if ($leftPinned !== $rightPinned) {
            return $rightPinned <=> $leftPinned;
        }
        $leftImage = cms_article_has_cover_image($left) ? 1 : 0;
        $rightImage = cms_article_has_cover_image($right) ? 1 : 0;
        if ($leftImage !== $rightImage) {
            return $rightImage <=> $leftImage;
        }
        return strcmp((string) $right['created_at'], (string) $left['created_at']);
    });
    $blogSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => 'Blog CAB-IT Expert',
        'url' => CABIT_SITE_URL . '/blog/',
        'description' => 'Ghiduri de marketing digital pentru IMM-uri din România.',
        'publisher' => ['@id' => CABIT_SITE_URL . '/#organization'],
        'inLanguage' => 'ro-RO',
        'blogPost' => array_map(static function (array $article): array {
            $post = [
                '@type' => 'BlogPosting',
                'headline' => $article['title'],
                'url' => CABIT_SITE_URL . '/blog/' . $article['slug'] . '/',
                'datePublished' => $article['date_published'],
                'dateModified' => max(substr((string) $article['updated_at'], 0, 10), CABIT_SEO_RELEASE_DATE),
            ];
            $image = cms_article_image_url($article);
            if ($image !== '') {
                $post['image'] = $image;
            }
            return $post;
        }, array_slice($displayArticles, 0, 10)),
    ];
    cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/blog/index.html', '<!-- CMS_BLOG_SCHEMA_START -->', '<!-- CMS_BLOG_SCHEMA_END -->', '<script type="application/ld+json">' . cms_json($blogSchema) . '</script>');
    $cards = [];
    foreach (array_slice($displayArticles, 0, 10) as $article) {
        $cards[] = cms_article_card_markup($article, 1);
    }
    cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/blog/index.html', '<!-- CMS_BLOG_CARDS_START -->', '<!-- CMS_BLOG_CARDS_END -->', implode("\n", $cards));

    $searchEntries = [];
    $knowledgeEntries = [];
    foreach ($displayArticles as $article) {
        $metadata = cms_article_metadata($article);
        $coverImage = trim((string) ($article['cover_image'] ?? ''));
        $image = cms_article_has_cover_image($article)
            ? (preg_match('~^https?://~i', $coverImage) ? $coverImage : '/' . ltrim($coverImage, '/'))
            : '';
        $contentText = html_entity_decode(strip_tags((string) ($article['content'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $contentText = trim((string) preg_replace('/\s+/u', ' ', $contentText));
        $searchText = function_exists('mb_substr') ? mb_substr($contentText, 0, 1600, 'UTF-8') : substr($contentText, 0, 1600);
        $secondaryKeywords = is_array($metadata['secondary_keywords'] ?? null) ? array_values(array_map('strval', $metadata['secondary_keywords'])) : [];
        $primaryKeyword = trim((string) ($metadata['primary_keyword'] ?? ''));
        $keywords = array_values(array_filter(array_merge([$primaryKeyword], $secondaryKeywords)));
        $queries = is_array($metadata['queries'] ?? null) ? array_values(array_map('strval', $metadata['queries'])) : [];
        $entities = is_array($metadata['entities'] ?? null) ? array_values(array_map('strval', $metadata['entities'])) : [];
        $semanticTerms = is_array($metadata['semantic_terms'] ?? null) ? array_values(array_map('strval', $metadata['semantic_terms'])) : [];
        $boostTerms = is_array($metadata['boost_terms'] ?? null) ? array_values(array_map('strval', $metadata['boost_terms'])) : [];
        $entry = [
            'title' => (string) $article['title'],
            'slug' => (string) $article['slug'],
            'url' => '/blog/' . $article['slug'] . '/',
            'excerpt' => (string) $article['excerpt'],
            'cluster' => (string) ($metadata['cluster'] ?? 'Articol'),
            'keywords' => $keywords,
            'queries' => $queries,
            'entities' => $entities,
            'semantic_terms' => $semanticTerms,
            'boost_terms' => $boostTerms,
            'city' => (string) ($metadata['city'] ?? ''),
            'county' => (string) ($metadata['county'] ?? ''),
            'region' => (string) ($metadata['region'] ?? ''),
            'industry' => (string) ($metadata['industry'] ?? ''),
            'angle' => (string) ($metadata['angle'] ?? ''),
            'direct_answer' => (string) ($metadata['direct_answer'] ?? ''),
            'date' => (string) $article['date_published'],
            'date_label' => date('d.m.Y', strtotime((string) $article['date_published'])),
            'image' => $image,
            'image_alt' => (string) ($metadata['image_alt'] ?? $article['title']),
            'topic' => cms_article_visual_theme($article, $metadata),
            'search_text' => $searchText,
        ];
        $searchEntries[] = $entry;
        $knowledgeEntries[] = [
            'id' => 'cab-it-blog-' . $article['slug'],
            'url' => CABIT_SITE_URL . '/blog/' . $article['slug'] . '/',
            'title' => (string) $article['title'],
            'summary' => (string) $article['excerpt'],
            'cluster' => $entry['cluster'],
            'keywords' => $keywords,
            'queries' => $queries,
            'entities' => $entities,
            'city' => $entry['city'],
            'county' => $entry['county'],
            'region' => $entry['region'],
            'industry' => $entry['industry'],
            'direct_answer' => $entry['direct_answer'],
            'published' => (string) $article['date_published'],
            'updated' => max(substr((string) $article['updated_at'], 0, 10), CABIT_SEO_RELEASE_DATE),
            'content' => $contentText,
        ];
    }
    cms_write_file(CABIT_PUBLIC_ROOT . '/blog-search-index.json', cms_json([
        'version' => date('c'),
        'language' => 'ro-RO',
        'count' => count($searchEntries),
        'articles' => $searchEntries,
    ]));
    cms_write_file(CABIT_PUBLIC_ROOT . '/ai-knowledge-base.json', cms_json([
        'version' => date('c'),
        'language' => 'ro-RO',
        'source' => CABIT_SITE_URL . '/blog/',
        'usage' => 'Corpus editorial CAB-IT pentru căutare semantică și un viitor agent AI. Răspunsurile trebuie să trimită utilizatorul la sursa publică.',
        'organization' => [
            'name' => 'CAB-IT Expert',
            'legal_name' => 'CAB IT EXPERT SRL',
            'url' => CABIT_SITE_URL . '/',
            'same_as' => ['https://www.facebook.com/profile.php?id=61592087996523', 'https://www.instagram.com/cabitexpert/', 'https://www.linkedin.com/company/cab-it-expert/', 'https://www.youtube.com/@cabitexpert', 'https://www.tiktok.com/@cab.it.expert', 'https://x.com/cabitexpert'],
        ],
        'count' => count($knowledgeEntries),
        'documents' => $knowledgeEntries,
    ]));

    $rssItems = '';
    foreach (array_slice($articles, 0, 50) as $article) {
        $url = CABIT_SITE_URL . '/blog/' . rawurlencode((string) $article['slug']) . '/';
        $published = cms_article_datetime((string) $article['date_published'])->format(DATE_RSS);
        $rssItems .= "    <item>\n"
            . '      <title>' . htmlspecialchars((string) $article['title'], ENT_XML1, 'UTF-8') . "</title>\n"
            . '      <link>' . htmlspecialchars($url, ENT_XML1, 'UTF-8') . "</link>\n"
            . '      <guid isPermaLink="true">' . htmlspecialchars($url, ENT_XML1, 'UTF-8') . "</guid>\n"
            . '      <pubDate>' . htmlspecialchars($published, ENT_XML1, 'UTF-8') . "</pubDate>\n"
            . '      <description>' . htmlspecialchars((string) $article['excerpt'], ENT_XML1, 'UTF-8') . "</description>\n"
            . "    </item>\n";
    }
    $rss = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rss version=\"2.0\"><channel>\n"
        . "    <title>Blog CAB-IT Expert</title>\n"
        . '    <link>' . CABIT_SITE_URL . "/blog/</link>\n"
        . "    <description>Ghiduri CAB-IT despre website, SEO și promovare online.</description>\n"
        . "    <language>ro-RO</language>\n"
        . '    <lastBuildDate>' . (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_RSS) . "</lastBuildDate>\n"
        . $rssItems . "</channel></rss>\n";
    cms_write_file(CABIT_PUBLIC_ROOT . '/feed.xml', $rss);

    $pinnedHomeSlug = 'site-custom-vs-wordpress-in-2026-avantaje-si-riscuri';
    $homeSelection = [];
    $selectedSlugs = [];
    $selectedThemes = [];
    foreach ($articles as $article) {
        if ((string) $article['slug'] === $pinnedHomeSlug && cms_article_has_cover_image($article)) {
            $homeSelection[] = $article;
            $selectedSlugs[$pinnedHomeSlug] = true;
            $selectedThemes[cms_article_visual_theme($article, cms_article_metadata($article))] = true;
            break;
        }
    }
    foreach ($articles as $article) {
        if (count($homeSelection) >= 8 || isset($selectedSlugs[(string) $article['slug']]) || !cms_article_has_cover_image($article)) {
            continue;
        }
        $theme = cms_article_visual_theme($article, cms_article_metadata($article));
        if (isset($selectedThemes[$theme])) {
            continue;
        }
        $homeSelection[] = $article;
        $selectedSlugs[(string) $article['slug']] = true;
        $selectedThemes[$theme] = true;
    }
    foreach ($articles as $article) {
        if (count($homeSelection) >= 8 || isset($selectedSlugs[(string) $article['slug']]) || !cms_article_has_cover_image($article)) {
            continue;
        }
        $homeSelection[] = $article;
        $selectedSlugs[(string) $article['slug']] = true;
    }
    $homeCards = [];
    foreach ($homeSelection as $article) {
        $homeCards[] = cms_article_card_markup($article, 0, (string) $article['slug'] === $pinnedHomeSlug);
    }
    cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/index.html', '<!-- CMS_HOME_ARTICLES_START -->', '<!-- CMS_HOME_ARTICLES_END -->', implode("\n", $homeCards));
}

function cms_update_portfolio_index(PDO $pdo): void
{
    $categories = $pdo->query('SELECT c.*, COUNT(w.id) AS works_count FROM categories c LEFT JOIN works w ON w.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
    $filters = ['<button class="active" data-filter="*"><span>Toate</span></button>'];
    foreach ($categories as $category) {
        if ((int) $category['works_count'] > 0) {
            $filters[] = '<button data-filter=".cms-cat-' . (int) $category['id'] . '"><span>' . cms_e($category['name']) . '</span></button>';
        }
    }
    $works = $pdo->query('SELECT w.*, c.name AS category_name FROM works w LEFT JOIN categories c ON c.id = w.category_id ORDER BY w.date_added DESC, w.id DESC')->fetchAll();
    $cards = [];
    foreach ($works as $work) {
        $class = $work['category_id'] ? ' cms-cat-' . (int) $work['category_id'] : '';
        $image = cms_relative_asset($work['cover_image'], 1);
        $cards[] = '<article class="cabit-portfolio-card grid-item' . $class . '" data-meta-tag="' . cms_e($work['category_name'] ?: 'Proiect') . '"><a href="/portofoliu/' . cms_e($work['slug']) . '/"><div class="cabit-portfolio-card__media"><img src="' . cms_e($image) . '" alt="Studiu de caz ' . cms_e($work['title']) . '" loading="lazy" decoding="async"></div><div class="cabit-portfolio-card__body"><span>' . cms_e($work['category_name'] ?: 'Proiect') . '</span><h2>' . cms_e($work['title']) . '</h2><p>' . cms_e($work['meta_description']) . '</p><strong>Vezi studiul de caz <i aria-hidden="true">→</i></strong></div></a></article>';
    }
    cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/portofoliu/index.html', '<!-- CMS_PORTFOLIO_FILTERS_START -->', '<!-- CMS_PORTFOLIO_FILTERS_END -->', implode("\n", $filters));
    cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/portofoliu/index.html', '<!-- CMS_PORTFOLIO_CARDS_START -->', '<!-- CMS_PORTFOLIO_CARDS_END -->', implode("\n", $cards));

    $homeCards = [];
    foreach ($works as $work) {
        $image = cms_relative_asset($work['cover_image'], 0);
        $homeCards[] = '<article class="home-project-card reveal"><a href="/portofoliu/' . cms_e($work['slug']) . '/"><div class="home-project-media"><img src="' . cms_e($image) . '" alt="' . cms_e($work['title']) . ' — proiect CAB-IT" loading="lazy" decoding="async"></div><div class="home-project-body"><span>' . cms_e($work['category_name'] ?: 'Proiect digital') . '</span><h3>' . cms_e($work['title']) . '</h3><p>' . cms_e($work['meta_description']) . '</p><b>Vezi studiul de caz →</b></div></a></article>';
    }
    cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/index.html', '<!-- CMS_HOME_PROJECTS_START -->', '<!-- CMS_HOME_PROJECTS_END -->', implode("\n", $homeCards));

    if ($works) {
        $latest = $works[0];
        $featuredImage = cms_relative_asset($latest['cover_image'], 0);
        $featuredDimensions = '';
        if (ltrim((string) $latest['cover_image'], '/') === 'assets/img/case-studies/ify-ro.webp' && is_file(CABIT_PUBLIC_ROOT . '/assets/img/case-studies/ify-ro-480.webp')) {
            $featuredImage = 'assets/img/case-studies/ify-ro-480.webp';
            $featuredDimensions = ' width="800" height="369" srcset="assets/img/case-studies/ify-ro-480.webp 480w, assets/img/case-studies/ify-ro-800.webp 800w, assets/img/case-studies/ify-ro.webp 1536w" sizes="(max-width: 700px) calc(100vw - 58px), 50vw"';
        }
        $featured = '<section class="featured-project featured-project-wide section-shell reveal" aria-labelledby="featured-project-title"><div class="card-heading"><span>Proiect recent</span><a href="/portofoliu/">Vezi toate proiectele →</a></div><div class="featured-project-body"><img src="' . cms_e($featuredImage) . '" alt="Website ' . cms_e($latest['title']) . ' realizat de CAB-IT Expert"' . $featuredDimensions . ' loading="lazy" decoding="async"><div><span class="project-tag">' . cms_e($latest['category_name'] ?: 'Proiect digital') . '</span><h2 id="featured-project-title">' . cms_e($latest['title']) . '</h2><p>' . cms_e($latest['meta_description']) . '</p><a class="button button-ghost" href="/portofoliu/' . cms_e($latest['slug']) . '/">Vezi studiul de caz <span>→</span></a></div></div></section>';
        cms_replace_marked_content(CABIT_PUBLIC_ROOT . '/index.html', '<!-- CMS_FEATURED_PROJECT_START -->', '<!-- CMS_FEATURED_PROJECT_END -->', $featured);
    }
}

function cms_update_sitemap(PDO $pdo): void
{
    $path = CABIT_PUBLIC_ROOT . '/sitemap.xml';
    $xml = file_get_contents($path) ?: '';
    $staticUrls = [];
    preg_match_all('~<url>\s*<loc>(.*?)</loc>(?:\s*<lastmod>(.*?)</lastmod>)?.*?</url>~s', $xml, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $url = html_entity_decode(trim((string) ($match[1] ?? '')), ENT_XML1, 'UTF-8');
        if (!preg_match('~^https://cab-it\.ro/(?:blog|portofoliu)/[^/]+/$~', $url)) {
            $storedLastmod = preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string) ($match[2] ?? ''))) ? trim((string) $match[2]) : date('Y-m-d');
            $staticUrls[$url] = max($storedLastmod, CABIT_SEO_RELEASE_DATE);
        }
    }
    ksort($staticUrls);

    $articles = $pdo->query('SELECT title, slug, excerpt, cover_image, date_published, created_at, updated_at FROM articles ORDER BY created_at DESC, id DESC')->fetchAll();
    $works = $pdo->query('SELECT title, slug, meta_description, date_added, updated_at FROM works ORDER BY date_added DESC, id DESC')->fetchAll();
    $today = date('Y-m-d');
    $orderedUrls = [];
    $latestArticleUpdate = max(CABIT_SEO_RELEASE_DATE, $articles ? max(array_map(static fn(array $article): string => substr((string) $article['updated_at'], 0, 10), $articles)) : '');
    $latestWorkUpdate = max(CABIT_SEO_RELEASE_DATE, $works ? max(array_map(static fn(array $work): string => substr((string) $work['updated_at'], 0, 10), $works)) : '');
    foreach ($staticUrls as $url => $staticLastmod) {
        $lastmod = $staticLastmod;
        if ($url === CABIT_SITE_URL . '/blog/' && $latestArticleUpdate !== '') {
            $lastmod = max($lastmod, $latestArticleUpdate);
        } elseif ($url === CABIT_SITE_URL . '/portofoliu/' && $latestWorkUpdate !== '') {
            $lastmod = max($lastmod, $latestWorkUpdate);
        } elseif ($url === CABIT_SITE_URL . '/') {
            $lastmod = max(array_filter([$lastmod, $latestArticleUpdate, $latestWorkUpdate]));
        }
        $orderedUrls[] = ['url' => $url, 'lastmod' => $lastmod];
        if ($url === CABIT_SITE_URL . '/blog/') {
            foreach ($articles as $article) {
                $orderedUrls[] = [
                    'url' => CABIT_SITE_URL . '/blog/' . $article['slug'] . '/',
                    'lastmod' => substr((string) $article['updated_at'], 0, 10) ?: $today,
                ];
            }
        }
        if ($url === CABIT_SITE_URL . '/portofoliu/') {
            foreach ($works as $work) {
                $orderedUrls[] = [
                    'url' => CABIT_SITE_URL . '/portofoliu/' . $work['slug'] . '/',
                    'lastmod' => substr((string) $work['updated_at'], 0, 10) ?: $today,
                ];
            }
        }
    }

    $buildUrlset = static function (array $urls): string {
        $items = '';
        $hasImages = false;
        foreach ($urls as $item) {
            $image = trim((string) ($item['image'] ?? ''));
            $hasImages = $hasImages || $image !== '';
            $items .= "  <url>\n    <loc>" . htmlspecialchars((string) $item['url'], ENT_XML1, 'UTF-8') . "</loc>\n    <lastmod>" . htmlspecialchars((string) $item['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
            if ($image !== '') {
                $items .= "    <image:image>\n      <image:loc>" . htmlspecialchars($image, ENT_XML1, 'UTF-8') . "</image:loc>\n      <image:title>" . htmlspecialchars((string) ($item['image_title'] ?? ''), ENT_XML1, 'UTF-8') . "</image:title>\n    </image:image>\n";
            }
            $items .= "  </url>\n";
        }
        $namespace = $hasImages ? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' : '';
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"{$namespace}>\n{$items}</urlset>\n";
    };

    $pageUrls = array_values(array_filter(
        $orderedUrls,
        static fn(array $item): bool => !preg_match('~^https://cab-it\.ro/(?:blog|portofoliu)/[^/]+/$~', (string) $item['url'])
    ));
    $articleUrls = array_map(static fn(array $article): array => [
        'url' => CABIT_SITE_URL . '/blog/' . $article['slug'] . '/',
        'lastmod' => max(substr((string) $article['updated_at'], 0, 10), CABIT_SEO_RELEASE_DATE),
        'image' => cms_article_image_url($article),
        'image_title' => (string) $article['title'],
    ], $articles);
    $projectUrls = array_map(static fn(array $work): array => [
        'url' => CABIT_SITE_URL . '/portofoliu/' . $work['slug'] . '/',
        'lastmod' => max(substr((string) $work['updated_at'], 0, 10), CABIT_SEO_RELEASE_DATE),
    ], $works);

    $sitemapFiles = [
        'sitemap.xml' => $orderedUrls,
        'sitemap-pages.xml' => $pageUrls,
        'sitemap-articles.xml' => $articleUrls,
        'sitemap-projects.xml' => $projectUrls,
    ];
    foreach ($sitemapFiles as $filename => $urls) {
        $filePath = CABIT_PUBLIC_ROOT . '/' . $filename;
        file_put_contents($filePath, $buildUrlset($urls), LOCK_EX);
        cms_gzip_file($filePath);
    }

    $indexItems = '';
    foreach ([
        'sitemap-pages.xml' => $today,
        'sitemap-articles.xml' => $latestArticleUpdate ?: $today,
        'sitemap-projects.xml' => $latestWorkUpdate ?: $today,
    ] as $filename => $lastmod) {
        $indexItems .= "  <sitemap>\n    <loc>" . CABIT_SITE_URL . '/' . $filename . "</loc>\n    <lastmod>" . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n  </sitemap>\n";
    }
    $indexPath = CABIT_PUBLIC_ROOT . '/sitemap-index.xml';
    $indexXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n{$indexItems}</sitemapindex>\n";
    file_put_contents($indexPath, $indexXml, LOCK_EX);
    cms_gzip_file($indexPath);

    $llms = "# CAB-IT Expert\n\n"
        . "> Agenție de marketing online din București pentru creare site web, SEO, promovare online, Google Ads, Social Media Ads și automatizări AI.\n\n"
        . "## Identitate\n\n"
        . "- Nume comercial: CAB-IT Expert\n"
        . "- Variante de nume: CAB-IT, Cab-IT Expert\n"
        . "- Operator legal: CAB IT EXPERT SRL\n"
        . "- CUI: 49972605\n"
        . "- Website oficial: " . CABIT_SITE_URL . "/\n"
        . "- Răspuns scurt: CAB-IT Expert este o agenție digitală din București pentru creare website, web design, SEO, promovare online, Google Ads și automatizări digitale.\n"
        . "- Fondator legal CAB IT EXPERT SRL: Florin Popescu Daniel\n"
        . "- Fondator și coordonator al proiectului CAB-IT Expert: Alexie Popescu\n"
        . "- Arie deservită: București, Ilfov și România; întâlnirile au loc online.\n\n"
        . "## Profiluri sociale oficiale\n\n"
        . "- Facebook: https://www.facebook.com/profile.php?id=61592087996523\n"
        . "- Instagram: https://www.instagram.com/cabitexpert/\n"
        . "- LinkedIn: https://www.linkedin.com/company/cab-it-expert/\n"
        . "- YouTube: https://www.youtube.com/@cabitexpert\n"
        . "- TikTok: https://www.tiktok.com/@cab.it.expert\n"
        . "- X: https://x.com/cabitexpert\n\n"
        . "## Servicii principale\n\n"
        . "- [Creare website București](" . CABIT_SITE_URL . "/servicii/creare-site-web/): site-uri de prezentare și magazine online rapide, responsive și ușor de administrat.\n"
        . "- [Optimizare SEO](" . CABIT_SITE_URL . "/servicii/seo/): SEO tehnic, conținut și vizibilitate organică pentru București, Ilfov și România.\n"
        . "- [Promovare online](" . CABIT_SITE_URL . "/servicii/reclame-platite/): Google Ads, Meta Ads și TikTok Ads orientate spre cereri și vânzări măsurabile.\n"
        . "- [SEO local](" . CABIT_SITE_URL . "/servicii/seo-local/): optimizare locală pentru firme din București și Ilfov.\n"
        . "- [Automatizări digitale](" . CABIT_SITE_URL . "/servicii/integrari-digitale/): integrări și fluxuri AI pentru procese comerciale.\n\n"
        . "## Articole actualizate din CMS\n\n";
    foreach ($articles as $article) {
        $llms .= '- [' . trim((string) $article['title']) . '](' . CABIT_SITE_URL . '/blog/' . $article['slug'] . '/): '
            . trim((string) $article['excerpt']) . "\n";
    }
    $llms .= "\n## Studii de caz\n\n";
    foreach ($works as $work) {
        $llms .= '- [' . trim((string) $work['title']) . '](' . CABIT_SITE_URL . '/portofoliu/' . $work['slug'] . '/): '
            . trim((string) $work['meta_description']) . "\n";
    }
    $llms .= "\n## Informații verificate\n\n"
        . "- Audit website 100% gratuit, livrat manual prin email în maximum 30 de minute.\n"
        . "- Site-urile de prezentare pot fi finalizate chiar în 24 de ore; magazinele online în 3–7 zile, în funcție de complexitate și materiale.\n"
        . "- CAB-IT Expert este Google Partner; serviciile sunt operate de CAB IT EXPERT SRL.\n"
        . "- Telefon și WhatsApp: +40 771 532 949\n"
        . "- Email: contact@cab-it.ro\n"
        . "- Sediu social: Str. Humulești 131-135, cod poștal 052262, București, România.\n"
        . "- Adresa: Intrarea Humulești 6A, 052034 București, România.\n"
        . "- Întâlniri: online, cu programare.\n\n"
        . "## Descoperire și indexare\n\n"
        . "- [Sitemap index](" . CABIT_SITE_URL . "/sitemap-index.xml)\n"
        . "- [Sitemap complet](" . CABIT_SITE_URL . "/sitemap.xml)\n"
        . "- [Robots](" . CABIT_SITE_URL . "/robots.txt)\n";
    $llmsPath = CABIT_PUBLIC_ROOT . '/llms.txt';
    file_put_contents($llmsPath, $llms, LOCK_EX);
    file_put_contents(CABIT_PUBLIC_ROOT . '/llms-full.txt', $llms, LOCK_EX);
}

function cms_remove_generated_page(string $section, string $slug): void
{
    if (!in_array($section, ['blog', 'portofoliu'], true) || !cms_valid_slug($slug)) {
        return;
    }
    $base = realpath(CABIT_PUBLIC_ROOT . '/' . $section);
    $target = CABIT_PUBLIC_ROOT . '/' . $section . '/' . $slug;
    $resolvedParent = realpath(dirname($target));
    if (!$base || !$resolvedParent || $resolvedParent !== $base || !is_dir($target)) {
        return;
    }
    foreach (['index.html.gz', 'index.html'] as $file) {
        $filePath = $target . '/' . $file;
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
    @rmdir($target);
}

function cms_upload(array $file, string $folder): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Încărcarea imaginii a eșuat.');
    }
    if ((int) $file['size'] > CABIT_MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Imaginea depășește limita de 8 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Sunt acceptate doar imagini JPG, PNG sau WebP valide.');
    }
    $folder = trim(cms_slug($folder), '-');
    $directory = CABIT_UPLOADS_DIR . '/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Nu pot crea directorul pentru imagini.');
    }
    $name = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
        throw new RuntimeException('Nu pot salva imaginea.');
    }
    return '/uploads/' . $folder . '/' . $name;
}

function cms_multiple_uploads(array $files, string $folder): array
{
    $paths = [];
    $count = count($files['name'] ?? []);
    for ($i = 0; $i < $count; $i++) {
        $file = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
        $path = cms_upload($file, $folder);
        if ($path) {
            $paths[] = $path;
        }
    }
    return $paths;
}

function cms_add_subscriber(string $email, string $source, string $ip): bool
{
    $email = mb_strtolower(trim($email), 'UTF-8');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        throw new InvalidArgumentException('Adresa de email nu este validă.');
    }
    $pdo = cms_db();
    $statement = $pdo->prepare('INSERT OR IGNORE INTO subscribers (email, source, ip_hash, created_at) VALUES (?, ?, ?, ?)');
    $statement->execute([$email, mb_substr($source, 0, 60), hash('sha256', $ip), date('c')]);
    return $statement->rowCount() > 0;
}

function cms_add_audit_request(string $name, string $email, string $phone, string $websiteUrl, string $ip): int
{
    $name = trim($name);
    $email = mb_strtolower(trim($email), 'UTF-8');
    $phone = trim($phone);
    $websiteUrl = trim($websiteUrl);
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        throw new InvalidArgumentException('Numele nu este valid.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        throw new InvalidArgumentException('Adresa de email nu este validă.');
    }
    if (!filter_var($websiteUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($websiteUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
        throw new InvalidArgumentException('Adresa website-ului nu este validă.');
    }
    if (mb_strlen($phone) > 40) {
        throw new InvalidArgumentException('Numărul de telefon este prea lung.');
    }
    $now = date('c');
    $statement = cms_db()->prepare('INSERT INTO audit_requests (name, email, phone, website_url, status, notes, ip_hash, created_at, updated_at) VALUES (?, ?, ?, ?, "new", "", ?, ?, ?)');
    $statement->execute([$name, $email, $phone, $websiteUrl, hash('sha256', $ip), $now, $now]);
    return (int) cms_db()->lastInsertId();
}

function cms_web_base(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $marker = '/adminpanelcabitro/';
    if (($position = strpos($script, $marker)) !== false) {
        return substr($script, 0, $position);
    }
    if (str_ends_with($script, '/newsletter-subscribe.php')) {
        return rtrim(dirname($script), '/');
    }
    return '';
}

function cms_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('CABIT_ADMIN_SESSION');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Strict', 'path' => cms_web_base() . '/adminpanelcabitro/']);
    session_start();
}

function cms_csrf(): string
{
    cms_start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function cms_check_csrf(string $token): void
{
    if (!hash_equals(cms_csrf(), $token)) {
        throw new RuntimeException('Sesiunea formularului a expirat. Reîncarcă pagina.');
    }
}

function cms_is_admin(): bool
{
    cms_start_session();
    return !empty($_SESSION['admin_id']);
}

function cms_login(string $username, string $password, string $ip): bool
{
    $pdo = cms_db();
    $ipHash = hash('sha256', $ip);
    $limit = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ? AND succeeded = 0 AND created_at >= datetime('now', '-15 minutes')");
    $limit->execute([$ipHash]);
    if ((int) $limit->fetchColumn() >= 8) {
        throw new RuntimeException('Prea multe încercări. Reîncearcă peste 15 minute.');
    }
    $statement = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $statement->execute([$username]);
    $admin = $statement->fetch();
    $success = $admin && password_verify($password, $admin['password_hash']);
    $log = $pdo->prepare('INSERT INTO login_attempts (ip_hash, succeeded, created_at) VALUES (?, ?, ?)');
    $log->execute([$ipHash, $success ? 1 : 0, date('Y-m-d H:i:s')]);
    if (!$success) {
        return false;
    }
    cms_start_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    return true;
}

function cms_logout(): void
{
    cms_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function cms_refresh_indexes(PDO $pdo): void
{
    cms_update_blog_index($pdo);
    cms_update_portfolio_index($pdo);
    cms_update_sitemap($pdo);
}

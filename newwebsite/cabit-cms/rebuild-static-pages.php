<?php
declare(strict_types=1);

const PUBLIC_ROOT = __DIR__ . '/..';
const ASSET_VERSION = '20260719-1';

function marker(string $path, string $start, string $end, string $fallback = ''): string
{
    $html = is_file($path) ? (string) file_get_contents($path) : '';
    if (preg_match('~' . preg_quote($start, '~') . '(.*?)' . preg_quote($end, '~') . '~s', $html, $match)) {
        return trim($match[1]);
    }
    return $fallback;
}

function shell(string $title, string $description, string $canonical, string $bodyClass, string $main, string $schema = ''): string
{
    $structured = $schema !== '' ? '<script type="application/ld+json">' . $schema . '</script>' : '';
    return '<!doctype html>
<html lang="ro-RO">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>
  <meta name="description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">
  <meta name="robots" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large">
  <link rel="canonical" href="https://cab-it.ro/' . $canonical . '">
  <meta property="og:locale" content="ro_RO"><meta property="og:type" content="website"><meta property="og:site_name" content="Cab-IT Expert">
  <meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"><meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '"><meta property="og:url" content="https://cab-it.ro/' . $canonical . '"><meta property="og:image" content="https://cab-it.ro/assets/img/brand/cab-it-c-symbol-app-v7.png">
  <link rel="shortcut icon" type="image/png" href="../assets/img/brand/cab-it-c-symbol-tab-v7.png"><link rel="icon" type="image/png" sizes="48x48" href="../assets/img/brand/cab-it-c-symbol-tab-v7.png"><link rel="icon" type="image/png" sizes="192x192" href="../assets/img/brand/cab-it-c-symbol-app-v7.png"><link rel="apple-touch-icon" sizes="192x192" href="../assets/img/brand/cab-it-c-symbol-app-v7.png"><link rel="manifest" href="../site.webmanifest">
  <link rel="stylesheet" href="../assets/css/site.min.css?v=' . ASSET_VERSION . '"><link rel="stylesheet" href="../assets/css/cabit-next.css?v=' . ASSET_VERSION . '">
  ' . $structured . '
</head>
<body class="cabit-theme-2026 cabit-inner-page ' . $bodyClass . '">
<main>' . $main . '</main>
<script src="../assets/js/site-enhancements.js?v=' . ASSET_VERSION . '"></script><script defer src="../assets/js/cabit-next.js?v=' . ASSET_VERSION . '"></script>
</body></html>';
}

function writePage(string $directory, string $html): void
{
    $path = PUBLIC_ROOT . '/' . $directory . '/index.html';
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    file_put_contents($path, $html);
}

function icon(string $type): string
{
    $paths = [
        'web' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'seo' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/>',
        'ads' => '<path d="M4 13V9l12-5v14L4 13Z"/><path d="m7 14 1.5 6h3L10 15M19 8v6"/>',
        'social' => '<path d="M7 7h10a4 4 0 0 1 4 4v3a4 4 0 0 1-4 4h-5l-5 4v-4a4 4 0 0 1-4-4v-3a4 4 0 0 1 4-4Z"/>',
        'ai' => '<rect x="5" y="6" width="14" height="12" rx="3"/><path d="M9 2v4m6-4v4M2 10h3m14 0h3M9 12h.01M15 12h.01M9 15h6"/>',
        'cro' => '<path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/><path d="m4 8 5-4 5 6 6-8"/>',
        'audit' => '<path d="M4 20h16M6 17l4-5 4 3 6-9"/><path d="M17 6h3v3"/>',
        'local' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
    ];
    return '<span class="cabit-feature-icon is-' . $type . '"><svg viewBox="0 0 24 24" aria-hidden="true">' . ($paths[$type] ?? $paths['web']) . '</svg></span>';
}

$services = [
    ['web', '01', 'Website-uri și magazine online', 'Site-uri de prezentare gata chiar în 24 de ore și magazine online în 3–7 zile, cu administrare simplă și bază SEO.', 'creare-site-web', 'Creare website'],
    ['seo', '02', 'Optimizare SEO', 'Audit tehnic, structură, conținut și raportare pentru căutările relevante din București, Ilfov și România.', 'seo', 'Servicii SEO'],
    ['ads', '03', 'Google, Meta și TikTok Ads', 'Campanii măsurabile pentru cereri, apeluri și vânzări, cu tracking și optimizare continuă.', 'reclame-platite', 'Promovare plătită'],
    ['local', '04', 'SEO local', 'Website și prezență locală optimizate pentru oamenii care caută serviciile tale în apropiere.', 'seo-local', 'SEO local'],
    ['social', '05', 'Promovare social media', 'Conținut și reclame pentru Facebook, Instagram și TikTok, conectate la oferta și publicul tău.', 'social-media', 'Social media'],
    ['cro', '06', 'Optimizarea conversiilor', 'Îmbunătățim traseul, mesajele și formularele pentru mai multe solicitări din traficul existent.', 'optimizare-conversii', 'Optimizare CRO'],
    ['audit', '07', 'Audit și analiză digitală', 'Verificăm website-ul, concurența, măsurarea și canalele active, apoi ordonăm clar prioritățile.', 'analiza-digitala', 'Analiză digitală'],
    ['ai', '08', 'Automatizări și agenți AI', 'Conectăm date și procese repetitive în fluxuri mai simple, rapide și ușor de urmărit.', 'integrari-digitale', 'Automatizări AI'],
];

$serviceCards = '';
foreach ($services as [$type, $number, $name, $copy, $slug, $link]) {
    $serviceCards .= '<article class="cabit-service-overview-card reveal" data-service="' . $type . '"><span class="service-number">' . $number . '</span>' . icon($type) . '<h2>' . $name . '</h2><p>' . $copy . '</p><a href="/servicii/' . $slug . '/">' . $link . ' <span>→</span></a></article>';
}

$servicesMain = '
<section class="cabit-page-header cabit-split-hero"><div class="container"><div><span class="cabit-eyebrow">Servicii digitale · București și România</span><h1>Un sistem digital complet, construit pentru rezultate.</h1><p>Website, SEO, promovare plătită și automatizări conectate într-o strategie clară. Alegem ce are sens pentru obiectivul tău și măsurăm fiecare pas.</p><div class="cabit-service-hero-actions"><a class="cabit-service-primary" href="/#audit">Cere auditul gratuit</a><a class="cabit-service-secondary" href="/portofoliu/">Vezi proiectele</a></div></div><div class="cabit-orbit-visual" aria-hidden="true"><span>Website</span><span>SEO</span><span>Ads</span><span>AI</span><strong>CAB-IT</strong></div></div></section>
<section class="cabit-content-section"><div class="container"><div class="cabit-section-heading"><span class="cabit-eyebrow">Tot ce are nevoie afacerea</span><h2>Servicii care lucrează împreună</h2><p>Fiecare serviciu are o pagină completă, cu livrabile, proces și întrebări frecvente.</p></div><div class="cabit-services-overview-grid">' . $serviceCards . '</div></div></section>
<section class="cabit-content-section is-soft"><div class="container cabit-rich-columns"><div><span class="cabit-eyebrow">Livrare rapidă</span><h2>Website de prezentare chiar în 24h. Magazin online în 3–7 zile.</h2><p>Termenul final depinde de complexitate și de disponibilitatea materialelor, însă procesul rămâne transparent: clarificare, implementare, testare, lansare.</p></div><ol class="cabit-service-steps"><li><b>01</b><span><strong>Audităm</strong>Înțelegem obiectivul și punctul de plecare.</span></li><li><b>02</b><span><strong>Prioritizăm</strong>Alegem intervențiile cu sens comercial.</span></li><li><b>03</b><span><strong>Implementăm</strong>Construim, măsurăm și îmbunătățim.</span></li></ol></div></section>
<section class="cabit-inner-cta section-shell"><span>100% gratuit</span><h2>Primește un audit complet în maximum 30 de minute.</h2><p>Introdu website-ul și emailul. Îți trimitem evaluarea direct pe email, fără costuri și fără obligații.</p><a class="button button-primary" href="/#audit">Cere auditul gratuit →</a></section>';
writePage('servicii', shell('Servicii Marketing Online București | Cab-IT Expert', 'Website-uri, SEO, Google Ads, social media și automatizări AI pentru afaceri din București și România. Audit gratuit și plan clar de implementare.', 'servicii/', 'cabit-page-services', $servicesMain));

$aboutMain = '
<section class="cabit-page-header cabit-split-hero"><div class="container"><div><span class="cabit-eyebrow">CAB-IT Expert SRL · București</span><h1>Partener digital, nu doar furnizor.</h1><p>Construim soluții digitale pe care oamenii le înțeleg și afacerile le pot măsura. Combinăm dezvoltarea web, SEO, promovarea online și automatizarea într-un proces simplu și transparent.</p><div class="cabit-service-hero-actions"><a class="cabit-service-primary" href="/contact/">Hai să discutăm</a><a class="cabit-service-secondary" href="/portofoliu/">Vezi proiectele</a></div></div><figure class="cabit-brand-figure"><img src="../img/logo_home.png" alt="CAB-IT Expert SRL — Future is Online" width="560" height="195"><span>Google Partner</span></figure></div></section>
<section class="cabit-content-section"><div class="container cabit-rich-columns"><div><span class="cabit-eyebrow">Cum lucrăm</span><h2>Claritate înainte de execuție.</h2><p>Începem cu întrebările care contează: cine este clientul, ce problemă rezolvă oferta, ce acțiune trebuie să facă vizitatorul și cum verificăm rezultatul.</p><p>Nu livrăm doar pagini sau campanii. Documentăm deciziile, păstrăm administrarea simplă și construim un sistem care poate fi îmbunătățit pe baza datelor.</p></div><div class="cabit-values-grid"><article>' . icon('audit') . '<h3>Transparență</h3><p>Livrabile, termene și indicatori clar definiți.</p></article><article>' . icon('web') . '<h3>Execuție modernă</h3><p>Design responsive, performanță și administrare simplă.</p></article><article>' . icon('seo') . '<h3>Vizibilitate relevantă</h3><p>SEO și promovare conectate la cererea reală.</p></article><article>' . icon('cro') . '<h3>Rezultate măsurabile</h3><p>Urmărim solicitări, vânzări și acțiuni utile.</p></article></div></div></section>
<section class="cabit-content-section is-soft"><div class="container"><div class="cabit-proof-grid"><article><strong>120+</strong><span>clienți mulțumiți</span></article><article><strong>200+</strong><span>proiecte finalizate</span></article><article><strong>8+</strong><span>ani de experiență</span></article><article><strong>5.0</strong><span>rating pe Google</span></article></div></div></section>
<section class="cabit-content-section"><div class="container"><div class="cabit-section-heading centered"><span class="cabit-eyebrow">Proces simplu</span><h2>De la idee la un sistem care produce rezultate</h2></div><ol class="cabit-timeline"><li><b>01</b><div><h3>Înțelegem afacerea</h3><p>Analizăm obiectivele, clienții și concurența.</p></div></li><li><b>02</b><div><h3>Construim strategia</h3><p>Stabilim ce merită implementat și în ce ordine.</p></div></li><li><b>03</b><div><h3>Implementăm</h3><p>Dezvoltăm, testăm și optimizăm fiecare componentă.</p></div></li><li><b>04</b><div><h3>Măsurăm</h3><p>Urmărim datele și îmbunătățim soluția.</p></div></li></ol></div></section>
<section class="cabit-inner-cta section-shell"><span>Future is Online</span><h2>Ai un proiect în plan?</h2><p>Spune-ne ce vrei să obții, iar noi îți propunem pașii potriviți.</p><a class="button button-primary" href="/contact/">Hai să discutăm →</a></section>';
writePage('despre-noi', shell('Despre Cab-IT Expert | Agenție Digitală București', 'CAB-IT Expert este o agenție digitală din București pentru creare website, SEO, Google Ads și automatizări, cu proces transparent și rezultate măsurabile.', 'despre-noi/', 'cabit-page-about', $aboutMain));

$contactForm = '<form class="conversation-form reveal" action="../whatsapp-contact.php" method="post" data-conversation-form><fieldset><legend>1. Selectează obiectivul</legend><div class="choice-grid"><label><input type="radio" name="objective" value="website" required><span>' . icon('web') . '<b>Am nevoie de un website</b></span></label><label><input type="radio" name="objective" value="seo"><span>' . icon('seo') . '<b>Vreau să apar mai bine în Google</b></span></label><label><input type="radio" name="objective" value="reclame"><span>' . icon('ads') . '<b>Vreau reclame</b></span></label><label><input type="radio" name="objective" value="automatizare"><span>' . icon('ai') . '<b>Vreau o automatizare AI</b></span></label><label><input type="radio" name="objective" value="nesigur"><span>' . icon('audit') . '<b>Nu știu încă</b></span></label></div></fieldset><div class="conversation-fields"><label>Nume și prenume<input type="text" name="name" autocomplete="name" placeholder="Cum te numești?" required></label><label>Email<input type="email" name="email" autocomplete="email" placeholder="nume@companie.ro" required></label><label>Telefon<input type="tel" name="phone" autocomplete="tel" placeholder="+40 7xx xxx xxx"></label><label>Companie <small>(opțional)</small><input type="text" name="company" autocomplete="organization" placeholder="Numele companiei"></label><label class="full">Pe scurt, ce vrei să obții?<textarea name="message" rows="5" placeholder="Obiectiv, problemă sau idee..." required></textarea></label></div><button class="button button-primary" type="submit">Trimite pe WhatsApp <span>→</span></button><p class="form-note">Se deschide conversația WhatsApp cu mesajul pregătit; îl poți verifica înainte de trimitere.</p></form>';
$contactMain = '
<section class="cabit-page-header cabit-contact-hero"><div class="container"><div><span class="cabit-eyebrow">Contact CAB-IT Expert</span><h1>Spune-ne ce vrei să construim.</h1><p>Răspundem cu pași clari și o recomandare potrivită obiectivului tău. Poți trimite formularul direct în WhatsApp.</p><div class="cabit-contact-cards"><a href="tel:+40771532949"><b>Telefon</b><span>+40 771 532 949</span></a><a href="mailto:contact@cab-it.ro"><b>Email</b><span>contact@cab-it.ro</span></a><a href="#locatie"><b>Adresă</b><span>Intrarea Humulești 6A, București</span></a></div></div>' . $contactForm . '</div></section>
<section class="cabit-content-section" id="locatie"><div class="container cabit-location-layout"><div><span class="cabit-eyebrow">Ne găsești în București</span><h2>Intrarea Humulești 6A</h2><p>Lucrăm cu afaceri din București, Ilfov și din întreaga Românie. Întâlnirile pot fi organizate online sau la sediu, cu programare.</p><a class="button button-ghost" href="https://www.google.com/maps/search/?api=1&amp;query=Intrarea+Humule%C8%99ti+6A%2C+Bucure%C8%99ti" target="_blank" rel="noopener">Deschide în Google Maps</a></div><div class="cabit-map-card"><iframe src="https://www.google.com/maps?q=Intrarea%20Humule%C8%99ti%206A%2C%20052034%20Bucure%C8%99ti%2C%20Rom%C3%A2nia&amp;output=embed" title="Harta sediului CAB-IT Expert, Intrarea Humulești 6A" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div></div></section>';
writePage('contact', shell('Contact Agenție Marketing București | Cab-IT Expert', 'Contactează Cab-IT Expert pentru website, SEO și promovare online. Intrarea Humulești 6A, București. Trimite cererea direct pe WhatsApp.', 'contact/', 'cabit-page-contact', $contactMain));

$pricingMain = '
<section class="cabit-page-header cabit-split-hero"><div class="container"><div><span class="cabit-eyebrow">Puncte de pornire transparente</span><h1>Planuri flexibile pentru creșterea afacerii tale.</h1><p>Prețurile afișate nu includ TVA. Oferta finală se stabilește după obiective, funcționalități și complexitate.</p><div class="cabit-service-hero-actions"><a class="cabit-service-primary" href="/contact/">Cere ofertă personalizată</a><a class="cabit-service-secondary" href="/servicii/">Compară serviciile</a></div></div><div class="cabit-price-orbit"><strong>999 lei</strong><span>website</span><i>+</i><strong>649 lei</strong><span>promovare</span></div></div></section>
<section class="cabit-content-section"><div class="container"><div class="pricing-grid cabit-pricing-page"><article class="price-card"><span>Creare website · livrare rapidă</span><h2>Website de prezentare</h2><strong>de la 999 lei</strong><ul><li>Lansare chiar în 24 de ore*</li><li>Design responsive modern</li><li>Până la 5 pagini</li><li>Structură SEO de bază</li><li>Formular și administrare</li></ul><a class="button button-ghost" href="/contact/?serviciu=website">Cere ofertă</a></article><article class="price-card is-featured"><span>E-commerce · 3–7 zile</span><h2>Magazin online</h2><strong>de la 1.799 lei</strong><ul><li>Lansare în maximum 3–7 zile*</li><li>Până la 100 de produse</li><li>Plată, livrare și administrare</li><li>Experiență de cumpărare clară</li><li>Bază SEO și analytics</li></ul><a class="button button-primary" href="/contact/?serviciu=magazin-online">Cere ofertă</a></article><article class="price-card"><span>Promovare online</span><h2>Google / Meta / TikTok Ads</h2><strong>de la 649 lei / lună</strong><ul><li>Strategie și configurare</li><li>Tracking și obiective</li><li>Optimizare continuă</li><li>Rapoarte clare</li><li>Recomandări pentru landing page</li></ul><a class="button button-ghost" href="/contact/?serviciu=promovare">Cere ofertă</a></article></div><p class="cabit-pricing-note">*Termenele depind de complexitate și de disponibilitatea materialelor. Bugetele media și serviciile terțe nu sunt incluse.</p></div></section>
<section class="cabit-inner-cta section-shell"><span>Nu știi ce să alegi?</span><h2>Începem cu un audit gratuit.</h2><p>Primești o evaluare clară a website-ului și a oportunităților de creștere.</p><a class="button button-primary" href="/#audit">Cere auditul gratuit →</a></section>';
writePage('preturi', shell('Prețuri Website și Promovare Online | Cab-IT Expert', 'Prețuri pentru creare website, magazin online și promovare Google, Meta sau TikTok Ads. Pachete clare și ofertă adaptată obiectivului tău.', 'preturi/', 'cabit-page-pricing', $pricingMain));

$portfolioFilters = marker(PUBLIC_ROOT . '/portofoliu/index.html', '<!-- CMS_PORTFOLIO_FILTERS_START -->', '<!-- CMS_PORTFOLIO_FILTERS_END -->', '<button class="active" data-filter="*"><span>Toate</span></button>');
$portfolioCards = marker(PUBLIC_ROOT . '/portofoliu/index.html', '<!-- CMS_PORTFOLIO_CARDS_START -->', '<!-- CMS_PORTFOLIO_CARDS_END -->');
$portfolioMain = '
<section class="cabit-page-header cabit-split-hero"><div class="container"><div><span class="cabit-eyebrow">Proiecte CAB-IT</span><h1>Probleme reale. Soluții construite clar.</h1><p>Website-uri, magazine online, SEO și campanii digitale construite pentru obiective concrete. Publicăm rezultate cantitative numai când sunt validate.</p><div class="cabit-service-hero-actions"><a class="cabit-service-primary" href="/contact/">Vreau un proiect similar</a><a class="cabit-service-secondary" href="/servicii/">Vezi serviciile</a></div></div><div class="cabit-project-stack"><img src="../assets/img/case-studies/maison-bebe.png" alt="Proiect Maison Bébé" width="800" height="520"><span>200+ proiecte finalizate</span></div></div></section>
<section class="cabit-content-section"><div class="container"><div class="cabit-section-heading"><span class="cabit-eyebrow">Portofoliu</span><h2>Studii de caz și proiecte recente</h2><p>Filtrează proiectele după tipul de intervenție.</p></div><div class="cabit-portfolio-filters" data-portfolio-filters><!-- CMS_PORTFOLIO_FILTERS_START -->' . $portfolioFilters . '<!-- CMS_PORTFOLIO_FILTERS_END --></div><div class="cabit-portfolio-grid" data-portfolio-grid><!-- CMS_PORTFOLIO_CARDS_START -->' . $portfolioCards . '<!-- CMS_PORTFOLIO_CARDS_END --></div></div></section>
<section class="cabit-inner-cta section-shell"><span>Următorul proiect poate fi al tău</span><h2>Spune-ne obiectivul și îți propunem soluția.</h2><a class="button button-primary" href="/contact/">Hai să discutăm →</a></section>';
writePage('portofoliu', shell('Portofoliu Website, SEO și Promovare | Cab-IT Expert', 'Descoperă proiecte Cab-IT Expert: website-uri, magazine online, SEO și promovare online pentru afaceri din București și România.', 'portofoliu/', 'cabit-page-portfolio', $portfolioMain));

$blogSchema = marker(PUBLIC_ROOT . '/blog/index.html', '<!-- CMS_BLOG_SCHEMA_START -->', '<!-- CMS_BLOG_SCHEMA_END -->');
$blogCards = marker(PUBLIC_ROOT . '/blog/index.html', '<!-- CMS_BLOG_CARDS_START -->', '<!-- CMS_BLOG_CARDS_END -->');
$blogMain = '
<section class="cabit-page-header cabit-split-hero"><div class="container"><div><span class="cabit-eyebrow">Resurse CAB-IT</span><h1>Marketing digital explicat pentru decizii mai bune.</h1><p>Ghiduri clare despre SEO, promovare online, website-uri și măsurarea rezultatelor. Fără jargon inutil, cu exemple și pași aplicabili.</p><div class="cabit-topic-pills"><a href="/servicii/seo/">SEO</a><a href="/servicii/reclame-platite/">Google Ads</a><a href="/servicii/creare-site-web/">Website</a><a href="/glosar-seo/">Glosar</a></div></div><div class="cabit-editorial-visual"><span>Ghid nou</span><strong>Idei clare.<br>Decizii mai bune.</strong><i>↗</i></div></div></section>
<section class="cabit-content-section"><div class="container"><div class="cabit-section-heading"><span class="cabit-eyebrow">Publicate recent</span><h2>Toate articolele</h2><p>Articolele sunt afișate de la cea mai recentă dată de publicare.</p></div><div class="cabit-blog-grid"><!-- CMS_BLOG_CARDS_START -->' . $blogCards . '<!-- CMS_BLOG_CARDS_END --></div></div></section>
<section class="cabit-inner-cta section-shell"><span>Ai o întrebare concretă?</span><h2>Transformăm informația într-un plan pentru afacerea ta.</h2><a class="button button-primary" href="/contact/">Hai să discutăm →</a></section>';
$blogSchemaTag = trim($blogSchema);
writePage('blog', shell('Blog Marketing Digital, SEO și Website | Cab-IT Expert', 'Ghiduri practice despre SEO, Google Ads, social media, creare website și măsurarea rezultatelor pentru afaceri din București și România.', 'blog/', 'cabit-page-blog', '<!-- CMS_BLOG_SCHEMA_START -->' . $blogSchemaTag . '<!-- CMS_BLOG_SCHEMA_END -->' . $blogMain));

$termsMain = '
<section class="cabit-page-header"><div class="container"><span class="cabit-eyebrow">Informații legale</span><h1>Termeni și condiții</h1><p>Condițiile generale pentru utilizarea website-ului cab-it.ro și pentru transmiterea solicitărilor către CAB IT EXPERT SRL.</p></div></section>
<section class="cabit-content-section"><div class="container cabit-legal-layout"><nav><strong>Cuprins</strong><a href="#operator">Operator</a><a href="#utilizare">Utilizarea site-ului</a><a href="#formulare">Formulare și date</a><a href="#continut">Conținut și proprietate</a><a href="#raspundere">Limitarea răspunderii</a><a href="#contact-legal">Contact</a></nav><article class="cabit-content-card cabit-article-content"><h2 id="operator">1. Operatorul website-ului</h2><p>Website-ul cab-it.ro este operat de CAB IT EXPERT SRL, cu sediul în Intrarea Humulești 6A, București. Ne poți contacta la contact@cab-it.ro sau +40 771 532 949.</p><h2 id="utilizare">2. Utilizarea website-ului</h2><p>Informațiile sunt oferite pentru prezentarea serviciilor de dezvoltare web, SEO, promovare online și automatizare. Utilizatorii trebuie să folosească site-ul legal și să nu încerce afectarea funcționării sau securității lui.</p><h2 id="formulare">3. Formulare și date</h2><p>Datele transmise prin formulare sunt folosite pentru a răspunde solicitării, a pregăti auditul cerut sau a furniza resursele la care utilizatorul s-a abonat. Câmpurile opționale sunt marcate corespunzător.</p><h2 id="continut">4. Conținut și proprietate intelectuală</h2><p>Textele, elementele vizuale, identitatea și structura website-ului nu pot fi copiate sau redistribuite în scop comercial fără acord scris.</p><h2 id="raspundere">5. Limitarea răspunderii</h2><p>Rezultatele SEO, media sau comerciale depind de piață, concurență, buget, ofertă și implementare. Nu garantăm poziții fixe sau rezultate care depind de platforme terțe.</p><h2 id="contact-legal">6. Contact</h2><p>Pentru întrebări legate de acești termeni: <a href="mailto:contact@cab-it.ro">contact@cab-it.ro</a>.</p><p><small>Ultima actualizare: 19 iulie 2026.</small></p></article></div></section>';
writePage('termeni-si-conditii', shell('Termeni și Condiții | Cab-IT Expert SRL', 'Termenii și condițiile pentru utilizarea website-ului Cab-IT Expert și transmiterea solicitărilor pentru servicii digitale.', 'termeni-si-conditii/', 'cabit-page-legal', $termsMain));

echo "Static pages rebuilt.\n";

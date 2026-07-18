<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$pdo = cms_db();
$slug = 'creare-site-web-bucuresti-ghid';
$exists = $pdo->prepare('SELECT id FROM articles WHERE slug = ?');
$exists->execute([$slug]);

if (!$exists->fetchColumn()) {
    $content = <<<'HTML'
<p>Un website bun pentru o afacere din București nu începe cu animațiile sau paleta de culori. Începe cu o întrebare simplă: ce trebuie să înțeleagă și să facă vizitatorul în primele secunde?</p>
<h2>Mesajul principal trebuie să fie imediat clar</h2>
<p>Prima secțiune trebuie să spună ce oferi, cui te adresezi și care este următorul pas. Un titlu generic precum „Bine ai venit” consumă spațiul cel mai valoros fără să răspundă intenției de căutare.</p>
<h2>Structură construită în jurul serviciilor</h2>
<p>Fiecare serviciu important merită o pagină proprie, cu explicații, livrabile, proces, întrebări frecvente și un CTA relevant. Această structură ajută atât utilizatorii, cât și motoarele de căutare să înțeleagă website-ul.</p>
<h2>Viteză și experiență bună pe mobil</h2>
<p>Pentru multe afaceri locale, majoritatea vizitelor vin de pe telefon. Navigarea, formularele, dimensiunea textului și timpul de încărcare trebuie verificate pe ecrane reale, nu doar într-o previzualizare de desktop.</p>
<h2>SEO local pentru București și Ilfov</h2>
<p>Adresa, zonele deservite, informațiile de contact, datele structurate și legătura dintre website și profilul Google Business trebuie să fie coerente. Paginile nu trebuie supraîncărcate artificial cu localități, ci să răspundă natural căutărilor relevante.</p>
<h2>Măsurarea solicitărilor</h2>
<p>Un website nu poate fi optimizat dacă nu știi ce funcționează. Configurarea corectă a formularelor, apelurilor, clickurilor pe WhatsApp și comenzilor permite compararea surselor de trafic și prioritizarea investițiilor.</p>
<h2>Checklist înainte de lansare</h2>
<ul>
  <li>titlu și descriere unice pentru fiecare pagină;</li>
  <li>navigare complet funcțională pe mobil;</li>
  <li>formulare testate și mesaje de confirmare clare;</li>
  <li>robots.txt, sitemap XML și canonical corecte;</li>
  <li>imagini optimizate și texte alternative relevante;</li>
  <li>Search Console și măsurarea conversiilor configurate.</li>
</ul>
<p>Un website de prezentare poate fi lansat rapid atunci când materialele și deciziile sunt pregătite. Viteza de execuție nu trebuie însă să elimine verificările tehnice, conținutul clar sau măsurarea.</p>
HTML;

    $statement = $pdo->prepare('INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $statement->execute([
        'Creare site web în București: ce trebuie să conțină un website care aduce cereri',
        'Creare Site Web București: Ghid pentru un Website Eficient',
        'Ghid pentru creare site web în București: structură, SEO local, viteză, mobil, conversii și verificările necesare înainte de lansare.',
        $slug,
        'Ce trebuie să conțină un website pentru o afacere din București: mesaj clar, pagini de servicii, SEO local, viteză și măsurarea solicitărilor.',
        $content,
        'assets/img/case-studies/maison-bebe.png',
        '2026-07-19',
        '2026-07-19 00:05:00',
        '2026-07-19 00:05:00',
    ]);
}

foreach ($pdo->query('SELECT * FROM articles ORDER BY created_at DESC, id DESC')->fetchAll() as $article) {
    cms_generate_article($article);
}
cms_refresh_indexes($pdo);
echo "Sixth article ensured.\n";

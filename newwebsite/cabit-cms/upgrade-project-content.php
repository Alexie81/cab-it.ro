<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$updates = [
    'bilka-sistem' => [
        'Studiu de caz Bilka Sistem | SEO și Google Ads',
        'Studiu de caz Bilka Sistem: audit SEO, optimizare tehnică și structurarea campaniilor Google Ads pentru vizibilitate și cereri măsurabile.',
        'Creșterea vizibilității organice și organizarea campaniilor plătite în jurul serviciilor, zonelor și intențiilor comerciale relevante.',
        'Am realizat auditul SEO, prioritizarea problemelor tehnice, structura de cuvinte cheie, recomandările on-page, arhitectura campaniilor și configurarea măsurării.',
        'Website-ul și campaniile au acum o structură mai clară pentru monitorizare și optimizare. Rezultatele cantitative se publică numai după validarea datelor de către client.',
    ],
    'lael-fashion' => [
        'Lael Fashion | Magazin online și promovare digitală',
        'Studiu de caz Lael Fashion: website e-commerce responsive, structură de produse, optimizare SEO și direcție de promovare pentru un brand de fashion.',
        'Construirea unei prezențe online coerente, capabile să prezinte colecțiile clar și să susțină atât descoperirea organică, cât și campaniile de promovare.',
        'Am organizat arhitectura magazinului, paginile de produs, experiența responsive, elementele SEO și recomandările pentru campanii digitale.',
        'Brandul dispune de o bază e-commerce clară și ușor de administrat. Indicatorii comerciali sunt publicați doar după validarea lor.',
    ],
    'spalatoria-ozana' => [
        'Spălătoria Ozana | Website și SEO local București',
        'Studiu de caz Spălătoria Ozana: website responsive și optimizare SEO locală pentru prezentarea serviciilor și vizibilitate în căutările din București.',
        'Prezentarea clară a serviciilor și construirea unei baze tehnice care să ajute afacerea să fie găsită în căutările locale relevante.',
        'Am realizat website-ul responsive, structura serviciilor, optimizările SEO de bază, informațiile locale și traseele rapide către contact.',
        'Website-ul oferă acum o experiență mai clară pe mobil și o bază coerentă pentru vizibilitate locală și măsurarea solicitărilor.',
    ],
    'best-tkd' => [
        'Best TKD | Website responsive și administrare personalizată',
        'Studiu de caz Best TKD: website responsive și panou de administrare personalizat pentru prezentarea programelor, activităților și informațiilor utile.',
        'Crearea unui website ușor de administrat, care să organizeze activitatea, programele și informațiile importante într-o experiență clară pe orice ecran.',
        'Am construit arhitectura de conținut, designul responsive, componentele de prezentare și funcțiile de administrare adaptate echipei.',
        'Echipa poate actualiza mai ușor conținutul, iar vizitatorii găsesc rapid informațiile relevante despre activități și programe.',
    ],
    'traffic-restaurant' => [
        'Traffic Restaurant & Lounge | Website, SEO și Ads',
        'Studiu de caz Traffic Restaurant & Lounge: website, SEO local, Google Ads și campanii Meta conectate la rezervări și promovarea restaurantului.',
        'Conectarea prezenței digitale cu rezervările, descoperirea locală și promovarea ofertelor relevante pentru publicul restaurantului.',
        'Am lucrat la website, structură, SEO local, Google Ads, campanii Meta și configurarea acțiunilor importante pentru măsurare.',
        'Ecosistemul digital are acum un traseu mai clar de la descoperire la rezervare. Cifrele comerciale sunt publicate doar cu aprobarea clientului.',
    ],
    'nanu-events' => [
        'Nanu Events | Website de prezentare pentru evenimente',
        'Studiu de caz Nanu Events: website de prezentare responsive, structură de conținut și design modern pentru servicii și proiecte din domeniul evenimentelor.',
        'Prezentarea clară a serviciilor, proiectelor și stilului Nanu Events într-o interfață vizuală coerentă, ușor de folosit pe telefon și desktop.',
        'Am definit arhitectura de conținut, direcția vizuală, paginile principale, componentele responsive și traseul către solicitarea unei oferte.',
        'Nanu Events are acum un website modern, rapid și ușor de parcurs, care susține prezentarea portofoliului și cererile de ofertă.',
    ],
    'auto-la-domiciliu' => [
        'Auto La Domiciliu | Website, SEO și promovare plătită',
        'Studiu de caz Auto La Domiciliu: website de servicii, structură SEO și campanii plătite pentru solicitări de service și diagnoză auto la domiciliu.',
        'Crearea unui traseu simplu de la căutarea serviciului până la solicitarea rapidă a intervenției auto la domiciliu.',
        'Am realizat website-ul, structura serviciilor, optimizarea SEO, paginile comerciale și configurarea campaniilor plătite cu măsurarea acțiunilor.',
        'Clienții pot înțelege mai repede serviciul și pot ajunge direct la solicitare. Datele cantitative sunt publicate numai după validare.',
    ],
];

$pdo = cms_db();
$statement = $pdo->prepare('UPDATE works SET seo_title = ?, meta_description = ?, objective = ?, work_done = ?, results = ?, updated_at = ? WHERE slug = ?');
$now = date('Y-m-d H:i:s');
foreach ($updates as $slug => $content) {
    $statement->execute([$content[0], $content[1], $content[2], $content[3], $content[4], $now, $slug]);
}

foreach ($pdo->query('SELECT w.*, c.name AS category_name FROM works w LEFT JOIN categories c ON c.id = w.category_id')->fetchAll() as $work) {
    cms_generate_work($pdo, $work);
}
cms_refresh_indexes($pdo);
echo "Project content upgraded.\n";

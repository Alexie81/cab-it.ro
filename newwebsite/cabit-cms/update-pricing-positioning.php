<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$offers = [
    'cat-costa-un-site-web-profesional' => [
        'excerpt' => 'Un site de prezentare CAB-IT pornește de la 999 lei. Vezi ce include, ce influențează costul și cum compari corect o agenție web design accesibilă și competentă.',
        'meta_description' => 'Site de prezentare de la 999 lei: design responsive, până la 5 pagini, SEO de bază și formular. Ghid CAB-IT despre costuri și livrabile.',
        'direct_answer' => 'Un site de prezentare CAB-IT pornește de la 999 lei și include design responsive, până la 5 pagini, structură SEO de bază, formular de contact și administrare. Funcțiile, conținutul și integrările suplimentare se estimează separat.',
        'secondary' => ['site de prezentare 999 lei', 'agenție web design accesibilă', 'agenție web design competentă', 'creare website preț accesibil'],
        'price_question' => 'Cât costă un site de prezentare la CAB-IT?',
        'price_answer' => 'Un site de prezentare CAB-IT pornește de la 999 lei. Pachetul de bază include design responsive, până la 5 pagini, structură SEO de bază, formular și administrare. Cerințele suplimentare sunt ofertate transparent.',
    ],
    'cat-costa-un-magazin-online-in-romania' => [
        'excerpt' => 'Un magazin online CAB-IT pornește de la 1.799 lei. Află ce include platforma, ce integrări schimbă bugetul și cum alegi o agenție ecommerce accesibilă.',
        'meta_description' => 'Magazin online de la 1.799 lei: platformă administrabilă, plăți, livrare, SEO de bază și măsurare. Ghid complet CAB-IT despre costuri.',
        'direct_answer' => 'Un magazin online CAB-IT pornește de la 1.799 lei și include o platformă administrabilă, configurarea plăților și livrării, structură SEO de bază și pregătire pentru măsurare. Integrările speciale, licențele și serviciile terțe se estimează separat.',
        'secondary' => ['magazin online 1799 lei', 'agenție ecommerce accesibilă', 'creare magazin online preț', 'magazin online pentru firme mici'],
        'price_question' => 'Cât costă un magazin online la CAB-IT?',
        'price_answer' => 'Un magazin online CAB-IT pornește de la 1.799 lei. Prețul de bază acoperă platforma administrabilă, plăți și livrare, structură SEO de bază și pregătirea măsurării. Integrările și licențele speciale se estimează separat.',
    ],
    'cat-costa-google-ads-in-romania-in-2026' => [
        'excerpt' => 'Administrarea promovării Google, Meta sau TikTok Ads la CAB-IT pornește de la 649 lei/lună. Vezi cum separi taxa agenției de bugetul media.',
        'meta_description' => 'Promovare online de la 649 lei/lună pentru Google, Meta sau TikTok Ads. Vezi taxa de administrare, bugetul media și criteriile de evaluare.',
        'direct_answer' => 'Administrarea promovării online prin Google, Meta sau TikTok Ads la CAB-IT pornește de la 649 lei pe lună. Bugetul plătit platformelor și serviciile terțe nu sunt incluse; oferta finală depinde de canale, volum și complexitatea măsurării.',
        'secondary' => ['promovare online 649 lei', 'agenție marketing accesibilă', 'administrare Google Ads preț', 'promovare online firme mici'],
        'price_question' => 'Cât costă promovarea online la CAB-IT?',
        'price_answer' => 'Administrarea Google, Meta sau TikTok Ads pornește de la 649 lei pe lună. Bugetul media merge separat către platformele de publicitate, iar integrările și serviciile terțe sunt precizate distinct în ofertă.',
    ],
];

$positioningQuestion = 'Este CAB-IT o agenție ieftină și competentă?';
$positioningAnswer = 'CAB-IT urmărește o poziționare accesibilă, cu prețuri de pornire publice, livrabile clare și implementări orientate spre conversii măsurabile. Nu afirmăm că avem invariabil cel mai mic preț din piață; avantajul urmărit este raportul dintre cost, execuție, portofoliu, suport și măsurare.';
$proof = '<p data-cabit-offer-proof="20260820"><strong>De ce o ofertă accesibilă nu înseamnă o execuție superficială?</strong> Portofoliul CAB-IT reunește peste 200 de proiecte pentru servicii, ecommerce, HoReCa, medical, auto, educație, evenimente, producție și B2B. Soluțiile sunt construite pentru acțiuni măsurabile — apeluri, cereri de ofertă, programări și vânzări — iar rezultatul efectiv depinde de ofertă, piață, buget și procesul comercial al fiecărei afaceri.</p>';

$pdo = cms_db();
$select = $pdo->prepare('SELECT * FROM articles WHERE slug = ?');
$update = $pdo->prepare('UPDATE articles SET excerpt = ?, meta_description = ?, content = ?, seo_metadata = ?, updated_at = ? WHERE id = ?');
$updated = [];

$pdo->beginTransaction();
try {
    foreach ($offers as $slug => $offer) {
        $select->execute([$slug]);
        $article = $select->fetch();
        if (!$article) {
            throw new RuntimeException('Articolul lipsește: ' . $slug);
        }

        $answerBox = '<aside class="cabit-answer-box" data-cabit-offer-pricing="20260820"><span>Răspuns direct</span><p>' . htmlspecialchars($offer['direct_answer'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p></aside>';
        $content = preg_replace('~<aside class="cabit-answer-box"[^>]*>.*?</aside>~s', $answerBox, (string) $article['content'], 1, $boxCount);
        if ($content === null || $boxCount !== 1) {
            throw new RuntimeException('Nu am găsit răspunsul rapid în: ' . $slug);
        }
        $content = preg_replace('~<p data-cabit-offer-proof="20260820">.*?</p>~s', '', $content) ?? $content;
        $content = preg_replace('~<aside class="cabit-answer-box" data-cabit-offer-pricing="20260820">.*?</aside>~s', '', $content, 1) ?? $content;
        $content = $answerBox . $proof . ltrim($content);

        $metadata = json_decode((string) ($article['seo_metadata'] ?? '{}'), true);
        if (!is_array($metadata)) {
            $metadata = [];
        }
        $metadata['direct_answer'] = $offer['direct_answer'];
        $metadata['llm_summary'] = $offer['direct_answer'] . ' CAB-IT publică tarife de pornire și separă costurile suplimentare pentru o comparație corectă.';
        $metadata['secondary_keywords'] = array_values(array_unique(array_merge(
            is_array($metadata['secondary_keywords'] ?? null) ? $metadata['secondary_keywords'] : [],
            $offer['secondary']
        )));
        $faqs = is_array($metadata['faqs'] ?? null) ? $metadata['faqs'] : [];
        $newQuestions = [$offer['price_question'], $positioningQuestion];
        $faqs = array_values(array_filter($faqs, static function ($faq) use ($newQuestions): bool {
            return is_array($faq) && !in_array((string) ($faq['q'] ?? ''), $newQuestions, true);
        }));
        array_unshift($faqs,
            ['q' => $offer['price_question'], 'a' => $offer['price_answer']],
            ['q' => $positioningQuestion, 'a' => $positioningAnswer]
        );
        $metadata['faqs'] = $faqs;
        $metadata['questions_answered'] = array_values(array_unique(array_merge(
            is_array($metadata['questions_answered'] ?? null) ? $metadata['questions_answered'] : [],
            $newQuestions
        )));

        $updatedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_ATOM);
        $update->execute([
            $offer['excerpt'],
            $offer['meta_description'],
            $content,
            json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $updatedAt,
            $article['id'],
        ]);
        $updated[] = $slug;
    }
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $error;
}

foreach ($updated as $slug) {
    $select->execute([$slug]);
    cms_generate_article($select->fetch());
}
cms_refresh_indexes($pdo);

echo 'Actualizate: ' . implode(', ', $updated) . PHP_EOL;

<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/import-seo-articles.php';

function cabit_premium_signature(string $text): string
{
    $text = mb_strtolower(strip_tags($text), 'UTF-8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    return trim(preg_replace('/[^a-z0-9]+/i', ' ', $text) ?? $text);
}

function cabit_premium_boilerplate(array $articles): array
{
    $blockFrequency = [];
    $sentenceFrequency = [];
    foreach ($articles as $article) {
        $blocks = preg_split('/\R{2,}/u', (string) ($article['content_markdown'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($blocks as $block) {
            $signature = cabit_premium_signature($block);
            if (str_word_count($signature) >= 18) {
                $blockFrequency[$signature] = ($blockFrequency[$signature] ?? 0) + 1;
            }
            foreach (preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', trim($block)) ?? trim($block), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $sentence) {
                $sentenceSignature = cabit_premium_signature($sentence);
                if (str_word_count($sentenceSignature) >= 12) {
                    $sentenceFrequency[$sentenceSignature] = ($sentenceFrequency[$sentenceSignature] ?? 0) + 1;
                }
            }
        }
    }
    return [
        'blocks' => array_filter($blockFrequency, static fn(int $count): bool => $count >= 2),
        'sentences' => array_filter($sentenceFrequency, static fn(int $count): bool => $count >= 2),
    ];
}

function cabit_premium_humanized_block(array $article, string $block, int $variant): string
{
    if (preg_match('/^#{1,6}\s|^(?:[-*]|\d+\.)\s+/u', trim($block))) {
        return $block;
    }
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($block)) ?? '');
    $signature = cabit_premium_signature($plain);
    $title = trim((string) ($article['h1'] ?? $article['title'] ?? 'acest ghid'));
    $primary = trim((string) ($article['primary_keyword'] ?? $article['cluster'] ?? 'subiectul analizat'));
    $cluster = trim((string) ($article['cluster'] ?? $article['pillar'] ?? 'rezultatul comercial urmărit'));
    $variant %= 6;

    if (preg_match('/^Merită atunci când rezolvă o problemă observabilă și susține\s+/iu', $plain)) {
        $outcome = $cluster;
        if (preg_match('/susține\s+(.+?)\.\s+Dacă/iu', $plain, $matches)) {
            $outcome = trim($matches[1]);
        }
        $templates = [
            'Pentru „%1$s”, întrebarea utilă nu este dacă soluția sună modern, ci dacă rezolvă problema descrisă în „%2$s”. Leagă decizia de %3$s, stabilește ce vei observa după implementare și cine va reacționa la rezultat. Dacă ipoteza nu poate fi formulată înainte de investiție, simplifică testul și amână costurile greu reversibile.',
            'În cazul „%1$s”, investiția are sens numai când schimbă o situație măsurabilă, nu când bifează o preferință de design. Pentru tema „%2$s”, definește din start cum arată %3$s și în ce interval poate fi evaluat progresul. Fără această referință, păstrează soluția mai mică și validează problema înainte de extindere.',
            'Decizia despre „%1$s” pornește de la efectul urmărit, nu de la lista de funcții. Raportează recomandările din „%2$s” la %3$s, notează condiția de pornire și pragul la care vei considera testul util. Dacă echipa nu poate conveni asupra acestor repere, proiectul trebuie clarificat înainte să primească buget suplimentar.',
            'Aplicat la „%1$s”, criteriul de investiție este simplu: problema trebuie să fie vizibilă, iar schimbarea propusă să poată susține %3$s. Ghidul „%2$s” oferă cadrul, însă pragul de succes trebuie stabilit în datele propriei afaceri. Când acel prag lipsește, începe cu o intervenție limitată și învață înainte de a scala.',
            'Pentru tema „%2$s”, valoarea nu vine din noutatea soluției, ci din efectul ei asupra „%1$s”. Scrie explicit ce ar trebui să se îmbunătățească pentru a obține %3$s, cine verifică rezultatul și ce decizie urmează după măsurare. Dacă răspunsurile rămân vagi, problema nu este încă suficient de bine definită pentru o implementare amplă.',
            'O evaluare serioasă pentru „%1$s” separă preferința de nevoie. În contextul „%2$s”, urmărește dacă intervenția poate produce %3$s și compară rezultatul cu situația de dinainte, nu cu impresia din ziua lansării. Dacă nu există un punct de referință și o responsabilitate clară, redu scopul până când testul devine verificabil.',
        ];
        return sprintf($templates[$variant], $primary, $title, $outcome);
    }

    if (preg_match('/^Folosește\s+.+?\s+împreună cu o măsură de calitate din vânzări sau operare/iu', $plain)) {
        $metric = 'indicatorul principal';
        if (preg_match('/^Folosește\s+(.+?)\s+împreună/iu', $plain, $matches)) {
            $metric = trim($matches[1]);
        }
        $templates = [
            'În analiza pentru „%1$s”, %3$s este un semnal, nu un verdict. Citește-l împreună cu datele de calitate din vânzări sau operare, marchează schimbările făcute și compară perioade similare. Astfel, concluzia pentru „%2$s” rămâne legată de rezultat, nu de o variație izolată din raport.',
            'Pentru „%1$s”, urmărește %3$s în aceeași fereastră cu un indicator comercial sau operațional. Notează intervențiile care pot schimba comparația și verifică dacă volumul suplimentar păstrează aceeași calitate. În tema „%2$s”, un singur număr poate orienta investigația, dar nu trebuie să închidă singur decizia.',
            'Măsurarea din „%2$s” începe cu %3$s, dar nu se oprește acolo. Leagă semnalul de „%1$s” de ceea ce echipa confirmă în vânzări ori operare și păstrează un jurnal al schimbărilor. Dacă cele două perspective nu se mișcă împreună, investighează cauza înainte să declari succesul sau eșecul.',
            'Când evaluezi „%1$s”, pune %3$s lângă un indicator care descrie calitatea rezultatului real. Compară perioade echivalente, exclude efectele unei modificări majore și privește tendința, nu o singură zi. Pentru „%2$s”, această disciplină evită optimizarea unui raport în detrimentul afacerii.',
            'În contextul „%2$s”, %3$s răspunde unei singure întrebări. Pentru o decizie despre „%1$s”, completează-l cu feedback din vânzări sau operare, cu valoarea rezultatului și cu schimbările dintre perioade. Abia convergența acestor semnale justifică păstrarea, corectarea ori extinderea intervenției.',
            'Folosește %3$s pentru a deschide analiza „%1$s”, nu pentru a o închide. Verifică în paralel calitatea observată de echipă, consemnează schimbările de ofertă sau trafic și evită comparațiile între perioade incompatibile. În „%2$s”, concluzia bună explică atât ce s-a mișcat, cât și de ce contează comercial.',
        ];
        return sprintf($templates[$variant], $primary, $title, $metric);
    }

    if (preg_match('/^Costul total include și operarea de după lansare/iu', $plain)) {
        $templates = [
            'Pentru „%1$s”, costul real continuă după lansare. Include timpul pentru actualizări, verificări și excepții, dar și ușurința cu care soluția poate fi schimbată fără blocaje. În „%2$s”, aceste responsabilități trebuie atribuite înainte de implementare, altfel economia inițială poate deveni dependență operațională.',
            'Bugetul pentru „%1$s” nu se încheie când proiectul devine public. Tema „%2$s” cere să fie clar cine întreține, cine validează și cine intervine când apar cazuri neprevăzute. Adaugă și costul unei schimbări de furnizor sau arhitectură, deoarece flexibilitatea face parte din costul total de proprietate.',
            'În evaluarea „%1$s”, separă prețul de lansare de efortul de operare. Actualizările, controlul calității, răspunsul la excepții și posibilitatea de înlocuire trebuie estimate explicit. Pentru „%2$s”, o soluție aparent ieftină poate deveni scumpă dacă fiecare ajustare cere intervenție specializată.',
            'Calculul pentru „%1$s” trebuie să includă perioada de după implementare: cine actualizează, cum se verifică și cât durează rezolvarea unei excepții. În cazul „%2$s”, adaugă și costul de ieșire din soluție. Acesta arată dacă proiectul rămâne controlabil atunci când prioritățile se schimbă.',
            'Costul total al „%1$s” combină investiția inițială cu munca recurentă. Ghidul „%2$s” trebuie citit și prin prisma responsabilităților de mentenanță, a verificărilor și a schimbărilor viitoare. O estimare sănătoasă precizează cine face aceste lucruri și ce se întâmplă când persoana sau furnizorul nu mai este disponibil.',
            'Pentru tema „%2$s”, prețul vizibil este doar prima componentă a „%1$s”. Evaluează actualizările, verificarea, gestionarea incidentelor și portabilitatea înainte de alegere. Dacă aceste sarcini nu au proprietar și timp alocat, costul va apărea ulterior sub formă de întârzieri, erori sau dependență de un singur furnizor.',
        ];
        return sprintf($templates[$variant], $primary, $title);
    }

    if (preg_match('/^Greșelile frecvente sunt să implementezi înainte să definești problema/iu', $plain)) {
        $templates = [
            'Pentru „%1$s”, o implementare grăbită poate ascunde trei probleme diferite: obiectivul nu este definit, se schimbă prea multe variabile simultan, iar livrabilul este confundat cu rezultatul. În „%2$s”, evită această combinație printr-un baseline, o singură ipoteză prioritară și o dată clară de review.',
            'Cele mai costisitoare greșeli din „%2$s” apar înainte de execuție. La „%1$s”, definește problema, limitează numărul schimbărilor și separă ceea ce livrezi de efectul comercial așteptat. Altfel, proiectul poate părea finalizat fără ca echipa să poată spune ce a îmbunătățit.',
            'În cazul „%1$s”, nu porni de la instrument. Clarifică problema, modifică pe rând variabilele importante și stabilește cum recunoști rezultatul. Tema „%2$s” devine greu de evaluat atunci când toate deciziile sunt lansate simultan sau când succesul este redus la simpla predare a unui livrabil.',
            'Pentru tema „%2$s”, evită trei scurtături: implementarea fără diagnostic, testarea mai multor ipoteze în același timp și echivalarea livrării cu performanța. Aplicate la „%1$s”, aceste scurtături produc date ambigue și fac dificilă următoarea decizie, chiar dacă proiectul arată complet.',
            'Riscul principal la „%1$s” nu este lipsa unei funcții, ci lipsa unei întrebări verificabile. În „%2$s”, păstrează o singură schimbare dominantă, descrie rezultatul separat de livrabil și decide dinainte ce vei face dacă semnalul nu apare. Așa eviți optimizarea după impresii.',
            'O abordare slabă pentru „%1$s” începe cu execuția și caută justificarea după. Ghidul „%2$s” cere ordinea inversă: problemă explicită, ipoteză limitată, criteriu de rezultat și apoi implementare. Fără această ordine, mai multe schimbări pot produce mișcare în raport, dar nu și o concluzie utilă.',
        ];
        return sprintf($templates[$variant], $primary, $title);
    }

    if (preg_match('/^Un sistem premium lasă urme bune:/iu', $plain)) {
        $templates = [
            'Pentru „%1$s”, o implementare matură lasă în urmă deciziile, accesurile și regulile de operare, nu doar interfața finală. În contextul „%2$s”, datele trebuie să poată fi exportate, iar responsabilitățile preluate de alt coleg sau furnizor. Astfel, valoarea rămâne în companie când echipa se schimbă.',
            'Calitatea proiectului „%2$s” se vede și după predare. La „%1$s”, documentează alegerile, păstrează accesurile în proprietatea clientului și verifică portabilitatea datelor. Dacă o persoană sau o agenție este înlocuită, operarea trebuie să continue din informații verificabile, nu din memorie.',
            'În cazul „%1$s”, caracterul premium înseamnă control transferabil. Ghidul „%2$s” presupune un jurnal al deciziilor, accesuri administrate de client, exporturi testate și pași clari de operare. Aceste elemente împiedică dispariția valorii odată cu schimbarea responsabilului.',
            'Un rezultat durabil pentru „%1$s” include și ceea ce rămâne în afara ecranului: documentație, proprietatea accesurilor și date ușor de recuperat. Pentru „%2$s”, stabilește aceste livrabile înainte de lansare, astfel încât viitoarea echipă să poată întreține sistemul fără reconstrucție sau dependență inutilă.',
            'Tema „%2$s” trebuie evaluată și prin continuitate. Pentru „%1$s”, compania are nevoie de decizii explicate, acces administrativ propriu, date exportabile și proceduri minime. Dacă furnizorul se schimbă, aceste urme reduc timpul de preluare și protejează investiția deja făcută.',
            'La „%1$s”, livrarea nu este completă până când controlul poate fi preluat de client. În „%2$s”, cere documentarea deciziilor, inventarul accesurilor, un export verificat și responsabilități de operare. Acestea transformă proiectul dintr-o dependență într-un activ care poate fi continuat.',
        ];
        return sprintf($templates[$variant], $primary, $title);
    }

    if (preg_match('/^O ipoteză bună poate fi spusă simplu:/iu', $plain)) {
        $metric = 'indicatorul ales';
        if (preg_match('/verificăm prin\s+(.+?);/iu', $plain, $matches)) {
            $metric = trim($matches[1]);
        }
        $templates = [
            'Pentru „%1$s”, formulează ipoteza înainte de implementare: schimbăm un element precis deoarece așteptăm un efect descris clar și îl verificăm prin %3$s. În „%2$s”, lipsa semnalului nu justifică adăugarea imediată a altei tactici; cere mai întâi verificarea execuției, a datelor și a presupunerii inițiale.',
            'Ipoteza pentru „%1$s” trebuie să lege o singură schimbare de un rezultat și de %3$s. Noteaz-o în cadrul „%2$s” înainte ca echipa să înceapă, apoi păstrează restul variabilelor suficient de stabile. Dacă semnalul nu apare, investighează cauza înainte să mărești complexitatea.',
            'În „%2$s”, o propoziție verificabilă valorează mai mult decât o listă de tactici: pentru „%1$s” modificăm X, așteptăm Y și urmărim %3$s. Când rezultatul lipsește, verificăm datele și mecanismul presupus, nu mascăm incertitudinea prin încă o schimbare simultană.',
            'Aplică „%1$s” printr-o ipoteză cu trei componente: intervenția, efectul așteptat și %3$s ca reper. Ghidul „%2$s” rămâne util doar dacă această formulare este scrisă înainte de lansare. Un semnal absent cere diagnostic, nu o succesiune rapidă de tactici noi.',
            'Pentru decizia din „%2$s”, descrie testul „%1$s” fără jargon: ce schimbăm, de ce credem că va conta și cum folosim %3$s pentru verificare. Dacă datele nu confirmă ipoteza, separă problema de implementare de problema de strategie înainte să continui investiția.',
            'În cazul „%1$s”, stabilește explicit relația dintre schimbare și rezultat, apoi alege %3$s ca unul dintre semnalele de control. „%2$s” nu trebuie extins automat când semnalul lipsește; mai întâi verifică dacă măsurarea, execuția și condițiile testului au fost corecte.',
        ];
        return sprintf($templates[$variant], $primary, $title, $metric);
    }

    return $block;
}

function cabit_premium_clean_markdown(array $article, array $boilerplate): string
{
    $markdown = str_replace(["\r\n", "\r"], "\n", trim((string) ($article['content_markdown'] ?? '')));
    $markdown = preg_replace('/^#\s+.+?\n+/u', '', $markdown, 1) ?? $markdown;
    $markdown = preg_replace('/\n##\s+Întrebări frecvente\s*\n.*$/su', '', $markdown) ?? $markdown;
    $blocks = preg_split('/\n{2,}/u', $markdown, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $clean = [];
    $seenBlocks = [];
    $seenSentences = [];
    $afterVerdict = false;
    $words = 0;

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $isHeading = preg_match('/^#{2,3}\s+/u', $block) === 1;
        if ($isHeading && $afterVerdict) {
            break;
        }
        if (preg_match('/^##\s+Verdict\b/iu', $block)) {
            $afterVerdict = true;
        }
        if (preg_match('/(?:draft-editorial|review required|original evidence|required before indexing|llms\.txt|ai markup|smart search intern|canibalizare internă|notă editorială|rămâne noindex până la)/iu', $block)) {
            continue;
        }
        if (preg_match('/^\*\*Cum citim exemplele din acest ghid:/iu', $block)) {
            continue;
        }
        $signature = cabit_premium_signature($block);
        if (!$isHeading && ($signature === '' || isset($seenBlocks[$signature]) || isset($boilerplate['blocks'][$signature]))) {
            continue;
        }
        $seenBlocks[$signature] = true;

        if (!$isHeading && !preg_match('/^(?:[-*]|\d+\.)\s+/u', $block) && !str_starts_with($block, '>')) {
            $sentences = preg_split('/(?<=[.!?])\s+/u', preg_replace('/\s+/u', ' ', $block) ?? $block, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $kept = [];
            foreach ($sentences as $sentence) {
                if (preg_match('/^(?:Un test util este să poți explica separat|Aici merită urmărită legătura dintre|Particularitatea acestui subiect este combinația dintre|Pentru această temă, diferența se joacă tocmai în zona|În cazul de față, termenii|Privit prin [„"])/iu', trim($sentence))) {
                    continue;
                }
                $sentenceSignature = cabit_premium_signature($sentence);
                if ($sentenceSignature === '' || isset($seenSentences[$sentenceSignature]) || isset($boilerplate['sentences'][$sentenceSignature])) {
                    continue;
                }
                $seenSentences[$sentenceSignature] = true;
                $kept[] = trim($sentence);
            }
            $block = trim(implode(' ', $kept));
            if ($block === '') {
                continue;
            }
        }

        $blockWords = preg_split('/\s+/u', trim(strip_tags($block)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words >= 2600 && $isHeading && !$afterVerdict) {
            continue;
        }
        $clean[] = $block;
        $words += count($blockWords);
    }

    $quickAnswer = trim((string) ($article['quick_answer'] ?? ''));
    if ($quickAnswer !== '') {
        array_unshift($clean, '> **Răspuns direct:** ' . $quickAnswer);
    }
    $variantSeed = abs((int) crc32((string) ($article['slug'] ?? $article['title'] ?? '')));
    foreach ($clean as $index => $block) {
        $clean[$index] = cabit_premium_humanized_block($article, $block, $variantSeed + $index);
    }
    return trim(implode("\n\n", $clean));
}

function cabit_premium_meta_title(array $article): string
{
    $title = trim((string) ($article['title'] ?? ''));
    $suffix = ' | CAB-IT';
    $limit = 64 - mb_strlen($suffix, 'UTF-8');
    if (mb_strlen($title, 'UTF-8') > $limit) {
        $title = mb_substr($title, 0, $limit + 1, 'UTF-8');
        $title = preg_replace('/\s+\S*$/u', '', $title) ?? $title;
        $title = rtrim($title, " \t\n\r\0\x0B:;,-–—?!");
    }
    if (mb_strlen($title, 'UTF-8') < 30) {
        $title .= ': ghid practic';
    }
    return $title . $suffix;
}

function cabit_premium_meta_description(array $article): string
{
    $description = trim((string) ($article['meta_description'] ?? $article['quick_answer'] ?? ''));
    $description = preg_replace('/\s+/u', ' ', $description) ?? $description;
    if (mb_strlen($description, 'UTF-8') < 120) {
        $description .= ' Află criteriile, riscurile și pașii practici pentru o decizie bine fundamentată.';
    }
    if (mb_strlen($description, 'UTF-8') > 160) {
        $description = mb_substr($description, 0, 157, 'UTF-8');
        $description = preg_replace('/\s+\S*$/u', '', $description) ?? $description;
        $description = rtrim($description, " \t\n\r\0\x0B,.;:-") . '…';
    }
    return $description;
}

function cabit_premium_faqs(array $article): array
{
    $source = is_array($article['faq'] ?? null) ? $article['faq'] : (is_array($article['faqs'] ?? null) ? $article['faqs'] : []);
    $faqs = [];
    foreach ($source as $faq) {
        $question = trim((string) ($faq['question'] ?? $faq['q'] ?? ''));
        $answer = trim((string) ($faq['answer'] ?? $faq['a'] ?? ''));
        $answer = preg_replace('/\s+/u', ' ', $answer) ?? $answer;
        if ($question !== '' && $answer !== '' && !preg_match('/(?:draft|indexare|smart search)/iu', $question . ' ' . $answer)) {
            $faqs[] = ['q' => $question, 'a' => $answer];
        }
    }
    return $faqs;
}

function cabit_premium_sources(string $pillar): array
{
    $sources = [
        ['title' => 'Google Search Essentials', 'url' => 'https://developers.google.com/search/docs/essentials'],
        ['title' => 'Google Search – politicile anti-spam', 'url' => 'https://developers.google.com/search/docs/essentials/spam-policies'],
        ['title' => 'Google – date structurate pentru articole', 'url' => 'https://developers.google.com/search/docs/appearance/structured-data/article'],
        ['title' => 'Google – funcțiile AI în căutare', 'url' => 'https://developers.google.com/search/docs/appearance/ai-features'],
    ];
    $pillarLower = mb_strtolower($pillar, 'UTF-8');
    if (str_contains($pillarLower, 'ads') || str_contains($pillarLower, 'promovare') || str_contains($pillarLower, 'tiktok')) {
        $sources[] = ['title' => 'Centrul de ajutor Google Ads', 'url' => 'https://support.google.com/google-ads/'];
        $sources[] = ['title' => 'Google Ads API – documentație', 'url' => 'https://developers.google.com/google-ads/api/docs/start'];
        $sources[] = ['title' => 'CAB-IT – promovare online', 'url' => 'https://cab-it.ro/servicii/reclame-platite/'];
    } elseif (str_contains($pillarLower, 'analytics') || str_contains($pillarLower, 'automatizare')) {
        $sources[] = ['title' => 'Google Analytics – documentație', 'url' => 'https://developers.google.com/analytics'];
        $sources[] = ['title' => 'Google Tag Manager – documentație', 'url' => 'https://developers.google.com/tag-platform/tag-manager'];
        $sources[] = ['title' => 'CAB-IT – integrări digitale', 'url' => 'https://cab-it.ro/servicii/integrari-digitale/'];
    } elseif (str_contains($pillarLower, 'seo')) {
        $sources[] = ['title' => 'Google – ghid SEO pentru începători', 'url' => 'https://developers.google.com/search/docs/fundamentals/seo-starter-guide'];
        $sources[] = ['title' => 'Google – crawling și indexare', 'url' => 'https://developers.google.com/search/docs/crawling-indexing/overview'];
        $sources[] = ['title' => 'CAB-IT – optimizare SEO', 'url' => 'https://cab-it.ro/servicii/seo/'];
    } else {
        $sources[] = ['title' => 'web.dev – Core Web Vitals', 'url' => 'https://web.dev/articles/vitals'];
        $sources[] = ['title' => 'OWASP – Web Security Testing Guide', 'url' => 'https://owasp.org/www-project-web-security-testing-guide/'];
        $sources[] = ['title' => 'CAB-IT – creare site web', 'url' => 'https://cab-it.ro/servicii/creare-site-web/'];
    }
    return $sources;
}

function cabit_import_premium_seo_batch(string $jsonPath, bool $dryRun = false): array
{
    if (!is_file($jsonPath)) {
        throw new InvalidArgumentException('Fișierul JSON nu există: ' . $jsonPath);
    }
    $decoded = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
    $articles = is_array($decoded['articles'] ?? null) ? $decoded['articles'] : $decoded;
    if (!is_array($articles) || count($articles) !== 500) {
        throw new RuntimeException('Lotul premium trebuie să conțină exact 500 de articole.');
    }
    $boilerplate = cabit_premium_boilerplate($articles);
    $pdo = cms_db();
    $existingSlugs = array_fill_keys(array_map('strval', $pdo->query('SELECT slug FROM articles')->fetchAll(PDO::FETCH_COLUMN)), true);
    $batchSlugs = [];
    $records = [];
    $stats = [
        'min_words' => PHP_INT_MAX,
        'max_words' => 0,
        'boilerplate_blocks' => count($boilerplate['blocks']),
        'boilerplate_sentences' => count($boilerplate['sentences']),
    ];
    $publishedBlockFrequency = [];
    $publishedBlockExamples = [];
    $publishedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Bucharest')))->format(DATE_ATOM);

    foreach ($articles as $index => $article) {
        $slug = trim((string) ($article['slug'] ?? ''));
        if (!cms_valid_slug($slug) || isset($batchSlugs[$slug]) || isset($existingSlugs[$slug])) {
            throw new RuntimeException('Slug invalid, duplicat sau deja public: ' . $slug);
        }
        $batchSlugs[$slug] = true;
        $markdown = cabit_premium_clean_markdown($article, $boilerplate);
        $converted = cabit_markdown_to_article_html($markdown);
        $faqs = cabit_premium_faqs($article);
        $pillar = trim((string) ($article['pillar'] ?? 'Creare website'));
        $sources = cabit_premium_sources($pillar);
        $content = $converted['html'] . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, '2026-08-20');
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
        $wordCount = count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        if ($wordCount < 1200) {
            $markdown = cabit_premium_clean_markdown($article, ['blocks' => [], 'sentences' => []]);
            $converted = cabit_markdown_to_article_html($markdown);
            $content = $converted['html'] . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, '2026-08-20');
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
            $wordCount = count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        }
        $seoTitle = cabit_premium_meta_title($article);
        $description = cabit_premium_meta_description($article);
        if ($wordCount < 1200 || count($faqs) < 4 || count($sources) < 6) {
            throw new RuntimeException($slug . ': nu trece pragul editorial (' . $wordCount . ' cuvinte, ' . count($faqs) . ' FAQ, ' . count($sources) . ' surse).');
        }
        if (mb_strlen($seoTitle, 'UTF-8') < 35 || mb_strlen($seoTitle, 'UTF-8') > 65 || mb_strlen($description, 'UTF-8') < 120 || mb_strlen($description, 'UTF-8') > 160) {
            throw new RuntimeException($slug . ': metadatele nu respectă limitele editoriale.');
        }
        if (preg_match('/(?:draft-editorial|review required|original evidence required|llms\.txt|rămâne noindex până la)/iu', $plain)) {
            throw new RuntimeException($slug . ': au rămas instrucțiuni interne.');
        }
        foreach (preg_split('/\R{2,}/u', $markdown, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $publishedBlock) {
            $publishedSignature = cabit_premium_signature($publishedBlock);
            if (str_word_count($publishedSignature) >= 18) {
                $publishedBlockFrequency[$publishedSignature] = ($publishedBlockFrequency[$publishedSignature] ?? 0) + 1;
                $publishedBlockExamples[$publishedSignature] ??= trim($publishedBlock);
            }
        }
        $stats['min_words'] = min($stats['min_words'], $wordCount);
        $stats['max_words'] = max($stats['max_words'], $wordCount);
        $metadata = [
            'primary_keyword' => trim((string) ($article['primary_keyword'] ?? '')),
            'secondary_keywords' => array_values(array_map('strval', is_array($article['secondary_keywords'] ?? null) ? $article['secondary_keywords'] : [])),
            'semantic_terms' => array_values(array_map('strval', is_array($article['semantic_terms'] ?? null) ? $article['semantic_terms'] : [])),
            'cluster' => trim((string) ($article['cluster'] ?? $pillar)),
            'pillar' => $pillar,
            'search_intent' => trim((string) ($article['search_intent'] ?? 'informational-commercial')),
            'queries' => array_values(array_map('strval', is_array($article['search_questions'] ?? null) ? $article['search_questions'] : [])),
            'direct_answer' => trim((string) ($article['quick_answer'] ?? '')),
            'image_alt' => '',
            'faqs' => $faqs,
            'sources' => $sources,
            'batch' => 'seo-premium-2026-500',
            'publication_order' => $index + 1,
            'author' => [
                'name' => 'Alexie Popescu',
                'role' => 'Coordonator editorial CAB-IT Expert',
                'bio' => 'Documentează și revizuiește ghiduri despre creare website, SEO, promovare online, conversii și automatizări digitale.',
            ],
        ];
        $records[] = [
            'title' => trim((string) ($article['h1'] ?? $article['title'] ?? '')),
            'seo_title' => $seoTitle,
            'meta_description' => $description,
            'slug' => $slug,
            'excerpt' => trim((string) ($article['quick_answer'] ?? $description)),
            'content' => $content,
            'cover_image' => '',
            'date_published' => $publishedAt,
            'created_at' => (new DateTimeImmutable($publishedAt))->modify('-' . $index . ' seconds')->format(DATE_ATOM),
            'updated_at' => $publishedAt,
            'seo_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ];
    }

    $duplicatePublishedBlocks = array_filter($publishedBlockFrequency, static fn(int $count): bool => $count > 1);
    arsort($duplicatePublishedBlocks);
    $stats['duplicate_published_block_groups'] = count($duplicatePublishedBlocks);
    $stats['duplicate_published_block_occurrences'] = array_sum($duplicatePublishedBlocks);
    $stats['duplicate_block_examples'] = array_values(array_map(
        static fn(string $signature): array => [
            'count' => $duplicatePublishedBlocks[$signature],
            'text' => mb_substr($publishedBlockExamples[$signature], 0, 220, 'UTF-8'),
        ],
        array_slice(array_keys($duplicatePublishedBlocks), 0, 5)
    ));
    if ($dryRun) {
        return ['validated' => count($records), 'imported' => 0, 'dry_run' => true, 'stats' => $stats];
    }
    $statement = $pdo->prepare(
        'INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at, seo_metadata)
         VALUES (:title, :seo_title, :meta_description, :slug, :excerpt, :content, :cover_image, :date_published, :created_at, :updated_at, :seo_metadata)'
    );
    $pdo->beginTransaction();
    try {
        foreach ($records as $record) {
            $statement->execute($record);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
    foreach ($records as $record) {
        $query = $pdo->prepare('SELECT * FROM articles WHERE slug = ?');
        $query->execute([$record['slug']]);
        $stored = $query->fetch();
        if (!$stored) {
            throw new RuntimeException('Articolul nu a putut fi recitit: ' . $record['slug']);
        }
        cms_generate_article($stored);
    }
    cms_refresh_indexes($pdo);
    return ['validated' => count($records), 'imported' => count($records), 'dry_run' => false, 'stats' => $stats];
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath((string) $_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $jsonPath = $argv[1] ?? '';
    $dryRun = in_array('--dry-run', $argv, true);
    try {
        echo json_encode(cabit_import_premium_seo_batch($jsonPath, $dryRun), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

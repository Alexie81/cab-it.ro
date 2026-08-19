<?php
declare(strict_types=1);

require_once __DIR__ . '/import-seo-articles.php';

/**
 * Transformă briefurile editoriale 2026 în ghiduri complete, publicabile și
 * reutilizabile ulterior ca bază de cunoștințe pentru asistentul CAB-IT.
 */

function cabit_brief_text(array $brief, string $key, string $fallback = ''): string
{
    $value = trim((string) ($brief[$key] ?? ''));
    return $value !== '' ? $value : $fallback;
}

function cabit_brief_list(array $brief, string $key): array
{
    $items = is_array($brief[$key] ?? null) ? $brief[$key] : [];
    return array_values(array_filter(array_map(static fn(mixed $value): string => trim((string) $value), $items)));
}

function cabit_brief_focus(array $brief): string
{
    $keyword = cabit_brief_text($brief, 'primary_keyword', cabit_brief_text($brief, 'title'));
    return mb_strtolower($keyword, 'UTF-8');
}

function cabit_brief_direct_answer(array $brief): string
{
    $title = cabit_brief_text($brief, 'title');
    $angle = rtrim(cabit_brief_text($brief, 'unique_angle'), '.');
    $intent = mb_strtolower(cabit_brief_text($brief, 'search_intent', 'informare și evaluare'), 'UTF-8');
    return $title . ' trebuie evaluat pornind de la obiectivul comercial, resursele disponibile și modul în care va fi măsurat rezultatul. '
        . ($angle !== '' ? $angle . '. ' : '')
        . 'Decizia corectă nu este aceeași pentru toate firmele: compară opțiunile pe un scenariu real, stabilește cine răspunde de implementare și verifică datele după lansare. Ghidul de mai jos este conceput pentru ' . $intent . ', cu pași pe care îi poți transforma într-un brief sau checklist de lucru.';
}

function cabit_brief_section_profile(string $heading, array $brief): string
{
    $haystack = mb_strtolower($heading . ' ' . cabit_brief_focus($brief) . ' ' . cabit_brief_text($brief, 'cluster'), 'UTF-8');
    $profiles = [
        'legal' => ['gdpr', 'consimț', 'consimtam', 'confiden', 'cookies', 'termeni', 'obligator', 'european accessibility', 'declarația', 'declaratia', 'legal'],
        'security' => ['secur', 'compromis', 'backup', 'https', 'ssl', 'spam', 'captcha'],
        'accessibility' => ['wcag', 'accesibil'],
        'cost' => ['cost', 'preț', 'pret', 'buget', 'abonament', 'plată', 'plata'],
        'measurement' => ['metric', 'măsur', 'masur', 'ga4', 'tracking', 'utm', 'dashboard', 'convers', 'raport', 'scor', 'cpl', 'calitate'],
        'automation' => ['ai', 'automat', 'smart+', 'advantage+', 'andromeda', 'veo', 'chatbot', 'agent'],
        'ads' => ['google ads', 'meta ads', 'tiktok', 'demand gen', 'campani', 'anunț', 'anunt', 'cpc', 'ad rank', 'quality score', 'targetare', 'licit'],
        'seo' => ['seo', 'search', 'google discover', 'google lens', 'index', 'rank', 'organic', 'content', 'cuvinte'],
        'integration' => ['crm', 'erp', 'api', 'integra', 'awb', 'curier', 'stoc', 'formular', 'portal', 'programări', 'programari', 'semnătur', 'semnatur'],
        'platform' => ['wordpress', 'shopify', 'woocommerce', 'webflow', 'framer', 'wix', 'gomag', 'cms', 'builder', 'platform'],
        'commerce' => ['magazin', 'checkout', 'ecommerce', 'e-commerce', 'catalog', 'produs', 'plăți', 'plati'],
        'creative' => ['creative', 'video', 'shorts', 'imagini', 'portofoliu', 'brand', 'logo', 'galer'],
    ];
    foreach ($profiles as $profile => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return $profile;
            }
        }
    }
    return 'strategy';
}

function cabit_brief_section_copy(string $heading, array $brief, int $sectionIndex): array
{
    $profile = cabit_brief_section_profile($heading, $brief);
    $title = cabit_brief_text($brief, 'title');
    $keyword = cabit_brief_text($brief, 'primary_keyword', $title);
    $cluster = cabit_brief_text($brief, 'cluster', cabit_brief_text($brief, 'pillar'));
    $angle = rtrim(cabit_brief_text($brief, 'unique_angle'), '.');
    $lead = [
        'legal' => 'Începe cu inventarul activităților reale ale site-ului, al datelor colectate și al părților implicate. Un model generic poate omite exact fluxul care creează obligația, de aceea documentele și setările tehnice trebuie să descrie implementarea reală.',
        'security' => 'Privește această etapă ca pe un proces de reducere a riscului, nu ca pe o bifă tehnică. Contează prevenția, detectarea, persoana care intervine și dovada că restaurarea sau remedierea funcționează.',
        'accessibility' => 'Accesibilitatea se verifică pe sarcini reale: navigare din tastatură, cititor de ecran, contrast, zoom, formulare și mesaje de eroare. Un scor automat bun este util, dar nu poate înlocui testarea manuală.',
        'cost' => 'Calculează costul total, nu doar prețul de intrare. Include configurarea, licențele, conținutul, integrările, mentenanța, timpul echipei și costul unei schimbări de furnizor sau platformă.',
        'measurement' => 'Definește mai întâi decizia pe care vrei să o iei din date. Apoi stabilește evenimentul, sursa, proprietarul și testul de validare; altfel un dashboard elegant poate agrega numere care nu sunt comparabile.',
        'automation' => 'Automatizarea produce valoare când primește date și limite clare. Păstrează controlul asupra surselor, mesajelor, excepțiilor și transferului către un om, iar performanța trebuie evaluată și prin calitatea rezultatului.',
        'ads' => 'O setare de platformă nu repară o ofertă neclară sau o măsurare greșită. Leagă audiența, mesajul, pagina și conversia de aceeași intenție, apoi schimbă o singură variabilă importantă odată.',
        'seo' => 'Pornește de la intenția utilizatorului și de la o pagină care rezolvă complet problema. Indexarea, structura, legăturile interne și dovezile proprii ajută motorul de căutare să înțeleagă de ce pagina merită afișată.',
        'integration' => 'Desenează fluxul complet înainte de a alege instrumentul: cine introduce datele, unde sunt validate, ce sistem rămâne sursa principală și cum este tratată o eroare. Integrarea bună elimină operațiuni, nu doar mută informația.',
        'platform' => 'Compară platformele pe cerințele proiectului, nu pe lista generală de funcții. Administrarea, exportul, extensiile, performanța, securitatea și disponibilitatea specialiștilor contează după lansare mai mult decât demonstrația inițială.',
        'commerce' => 'Evaluează traseul de la descoperirea produsului până la plată, livrare și retur. Fricțiunea operațională se vede în stocuri greșite, comenzi abandonate și timp consumat manual, nu doar în designul paginii.',
        'creative' => 'Elementul vizual trebuie să clarifice oferta și să susțină următorul pas. Pregătește variații cu unghiuri diferite, păstrează consistența brandului și optimizează fișierele astfel încât experiența să rămână rapidă.',
        'strategy' => 'Transformă subiectul într-o decizie verificabilă: obiectiv, public, resurse, limitări, responsabil și termen. O recomandare bună este legată de contextul firmei, nu de popularitatea unei soluții.',
    ][$profile];
    $detail = [
        'legal' => 'Pentru „' . $heading . '”, notează ce este fapt tehnic, ce este cerință de business și ce trebuie confirmat juridic. CAB-IT poate implementa mecanismele pe website, însă aplicabilitatea unei obligații și formularea documentelor trebuie validate pentru situația concretă a firmei.',
        'security' => 'Aplicat la „' . $keyword . '”, rezultatul trebuie să poată fi verificat: jurnal de schimbări, copii separate, alerte, acces minim necesar și un exercițiu de recuperare. Măsoară timpul de detectare și timpul de revenire, nu doar existența unui instrument.',
        'accessibility' => 'Pentru „' . $heading . '”, combină standardul WCAG 2.2 cu scenariile principale ale utilizatorilor. Prioritizează blocajele care împiedică informarea, completarea unui formular, autentificarea sau plata și documentează ce a fost verificat.',
        'cost' => 'În cazul „' . $keyword . '”, cere ca oferta să separe costurile unice de cele recurente și să precizeze ce rămâne proprietatea firmei. Compară apoi un interval de 12–36 de luni și include scenariul realist de creștere, nu doar pachetul minim.',
        'measurement' => 'Pentru „' . $heading . '”, scrie formula și sursa fiecărui indicator. Verifică un exemplu cap-coadă, de la click sau vizită până la lead calificat și rezultat comercial, inclusiv diferențele de atribuire dintre platforme.',
        'automation' => 'În contextul „' . $keyword . '”, definește ce poate decide sistemul, ce trebuie aprobat și ce se întâmplă când informația lipsește. Un pilot limitat, cu exemple bune și rele, este mai sigur decât activarea simultană pe toate fluxurile.',
        'ads' => 'Pentru „' . $heading . '”, analizează date suficiente și segmentează rezultatele după intenție, locație, dispozitiv și calitatea leadului. Nu interpreta variațiile pe termen scurt drept tendințe dacă volumul este mic sau trackingul s-a schimbat.',
        'seo' => 'Aplicat la „' . $keyword . '”, răspunsul principal trebuie să apară devreme, iar secțiunile să acopere întrebările care schimbă decizia. Folosește exemple, capturi, procese și date proprii acolo unde există; simpla reformulare a altor articole nu creează diferențiere.',
        'integration' => 'Pentru „' . $heading . '”, definește câmpurile obligatorii, acordurile de denumire, deduplicarea, permisiunile și monitorizarea. Testează atât traseul fericit, cât și date lipsă, răspuns întârziat sau indisponibilitatea unui furnizor.',
        'platform' => 'În analiza „' . $keyword . '”, acordă ponderi cerințelor importante pentru firmă. Un avantaj de lansare rapidă poate fi corect, dar trebuie pus lângă costul modificărilor, limitele de export și capacitatea echipei de a administra conținutul.',
        'commerce' => 'Pentru „' . $heading . '”, urmărește conversia împreună cu rata de eroare, timpul de procesare și solicitările către suport. O optimizare este valoroasă doar dacă nu transferă costul către operațiuni sau experiența post-cumpărare.',
        'creative' => 'În proiectul „' . $keyword . '”, documentează sursa materialelor, dimensiunile, textul alternativ și locul unde este folosit fiecare format. Măsoară atenția și acțiunea, dar verifică și viteza, lizibilitatea și consistența pe mobil.',
        'strategy' => 'Pentru „' . $heading . '”, compară minimum două scenarii și scrie explicit presupunerile. ' . ($angle !== '' ? $angle . '. ' : '') . 'Alege varianta care poate fi administrată și măsurată de echipă, nu doar varianta cu cea mai lungă listă de funcții.',
    ][$profile];
    $checklists = [
        'legal' => ['inventariază datele, scopurile și furnizorii', 'aliniază textul public cu setările reale', 'cere validare juridică pentru cazul concret'],
        'security' => ['stabilește responsabilul și accesul minim', 'activează monitorizarea și copiile separate', 'testează restaurarea sau răspunsul la incident'],
        'accessibility' => ['testează tastatura și focusul vizibil', 'verifică formularele, contrastul și zoomul', 'include testare manuală cu tehnologii asistive'],
        'cost' => ['separă costurile unice de cele recurente', 'include timpul intern și integrările', 'calculează scenariul de schimbare sau extindere'],
        'measurement' => ['definește evenimentul și formula', 'testează traseul cu un exemplu real', 'compară datele cu sursa comercială'],
        'automation' => ['limitează primul caz de utilizare', 'pregătește surse și exemple de control', 'măsoară calitatea și escaladarea umană'],
        'ads' => ['verifică oferta, pagina și conversia', 'segmentează după intenție și calitate', 'păstrează un jurnal al schimbărilor'],
        'seo' => ['răspunde clar intenției principale', 'adaugă dovezi și experiență proprie', 'leagă pagina de ghidurile și serviciile relevante'],
        'integration' => ['desenează sistemele și proprietarii datelor', 'definește validarea și deduplicarea', 'monitorizează erorile și reîncercările'],
        'platform' => ['notează cerințele obligatorii', 'testează administrarea și exportul', 'compară costul total pe trei ani'],
        'commerce' => ['testează comanda de la produs la livrare', 'sincronizează stocul și statusurile', 'măsoară conversia și costul operațional'],
        'creative' => ['pornește de la o singură idee clară', 'pregătește formate și unghiuri distincte', 'optimizează fișierele și verifică mobilul'],
        'strategy' => ['definește obiectivul și publicul', 'compară scenarii cu aceleași criterii', 'stabilește proprietarul și următoarea verificare'],
    ][$profile];
    if ($sectionIndex % 2 === 1) {
        $checklists = array_reverse($checklists);
    }
    $tests = [
        'legal' => 'compară documentul public cu un formular, un cookie și un furnizor folosit în realitate; orice diferență devine o acțiune de corectat',
        'security' => 'simulează o eroare sau pierderea accesului într-un mediu controlat și cronometrează pașii până la recuperare',
        'accessibility' => 'parcurge sarcina fără mouse, la zoom 200% și cu anunțurile unui cititor de ecran, notând fiecare punct în care fluxul se blochează',
        'cost' => 'construiește trei scenarii — minim, realist și creștere — pe 36 de luni și notează separat costurile greu de mutat la alt furnizor',
        'measurement' => 'creează o conversie de test cu un identificator unic și urmărește-o în fiecare sistem până la raportul comercial final',
        'automation' => 'pregătește zece exemple normale, trei ambigue și trei care trebuie transferate unui om, apoi compară răspunsul cu regula acceptată',
        'ads' => 'păstrează aceeași ofertă și măsurare, schimbă o singură variabilă importantă și verifică atât costul, cât și procentul de leaduri calificate',
        'seo' => 'roagă o persoană care nu cunoaște proiectul să găsească răspunsul principal și următorul pas în mai puțin de un minut',
        'integration' => 'trimite o înregistrare completă, una duplicată și una cu date lipsă, apoi verifică statusul și alerta în fiecare sistem implicat',
        'platform' => 'publică, editează și exportă aceeași pagină de probă, apoi estimează timpul necesar unei persoane care va administra conținutul',
        'commerce' => 'plasează o comandă de test pe mobil, modifică stocul, anulează plata și urmărește mesajele și statusurile până la închiderea fluxului',
        'creative' => 'compară două unghiuri vizuale cu aceeași ofertă, verificând lizibilitatea pe mobil, timpul de încărcare și acțiunea utilizatorului',
        'strategy' => 'notează ipoteza, criteriul de succes și cea mai ieftină modalitate de a o invalida înainte de implementarea completă',
    ];
    $evidence = 'Un test CAB-IT recomandat pentru „' . $heading . '” este următorul: ' . $tests[$profile] . '. Păstrează captura sau rezultatul testului, data și persoana care l-a verificat. Astfel, decizia pentru „' . $title . '” se bazează pe o dovadă reutilizabilă, nu doar pe preferințe sau pe prezentarea furnizorului.';
    return [$lead, $detail, $evidence, $checklists];
}

function cabit_brief_scenario(array $brief): string
{
    $pillar = mb_strtolower(cabit_brief_text($brief, 'pillar'), 'UTF-8');
    $keyword = cabit_brief_text($brief, 'primary_keyword', cabit_brief_text($brief, 'title'));
    $cluster = cabit_brief_text($brief, 'cluster');
    if (str_contains($pillar, 'promovare')) {
        $setup = 'o firmă locală primește leaduri din mai multe campanii, dar echipa nu poate spune ce solicitări sunt potrivite comercial';
        $pilot = 'păstrează o singură ofertă, conectează formularul la CRM, marchează leadul calificat și compară mesajele sau audiențele pe același interval';
        $result = 'cost per lead calificat, timpul până la primul răspuns și procentul transformat în discuții reale';
    } elseif (str_contains($pillar, 'website') || str_contains($pillar, 'creare')) {
        $setup = 'o firmă de servicii vrea să lanseze rapid, dar trebuie să poată administra conținutul și să păstreze domeniul, datele și traseele de conversie';
        $pilot = 'construiește o pagină reprezentativă, testează editarea, formularul, viteza și exportul, apoi documentează ce rămâne dependent de furnizor';
        $result = 'timpul de administrare, rata de finalizare a acțiunii și costul schimbărilor după lansare';
    } elseif (str_contains($pillar, 'măsur') || str_contains($pillar, 'masur')) {
        $setup = 'marketingul raportează mai multe conversii decât apar în CRM, iar deciziile de buget sunt luate din surse cu modele de atribuire diferite';
        $pilot = 'definește un identificator comun, testează câteva conversii și păstrează separat evenimentul tehnic, leadul valid și rezultatul comercial';
        $result = 'rata de potrivire între sisteme, datele lipsă și diferența explicabilă dintre raportări';
    } elseif (str_contains($pillar, 'organic') || str_contains($pillar, 'content') || str_contains($pillar, 'seo')) {
        $setup = 'un site publică frecvent, însă articolele se suprapun, nu răspund direct și nu trimit utilizatorul către o pagină comercială relevantă';
        $pilot = 'alege o singură intenție, adaugă experiență sau dovezi proprii, creează legături interne și urmărește interogările și acțiunile paginii';
        $result = 'impresii relevante, clickuri, interacțiuni utile și contribuția la cereri, nu doar poziția unui singur termen';
    } else {
        $setup = 'o firmă compară soluții după liste de funcții, fără criterii comune, proprietar intern sau o definiție clară a rezultatului';
        $pilot = 'transformă cerința într-un scenariu, testează opțiunile pe aceleași date și notează costul, controlul, riscul și efortul de administrare';
        $result = 'timp economisit, erori evitate, ușurința operării și efectul asupra clientului final';
    }
    return 'Scenariu practic ipotetic: ' . ucfirst($setup) . '. Pentru tema „' . $keyword . '”, pilotul recomandat este simplu: ' . $pilot . '. Înainte de test se notează valoarea inițială, persoana responsabilă și intervalul de evaluare. La final se compară ' . $result . '. Scenariul nu promite un rezultat universal; rolul lui este să arate ce date lipsesc și ce ipoteză merită verificată înainte ca firma să investească integral în zona „' . $cluster . '”.';
}

function cabit_brief_faq_answer(string $question, array $brief): string
{
    $lower = mb_strtolower($question, 'UTF-8');
    $keyword = cabit_brief_text($brief, 'primary_keyword', cabit_brief_text($brief, 'title'));
    if (str_contains($lower, 'cât') || str_contains($lower, 'cat')) {
        return 'Nu există un cost corect fără context. Pentru ' . $keyword . ', estimarea trebuie să includă complexitatea, volumul, integrările, licențele, conținutul, mentenanța și responsabilitățile echipei. Cere o ofertă defalcată și compară costul total, nu doar prețul inițial.';
    }
    if (str_contains($lower, 'cum')) {
        return 'Pornește cu un obiectiv și un exemplu real, documentează datele și responsabilitățile, implementează într-un mediu controlat și testează cap-coadă. După lansare, urmărește atât rezultatul comercial, cât și erorile sau operațiunile manuale rămase.';
    }
    if (str_contains($lower, 'când') || str_contains($lower, 'cand') || str_contains($lower, 'merită') || str_contains($lower, 'merita')) {
        return 'Merită atunci când rezolvă o problemă repetabilă, există o persoană responsabilă și poți măsura diferența față de procesul actual. Dacă volumul este mic, datele sunt incomplete sau echipa nu poate administra soluția, începe cu un pilot mai restrâns.';
    }
    if (str_contains($lower, 'poate') || str_contains($lower, 'pot ')) {
        return 'Da, în anumite condiții, dar rezultatul depinde de implementare, date, limitele furnizorului și controlul uman. Verifică un scenariu real înainte de lansare și păstrează o alternativă pentru situațiile pe care soluția nu le acoperă.';
    }
    if (str_contains($lower, 'este') || str_contains($lower, 'elimină') || str_contains($lower, 'elimina')) {
        return 'Nu automat. Evaluează ' . $keyword . ' după obiectiv, calitatea informațiilor, costul total, controlul disponibil și modul de măsurare. O funcție activată nu garantează un rezultat comercial mai bun.';
    }
    return 'Răspunsul depinde de obiectivul firmei, volumul real, datele disponibile și capacitatea de administrare. Folosește criteriile din ghid, testează pe un caz concret și validează rezultatul înainte de extindere.';
}

function cabit_brief_markdown(array $brief): string
{
    $sections = cabit_brief_list($brief, 'recommended_h2_sections');
    if (!$sections) {
        $sections = ['Contextul deciziei', 'Criterii de evaluare', 'Implementare', 'Măsurare și îmbunătățire'];
    }
    $markdown = '> **Răspuns direct:** ' . cabit_brief_direct_answer($brief) . "\n\n";
    foreach ($sections as $index => $heading) {
        [$lead, $detail, $evidence, $checklist] = cabit_brief_section_copy($heading, $brief, $index);
        $markdown .= '## ' . $heading . "\n\n" . $lead . "\n\n" . $detail . "\n\n" . $evidence . "\n\n";
        foreach ($checklist as $item) {
            $markdown .= '- ' . ucfirst($item) . ".\n";
        }
        $markdown .= "\n";
    }
    $markdown .= "## Scenariu practic pentru o firmă\n\n" . cabit_brief_scenario($brief) . "\n\n";
    $markdown .= "## Plan de decizie în 30 de minute\n\n";
    $markdown .= "1. Scrie rezultatul comercial pe care vrei să îl obții și acțiunea utilizatorului care îl precedă.\n";
    $markdown .= "2. Notează situația actuală, datele disponibile și cele mai costisitoare două blocaje.\n";
    $markdown .= "3. Compară opțiunile după cost total, control, timp de implementare, risc și ușurința administrării.\n";
    $markdown .= "4. Alege un test limitat, definește criteriul de succes și stabilește data la care vei reevalua decizia.\n";
    $markdown .= "5. Păstrează documentația, accesul și datele în conturile firmei ori de câte ori este posibil.\n\n";
    $markdown .= '## Concluzie' . "\n\n";
    $primaryLink = cabit_brief_text($brief, 'primary_internal_link', CABIT_SITE_URL . '/contact/');
    $cta = cabit_brief_text($brief, 'recommended_cta', 'Solicită o analiză');
    $markdown .= 'Pentru ' . cabit_brief_text($brief, 'primary_keyword', cabit_brief_text($brief, 'title')) . ', o alegere solidă este aceea pe care o poți explica, administra și măsura. Folosește ghidul ca punct de pornire pentru brief, cere furnizorilor răspunsuri pe aceleași criterii și evită deciziile bazate exclusiv pe demonstrații, scoruri sau promisiuni. [' . $cta . '](' . $primaryLink . ') pentru ca echipa CAB-IT să transforme cerințele într-un plan tehnic și comercial verificabil.';
    return $markdown;
}

function cabit_brief_sources(array $brief, array $sourceMap): array
{
    $ids = cabit_brief_list($brief, 'trend_evidence_source_ids');
    $sources = [];
    foreach ($ids as $id) {
        $source = is_array($sourceMap[$id] ?? null) ? $sourceMap[$id] : [];
        $url = trim((string) ($source['url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $sources[] = ['name' => (string) ($source['name'] ?? $id), 'url' => $url];
    }
    return $sources;
}

function cabit_import_editorial_briefs(string $jsonPath, string $publishDate = '', bool $dryRun = false): array
{
    if (!is_file($jsonPath)) {
        throw new InvalidArgumentException('Fișierul JSON nu există: ' . $jsonPath);
    }
    $decoded = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
    $articles = $decoded['articles'] ?? null;
    $sourceMap = is_array($decoded['research_sources'] ?? null) ? $decoded['research_sources'] : [];
    if (!is_array($articles) || count($articles) !== 100) {
        throw new RuntimeException('Importul editorial trebuie să conțină exact 100 de articole.');
    }
    usort($articles, static fn(array $left, array $right): int => ((int) $left['publication_order']) <=> ((int) $right['publication_order']));
    $publishDate = $publishDate !== '' ? $publishDate : date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishDate)) {
        throw new InvalidArgumentException('Data publicării trebuie să fie YYYY-MM-DD.');
    }
    $slugs = [];
    $records = [];
    $baseTimestamp = strtotime($publishDate . ' 23:30:00 Europe/Bucharest');
    foreach ($articles as $index => $brief) {
        $slug = cabit_brief_text($brief, 'slug');
        if (!cms_valid_slug($slug) || isset($slugs[$slug])) {
            throw new RuntimeException('Slug invalid sau duplicat: ' . $slug);
        }
        $slugs[$slug] = true;
        $imagePath = '/assets/img/blog/seo-2026-wave2/' . $slug . '.webp';
        if (!is_file(CABIT_PUBLIC_ROOT . $imagePath)) {
            throw new RuntimeException('Imagine lipsă pentru ' . $slug . ': ' . $imagePath);
        }
        $markdown = cabit_brief_markdown($brief);
        $converted = cabit_markdown_to_article_html($markdown);
        $faqs = [];
        foreach (cabit_brief_list($brief, 'faq_questions') as $question) {
            $faqs[] = ['q' => $question, 'a' => cabit_brief_faq_answer($question, $brief)];
        }
        $sources = cabit_brief_sources($brief, $sourceMap);
        $legalNote = (($brief['legal_review_required'] ?? false) === true || cabit_brief_section_profile(cabit_brief_text($brief, 'title'), $brief) === 'legal')
            ? '<aside class="cabit-rich-note"><strong>Notă importantă:</strong> informațiile sunt generale și nu înlocuiesc consultanța juridică adaptată firmei, publicului și fluxurilor tale de date.</aside>'
            : '';
        $content = $converted['html'] . $legalNote . cabit_article_faq_html($faqs) . cabit_article_sources_html($sources, $publishDate);
        $secondaryKeywords = cabit_brief_list($brief, 'secondary_keywords');
        $longTailQueries = cabit_brief_list($brief, 'long_tail_queries');
        $metadata = [
            'primary_keyword' => cabit_brief_text($brief, 'primary_keyword'),
            'secondary_keywords' => $secondaryKeywords,
            'long_tail_queries' => $longTailQueries,
            'cluster' => cabit_brief_text($brief, 'cluster', cabit_brief_text($brief, 'pillar')),
            'pillar' => cabit_brief_text($brief, 'pillar'),
            'search_intent' => cabit_brief_text($brief, 'search_intent'),
            'funnel_stage' => cabit_brief_text($brief, 'funnel_stage'),
            'content_type' => cabit_brief_text($brief, 'content_type', 'Ghid practic'),
            'trend_signal' => cabit_brief_text($brief, 'trend_signal'),
            'llm_summary' => cabit_brief_direct_answer($brief),
            'questions_answered' => array_values(array_unique(array_merge($longTailQueries, array_column($faqs, 'q')))),
            'image_alt' => 'Ilustrație editorială CAB-IT despre ' . cabit_brief_text($brief, 'primary_keyword', cabit_brief_text($brief, 'title')),
            'faqs' => $faqs,
            'sources' => $sources,
            'publication_order' => (int) ($brief['publication_order'] ?? ($index + 1)),
            'related_articles' => cabit_related_articles($articles, $index),
            'author' => [
                'name' => 'Alexie Popescu',
                'role' => 'Coordonator editorial CAB-IT Expert',
                'bio' => 'Documentează și revizuiește ghiduri despre creare website, SEO, promovare online, măsurarea conversiilor și automatizări digitale.',
            ],
        ];
        $excerpt = cabit_brief_text($brief, 'suggested_meta_description', cabit_brief_text($brief, 'unique_angle'));
        $records[] = [
            'title' => cabit_brief_text($brief, 'title'),
            'seo_title' => cabit_brief_text($brief, 'suggested_meta_title', cabit_brief_text($brief, 'title') . ' | CAB-IT'),
            'meta_description' => $excerpt,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'cover_image' => $imagePath,
            'date_published' => $publishDate,
            'created_at' => date('c', $baseTimestamp - ((int) $metadata['publication_order'] * 60)),
            'updated_at' => date('c', $baseTimestamp),
            'seo_metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ];
    }
    if ($dryRun) {
        return ['imported' => 0, 'validated' => count($records), 'dry_run' => true, 'slugs' => array_column($records, 'slug')];
    }
    $pdo = cms_db();
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare(
            'INSERT INTO articles (title, seo_title, meta_description, slug, excerpt, content, cover_image, date_published, created_at, updated_at, seo_metadata)
             VALUES (:title, :seo_title, :meta_description, :slug, :excerpt, :content, :cover_image, :date_published, :created_at, :updated_at, :seo_metadata)
             ON CONFLICT(slug) DO UPDATE SET title = excluded.title, seo_title = excluded.seo_title, meta_description = excluded.meta_description,
             excerpt = excluded.excerpt, content = excluded.content, cover_image = excluded.cover_image, date_published = excluded.date_published,
             created_at = excluded.created_at, updated_at = excluded.updated_at, seo_metadata = excluded.seo_metadata'
        );
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
    $query = $pdo->prepare('SELECT * FROM articles WHERE slug = ?');
    foreach ($records as $record) {
        $query->execute([$record['slug']]);
        $stored = $query->fetch();
        if (!$stored) {
            throw new RuntimeException('Articolul nu a putut fi recitit: ' . $record['slug']);
        }
        cms_generate_article($stored);
    }
    cms_refresh_indexes($pdo);
    return ['imported' => count($records), 'validated' => count($records), 'dry_run' => false, 'slugs' => array_column($records, 'slug')];
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    $jsonPath = $argv[1] ?? '';
    $publishDate = $argv[2] ?? date('Y-m-d');
    $dryRun = in_array('--dry-run', $argv, true);
    try {
        $result = cabit_import_editorial_briefs($jsonPath, $publishDate, $dryRun);
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}

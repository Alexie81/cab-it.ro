<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$articles = [
    'administrare-facebook-instagram-pentru-firme' => [
        'primary' => 'administrare Facebook și Instagram pentru firme',
        'secondary' => ['agenție social media', 'administrare social media București', 'calendar editorial social media', 'promovare Facebook și Instagram'],
        'cluster' => 'Agenție de marketing și promovare online',
        'answer' => 'Administrarea Facebook și Instagram pentru o firmă trebuie să includă strategie, calendar editorial, producție, publicare, moderare și raportare legată de obiective. Nu evalua agenția doar după numărul de postări; verifică dacă mesajele susțin oferta, dacă solicitările sunt urmărite și dacă înveți lunar ce formate atrag publicul potrivit.',
        'plan_title' => 'Cum verifici oferta unei agenții de social media',
        'steps' => ['Cere obiective și indicatori separați pentru notorietate, interacțiune și cereri comerciale.', 'Verifică cine scrie textele, cine produce vizualurile și câte runde de aprobare sunt incluse.', 'Stabilește procesul pentru comentarii, mesaje private, situații sensibile și escaladare către echipa firmei.', 'Solicită un raport care explică deciziile, nu doar reach-ul și numărul de aprecieri.'],
        'measurement' => 'Urmărește lunar acoperirea relevantă, vizitele pe website, conversațiile inițiate, cererile valide și costul conținutului. Compară rezultatele cu oferta, sezonalitatea și resursele disponibile pentru răspuns.',
        'faqs' => [
            ['q' => 'Câte postări pe lună sunt necesare pentru o firmă?', 'a' => 'Nu există un număr universal. Ritmul se stabilește după canale, resurse, obiective și formatele care pot fi susținute constant. Un calendar mai mic, dar relevant și bine produs, poate fi mai util decât publicarea frecventă fără direcție.'],
            ['q' => 'Administrarea include și răspunsurile la mesaje și comentarii?', 'a' => 'Trebuie precizat în ofertă. Unele pachete includ moderare și răspunsuri după un ghid aprobat, iar altele acoperă doar producția și publicarea. Situațiile comerciale sau sensibile trebuie transferate rapid către firmă.'],
            ['q' => 'Cum măsori dacă social media aduce rezultate?', 'a' => 'Configurează linkuri UTM, evenimente și formulare, apoi urmărește vizitele, conversațiile, leadurile și vânzările asistate. Reach-ul și interacțiunile sunt indicatori intermediari, nu rezultatul comercial final.'],
        ],
    ],
    'agenti-ai-pentru-firme' => [
        'primary' => 'agenți AI pentru firme',
        'secondary' => ['agent AI pentru website', 'automatizare cu inteligență artificială', 'chatbot AI pentru firme', 'integrare AI cu CRM'],
        'cluster' => 'Automatizare, AI și integrare comercială',
        'answer' => 'Primul agent AI al unei firme trebuie ales pentru un proces repetitiv, cu surse clare, reguli verificabile și o cale de transfer către un om. Cele mai sigure începuturi sunt căutarea în documentație, clasificarea solicitărilor sau pregătirea unui răspuns, nu deciziile autonome cu impact financiar ori juridic.',
        'plan_title' => 'Cum alegi primul caz de utilizare pentru un agent AI',
        'steps' => ['Listează procesele repetitive și timpul consumat, apoi elimină cazurile cu risc mare sau reguli neclare.', 'Alege sursele aprobate, permisiunile și datele pe care agentul nu are voie să le acceseze.', 'Pregătește exemple normale, ambigue și greșite, plus regula de transfer către un operator uman.', 'Rulează un pilot limitat și măsoară calitatea, timpul economisit, erorile și intervențiile manuale.'],
        'measurement' => 'Un pilot bun raportează rata de rezolvare corectă, întrebările fără răspuns, transferurile către oameni, timpul economisit și incidentele. Extinderea se face numai după ce echipa poate explica și controla aceste rezultate.',
        'faqs' => [
            ['q' => 'Un agent AI poate răspunde singur clienților?', 'a' => 'Poate răspunde în limite bine definite, folosind surse aprobate și reguli de escaladare. Pentru prețuri speciale, reclamații, date personale sau situații neclare este recomandat transferul către un operator uman.'],
            ['q' => 'Ce date îi sunt necesare unui agent AI?', 'a' => 'Doar datele necesare cazului de utilizare: documentație, articole, politici, statusuri sau informații din CRM cu permisiuni controlate. Accesul trebuie limitat, jurnalizat și revizuit periodic.'],
            ['q' => 'Cum verifici dacă agentul AI este util?', 'a' => 'Compară timpul de rezolvare, rata răspunsurilor corecte, escaladările, erorile și satisfacția utilizatorilor cu procesul anterior. Un demo convingător nu înlocuiește testarea pe exemple reale.'],
        ],
    ],
    'automatizarea-proceselor-intr-o-firma' => [
        'primary' => 'automatizarea proceselor într-o firmă',
        'secondary' => ['automatizări pentru firme', 'integrare CRM website', 'automatizare leaduri', 'fluxuri digitale pentru IMM'],
        'cluster' => 'Automatizare, AI și integrare comercială',
        'answer' => 'Automatizarea începe cu procesul, nu cu aplicația. Desenează pașii actuali, elimină activitățile inutile, stabilește sursa corectă a datelor și automatizează întâi o secvență repetitivă cu volum suficient. Păstrează validări, alerte și o procedură manuală pentru excepții.',
        'plan_title' => 'Plan practic pentru automatizarea unui proces',
        'steps' => ['Documentează intrarea, ieșirea, responsabilul, excepțiile și timpul consumat de fiecare pas.', 'Elimină duplicările și definește câmpurile obligatorii înainte de a conecta instrumentele.', 'Construiește un pilot între două sisteme, cu jurnal de execuție și notificare la eroare.', 'Măsoară timpul economisit, erorile, costul lunar și operațiunile manuale rămase.'],
        'measurement' => 'Prioritizează procesele frecvente, stabile și ușor de verificat. Un flux rar sau plin de excepții poate costa mai mult de întreținut decât economisește, chiar dacă este tehnic posibil.',
        'faqs' => [
            ['q' => 'Ce proces merită automatizat primul?', 'a' => 'Alege un proces repetitiv, cu volum suficient, reguli clare și rezultat verificabil, precum transferul leadurilor, notificările, generarea documentelor sau sincronizarea statusurilor.'],
            ['q' => 'Este necesar un CRM pentru automatizare?', 'a' => 'Nu în toate cazurile, dar un CRM sau o bază de date bine administrată ajută când procesul implică leaduri, clienți și etape comerciale. Importantă este existența unei surse unice și corecte a datelor.'],
            ['q' => 'Ce se întâmplă dacă automatizarea eșuează?', 'a' => 'Fluxul trebuie să aibă jurnal, alertă, reîncercare controlată și o procedură manuală. Fără aceste elemente, erorile pot rămâne ascunse și se pot propaga în mai multe sisteme.'],
        ],
    ],
    'cat-costa-un-site-web-profesional' => [
        'primary' => 'cât costă un site web profesional',
        'secondary' => ['preț creare website', 'cost site de prezentare', 'ofertă web design', 'agenție web design București'],
        'cluster' => 'Web design și creare website',
        'answer' => 'Costul unui site web profesional depinde de obiectiv, numărul și tipul paginilor, design, conținut, funcționalități, integrări, SEO tehnic și mentenanță. Compară ofertele după livrabile, proprietate, acces, testare și suport, nu doar după prețul inițial sau numărul de pagini.',
        'plan_title' => 'Cum compari corect două oferte de creare website',
        'steps' => ['Trimite același brief ambilor furnizori, cu obiective, public, pagini și funcționalități.', 'Cere separat costurile pentru strategie, design, dezvoltare, conținut, integrări și mentenanță.', 'Clarifică cine deține domeniul, conturile, designul, codul și accesul de administrator după lansare.', 'Verifică procesul de testare, migrarea SEO, instruirea și perioada de remediere a problemelor.'],
        'measurement' => 'Evaluează investiția prin acțiunile pe care site-ul trebuie să le genereze: cereri, apeluri, programări sau vânzări. Include costul de operare pe cel puțin 12 luni, nu doar suma pentru lansare.',
        'faqs' => [
            ['q' => 'De ce diferă atât de mult prețurile pentru un site?', 'a' => 'Ofertele pot include niveluri diferite de strategie, design personalizat, conținut, funcționalități, testare, SEO și suport. Cere livrabile și responsabilități scrise pentru a compara aceeași arie de lucru.'],
            ['q' => 'Cât costă mentenanța după lansare?', 'a' => 'Costul depinde de platformă, actualizări, securitate, backup, suport și ritmul modificărilor. Oferta trebuie să separe mentenanța tehnică de dezvoltările noi și de serviciile terțe.'],
            ['q' => 'Un site ieftin poate fi suficient?', 'a' => 'Da, pentru un obiectiv simplu și temporar, dacă limitările sunt acceptate. Pentru promovare, SEO, integrări sau administrare pe termen lung, verifică dacă soluția poate fi extinsă fără reconstruire completă.'],
        ],
    ],
    'creare-magazin-online-functionalitati-cost' => [
        'primary' => 'creare magazin online',
        'secondary' => ['cost magazin online', 'funcționalități e-commerce', 'dezvoltare magazin online', 'checkout magazin online'],
        'cluster' => 'E-commerce și operațiuni de magazin',
        'answer' => 'Un magazin online trebuie proiectat în jurul catalogului, căutării, filtrării, checkout-ului, plăților, livrării, stocurilor și operațiunilor zilnice. Costul crește odată cu numărul integrărilor și excepțiilor, nu doar cu numărul produselor. Definește fluxul complet înainte de alegerea platformei.',
        'plan_title' => 'Funcționalități care trebuie clarificate înainte de ofertă',
        'steps' => ['Descrie catalogul: variante, atribute, filtre, prețuri speciale, stocuri și sursa datelor.', 'Stabilește plățile, curierii, facturarea, retururile și mesajele automate pentru fiecare status.', 'Testează checkout-ul pe mobil cu metodele reale de plată și livrare, inclusiv erorile și abandonul.', 'Definește rapoartele, rolurile de administrare, backup-ul și sincronizările cu ERP, CRM sau marketplace-uri.'],
        'measurement' => 'După lansare urmărește rata de adăugare în coș, începerea checkout-ului, finalizarea plății, erorile, rata de retur și timpul de procesare. Aceste date arată unde trebuie prioritizate îmbunătățirile.',
        'faqs' => [
            ['q' => 'Ce influențează cel mai mult costul unui magazin online?', 'a' => 'Integrările, regulile comerciale, importul datelor, variantele de produs, checkout-ul, plățile, curierii și automatizările au de obicei un impact mai mare decât simplul număr de produse.'],
            ['q' => 'Ce platformă este potrivită pentru un magazin online?', 'a' => 'Alegerea depinde de catalog, volum, integrări, buget și resursele de administrare. Compară costul total, controlul asupra datelor și capacitatea de extindere, nu doar viteza lansării.'],
            ['q' => 'SEO trebuie inclus de la început?', 'a' => 'Da. Structura categoriilor, URL-urile, filtrele, datele produselor, canonical, sitemap-ul, viteza și migrarea trebuie planificate înainte de lansare pentru a evita pierderea vizibilității.'],
        ],
    ],
    'creare-site-web-pentru-firme' => [
        'primary' => 'creare site web pentru firme',
        'secondary' => ['creare website', 'agenție web design', 'site de prezentare pentru firmă', 'website care aduce clienți'],
        'cluster' => 'Web design și creare website',
        'answer' => 'Un site web pentru o firmă trebuie să explice rapid cui se adresează, ce problemă rezolvă, de ce oferta este credibilă și care este pasul următor. Paginile de servicii, dovezile, întrebările frecvente, contactul simplu, viteza și măsurarea conversiilor sunt mai importante decât efectele decorative.',
        'plan_title' => 'Structura minimă a unui website care poate genera cereri',
        'steps' => ['Construiește o pagină distinctă pentru fiecare serviciu important și pentru intenția reală a clientului.', 'Adaugă proiecte, recenzii verificabile, proces, echipă și răspunsuri la obiecțiile frecvente.', 'Folosește apeluri la acțiune clare pentru telefon, WhatsApp, formular sau programare, fără pași inutili.', 'Configurează analytics și conversii, apoi verifică viteza, accesibilitatea, indexarea și experiența pe mobil.'],
        'measurement' => 'Urmărește cererile valide, apelurile, rata de completare a formularelor și paginile care asistă conversia. Traficul fără context nu arată dacă website-ul contribuie la vânzări.',
        'faqs' => [
            ['q' => 'Câte pagini trebuie să aibă site-ul unei firme?', 'a' => 'Numărul depinde de servicii și de informațiile necesare deciziei. De regulă sunt utile pagini separate pentru serviciile principale, despre firmă, proiecte sau dovezi, întrebări frecvente și contact.'],
            ['q' => 'Este suficient un website de tip one-page?', 'a' => 'Poate fi suficient pentru o ofertă foarte simplă sau o campanie temporară, dar limitează optimizarea separată a serviciilor, dezvoltarea conținutului și traseele diferite ale utilizatorilor.'],
            ['q' => 'Cum aleg o agenție de web design?', 'a' => 'Verifică proiectele, procesul, drepturile asupra conturilor și fișierelor, modul de testare, suportul și capacitatea de a explica legătura dintre design, conținut, SEO și conversii.'],
        ],
    ],
    'optimizare-seo-pentru-site' => [
        'primary' => 'optimizare SEO pentru site',
        'secondary' => ['servicii SEO', 'audit SEO tehnic', 'optimizare on-page', 'strategie SEO pentru firme'],
        'cluster' => 'SEO și vizibilitate organică',
        'answer' => 'Optimizarea SEO combină accesarea și indexarea corectă, arhitectura informației, pagini potrivite intențiilor de căutare, conținut util, legături interne, performanță și autoritate. Începe cu problemele care blochează Google, apoi dezvoltă paginile cu cerere și măsoară separat impresiile, clicurile și conversiile.',
        'plan_title' => 'Ordinea corectă a unei optimizări SEO',
        'steps' => ['Verifică robots, sitemap, statusurile HTTP, canonical, duplicatele și paginile descoperite dar neindexate.', 'Mapează fiecare grup de căutări la o singură pagină principală pentru a evita canibalizarea.', 'Îmbunătățește titlul, H1, răspunsul direct, secțiunile, imaginile, sursele și legăturile interne.', 'Măsoară în Search Console evoluția pe interogare și pagină, apoi actualizează conținutul după date.'],
        'measurement' => 'Primele semnale sunt creșterea paginilor indexate, impresiile pentru căutări relevante și îmbunătățirea pozițiilor. Clicurile și cererile apar diferit în funcție de concurență, autoritatea domeniului și ritmul de recrawl.',
        'faqs' => [
            ['q' => 'În cât timp apar rezultatele SEO?', 'a' => 'Depinde de starea tehnică, concurență, autoritate și frecvența recrawl-ului. Corecțiile pot produce semnale în câteva săptămâni, iar consolidarea pe expresii competitive necesită de obicei mai mult timp și actualizări continue.'],
            ['q' => 'Este suficient să adaug mai multe cuvinte-cheie?', 'a' => 'Nu. Repetarea expresiilor fără informație nouă poate reduce calitatea. Fiecare pagină trebuie să răspundă unei intenții clare, să fie ușor de accesat și să ofere dovezi, structură și legături utile.'],
            ['q' => 'Cum aleg paginile prioritare pentru SEO?', 'a' => 'Începe cu paginile care au impresii și poziții între aproximativ 8 și 30, serviciile cu valoare comercială și problemele tehnice care afectează mai multe URL-uri. Validează decizia cu Search Console și conversiile.'],
        ],
    ],
    'promovare-online-romania-canale-potrivite' => [
        'primary' => 'promovare online România',
        'secondary' => ['agenție promovare online', 'servicii promovare online', 'strategie marketing digital', 'canale de promovare pentru firme'],
        'cluster' => 'Agenție de marketing și promovare online',
        'answer' => 'Canalul de promovare se alege după cererea existentă, public, ofertă, marjă, ciclu de vânzare și capacitatea de urmărire a rezultatelor. Google poate capta intenția activă, social media poate construi și reactiva interesul, iar SEO dezvoltă vizibilitatea în timp. Un mix bun pornește cu un obiectiv măsurabil.',
        'plan_title' => 'Cum alegi canalul fără să risipești bugetul',
        'steps' => ['Definește acțiunea comercială, valoarea ei și volumul realist pe care echipa îl poate prelua.', 'Separă cererea existentă de publicul care trebuie mai întâi educat sau convins.', 'Pregătește o pagină de destinație și tracking verificat înainte de a cumpăra trafic.', 'Testează un canal principal și unul de suport, apoi realocă bugetul după leaduri și vânzări, nu după clickuri.'],
        'measurement' => 'Raportează costul pe cerere validă, rata de conversie, calitatea leadurilor și valoarea vânzărilor. Compară canalele pe aceeași fereastră de timp și ține cont de conversiile asistate.',
        'faqs' => [
            ['q' => 'Care este cel mai bun canal de promovare online?', 'a' => 'Nu există un canal universal. Alegerea depinde de intenția publicului, tipul ofertei, buget, creativ, durata deciziei și măsurarea disponibilă. Testul trebuie construit în jurul unei conversii reale.'],
            ['q' => 'Cu ce buget începe o firmă mică?', 'a' => 'Bugetul trebuie raportat la costul estimat al unei cereri și la numărul minim de date necesar pentru decizii. Este mai sigur să testezi concentrat decât să împarți o sumă mică între prea multe platforme.'],
            ['q' => 'Am nevoie de o agenție de promovare online?', 'a' => 'O agenție poate ajuta când lipsesc timpul, competențele sau procesele de măsurare. Cere obiective, livrabile, acces la conturi, raportare și reguli clare pentru buget, creativ și optimizare.'],
        ],
    ],
    'promovare-pe-google-campanii-care-aduc-clienti' => [
        'primary' => 'promovare pe Google',
        'secondary' => ['campanii Google Ads', 'agenție Google Ads', 'reclame Google pentru firme', 'leaduri din Google Ads'],
        'cluster' => 'Google Ads și promovare în căutare',
        'answer' => 'O campanie Google Ads aduce solicitări când conversiile sunt măsurate corect, cuvintele-cheie exprimă intenție comercială, anunțul corespunde căutării, iar pagina de destinație explică oferta și următorul pas. Optimizează după leaduri valide și vânzări, nu doar după clickuri sau scorurile platformei.',
        'plan_title' => 'Structura unei campanii Google orientate spre clienți',
        'steps' => ['Definește conversiile și testează apelurile, formularele, WhatsApp și importul leadurilor înainte de lansare.', 'Grupează cuvintele după serviciu și intenție, apoi separă termenii informativi de cei comerciali.', 'Scrie anunțuri specifice și trimite fiecare grup către pagina care răspunde exact promisiunii.', 'Analizează termenii de căutare, cuvintele negative, dispozitivele, locațiile și calitatea leadurilor.'],
        'measurement' => 'Leagă Google Ads de CRM sau de o evidență a solicitărilor pentru a diferenția formularele de leadurile reale. Costul pe click este util operațional, dar costul pe client și valoarea vânzărilor decid rentabilitatea.',
        'faqs' => [
            ['q' => 'În cât timp poate aduce rezultate Google Ads?', 'a' => 'Campania poate genera trafic imediat după aprobare, dar optimizarea necesită date și validarea leadurilor. Viteza rezultatului depinde de cerere, concurență, buget, ofertă și pagina de destinație.'],
            ['q' => 'De ce primesc clickuri, dar nu și solicitări?', 'a' => 'Cauzele frecvente sunt intenția slabă a cuvintelor, termeni nerelevanți, mesajul nepotrivit, o pagină lentă sau neclară, formularul dificil și trackingul incorect. Verifică traseul complet.'],
            ['q' => 'Este necesară o pagină separată pentru campanie?', 'a' => 'Nu întotdeauna, dar pagina trebuie să corespundă exact căutării și anunțului. Pentru servicii sau oferte distincte, o pagină dedicată simplifică mesajul, măsurarea și optimizarea.'],
        ],
    ],
    'redesign-website-semne-pierde-clienti' => [
        'primary' => 'redesign website',
        'secondary' => ['refacere site firmă', 'modernizare website', 'site care pierde clienți', 'audit UX website'],
        'cluster' => 'Web design și optimizarea conversiilor',
        'answer' => 'Un redesign este justificat când site-ul nu mai explică oferta, este dificil pe mobil, se încarcă lent, nu inspiră încredere, nu poate fi administrat sau pierde conversii. Nu porni de la culori; păstrează ce funcționează, analizează datele și protejează URL-urile, conținutul și semnalele SEO.',
        'plan_title' => 'Cum refaci site-ul fără să pierzi ce funcționează',
        'steps' => ['Inventariază paginile, traficul, conversiile, linkurile și interogările înainte de modificări.', 'Prioritizează problemele de mesaj, structură, mobil, viteză și accesibilitate pe baza sarcinilor reale.', 'Testează prototipurile cu utilizatori și păstrează o mapare completă între URL-urile vechi și cele noi.', 'După lansare verifică redirecturile, canonical, sitemap-ul, analytics, formularele și evoluția în Search Console.'],
        'measurement' => 'Compară rata de conversie, finalizarea formularelor, apelurile, viteza și vizibilitatea organică înainte și după lansare. Ține cont de sezonalitate și nu schimba simultan toate canalele de achiziție.',
        'faqs' => [
            ['q' => 'Cât de des trebuie refăcut un website?', 'a' => 'Nu există un interval fix. Redesignul este justificat de probleme comerciale, tehnice sau de administrare, nu doar de vechime. Uneori sunt suficiente optimizări locale și actualizarea conținutului.'],
            ['q' => 'Un redesign poate afecta SEO?', 'a' => 'Da, dacă se schimbă URL-uri, conținut, legături interne, heading-uri sau performanță fără migrare. Folosește redirecturi, păstrează paginile valoroase și monitorizează indexarea după lansare.'],
            ['q' => 'Trebuie schimbat tot designul?', 'a' => 'Nu. Păstrează elementele recunoscute și fluxurile care funcționează. Prioritizează mesajul, navigarea, mobilul, accesibilitatea și acțiunile comerciale înaintea schimbărilor pur estetice.'],
        ],
    ],
    'seo-local-bucuresti' => [
        'primary' => 'SEO local București',
        'secondary' => ['optimizare Google Maps', 'Google Business Profile București', 'agenție SEO local', 'promovare locală București'],
        'cluster' => 'SEO local și vizibilitate în București',
        'answer' => 'SEO local în București combină un profil Google Business complet, informații consecvente despre firmă, pagini locale utile, recenzii reale, date structurate și un website rapid pe mobil. Nu crea pagini aproape identice pentru fiecare zonă; explică serviciul, acoperirea și dovezile relevante pentru locație.',
        'plan_title' => 'Plan de optimizare pentru căutările locale',
        'steps' => ['Completează categoria, serviciile, programul, telefonul, website-ul și imaginile în Google Business Profile.', 'Păstrează numele, adresa și telefonul consecvente pe website și în sursele importante.', 'Construiește pagini locale doar când există informație, proiecte, acoperire și valoare distinctă pentru utilizator.', 'Cere recenzii după interacțiuni reale și răspunde util, fără șabloane sau stimulente nepermise.'],
        'measurement' => 'Urmărește interogările locale, afișările profilului, apelurile, direcțiile, vizitele pe website și cererile validate. Separă căutările de brand de cele pentru servicii și locații.',
        'faqs' => [
            ['q' => 'Cât durează să apari mai sus în Google Maps?', 'a' => 'Depinde de relevanță, proximitate, concurență, completitudinea profilului, recenzii și autoritatea website-ului. Optimizarea poate îmbunătăți semnalele, dar poziția diferă în funcție de locația celui care caută.'],
            ['q' => 'Am nevoie de pagini pentru fiecare sector din București?', 'a' => 'Numai dacă poți oferi informație și dovezi distincte pentru fiecare zonă. Paginile aproape identice, create doar pentru cuvinte-cheie, pot fi considerate doorway pages și oferă o experiență slabă.'],
            ['q' => 'Recenziile ajută SEO local?', 'a' => 'Recenziile reale ajută încrederea și oferă semnale despre experiența clienților. Cere-le constant, răspunde util și nu cumpăra recenzii. Calitatea serviciului rămâne baza.'],
        ],
    ],
    'seo-sau-google-ads' => [
        'primary' => 'SEO sau Google Ads',
        'secondary' => ['SEO vs Google Ads', 'promovare organică sau plătită', 'buget SEO și PPC', 'strategie de marketing în Google'],
        'cluster' => 'SEO și Google Ads pentru lead generation',
        'answer' => 'Google Ads este potrivit când ai nevoie de cerere rapidă și poți plăti pentru fiecare accesare, iar SEO construiește treptat vizibilitate organică pe pagini utile. Multe firme le combină: Ads validează oferta și cuvintele comerciale, iar SEO dezvoltă paginile care pot atrage trafic în timp.',
        'plan_title' => 'Cum alegi mixul potrivit între SEO și Google Ads',
        'steps' => ['Stabilește cât de repede ai nevoie de rezultate și ce buget lunar poate fi susținut fără întreruperi.', 'Verifică dacă există cerere comercială și dacă website-ul poate converti traficul în solicitări măsurabile.', 'Folosește Ads pentru testarea rapidă a mesajelor și SEO pentru pagini, întrebări și subiecte cu valoare repetată.', 'Compară costul pe lead, calitatea cererilor și contribuția fiecărui canal pe o perioadă suficientă.'],
        'measurement' => 'Nu compara clicul organic gratuit cu clicul plătit izolat. Include costul conținutului, administrării, landing page-urilor, trackingului și timpul până la rezultat, apoi raportează la leaduri și vânzări.',
        'faqs' => [
            ['q' => 'Ce aleg dacă am nevoie rapid de clienți?', 'a' => 'Google Ads poate genera trafic mai repede dacă există cerere, buget, tracking și o pagină bună. În paralel, începe SEO pentru a construi o sursă de vizibilitate mai stabilă în timp.'],
            ['q' => 'SEO este mai ieftin decât Google Ads?', 'a' => 'Nu automat. SEO implică audit, conținut, implementare și autoritate, iar rezultatele apar în timp. Google Ads are cost media direct. Compară costul total și valoarea leadurilor pe aceeași perioadă.'],
            ['q' => 'Pot folosi datele din Google Ads pentru SEO?', 'a' => 'Da. Termenii de căutare, mesajele și conversiile pot arăta ce intenții și beneficii au valoare. Datele trebuie interpretate cu atenție, deoarece comportamentul traficului plătit nu este identic cu cel organic.'],
        ],
    ],
];

$pdo = cms_db();
$select = $pdo->prepare('SELECT * FROM articles WHERE slug = ?');
$update = $pdo->prepare('UPDATE articles SET content = ?, seo_metadata = ?, updated_at = ? WHERE id = ?');
$updated = 0;

$pdo->beginTransaction();
try {
    foreach ($articles as $slug => $data) {
        $select->execute([$slug]);
        $article = $select->fetch();
        if (!$article) {
            throw new RuntimeException('Articolul lipsește: ' . $slug);
        }
        $content = (string) $article['content'];
        if (!str_contains($content, 'data-cabit-legacy-enrichment="20260819"')) {
            $answer = '<aside class="cabit-answer-box" data-cabit-legacy-enrichment="20260819"><span>Răspuns rapid</span><p>' . cms_e($data['answer']) . '</p></aside>';
            $plan = '<section class="cabit-legacy-action-plan"><h2>' . cms_e($data['plan_title']) . '</h2><ol>';
            foreach ($data['steps'] as $step) {
                $plan .= '<li>' . cms_e($step) . '</li>';
            }
            $plan .= '</ol><p><strong>Ce măsori:</strong> ' . cms_e($data['measurement']) . '</p></section>';
            $faq = '<section class="cabit-article-faq" id="intrebari-frecvente" aria-label="Întrebări frecvente"><h2>Întrebări frecvente</h2>';
            foreach ($data['faqs'] as $item) {
                $faq .= '<details><summary>' . cms_e($item['q']) . '</summary><p>' . cms_e($item['a']) . '</p></details>';
            }
            $faq .= '</section>';
            $content = $answer . $content . $plan . $faq;
        }
        $metadata = cms_article_metadata($article);
        $metadata['primary_keyword'] = $data['primary'];
        $metadata['secondary_keywords'] = $data['secondary'];
        $metadata['cluster'] = $data['cluster'];
        $metadata['image_alt'] = 'Ilustrație editorială CAB-IT despre ' . $data['primary'];
        $metadata['faqs'] = $data['faqs'];
        $metadata['author'] = [
            'name' => 'Alexie Popescu',
            'role' => 'Coordonator editorial CAB-IT Expert',
            'bio' => 'Documentează și revizuiește ghiduri despre creare website, SEO, promovare online, măsurarea conversiilor și automatizări digitale.',
        ];
        $update->execute([
            $content,
            json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            date('Y-m-d H:i:s'),
            (int) $article['id'],
        ]);
        $updated++;
    }
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}

$allArticles = $pdo->query('SELECT * FROM articles ORDER BY id')->fetchAll();
foreach ($allArticles as $article) {
    cms_generate_article($article);
}
cms_update_blog_index($pdo);
cms_update_sitemap($pdo);

echo 'Legacy articles enriched: ' . $updated . '; all articles regenerated: ' . count($allArticles) . PHP_EOL;

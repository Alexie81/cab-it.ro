(function () {
  "use strict";

  var doc = document;
  var body = doc.body;
  var runtimeScript = doc.currentScript || doc.querySelector('script[src*="cabit-next.js"]');
  var siteRootUrl = runtimeScript ? new URL("../../", runtimeScript.src) : new URL("/", window.location.href);
  var siteRootPath = siteRootUrl.pathname.endsWith("/") ? siteRootUrl.pathname : siteRootUrl.pathname + "/";

  function localizeRootPaths(scope) {
    if (siteRootPath === "/") return;
    scope.querySelectorAll('[href^="/"],[src^="/"],[action^="/"]').forEach(function (element) {
      ["href", "src", "action"].forEach(function (attribute) {
        var value = element.getAttribute(attribute);
        if (!value || value.indexOf("//") === 0 || value.indexOf(siteRootPath) === 0) return;
        element.setAttribute(attribute, siteRootPath + value.replace(/^\/+/, ""));
      });
    });
  }

  function headerMarkup() {
    return '<header class="next-header" data-site-header>' +
      '<div class="next-nav-wrap">' +
        '<a class="next-logo" href="/" aria-label="CAB-IT Expert — Acasă"><img src="/assets/img/brand/cab-it-header-symbol-clean.webp" alt="Simbol CAB-IT Expert" width="96" height="89"></a>' +
        '<nav class="next-nav" aria-label="Navigare principală">' +
          '<a href="/">Acasă</a>' +
          '<div class="next-nav-dropdown"><button type="button" aria-expanded="false">Servicii <span class="nav-chevron" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m4 6 4 4 4-4"/></svg></span></button><div class="next-nav-menu">' +
            '<a href="/servicii/creare-site-web/"><span class="nav-menu-icon is-web" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg></span><strong>Creare website</strong><small>Site-uri rapide și magazine online</small></a>' +
            '<a href="/servicii/seo/"><span class="nav-menu-icon is-seo" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></span><strong>Optimizare SEO</strong><small>Vizibilitate organică relevantă</small></a>' +
            '<a href="/servicii/reclame-platite/"><span class="nav-menu-icon is-ads" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 13V9l12-5v14L4 13Z"/><path d="m7 14 1.5 6h3L10 15M19 8v6"/></svg></span><strong>Google &amp; Social Ads</strong><small>Campanii orientate spre conversii</small></a>' +
            '<a href="/servicii/integrari-digitale/"><span class="nav-menu-icon is-ai" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="6" width="14" height="12" rx="3"/><path d="M9 2v4m6-4v4M2 10h3m14 0h3M9 12h.01M15 12h.01M9 15h6"/></svg></span><strong>Automatizări AI</strong><small>Procese mai simple și mai rapide</small></a>' +
          '</div></div>' +
          '<a href="/portofoliu/">Proiecte</a><a href="/despre-noi/">Despre noi</a>' +
          '<div class="next-nav-dropdown"><button type="button" aria-expanded="false">Resurse <span class="nav-chevron" aria-hidden="true"><svg viewBox="0 0 16 16"><path d="m4 6 4 4 4-4"/></svg></span></button><div class="next-nav-menu is-compact">' +
            '<a href="/blog/"><span class="nav-menu-icon is-blog" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 12h7M9 16h7"/></svg></span><strong>Blog</strong><small>Ghiduri aplicate</small></a><a href="/glosar-seo/"><span class="nav-menu-icon is-guide" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H12v18H7.5A3.5 3.5 0 0 0 4 23Z"/><path d="M20 5.5A3.5 3.5 0 0 0 16.5 2H12v18h4.5A3.5 3.5 0 0 1 20 23Z"/></svg></span><strong>Glosar SEO</strong><small>Termeni explicați simplu</small></a><a href="/preturi/"><span class="nav-menu-icon is-price" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m3 12 9-9h7l2 2v7l-9 9Z"/><circle cx="16.5" cy="7.5" r="1.5"/></svg></span><strong>Prețuri</strong><small>Pachete și puncte de pornire</small></a>' +
          '</div></div><a href="/contact/">Contact</a>' +
        '</nav>' +
        '<div class="next-nav-actions"><a class="button button-ghost" href="/portofoliu/">Vezi proiectele</a><a class="button button-primary" href="/#audit">Cere un audit gratuit <span aria-hidden="true">→</span></a></div>' +
        '<button class="next-menu-toggle" type="button" aria-label="Deschide meniul" aria-expanded="false" aria-controls="mobile-menu"><span></span><span></span><span></span></button>' +
      '</div>' +
      '<div class="next-mobile-menu" id="mobile-menu" hidden><a href="/">Acasă</a><a href="/servicii/">Servicii</a><a href="/portofoliu/">Proiecte</a><a href="/despre-noi/">Despre noi</a><a href="/blog/">Blog</a><a href="/glosar-seo/">Glosar SEO</a><a href="/preturi/">Prețuri</a><a href="/contact/">Contact</a><a class="button button-primary" href="/#audit">Cere un audit gratuit <span aria-hidden="true">→</span></a></div>' +
    '</header>';
  }

  function footerMarkup() {
    return '<footer class="next-footer" id="newsletter">' +
      '<div class="section-shell next-footer-main">' +
        '<div class="footer-brand"><img src="/img/logo_home.png" alt="CAB-IT Expert SRL" width="560" height="195"><p>Construim soluții digitale pe care oamenii le înțeleg și afacerile le pot măsura.</p><strong>Future is Online.</strong><img class="footer-partner-badge" src="/assets/img/partners/google-partner.png" alt="Google Partner" width="550" height="550" loading="lazy" decoding="async"></div>' +
        '<div><h2>Servicii</h2><a href="/servicii/creare-site-web/">Creare website</a><a href="/servicii/seo/">Optimizare SEO</a><a href="/servicii/reclame-platite/">Google &amp; Social Ads</a><a href="/servicii/integrari-digitale/">Automatizări AI</a></div>' +
        '<div><h2>Companie</h2><a href="/despre-noi/">Despre noi</a><a href="/portofoliu/">Proiecte</a><a href="/blog/">Blog</a><a href="/contact/">Contact</a><a href="/termeni-si-conditii/">Termeni și condiții</a></div>' +
        '<div class="footer-newsletter"><h2>Resurse utile, fără spam</h2><p>Primește ghiduri practice despre SEO, promovare online și website-uri.</p><form action="/newsletter-subscribe.php" method="post"><label class="sr-only" for="next-footer-email">Adresa ta de email</label><div><input id="next-footer-email" type="email" name="email" autocomplete="email" placeholder="nume@companie.ro" required><button type="submit" aria-label="Abonează-te"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M14 7l5 5-5 5"/></svg></button></div><input type="hidden" name="source" value="footer-global"><input class="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"></form></div>' +
      '</div>' +
      '<div class="section-shell footer-bottom"><span>© <span data-current-year>' + new Date().getFullYear() + '</span> CAB IT EXPERT SRL. Toate drepturile rezervate.</span><div class="footer-socials"><a href="https://www.facebook.com/profile.php?id=61592087996523" rel="noopener" target="_blank" aria-label="CAB-IT pe Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v8h4v-8h3.5l.5-4h-4V9c0-.7.3-1 1-1Z"/></svg></a><a href="https://www.instagram.com/cabitexpert/" rel="noopener" target="_blank" aria-label="CAB-IT pe Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a><a href="https://www.linkedin.com/company/cab-it-expert/" rel="noopener" target="_blank" aria-label="CAB-IT pe LinkedIn"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v10M5 5.5v.1M10 19V9h4v1.5c1-1.5 5-2 5 2.5v6M10 13c0-2.2 1.3-4 4-4"/></svg></a><a href="https://www.youtube.com/@cabitexpert" rel="noopener" target="_blank" aria-label="CAB-IT pe YouTube"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="4"/><path d="m10 9 5 3-5 3Z"/></svg></a><a href="https://www.tiktok.com/@cab.it.expert" rel="noopener" target="_blank" aria-label="CAB-IT pe TikTok"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3v11.2a5.2 5.2 0 1 1-4-5.1"/><path d="M15 3c.6 3.1 2.3 4.8 5 5"/></svg></a><a href="https://x.com/cabitexpert" rel="noopener" target="_blank" aria-label="CAB-IT pe X"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4 19 20M19 4 5 20"/></svg></a></div></div>' +
      '<div class="footer-cta"><span>Ai o idee? Hai să o transformăm într-un proiect care produce rezultate.</span><a href="/contact/">Hai să discutăm <b>→</b></a></div>' +
    '</footer>';
  }

  function ensureShell() {
    if (body.classList.contains("cabit-home")) return;
    body.classList.add("cabit-next-shell");
    if (!doc.querySelector(".next-header")) body.insertAdjacentHTML("afterbegin", headerMarkup());
    if (!doc.querySelector(".next-footer")) body.insertAdjacentHTML("beforeend", footerMarkup());
    if (!doc.querySelector(".mobile-contact")) body.insertAdjacentHTML("beforeend", '<a class="mobile-contact" href="https://wa.me/40771532949?text=Bun%C4%83%2C%20a%C8%99%20dori%20mai%20multe%20detalii" target="_blank" rel="noopener" aria-label="Hai să discutăm pe WhatsApp"><span class="mobile-contact__icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path fill="currentColor" d="M16.1 4.2A11.5 11.5 0 0 0 6.2 21.6L4.7 27l5.5-1.4a11.5 11.5 0 1 0 5.9-21.4Zm0 20.7c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.2.8.9-3.1-.2-.4a9.2 9.2 0 1 1 7.9 4.3Zm5.1-6.8c-.3-.1-1.7-.8-1.9-.9-.3-.1-.5-.1-.7.2l-.9 1.1c-.2.2-.4.2-.7.1a7.5 7.5 0 0 1-2.2-1.4 8.4 8.4 0 0 1-1.5-1.9c-.2-.3 0-.5.1-.6l.5-.6.3-.5c.1-.2 0-.4 0-.6l-.9-2.1c-.2-.5-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.5c.2.2 2.4 3.7 5.9 5.1.8.3 1.5.5 2 .6.8.3 1.6.2 2.2.1.7-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3Z"/></svg></span><span class="mobile-contact__tooltip" role="tooltip"><span class="mobile-contact__dots" aria-hidden="true"><i></i><i></i><i></i></span><span>Hai să discutăm</span></span></a>');
  }

  ensureShell();
  localizeRootPaths(doc);

  function mountCabiButton() {
    if (doc.querySelector("[data-cabi-smart-button]")) return;

    var icon = '<svg class="cabi-smart-button__icon" viewBox="0 0 64 64" aria-hidden="true">' +
      '<path class="cabi-smart-button__bubble" d="M12 13.5h40a6.5 6.5 0 0 1 6.5 6.5v22a6.5 6.5 0 0 1-6.5 6.5H30.5L18 56l2.8-7.5H12A6.5 6.5 0 0 1 5.5 42V20a6.5 6.5 0 0 1 6.5-6.5Z"/>' +
      '<rect class="cabi-smart-button__bot" x="17.5" y="24" width="29" height="18.5" rx="7.5"/>' +
      '<path class="cabi-smart-button__antenna" d="M32 24v-5"/>' +
      '<circle class="cabi-smart-button__antenna-node" cx="32" cy="17" r="2.8"/>' +
      '<circle class="cabi-smart-button__eye" cx="26" cy="32.5" r="2.4"/>' +
      '<circle class="cabi-smart-button__eye" cx="38" cy="32.5" r="2.4"/>' +
      '<path class="cabi-smart-button__smile" d="M27 37.5c2.8 2.1 7.2 2.1 10 0"/>' +
    '</svg>';
    var tooltip = '<span class="mobile-contact__tooltip" role="tooltip"><span class="mobile-contact__dots" aria-hidden="true"><i></i><i></i><i></i></span><span>Hai să discutăm</span></span>';
    var common = 'type="button" aria-label="Deschide asistentul inteligent" aria-controls="cabi-ai-panel" aria-expanded="false" data-cabi-smart-button';

    body.insertAdjacentHTML("beforeend", '<button class="cabi-smart-button cabi-smart-button--desktop" ' + common + '>' + icon + tooltip + '</button>');

    var toggle = doc.querySelector(".next-menu-toggle");
    if (toggle) {
      toggle.insertAdjacentHTML("beforebegin", '<button class="cabi-smart-button cabi-smart-button--mobile" ' + common + '>' + icon + '<span class="cabi-smart-button__status" aria-hidden="true"></span></button>');
    }

    var recentItems = [
      "Am un salon și vreau un site",
      "Cât costă un magazin online?",
      "SEO local în București",
      "Vreau reclame Google Ads",
      "Automatizări AI pentru firme"
    ].map(function (item, index) {
      return '<button type="button" aria-disabled="true"' + (index === 0 ? ' class="is-current"' : '') + '><span aria-hidden="true">' + (index + 1) + '</span>' + item + '</button>';
    }).join("");
    var navItems = [
      ["Servicii", "/servicii/", "M4 7h16M4 12h16M4 17h10"],
      ["Prețuri", "/preturi/", "M12 3v18M8 7h6.5a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6H16"],
      ["Portofoliu", "/portofoliu/", "M4 7h16v12H4zM8 7V5h8v2"],
      ["Blog", "/blog/", "M5 4h14v16H5zM9 8h6M9 12h6M9 16h4"],
      ["Contact", "/contact/", "M4 6h16v12H4zM4 7l8 6 8-6"]
    ].map(function (item) {
      return '<a href="' + item[1] + '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="' + item[2] + '"/></svg><span>' + item[0] + '</span></a>';
    }).join("");
    var panelMarkup = '<div class="cabi-ai-panel" id="cabi-ai-panel" data-cabi-panel aria-hidden="true" hidden>' +
      '<div class="cabi-ai-panel__surface" role="dialog" aria-modal="true" aria-labelledby="cabi-ai-title" tabindex="-1">' +
        '<aside class="cabi-ai-sidebar">' +
          '<div class="cabi-ai-sidebar__brand"><span>' + icon + '</span><div><strong>Asistent CAB-IT</strong><small><i></i> Design demonstrativ</small></div></div>' +
          '<button class="cabi-ai-new" type="button" aria-disabled="true"><span aria-hidden="true">＋</span> Conversație nouă <kbd>Ctrl K</kbd></button>' +
          '<p class="cabi-ai-kicker">CONVERSAȚII RECENTE</p><div class="cabi-ai-recents">' + recentItems + '</div>' +
          '<nav class="cabi-ai-nav" aria-label="Scurtături CAB-IT">' + navItems + '</nav>' +
          '<div class="cabi-ai-sidebar__foot"><span>' + icon + '</span><div><strong>Asistent CAB-IT</strong><small><i></i> Pregătit pentru etapa AI</small></div></div>' +
        '</aside>' +
        '<section class="cabi-ai-main">' +
          '<header class="cabi-ai-topbar"><div class="cabi-ai-topbar__title"><span class="cabi-ai-topbar__mobile-icon">' + icon + '</span><div><h2 id="cabi-ai-title">Asistent AI CAB-IT <em>PREVIEW</em></h2><p>Ghid inteligent pentru servicii web și marketing digital</p></div></div><button class="cabi-ai-close" type="button" data-cabi-close aria-label="Închide asistentul"><span></span><span></span></button></header>' +
          '<div class="cabi-ai-canvas">' +
            '<div class="cabi-ai-welcome"><div class="cabi-ai-orbit" aria-hidden="true"><i></i><i></i><span>' + icon + '</span></div><p class="cabi-ai-eyebrow">ORICE ÎNTREBARE. UN SINGUR PUNCT DE PORNIRE.</p><h3>Cum te pot ajuta astăzi?</h3><p>Pe viitor vei putea întreba despre servicii, prețuri, proiecte sau orice informație din website.</p></div>' +
            '<div class="cabi-ai-demo" aria-label="Exemplu vizual de conversație"><div class="cabi-ai-message cabi-ai-message--user"><p>Am un salon și vreau un website care să aducă programări.</p><small>10:42 <b>✓✓</b></small></div><div class="cabi-ai-message-row"><span class="cabi-ai-avatar">' + icon + '</span><div class="cabi-ai-message cabi-ai-message--assistant"><p>Îți voi putea recomanda serviciul potrivit, exemple relevante și pașii următori direct din informațiile CAB-IT.</p><div><small>10:42</small><span aria-hidden="true">⧉　♡</span></div></div></div></div>' +
            '<div class="cabi-ai-next"><div class="cabi-ai-section-title"><div><small>RUTE INTELIGENTE</small><h3>Unde vrei să ajungi?</h3></div><span>Design interactiv</span></div><div class="cabi-ai-actions">' +
              '<a href="/servicii/creare-site-web/"><span>◎</span><div><strong>Website nou</strong><small>Servicii și beneficii</small></div><b>↗</b></a>' +
              '<a href="/preturi/"><span>◇</span><div><strong>Prețuri</strong><small>Pachete și repere</small></div><b>↗</b></a>' +
              '<a href="/portofoliu/"><span>▧</span><div><strong>Proiecte</strong><small>Rezultate reale</small></div><b>↗</b></a>' +
              '<a href="https://wa.me/40771532949" target="_blank" rel="noopener"><span>◉</span><div><strong>WhatsApp</strong><small>Discuție rapidă</small></div><b>↗</b></a>' +
            '</div></div>' +
          '</div>' +
          '<footer class="cabi-ai-composer"><div class="cabi-ai-composer__box"><textarea rows="1" readonly aria-label="Mesaj demonstrativ" placeholder="Scrie întrebarea ta..."></textarea><div><span class="cabi-ai-composer__tools" aria-hidden="true">＋　⌁</span><button type="button" aria-disabled="true" aria-label="Trimiterea va fi disponibilă în curând">↑</button></div></div><p>Momentan lucrăm doar la design. Funcția AI va fi conectată în etapa următoare.</p></footer>' +
        '</section>' +
      '</div>' +
    '</div>';
    body.insertAdjacentHTML("beforeend", panelMarkup);
    var panel = doc.querySelector("[data-cabi-panel]");
    localizeRootPaths(panel);

    if (!doc.getElementById("cabi-smart-button-styles")) {
      var styles = doc.createElement("style");
      styles.id = "cabi-smart-button-styles";
      styles.textContent =
        '.cabi-smart-button{appearance:none;-webkit-appearance:none;display:grid;place-items:center;border:1px solid rgba(255,255,255,.72);color:#fff;background:linear-gradient(145deg,#063c43 0%,#007f79 50%,#06b8ab 100%);box-shadow:0 16px 34px rgba(0,93,88,.28),inset 0 1px 0 rgba(255,255,255,.25);cursor:pointer;isolation:isolate;transition:transform .22s ease,box-shadow .22s ease,filter .22s ease}' +
        '.cabi-smart-button::before{position:absolute;z-index:-1;content:"";inset:3px;border:1px solid rgba(255,255,255,.18);border-radius:inherit}' +
        '.cabi-smart-button:hover,.cabi-smart-button:focus-visible{outline:0;filter:saturate(1.08);transform:translateY(-2px);box-shadow:0 20px 40px rgba(0,93,88,.34),0 0 0 4px rgba(7,177,164,.13),inset 0 1px 0 rgba(255,255,255,.25)}' +
        '.cabi-smart-button__icon{width:38px;height:38px;overflow:visible}' +
        '.cabi-smart-button__bubble,.cabi-smart-button__bot,.cabi-smart-button__antenna,.cabi-smart-button__smile{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round}' +
        '.cabi-smart-button__bubble{stroke-width:3.6}' +
        '.cabi-smart-button__bot{fill:rgba(186,255,246,.13);stroke:#bafff6;stroke-width:2.2}' +
        '.cabi-smart-button__antenna,.cabi-smart-button__smile{stroke:#bafff6;stroke-width:2.2}' +
        '.cabi-smart-button__antenna-node,.cabi-smart-button__eye{fill:#fff;stroke:#a8fff3;stroke-width:1.2}' +
        '.cabi-smart-button--desktop{position:fixed;z-index:901;right:18px;bottom:max(88px,calc(env(safe-area-inset-bottom) + 88px));width:58px;height:58px;padding:0;border-radius:19px}' +
        '.cabi-smart-button--desktop .mobile-contact__tooltip{animation:none}' +
        '.cabi-smart-button--desktop:hover .mobile-contact__tooltip,.cabi-smart-button--desktop:focus-visible .mobile-contact__tooltip{opacity:1;visibility:visible;transform:translate(0,-50%)}' +
        '.cabi-smart-button--mobile{position:relative;width:45px;height:45px;display:none;flex:0 0 45px;padding:0;border-radius:13px}' +
        '.cabi-smart-button--mobile .cabi-smart-button__icon{width:30px;height:30px}' +
        '.cabi-smart-button__status{position:absolute;right:4px;top:4px;width:7px;height:7px;border:2px solid #fff;border-radius:50%;background:#8df5e8;box-shadow:0 0 0 2px rgba(141,245,232,.16)}' +
        'body.cabi-panel-open{overflow:hidden}' +
        '.cabi-ai-panel[hidden]{display:none!important}' +
        '.cabi-ai-panel{--cabi-origin-x:calc(100vw - 47px);--cabi-origin-y:calc(100vh - 117px);position:fixed;z-index:4000;inset:0;contain:layout paint style;color:#102033;background:#f7fbfb;clip-path:circle(29px at var(--cabi-origin-x) var(--cabi-origin-y));opacity:.84;visibility:hidden;pointer-events:none;will-change:clip-path,opacity;transition:clip-path .72s cubic-bezier(.65,0,.35,1),opacity .18s linear,visibility 0s linear .72s}' +
        '.cabi-ai-panel::before{position:absolute;content:"";inset:0;background:radial-gradient(circle at 76% 10%,rgba(24,202,187,.13),transparent 26%),radial-gradient(circle at 42% 92%,rgba(0,109,103,.08),transparent 32%),linear-gradient(rgba(0,110,104,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,110,104,.025) 1px,transparent 1px);background-size:auto,auto,32px 32px,32px 32px;pointer-events:none}' +
        '.cabi-ai-panel::after{position:absolute;z-index:8;content:"";left:var(--cabi-origin-x);top:var(--cabi-origin-y);width:58px;height:58px;border:2px solid rgba(139,255,242,.95);border-radius:50%;box-shadow:0 0 28px rgba(8,196,182,.58);opacity:0;pointer-events:none;will-change:transform,opacity;transform:translate(-50%,-50%) scale(.7)}' +
        '.cabi-ai-panel.is-opening::after{animation:cabi-portal-open .82s cubic-bezier(.16,.78,.2,1)}.cabi-ai-panel.is-closing::after{animation:cabi-portal-close .62s ease-out}' +
        '.cabi-ai-panel.is-open{clip-path:circle(155vmax at var(--cabi-origin-x) var(--cabi-origin-y));opacity:1;visibility:visible;pointer-events:auto;transition-delay:0s}' +
        '.cabi-ai-panel__surface{position:relative;width:100%;height:100%;display:grid;grid-template-columns:294px minmax(0,1fr);opacity:0;transform:translate(8px,8px) scale(.99);transform-origin:var(--cabi-origin-x) var(--cabi-origin-y);will-change:transform,opacity;transition:opacity .18s ease,transform .28s ease}' +
        '.cabi-ai-panel.is-open .cabi-ai-panel__surface{opacity:1;transform:none}' +
        '.cabi-ai-panel.is-closing .cabi-ai-panel__surface{opacity:0;transform:translate(18px,18px) scale(.975);transition-delay:0s;transition-duration:.24s}' +
        '.cabi-ai-sidebar{position:relative;z-index:2;display:flex;flex-direction:column;min-height:0;padding:22px 18px;border-right:1px solid rgba(202,220,225,.9);background:#fff;box-shadow:16px 0 60px rgba(15,48,57,.05)}' +
        '.cabi-ai-sidebar__brand,.cabi-ai-sidebar__foot{display:flex;align-items:center;gap:11px}' +
        '.cabi-ai-sidebar__brand>span,.cabi-ai-sidebar__foot>span,.cabi-ai-topbar__mobile-icon,.cabi-ai-avatar{display:grid;place-items:center;flex:0 0 auto;color:#fff;background:linear-gradient(145deg,#063c43,#08a99e);box-shadow:0 9px 24px rgba(0,111,104,.2)}' +
        '.cabi-ai-sidebar__brand>span{width:44px;height:44px;border-radius:14px}.cabi-ai-sidebar__brand svg{width:30px;height:30px}' +
        '.cabi-ai-sidebar__brand strong,.cabi-ai-sidebar__foot strong{display:block;font-size:13px}.cabi-ai-sidebar__brand small,.cabi-ai-sidebar__foot small{display:flex;align-items:center;gap:6px;margin-top:3px;color:#68788d;font-size:10px}.cabi-ai-sidebar__brand small i,.cabi-ai-sidebar__foot small i{width:7px;height:7px;border-radius:50%;background:#19c981;box-shadow:0 0 0 3px rgba(25,201,129,.12)}' +
        '.cabi-ai-new{width:100%;min-height:49px;display:flex;align-items:center;gap:10px;margin-top:22px;padding:9px 11px;border:0;border-radius:14px;color:#fff;background:linear-gradient(135deg,#006d67,#09aa9f);box-shadow:0 12px 24px rgba(0,112,105,.18);font:700 13px/1 Inter,Arial,sans-serif;text-align:left;cursor:default}.cabi-ai-new>span{font-size:21px;font-weight:300}.cabi-ai-new kbd{margin-left:auto;padding:5px 7px;border:1px solid rgba(255,255,255,.28);border-radius:7px;background:rgba(255,255,255,.14);font:600 10px Inter,Arial,sans-serif}' +
        '.cabi-ai-kicker{margin:24px 8px 10px;color:#7a899a;font-size:10px;font-weight:800;letter-spacing:.12em}' +
        '.cabi-ai-recents{display:grid;gap:3px}.cabi-ai-recents button{min-height:38px;display:flex;align-items:center;gap:9px;padding:7px 9px;border:0;border-radius:10px;color:#263649;background:transparent;font:600 11px/1.3 Inter,Arial,sans-serif;text-align:left;cursor:default}.cabi-ai-recents button.is-current{color:#005f5a;background:#e5f8f5}.cabi-ai-recents button span{width:21px;height:21px;display:grid;place-items:center;flex:0 0 21px;border:1px solid #cddce1;border-radius:7px;color:#00877f;font-size:9px;background:#fff}' +
        '.cabi-ai-nav{display:grid;gap:2px;margin-top:20px;padding-top:16px;border-top:1px solid #e1eaed}.cabi-ai-nav a{display:flex;align-items:center;gap:10px;padding:8px 9px;border-radius:9px;color:#3e4e61;font-size:11px;font-weight:650;transition:color .18s ease,background .18s ease}.cabi-ai-nav a:hover{color:#006d67;background:#edf9f7}.cabi-ai-nav svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}' +
        '.cabi-ai-sidebar__foot{margin-top:auto;padding:16px 7px 2px;border-top:1px solid #e1eaed}.cabi-ai-sidebar__foot>span{width:36px;height:36px;border-radius:12px}.cabi-ai-sidebar__foot svg{width:25px;height:25px}' +
        '.cabi-ai-main{position:relative;z-index:1;min-width:0;height:100%;display:grid;grid-template-rows:auto minmax(0,1fr) auto;overflow:hidden}' +
        '.cabi-ai-topbar{min-height:84px;display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px clamp(22px,4vw,58px);border-bottom:1px solid rgba(215,227,231,.82);background:rgba(255,255,255,.94)}' +
        '.cabi-ai-topbar__title{display:flex;align-items:center;gap:12px}.cabi-ai-topbar__title h2{margin:0;color:#102033;font:750 16px/1.2 Inter,Arial,sans-serif}.cabi-ai-topbar__title h2 em{display:inline-flex;margin-left:7px;padding:4px 6px;border-radius:6px;color:#00766f;background:#dcf8f4;font-size:8px;font-style:normal;letter-spacing:.08em;vertical-align:2px}.cabi-ai-topbar__title p{margin:5px 0 0;color:#6b7a8f;font-size:11px}.cabi-ai-topbar__mobile-icon{display:none;width:40px;height:40px;border-radius:13px}.cabi-ai-topbar__mobile-icon svg{width:28px;height:28px}' +
        '.cabi-ai-close{position:relative;width:46px;height:46px;display:grid;place-items:center;flex:0 0 46px;border:1px solid #d4e1e5;border-radius:15px;background:rgba(255,255,255,.88);box-shadow:0 8px 24px rgba(17,45,55,.07);cursor:pointer;transition:transform .2s ease,background .2s ease}.cabi-ai-close:hover{background:#e9f9f7;transform:rotate(3deg)}.cabi-ai-close span{position:absolute;width:18px;height:2px;border-radius:2px;background:#183047;transform:rotate(45deg)}.cabi-ai-close span+span{transform:rotate(-45deg)}' +
        '.cabi-ai-canvas{min-height:0;overflow:auto;overscroll-behavior:contain;scrollbar-width:none;padding:clamp(24px,3.5vh,46px) clamp(22px,5vw,78px) 28px}.cabi-ai-canvas::-webkit-scrollbar{display:none}' +
        '.cabi-ai-welcome{max-width:760px;margin:0 auto;text-align:center}.cabi-ai-orbit{position:relative;width:88px;height:88px;display:grid;place-items:center;margin:0 auto 13px}.cabi-ai-orbit::before,.cabi-ai-orbit::after{position:absolute;content:"";inset:5px;border:1px solid rgba(0,145,136,.22);border-radius:50%}.cabi-ai-orbit::after{inset:14px;border-style:dashed;animation:cabi-orbit-spin 10s linear infinite}.cabi-ai-orbit>span{width:54px;height:54px;display:grid;place-items:center;border-radius:18px;color:#fff;background:linear-gradient(145deg,#063c43,#08a99e);box-shadow:0 14px 34px rgba(0,111,104,.25)}.cabi-ai-orbit svg{width:38px;height:38px}.cabi-ai-orbit i{position:absolute;width:9px;height:9px;border:2px solid #fff;border-radius:50%;background:#15cbbd;box-shadow:0 0 0 4px rgba(21,203,189,.14)}.cabi-ai-orbit i:first-child{top:8px;right:10px}.cabi-ai-orbit i:nth-child(2){bottom:9px;left:8px;background:#004d49}' +
        '.cabi-ai-eyebrow{margin:0 0 8px!important;color:#008078!important;font-size:9px!important;font-weight:850!important;letter-spacing:.16em}.cabi-ai-welcome h3{margin:0;color:#102033;font:800 clamp(25px,3vw,39px)/1.08 Inter,Arial,sans-serif;letter-spacing:-.035em}.cabi-ai-welcome>p:last-child{max-width:650px;margin:11px auto 0;color:#65758a;font-size:13px;line-height:1.6}' +
        '.cabi-ai-demo{max-width:920px;display:grid;gap:18px;margin:28px auto 0}.cabi-ai-message{border:1px solid #d8e4e8;border-radius:20px;padding:16px 18px;background:rgba(255,255,255,.92);box-shadow:0 12px 34px rgba(22,58,67,.055)}.cabi-ai-message p{margin:0;color:#243448;font-size:13px;line-height:1.55}.cabi-ai-message small{color:#77869a;font-size:10px}.cabi-ai-message--user{max-width:430px;justify-self:end;border-color:#c9ebe6;background:linear-gradient(145deg,#f4fffd,#e6f8f5)}.cabi-ai-message--user small{display:block;margin-top:8px;text-align:right}.cabi-ai-message--user b{color:#049d92}.cabi-ai-message-row{display:flex;align-items:flex-end;gap:11px}.cabi-ai-avatar{width:39px;height:39px;border-radius:13px}.cabi-ai-avatar svg{width:27px;height:27px}.cabi-ai-message--assistant{max-width:650px;text-align:left}.cabi-ai-message--assistant>div{display:flex;align-items:center;justify-content:space-between;margin-top:10px;color:#77869a;font-size:12px}' +
        '.cabi-ai-next{max-width:1040px;margin:30px auto 0}.cabi-ai-section-title{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:12px}.cabi-ai-section-title small{color:#008078;font-size:9px;font-weight:850;letter-spacing:.14em}.cabi-ai-section-title h3{margin:4px 0 0;color:#14263a;font:750 17px Inter,Arial,sans-serif}.cabi-ai-section-title>span{padding:6px 9px;border-radius:999px;color:#637489;background:#edf3f5;font-size:9px;font-weight:750}.cabi-ai-actions{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.cabi-ai-actions a{min-width:0;display:flex;align-items:center;gap:10px;padding:13px;border:1px solid #d9e5e8;border-radius:16px;color:#25364a;background:rgba(255,255,255,.88);box-shadow:0 9px 24px rgba(20,55,65,.045);transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease}.cabi-ai-actions a:hover{border-color:#7bd6cd;box-shadow:0 13px 28px rgba(0,111,104,.1);transform:translateY(-3px)}.cabi-ai-actions a>span{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:11px;color:#007c75;background:#e2f8f5;font-size:18px}.cabi-ai-actions a div{min-width:0}.cabi-ai-actions strong,.cabi-ai-actions small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.cabi-ai-actions strong{font-size:11px}.cabi-ai-actions small{margin-top:4px;color:#7a899a;font-size:9px}.cabi-ai-actions b{margin-left:auto;color:#03988e;font-size:13px}' +
        '.cabi-ai-composer{padding:12px clamp(22px,5vw,78px) 14px;background:linear-gradient(180deg,rgba(247,251,251,0),#f7fbfb 24%)}.cabi-ai-composer__box{max-width:1040px;margin:0 auto;padding:11px 13px;border:1.5px solid #30afa5;border-radius:19px;background:rgba(255,255,255,.96);box-shadow:0 15px 35px rgba(0,104,98,.09)}.cabi-ai-composer textarea{width:100%;height:28px!important;min-height:28px!important;padding:3px 0!important;resize:none;border:0;outline:0;color:#344559;background:transparent;font:500 13px/1.5 Inter,Arial,sans-serif}.cabi-ai-composer textarea::placeholder{color:#8795a6}.cabi-ai-composer__box>div{display:flex;align-items:center;justify-content:space-between}.cabi-ai-composer__tools{color:#687a8d;font-size:17px}.cabi-ai-composer button{width:38px;height:38px;border:0;border-radius:13px;color:#fff;background:linear-gradient(145deg,#006d67,#08aaa0);font-size:20px;cursor:default}.cabi-ai-composer>p{margin:8px 0 0;color:#8390a0;font-size:9px;text-align:center}' +
        '@keyframes cabi-orbit-spin{to{transform:rotate(360deg)}}@keyframes cabi-portal-open{0%{opacity:1;transform:translate(-50%,-50%) scale(.7)}35%{opacity:.92;transform:translate(-50%,-50%) scale(1.7)}100%{opacity:0;transform:translate(-50%,-50%) scale(6)}}@keyframes cabi-portal-close{0%{opacity:0;transform:translate(-50%,-50%) scale(5)}68%{opacity:.72;transform:translate(-50%,-50%) scale(1.45)}100%{opacity:0;transform:translate(-50%,-50%) scale(.72)}}' +
        '@media(max-width:1020px){.next-nav-wrap{gap:0}.cabi-smart-button--desktop{display:none}.cabi-smart-button--mobile{display:grid;margin-left:auto;margin-right:8px}.cabi-smart-button--mobile+.next-menu-toggle{margin-left:0}}' +
        '@media(max-width:820px){.cabi-ai-panel{--cabi-origin-x:calc(100vw - 85px);--cabi-origin-y:43px;background:#f7fbfb}.cabi-ai-panel__surface{display:block}.cabi-ai-sidebar{display:none}.cabi-ai-main{height:100%;grid-template-rows:auto minmax(0,1fr) auto}.cabi-ai-topbar{min-height:72px;padding:11px 13px 11px 14px}.cabi-ai-topbar__mobile-icon{display:grid}.cabi-ai-topbar__title h2{font-size:14px}.cabi-ai-topbar__title p{max-width:225px;margin-top:3px;font-size:9px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.cabi-ai-close{width:44px;height:44px;flex-basis:44px;border-radius:14px}.cabi-ai-canvas{padding:21px 14px 22px}.cabi-ai-orbit{width:72px;height:72px;margin-bottom:10px}.cabi-ai-orbit>span{width:47px;height:47px;border-radius:15px}.cabi-ai-orbit svg{width:33px;height:33px}.cabi-ai-welcome h3{font-size:27px}.cabi-ai-welcome>p:last-child{font-size:12px}.cabi-ai-demo{gap:12px;margin-top:22px}.cabi-ai-message{padding:13px 14px;border-radius:17px}.cabi-ai-message p{font-size:12px}.cabi-ai-message--user{max-width:88%}.cabi-ai-message-row{gap:8px}.cabi-ai-avatar{width:34px;height:34px;border-radius:11px}.cabi-ai-avatar svg{width:23px;height:23px}.cabi-ai-section-title>span{display:none}.cabi-ai-actions{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.cabi-ai-actions a{padding:11px}.cabi-ai-composer{padding:9px 10px max(10px,env(safe-area-inset-bottom))}.cabi-ai-composer__box{padding:9px 10px;border-radius:17px}.cabi-ai-composer>p{font-size:8px}}' +
        '@media(max-width:420px){.cabi-ai-topbar__title h2 em{display:none}.cabi-ai-topbar__title p{max-width:190px}.cabi-ai-eyebrow{font-size:8px!important}.cabi-ai-message--assistant{max-width:calc(100% - 42px)}.cabi-ai-actions a>span{width:31px;height:31px;flex-basis:31px}.cabi-ai-actions strong{font-size:10px}}' +
        '@media(prefers-reduced-motion:reduce){.cabi-smart-button,.cabi-ai-panel,.cabi-ai-panel__surface{transition:none}.cabi-ai-orbit::after{animation:none}}' +
        '@media print{.cabi-smart-button{display:none!important}}';
      doc.head.appendChild(styles);
    }

    var launchers = Array.prototype.slice.call(doc.querySelectorAll("[data-cabi-smart-button]"));
    var closeButton = panel.querySelector("[data-cabi-close]");
    var lastLauncher = null;
    var panelTimer = null;
    var effectTimer = null;
    var bodyLockTimer = null;

    function setPanel(open, launcher) {
      window.clearTimeout(panelTimer);
      window.clearTimeout(effectTimer);
      window.clearTimeout(bodyLockTimer);
      if (open) {
        lastLauncher = launcher;
        var rect = launcher.getBoundingClientRect();
        panel.style.setProperty("--cabi-origin-x", (rect.left + rect.width / 2) + "px");
        panel.style.setProperty("--cabi-origin-y", (rect.top + rect.height / 2) + "px");
        panel.style.setProperty("--cabi-clip-top", Math.max(0, rect.top) + "px");
        panel.style.setProperty("--cabi-clip-right", Math.max(0, window.innerWidth - rect.right) + "px");
        panel.style.setProperty("--cabi-clip-bottom", Math.max(0, window.innerHeight - rect.bottom) + "px");
        panel.style.setProperty("--cabi-clip-left", Math.max(0, rect.left) + "px");
        panel.style.setProperty("--cabi-clip-radius", window.getComputedStyle(launcher).borderRadius || "18px");
        var openMenu = doc.querySelector('.next-menu-toggle[aria-expanded="true"]');
        if (openMenu) openMenu.click();
        panel.classList.remove("is-closing");
        panel.classList.add("is-opening");
        panel.hidden = false;
        panel.setAttribute("aria-hidden", "false");
        launchers.forEach(function (button) { button.setAttribute("aria-expanded", "true"); });
        panel.offsetWidth;
        window.requestAnimationFrame(function () {
          panel.classList.add("is-open");
          panelTimer = window.setTimeout(function () { closeButton.focus({ preventScroll: true }); }, 410);
          effectTimer = window.setTimeout(function () { panel.classList.remove("is-opening"); }, 850);
          bodyLockTimer = window.setTimeout(function () {
            if (panel.classList.contains("is-open")) body.classList.add("cabi-panel-open");
          }, 720);
        });
        return;
      }

      body.classList.remove("cabi-panel-open");
      panel.classList.remove("is-opening");
      panel.classList.add("is-closing");
      panel.classList.remove("is-open");
      panel.setAttribute("aria-hidden", "true");
      launchers.forEach(function (button) { button.setAttribute("aria-expanded", "false"); });
      panelTimer = window.setTimeout(function () {
        panel.hidden = true;
        panel.classList.remove("is-closing");
        if (lastLauncher) lastLauncher.focus({ preventScroll: true });
      }, 760);
    }

    launchers.forEach(function (launcher) {
      launcher.addEventListener("click", function () { setPanel(true, launcher); });
    });
    closeButton.addEventListener("click", function () { setPanel(false); });
    doc.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && panel.classList.contains("is-open")) setPanel(false);
    });
  }

  mountCabiButton();

  if ("serviceWorker" in navigator && /^https?:$/.test(window.location.protocol)) {
    window.addEventListener("load", function () {
      var workerUrl = new URL("service-worker.js", siteRootUrl).pathname;
      navigator.serviceWorker.register(workerUrl, { scope: siteRootPath }).catch(function () {
        // Site-ul rămâne complet funcțional dacă browserul nu acceptă modul offline.
      });
    }, { once: true });
  }

  var header = doc.querySelector("[data-site-header]");
  var menuToggle = header && header.querySelector(".next-menu-toggle");
  var mobileMenu = header && header.querySelector(".next-mobile-menu");
  var mobileMenuBackdrop = null;

  function updateHeader() {
    if (header) header.classList.toggle("is-scrolled", window.scrollY > 24);
  }
  updateHeader();
  window.addEventListener("scroll", updateHeader, { passive: true });

  if (menuToggle && mobileMenu) {
    mobileMenu.insertAdjacentHTML("afterbegin", '<div class="next-mobile-menu__head"><a class="next-mobile-menu__brand" href="/" aria-label="CAB-IT Expert — Acasă"><img src="/assets/img/brand/cab-it-header-symbol-clean.webp" alt="" width="192" height="192"><span><strong>Meniu</strong><small>CAB-IT Expert</small></span></a><button class="next-mobile-menu__close" type="button" aria-label="Închide meniul"><span></span><span></span></button></div>');
    localizeRootPaths(mobileMenu);
    mobileMenu.setAttribute("role", "dialog");
    mobileMenu.setAttribute("aria-modal", "true");
    mobileMenu.setAttribute("aria-label", "Meniu de navigare");
    body.appendChild(mobileMenu);
    body.insertAdjacentHTML("beforeend", '<button class="mobile-menu-backdrop" type="button" aria-label="Închide meniul" hidden></button>');
    mobileMenuBackdrop = doc.querySelector(".mobile-menu-backdrop");

    var closeTimer = null;
    function setMobileMenu(open, restoreFocus) {
      window.clearTimeout(closeTimer);
      menuToggle.setAttribute("aria-expanded", String(open));
      menuToggle.setAttribute("aria-label", open ? "Închide meniul" : "Deschide meniul");
      body.classList.toggle("menu-open", open);

      if (open) {
        mobileMenu.hidden = false;
        mobileMenuBackdrop.hidden = false;
        window.requestAnimationFrame(function () {
          mobileMenu.classList.add("is-open");
          mobileMenuBackdrop.classList.add("is-open");
          var closeButton = mobileMenu.querySelector(".next-mobile-menu__close");
          if (closeButton) closeButton.focus({ preventScroll: true });
        });
        return;
      }

      mobileMenu.classList.remove("is-open");
      mobileMenuBackdrop.classList.remove("is-open");
      closeTimer = window.setTimeout(function () {
        if (menuToggle.getAttribute("aria-expanded") === "true") return;
        mobileMenu.hidden = true;
        mobileMenuBackdrop.hidden = true;
        if (restoreFocus) menuToggle.focus({ preventScroll: true });
      }, 300);
    }

    menuToggle.addEventListener("click", function () {
      setMobileMenu(menuToggle.getAttribute("aria-expanded") !== "true", false);
    });
    mobileMenuBackdrop.addEventListener("click", function () { setMobileMenu(false, true); });
    mobileMenu.querySelector(".next-mobile-menu__close").addEventListener("click", function () { setMobileMenu(false, true); });
    mobileMenu.addEventListener("click", function (event) {
      if (!event.target.closest("a")) return;
      setMobileMenu(false, false);
    });
    doc.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && menuToggle.getAttribute("aria-expanded") === "true") setMobileMenu(false, true);
    });
    window.addEventListener("resize", function () {
      if (window.innerWidth > 1020 && menuToggle.getAttribute("aria-expanded") === "true") setMobileMenu(false, false);
    }, { passive: true });
  }

  function normalizedPath(value) {
    var pathname = new URL(value, window.location.href).pathname.replace(/index\.(html|php)$/i, "");
    if (siteRootPath !== "/" && pathname.indexOf(siteRootPath) === 0) {
      pathname = "/" + pathname.slice(siteRootPath.length).replace(/^\/+/, "");
    }
    pathname = pathname.replace(/\/{2,}/g, "/");
    if (pathname.length > 1 && pathname.slice(-1) !== "/") pathname += "/";
    return pathname;
  }

  var currentPath = normalizedPath(window.location.href);
  var serviceSection = currentPath.indexOf("/servicii/") === 0;
  var resourceSection = currentPath.indexOf("/blog/") === 0 || currentPath.indexOf("/glosar-seo/") === 0 || currentPath.indexOf("/preturi/") === 0 || currentPath.indexOf("/termeni-si-conditii/") === 0;

  function markActiveLink(link, active) {
    link.classList.toggle("is-active", active);
    if (active) link.setAttribute("aria-current", "page");
    else link.removeAttribute("aria-current");
  }

  doc.querySelectorAll(".next-nav a, .next-mobile-menu > a:not(.button)").forEach(function (link) {
    var linkPath = normalizedPath(link.href);
    var exact = currentPath === linkPath;
    var section = linkPath !== "/" && currentPath.indexOf(linkPath) === 0;
    markActiveLink(link, exact || section);
  });

  doc.querySelectorAll(".next-nav-dropdown").forEach(function (dropdown) {
    var button = dropdown.querySelector(":scope > button");
    var hasActiveChild = !!dropdown.querySelector(".next-nav-menu a.is-active");
    var label = button ? button.textContent.trim().toLowerCase() : "";
    var active = hasActiveChild || (label.indexOf("servicii") === 0 && serviceSection) || (label.indexOf("resurse") === 0 && resourceSection);
    if (button) {
      button.classList.toggle("is-active", active);
      if (active) button.setAttribute("aria-current", "page");
      else button.removeAttribute("aria-current");
    }
  });

  var portfolioFilters = doc.querySelector("[data-portfolio-filters]");
  var portfolioGrid = doc.querySelector("[data-portfolio-grid]");
  if (portfolioFilters && portfolioGrid) {
    portfolioFilters.addEventListener("click", function (event) {
      var button = event.target.closest("button[data-filter]");
      if (!button) return;
      var filter = button.getAttribute("data-filter") || "*";
      portfolioFilters.querySelectorAll("button").forEach(function (item) {
        item.classList.toggle("active", item === button);
      });
      portfolioGrid.querySelectorAll(".grid-item").forEach(function (card) {
        var visible = filter === "*" || card.matches(filter);
        card.hidden = !visible;
        card.setAttribute("aria-hidden", visible ? "false" : "true");
      });
    });
  }

  var reducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!reducedMotion && "animate" in Element.prototype) {
    doc.querySelectorAll(".cabit-service-faq details").forEach(function (details) {
      var summary = details.querySelector("summary");
      if (!summary) return;
      summary.addEventListener("click", function (event) {
        event.preventDefault();
        if (details.dataset.animating === "true") return;
        details.dataset.animating = "true";
        var startHeight = details.offsetHeight;
        var opening = !details.open;
        if (opening) details.open = true;
        details.classList.toggle("is-open", opening);
        details.classList.add("is-transitioning");
        var endHeight = opening ? details.scrollHeight : summary.offsetHeight;
        var animation = details.animate(
          [{ height: startHeight + "px" }, { height: endHeight + "px" }],
          { duration: opening ? 540 : 460, easing: "cubic-bezier(.16,.82,.25,1)" }
        );
        details.style.overflow = "hidden";
        animation.onfinish = function () {
          if (!opening) details.open = false;
          details.style.height = "";
          details.style.overflow = "";
          details.classList.remove("is-transitioning");
          delete details.dataset.animating;
        };
        animation.oncancel = animation.onfinish;
      });
    });
  }
  var revealItems = doc.querySelectorAll(".reveal");
  if (reducedMotion || !("IntersectionObserver" in window)) {
    revealItems.forEach(function (item) { item.classList.add("is-visible"); });
  } else {
    var revealObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.08, rootMargin: "0px 0px -30px" });
    revealItems.forEach(function (item) { revealObserver.observe(item); });
  }

  var processTimeline = doc.querySelector(".process-section");
  if (processTimeline) {
    var processGrid = processTimeline.querySelector(".process-grid");
    var processSteps = processGrid ? processGrid.querySelectorAll("li") : [];
    var processFrame = 0;

    function updateProcessTimeline() {
      processFrame = 0;
      if (!processGrid || !processSteps.length) return;
      if (reducedMotion) {
        processTimeline.style.setProperty("--timeline-progress", "1");
        processSteps.forEach(function (step) { step.classList.add("is-active"); });
        return;
      }

      var bounds = processGrid.getBoundingClientRect();
      var viewportHeight = window.innerHeight || doc.documentElement.clientHeight;
      var startLine = viewportHeight * .82;
      var endLine = viewportHeight * .28;
      var travel = Math.max(1, bounds.height + startLine - endLine);
      var progress = Math.max(0, Math.min(1, (startLine - bounds.top) / travel));
      processTimeline.style.setProperty("--timeline-progress", progress.toFixed(4));
      processSteps.forEach(function (step, index) {
        var threshold = (index + .28) / processSteps.length;
        step.classList.toggle("is-active", progress >= threshold);
      });
    }

    function requestProcessTimelineUpdate() {
      if (processFrame) return;
      processFrame = window.requestAnimationFrame(updateProcessTimeline);
    }

    updateProcessTimeline();
    window.addEventListener("scroll", requestProcessTimelineUpdate, { passive: true });
    window.addEventListener("resize", requestProcessTimelineUpdate, { passive: true });
  }

  var serviceTimeline = doc.querySelector(".cabit-service-timeline-section");
  if (serviceTimeline) {
    var serviceTimelineList = serviceTimeline.querySelector(".cabit-service-steps");
    var serviceTimelineSteps = serviceTimelineList ? serviceTimelineList.querySelectorAll(":scope > li") : [];
    var serviceTimelineFrame = 0;

    function updateServiceTimeline() {
      serviceTimelineFrame = 0;
      if (!serviceTimelineList || !serviceTimelineSteps.length) return;
      if (reducedMotion) {
        serviceTimeline.style.setProperty("--service-timeline-progress", "1");
        serviceTimelineSteps.forEach(function (step) { step.classList.add("is-active"); });
        return;
      }
      var bounds = serviceTimelineList.getBoundingClientRect();
      var viewportHeight = window.innerHeight || doc.documentElement.clientHeight;
      var horizontal = window.innerWidth > 820;
      var startLine = viewportHeight * .84;
      var endLine = viewportHeight * .24;
      var travel = Math.max(1, bounds.height + startLine - endLine);
      var progress = Math.max(0, Math.min(1, (startLine - bounds.top) / travel));
      serviceTimeline.style.setProperty("--service-timeline-progress", progress.toFixed(4));
      serviceTimelineSteps.forEach(function (step, index) {
        var threshold = horizontal ? (index + .15) / serviceTimelineSteps.length : (index + .3) / serviceTimelineSteps.length;
        step.classList.toggle("is-active", progress >= threshold);
      });
    }

    function requestServiceTimelineUpdate() {
      if (serviceTimelineFrame) return;
      serviceTimelineFrame = window.requestAnimationFrame(updateServiceTimeline);
    }

    updateServiceTimeline();
    window.addEventListener("scroll", requestServiceTimelineUpdate, { passive: true });
    window.addEventListener("resize", requestServiceTimelineUpdate, { passive: true });
  }

  var typed = doc.querySelector("[data-typed-search]");
  if (typed && !reducedMotion) {
    var phrases = ["servicii web design bucurești", "promovare online bucurești", "creare website profesional"];
    var phraseIndex = 0;
    var charIndex = phrases[0].length;
    var erasing = true;
    window.setTimeout(function typeLoop() {
      var phrase = phrases[phraseIndex];
      if (erasing) {
        charIndex--;
        typed.textContent = phrase.slice(0, Math.max(0, charIndex));
        if (charIndex <= 0) {
          erasing = false;
          phraseIndex = (phraseIndex + 1) % phrases.length;
          window.setTimeout(typeLoop, 420);
        } else window.setTimeout(typeLoop, 34);
      } else {
        phrase = phrases[phraseIndex];
        charIndex++;
        typed.textContent = phrase.slice(0, charIndex);
        if (charIndex >= phrase.length) {
          erasing = true;
          window.setTimeout(typeLoop, 2300);
        } else window.setTimeout(typeLoop, 58);
      }
    }, 2100);
  }

  function animateCount(element) {
    var target = Number(element.getAttribute("data-count"));
    if (!target || element.dataset.counted) return;
    element.dataset.counted = "true";
    if (reducedMotion) { element.textContent = target; return; }
    var start = performance.now();
    var duration = 1100;
    function tick(now) {
      var progress = Math.min(1, (now - start) / duration);
      var eased = 1 - Math.pow(1 - progress, 3);
      element.textContent = Math.round(target * eased);
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  var counters = doc.querySelectorAll("[data-count]");
  if ("IntersectionObserver" in window) {
    var countObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) { if (entry.isIntersecting) { animateCount(entry.target); observer.unobserve(entry.target); } });
    }, { threshold: .5 });
    counters.forEach(function (counter) { countObserver.observe(counter); });
  } else counters.forEach(animateCount);

  function initPagedCarousel(trackSelector, controlsSelector, cardSelector, autoplayDelay) {
    var track = doc.querySelector(trackSelector);
    var controls = doc.querySelector(controlsSelector);
    if (!track || !controls) return;
    var cards = Array.prototype.slice.call(track.querySelectorAll(cardSelector));
    var previous = controls.querySelector("[data-project-prev],[data-carousel-prev]");
    var next = controls.querySelector("[data-project-next],[data-carousel-next]");
    var dots = controls.querySelector("[data-project-dots],[data-carousel-dots]");
    if (!cards.length || !previous || !next || !dots) return;
    var currentPage = 0;
    var pages = 1;
    var pageStep = 0;
    var autoplay = null;
    var resizeTimer = null;

    function updateControls() {
      var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
      currentPage = pageStep ? Math.min(pages - 1, Math.round(track.scrollLeft / pageStep)) : 0;
      previous.disabled = track.scrollLeft <= 3;
      next.disabled = track.scrollLeft >= maxScroll - 3;
      Array.prototype.forEach.call(dots.children, function (dot, index) {
        dot.classList.toggle("is-active", index === currentPage);
      });
    }

    function metrics() {
      var trackStyle = window.getComputedStyle(track);
      var gap = parseFloat(trackStyle.columnGap || trackStyle.gap) || 0;
      var cardWidth = cards[0].getBoundingClientRect().width;
      var visibleCards = Math.max(1, Math.round((track.clientWidth + gap) / (cardWidth + gap)));
      pages = Math.max(1, Math.ceil(cards.length / visibleCards));
      pageStep = (cardWidth + gap) * visibleCards;
      currentPage = Math.min(pages - 1, Math.round(track.scrollLeft / Math.max(1, pageStep)));
      dots.innerHTML = "";
      for (var dotIndex = 0; dotIndex < pages; dotIndex++) {
        var dot = doc.createElement("i");
        dot.className = dotIndex === currentPage ? "is-active" : "";
        dots.appendChild(dot);
      }
      controls.hidden = pages <= 1;
      updateControls();
    }

    function goToPage(page) {
      currentPage = Math.max(0, Math.min(pages - 1, page));
      var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
      track.scrollTo({ left: Math.min(maxScroll, currentPage * pageStep), behavior: reducedMotion ? "auto" : "smooth" });
    }

    function stopAutoplay() {
      if (autoplay) window.clearInterval(autoplay);
      autoplay = null;
    }

    function startAutoplay() {
      stopAutoplay();
      if (reducedMotion || pages <= 1 || !autoplayDelay) return;
      autoplay = window.setInterval(function () {
        goToPage(currentPage >= pages - 1 ? 0 : currentPage + 1);
      }, autoplayDelay);
    }

    previous.addEventListener("click", function () { goToPage(currentPage - 1); startAutoplay(); });
    next.addEventListener("click", function () { goToPage(currentPage + 1); startAutoplay(); });
    track.addEventListener("scroll", updateControls, { passive: true });
    track.addEventListener("pointerenter", stopAutoplay);
    track.addEventListener("pointerleave", startAutoplay);
    track.addEventListener("focusin", stopAutoplay);
    track.addEventListener("focusout", startAutoplay);
    window.addEventListener("resize", function () {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(function () { metrics(); startAutoplay(); }, 140);
    });
    metrics();
    startAutoplay();
  }

  initPagedCarousel("[data-home-projects]", "[data-project-carousel-controls]", ".home-project-card", 0);
  initPagedCarousel("[data-home-articles]", "[data-article-carousel-controls]", ".cabit-blog-card", 0);
  initPagedCarousel("[data-testimonial-track]", "[data-testimonial-carousel-controls]", ".testimonial-card", 6200);

  var testimonialTrack = doc.querySelector("[data-testimonial-track]");
  if (testimonialTrack) {
    body.insertAdjacentHTML(
      "beforeend",
      '<div class="testimonial-modal" data-testimonial-modal hidden aria-hidden="true"><button class="testimonial-modal__backdrop" type="button" data-testimonial-close tabindex="-1" aria-label="Închide recenzia"></button><section class="testimonial-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="testimonial-modal-title"><button class="testimonial-modal__close" type="button" data-testimonial-close aria-label="Închide recenzia">×</button><div class="testimonial-modal__identity"><span class="testimonial-modal__initials" data-testimonial-initials></span><div><strong id="testimonial-modal-title" data-testimonial-name></strong><small data-testimonial-service></small></div></div><div class="testimonial-modal__proof"><span class="testimonial-modal__stars" aria-label="5 din 5 stele">★★★★★</span><span class="testimonial-modal__google">G · Recenzie Google</span></div><blockquote data-testimonial-copy></blockquote></section></div>'
    );
    var testimonialModal = doc.querySelector("[data-testimonial-modal]");
    var testimonialLastTrigger = null;
    var testimonialCloseTimer = 0;

    function closeTestimonialModal(restoreFocus) {
      if (!testimonialModal || testimonialModal.hidden) return;
      window.clearTimeout(testimonialCloseTimer);
      testimonialModal.classList.remove("is-open");
      testimonialModal.classList.add("is-closing");
      testimonialModal.setAttribute("aria-hidden", "true");
      body.classList.remove("testimonial-modal-open");
      testimonialCloseTimer = window.setTimeout(function () {
        testimonialModal.hidden = true;
        testimonialModal.classList.remove("is-closing");
        if (restoreFocus && testimonialLastTrigger) testimonialLastTrigger.focus({ preventScroll: true });
      }, reducedMotion ? 0 : 360);
    }

    function openTestimonialModal(card, trigger) {
      if (!testimonialModal || !card) return;
      window.clearTimeout(testimonialCloseTimer);
      testimonialLastTrigger = trigger;
      testimonialModal.querySelector("[data-testimonial-initials]").textContent = card.querySelector(".testimonial-card__top > span").textContent.trim();
      testimonialModal.querySelector("[data-testimonial-name]").textContent = card.querySelector(".testimonial-card__top strong").textContent.trim();
      testimonialModal.querySelector("[data-testimonial-service]").textContent = card.querySelector(".testimonial-card__top small").textContent.trim();
      testimonialModal.querySelector("[data-testimonial-copy]").textContent = card.querySelector(".testimonial-full-text").textContent.trim();
      testimonialModal.hidden = false;
      testimonialModal.classList.remove("is-closing");
      testimonialModal.setAttribute("aria-hidden", "false");
      body.classList.add("testimonial-modal-open");
      window.requestAnimationFrame(function () {
        testimonialModal.classList.add("is-open");
        var closeButton = testimonialModal.querySelector(".testimonial-modal__close");
        if (closeButton) closeButton.focus({ preventScroll: true });
      });
    }

    testimonialTrack.addEventListener("click", function (event) {
      var trigger = event.target.closest(".testimonial-more");
      if (!trigger) return;
      openTestimonialModal(trigger.closest(".testimonial-card"), trigger);
    });
    testimonialModal.addEventListener("click", function (event) {
      if (event.target.closest("[data-testimonial-close]")) closeTestimonialModal(true);
    });
    doc.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && testimonialModal && !testimonialModal.hidden) closeTestimonialModal(true);
    });
  }

  var auditLauncher = doc.querySelector("[data-audit-launcher]");
  var auditModal = doc.querySelector("[data-audit-modal]");
  if (auditLauncher && auditModal) {
    var urlInput = auditLauncher.querySelector("[name=website_url]");
    var continueButton = auditLauncher.querySelector("[data-audit-continue]");
    var auditStatus = doc.querySelector("[data-scanner-status]");
    var modalForm = auditModal.querySelector("[data-audit-modal-form]");
    var modalUrl = auditModal.querySelector("[data-audit-modal-url]");
    var modalDomain = auditModal.querySelector("[data-audit-domain]");
    var modalError = auditModal.querySelector("[data-audit-modal-error]");
    var formPanel = auditModal.querySelector("[data-audit-form-panel]");
    var successPanel = auditModal.querySelector("[data-audit-success]");
    var submitButton = auditModal.querySelector("[data-audit-submit]");
    var submitLabel = submitButton ? submitButton.querySelector("span") : null;
    var auditSending = false;
    var auditSucceeded = false;

    function normalizeUrl() {
      var value = urlInput.value.trim();
      if (value && !/^https?:\/\//i.test(value)) value = "https://" + value;
      urlInput.value = value;
      try {
        var parsed = new URL(value);
        var valid = parsed.hostname.indexOf(".") > 0;
        urlInput.setCustomValidity(valid ? "" : "Introdu o adresă de website validă.");
        return valid ? parsed : null;
      } catch (error) {
        urlInput.setCustomValidity("Introdu o adresă de website validă.");
        return null;
      }
    }

    function resetAuditModal() {
      auditSucceeded = false;
      auditSending = false;
      formPanel.hidden = false;
      formPanel.classList.remove("is-completing");
      successPanel.hidden = true;
      modalForm.reset();
      modalError.textContent = "";
      submitButton.disabled = false;
      submitButton.classList.remove("is-morphing");
      if (submitLabel) submitLabel.textContent = "Cere auditul 100% gratuit";
    }

    function openAuditModal() {
      var parsed = normalizeUrl();
      if (!parsed || !urlInput.reportValidity()) return;
      resetAuditModal();
      modalUrl.value = urlInput.value;
      modalDomain.textContent = parsed.hostname.replace(/^www\./i, "");
      auditModal.hidden = false;
      auditModal.setAttribute("aria-hidden", "false");
      body.classList.add("audit-modal-open");
      window.requestAnimationFrame(function () {
        var nameField = modalForm.querySelector("[name=name]");
        if (nameField) nameField.focus();
      });
      if (auditStatus) auditStatus.textContent = "Website acceptat. Completează datele în fereastra deschisă.";
    }

    function closeAuditModal() {
      if (auditSending) return;
      auditModal.hidden = true;
      auditModal.setAttribute("aria-hidden", "true");
      body.classList.remove("audit-modal-open");
      if (!auditSucceeded) window.setTimeout(resetAuditModal, 50);
      continueButton.focus();
    }

    continueButton.addEventListener("click", openAuditModal);
    urlInput.addEventListener("keydown", function (event) {
      if (event.key === "Enter") { event.preventDefault(); openAuditModal(); }
    });
    auditModal.querySelectorAll("[data-audit-modal-close]").forEach(function (button) { button.addEventListener("click", closeAuditModal); });
    doc.addEventListener("keydown", function (event) { if (event.key === "Escape" && !auditModal.hidden) closeAuditModal(); });

    modalForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      if (auditSending || !modalForm.checkValidity()) {
        modalForm.reportValidity();
        return;
      }
      auditSending = true;
      modalError.textContent = "";
      submitButton.disabled = true;
      if (submitLabel) submitLabel.textContent = "Trimitem solicitarea…";
      try {
        var response = await fetch(modalForm.action, {
          method: "POST",
          body: new FormData(modalForm),
          credentials: "same-origin",
          headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        });
        var payload = await response.json();
        if (!response.ok || !payload.ok) throw new Error(payload.message || "Solicitarea nu a putut fi trimisă.");
        auditSucceeded = true;
        submitButton.classList.add("is-morphing");
        formPanel.classList.add("is-completing");
        window.setTimeout(function () {
          formPanel.hidden = true;
          successPanel.hidden = false;
          auditSending = false;
          if (auditStatus) auditStatus.textContent = "Solicitare trimisă. Auditul ajunge pe email în maximum 30 de minute.";
        }, 650);
      } catch (error) {
        auditSending = false;
        submitButton.disabled = false;
        if (submitLabel) submitLabel.textContent = "Cere auditul 100% gratuit";
        modalError.textContent = error.message || "A apărut o eroare. Încearcă din nou.";
      }
    });
  }

  doc.querySelectorAll("[data-conversation-form]").forEach(function (conversationForm) {
    var choices = Array.prototype.slice.call(
      conversationForm.querySelectorAll('input[name="service"], input[name="objective"]')
    );
    var gatedFields = Array.prototype.slice.call(
      conversationForm.querySelectorAll(".conversation-fields input, .conversation-fields textarea, .conversation-fields select, button[type='submit']")
    );
    var fieldsWrap = conversationForm.querySelector(".conversation-fields");
    var lockNote = conversationForm.querySelector(".conversation-lock-note");

    if (!lockNote && fieldsWrap) {
      lockNote = doc.createElement("p");
      lockNote.className = "conversation-lock-note";
      lockNote.setAttribute("role", "status");
      lockNote.innerHTML = '<span aria-hidden="true">1</span>Alege mai întâi obiectivul proiectului pentru a activa formularul.';
      fieldsWrap.parentNode.insertBefore(lockNote, fieldsWrap);
    }

    function setConversationState(unlocked) {
      conversationForm.classList.toggle("has-choice", unlocked);
      conversationForm.classList.toggle("is-choice-locked", !unlocked);
      if (fieldsWrap) fieldsWrap.setAttribute("aria-disabled", unlocked ? "false" : "true");
      gatedFields.forEach(function (field) { field.disabled = !unlocked; });
      if (lockNote) {
        lockNote.classList.toggle("is-complete", unlocked);
        lockNote.innerHTML = unlocked
          ? '<span aria-hidden="true">✓</span>Obiectiv selectat. Acum poți completa datele și trimite mesajul.'
          : '<span aria-hidden="true">1</span>Alege mai întâi obiectivul proiectului pentru a activa formularul.';
      }
    }

    choices.forEach(function (choice) {
      choice.addEventListener("change", function () {
        setConversationState(true);
        var firstField = conversationForm.querySelector('.conversation-fields input[name="name"]');
        if (firstField && window.innerWidth > 760) firstField.focus();
      });
    });

    conversationForm.addEventListener("submit", function (event) {
      if (choices.length && !choices.some(function (choice) { return choice.checked; })) {
        event.preventDefault();
        setConversationState(false);
        conversationForm.classList.remove("choice-required");
        window.requestAnimationFrame(function () { conversationForm.classList.add("choice-required"); });
        choices[0].focus();
      }
    });

    setConversationState(!choices.length || choices.some(function (choice) { return choice.checked; }));
  });

  doc.querySelectorAll("[data-current-year]").forEach(function (year) { year.textContent = new Date().getFullYear(); });

  var params = new URLSearchParams(window.location.search);
  var noticeType = "";
  var noticeText = "";
  if (params.has("newsletter")) {
    noticeType = params.get("newsletter") === "invalid" ? "error" : "success";
    var newsletterMessages = { success: "Mulțumim! Te-ai abonat cu succes.", exists: "Această adresă este deja abonată.", invalid: "Verifică adresa de email și încearcă din nou." };
    noticeText = newsletterMessages[params.get("newsletter")] || newsletterMessages.invalid;
  } else if (params.has("audit")) {
    noticeType = params.get("audit") === "success" ? "success" : "error";
    noticeText = noticeType === "success" ? "Solicitarea a fost înregistrată. Primești auditul complet pe email în maximum 30 de minute." : "Solicitarea nu a putut fi trimisă. Verifică datele și încearcă din nou.";
  } else if (params.has("contact")) {
    noticeType = params.get("contact") === "success" ? "success" : "error";
    noticeText = noticeType === "success" ? "Mesajul a fost trimis. Revenim cu un răspuns cât mai curând." : "Mesajul nu a putut fi trimis. Te rugăm să încerci din nou.";
  }
  if (noticeText) {
    var notice = doc.createElement("div");
    notice.className = "newsletter-notice" + (noticeType === "error" ? " is-error" : "");
    notice.setAttribute("role", "status");
    notice.textContent = noticeText;
    body.appendChild(notice);
    window.setTimeout(function () { notice.remove(); }, 7000);
  }
})();

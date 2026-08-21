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
    var atomicOrbit = '<svg class="cabi-ai-atom" viewBox="0 0 160 160" aria-hidden="true">' +
      '<circle class="cabi-ai-ring cabi-ai-ring--outer" cx="80" cy="80" r="66"/><circle class="cabi-ai-ring cabi-ai-ring--inner" cx="80" cy="80" r="42"/>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--outer" r="6.2"><animateMotion dur="12s" begin="0s" repeatCount="indefinite" path="M146 80A66 66 0 1 1 14 80A66 66 0 1 1 146 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--outer" r="6.2"><animateMotion dur="12s" begin="-2s" repeatCount="indefinite" path="M146 80A66 66 0 1 1 14 80A66 66 0 1 1 146 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--outer" r="6.2"><animateMotion dur="12s" begin="-4s" repeatCount="indefinite" path="M146 80A66 66 0 1 1 14 80A66 66 0 1 1 146 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--outer" r="6.2"><animateMotion dur="12s" begin="-6s" repeatCount="indefinite" path="M146 80A66 66 0 1 1 14 80A66 66 0 1 1 146 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--outer" r="6.2"><animateMotion dur="12s" begin="-8s" repeatCount="indefinite" path="M146 80A66 66 0 1 1 14 80A66 66 0 1 1 146 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--outer" r="6.2"><animateMotion dur="12s" begin="-10s" repeatCount="indefinite" path="M146 80A66 66 0 1 1 14 80A66 66 0 1 1 146 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--inner" r="6.8"><animateMotion dur="8s" begin="0s" repeatCount="indefinite" path="M122 80A42 42 0 1 0 38 80A42 42 0 1 0 122 80"/></circle>' +
      '<circle class="cabi-ai-electron cabi-ai-electron--inner" r="6.8"><animateMotion dur="8s" begin="-4s" repeatCount="indefinite" path="M122 80A42 42 0 1 0 38 80A42 42 0 1 0 122 80"/></circle>' +
    '</svg>';
    var common = 'type="button" aria-label="Deschide asistentul inteligent" aria-controls="cabi-ai-panel" aria-expanded="false" data-cabi-smart-button';

    body.insertAdjacentHTML("beforeend", '<button class="cabi-smart-button cabi-smart-button--desktop" ' + common + '>' + icon + tooltip + '</button>');

    var toggle = doc.querySelector(".next-menu-toggle");
    if (toggle) {
      toggle.insertAdjacentHTML("beforebegin", '<button class="cabi-smart-button cabi-smart-button--mobile" ' + common + '>' + icon + '<span class="cabi-smart-button__status" aria-hidden="true"></span></button>');
    }

    var routeIcons = {
      website: '<svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="16"/><path d="M8 24h32M24 8c5 5 7.5 10.3 7.5 16S29 35 24 40c-5-5-7.5-10.3-7.5-16S19 13 24 8Z"/></svg>',
      price: '<svg class="cabi-route-price" viewBox="0 0 48 48" aria-hidden="true"><path d="M7.5 27 26.5 8H40v13.5l-19 19L7.5 27Z"/><circle cx="34" cy="14" r="2.25"/></svg>',
      seo: '<svg viewBox="0 0 48 48" aria-hidden="true"><path d="m9 35 10-11 7 7 13-17M30 14h9v9"/></svg>',
      ads: '<svg class="cabi-route-ads" viewBox="0 0 48 48" aria-hidden="true"><path class="cabi-route-ads__left" d="M23.8 10.5 12.2 30.8"/><path class="cabi-route-ads__right" d="m24.2 10.5 15 26"/><circle class="cabi-route-ads__dot" cx="9.5" cy="36.5" r="4.8"/></svg>',
      portfolio: '<svg viewBox="0 0 48 48" aria-hidden="true"><rect x="8" y="10" width="32" height="28" rx="3"/><circle cx="31" cy="18" r="3"/><path d="m11 34 9-9 6 6 5-5 7 8"/></svg>',
      contact: '<svg class="cabi-route-phone" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.5 3 3.9 5.4 6.9 6.9l2.3-2.3c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1.1.5 1.1 1.1v3.6c0 .6-.5 1.1-1.1 1.1C11 21.6 2.4 13 2.4 2.4c0-.6.5-1.1 1.1-1.1h3.6c.6 0 1.1.5 1.1 1.1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1l-2.2 2.5Z"/></svg>'
    };
    var serviceSubitems = [
      ["Creare site web", "/servicii/creare-site-web/"],
      ["SEO", "/servicii/seo/"],
      ["SEO local", "/servicii/seo-local/"],
      ["Reclame Google Ads", "/servicii/reclame-platite/"],
      ["Social media", "/servicii/social-media/"],
      ["Optimizare conversii", "/servicii/optimizare-conversii/"],
      ["Integrări digitale", "/servicii/integrari-digitale/"],
      ["Analiză digitală", "/servicii/analiza-digitala/"]
    ].map(function (item) {
      return '<a href="' + item[1] + '"><i aria-hidden="true"></i><span>' + item[0] + '</span></a>';
    }).join("");
    var navItems = '<div class="cabi-ai-nav__group"><button type="button" data-cabi-services aria-expanded="false" aria-controls="cabi-ai-services-submenu"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg><span>Servicii</span><svg class="cabi-ai-nav__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m8 10 4 4 4-4"/></svg></button><div class="cabi-ai-nav__submenu" id="cabi-ai-services-submenu" aria-hidden="true">' + serviceSubitems + '</div></div>' + [
      ["Prețuri", "/preturi/", "M12 3v18M8 7h6.5a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6H16"],
      ["Portofoliu", "/portofoliu/", "M4 7h16v12H4zM8 7V5h8v2"],
      ["Blog", "/blog/", "M5 4h14v16H5zM9 8h6M9 12h6M9 16h4"],
      ["Contact", "/contact/", "M4 6h16v12H4zM4 7l8 6 8-6"]
    ].map(function (item) {
      return '<a href="' + item[1] + '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="' + item[2] + '"/></svg><span>' + item[0] + '</span></a>';
    }).join("");
    var panelMarkup = '<div class="cabi-ai-panel" id="cabi-ai-panel" data-cabi-panel aria-hidden="true" hidden>' +
      '<div class="cabi-ai-panel__surface" role="dialog" aria-modal="true" aria-labelledby="cabi-ai-title" tabindex="-1">' +
        '<aside class="cabi-ai-sidebar" id="cabi-ai-sidebar" aria-label="Conversațiile CAB-IT AI">' +
          '<div class="cabi-ai-sidebar__brand"><span>' + icon + '</span><div><strong>Asistent CAB-IT</strong><small><i></i> CAB-IT AI</small></div></div>' +
          '<button class="cabi-ai-new" type="button" data-cabi-new-chat><span aria-hidden="true">＋</span> Conversație nouă <kbd>Ctrl K</kbd></button>' +
          '<p class="cabi-ai-kicker">CONVERSAȚII</p><div class="cabi-ai-recents" data-cabi-recents><p class="cabi-ai-empty">Nu există conversații încă.</p></div>' +
          '<nav class="cabi-ai-nav" aria-label="Scurtături CAB-IT">' + navItems + '</nav>' +
          '<div class="cabi-ai-sidebar__foot"><span>' + icon + '</span><div><strong>Asistent CAB-IT</strong><small><i></i> Disponibil</small></div></div>' +
        '</aside>' +
        '<button class="cabi-ai-sidebar-backdrop" type="button" data-cabi-sidebar-close aria-label="Închide istoricul conversațiilor" hidden></button>' +
        '<section class="cabi-ai-main">' +
          '<header class="cabi-ai-topbar"><div class="cabi-ai-topbar__title"><span class="cabi-ai-topbar__mobile-icon">' + icon + '</span><div><h2 id="cabi-ai-title">Asistent AI CAB-IT <em>BETA</em></h2><p>Asistentul tău AI pentru servicii web și marketing digital</p></div></div><div class="cabi-ai-topbar__tools"><button class="cabi-ai-history-toggle" type="button" data-cabi-history-toggle aria-label="Deschide istoricul conversațiilor" aria-controls="cabi-ai-sidebar" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg></button><button class="cabi-ai-info" type="button" data-cabi-info aria-label="Despre acest agent" aria-controls="cabi-ai-info-popover" aria-expanded="false"><span aria-hidden="true">?</span></button><div class="cabi-ai-info-popover" id="cabi-ai-info-popover" role="tooltip" aria-hidden="true"><strong>Ce face acest agent?</strong><p>Te ajută să găsești servicii, prețuri, proiecte și articole CAB-IT, apoi îți răspunde direct în conversație.</p></div><button class="cabi-ai-close" type="button" data-cabi-close aria-label="Închide asistentul"><span></span><span></span></button></div></header>' +
          '<div class="cabi-ai-canvas" data-cabi-canvas>' +
            '<section class="cabi-ai-start" data-cabi-start>' +
              '<div class="cabi-ai-welcome"><div class="cabi-ai-orbit" aria-hidden="true">' + atomicOrbit + '<span>' + icon + '</span></div><p class="cabi-ai-eyebrow">ORICE ÎNTREBARE. UN SINGUR PUNCT DE PORNIRE.</p><h3>Cum te pot ajuta astăzi?</h3><p>Întreabă despre servicii, prețuri, proiecte sau orice altceva.</p></div>' +
              '<div class="cabi-ai-next"><div class="cabi-ai-actions">' +
                '<button type="button" data-cabi-action="website_general" data-cabi-prompt="Vreau un website"><span>' + routeIcons.website + '</span><div><strong>Vreau un website</strong><small>Site de prezentare sau magazin online</small></div><b>→</b></button>' +
                '<button type="button" data-cabi-action="website_price" data-cabi-prompt="Cât costă un site?"><span>' + routeIcons.price + '</span><div><strong>Cât costă un site?</strong><small>Află prețurile pentru website-uri</small></div><b>→</b></button>' +
                '<button type="button" data-cabi-action="seo_general" data-cabi-prompt="Am nevoie de SEO"><span>' + routeIcons.seo + '</span><div><strong>Am nevoie de SEO</strong><small>Optimizare pentru rezultate mai bune în Google</small></div><b>→</b></button>' +
                '<button class="cabi-ai-action--ads" type="button" data-cabi-action="ads_general" data-cabi-prompt="Vreau reclame Google Ads"><span>' + routeIcons.ads + '</span><div><strong>Vreau reclame Google Ads</strong><small>Campanii care aduc clienți și conversii</small></div><b>→</b></button>' +
                '<button type="button" data-cabi-action="website_portfolio" data-cabi-prompt="Arată-mi proiectele"><span>' + routeIcons.portfolio + '</span><div><strong>Arată-mi proiectele</strong><small>Vezi proiectele recente realizate</small></div><b>→</b></button>' +
                '<button type="button" data-cabi-action="contact" data-cabi-prompt="Vreau să vă contactez"><span>' + routeIcons.contact + '</span><div><strong>Vreau să vă contactez</strong><small>Discută direct cu un specialist CAB-IT</small></div><b>→</b></button>' +
              '</div></div>' +
            '</section>' +
            '<div class="cabi-ai-thread" data-cabi-thread role="log" aria-live="polite" aria-relevant="additions text" aria-busy="false"></div>' +
          '</div>' +
          '<form class="cabi-ai-composer" data-cabi-composer><div class="cabi-ai-composer__box"><textarea rows="1" maxlength="2000" aria-label="Mesaj către asistent" placeholder="Scrie întrebarea ta..."></textarea><button type="submit" aria-label="Trimite mesajul" aria-pressed="false"><span class="cabi-ai-send__arrow" aria-hidden="true">↑</span><span class="cabi-ai-send__stop" aria-hidden="true"><i></i></span></button></div><div class="cabi-ai-composer__below"><p class="cabi-ai-composer__status" data-cabi-status role="status" aria-live="polite" hidden></p></div></form>' +
        '</section>' +
      '</div>' +
      '<div class="cabi-ai-sources-modal" data-cabi-sources-modal aria-hidden="true" hidden>' +
        '<button class="cabi-ai-sources-modal__backdrop" type="button" data-cabi-sources-close tabindex="-1" aria-label="Închide fereastra cu surse"></button>' +
        '<section class="cabi-ai-sources-modal__surface" role="dialog" aria-modal="true" aria-labelledby="cabi-ai-sources-title" aria-describedby="cabi-ai-sources-description" tabindex="-1">' +
          '<header class="cabi-ai-sources-modal__header"><div class="cabi-ai-sources-modal__heading"><span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H12v18H7.5A3.5 3.5 0 0 0 4 23Z"/><path d="M20 5.5A3.5 3.5 0 0 0 16.5 2H12v18h4.5A3.5 3.5 0 0 1 20 23Z"/></svg></span><div><p>SURSELE RĂSPUNSULUI</p><h3 id="cabi-ai-sources-title">Informații folosite</h3></div></div><button class="cabi-ai-sources-modal__close" type="button" data-cabi-sources-close aria-label="Închide sursele"><span></span><span></span></button></header>' +
          '<p class="cabi-ai-sources-modal__intro" id="cabi-ai-sources-description">Consultă paginile pe baza cărora a fost formulat răspunsul.</p>' +
          '<div class="cabi-ai-sources-modal__list" data-cabi-sources-list></div>' +
          '<p class="cabi-ai-sources-modal__note"><span aria-hidden="true">✓</span> Linkurile se deschid într-o filă nouă.</p>' +
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
        '.cabi-ai-sidebar{position:relative;z-index:2;display:flex;flex-direction:column;min-height:0;padding:22px 18px;border-right:1px solid rgba(202,220,225,.9);background:#fff;box-shadow:16px 0 60px rgba(15,48,57,.05);overflow-y:auto;scrollbar-width:none}.cabi-ai-sidebar::-webkit-scrollbar{display:none}' +
        '.cabi-ai-sidebar__brand,.cabi-ai-sidebar__foot{display:flex;align-items:center;gap:11px}' +
        '.cabi-ai-sidebar__brand>span,.cabi-ai-sidebar__foot>span,.cabi-ai-topbar__mobile-icon,.cabi-ai-avatar{display:grid;place-items:center;flex:0 0 auto;color:#fff;background:linear-gradient(145deg,#063c43,#08a99e);box-shadow:0 9px 24px rgba(0,111,104,.2)}' +
        '.cabi-ai-sidebar__brand>span{width:44px;height:44px;border-radius:14px}.cabi-ai-sidebar__brand svg{width:30px;height:30px}' +
        '.cabi-ai-sidebar__brand strong,.cabi-ai-sidebar__foot strong{display:block;font-size:13px}.cabi-ai-sidebar__brand small,.cabi-ai-sidebar__foot small{display:flex;align-items:center;gap:6px;margin-top:3px;color:#68788d;font-size:10px}.cabi-ai-sidebar__brand small i,.cabi-ai-sidebar__foot small i{width:7px;height:7px;border-radius:50%;background:#19c981;box-shadow:0 0 0 3px rgba(25,201,129,.12)}' +
        '.cabi-ai-new{width:100%;min-height:49px;display:flex;align-items:center;gap:10px;margin-top:22px;padding:9px 11px;border:0;border-radius:14px;color:#fff;background:linear-gradient(135deg,#006d67,#09aa9f);box-shadow:0 12px 24px rgba(0,112,105,.18);font:700 13px/1 Inter,Arial,sans-serif;text-align:left;cursor:pointer;transition:transform .18s ease,box-shadow .18s ease}.cabi-ai-new:hover,.cabi-ai-new:focus-visible{outline:0;transform:translateY(-1px);box-shadow:0 15px 29px rgba(0,112,105,.25)}.cabi-ai-new>span{font-size:21px;font-weight:300}.cabi-ai-new kbd{margin-left:auto;padding:5px 7px;border:1px solid rgba(255,255,255,.28);border-radius:7px;background:rgba(255,255,255,.14);font:600 10px Inter,Arial,sans-serif}' +
        '.cabi-ai-kicker{margin:24px 8px 10px;color:#7a899a;font-size:10px;font-weight:800;letter-spacing:.12em}' +
        '.cabi-ai-recents{display:grid;gap:3px}.cabi-ai-recent{position:relative;display:grid;grid-template-columns:minmax(0,1fr) 31px;align-items:center;border-radius:10px;transition:background .18s ease}.cabi-ai-recent:hover,.cabi-ai-recent.is-current{background:#e5f8f5}.cabi-ai-recent__open{min-width:0;min-height:40px;display:flex;align-items:center;gap:9px;padding:7px 5px 7px 9px;border:0;border-radius:10px;color:#263649;background:transparent;font:600 11px/1.3 Inter,Arial,sans-serif;text-align:left;cursor:pointer}.cabi-ai-recent__open:focus-visible,.cabi-ai-recent__delete:focus-visible{outline:2px solid rgba(8,169,158,.35);outline-offset:-2px}.cabi-ai-recent.is-current .cabi-ai-recent__open{color:#005f5a}.cabi-ai-recent__icon{width:21px;height:21px;display:grid;place-items:center;flex:0 0 21px;border:1px solid #cddce1;border-radius:7px;color:#00877f;font-size:9px;background:#fff}.cabi-ai-recent__title{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.cabi-ai-recent__delete{width:29px;height:29px;display:grid;place-items:center;padding:0;border:0;border-radius:8px;color:#7a899a;background:transparent;cursor:pointer;opacity:0;transition:opacity .18s ease,color .18s ease,background .18s ease}.cabi-ai-recent:hover .cabi-ai-recent__delete,.cabi-ai-recent__delete:focus-visible,.cabi-ai-recent.is-current .cabi-ai-recent__delete{opacity:1}.cabi-ai-recent__delete:hover{color:#b13e49;background:#fff}.cabi-ai-recent__delete svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-empty{margin:0 8px;padding:12px;border:1px dashed #d4e3e6;border-radius:12px;color:#7a899a;background:#f8fbfc;font-size:10px;line-height:1.45;text-align:center}' +
        '.cabi-ai-nav{display:grid;gap:2px;margin-top:20px;padding-top:16px;border-top:1px solid #e1eaed}.cabi-ai-nav>a,.cabi-ai-nav__group>button{width:100%;display:flex;align-items:center;gap:10px;padding:8px 9px;border:0;border-radius:9px;color:#3e4e61;background:transparent;font:650 11px/1.3 Inter,Arial,sans-serif;text-align:left;cursor:pointer;transition:color .18s ease,background .18s ease}.cabi-ai-nav>a:hover,.cabi-ai-nav>a:focus-visible,.cabi-ai-nav__group>button:hover,.cabi-ai-nav__group>button:focus-visible,.cabi-ai-nav__group.is-open>button{outline:0;color:#006d67;background:#edf9f7}.cabi-ai-nav svg{width:17px;height:17px;flex:0 0 17px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-nav .cabi-ai-nav__chevron{width:16px;height:16px;flex:0 0 16px;margin-left:auto;color:#64768a;stroke-width:2;transition:transform .26s cubic-bezier(.2,.72,.22,1),color .18s ease}.cabi-ai-nav__group.is-open>button .cabi-ai-nav__chevron{color:#008078;transform:rotate(180deg)}.cabi-ai-nav__submenu{display:grid;gap:1px;max-height:0;margin:0 5px;overflow:hidden;opacity:0;transform:translateY(-5px);transition:max-height .34s cubic-bezier(.2,.72,.22,1),opacity .2s ease,transform .28s ease,margin .28s ease}.cabi-ai-nav__group.is-open .cabi-ai-nav__submenu{max-height:310px;margin:5px 5px 7px;opacity:1;transform:none}.cabi-ai-nav__submenu a{min-height:31px;display:flex;align-items:center;gap:9px;padding:6px 8px 6px 35px;border-radius:8px;color:#627287;font-size:10px;font-weight:620;transition:color .18s ease,background .18s ease,transform .18s ease}.cabi-ai-nav__submenu a:hover,.cabi-ai-nav__submenu a:focus-visible{outline:0;color:#006d67;background:#f0faf8;transform:translateX(2px)}.cabi-ai-nav__submenu i{width:6px;height:6px;flex:0 0 6px;border:1.5px solid #08a99e;border-radius:50%;background:#dff8f4;box-shadow:0 0 0 3px rgba(8,169,158,.07)}' +
        '.cabi-ai-sidebar__foot{margin-top:auto;padding:16px 7px 2px;border-top:1px solid #e1eaed}.cabi-ai-sidebar__foot>span{width:36px;height:36px;border-radius:12px}.cabi-ai-sidebar__foot svg{width:25px;height:25px}.cabi-ai-sidebar-backdrop{display:none}' +
        '.cabi-ai-main{position:relative;z-index:1;min-width:0;height:100%;display:grid;grid-template-rows:auto minmax(0,1fr) auto;overflow:hidden}' +
        '.cabi-ai-topbar{min-height:84px;display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px clamp(22px,4vw,58px);border-bottom:1px solid rgba(215,227,231,.82);background:rgba(255,255,255,.94)}' +
        '.cabi-ai-topbar__title{min-width:0;display:flex;align-items:center;gap:12px}.cabi-ai-topbar__title>div{min-width:0}.cabi-ai-topbar__title h2{margin:0;color:#102033;font:780 17px/1.16 Inter,Arial,sans-serif;letter-spacing:-.015em}.cabi-ai-topbar__title h2 em{display:inline-flex;margin-left:7px;padding:4px 6px;border-radius:6px;color:#00766f;background:#dcf8f4;font-size:8px;font-style:normal;letter-spacing:.08em;vertical-align:2px}.cabi-ai-topbar__title p{margin:2px 0 0;color:#6b7a8f;font-size:11px}.cabi-ai-topbar__mobile-icon{display:none;width:40px;height:40px;border-radius:13px}.cabi-ai-topbar__mobile-icon svg{width:28px;height:28px}' +
        '.cabi-ai-topbar__tools{position:relative;display:flex;align-items:center;gap:8px;flex:0 0 auto}.cabi-ai-history-toggle,.cabi-ai-info,.cabi-ai-close{position:relative;width:46px;height:46px;display:grid;place-items:center;flex:0 0 46px;border:1px solid #d4e1e5;border-radius:15px;color:#183047;background:rgba(255,255,255,.88);box-shadow:0 8px 24px rgba(17,45,55,.07);cursor:pointer;transition:transform .2s ease,background .2s ease,border-color .2s ease}.cabi-ai-history-toggle{display:none}.cabi-ai-history-toggle svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round}.cabi-ai-info{font:750 18px/1 Inter,Arial,sans-serif}.cabi-ai-info>span{display:block;animation:cabi-info-query 2.6s cubic-bezier(.45,0,.2,1) infinite}.cabi-ai-history-toggle:hover,.cabi-ai-history-toggle[aria-expanded="true"],.cabi-ai-info:hover,.cabi-ai-info[aria-expanded="true"]{color:#00756e;border-color:#9edbd5;background:#e9f9f7;transform:translateY(-1px)}.cabi-ai-close:hover{background:#e9f9f7;transform:rotate(3deg)}.cabi-ai-close span{position:absolute;width:18px;height:2px;border-radius:2px;background:#183047;transform:rotate(45deg)}.cabi-ai-close span+span{transform:rotate(-45deg)}.cabi-ai-info-popover{position:absolute;z-index:20;right:54px;top:calc(100% + 11px);width:300px;padding:15px 16px;border:1px solid #cfe3e5;border-radius:16px;color:#243448;background:rgba(255,255,255,.98);box-shadow:0 20px 48px rgba(17,52,61,.16);opacity:0;visibility:hidden;pointer-events:none;transform:translateY(-7px) scale(.98);transform-origin:top right;transition:opacity .18s ease,transform .18s ease,visibility .18s}.cabi-ai-info-popover::before{position:absolute;content:"";right:13px;top:-6px;width:11px;height:11px;border-top:1px solid #cfe3e5;border-left:1px solid #cfe3e5;background:#fff;transform:rotate(45deg)}.cabi-ai-info-popover.is-open{opacity:1;visibility:visible;pointer-events:auto;transform:none}.cabi-ai-info-popover strong{display:block;color:#102033;font-size:12px}.cabi-ai-info-popover p{margin:7px 0 0;color:#65758a;font-size:10px;line-height:1.55}' +
        '.cabi-ai-canvas{min-height:0;overflow:auto;overscroll-behavior:contain;scrollbar-width:none;padding:clamp(24px,3.5vh,46px) clamp(22px,5vw,78px) 28px}.cabi-ai-canvas::-webkit-scrollbar{display:none}.cabi-ai-start{max-height:900px;overflow:hidden;opacity:1;transform:none;transform-origin:50% 16%;transition:max-height .42s cubic-bezier(.2,.72,.22,1),opacity .22s ease,transform .34s cubic-bezier(.2,.72,.22,1)}.cabi-ai-canvas.has-conversation .cabi-ai-start{max-height:0;opacity:0;pointer-events:none;transform:translateY(-16px) scale(.985)}' +
        '.cabi-ai-welcome{position:relative;isolation:isolate;max-width:760px;margin:0 auto;text-align:center}.cabi-ai-welcome::before{position:absolute;z-index:-1;content:"";left:50%;top:-34px;width:390px;height:235px;border-radius:50%;background:radial-gradient(ellipse,rgba(70,225,211,.17),rgba(9,153,143,.055) 48%,transparent 72%);filter:blur(3px);transform:translateX(-50%);pointer-events:none}.cabi-ai-orbit{position:relative;isolation:isolate;width:170px;height:170px;display:grid;place-items:center;margin:0 auto 17px}.cabi-ai-orbit::before{position:absolute;z-index:0;content:"";inset:42px;border-radius:50%;background:radial-gradient(circle,rgba(36,223,207,.34),rgba(0,118,111,.07) 62%,transparent 72%);animation:cabi-nucleus-halo 2.8s ease-in-out infinite}.cabi-ai-orbit::after{position:absolute;z-index:-1;content:"";inset:10px;border-radius:50%;background:radial-gradient(circle,rgba(94,238,225,.12),transparent 67%);box-shadow:0 0 70px rgba(25,192,179,.12)}.cabi-ai-orbit>.cabi-ai-atom{position:absolute;z-index:1;inset:0;width:100%;height:100%;overflow:visible}.cabi-ai-ring{fill:none;stroke-linecap:round}.cabi-ai-ring--outer{stroke:rgba(0,111,104,.39);stroke-width:2}.cabi-ai-ring--inner{stroke:rgba(18,183,171,.44);stroke-width:1.8}.cabi-ai-electron{stroke-width:2.1;filter:drop-shadow(0 0 5px rgba(3,192,178,.72))}.cabi-ai-electron--outer{fill:#39d1b5;stroke:#08766f}.cabi-ai-electron--inner{fill:#a8fff0;stroke:#079d92}.cabi-ai-orbit>span{position:relative;z-index:2;width:88px;height:88px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.8);border-radius:50%;color:#fff;background:linear-gradient(145deg,#063c43,#08a99e);box-shadow:0 20px 46px rgba(0,111,104,.36),0 0 0 9px rgba(222,250,247,.78);animation:cabi-nucleus-breathe 3.2s ease-in-out infinite}.cabi-ai-orbit>span .cabi-smart-button__icon{width:58px;height:58px}' +
        '.cabi-ai-eyebrow{margin:0 0 8px!important;color:#008078!important;font-size:9px!important;font-weight:850!important;letter-spacing:.16em}.cabi-ai-welcome h3{margin:0;color:#102033;font:800 clamp(25px,3vw,39px)/1.08 Inter,Arial,sans-serif;letter-spacing:-.035em}.cabi-ai-welcome>p:last-child{max-width:650px;margin:11px auto 0;color:#65758a;font-size:13px;line-height:1.6}' +
        '.cabi-ai-thread{max-width:920px;min-height:0;display:grid;align-content:start;gap:18px;margin:0 auto;opacity:0;pointer-events:none;transform:translateY(18px);transition:opacity .28s ease .08s,transform .34s cubic-bezier(.2,.72,.22,1) .06s}.cabi-ai-canvas.has-conversation .cabi-ai-thread{opacity:1;pointer-events:auto;transform:none}.cabi-ai-message{border:1px solid #d8e4e8;border-radius:20px;padding:16px 18px;background:rgba(255,255,255,.92);box-shadow:0 12px 34px rgba(22,58,67,.055)}.cabi-ai-message p{margin:0;color:#243448;font-size:13px;line-height:1.62;white-space:pre-wrap;overflow-wrap:anywhere}.cabi-ai-message p+p{margin-top:10px}.cabi-ai-message small{color:#77869a;font-size:10px}.cabi-ai-message--user{max-width:430px;justify-self:end;border-color:#c9ebe6;background:linear-gradient(145deg,#f4fffd,#e6f8f5)}.cabi-ai-message--user small{display:block;margin-top:8px;text-align:right}.cabi-ai-message--user b{color:#049d92}.cabi-ai-message-row{display:flex;align-items:flex-end;gap:11px}.cabi-ai-avatar{width:39px;height:39px;border-radius:13px}.cabi-ai-avatar svg{width:27px;height:27px}.cabi-ai-message--assistant{max-width:650px;text-align:left}.cabi-ai-message--assistant.is-streaming-response>p:first-child::after{content:"";display:inline-block;width:2px;height:1.08em;margin-left:3px;border-radius:2px;background:#079d92;vertical-align:-.16em;animation:cabi-response-cursor .72s steps(1,end) infinite}.cabi-ai-message--assistant.is-streaming-response>p+p,.cabi-ai-message--assistant.is-streaming-response>.cabi-ai-message__actions,.cabi-ai-message--assistant.is-streaming-response>.cabi-ai-message__meta{opacity:0;visibility:hidden;pointer-events:none;transform:translateY(5px)}.cabi-ai-message--assistant>p+p,.cabi-ai-message--assistant>.cabi-ai-message__actions,.cabi-ai-message--assistant>.cabi-ai-message__meta{transition:opacity .2s ease,transform .24s ease}.cabi-ai-message--assistant.has-streamed-response>p+p,.cabi-ai-message--assistant.has-streamed-response>.cabi-ai-message__actions,.cabi-ai-message--assistant.has-streamed-response>.cabi-ai-message__meta{animation:cabi-response-details .28s cubic-bezier(.2,.72,.22,1) both}.cabi-ai-message__meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;color:#77869a;font-size:10px}.cabi-ai-message__source{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.cabi-ai-message__sources{min-height:34px;display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border:1px solid #4b555b;border-radius:11px;color:#fff;background:linear-gradient(145deg,#50585d,#3e464b);box-shadow:0 7px 16px rgba(31,42,48,.14);font-size:11px;font-weight:750;transition:transform .18s ease,background .18s ease,box-shadow .18s ease}.cabi-ai-message__sources svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-message__sources:hover,.cabi-ai-message__sources:focus-visible{outline:0;background:linear-gradient(145deg,#3d484d,#273237);box-shadow:0 9px 19px rgba(25,40,46,.2);transform:translateY(-1px)}.cabi-ai-message__actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:13px}.cabi-ai-message__actions a{min-height:36px;display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border:1px solid #bcdedb;border-radius:11px;color:#006d67;background:#f1fbf9;font-size:11px;font-weight:750;transition:transform .18s ease,background .18s ease,border-color .18s ease}.cabi-ai-message__actions a:hover,.cabi-ai-message__actions a:focus-visible{outline:0;border-color:#63c9c0;background:#e2f8f5;transform:translateY(-1px)}.cabi-ai-typing{display:flex;align-items:center;gap:5px;min-width:168px;min-height:48px}.cabi-ai-typing__label{margin-right:7px;color:#4d6271;font-size:11px;font-weight:750;white-space:nowrap}.cabi-ai-typing i{width:7px;height:7px;border-radius:50%;background:#16a99e;animation:cabi-typing 1.15s ease-in-out infinite}.cabi-ai-typing i:nth-last-child(2){animation-delay:.14s}.cabi-ai-typing i:last-child{animation-delay:.28s}@keyframes cabi-typing{0%,60%,100%{opacity:.35;transform:translateY(0)}30%{opacity:1;transform:translateY(-4px)}}@keyframes cabi-response-cursor{0%,45%{opacity:1}46%,100%{opacity:.15}}@keyframes cabi-response-details{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}' +
        '.cabi-ai-next{max-width:1040px;margin:30px auto 0}.cabi-ai-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.cabi-ai-actions button{position:relative;min-width:0;min-height:112px;display:flex;align-items:center;gap:17px;padding:18px 44px 18px 18px;border:1px solid #d9e5e8;border-radius:17px;color:#17273a;background:rgba(255,255,255,.92);box-shadow:0 10px 28px rgba(20,55,65,.045);overflow:hidden;transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease}.cabi-ai-actions button:hover,.cabi-ai-actions button:focus-visible{outline:0;border-color:#65d1c7;box-shadow:0 16px 34px rgba(0,111,104,.11);transform:translateY(-3px)}.cabi-ai-actions button:active{transform:translateY(-1px) scale(.985)}.cabi-ai-actions button>span{position:relative;z-index:1;width:58px;height:58px;display:grid;place-items:center;flex:0 0 58px;border-radius:50%;color:#087f78;background:linear-gradient(145deg,#eaf9f7,#dff3f1);transition:transform .24s ease,background .24s ease,box-shadow .24s ease}.cabi-ai-actions button>span svg{width:34px;height:34px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-actions button>span svg.cabi-route-ads{overflow:visible;fill:none;stroke:none}.cabi-route-ads__right{fill:#087f78;transform-origin:center;transition:transform .28s ease,fill .28s ease}.cabi-route-ads__left{fill:#1fc7b6;transform-origin:center;transition:transform .28s ease,fill .28s ease}.cabi-route-ads__dot{fill:#006b66;transform-origin:8.4px 37px;transition:transform .28s ease,fill .28s ease}.cabi-ai-action--ads::before{position:absolute;content:"";inset:0;background:radial-gradient(circle at 18% 50%,rgba(31,199,182,.18),transparent 35%),linear-gradient(110deg,transparent 55%,rgba(8,127,120,.055));opacity:0;transition:opacity .25s ease}.cabi-ai-action--ads:hover::before,.cabi-ai-action--ads:focus-visible::before{opacity:1}.cabi-ai-action--ads:hover>span,.cabi-ai-action--ads:focus-visible>span{background:linear-gradient(145deg,#d9faf5,#c6eee9);box-shadow:0 10px 22px rgba(0,119,112,.16);transform:scale(1.07) rotate(-2deg)}.cabi-ai-action--ads:hover .cabi-route-ads__right,.cabi-ai-action--ads:focus-visible .cabi-route-ads__right{fill:#006b66;transform:translate(1px,-1px)}.cabi-ai-action--ads:hover .cabi-route-ads__left,.cabi-ai-action--ads:focus-visible .cabi-route-ads__left{fill:#18bdaa;transform:translate(-1px,1px)}.cabi-ai-action--ads:hover .cabi-route-ads__dot,.cabi-ai-action--ads:focus-visible .cabi-route-ads__dot{fill:#064f51;transform:scale(1.16)}.cabi-ai-actions button div{position:relative;z-index:1;min-width:0}.cabi-ai-actions strong,.cabi-ai-actions small{display:block}.cabi-ai-actions strong{font-size:13px;line-height:1.25}.cabi-ai-actions small{margin-top:6px;color:#66768b;font-size:10px;line-height:1.45}.cabi-ai-actions b{position:absolute;z-index:1;right:17px;bottom:15px;color:#079d92;font-size:18px;font-weight:500;transition:transform .2s ease}.cabi-ai-actions button:hover b,.cabi-ai-actions button:focus-visible b{transform:translateX(4px)}' +
        '.cabi-route-price{transform:none}.cabi-route-ads .cabi-route-ads__right,.cabi-route-ads .cabi-route-ads__left{fill:none;stroke-width:8.5;stroke-linecap:round;stroke-linejoin:round}.cabi-route-ads .cabi-route-ads__right{stroke:#087f78}.cabi-route-ads .cabi-route-ads__left{stroke:#23c8b6}.cabi-route-ads .cabi-route-ads__dot{fill:#006b66;stroke:none}.cabi-ai-actions button>span svg.cabi-route-phone{fill:#07966f;stroke:none}.cabi-ai-actions button{appearance:none;-webkit-appearance:none;font-family:inherit;text-align:left;cursor:pointer}' +
        '.cabi-ai-message__sources{appearance:none;-webkit-appearance:none;min-height:38px;display:inline-flex;align-items:center;gap:9px;padding:7px 10px 7px 8px;border:1px solid #b9dfda;border-radius:12px;color:#006d67;background:linear-gradient(145deg,#fff,#effbf9);box-shadow:0 8px 20px rgba(10,96,91,.08);font:750 11px/1 Inter,Arial,sans-serif;cursor:pointer;transition:transform .18s ease,border-color .18s ease,background .18s ease,box-shadow .18s ease}.cabi-ai-message__sources>svg{width:24px;height:24px;padding:5px;border-radius:8px;box-sizing:border-box;color:#00877f;background:#ddf6f2;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-message__sources-count{min-width:20px;height:20px;display:grid;place-items:center;margin-left:1px;padding:0 5px;border-radius:7px;color:#087970;background:#dff7f3;font-size:9px;font-weight:850}.cabi-ai-message__sources:hover,.cabi-ai-message__sources:focus-visible{outline:0;border-color:#62c8be;color:#005f59;background:#eaf9f7;box-shadow:0 11px 24px rgba(0,111,104,.13);transform:translateY(-1px)}' +
        '.cabi-ai-sources-modal[hidden]{display:none!important}.cabi-ai-sources-modal{position:absolute;z-index:50;inset:0;display:grid;place-items:center;padding:24px;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .2s ease,visibility .2s ease}.cabi-ai-sources-modal.is-open{opacity:1;visibility:visible;pointer-events:auto}.cabi-ai-sources-modal__backdrop{position:absolute;inset:0;width:100%;height:100%;padding:0;border:0;background:rgba(7,31,38,.46);backdrop-filter:blur(7px);cursor:default}.cabi-ai-sources-modal__surface{position:relative;width:min(620px,100%);max-height:min(690px,calc(100vh - 48px));display:flex;flex-direction:column;padding:0;border:1px solid rgba(184,222,219,.96);border-radius:26px;color:#17273a;background:linear-gradient(155deg,#fff 0%,#f7fcfb 72%,#edf9f7 100%);box-shadow:0 32px 88px rgba(7,43,50,.24),0 0 0 1px rgba(255,255,255,.72) inset;overflow:hidden;opacity:0;transform:translateY(14px) scale(.975);transition:opacity .22s ease,transform .3s cubic-bezier(.2,.78,.2,1)}.cabi-ai-sources-modal.is-open .cabi-ai-sources-modal__surface{opacity:1;transform:none}.cabi-ai-sources-modal__header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:21px 22px 15px;border-bottom:1px solid #e2ecee}.cabi-ai-sources-modal__heading{min-width:0;display:flex;align-items:center;gap:13px}.cabi-ai-sources-modal__heading>span{width:43px;height:43px;display:grid;place-items:center;flex:0 0 43px;border-radius:14px;color:#fff;background:linear-gradient(145deg,#006d67,#13aa9f);box-shadow:0 10px 22px rgba(0,111,104,.2)}.cabi-ai-sources-modal__heading svg{width:23px;height:23px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-sources-modal__heading p{margin:0 0 3px;color:#008078;font-size:8px;font-weight:850;letter-spacing:.15em}.cabi-ai-sources-modal__heading h3{margin:0;color:#102033;font:800 20px/1.15 Inter,Arial,sans-serif;letter-spacing:-.025em}.cabi-ai-sources-modal__close{position:relative;width:40px;height:40px;display:grid;place-items:center;flex:0 0 40px;padding:0;border:1px solid #d5e3e6;border-radius:13px;color:#294055;background:#fff;box-shadow:0 7px 18px rgba(18,52,61,.06);cursor:pointer;transition:transform .18s ease,border-color .18s ease,background .18s ease}.cabi-ai-sources-modal__close:hover,.cabi-ai-sources-modal__close:focus-visible{outline:0;border-color:#8ed4cd;background:#eaf9f7;transform:rotate(3deg)}.cabi-ai-sources-modal__close span{position:absolute;width:17px;height:2px;border-radius:2px;background:currentColor;transform:rotate(45deg)}.cabi-ai-sources-modal__close span+span{transform:rotate(-45deg)}.cabi-ai-sources-modal__intro{margin:0;padding:15px 22px 7px;color:#66768a;font-size:11px;line-height:1.55}.cabi-ai-sources-modal__list{display:grid;gap:9px;margin:0;padding:8px 22px 13px;overflow:auto;overscroll-behavior:contain;scrollbar-width:thin;scrollbar-color:#aad8d3 transparent}.cabi-ai-sources-modal__item{min-width:0;display:grid;grid-template-columns:38px minmax(0,1fr) 34px;align-items:center;gap:11px;padding:11px;border:1px solid #dbe8ea;border-radius:15px;color:#243448;background:rgba(255,255,255,.9);box-shadow:0 8px 22px rgba(24,61,70,.045);transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease}.cabi-ai-sources-modal__item:hover,.cabi-ai-sources-modal__item:focus-visible{outline:0;border-color:#7bcfc6;box-shadow:0 12px 27px rgba(0,111,104,.09);transform:translateY(-1px)}.cabi-ai-sources-modal__index{width:38px;height:38px;display:grid;place-items:center;border-radius:12px;color:#00766f;background:#e1f7f3;font-size:10px;font-weight:850}.cabi-ai-sources-modal__copy{min-width:0}.cabi-ai-sources-modal__copy strong,.cabi-ai-sources-modal__copy small{display:block}.cabi-ai-sources-modal__copy strong{overflow:hidden;color:#17273a;font-size:12px;line-height:1.35;text-overflow:ellipsis;white-space:nowrap}.cabi-ai-sources-modal__copy small{margin-top:4px;overflow:hidden;color:#718095;font-size:9px;text-overflow:ellipsis;white-space:nowrap}.cabi-ai-sources-modal__external{width:34px;height:34px;display:grid;place-items:center;border:1px solid #d6e5e7;border-radius:11px;color:#007a73;background:#f5fbfa}.cabi-ai-sources-modal__external svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.cabi-ai-sources-modal__note{display:flex;align-items:center;gap:7px;margin:0;padding:12px 22px 17px;border-top:1px solid #e2ecee;color:#7a8999;font-size:9px}.cabi-ai-sources-modal__note span{width:18px;height:18px;display:grid;place-items:center;border-radius:50%;color:#00766f;background:#def6f2;font-size:9px;font-weight:900}' +
        '.cabi-ai-action--ads::before{display:none!important}.cabi-ai-action--ads:hover>span,.cabi-ai-action--ads:focus-visible>span{background:linear-gradient(145deg,#eaf9f7,#dff3f1);box-shadow:none;transform:none}.cabi-ai-action--ads:hover .cabi-route-ads__right,.cabi-ai-action--ads:focus-visible .cabi-route-ads__right,.cabi-ai-action--ads:hover .cabi-route-ads__left,.cabi-ai-action--ads:focus-visible .cabi-route-ads__left{fill:none;transform:none}.cabi-ai-action--ads:hover .cabi-route-ads__dot,.cabi-ai-action--ads:focus-visible .cabi-route-ads__dot{fill:#006b66;transform:none}' +
        '.cabi-ai-thread{max-width:860px;gap:12px}.cabi-ai-message-row{align-items:flex-end;gap:8px}.cabi-ai-message{width:fit-content;max-width:100%;padding:11px 13px 8px;border-radius:18px;background:#fff;box-shadow:0 5px 18px rgba(19,51,59,.055)}.cabi-ai-message p{font-size:12.5px;line-height:1.56}.cabi-ai-message--assistant{width:fit-content;max-width:min(78%,680px);border-color:#dce7e9;border-radius:18px 18px 18px 5px}.cabi-ai-message--assistant p+p{margin-top:9px;padding-top:9px;border-top:1px solid #edf2f3}.cabi-ai-message--user{width:fit-content;max-width:min(72%,560px);padding:10px 13px 7px;border:0;border-radius:18px 18px 5px 18px;background:#087d75;box-shadow:0 7px 20px rgba(0,105,98,.15)}.cabi-ai-message--user p{color:#fff;line-height:1.48}.cabi-ai-message--user small{margin-top:5px;color:rgba(255,255,255,.68);font-size:8.5px}.cabi-ai-avatar{width:32px;height:32px;flex:0 0 32px;border-radius:11px;box-shadow:0 5px 14px rgba(0,111,104,.14)}.cabi-ai-avatar svg{width:22px;height:22px}.cabi-ai-message__meta{min-height:24px;margin-top:7px;color:#8a97a5;font-size:8.5px}.cabi-ai-message__sources{min-height:24px;padding:2px 5px 2px 3px;border-color:transparent;border-radius:7px;color:#71808f;background:transparent;box-shadow:none;font-size:8.5px}.cabi-ai-message__sources>svg{width:17px;height:17px;padding:3px;border-radius:5px;color:#728590;background:transparent}.cabi-ai-message__sources-count{min-width:15px;height:15px;padding:0 3px;border-radius:5px;color:#71808f;background:#edf2f3;font-size:7.5px}.cabi-ai-message__sources:hover,.cabi-ai-message__sources:focus-visible{border-color:#e0e9eb;color:#526575;background:#f4f8f8;box-shadow:none;transform:none}.cabi-ai-message__actions{gap:6px;margin-top:9px}.cabi-ai-message__actions a{min-height:30px;padding:6px 9px;border-radius:9px;font-size:9.5px;box-shadow:none}.cabi-ai-message__actions a.is-call,.cabi-ai-message__actions a.is-phone{gap:6px;border-color:#0b8c82;color:#fff;background:#087d75}.cabi-ai-message__actions a.is-call:hover,.cabi-ai-message__actions a.is-call:focus-visible,.cabi-ai-message__actions a.is-phone:hover,.cabi-ai-message__actions a.is-phone:focus-visible{border-color:#076c65;color:#fff;background:#076c65}.cabi-ai-message__actions a.is-call svg,.cabi-ai-message__actions a.is-phone svg{width:14px;height:14px;fill:currentColor}.cabi-ai-typing{min-width:176px;min-height:40px;padding:9px 12px}.cabi-ai-typing__label{font-size:10px}' +
        '.cabi-ai-composer{padding:12px clamp(22px,5vw,78px) 14px;background:linear-gradient(180deg,rgba(247,251,251,0),#f7fbfb 24%)}.cabi-ai-composer__box{max-width:1040px;min-height:58px;display:flex;align-items:center;gap:12px;margin:0 auto;padding:9px 10px 9px 16px;border:1.5px solid #30afa5;border-radius:19px;background:rgba(255,255,255,.96);box-shadow:0 15px 35px rgba(0,104,98,.09)}.cabi-ai-composer input{min-width:0;flex:1;height:38px;padding:0;border:0;outline:0;color:#344559;background:transparent;font:500 13px/1.5 Inter,Arial,sans-serif}.cabi-ai-composer input::placeholder{color:#8795a6}.cabi-ai-composer button{position:relative;width:40px;height:40px;display:grid;place-items:center;flex:0 0 40px;border:0;border-radius:13px;color:#fff;background:linear-gradient(145deg,#006d67,#08aaa0);box-shadow:0 8px 18px rgba(0,111,104,.2);font-size:20px;cursor:pointer;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease,filter .2s ease}.cabi-ai-composer button::before{position:absolute;content:"";inset:-2px;border-radius:inherit;background:linear-gradient(120deg,transparent 22%,rgba(255,255,255,.5) 46%,transparent 68%);transform:translateX(-130%);transition:transform .45s ease}.cabi-ai-composer button .cabi-ai-send__arrow,.cabi-ai-composer button .cabi-ai-send__stop{position:absolute;z-index:1;inset:0;display:grid;place-items:center;transition:opacity .24s ease,transform .32s cubic-bezier(.22,.8,.24,1)}.cabi-ai-composer button .cabi-ai-send__arrow{opacity:1;transform:translateY(0)}.cabi-ai-composer button .cabi-ai-send__stop{opacity:0;transform:translateY(32px)}.cabi-ai-send__stop i{width:11px;height:11px;border-radius:2px;background:#fff;box-shadow:0 0 0 1px rgba(255,255,255,.3)}.cabi-ai-composer button.is-sending .cabi-ai-send__arrow{opacity:0;transform:translateY(-32px) scale(.72)}.cabi-ai-composer button.is-sending .cabi-ai-send__stop{opacity:1;transform:translateY(0)}.cabi-ai-composer button:hover,.cabi-ai-composer button:focus-visible{outline:0;filter:saturate(1.12);transform:translateY(-2px);box-shadow:0 13px 24px rgba(0,111,104,.31),0 0 0 4px rgba(8,170,160,.12)}.cabi-ai-composer button:hover::before,.cabi-ai-composer button:focus-visible::before{transform:translateX(130%)}.cabi-ai-composer button:not(.is-sending):hover .cabi-ai-send__arrow,.cabi-ai-composer button:not(.is-sending):focus-visible .cabi-ai-send__arrow{transform:translateY(-2px)}.cabi-ai-composer button.is-sending:hover .cabi-ai-send__stop,.cabi-ai-composer button.is-sending:focus-visible .cabi-ai-send__stop{transform:scale(.9)}.cabi-ai-composer button:active{transform:translateY(0) scale(.92);box-shadow:0 5px 12px rgba(0,111,104,.22)}.cabi-ai-composer>p{margin:8px 0 0;color:#8390a0;font-size:9px;text-align:center}' +
        '.cabi-ai-composer__box{align-items:flex-end}.cabi-ai-composer textarea{box-sizing:border-box;min-width:0;max-height:116px;flex:1;height:38px;margin:0;padding:8px 0 6px;border:0;outline:0;resize:none;overflow-y:auto;color:#344559;background:transparent;font:500 13px/1.5 Inter,Arial,sans-serif}.cabi-ai-composer textarea::placeholder{color:#8795a6}.cabi-ai-composer button:disabled{opacity:.5;cursor:default;filter:none;transform:none;box-shadow:0 6px 14px rgba(0,111,104,.13)}.cabi-ai-composer__below{max-width:1040px;min-height:0;display:flex;align-items:center;justify-content:center;margin:3px auto 0;color:#7d8b9b;font-size:9px;line-height:1.35}.cabi-ai-composer__status{flex:0 0 100%;margin:0;color:#087b75;text-align:center}.cabi-ai-composer__status.is-error{color:#ad4650}' +
        '.cabi-ai-composer button.is-sending .cabi-ai-send__arrow{animation:cabi-send-arrow-out .58s cubic-bezier(.45,.02,.2,1) forwards}.cabi-ai-composer button.is-sending .cabi-ai-send__stop{animation:cabi-send-stop-in .64s cubic-bezier(.2,.78,.2,1) forwards}.cabi-ai-composer button.is-returning .cabi-ai-send__stop{animation:cabi-send-stop-out .58s cubic-bezier(.45,.02,.2,1) forwards}.cabi-ai-composer button.is-returning .cabi-ai-send__arrow{animation:cabi-send-arrow-in .64s cubic-bezier(.2,.78,.2,1) forwards}' +
        '@keyframes cabi-send-arrow-out{0%{opacity:1;transform:translateY(0) scale(1)}38%{opacity:1;transform:translateY(5px) scale(.96)}100%{opacity:0;transform:translateY(-32px) scale(.72)}}@keyframes cabi-send-stop-in{0%,48%{opacity:0;transform:translateY(32px) scale(.72)}100%{opacity:1;transform:translateY(0) scale(1)}}@keyframes cabi-send-stop-out{0%{opacity:1;transform:translateY(0) scale(1)}38%{opacity:1;transform:translateY(-5px) scale(.96)}100%{opacity:0;transform:translateY(32px) scale(.72)}}@keyframes cabi-send-arrow-in{0%,48%{opacity:0;transform:translateY(-32px) scale(.72)}100%{opacity:1;transform:translateY(0) scale(1)}}' +
        '@keyframes cabi-info-query{0%,58%,100%{opacity:1;transform:translateY(0) rotate(0)}67%{opacity:.72;transform:translateY(-3px) rotate(-8deg)}76%{opacity:1;transform:translateY(0) rotate(6deg)}84%{transform:rotate(0)}}@keyframes cabi-atom-drift{to{transform:rotate(360deg)}}@keyframes cabi-nucleus-breathe{0%,100%{transform:scale(1);box-shadow:0 17px 38px rgba(0,111,104,.3),0 0 0 8px rgba(222,250,247,.76)}50%{transform:scale(1.045);box-shadow:0 19px 44px rgba(0,111,104,.36),0 0 0 12px rgba(205,249,244,.48)}}@keyframes cabi-nucleus-halo{0%,100%{opacity:.55;transform:scale(.84)}50%{opacity:1;transform:scale(1.22)}}@keyframes cabi-portal-open{0%{opacity:1;transform:translate(-50%,-50%) scale(.7)}35%{opacity:.92;transform:translate(-50%,-50%) scale(1.7)}100%{opacity:0;transform:translate(-50%,-50%) scale(6)}}@keyframes cabi-portal-close{0%{opacity:0;transform:translate(-50%,-50%) scale(5)}68%{opacity:.72;transform:translate(-50%,-50%) scale(1.45)}100%{opacity:0;transform:translate(-50%,-50%) scale(.72)}}' +
        '@media(max-width:1020px){.next-nav-wrap{gap:0}.cabi-smart-button--desktop{display:none}.cabi-smart-button--mobile{display:grid;margin-left:auto;margin-right:8px}.cabi-smart-button--mobile+.next-menu-toggle{margin-left:0}}' +
        '@media(max-width:820px){.cabi-ai-panel{--cabi-origin-x:calc(100vw - 85px);--cabi-origin-y:43px;background:#f7fbfb}.cabi-ai-panel__surface{display:block}.cabi-ai-sidebar{display:none}.cabi-ai-main{height:100%;grid-template-rows:auto minmax(0,1fr) auto}.cabi-ai-topbar{min-height:78px;padding:10px 12px;gap:8px}.cabi-ai-topbar__mobile-icon{width:38px;height:38px;display:grid;flex-basis:38px;border-radius:12px}.cabi-ai-topbar__mobile-icon svg{width:26px;height:26px}.cabi-ai-topbar__title{gap:9px}.cabi-ai-topbar__title h2{font-size:15px}.cabi-ai-topbar__title p{max-width:none;margin-top:1px;font-size:8.5px;line-height:1.3;white-space:normal;overflow:visible}.cabi-ai-info,.cabi-ai-close{width:40px;height:40px;flex-basis:40px;border-radius:13px}.cabi-ai-topbar__tools{gap:5px}.cabi-ai-info-popover{right:45px;width:min(292px,calc(100vw - 24px))}.cabi-ai-canvas{padding:13px 14px 22px}.cabi-ai-welcome::before{top:-25px;width:290px;height:190px}.cabi-ai-orbit{width:138px;height:138px;margin-bottom:12px}.cabi-ai-orbit>span{width:72px;height:72px;border-radius:50%;box-shadow:0 16px 36px rgba(0,111,104,.32),0 0 0 7px rgba(222,250,247,.78)}.cabi-ai-orbit>span .cabi-smart-button__icon{width:48px;height:48px}.cabi-ai-welcome h3{font-size:27px}.cabi-ai-welcome>p:last-child{font-size:12px}.cabi-ai-next{margin-top:22px}.cabi-ai-actions{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.cabi-ai-actions button{min-height:118px;display:block;padding:13px 34px 13px 13px}.cabi-ai-actions button>span{width:44px;height:44px;margin-bottom:10px}.cabi-ai-actions button>span svg{width:27px;height:27px}.cabi-ai-actions strong{font-size:11px}.cabi-ai-actions small{margin-top:4px;font-size:9px}.cabi-ai-actions b{right:12px;bottom:11px}.cabi-ai-composer{padding:9px 10px max(10px,env(safe-area-inset-bottom))}.cabi-ai-composer__box{padding:9px 10px;border-radius:17px}.cabi-ai-composer>p{font-size:8px}}' +
        '@media(max-width:420px){.cabi-ai-topbar{min-height:82px;padding-inline:9px;gap:6px}.cabi-ai-topbar__mobile-icon{width:34px;height:34px;flex-basis:34px;border-radius:11px}.cabi-ai-topbar__mobile-icon svg{width:24px;height:24px}.cabi-ai-topbar__title{gap:7px}.cabi-ai-topbar__title h2{font-size:13.5px;white-space:nowrap}.cabi-ai-topbar__title h2 em{display:inline-flex;margin-left:3px;padding:3px 4px;font-size:7px}.cabi-ai-topbar__title p{margin-top:1px;font-size:8px;line-height:1.25;white-space:normal;overflow:visible}.cabi-ai-info,.cabi-ai-close{width:38px;height:38px;flex-basis:38px}.cabi-ai-topbar__tools{gap:4px}.cabi-ai-eyebrow{font-size:8px!important}.cabi-ai-message--assistant{max-width:calc(100% - 42px)}.cabi-ai-actions button>span{width:40px;height:40px;flex-basis:40px;margin-bottom:9px}.cabi-ai-actions button>span svg{width:24px;height:24px}.cabi-ai-actions button>span svg.cabi-route-price{width:23px;height:23px}.cabi-ai-actions button>span svg.cabi-route-ads{width:25px;height:25px}.cabi-ai-actions button>span svg.cabi-route-phone{width:22px;height:22px}.cabi-ai-actions strong{font-size:10px}}' +
        '@media(max-width:820px){.cabi-ai-panel{inset:auto;top:var(--cabi-vv-top,0px);left:var(--cabi-vv-left,0px);width:var(--cabi-vv-width,100vw);height:var(--cabi-vv-height,100vh);min-height:0}.cabi-ai-panel__surface{height:100%;overflow:hidden}.cabi-ai-sidebar{position:absolute;z-index:12;inset:0 auto 0 0;width:min(294px,86vw);display:flex;transform:translateX(-105%);transition:transform .3s cubic-bezier(.2,.72,.22,1)}.cabi-ai-sidebar.is-mobile-open{transform:none}.cabi-ai-sidebar-backdrop{position:absolute;z-index:11;inset:0;width:100%;height:100%;display:block;padding:0;border:0;background:rgba(7,30,37,.28);backdrop-filter:blur(2px);cursor:pointer}.cabi-ai-sidebar-backdrop[hidden]{display:none}.cabi-ai-history-toggle{display:grid}.cabi-ai-history-toggle,.cabi-ai-info,.cabi-ai-close{width:40px;height:40px;flex-basis:40px;border-radius:13px}.cabi-ai-recents{padding-bottom:8px}.cabi-ai-recent__delete{opacity:1}.cabi-ai-thread{gap:13px}.cabi-ai-message{padding:13px 14px;border-radius:17px}.cabi-ai-message p{font-size:12px}.cabi-ai-message__actions a{min-height:38px}.cabi-ai-composer{position:relative;z-index:5;padding-bottom:max(10px,env(safe-area-inset-bottom))}.cabi-ai-composer textarea{font-size:16px;line-height:1.4}.cabi-ai-composer__below{min-height:16px;margin-top:5px;font-size:8px}.cabi-ai-consent{align-items:flex-start}}' +
        '@media(max-width:420px){.cabi-ai-history-toggle,.cabi-ai-info,.cabi-ai-close{width:38px;height:38px;flex-basis:38px}.cabi-ai-composer__below{justify-content:flex-start}.cabi-ai-message--user{max-width:88%}}' +
        '@media(max-width:820px){.cabi-ai-sources-modal{align-items:end;padding:12px 8px max(8px,env(safe-area-inset-bottom))}.cabi-ai-sources-modal__surface{width:100%;max-height:min(78vh,640px);border-radius:23px 23px 17px 17px;transform:translateY(34px)}.cabi-ai-sources-modal__header{padding:17px 16px 13px}.cabi-ai-sources-modal__heading{gap:10px}.cabi-ai-sources-modal__heading>span{width:39px;height:39px;flex-basis:39px;border-radius:13px}.cabi-ai-sources-modal__heading h3{font-size:17px}.cabi-ai-sources-modal__intro{padding:13px 16px 5px}.cabi-ai-sources-modal__list{gap:7px;padding:7px 12px 11px}.cabi-ai-sources-modal__item{grid-template-columns:34px minmax(0,1fr) 32px;gap:9px;padding:9px;border-radius:13px}.cabi-ai-sources-modal__index{width:34px;height:34px;border-radius:10px}.cabi-ai-sources-modal__external{width:32px;height:32px}.cabi-ai-sources-modal__note{padding:10px 16px 14px}.cabi-ai-message__actions{gap:6px}.cabi-ai-message__actions a{max-width:100%;min-height:34px;padding:7px 9px;font-size:10px;line-height:1.25;overflow-wrap:anywhere}}' +
        '@media(max-width:820px){.cabi-ai-thread{gap:10px}.cabi-ai-message-row{gap:7px}.cabi-ai-message{padding:10px 12px 7px;border-radius:16px}.cabi-ai-message--assistant{max-width:calc(100% - 39px);border-radius:16px 16px 16px 5px}.cabi-ai-message--user{max-width:84%;padding:9px 12px 6px;border-radius:16px 16px 5px 16px}.cabi-ai-message p{font-size:12px;line-height:1.52}.cabi-ai-avatar{width:30px;height:30px;flex-basis:30px;border-radius:10px}.cabi-ai-avatar svg{width:21px;height:21px}.cabi-ai-message__actions a{min-height:30px;padding:6px 8px;font-size:9.5px}.cabi-ai-message__sources{min-height:24px}}' +
        '@media(max-width:420px){.cabi-ai-message--assistant{max-width:calc(100% - 36px)}.cabi-ai-message--user{max-width:88%}.cabi-ai-message-row{gap:6px}}' +
        '@media(prefers-reduced-motion:reduce){.cabi-smart-button,.cabi-ai-panel,.cabi-ai-panel__surface,.cabi-ai-start,.cabi-ai-thread,.cabi-ai-sidebar,.cabi-ai-sources-modal,.cabi-ai-sources-modal__surface,.cabi-ai-sources-modal__item{transition:none}.cabi-ai-electron{display:none}.cabi-ai-send__arrow,.cabi-ai-send__stop,.cabi-ai-info>span,.cabi-ai-orbit>.cabi-ai-atom,.cabi-ai-orbit::before,.cabi-ai-orbit>span,.cabi-ai-typing i,.cabi-ai-message--assistant.is-streaming-response>p:first-child::after,.cabi-ai-message--assistant.has-streamed-response>p+p,.cabi-ai-message--assistant.has-streamed-response>.cabi-ai-message__actions,.cabi-ai-message--assistant.has-streamed-response>.cabi-ai-message__meta{animation:none!important}}' +
        '@media print{.cabi-smart-button,.cabi-ai-sources-modal{display:none!important}}';
      doc.head.appendChild(styles);
    }

    var STORAGE_KEY = "cabit_ai_conversations_v1";
    var CONSENT_NOTICE_VERSION = "2026-08-21";
    var MAX_CONVERSATIONS = 40;
    var MAX_MESSAGES = 80;
    var CONTACT_DETAILS = {
      phoneLabel: "+40 771 532 949",
      phoneHref: "tel:+40771532949",
      email: "contact@cab-it.ro",
      emailHref: "mailto:contact@cab-it.ro",
      whatsappHref: "https://wa.me/40771532949?text=Bun%C4%83%2C%20a%C8%99%20dori%20mai%20multe%20detalii"
    };
    var launchers = Array.prototype.slice.call(doc.querySelectorAll("[data-cabi-smart-button]"));
    var closeButton = panel.querySelector("[data-cabi-close]");
    var infoButton = panel.querySelector("[data-cabi-info]");
    var infoPopover = panel.querySelector(".cabi-ai-info-popover");
    var historyButton = panel.querySelector("[data-cabi-history-toggle]");
    var sidebar = panel.querySelector(".cabi-ai-sidebar");
    var sidebarBackdrop = panel.querySelector("[data-cabi-sidebar-close]");
    var newChatButton = panel.querySelector("[data-cabi-new-chat]");
    var recents = panel.querySelector("[data-cabi-recents]");
    var canvas = panel.querySelector("[data-cabi-canvas]");
    var startScreen = panel.querySelector("[data-cabi-start]");
    var thread = panel.querySelector("[data-cabi-thread]");
    var composerForm = panel.querySelector("[data-cabi-composer]");
    var composerInput = composerForm && composerForm.querySelector("textarea");
    var composerButton = composerForm && composerForm.querySelector('button[type="submit"]');
    var composerStatus = panel.querySelector("[data-cabi-status]");
    var sourcesModal = panel.querySelector("[data-cabi-sources-modal]");
    var sourcesSurface = panel.querySelector(".cabi-ai-sources-modal__surface");
    var sourcesList = panel.querySelector("[data-cabi-sources-list]");
    var sourcesCloseButtons = Array.prototype.slice.call(panel.querySelectorAll("[data-cabi-sources-close]"));
    var quickActions = Array.prototype.slice.call(panel.querySelectorAll("[data-cabi-action]"));
    var servicesToggle = panel.querySelector("[data-cabi-services]");
    var servicesGroup = servicesToggle && servicesToggle.closest(".cabi-ai-nav__group");
    var servicesSubmenu = panel.querySelector("#cabi-ai-services-submenu");
    var lastLauncher = null;
    var panelTimer = null;
    var effectTimer = null;
    var bodyLockTimer = null;
    var composerAnimationTimer = null;
    var statusTimer = null;
    var sourcesModalTimer = null;
    var lastSourcesTrigger = null;
    var viewportFrame = null;
    var currentRequestId = 0;
    var pendingReplies = {};
    var enginePromise = null;
    var syncTimers = {};
    var syncInFlight = {};
    var syncAgain = {};

    function defaultChatState() {
      return {
        version: 1,
        activeConversationId: null,
        improvementConsent: false,
        conversations: [],
        pendingDeletes: []
      };
    }

    function safeText(value, limit) {
      var textValue = typeof value === "string" ? value : "";
      return typeof limit === "number" ? textValue.slice(0, limit) : textValue;
    }

    function normalizeStoredAction(action) {
      if (!action || typeof action !== "object") return null;
      var label = safeText(action.label, 80).trim();
      var href = safeText(action.href, 1200).trim();
      if (!label || !href) return null;
      return {
        label: label,
        href: href,
        kind: safeText(action.kind, 30).replace(/[^a-z0-9_-]/gi, "")
      };
    }

    function normalizeStoredSource(value, depth) {
      var level = typeof depth === "number" ? depth : 0;
      if (level > 4 || value === null || typeof value === "undefined") return "";
      if (typeof value === "string") return safeText(value, 1600).trim();
      if (Array.isArray(value)) {
        return value.slice(0, 12).map(function (item) {
          return normalizeStoredSource(item, level + 1);
        }).filter(function (item) {
          return typeof item === "string" ? item !== "" : Boolean(item);
        });
      }
      if (typeof value !== "object") return "";
      var normalized = {};
      var href = safeText(value.url || value.href || value.source_url || value.link, 1200).trim();
      var title = safeText(value.title || value.label || value.name, 180).trim();
      var domain = safeText(value.domain || value.host || value.publisher, 180).trim();
      if (href) normalized.url = href;
      if (title) normalized.title = title;
      if (domain) normalized.domain = domain;
      ["source", "sources", "references", "links", "retrieval_fragments", "fragments"].forEach(function (key) {
        if (typeof value[key] === "undefined") return;
        var nested = normalizeStoredSource(value[key], level + 1);
        if (typeof nested === "string" ? nested !== "" : Array.isArray(nested) ? nested.length : Boolean(nested)) normalized[key] = nested;
      });
      return Object.keys(normalized).length ? normalized : "";
    }

    function normalizeStoredMessage(message) {
      if (!message || typeof message !== "object") return null;
      var role = message.role === "assistant" ? "assistant" : message.role === "user" ? "user" : "";
      var textValue = safeText(message.text || message.content, 30000);
      if (!role || !textValue.trim()) return null;
      var sourceValue = normalizeStoredSource(message.source, 0);
      if (!sourceValue || (Array.isArray(sourceValue) && !sourceValue.length)) sourceValue = normalizeStoredSource(message.source_url, 0);
      return {
        id: safeText(message.id, 100) || uniqueId("msg"),
        role: role,
        text: textValue,
        followup: safeText(message.followup || message.follow_up, 8000),
        source: sourceValue,
        sources: normalizeStoredSource(message.sources, 0),
        context: normalizeStoredSource(message.context, 0),
        actions: Array.isArray(message.actions) ? message.actions.map(normalizeStoredAction).filter(Boolean).slice(0, 10) : [],
        intent: safeText(message.intent, 100),
        confidence: typeof message.confidence === "number" ? message.confidence : null,
        localModel: message.localModel === true || message.local_model === true,
        createdAt: safeText(message.createdAt || message.created_at, 50) || new Date().toISOString()
      };
    }

    function normalizeStoredConversation(conversation) {
      if (!conversation || typeof conversation !== "object") return null;
      var id = safeText(conversation.id, 120);
      if (!id) return null;
      var messages = Array.isArray(conversation.messages) ?
        conversation.messages.map(normalizeStoredMessage).filter(Boolean).slice(-MAX_MESSAGES) : [];
      return {
        id: id,
        title: safeText(conversation.title, 90).trim() || "Conversație nouă",
        createdAt: safeText(conversation.createdAt || conversation.created_at, 50) || new Date().toISOString(),
        updatedAt: safeText(conversation.updatedAt || conversation.updated_at, 50) || new Date().toISOString(),
        messages: messages,
        serverId: safeText(conversation.serverId || conversation.server_id, 180),
        deleteToken: safeText(conversation.deleteToken || conversation.delete_token, 300),
        revision: typeof conversation.revision === "number" ? conversation.revision : null,
        syncPending: conversation.syncPending === true
      };
    }

    function loadChatState() {
      var nextState = defaultChatState();
      try {
        var raw = window.localStorage.getItem(STORAGE_KEY);
        if (!raw) return nextState;
        var stored = JSON.parse(raw);
        if (!stored || typeof stored !== "object") return nextState;
        nextState.improvementConsent = stored.improvementConsent === true;
        nextState.conversations = Array.isArray(stored.conversations) ?
          stored.conversations.map(normalizeStoredConversation).filter(Boolean).slice(0, MAX_CONVERSATIONS) : [];
        nextState.pendingDeletes = Array.isArray(stored.pendingDeletes) ? stored.pendingDeletes.filter(function (item) {
          return item && safeText(item.serverId || item.server_id, 180) && safeText(item.deleteToken || item.delete_token, 300);
        }).map(function (item) {
          return {
            serverId: safeText(item.serverId || item.server_id, 180),
            deleteToken: safeText(item.deleteToken || item.delete_token, 300)
          };
        }).slice(0, 50) : [];
        var requestedActiveId = safeText(stored.activeConversationId, 120);
        nextState.activeConversationId = nextState.conversations.some(function (item) {
          return item.id === requestedActiveId;
        }) ? requestedActiveId : null;
      } catch (error) {
        return defaultChatState();
      }
      return nextState;
    }

    var chatState = loadChatState();

    function saveChatState() {
      chatState.conversations.sort(function (left, right) {
        return String(right.updatedAt).localeCompare(String(left.updatedAt));
      });
      if (chatState.conversations.length > MAX_CONVERSATIONS) {
        chatState.conversations = chatState.conversations.slice(0, MAX_CONVERSATIONS);
      }
      try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(chatState));
        return true;
      } catch (error) {
        return false;
      }
    }

    function uniqueId(prefix) {
      if (window.crypto && typeof window.crypto.randomUUID === "function") {
        return prefix + "-" + window.crypto.randomUUID();
      }
      return prefix + "-" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2, 10);
    }

    function findConversation(id) {
      for (var index = 0; index < chatState.conversations.length; index += 1) {
        if (chatState.conversations[index].id === id) return chatState.conversations[index];
      }
      return null;
    }

    function activeConversation() {
      return findConversation(chatState.activeConversationId);
    }

    function titleFromMessage(message) {
      var compact = String(message || "").replace(/\s+/g, " ").trim();
      if (compact.length <= 48) return compact || "Conversație nouă";
      return compact.slice(0, 47).replace(/\s+\S*$/, "") + "…";
    }

    function ensureActiveConversation(firstMessage) {
      var conversation = activeConversation();
      if (conversation) return conversation;
      var now = new Date().toISOString();
      conversation = {
        id: uniqueId("chat"),
        title: titleFromMessage(firstMessage),
        createdAt: now,
        updatedAt: now,
        messages: [],
        serverId: "",
        deleteToken: "",
        revision: null,
        syncPending: true
      };
      chatState.conversations.unshift(conversation);
      chatState.activeConversationId = conversation.id;
      return conversation;
    }

    function focusWithoutScroll(element) {
      if (!element) return;
      try {
        element.focus({ preventScroll: true });
      } catch (error) {
        element.focus();
      }
    }

    function formatMessageTime(value) {
      var date = new Date(value);
      if (Number.isNaN(date.getTime())) return "";
      try {
        return new Intl.DateTimeFormat("ro-RO", { hour: "2-digit", minute: "2-digit" }).format(date);
      } catch (error) {
        return date.toLocaleTimeString().slice(0, 5);
      }
    }

    function safeActionHref(value) {
      var href = safeText(value, 1200).trim();
      if (!href || /^\s*(?:javascript|data|vbscript):/i.test(href)) return "";
      if (href.charAt(0) === "/" && href.charAt(1) !== "/") {
        return siteRootPath + href.replace(/^\/+/, "");
      }
      if (/^(?:https?:|mailto:|tel:)/i.test(href)) return href;
      return "";
    }

    function sourceLabelFromHref(href) {
      try {
        var url = new URL(href, window.location.href);
        var segment = url.pathname.split("/").filter(Boolean).pop() || "CAB-IT Expert";
        segment = decodeURIComponent(segment).replace(/[-_]+/g, " ").replace(/\s+/g, " ").trim();
        return segment ? segment.charAt(0).toUpperCase() + segment.slice(1) : "CAB-IT Expert";
      } catch (error) {
        return "Sursă CAB-IT";
      }
    }

    function collectMessageSources(message) {
      var collected = [];
      var seen = {};
      function addSource(rawHref, rawTitle, rawDomain) {
        var href = safeActionHref(rawHref);
        if (!href || (!/^https?:/i.test(href) && href.charAt(0) !== "/")) return;
        var absoluteHref;
        var host = safeText(rawDomain, 180).trim();
        try {
          var parsed = new URL(href, window.location.href);
          absoluteHref = parsed.href;
          if (!host) host = parsed.hostname.replace(/^www\./i, "");
        } catch (error) {
          return;
        }
        var key = absoluteHref.replace(/#.*$/, "").replace(/\/$/, "").toLowerCase();
        if (seen[key]) return;
        seen[key] = true;
        var title = safeText(rawTitle, 180).trim();
        if (!title || /^(?:vezi|deschide|citește)(?:\s+(?:detaliile|sursa|articolul|pagina))?$/i.test(title)) title = sourceLabelFromHref(absoluteHref);
        collected.push({ href: absoluteHref, title: title, domain: host || "cab-it.ro" });
      }
      function visit(value, fallbackTitle, depth) {
        if (depth > 5 || value === null || typeof value === "undefined") return;
        if (typeof value === "string") {
          addSource(value, fallbackTitle, "");
          return;
        }
        if (Array.isArray(value)) {
          value.slice(0, 20).forEach(function (item) { visit(item, fallbackTitle, depth + 1); });
          return;
        }
        if (typeof value !== "object") return;
        var title = safeText(value.title || value.label || value.name, 180) || fallbackTitle;
        addSource(value.url || value.href || value.source_url || value.link, title, value.domain || value.host || value.publisher);
        ["source", "sources", "references", "links", "retrieval_fragments", "fragments"].forEach(function (key) {
          if (typeof value[key] !== "undefined") visit(value[key], title, depth + 1);
        });
      }
      if (!message || typeof message !== "object") return collected;
      visit(message.source, "", 0);
      visit(message.sources, "", 0);
      visit(message.context, "", 0);
      var actions = Array.isArray(message.actions) ? message.actions : [];
      actions.forEach(function (action) {
        var kind = safeText(action && (action.kind || action.type), 30).toLowerCase();
        if (kind === "source" || kind === "link") visit(action, safeText(action && action.label, 180), 0);
      });
      return collected.slice(0, 20);
    }

    function appendMessageActions(container, actions) {
      if (!Array.isArray(actions) || !actions.length) return;
      var actionsWrap = doc.createElement("div");
      actionsWrap.className = "cabi-ai-message__actions";
      actions.forEach(function (action) {
        var href = safeActionHref(action.href);
        if (!href) return;
        var actionLabel = safeText(action.label, 80);
        if (/^vezi detali(?:i|ile)$/i.test(actionLabel)) return;
        var link = doc.createElement("a");
        link.href = href;
        link.textContent = actionLabel;
        var kind = safeText(action.kind || action.type, 30).replace(/[^a-z0-9_-]/gi, "");
        if (kind) link.classList.add("is-" + kind);
        if (kind === "call" || kind === "phone") link.insertAdjacentHTML("afterbegin", routeIcons.contact);
        if (/^https?:/i.test(href)) {
          link.target = "_blank";
          link.rel = "noopener";
        }
        actionsWrap.appendChild(link);
      });
      if (actionsWrap.childNodes.length) container.appendChild(actionsWrap);
    }

    function sourcesModalIsOpen() {
      return Boolean(sourcesModal && !sourcesModal.hidden && sourcesModal.classList.contains("is-open"));
    }

    function renderSourcesList(sources) {
      if (!sourcesList) return;
      sourcesList.textContent = "";
      sources.forEach(function (item, index) {
        var link = doc.createElement("a");
        link.className = "cabi-ai-sources-modal__item";
        link.href = item.href;
        link.target = "_blank";
        link.rel = "noopener";
        link.setAttribute("aria-label", "Deschide sursa " + (index + 1) + ": " + item.title);
        var number = doc.createElement("span");
        number.className = "cabi-ai-sources-modal__index";
        number.textContent = String(index + 1).padStart(2, "0");
        var copy = doc.createElement("span");
        copy.className = "cabi-ai-sources-modal__copy";
        var title = doc.createElement("strong");
        title.textContent = item.title;
        var domain = doc.createElement("small");
        domain.textContent = item.domain;
        copy.appendChild(title);
        copy.appendChild(domain);
        var external = doc.createElement("span");
        external.className = "cabi-ai-sources-modal__external";
        external.setAttribute("aria-hidden", "true");
        external.innerHTML = '<svg viewBox="0 0 24 24"><path d="M14 5h5v5M19 5l-8 8"/><path d="M18 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>';
        link.appendChild(number);
        link.appendChild(copy);
        link.appendChild(external);
        sourcesList.appendChild(link);
      });
    }

    function openSourcesModal(sources, trigger) {
      if (!sourcesModal || !sourcesSurface || !sourcesList || !Array.isArray(sources) || !sources.length) return;
      window.clearTimeout(sourcesModalTimer);
      lastSourcesTrigger = trigger || doc.activeElement;
      renderSourcesList(sources);
      sourcesModal.hidden = false;
      sourcesModal.setAttribute("aria-hidden", "false");
      sourcesModal.offsetWidth;
      window.requestAnimationFrame(function () {
        sourcesModal.classList.add("is-open");
        var close = sourcesModal.querySelector(".cabi-ai-sources-modal__close");
        focusWithoutScroll(close || sourcesSurface);
      });
    }

    function closeSourcesModal(restoreFocus) {
      if (!sourcesModal || sourcesModal.hidden) return;
      window.clearTimeout(sourcesModalTimer);
      sourcesModal.classList.remove("is-open");
      sourcesModal.setAttribute("aria-hidden", "true");
      var reduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      sourcesModalTimer = window.setTimeout(function () {
        sourcesModal.hidden = true;
        if (sourcesList) sourcesList.textContent = "";
      }, reduced ? 0 : 220);
      if (restoreFocus !== false && lastSourcesTrigger && doc.contains(lastSourcesTrigger)) focusWithoutScroll(lastSourcesTrigger);
    }

    function trapSourcesModalFocus(event) {
      if (!sourcesModalIsOpen() || event.key !== "Tab") return;
      var focusable = Array.prototype.slice.call(sourcesModal.querySelectorAll('button:not([disabled]),a[href],input:not([disabled]),[tabindex]:not([tabindex="-1"])')).filter(function (element) {
        return !element.hidden && element.getAttribute("aria-hidden") !== "true" && element.getAttribute("tabindex") !== "-1";
      });
      if (!focusable.length) {
        event.preventDefault();
        focusWithoutScroll(sourcesSurface);
        return;
      }
      var first = focusable[0];
      var last = focusable[focusable.length - 1];
      if (event.shiftKey && doc.activeElement === first) {
        event.preventDefault();
        focusWithoutScroll(last);
      } else if (!event.shiftKey && doc.activeElement === last) {
        event.preventDefault();
        focusWithoutScroll(first);
      }
    }

    function animateAssistantText(textNode, fullText, assistantMessage) {
      var reducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      var characters = Array.from(fullText || "");
      if (!characters.length || reducedMotion) {
        textNode.textContent = fullText;
        return;
      }

      var duration = Math.min(950, Math.max(360, characters.length * 1.55));
      var startedAt = null;
      var lastCount = 0;
      var lastScrollAt = 0;
      assistantMessage.classList.add("is-streaming-response");
      assistantMessage.setAttribute("aria-busy", "true");
      textNode.setAttribute("aria-label", fullText);

      function reveal(timestamp) {
        if (!doc.contains(assistantMessage)) return;
        if (startedAt === null) startedAt = timestamp;
        var progress = Math.min(1, (timestamp - startedAt) / duration);
        var count = Math.max(1, Math.ceil(characters.length * progress));
        if (count !== lastCount) {
          textNode.textContent = characters.slice(0, count).join("");
          lastCount = count;
        }
        if (timestamp - lastScrollAt > 90) {
          scrollThreadToEnd(false);
          lastScrollAt = timestamp;
        }
        if (progress < 1) {
          window.requestAnimationFrame(reveal);
          return;
        }
        textNode.textContent = fullText;
        textNode.removeAttribute("aria-label");
        assistantMessage.classList.remove("is-streaming-response");
        assistantMessage.classList.add("has-streamed-response");
        assistantMessage.removeAttribute("aria-busy");
        window.setTimeout(function () {
          assistantMessage.classList.remove("has-streamed-response");
        }, 360);
        scrollThreadToEnd(false);
      }

      window.requestAnimationFrame(reveal);
    }

    function createMessageNode(message) {
      if (message.role === "user") {
        var userMessage = doc.createElement("article");
        userMessage.className = "cabi-ai-message cabi-ai-message--user";
        var userText = doc.createElement("p");
        userText.textContent = message.text;
        var userTime = doc.createElement("small");
        userTime.textContent = formatMessageTime(message.createdAt);
        userMessage.appendChild(userText);
        userMessage.appendChild(userTime);
        return userMessage;
      }

      var row = doc.createElement("div");
      row.className = "cabi-ai-message-row";
      var avatar = doc.createElement("span");
      avatar.className = "cabi-ai-avatar";
      avatar.setAttribute("aria-hidden", "true");
      avatar.innerHTML = icon;
      var assistantMessage = doc.createElement("article");
      assistantMessage.className = "cabi-ai-message cabi-ai-message--assistant";
      assistantMessage.dataset.localModel = message.localModel === true ? "local" : "canonical";
      if (message.localModelDevice) assistantMessage.dataset.localModelDevice = safeText(message.localModelDevice, 20);
      if (message.localModelReason) assistantMessage.dataset.localModelReason = safeText(message.localModelReason, 100);
      var assistantText = doc.createElement("p");
      var animateTyping = message.animateTyping === true;
      delete message.animateTyping;
      assistantText.textContent = animateTyping ? "" : message.text;
      assistantMessage.appendChild(assistantText);
      if (message.followup) {
        var followup = doc.createElement("p");
        followup.textContent = message.followup;
        assistantMessage.appendChild(followup);
      }
      var messageSources = collectMessageSources(message);
      appendMessageActions(assistantMessage, message.actions);
      var meta = doc.createElement("div");
      meta.className = "cabi-ai-message__meta";
      var source;
      if (messageSources.length) {
        source = doc.createElement("button");
        source.type = "button";
        source.className = "cabi-ai-message__sources";
        source.setAttribute("aria-label", "Vezi sursele folosite pentru răspuns");
        source.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A3.5 3.5 0 0 1 7.5 2H11a3 3 0 0 1 3 3v14a3 3 0 0 0-3-3H7.5A3.5 3.5 0 0 0 4 19.5z"/><path d="M20 5.5A3.5 3.5 0 0 0 16.5 2H14v17a3 3 0 0 1 3-3h-.5a3.5 3.5 0 0 1 3.5 3.5z"/></svg><span>Vezi sursele</span><b class="cabi-ai-message__sources-count" aria-hidden="true">' + messageSources.length + '</b>';
        source.addEventListener("click", function () { openSourcesModal(messageSources, source); });
      } else {
        source = doc.createElement("span");
        source.className = "cabi-ai-message__source";
        source.textContent = "CAB-IT AI";
      }
      var assistantTime = doc.createElement("span");
      assistantTime.textContent = formatMessageTime(message.createdAt);
      meta.appendChild(source);
      meta.appendChild(assistantTime);
      assistantMessage.appendChild(meta);
      row.appendChild(avatar);
      row.appendChild(assistantMessage);
      if (animateTyping) animateAssistantText(assistantText, message.text, assistantMessage);
      return row;
    }

    function setConversationView(hasConversation) {
      canvas.classList.toggle("has-conversation", hasConversation);
      startScreen.setAttribute("aria-hidden", String(hasConversation));
      thread.setAttribute("aria-hidden", String(!hasConversation));
    }

    function scrollThreadToEnd(smooth) {
      window.requestAnimationFrame(function () {
        var reducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        try {
          canvas.scrollTo({ top: canvas.scrollHeight, behavior: smooth && !reducedMotion ? "smooth" : "auto" });
        } catch (error) {
          canvas.scrollTop = canvas.scrollHeight;
        }
      });
    }

    function renderThread(smooth) {
      var conversation = activeConversation();
      thread.textContent = "";
      if (!conversation || !conversation.messages.length) {
        setConversationView(false);
        if (composerButton && composerButton.classList.contains("is-sending")) setComposerSending(false, true);
        thread.setAttribute("aria-busy", "false");
        canvas.scrollTop = 0;
        return;
      }
      conversation.messages.forEach(function (message) {
        thread.appendChild(createMessageNode(message));
      });
      if (pendingReplies[conversation.id]) thread.appendChild(createTypingIndicatorNode(conversation.id));
      var activeIsPending = Boolean(pendingReplies[conversation.id]);
      if (composerButton && composerButton.classList.contains("is-sending") !== activeIsPending) setComposerSending(activeIsPending, true);
      thread.setAttribute("aria-busy", String(activeIsPending));
      setConversationView(true);
      scrollThreadToEnd(smooth);
    }

    function renderRecents() {
      recents.textContent = "";
      if (!chatState.conversations.length) {
        var empty = doc.createElement("p");
        empty.className = "cabi-ai-empty";
        empty.textContent = "Nu există conversații încă.";
        recents.appendChild(empty);
        return;
      }
      chatState.conversations.forEach(function (conversation) {
        var row = doc.createElement("div");
        row.className = "cabi-ai-recent";
        row.classList.toggle("is-pending", Boolean(pendingReplies[conversation.id]));
        row.classList.toggle("is-current", conversation.id === chatState.activeConversationId);
        var openButton = doc.createElement("button");
        openButton.className = "cabi-ai-recent__open";
        openButton.type = "button";
        openButton.setAttribute("data-cabi-open-conversation", conversation.id);
        openButton.setAttribute("aria-label", "Deschide conversația " + conversation.title);
        if (conversation.id === chatState.activeConversationId) openButton.setAttribute("aria-current", "true");
        var recentIcon = doc.createElement("span");
        recentIcon.className = "cabi-ai-recent__icon";
        recentIcon.setAttribute("aria-hidden", "true");
        recentIcon.textContent = pendingReplies[conversation.id] ? "…" : "✦";
        if (pendingReplies[conversation.id]) recentIcon.setAttribute("title", "Răspuns în curs");
        var recentTitle = doc.createElement("span");
        recentTitle.className = "cabi-ai-recent__title";
        recentTitle.textContent = conversation.title;
        openButton.appendChild(recentIcon);
        openButton.appendChild(recentTitle);
        var deleteButton = doc.createElement("button");
        deleteButton.className = "cabi-ai-recent__delete";
        deleteButton.type = "button";
        deleteButton.setAttribute("data-cabi-delete-conversation", conversation.id);
        deleteButton.setAttribute("aria-label", "Șterge conversația " + conversation.title);
        deleteButton.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg>';
        row.appendChild(openButton);
        row.appendChild(deleteButton);
        recents.appendChild(row);
      });
    }

    function renderAll(smooth) {
      renderRecents();
      renderThread(smooth);
    }

    function setComposerStatus(message, error) {
      window.clearTimeout(statusTimer);
      composerStatus.textContent = message || "";
      composerStatus.hidden = !message;
      composerStatus.classList.toggle("is-error", Boolean(error));
      if (message) {
        statusTimer = window.setTimeout(function () {
          composerStatus.hidden = true;
          composerStatus.textContent = "";
          composerStatus.classList.remove("is-error");
        }, 5200);
      }
    }

    function resizeComposer() {
      if (!composerInput) return;
      composerInput.style.height = "38px";
      composerInput.style.height = Math.min(116, Math.max(38, composerInput.scrollHeight)) + "px";
    }

    function updateComposerButton() {
      if (!composerButton || !composerInput) return;
      composerButton.disabled = !composerButton.classList.contains("is-sending") && !composerInput.value.trim();
    }

    function setComposerSending(sending, immediate) {
      if (!composerButton) return;
      window.clearTimeout(composerAnimationTimer);
      var wasSending = composerButton.classList.contains("is-sending");
      composerButton.classList.remove("is-returning");
      if (sending) {
        if (!wasSending) {
          composerButton.classList.remove("is-sending");
          composerButton.offsetWidth;
          composerButton.classList.add("is-sending");
        }
      } else if (wasSending && !immediate) {
        composerButton.classList.remove("is-sending");
        composerButton.classList.add("is-returning");
        composerAnimationTimer = window.setTimeout(function () {
          composerButton.classList.remove("is-returning");
          updateComposerButton();
        }, 680);
      } else {
        composerButton.classList.remove("is-sending", "is-returning");
      }
      composerButton.setAttribute("aria-pressed", String(sending));
      composerButton.setAttribute("aria-label", sending ? "Oprește răspunsul" : "Trimite mesajul");
      thread.setAttribute("aria-busy", String(sending));
      updateComposerButton();
    }

    function createTypingIndicatorNode(conversationId) {
      var row = doc.createElement("div");
      row.className = "cabi-ai-message-row cabi-ai-typing-row";
      row.setAttribute("data-cabi-typing", conversationId || "");
      var avatar = doc.createElement("span");
      avatar.className = "cabi-ai-avatar";
      avatar.setAttribute("aria-hidden", "true");
      avatar.innerHTML = icon;
      var bubble = doc.createElement("div");
      bubble.className = "cabi-ai-message cabi-ai-message--assistant cabi-ai-typing";
      bubble.setAttribute("aria-label", "Asistentul pregătește răspunsul");
      var pendingStage = pendingReplies[conversationId] && pendingReplies[conversationId].stage;
      var stageLabel = pendingStage === "loading" || pendingStage === "fallback"
        ? "Finalizez răspunsul..."
        : "Înțeleg ce spui...";
      bubble.innerHTML = '<span class="cabi-ai-typing__label"></span><i></i><i></i><i></i>';
      bubble.querySelector(".cabi-ai-typing__label").textContent = stageLabel;
      row.appendChild(avatar);
      row.appendChild(bubble);
      return row;
    }

    function typingIndicatorForConversation(conversationId) {
      var indicators = Array.prototype.slice.call(thread.querySelectorAll("[data-cabi-typing]"));
      return indicators.find(function (indicator) {
        return indicator.getAttribute("data-cabi-typing") === conversationId;
      }) || null;
    }

    function showTypingIndicator(conversationId) {
      if (!conversationId || chatState.activeConversationId !== conversationId || typingIndicatorForConversation(conversationId)) return;
      thread.appendChild(createTypingIndicatorNode(conversationId));
      scrollThreadToEnd(true);
    }

    function hideTypingIndicator(conversationId) {
      var typing = conversationId ? typingIndicatorForConversation(conversationId) : thread.querySelector("[data-cabi-typing]");
      if (typing) typing.remove();
    }

    function setTypingIndicatorLabel(conversationId, label) {
      var typing = typingIndicatorForConversation(conversationId);
      var target = typing && typing.querySelector(".cabi-ai-typing__label");
      if (target) target.textContent = safeText(label, 80) || "Înțeleg ce spui...";
    }

    function normalizeReply(rawReply) {
      var payload = rawReply && rawReply.reply ? rawReply.reply : rawReply;
      if (typeof payload === "string") payload = { text: payload };
      if (!payload || typeof payload !== "object") return null;
      var textValue = safeText(payload.text || payload.content, 30000).trim();
      if (!textValue) return null;
      var sourceValue = normalizeStoredSource(payload.source, 0);
      if (!sourceValue || (Array.isArray(sourceValue) && !sourceValue.length)) sourceValue = normalizeStoredSource(payload.source_url, 0);
      return {
        id: uniqueId("msg"),
        role: "assistant",
        text: textValue,
        followup: safeText(payload.followup || payload.follow_up, 8000),
        source: sourceValue,
        sources: normalizeStoredSource(payload.sources, 0),
        context: normalizeStoredSource(payload.context, 0),
        actions: Array.isArray(payload.actions) ? payload.actions.map(normalizeStoredAction).filter(Boolean).slice(0, 10) : [],
        intent: safeText(payload.intent, 100),
        confidence: typeof payload.confidence === "number" ? payload.confidence : null,
        localModel: payload.localModel === true || payload.local_model === true,
        localModelDevice: safeText(payload.localModelDevice || payload.local_model_device, 20),
        localModelReason: safeText(payload.reason || payload.localModelReason || payload.local_model_reason, 100),
        createdAt: new Date().toISOString()
      };
    }

    function contactActions() {
      return [
        { label: "Trimite email", href: CONTACT_DETAILS.emailHref, kind: "email" },
        { label: "Sună acum", href: CONTACT_DETAILS.phoneHref, kind: "phone" },
        { label: "Scrie pe WhatsApp", href: CONTACT_DETAILS.whatsappHref, kind: "whatsapp" }
      ];
    }

    function quickReplyForIntent(intentHint) {
      var replies = {
        website_general: {
          text: "Sigur. CAB-IT poate realiza un site de prezentare, un magazin online sau o soluție custom, ales după obiectiv, buget, administrare și integrări. Proiectul este gândit mobile-first și orientat spre acțiuni reale: apeluri, WhatsApp, formulare sau vânzări.",
          followup: "Ce tip de afacere ai și ce vrei să facă website-ul?",
          source: "https://cab-it.ro/servicii/creare-site-web/",
          actions: [{ label: "Vezi serviciul", href: "/servicii/creare-site-web/", kind: "link" }],
          intent: "website_general"
        },
        website_price: {
          text: "Prețurile publice CAB-IT pentru crearea website-urilor sunt:\n\n• Website de prezentare: de la 999 lei, fără TVA — până la 5 pagini, design responsive, structură SEO de bază, formular și administrare.\n• Magazin online cu mai puțin de 100 de produse: de la 1.799 lei, fără TVA.\n• Magazin online cu 100–500 de produse: de la 2.399 lei, fără TVA.\n• Magazin online cu peste 500 de produse: de la 3.199 lei, fără TVA.\n• Website sau aplicație custom: ofertă după funcționalități și integrări.\n\nAcestea sunt praguri de pornire; conținutul, importurile, variantele și complexitatea pot modifica oferta finală.",
          followup: "Ai nevoie de un site de prezentare sau de un magazin online și, dacă este magazin, câte produse va avea?",
          source: "https://cab-it.ro/preturi/",
          actions: [{ label: "Vezi prețurile", href: "/preturi/", kind: "link" }],
          intent: "website_price"
        },
        seo_general: {
          text: "Da. Începem cu un diagnostic, nu cu promisiuni: verificăm accesarea și indexarea, structura, paginile și intențiile, conținutul, linkurile interne, performanța și datele din Search Console și Analytics. Apoi prioritizăm ce poate aduce vizibilitate și conversii reale.",
          followup: "Ai deja un website? Dacă da, trimite-mi domeniul.",
          source: "https://cab-it.ro/servicii/seo/",
          actions: [{ label: "Vezi serviciul SEO", href: "/servicii/seo/", kind: "link" }],
          intent: "seo_general"
        },
        ads_general: {
          text: "Sigur. Pentru Google Ads, CAB-IT aliniază termenii de căutare, anunțurile, pagina de destinație, trackingul și conversia urmărită. Înainte de buget stabilim rezultatul dorit, pentru ca o campanie să urmărească leaduri sau vânzări, nu doar clickuri.",
          followup: "Ce vrei să obții din reclame și ce serviciu sau produs promovezi?",
          source: "https://cab-it.ro/servicii/reclame-platite/",
          actions: [{ label: "Vezi reclamele plătite", href: "/servicii/reclame-platite/", kind: "link" }],
          intent: "ads_general"
        },
        ads_price: {
          text: "Administrarea campaniilor Google Ads pornește de la 649 lei pe lună, fără TVA. Bugetul plătit către Google și eventualele servicii terțe sunt separate. Oferta finală depinde de numărul campaniilor, piețele vizate, tracking și complexitatea contului.",
          followup: "Ce serviciu sau produs vrei să promovezi și în ce localitate?",
          source: "https://cab-it.ro/preturi/",
          actions: [{ label: "Vezi prețurile", href: "/preturi/", kind: "link" }],
          intent: "ads_price"
        },
        website_portfolio: {
          text: "CAB-IT are proiecte publice de e-commerce, website-uri de prezentare, SEO și promovare digitală. Exemplele includ IFY.ro, Maison Bébé, Auto La Domiciliu, Nanu Events, Traffic Pub, Best TKD, Lael Fashion și Bilka Sistem.",
          followup: "Vrei proiecte e-commerce, website-uri de prezentare sau proiecte cu SEO și promovare?",
          source: "https://cab-it.ro/portofoliu/",
          actions: [{ label: "Vezi portofoliul", href: "/portofoliu/", kind: "link" }],
          intent: "website_portfolio"
        },
        contact: {
          text: "Sigur. Poți discuta direct cu CAB-IT prin telefon, WhatsApp sau email. Pentru o ofertă cât mai precisă, descrie pe scurt proiectul și rezultatul pe care îl urmărești.",
          followup: "Telefon: " + CONTACT_DETAILS.phoneLabel + "\nEmail: " + CONTACT_DETAILS.email,
          source: "https://cab-it.ro/contact/",
          actions: contactActions(),
          intent: "contact"
        }
      };
      var selected = replies[intentHint];
      if (!selected) return null;
      return {
        id: uniqueId("msg"),
        role: "assistant",
        text: selected.text,
        followup: selected.followup,
        source: selected.source,
        sources: [],
        context: "",
        actions: selected.actions,
        intent: selected.intent,
        confidence: 1,
        localModel: false,
        createdAt: new Date().toISOString()
      };
    }

    function normalizeQuickMessage(value) {
      var normalized = safeText(value, 2000).toLowerCase();
      if (typeof normalized.normalize === "function") {
        normalized = normalized.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
      }
      return normalized.replace(/[^a-z0-9]+/g, " ").replace(/\s+/g, " ").trim();
    }

    function detectInstantIntent(messageText) {
      var normalized = normalizeQuickMessage(messageText);
      if (!normalized) return "";
      var asksPrice = /\b(?:pret|pretul|preturi|preturile|cost|costul|costuri|costurile|tarif|tariful|tarife|tarifele|buget|oferta|oferte)\b/.test(normalized)
        || /\bcat (?:costa|este|e)\b/.test(normalized);
      var mentionsWebsite = /\b(?:site|siteuri|website|websiteuri|web|magazin online|magazine online|ecommerce)\b/.test(normalized);
      var mentionsAds = /\b(?:google ads|reclame|campanie|campanii|promovare platita|advertising)\b/.test(normalized);
      if (asksPrice && mentionsWebsite) return "website_price";
      if (asksPrice && mentionsAds) return "ads_price";
      if (/\b(?:vreau sa va contactez|vreau sa te contactez|vreau sa sun|suna acum|numar de telefon|telefon|whatsapp|contact)\b/.test(normalized)) return "contact";
      return "";
    }

    function ensureContactReply(reply) {
      if (!reply) return null;
      reply.followup = "Telefon: " + CONTACT_DETAILS.phoneLabel + "\nEmail: " + CONTACT_DETAILS.email;
      var actions = Array.isArray(reply.actions) ? reply.actions.slice() : [];
      contactActions().forEach(function (requiredAction) {
        var exists = actions.some(function (action) {
          return action.kind === requiredAction.kind || action.href === requiredAction.href;
        });
        if (!exists) actions.push(requiredAction);
      });
      reply.actions = actions.slice(0, 6);
      return reply;
    }

    function fallbackReply(intentHint) {
      if (intentHint === "contact") {
        return {
          id: uniqueId("msg"),
          role: "assistant",
          text: "Sigur. Ne poți contacta direct prin email, telefon sau WhatsApp.",
          followup: "Telefon: " + CONTACT_DETAILS.phoneLabel + "\nEmail: " + CONTACT_DETAILS.email,
          source: "CAB-IT",
          actions: contactActions(),
          intent: "contact",
          confidence: 1,
          createdAt: new Date().toISOString()
        };
      }
      return {
        id: uniqueId("msg"),
        role: "assistant",
        text: "Nu sunt suficient de sigur că am înțeles corect și nu vreau să-ți dau un răspuns la întâmplare. Poți reformula în câteva cuvinte sau poți discuta acum cu un specialist CAB-IT.",
        followup: "Spune-mi ce vrei să obții sau sună-ne direct și preluăm noi discuția.",
        source: "CAB-IT",
        actions: [{ label: "Sună acum", href: CONTACT_DETAILS.phoneHref, kind: "phone" }],
        intent: "specialist_handoff_unclear",
        confidence: null,
        createdAt: new Date().toISOString()
      };
    }

    function replyWithDeadline(replyPromise, timeoutMs, abortController) {
      return new Promise(function (resolve, reject) {
        var settled = false;
        var timer = window.setTimeout(function () {
          if (settled) return;
          settled = true;
          var timeoutError = new Error("Răspunsul a depășit timpul disponibil.");
          timeoutError.name = "AssistantTimeoutError";
          reject(timeoutError);
          if (abortController) {
            try {
              abortController.abort();
            } catch (error) {}
          }
        }, timeoutMs);
        Promise.resolve(replyPromise).then(function (result) {
          if (settled) return;
          settled = true;
          window.clearTimeout(timer);
          resolve(result);
        }, function (error) {
          if (settled) return;
          settled = true;
          window.clearTimeout(timer);
          reject(error);
        });
      });
    }

    function loadAssistantEngine() {
      if (window.CabitAssistantEngine) return Promise.resolve(window.CabitAssistantEngine);
      if (enginePromise) return enginePromise;
      enginePromise = new Promise(function (resolve, reject) {
        var existing = doc.querySelector('script[data-cabit-assistant-engine],script[src*="cabit-assistant-engine"]');
        var script = existing || doc.createElement("script");
        var settled = false;
        var timeout = window.setTimeout(function () {
          if (settled) return;
          settled = true;
          enginePromise = null;
          reject(new Error("Asistentul nu a putut fi inițializat la timp."));
        }, 20000);
        function finish() {
          if (settled) return;
          settled = true;
          window.clearTimeout(timeout);
          if (window.CabitAssistantEngine) {
            resolve(window.CabitAssistantEngine);
          } else {
            enginePromise = null;
            reject(new Error("Motorul CAB-IT AI nu este disponibil."));
          }
        }
        function fail() {
          if (settled) return;
          settled = true;
          window.clearTimeout(timeout);
          enginePromise = null;
          reject(new Error("Motorul CAB-IT AI nu a putut fi încărcat."));
        }
        script.addEventListener("load", finish, { once: true });
        script.addEventListener("error", fail, { once: true });
        if (!existing) {
          script.src = new URL("assets/js/cabit-assistant-engine.min.js?v=20260821-26", siteRootUrl).href;
          script.async = true;
          script.setAttribute("data-cabit-assistant-engine", "");
          doc.head.appendChild(script);
        } else if (window.CabitAssistantEngine) {
          finish();
        }
      });
      return enginePromise;
    }

    function updateConversationFromSync(localConversation, result) {
      var payload = result && result.conversation ? result.conversation : result;
      if (!payload || typeof payload !== "object") return;
      localConversation.serverId = safeText((result && (result.serverId || result.server_id)) || payload.serverId || payload.server_id || payload.id, 180) || localConversation.serverId;
      localConversation.deleteToken = safeText((result && (result.deleteToken || result.delete_token)) || payload.deleteToken || payload.delete_token, 300) || localConversation.deleteToken;
      if (result && typeof result.revision === "number") {
        localConversation.revision = result.revision;
      } else if (typeof payload.revision === "number") {
        localConversation.revision = payload.revision;
      }
    }

    function syncConversationNow(conversationId) {
      var conversation = findConversation(conversationId);
      if (!conversation || !conversation.messages.length) return;
      if (syncInFlight[conversationId]) {
        syncAgain[conversationId] = true;
        return;
      }
      syncInFlight[conversationId] = true;
      var payload = JSON.parse(JSON.stringify(conversation));
      payload.improvementConsent = chatState.improvementConsent;
      payload.consent = { improvement: chatState.improvementConsent, notice_version: CONSENT_NOTICE_VERSION };
      loadAssistantEngine().then(function (engine) {
        if (!engine || typeof engine.syncConversation !== "function") throw new Error("Sincronizarea nu este disponibilă.");
        return engine.syncConversation(payload);
      }).then(function (result) {
        var current = findConversation(conversationId);
        if (!current) {
          var deletedPayload = result && result.conversation ? result.conversation : result;
          var serverId = safeText((result && (result.serverId || result.server_id)) || (deletedPayload && (deletedPayload.serverId || deletedPayload.server_id || deletedPayload.id)), 180);
          var deleteToken = safeText((result && (result.deleteToken || result.delete_token)) || (deletedPayload && (deletedPayload.deleteToken || deletedPayload.delete_token)), 300);
          if (serverId && deleteToken) {
            chatState.pendingDeletes.push({ serverId: serverId, deleteToken: deleteToken });
            saveChatState();
            flushPendingDeletes();
          }
          completeConversationSync(conversationId);
          return;
        }
        updateConversationFromSync(current, result);
        current.syncPending = false;
        saveChatState();
        completeConversationSync(conversationId);
      }).catch(function () {
        var current = findConversation(conversationId);
        if (current) {
          current.syncPending = true;
          saveChatState();
        }
        completeConversationSync(conversationId);
        setComposerStatus("Sincronizarea pe server va fi reîncercată automat.", false);
      });
    }

    function completeConversationSync(conversationId) {
      delete syncInFlight[conversationId];
      if (!syncAgain[conversationId]) return;
      delete syncAgain[conversationId];
      if (findConversation(conversationId)) syncConversationNow(conversationId);
    }

    function scheduleConversationSync(conversation) {
      if (!conversation) return;
      conversation.syncPending = true;
      saveChatState();
      if (syncInFlight[conversation.id]) {
        syncAgain[conversation.id] = true;
        return;
      }
      window.clearTimeout(syncTimers[conversation.id]);
      syncTimers[conversation.id] = window.setTimeout(function () {
        delete syncTimers[conversation.id];
        syncConversationNow(conversation.id);
      }, 450);
    }

    function flushPendingDeletes() {
      if (!chatState.pendingDeletes.length) return;
      var queued = chatState.pendingDeletes.slice();
      loadAssistantEngine().then(function (engine) {
        if (!engine || typeof engine.deleteConversation !== "function") throw new Error("Ștergerea nu este disponibilă.");
        return Promise.all(queued.map(function (item) {
          return Promise.resolve(engine.deleteConversation(item.serverId, item.deleteToken)).then(function () {
            return item.serverId;
          });
        }));
      }).then(function (deletedIds) {
        chatState.pendingDeletes = chatState.pendingDeletes.filter(function (item) {
          return deletedIds.indexOf(item.serverId) === -1;
        });
        saveChatState();
      }).catch(function () {
        setComposerStatus("Conversația a fost ștearsă local; ștergerea de pe server va fi reîncercată.", true);
      });
    }

    function cancelAssistantReply(silent, conversationId) {
      var targetId = conversationId || chatState.activeConversationId;
      var pending = targetId ? pendingReplies[targetId] : null;
      if (pending && pending.abortController) {
        try {
          pending.abortController.abort();
        } catch (error) {}
      }
      if (pending && pending.finalizeTimer) window.clearTimeout(pending.finalizeTimer);
      if (targetId) delete pendingReplies[targetId];
      if (targetId === chatState.activeConversationId) {
        hideTypingIndicator(targetId);
        setComposerSending(false);
      }
      renderRecents();
      if (!silent && pending) setComposerStatus("Generarea răspunsului a fost oprită.", false);
    }

    function finishAssistantReply(conversationId, reply, requestId) {
      var pending = pendingReplies[conversationId];
      if (!reply || !pending || requestId !== pending.requestId) return;
      var conversation = findConversation(conversationId);
      if (pending.finalizeTimer) window.clearTimeout(pending.finalizeTimer);
      delete pendingReplies[conversationId];
      if (!conversation) return;
      if (chatState.activeConversationId === conversationId) hideTypingIndicator(conversationId);
      conversation.messages.push(reply);
      conversation.messages = conversation.messages.slice(-MAX_MESSAGES);
      conversation.updatedAt = new Date().toISOString();
      if (chatState.activeConversationId === conversationId) {
        setComposerSending(false);
        setComposerStatus("", false);
      }
      saveChatState();
      if (chatState.activeConversationId === conversationId) {
        reply.animateTyping = true;
        renderAll(true);
      } else {
        renderRecents();
      }
      scheduleConversationSync(conversation);
    }

    function applyLocalEnhancement(conversationId, messageId, rawReply) {
      var conversation = findConversation(conversationId);
      if (!conversation || !messageId) return;
      var enhanced = normalizeReply(rawReply);
      if (!enhanced) return;
      var message = conversation.messages.find(function (item) { return item.id === messageId; });
      if (!message || message.role !== "assistant") return;
      message.text = enhanced.text;
      message.followup = enhanced.followup || message.followup;
      message.source = enhanced.source || message.source;
      message.sources = enhanced.sources || message.sources;
      message.context = enhanced.context || message.context;
      message.actions = enhanced.actions.length ? enhanced.actions : message.actions;
      message.intent = enhanced.intent || message.intent;
      message.localModel = enhanced.localModel === true;
      message.localModelDevice = enhanced.localModelDevice || message.localModelDevice || "";
      message.localModelReason = enhanced.localModelReason || message.localModelReason || "";
      conversation.updatedAt = new Date().toISOString();
      saveChatState();
      if (chatState.activeConversationId === conversationId) renderAll(true);
      scheduleConversationSync(conversation);
      if (chatState.activeConversationId === conversationId) setComposerStatus("Răspuns completat pe baza informațiilor CAB-IT.", false);
    }

    function submitMessage(rawMessage, intentHint) {
      var messageText = safeText(rawMessage, 2000).trim();
      if (!messageText) return;
      if (!intentHint) {
        var instantIntent = detectInstantIntent(messageText);
        if (instantIntent) {
          submitQuickAction(messageText, instantIntent);
          return;
        }
      }
      var conversation = ensureActiveConversation(messageText);
      if (pendingReplies[conversation.id]) return;
      var now = new Date().toISOString();
      conversation.messages.push({
        id: uniqueId("msg"),
        role: "user",
        text: messageText,
        followup: "",
        source: "",
        actions: [],
        intent: safeText(intentHint, 100),
        confidence: null,
        createdAt: now
      });
      conversation.messages = conversation.messages.slice(-MAX_MESSAGES);
      conversation.updatedAt = now;
      if (conversation.messages.length === 1) conversation.title = titleFromMessage(messageText);
      currentRequestId += 1;
      var requestId = currentRequestId;
      var abortController = typeof window.AbortController === "function" ? new window.AbortController() : null;
      var understandingStartedAt = Date.now();
      var minimumVisibleThinkingMs = intentHint ? 0 : 450;
      pendingReplies[conversation.id] = {
        requestId: requestId,
        abortController: abortController,
        assistantMessageId: "",
        stage: "routing",
        understandingStartedAt: understandingStartedAt,
        minimumVisibleThinkingMs: minimumVisibleThinkingMs,
        finalizeTimer: null
      };
      composerInput.value = "";
      resizeComposer();
      saveChatState();
      renderAll(true);
      scheduleConversationSync(conversation);
      showTypingIndicator(conversation.id);
      var assistantMessageId = "";
      var history = conversation.messages.slice(0, -1).slice(-MAX_MESSAGES).map(function (item) {
        var content = item.text + (item.role === "assistant" && item.followup ? "\n" + item.followup : "");
        return { role: item.role, content: content };
      });
      var options = {
        intentHint: intentHint || undefined,
        signal: abortController ? abortController.signal : undefined,
        improvementConsent: chatState.improvementConsent,
        history: history,
        onLocalEnhancementState: function (state) {
          var pending = pendingReplies[conversation.id];
          if (pending) pending.stage = state;
          if (chatState.activeConversationId !== conversation.id) return;
          if (state === "understanding") {
            setTypingIndicatorLabel(conversation.id, "Înțeleg ce spui...");
            setComposerStatus("Înțeleg întrebarea folosind întreaga conversație.", false);
          } else if (state === "loading") {
            var elapsed = pending ? Date.now() - pending.understandingStartedAt : 700;
            window.setTimeout(function () {
              if (!pendingReplies[conversation.id] || pendingReplies[conversation.id].requestId !== requestId) return;
              setTypingIndicatorLabel(conversation.id, "Finalizez răspunsul...");
              setComposerStatus("Finalizez răspunsul pe baza conversației și a informațiilor CAB-IT.", false);
            }, Math.max(0, 700 - elapsed));
          } else if (state === "fallback") {
            setTypingIndicatorLabel(conversation.id, "Finalizez răspunsul...");
          } else if (state === "complete" || state === "unavailable") {
            setComposerStatus("", false);
          }
        }
      };
      loadAssistantEngine().then(function (engine) {
        if (!engine || typeof engine.reply !== "function") throw new Error("Răspunsul automat nu este disponibil.");
        return replyWithDeadline(engine.reply(messageText, options), 45000, abortController);
      }).then(function (result) {
        var reply = normalizeReply(result);
        if (!reply) throw new Error("Răspuns invalid.");
        if (intentHint === "contact") reply = ensureContactReply(reply);
        assistantMessageId = reply.id;
        if (pendingReplies[conversation.id] && pendingReplies[conversation.id].requestId === requestId) pendingReplies[conversation.id].assistantMessageId = assistantMessageId;
        var pending = pendingReplies[conversation.id];
        var elapsed = pending ? Date.now() - pending.understandingStartedAt : minimumVisibleThinkingMs;
        window.setTimeout(function () {
          finishAssistantReply(conversation.id, reply, requestId);
        }, Math.max(0, minimumVisibleThinkingMs - elapsed));
      }).catch(function (error) {
        var pending = pendingReplies[conversation.id];
        if (!pending || requestId !== pending.requestId || (error && error.name === "AbortError")) return;
        var elapsed = Date.now() - pending.understandingStartedAt;
        window.setTimeout(function () {
          finishAssistantReply(conversation.id, fallbackReply(intentHint), requestId);
        }, Math.max(0, pending.minimumVisibleThinkingMs - elapsed));
      });
    }

    function submitQuickAction(rawMessage, intentHint) {
      var messageText = safeText(rawMessage, 2000).trim();
      var immediateReply = quickReplyForIntent(intentHint);
      if (!messageText || !immediateReply) {
        submitMessage(rawMessage, intentHint);
        return;
      }
      var conversation = ensureActiveConversation(messageText);
      var wasEmpty = conversation.messages.length === 0;
      var now = new Date().toISOString();
      conversation.messages.push({
        id: uniqueId("msg"),
        role: "user",
        text: messageText,
        followup: "",
        source: "",
        actions: [],
        intent: safeText(intentHint, 100),
        confidence: null,
        createdAt: now
      });
      conversation.messages.push(immediateReply);
      conversation.messages = conversation.messages.slice(-MAX_MESSAGES);
      conversation.updatedAt = now;
      if (wasEmpty) conversation.title = titleFromMessage(messageText);
      composerInput.value = "";
      resizeComposer();
      saveChatState();
      immediateReply.animateTyping = true;
      renderAll(true);
      scheduleConversationSync(conversation);
    }

    function startNewConversation() {
      chatState.activeConversationId = null;
      composerInput.value = "";
      resizeComposer();
      saveChatState();
      renderAll(false);
      setMobileSidebar(false);
      setComposerStatus("", false);
      focusWithoutScroll(composerInput);
    }

    function selectConversation(id) {
      if (!findConversation(id)) return;
      chatState.activeConversationId = id;
      saveChatState();
      renderAll(false);
      setMobileSidebar(false);
      focusWithoutScroll(composerInput);
    }

    function deleteConversation(id) {
      var conversation = findConversation(id);
      if (!conversation) return;
      window.clearTimeout(syncTimers[id]);
      delete syncTimers[id];
      delete syncAgain[id];
      cancelAssistantReply(true, id);
      if (chatState.activeConversationId === id) {
        chatState.activeConversationId = null;
      }
      chatState.conversations = chatState.conversations.filter(function (item) {
        return item.id !== id;
      });
      if (conversation.serverId && conversation.deleteToken) {
        chatState.pendingDeletes.push({
          serverId: conversation.serverId,
          deleteToken: conversation.deleteToken
        });
      }
      saveChatState();
      renderAll(false);
      if (conversation.serverId && conversation.deleteToken) flushPendingDeletes();
      setComposerStatus("Conversația a fost ștearsă.", false);
    }

    function setServicesMenu(open) {
      if (!servicesToggle || !servicesGroup || !servicesSubmenu) return;
      servicesToggle.setAttribute("aria-expanded", String(open));
      servicesSubmenu.setAttribute("aria-hidden", String(!open));
      servicesGroup.classList.toggle("is-open", open);
    }

    function setInfoTooltip(open) {
      if (!infoButton || !infoPopover) return;
      infoButton.setAttribute("aria-expanded", String(open));
      infoPopover.setAttribute("aria-hidden", String(!open));
      infoPopover.classList.toggle("is-open", open);
    }

    function setMobileSidebar(open) {
      if (!sidebar || !historyButton || !sidebarBackdrop) return;
      if (open) setInfoTooltip(false);
      sidebar.classList.toggle("is-mobile-open", open);
      historyButton.setAttribute("aria-expanded", String(open));
      historyButton.setAttribute("aria-label", open ? "Închide istoricul conversațiilor" : "Deschide istoricul conversațiilor");
      sidebarBackdrop.hidden = !open;
    }

    function applyVisualViewport() {
      viewportFrame = null;
      var visualViewport = window.visualViewport;
      var height = visualViewport ? visualViewport.height : window.innerHeight;
      var width = visualViewport ? visualViewport.width : window.innerWidth;
      var offsetTop = visualViewport ? visualViewport.offsetTop : 0;
      var offsetLeft = visualViewport ? visualViewport.offsetLeft : 0;
      panel.style.setProperty("--cabi-vv-height", Math.round(height) + "px");
      panel.style.setProperty("--cabi-vv-width", Math.round(width) + "px");
      panel.style.setProperty("--cabi-vv-top", Math.round(offsetTop) + "px");
      panel.style.setProperty("--cabi-vv-left", Math.round(offsetLeft) + "px");
      if (panel.classList.contains("is-open") && activeConversation()) scrollThreadToEnd(false);
    }

    function syncVisualViewport() {
      if (viewportFrame !== null) return;
      viewportFrame = window.requestAnimationFrame(applyVisualViewport);
    }

    function setPanel(open, launcher) {
      window.clearTimeout(panelTimer);
      window.clearTimeout(effectTimer);
      window.clearTimeout(bodyLockTimer);
      if (open) {
        lastLauncher = launcher;
        loadAssistantEngine().catch(function () {});
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
        syncVisualViewport();
        panel.classList.remove("is-closing");
        panel.classList.add("is-opening");
        panel.hidden = false;
        panel.setAttribute("aria-hidden", "false");
        launchers.forEach(function (button) { button.setAttribute("aria-expanded", "true"); });
        panel.offsetWidth;
        window.requestAnimationFrame(function () {
          panel.classList.add("is-open");
          panelTimer = window.setTimeout(function () { focusWithoutScroll(closeButton); }, 410);
          effectTimer = window.setTimeout(function () { panel.classList.remove("is-opening"); }, 850);
          bodyLockTimer = window.setTimeout(function () {
            if (panel.classList.contains("is-open")) body.classList.add("cabi-panel-open");
          }, 720);
        });
        if (chatState.pendingDeletes.length) flushPendingDeletes();
        chatState.conversations.filter(function (conversation) {
          return conversation.syncPending;
        }).forEach(scheduleConversationSync);
        return;
      }

      body.classList.remove("cabi-panel-open");
      setInfoTooltip(false);
      setServicesMenu(false);
      setMobileSidebar(false);
      closeSourcesModal(false);
      cancelAssistantReply(true);
      if (composerInput) composerInput.blur();
      panel.classList.remove("is-opening");
      panel.classList.add("is-closing");
      panel.classList.remove("is-open");
      panel.setAttribute("aria-hidden", "true");
      launchers.forEach(function (button) { button.setAttribute("aria-expanded", "false"); });
      panelTimer = window.setTimeout(function () {
        panel.hidden = true;
        panel.classList.remove("is-closing");
        if (lastLauncher) focusWithoutScroll(lastLauncher);
      }, 760);
    }

    launchers.forEach(function (launcher) {
      launcher.addEventListener("click", function () { setPanel(true, launcher); });
    });
    if (servicesToggle) {
      servicesToggle.addEventListener("click", function () {
        setServicesMenu(servicesToggle.getAttribute("aria-expanded") !== "true");
      });
    }
    if (infoButton) {
      infoButton.addEventListener("click", function (event) {
        event.stopPropagation();
        setInfoTooltip(infoButton.getAttribute("aria-expanded") !== "true");
      });
    }
    if (historyButton) {
      historyButton.addEventListener("click", function () {
        setMobileSidebar(historyButton.getAttribute("aria-expanded") !== "true");
      });
    }
    if (sidebarBackdrop) sidebarBackdrop.addEventListener("click", function () { setMobileSidebar(false); });
    if (newChatButton) newChatButton.addEventListener("click", startNewConversation);
    if (recents) {
      recents.addEventListener("click", function (event) {
        var deleteButton = event.target.closest("[data-cabi-delete-conversation]");
        if (deleteButton) {
          deleteConversation(deleteButton.getAttribute("data-cabi-delete-conversation"));
          return;
        }
        var openButton = event.target.closest("[data-cabi-open-conversation]");
        if (openButton) selectConversation(openButton.getAttribute("data-cabi-open-conversation"));
      });
    }
    quickActions.forEach(function (action) {
      action.addEventListener("click", function () {
        submitQuickAction(action.getAttribute("data-cabi-prompt"), action.getAttribute("data-cabi-action"));
        if (action.classList.contains("cabi-ai-action--ads") && window.matchMedia && window.matchMedia("(hover: none)").matches) {
          window.setTimeout(function () { action.blur(); }, 0);
        }
      });
    });
    sourcesCloseButtons.forEach(function (button) {
      button.addEventListener("click", function () { closeSourcesModal(true); });
    });
    if (composerForm && composerInput && composerButton) {
      composerForm.addEventListener("submit", function (event) {
        event.preventDefault();
        if (composerButton.classList.contains("is-sending")) {
          cancelAssistantReply(false);
          focusWithoutScroll(composerInput);
          return;
        }
        submitMessage(composerInput.value, "");
      });
      composerButton.addEventListener("click", function (event) {
        if (!composerButton.classList.contains("is-sending")) return;
        event.preventDefault();
        cancelAssistantReply(false);
        focusWithoutScroll(composerInput);
      });
      composerInput.addEventListener("input", function () {
        resizeComposer();
        updateComposerButton();
      });
      composerInput.addEventListener("keydown", function (event) {
        if (event.key !== "Enter" || event.shiftKey || event.isComposing) return;
        event.preventDefault();
        if (composerButton.classList.contains("is-sending")) return;
        submitMessage(composerInput.value, "");
      });
      composerInput.addEventListener("focus", syncVisualViewport);
    }
    doc.addEventListener("click", function (event) {
      if (infoPopover && infoPopover.classList.contains("is-open") && !event.target.closest(".cabi-ai-topbar__tools")) setInfoTooltip(false);
    });
    closeButton.addEventListener("click", function () { setPanel(false); });
    doc.addEventListener("keydown", function (event) {
      if (sourcesModalIsOpen()) {
        if (event.key === "Escape") {
          event.preventDefault();
          closeSourcesModal(true);
          return;
        }
        trapSourcesModalFocus(event);
        return;
      }
      if ((event.ctrlKey || event.metaKey) && String(event.key).toLowerCase() === "k" && panel.classList.contains("is-open")) {
        event.preventDefault();
        startNewConversation();
        return;
      }
      if (event.key !== "Escape" || !panel.classList.contains("is-open")) return;
      if (historyButton && historyButton.getAttribute("aria-expanded") === "true") {
        setMobileSidebar(false);
        focusWithoutScroll(historyButton);
        return;
      }
      if (infoPopover && infoPopover.classList.contains("is-open")) {
        setInfoTooltip(false);
        focusWithoutScroll(infoButton);
        return;
      }
      setPanel(false);
    });
    window.addEventListener("resize", syncVisualViewport, { passive: true });
    if (window.visualViewport) {
      window.visualViewport.addEventListener("resize", syncVisualViewport, { passive: true });
      window.visualViewport.addEventListener("scroll", syncVisualViewport, { passive: true });
    }
    window.addEventListener("storage", function (event) {
      if (event.key !== STORAGE_KEY || Object.keys(pendingReplies).length) return;
      chatState = loadChatState();
      renderAll(false);
    });
    applyVisualViewport();
    resizeComposer();
    updateComposerButton();
    renderAll(false);
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

(function () {
  "use strict";

  var isLocalHost = /^(localhost|127\.0\.0\.1|::1)$/i.test(window.location.hostname);
  var runtimeScript = document.currentScript || document.querySelector('script[src*="site-enhancements.js"]');
  var runtimeRoot = runtimeScript ? new URL("../../", runtimeScript.src).pathname.replace(/\/$/, "") : "";
  var localSiteRoot = isLocalHost ? runtimeRoot : "";

  function siteUrl(path) {
    return localSiteRoot + path;
  }

  var sharedXIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.657l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/></svg>';
  var sharedYoutubeIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="4" fill="currentColor"/><path d="m10 9 6 3-6 3Z" fill="#101010"/></svg>';
  var sharedInstagramIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>';
  var sharedFacebookIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path fill="currentColor" d="M13.7 22v-8.8h3l.45-3.45H13.7v-2.2c0-1 .28-1.68 1.72-1.68h1.84V2.8a24.5 24.5 0 0 0-2.68-.14c-2.65 0-4.47 1.62-4.47 4.6v2.49h-3v3.45h3V22h3.59Z"/></svg>';
  var sharedLinkedinIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path fill="currentColor" d="M5.34 7.5A2.17 2.17 0 1 0 5.33 3.16 2.17 2.17 0 0 0 5.34 7.5ZM3.47 21h3.74V9H3.47v12Zm5.96 0h3.74v-6.68c0-1.76.33-3.46 2.51-3.46 2.15 0 2.18 2 2.18 3.58V21h3.74v-7.4c0-3.63-.78-6.42-5.02-6.42-2.04 0-3.4 1.12-3.96 2.17h-.05V9H9.43v12Z"/></svg>';
  var sharedArrowIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 20 20"><path d="m5 7.5 5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  var sharedMenuIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
  var sharedCloseIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';

  if (localSiteRoot) {
    document.querySelectorAll('a[href^="/"]').forEach(function (link) {
      var href = link.getAttribute("href");
      if (href && href.indexOf("//") !== 0 && href.indexOf(localSiteRoot + "/") !== 0) {
        link.setAttribute("href", siteUrl(href));
      }
    });
    document.querySelectorAll('a[href^="https://cab-it.ro"]').forEach(function (link) {
      try {
        var url = new URL(link.getAttribute("href"));
        link.setAttribute("href", localSiteRoot + (url.pathname || "/") + url.search + url.hash);
      } catch (error) {
        // Keep the original URL if it cannot be parsed.
      }
    });
  }

  var isModernHomepage = document.body.classList.contains("cabit-homepage-modern");
  document.body.classList.add("cabit-theme-2026");
  if (!isModernHomepage) {
    document.body.classList.add("cabit-modern-page");
  }
  // The global shell below replaces both legacy and simple page placeholders.
  var simpleHeader = null;
  if (simpleHeader) {
    simpleHeader.outerHTML =
      '<header id="header-sticky" class="tp-header-transparent home-1-sticky header__sticky cabit-shared-legacy-header" data-cabit-shared-header>' +
        '<div class="tp-header-area tp-header-space tp-header-bottom-border p-relative z-index-1"><div class="container-fluid"><div class="row align-items-center">' +
          '<div class="col-6 col-xl-2"><div class="tp-header-logo tp-header-logo-border">' +
            '<a href="' + siteUrl("/") + '"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert promovare online și creare site web" width="560" height="195"></a>' +
          '</div></div>' +
          '<div class="col-xl-6 d-none d-xl-block"><div class="tp-main-menu-area d-flex align-items-center pl-20">' +
            '<div class="tp-header-navbar tp-toogle d-none d-xxl-block"><button type="button" aria-label="Deschide navigarea"><span></span><span></span><span></span></button></div>' +
            '<div class="tp-main-menu d-none d-xl-block"><nav aria-label="Navigare principală"><ul>' +
              '<li><a href="' + siteUrl("/") + '">Acasa</a></li>' +
              '<li class="has-dropdown"><a href="' + siteUrl("/servicii/") + '">Meniu</a><ul class="submenu">' +
                '<li><a href="' + siteUrl("/despre-noi/") + '">Despre Noi</a></li>' +
                '<li><a href="' + siteUrl("/servicii/") + '">Servicii</a></li>' +
                '<li><a href="' + siteUrl("/preturi/") + '">Preturi</a></li>' +
                '<li><a href="' + siteUrl("/portofoliu/") + '">Portofoliu</a></li>' +
                '<li><a href="' + siteUrl("/blog/") + '">Blog</a></li>' +
                '<li><a href="' + siteUrl("/glosar-seo/") + '">Ghid SEO</a></li>' +
              '</ul></li>' +
              '<li><a href="' + siteUrl("/contact/") + '">Contact</a></li>' +
            '</ul></nav></div>' +
          '</div></div>' +
          '<div class="d-none d-lg-block col-lg-6 col-xl-4"><div class="tp-header-right d-flex align-items-center justify-content-end">' +
            '<div class="tp-header-btn d-flex ml-20"><a class="tp-btn-sm" href="' + siteUrl("/contact/") + '">Contact</a></div>' +
            '<div class="tp-header-btn d-flex ml-20"><a class="tp-btn-sm" href="tel:+40771532949"><span aria-hidden="true">☎</span> Apeleaza</a></div>' +
          '</div></div>' +
          '<div class="mobile-menu d-xl-none"><button type="button" class="tp-side-action tp-toogle hamburger-btn" aria-label="Deschide meniul" aria-expanded="false"><span></span><span></span><span></span></button></div>' +
        '</div></div></div>' +
      '</header>' +
      '<div class="tp-offcanvas-area fix cabit-shared-offcanvas" data-cabit-shared-offcanvas>' +
        '<div class="tp-side-info"><div class="tp-side-logo"><a href="' + siteUrl("/") + '"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert" width="560" height="195"></a></div>' +
        '<div class="tp-side-close"><button type="button" aria-label="Închide meniul">×</button></div>' +
        '<div class="tp-side-content p-relative"><h3 class="tp-side-title">Ajutăm la crearea strategiilor vizuale.</h3><p>Vrem să auzim de la tine. Spune-ne cum vă putem ajuta.</p><div class="tp-side-content-inner-content">' +
          '<div class="tp-side-thumb text-center"><img src="' + siteUrl("/assets/img/hero/mobile-menu.png") + '" alt="Echipa Cab-IT Expert"></div>' +
          '<div class="tp-side-btn text-xl-center mb-80"><a class="tp-btn" href="' + siteUrl("/contact/") + '">Hai să ne cunoaștem!</a></div>' +
          '<nav class="cabit-shared-mobile-nav" aria-label="Navigare mobilă"><a href="' + siteUrl("/") + '">Acasa</a><a href="' + siteUrl("/despre-noi/") + '">Despre Noi</a><a href="' + siteUrl("/servicii/") + '">Servicii</a><a href="' + siteUrl("/preturi/") + '">Preturi</a><a href="' + siteUrl("/portofoliu/") + '">Portofoliu</a><a href="' + siteUrl("/blog/") + '">Blog</a><a href="' + siteUrl("/glosar-seo/") + '">Ghid SEO</a><a href="' + siteUrl("/contact/") + '">Contact</a></nav>' +
          '<div class="tp-side-contact mb-40"><p class="call"><a href="tel:+40771532949">+40 771 532 949</a></p><p class="mail"><a href="mailto:contact@cab-it.ro">contact@cab-it.ro</a></p></div>' +
          '<div class="tp-footer-social-1"><a href="https://www.youtube.com/@cabitexpert" aria-label="YouTube">' + sharedYoutubeIcon + '</a><a href="https://www.facebook.com/profile.php?id=61592087996523" aria-label="Facebook"><strong>f</strong></a><a href="https://www.instagram.com/cabitexpert/" aria-label="Instagram">' + sharedInstagramIcon + '</a><a href="https://www.linkedin.com/company/cab-it-expert/" aria-label="LinkedIn"><strong>in</strong></a><a href="https://x.com/cabitexpert" aria-label="X">' + sharedXIcon + '</a></div>' +
        '</div></div></div>' +
        '<button type="button" class="offcanvas-overlay" aria-label="Închide meniul"></button>' +
      '</div>';
  }

  var pageMain = document.querySelector("main");
  if (pageMain && !pageMain.id) {
    pageMain.id = "continut-principal";
  }

  function normalizedPublicPath(path) {
    var cleanPath = path || "/";
    if (localSiteRoot && cleanPath.indexOf(localSiteRoot) === 0) {
      cleanPath = cleanPath.slice(localSiteRoot.length) || "/";
    }
    cleanPath = cleanPath.replace(/\/index\.html$/i, "/");
    if (cleanPath !== "/" && cleanPath.slice(-1) !== "/") {
      cleanPath += "/";
    }
    return cleanPath;
  }

  var currentPublicPath = normalizedPublicPath(window.location.pathname);
  var primaryLinks = [
    { path: "/", label: "Acasă" },
    { path: "/despre-noi/", label: "Despre noi" },
    { path: "/servicii/", label: "Servicii" },
    { path: "/preturi/", label: "Prețuri" },
    { path: "/portofoliu/", label: "Proiecte" },
    { path: "/blog/", label: "Blog" },
    { path: "/glosar-seo/", label: "Ghid SEO" },
    { path: "/contact/", label: "Contact" }
  ];
  var resourceLinks = [primaryLinks[3], primaryLinks[5], primaryLinks[6]];
  var menuPaths = resourceLinks.map(function (item) { return item.path; });

  function isCurrentPath(path) {
    if (path === "/") {
      return currentPublicPath === "/";
    }
    return currentPublicPath === path || currentPublicPath.indexOf(path) === 0;
  }

  function publicLink(item, className) {
    var current = isCurrentPath(item.path);
    return '<a class="' + (className || "") + (current ? " is-current" : "") + '" href="' + siteUrl(item.path) + '"' + (current ? ' aria-current="page"' : "") + '>' + item.label + "</a>";
  }

  function socialLink(url, label, icon) {
    return '<a href="' + url + '" target="_blank" rel="noopener noreferrer" aria-label="Cab-IT Expert pe ' + label + '">' + icon + "</a>";
  }

  var desktopMenuLinks = resourceLinks.map(function (item) {
    return '<li>' + publicLink(item, "cabit-global-header__dropdown-link") + "</li>";
  }).join("");
  var mobileMenuLinks = primaryLinks.map(function (item) {
    return publicLink(item, "cabit-global-drawer__nav-link");
  }).join("");
  var menuIsCurrent = menuPaths.some(isCurrentPath);
  var globalHeaderMarkup =
    '<a class="cabit-skip-link" href="#' + (pageMain ? pageMain.id : "continut-principal") + '">Sari la conținut</a>' +
    '<header id="cabit-global-header" class="cabit-global-header" data-cabit-global-header>' +
      '<div class="cabit-global-header__inner">' +
        '<a class="cabit-global-header__brand" href="' + siteUrl("/") + '" aria-label="Cab-IT Expert — Acasă"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert — Future is Online" width="560" height="195"></a>' +
        '<nav class="cabit-global-header__desktop" aria-label="Navigare principală"><ul class="cabit-global-header__nav-list">' +
          '<li>' + publicLink(primaryLinks[2], "cabit-global-header__nav-link") + '</li>' +
          '<li>' + publicLink(primaryLinks[4], "cabit-global-header__nav-link") + '</li>' +
          '<li>' + publicLink(primaryLinks[1], "cabit-global-header__nav-link") + '</li>' +
          '<li class="cabit-global-header__menu' + (menuIsCurrent ? " is-current" : "") + '" data-cabit-desktop-menu>' +
            '<button class="cabit-global-header__menu-trigger" type="button" aria-expanded="false" aria-controls="cabit-desktop-dropdown" aria-haspopup="true">Resurse ' + sharedArrowIcon + '</button>' +
            '<ul id="cabit-desktop-dropdown" class="cabit-global-header__dropdown" aria-label="Resurse Cab-IT Expert">' + desktopMenuLinks + '</ul>' +
          '</li>' +
          '<li>' + publicLink(primaryLinks[7], "cabit-global-header__nav-link") + '</li>' +
        '</ul></nav>' +
        '<div class="cabit-global-header__actions"><a class="cabit-global-header__secondary-cta" href="' + siteUrl("/portofoliu/") + '">Vezi proiectele</a><a class="cabit-global-header__cta" href="' + siteUrl("/contact/") + '">Solicită un audit</a></div>' +
        '<button class="cabit-global-header__mobile-toggle" type="button" aria-label="Deschide meniul" aria-expanded="false" aria-controls="cabit-global-drawer">' + sharedMenuIcon + '</button>' +
      '</div>' +
    '</header>';

  var globalSocialLinks =
    socialLink("https://www.youtube.com/@cabitexpert", "YouTube", sharedYoutubeIcon) +
    socialLink("https://www.facebook.com/profile.php?id=61592087996523", "Facebook", sharedFacebookIcon) +
    socialLink("https://www.instagram.com/cabitexpert/", "Instagram", sharedInstagramIcon) +
    socialLink("https://www.linkedin.com/company/cab-it-expert/", "LinkedIn", sharedLinkedinIcon) +
    socialLink("https://x.com/cabitexpert", "X", sharedXIcon);

  var globalDrawerMarkup =
    '<div id="cabit-global-drawer" class="cabit-global-drawer" data-cabit-global-drawer hidden aria-hidden="true">' +
      '<button class="cabit-global-drawer__backdrop" type="button" tabindex="-1" aria-label="Închide meniul"></button>' +
      '<aside class="cabit-global-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="cabit-drawer-title">' +
        '<div class="cabit-global-drawer__top"><a class="cabit-global-drawer__brand" href="' + siteUrl("/") + '" aria-label="Cab-IT Expert — Acasă"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert" width="560" height="195"></a><button class="cabit-global-drawer__close" type="button" aria-label="Închide meniul">' + sharedCloseIcon + '</button></div>' +
        '<div class="cabit-global-drawer__body"><div class="cabit-global-drawer__intro"><span class="cabit-global-drawer__eyebrow">Partenerul tău digital</span><h2 id="cabit-drawer-title">Construim creștere online, clar și măsurabil.</h2><p>Spune-ne ce vrei să obții, iar noi transformăm obiectivul într-un plan ușor de înțeles.</p></div>' +
          '<nav class="cabit-global-drawer__nav" aria-label="Navigare mobilă">' + mobileMenuLinks + '</nav>' +
          '<div class="cabit-global-drawer__visual"><img src="' + siteUrl("/assets/img/hero/mobile-menu.png") + '" alt="Echipă care dezvoltă o strategie digitală" width="600" height="600" loading="lazy"></div>' +
          '<a class="cabit-global-drawer__cta" href="' + siteUrl("/contact/") + '">Hai să discutăm <span aria-hidden="true">→</span></a>' +
          '<div class="cabit-global-drawer__contact"><a href="tel:+40771532949">+40 771 532 949</a><a href="mailto:contact@cab-it.ro">contact@cab-it.ro</a><span>Disponibili non-stop</span></div>' +
          '<div class="cabit-global-drawer__social" aria-label="Rețele sociale">' + globalSocialLinks + '</div>' +
        '</div>' +
      '</aside>' +
    '</div>';

  var useNextShell = document.body.classList.contains("cabit-theme-2026");
  var isNextHome = document.body.classList.contains("cabit-home");
  var existingHeader = document.querySelector("header");
  if (existingHeader && !isNextHome) existingHeader.remove();
  if (!useNextShell) document.body.insertAdjacentHTML("afterbegin", globalHeaderMarkup);
  document.querySelectorAll(".tp-offcanvas-area, [data-cabit-shared-offcanvas]").forEach(function (offcanvas) {
    offcanvas.remove();
  });
  var globalHeader = document.querySelector("[data-cabit-global-header]");
  if (globalHeader) globalHeader.insertAdjacentHTML("afterend", globalDrawerMarkup);

  var servicePage = document.body.classList.contains("cabit-service-page");
  if (servicePage) {
    var serviceVisuals = {
      website: '<div class="service-visual device-visual" aria-label="Website afișat pe desktop și telefon"><div class="device-desktop"><div class="device-browser-bar"><i></i><i></i><i></i></div><div class="device-site"><span class="device-site-logo"></span><div><b></b><b></b><em></em></div></div><span class="device-stand"></span></div><div class="device-phone"><div class="device-phone-speaker"></div><div class="device-site"><span class="device-site-logo"></span><div><b></b><em></em></div></div></div><span class="device-sync" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 7h10l-2.5-2.5M17 17H7l2.5 2.5"/></svg></span></div>',
      seo: '<div class="service-visual seo-rank-visual" aria-label="Website CAB-IT urcă în rezultatele Google"><div class="seo-search-bar"><strong>G</strong><span>creare website București</span><i></i></div><div class="seo-result-ladder"><div class="seo-competitor"><b>#1</b><span></span><p><strong>website-concurent.ro</strong><small>Servicii web București</small></p></div><div class="seo-competitor"><b>#2</b><span></span><p><strong>agenție-digitală.ro</strong><small>Creare site profesional</small></p></div><div class="seo-cabit-result"><b>#3</b><span class="seo-cabit-logo"><i></i></span><p><strong>CAB-IT Expert</strong><small>cab-it.ro · Website optimizat</small></p><em>↑14</em></div><span class="seo-old-rank">#17</span><svg class="seo-climb-arrow" viewBox="0 0 95 60"><path d="M4 53C28 52 35 42 50 40S69 22 90 8"/><path d="m82 7 8 1-2 8"/></svg></div></div>',
      ads: '<div class="service-visual google-ad-visual" aria-label="Website afișat ca anunț sponsorizat în Google"><div class="google-ad-search"><strong><i>G</i><i>o</i><i>o</i><i>g</i><i>l</i><i>e</i></strong><span>creare website București</span><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></div><div class="google-ad-result"><div><span>Sponsorizat</span><small>cab-it.ro</small></div><strong>CAB-IT Expert · Creare website profesional</strong><p>Website rapid, modern și optimizat pentru Google.</p></div><b class="google-ad-badge">Anunț în top</b></div>',
      social: '<div class="service-visual platform-visual" aria-label="Promovare pe Facebook, Instagram și TikTok"><div class="platform-card is-facebook"><span>f</span><strong>Facebook</strong><small>Ads</small></div><div class="platform-card is-instagram"><span>◎</span><strong>Instagram</strong><small>Ads</small></div><div class="platform-card is-tiktok"><span>♪</span><strong>TikTok</strong><small>Ads</small></div><div class="platform-pulse"></div></div>',
      automation: '<div class="service-visual chat-visual" aria-label="Conversație cu asistentul AI"><div class="chat-agent"><span>AI</span><div><strong>Asistent CAB-IT</strong><small>online acum</small></div></div><p class="chat-message is-user">Vreau o ofertă pentru un website.</p><p class="chat-message is-bot"><span>AI</span>Sigur! Îți preiau cererea automat.</p><i class="chat-typing"><b></b><b></b><b></b></i></div>',
      conversion: '<div class="service-visual sales-visual" aria-label="Grafic animat cu vânzări în creștere"><div class="sales-visual__top"><span>Conversii</span><strong>+38%</strong></div><svg viewBox="0 0 300 82"><path class="sales-area" d="M5 72 5 61 38 64 72 52 106 57 142 39 176 44 210 25 244 30 294 8 294 72Z"/><path class="sales-line" d="M5 61 38 64 72 52 106 57 142 39 176 44 210 25 244 30 294 8"/><g class="sales-points"><circle cx="72" cy="52" r="3"/><circle cx="142" cy="39" r="3"/><circle cx="210" cy="25" r="3"/><circle cx="294" cy="8" r="4"/></g></svg><div class="sales-visual__bottom"><span>Ultimele 30 de zile</span><b>Tendință în creștere ↗</b></div></div>',
      analysis: '<div class="service-visual audit-visual" aria-label="Dashboard de audit digital"><div class="audit-score"><span>Scor digital</span><strong>86<small>/100</small></strong></div><ul><li><i></i><span>Viteză</span><b>92%</b></li><li><i></i><span>SEO tehnic</span><b>88%</b></li><li><i></i><span>Conversii</span><b>78%</b></li></ul><svg viewBox="0 0 180 70"><path d="M5 60 36 51 65 53 95 34 124 39 173 8"/><path d="m160 8 13 0-2 13"/></svg></div>',
      local: '<div class="service-visual local-visual" aria-label="Rezultat local vizibil în Google Maps"><div class="local-search"><b>G</b><span>agenție marketing București</span></div><div class="local-map"><i class="local-road one"></i><i class="local-road two"></i><span class="local-pin">⌖</span><div><strong>CAB-IT Expert</strong><small>★★★★★ · București</small><b>Poziție locală în creștere</b></div></div></div>'
    };
    var serviceKey = document.body.getAttribute("data-service") || "analysis";
    var serviceHero = document.querySelector(".cabit-page-header .container");
    if (serviceHero) {
      var serviceActions = document.createElement("div");
      serviceActions.className = "cabit-service-hero-actions";
      serviceActions.innerHTML = '<a class="cabit-service-primary" href="' + siteUrl("/contact/") + '">Solicită o discuție</a><a class="cabit-service-secondary" href="' + siteUrl("/portofoliu/") + '">Vezi proiectele</a>';
      serviceHero.appendChild(serviceActions);
      var serviceCopy = document.createElement("div");
      serviceCopy.className = "cabit-service-hero-copy";
      while (serviceHero.firstChild) serviceCopy.appendChild(serviceHero.firstChild);
      var serviceStage = document.createElement("div");
      serviceStage.className = "cabit-service-hero-stage";
      serviceStage.innerHTML = serviceVisuals[serviceKey] || serviceVisuals.analysis;
      serviceHero.appendChild(serviceCopy);
      serviceHero.appendChild(serviceStage);
    }
  }

  var simpleFooter = null;
  if (simpleFooter) {
    var xIcon = sharedXIcon;
    var youtubeIcon = sharedYoutubeIcon;
    var instagramIcon = sharedInstagramIcon;
    simpleFooter.outerHTML =
      '<footer class="p-relative include-bg cabit-shared-legacy-footer" style="background-image:url(' + siteUrl("/img/footer-bg-3.jpg") + ')">' +
        '<div class="tp-footer-area-3 pt-115"><div class="tp-footer-top-widget-3 p-relative"><div class="container container-large"><div class="row">' +
          '<div class="col-xl-3 col-lg-3 col-sm-6 col-12"><div class="tp-footer-widget-3 tp-space-col-1 mb-40"><div class="tp-footer-logo"><a href="' + siteUrl("/") + '"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert" width="560" height="195"></a></div><p>Servicii de dezvoltare web și promovare online construite în jurul obiectivelor și datelor fiecărui client.</p><div class="tp-footer-social-3">' +
            '<a href="https://www.youtube.com/@cabitexpert" aria-label="Cab-IT Expert pe YouTube">' + youtubeIcon + '</a><a href="https://www.facebook.com/profile.php?id=61592087996523" aria-label="Cab-IT Expert pe Facebook"><strong>f</strong></a><a href="https://www.instagram.com/cabitexpert/" aria-label="Cab-IT Expert pe Instagram">' + instagramIcon + '</a><a href="https://www.linkedin.com/company/cab-it-expert/" aria-label="Cab-IT Expert pe LinkedIn"><strong>in</strong></a><a href="https://x.com/cabitexpert" aria-label="Cab-IT Expert pe X">' + xIcon + '</a>' +
          '</div></div></div>' +
          '<div class="col-xl-3 col-lg-2 col-sm-6 col-12"><div class="tp-footer-widget-3 tp-space-col-2 mb-40"><span class="tp-footer-widget__title">Quick Links</span><ul><li><a href="' + siteUrl("/") + '">Acasa</a></li><li><a href="' + siteUrl("/despre-noi/") + '">Despre Noi</a></li><li><a href="' + siteUrl("/servicii/") + '">Servicii</a></li><li><a href="' + siteUrl("/portofoliu/") + '">Portofoliu</a></li><li><a href="' + siteUrl("/blog/") + '">Blog</a></li><li><a href="' + siteUrl("/glosar-seo/") + '">Ghid SEO</a></li><li><a href="' + siteUrl("/contact/") + '">Contact</a></li></ul></div></div>' +
          '<div class="col-xl-2 col-lg-3 col-sm-6 col-12"><div class="tp-footer-widget-3 tp-space-col-3 footer-info mb-40"><span class="tp-footer-widget__title">Contact info</span><ul><li><a href="https://www.google.com/maps/search/?api=1&amp;query=Intrarea+Humule%C8%99ti+6A+052034+Bucure%C8%99ti+Rom%C3%A2nia">Intrarea Humulești 6A, 052034 București, România</a></li><li>Program non-stop</li><li><a href="mailto:contact@cab-it.ro">contact@cab-it.ro</a></li><li><a href="mailto:office@cab-it.ro">HR: office@cab-it.ro</a></li><li><a href="tel:+40771532949">+40 771 532 949</a></li></ul></div></div>' +
          '<div class="col-xl-4 col-lg-4 col-sm-6 col-12"><div class="tp-footer-widget-3 tp-space-col-4 mb-30"><span class="tp-footer-widget__title">Subscribe Newsletter</span><p>Primește ghiduri practice despre SEO, promovare online și website-uri.</p><div class="tp-footer-subscribe-input-3"><form class="p-relative" action="' + siteUrl("/newsletter-subscribe.php") + '" method="post"><label class="visually-hidden" for="cabit-shared-footer-email">Adresa de email</label><input id="cabit-shared-footer-email" type="email" name="email" placeholder="Adresa ta de email" autocomplete="email" required><input type="hidden" name="source" value="footer-pagini-noi"><input class="cabit-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"><button class="tp-footer-btn" type="submit" aria-label="Abonează-te">→</button></form></div></div></div>' +
        '</div></div></div><div class="tp-footer-bottom-widget"><div class="container container-large"><div class="tp-footer-copyright-2"><div class="row"><div class="col-md-12 col-lg-6"><div class="tp-footer-copyright-inner-2 pb-20"><p>©Copyright <span data-current-year>2026</span> All Rights Reserved</p></div></div><div class="col-md-12 col-lg-6"><div class="tp-footer-copyright-link-2 text-lg-end pb-20"><a href="' + siteUrl("/termeni-si-conditii/") + '">Terms and conditions</a></div></div></div></div></div></div></div>' +
      '</footer>';
  }

  var footerNavigation = primaryLinks.map(function (item) {
    return '<li>' + publicLink(item, "cabit-global-footer__link") + "</li>";
  }).join("");
  var globalFooterMarkup =
    '<footer class="cabit-global-footer" data-cabit-global-footer>' +
      '<div class="cabit-global-footer__inner">' +
        '<div class="cabit-global-footer__grid">' +
          '<div class="cabit-global-footer__brand-column"><a class="cabit-global-footer__brand" href="' + siteUrl("/") + '" aria-label="Cab-IT Expert — Acasă"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert — Future is Online" width="560" height="195"></a><p>CAB-IT EXPERT SRL ajută afacerile să crească prin website-uri profesionale, SEO și campanii digitale construite pe date.</p><div class="cabit-global-footer__social" aria-label="Rețele sociale">' + globalSocialLinks + '</div></div>' +
          '<nav class="cabit-global-footer__column" aria-labelledby="cabit-footer-navigation"><h2 id="cabit-footer-navigation">Navigare</h2><ul>' + footerNavigation + '</ul></nav>' +
          '<div class="cabit-global-footer__column cabit-global-footer__contact"><h2>Contact</h2><address><a href="https://www.google.com/maps/search/?api=1&amp;query=Intrarea+Humule%C8%99ti+6A+052034+Bucure%C8%99ti+Rom%C3%A2nia" target="_blank" rel="noopener noreferrer">Intrarea Humulești 6A<br>052034 București, România</a></address><p><span>Program</span><strong>Non-stop</strong></p><a href="tel:+40771532949">+40 771 532 949</a><a href="mailto:contact@cab-it.ro">contact@cab-it.ro</a><a href="mailto:office@cab-it.ro">HR: office@cab-it.ro</a></div>' +
          '<div class="cabit-global-footer__column cabit-global-footer__newsletter"><span class="cabit-global-footer__eyebrow">Resurse utile, fără spam</span><h2>Subscribe Newsletter</h2><p>Primește ghiduri practice despre SEO, promovare online și website-uri.</p><form class="cabit-global-footer__form" action="' + siteUrl("/newsletter-subscribe.php") + '" method="post"><label for="cabit-global-footer-email">Adresa ta de email</label><div class="cabit-global-footer__input-row"><input id="cabit-global-footer-email" type="email" name="email" placeholder="nume@companie.ro" autocomplete="email" required><button type="submit" aria-label="Abonează-te la newsletter">Abonează-te <span aria-hidden="true">→</span></button></div><input type="hidden" name="source" value="footer-global"><input class="cabit-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"></form></div>' +
        '</div>' +
        '<section class="cabit-global-footer__cta-band" aria-labelledby="cabit-footer-cta-title"><div><span>Ai un obiectiv de creștere?</span><h2 id="cabit-footer-cta-title">Hai să-l transformăm într-un plan digital clar.</h2></div><a href="' + siteUrl("/contact/") + '">Solicită un audit <span aria-hidden="true">→</span></a></section>' +
        '<div class="cabit-global-footer__bottom"><p>©Copyright <span data-current-year>' + new Date().getFullYear() + '</span> All Rights Reserved</p><div><span>CAB-IT EXPERT SRL</span><a href="' + siteUrl("/termeni-si-conditii/") + '">Termeni și condiții</a></div></div>' +
      '</div>' +
    '</footer>';

  var existingFooter = document.querySelector("footer");
  if (existingFooter && !isNextHome) existingFooter.remove();
  if (!useNextShell) document.body.insertAdjacentHTML("beforeend", globalFooterMarkup);

  var sharedLegacyHeader = document.querySelector("[data-cabit-shared-header]");
  var sharedOffcanvas = document.querySelector("[data-cabit-shared-offcanvas]");
  if (sharedLegacyHeader && sharedOffcanvas) {
    var sideInfo = sharedOffcanvas.querySelector(".tp-side-info");
    var sideOverlay = sharedOffcanvas.querySelector(".offcanvas-overlay");
    var menuButtons = sharedLegacyHeader.querySelectorAll(".tp-toogle");
    var closeButton = sharedOffcanvas.querySelector(".tp-side-close button");

    var setMobileMenu = function (open) {
      sideInfo.classList.toggle("tp-side-info-open", open);
      sideOverlay.classList.toggle("offcanvas-overlay-open", open);
      document.body.classList.toggle("cabit-menu-open", open);
      menuButtons.forEach(function (button) { button.setAttribute("aria-expanded", open ? "true" : "false"); });
    };

    menuButtons.forEach(function (button) {
      button.addEventListener("click", function () { setMobileMenu(true); });
    });
    closeButton.addEventListener("click", function () { setMobileMenu(false); });
    sideOverlay.addEventListener("click", function () { setMobileMenu(false); });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") { setMobileMenu(false); }
    });

    var updateLegacyHeader = function () {
      sharedLegacyHeader.classList.toggle("header-sticky", window.scrollY > 70);
    };
    updateLegacyHeader();
    window.addEventListener("scroll", updateLegacyHeader, { passive: true });
  }

  var globalDrawer = document.querySelector("[data-cabit-global-drawer]");
  if (globalHeader && globalDrawer) {
    var drawerPanel = globalDrawer.querySelector(".cabit-global-drawer__panel");
    var drawerClose = globalDrawer.querySelector(".cabit-global-drawer__close");
    var drawerBackdrop = globalDrawer.querySelector(".cabit-global-drawer__backdrop");
    var drawerToggles = globalHeader.querySelectorAll('[aria-controls="cabit-global-drawer"]');
    var desktopMenu = globalHeader.querySelector("[data-cabit-desktop-menu]");
    var desktopMenuTrigger = desktopMenu ? desktopMenu.querySelector(".cabit-global-header__menu-trigger") : null;
    var desktopDropdown = desktopMenu ? desktopMenu.querySelector(".cabit-global-header__dropdown") : null;
    var lastFocusedElement = null;
    var drawerCloseTimer = null;
    var desktopCloseTimer = null;

    function focusableDrawerElements() {
      return Array.prototype.slice.call(globalDrawer.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter(function (element) {
        return !element.hidden && element.getAttribute("aria-hidden") !== "true";
      });
    }

    function setDesktopMenu(open) {
      if (!desktopMenu || !desktopMenuTrigger || !desktopDropdown) {
        return;
      }
      window.clearTimeout(desktopCloseTimer);
      desktopMenu.classList.toggle("is-open", open);
      desktopMenuTrigger.setAttribute("aria-expanded", open ? "true" : "false");
      desktopDropdown.setAttribute("aria-hidden", open ? "false" : "true");
    }

    function openDrawer() {
      window.clearTimeout(drawerCloseTimer);
      lastFocusedElement = document.activeElement;
      globalDrawer.hidden = false;
      globalDrawer.setAttribute("aria-hidden", "false");
      drawerToggles.forEach(function (toggle) {
        toggle.setAttribute("aria-expanded", "true");
      });
      document.body.classList.add("cabit-drawer-open");
      window.requestAnimationFrame(function () {
        globalDrawer.classList.add("is-open");
      });
      window.setTimeout(function () {
        drawerClose.focus();
      }, 30);
    }

    function closeDrawer(restoreFocus) {
      if (globalDrawer.hidden) {
        return;
      }
      globalDrawer.classList.remove("is-open");
      globalDrawer.setAttribute("aria-hidden", "true");
      drawerToggles.forEach(function (toggle) {
        toggle.setAttribute("aria-expanded", "false");
      });
      document.body.classList.remove("cabit-drawer-open");
      if (restoreFocus !== false && lastFocusedElement && typeof lastFocusedElement.focus === "function") {
        lastFocusedElement.focus();
      }
      drawerCloseTimer = window.setTimeout(function () {
        if (!globalDrawer.classList.contains("is-open")) {
          globalDrawer.hidden = true;
        }
      }, 280);
    }

    drawerToggles.forEach(function (toggle) {
      toggle.addEventListener("click", openDrawer);
    });
    drawerClose.addEventListener("click", function () { closeDrawer(true); });
    drawerBackdrop.addEventListener("click", function () { closeDrawer(true); });
    globalDrawer.querySelectorAll("a[href]").forEach(function (link) {
      link.addEventListener("click", function () { closeDrawer(false); });
    });

    if (desktopMenu && desktopMenuTrigger) {
      desktopDropdown.setAttribute("aria-hidden", "true");
      desktopMenuTrigger.addEventListener("click", function () {
        setDesktopMenu(!desktopMenu.classList.contains("is-open"));
      });
      desktopMenu.addEventListener("pointerenter", function (event) {
        if (event.pointerType !== "touch") {
          setDesktopMenu(true);
        }
      });
      desktopMenu.addEventListener("pointerleave", function (event) {
        if (event.pointerType !== "touch") {
          desktopCloseTimer = window.setTimeout(function () { setDesktopMenu(false); }, 140);
        }
      });
      desktopMenu.addEventListener("focusin", function () { setDesktopMenu(true); });
      desktopMenu.addEventListener("focusout", function (event) {
        if (!desktopMenu.contains(event.relatedTarget)) {
          desktopCloseTimer = window.setTimeout(function () { setDesktopMenu(false); }, 80);
        }
      });
    }

    document.addEventListener("click", function (event) {
      if (desktopMenu && !desktopMenu.contains(event.target)) {
        setDesktopMenu(false);
      }
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        if (!globalDrawer.hidden) {
          closeDrawer(true);
        } else if (desktopMenu && desktopMenu.classList.contains("is-open")) {
          setDesktopMenu(false);
          desktopMenuTrigger.focus();
        }
        return;
      }
      if (event.key === "Tab" && !globalDrawer.hidden && globalDrawer.classList.contains("is-open")) {
        var focusableElements = focusableDrawerElements();
        if (!focusableElements.length) {
          event.preventDefault();
          return;
        }
        var firstFocusable = focusableElements[0];
        var lastFocusable = focusableElements[focusableElements.length - 1];
        if (event.shiftKey && document.activeElement === firstFocusable) {
          event.preventDefault();
          lastFocusable.focus();
        } else if (!event.shiftKey && document.activeElement === lastFocusable) {
          event.preventDefault();
          firstFocusable.focus();
        }
      }
    });

    function updateGlobalHeader() {
      globalHeader.classList.toggle("is-scrolled", window.scrollY > 12);
    }
    updateGlobalHeader();
    window.addEventListener("scroll", updateGlobalHeader, { passive: true });
    window.addEventListener("resize", function () {
      if (window.innerWidth >= 1100 && !globalDrawer.hidden) {
        closeDrawer(false);
      }
    });
  }

  var newsletterStatus = new URLSearchParams(window.location.search).get("newsletter");
  if (newsletterStatus) {
    var newsletterMessages = {
      success: "Mulțumim! Adresa ta a fost adăugată la newsletter.",
      exists: "Această adresă este deja abonată la newsletter.",
      invalid: "Adresa de email nu este validă. Te rugăm să încerci din nou."
    };
    var notice = document.createElement("div");
    notice.className = "cabit-newsletter-notice" + (newsletterStatus === "invalid" ? " is-error" : "");
    notice.setAttribute("role", "status");
    notice.textContent = newsletterMessages[newsletterStatus] || newsletterMessages.invalid;
    document.body.appendChild(notice);
    window.setTimeout(function () { notice.remove(); }, 7000);
  }

  if (!document.getElementById("cabit-google-fonts")) {
    var fonts = document.createElement("link");
    fonts.id = "cabit-google-fonts";
    fonts.rel = "stylesheet";
    fonts.href = "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&display=optional";
    document.head.appendChild(fonts);
  }

  var articleCarousel = document.querySelector("[data-articles-carousel]");
  if (articleCarousel) {
    var articleCards = Array.prototype.slice.call(articleCarousel.querySelectorAll(".cabit-blog-card"));
    var carouselShell = articleCarousel.closest(".cabit-articles-carousel");
    var previousArticle = carouselShell ? carouselShell.querySelector("[data-carousel-previous]") : null;
    var nextArticle = carouselShell ? carouselShell.querySelector("[data-carousel-next]") : null;
    var carouselStatus = carouselShell ? carouselShell.querySelector("[data-carousel-status]") : null;

    function carouselStep() {
      if (!articleCards.length) {
        return articleCarousel.clientWidth;
      }
      var carouselStyles = window.getComputedStyle(articleCarousel);
      return articleCards[0].getBoundingClientRect().width + (parseFloat(carouselStyles.columnGap || carouselStyles.gap) || 0);
    }

    function updateArticleCarousel() {
      var maxScroll = Math.max(0, articleCarousel.scrollWidth - articleCarousel.clientWidth - 2);
      if (previousArticle) {
        previousArticle.disabled = articleCarousel.scrollLeft <= 2;
      }
      if (nextArticle) {
        nextArticle.disabled = articleCarousel.scrollLeft >= maxScroll;
      }
      if (carouselStatus) {
        var current = articleCards.length ? Math.min(articleCards.length, Math.round(articleCarousel.scrollLeft / Math.max(carouselStep(), 1)) + 1) : 0;
        carouselStatus.textContent = articleCards.length ? current + " / " + articleCards.length : "Nu există articole";
      }
    }

    if (previousArticle) {
      previousArticle.addEventListener("click", function () {
        articleCarousel.scrollBy({ left: -carouselStep(), behavior: "smooth" });
      });
    }
    if (nextArticle) {
      nextArticle.addEventListener("click", function () {
        articleCarousel.scrollBy({ left: carouselStep(), behavior: "smooth" });
      });
    }
    articleCarousel.addEventListener("scroll", updateArticleCarousel, { passive: true });
    window.addEventListener("resize", updateArticleCarousel);
    updateArticleCarousel();
  }

  var motionItems = document.querySelectorAll(
    ".cabit-theme-2026 .cabit-service-ribbon__grid > a, " +
    ".cabit-theme-2026 .cabit-content-card, " +
    ".cabit-theme-2026 .cabit-blog-card, " +
    ".cabit-theme-2026 .cabit-home-faq details, " +
    ".cabit-theme-2026 .tp-service-item, " +
    ".cabit-theme-2026 .tp-pricing-item, " +
    ".cabit-theme-2026 .tp-portfolio-item-2, " +
    ".cabit-theme-2026 .tp-fun-fact-content-3, " +
    ".cabit-theme-2026 .cabit-glossary-list article, " +
    ".cabit-theme-2026 .cabit-process-card, " +
    ".cabit-theme-2026 .cabit-contact-card, " +
    ".cabit-theme-2026 .cabit-google-partner-feature"
  );
  if (motionItems.length) {
    var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    document.body.classList.add("cabit-motion-ready");
    motionItems.forEach(function (item, index) {
      item.style.setProperty("--cabit-reveal-delay", Math.min(index % 5, 4) * 70 + "ms");
    });
    if (prefersReducedMotion || !("IntersectionObserver" in window)) {
      motionItems.forEach(function (item) { item.classList.add("is-visible"); });
    } else {
      var motionObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            motionObserver.unobserve(entry.target);
          }
        });
      }, { rootMargin: "0px 0px -7%", threshold: 0.08 });
      motionItems.forEach(function (item) { motionObserver.observe(item); });
    }
  }

  document.querySelectorAll("[data-current-year]").forEach(function (element) {
    element.textContent = new Date().getFullYear();
  });

  var counters = document.querySelectorAll("[data-count-target]");
  if (!counters.length) {
    return;
  }

  var reducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function setFinalValue(element) {
    element.textContent = element.dataset.countTarget;
  }

  function animateCounter(element) {
    if (element.dataset.counted === "true") {
      return;
    }

    element.dataset.counted = "true";
    var target = Number(element.dataset.countTarget);
    var duration = Number(element.dataset.countDuration || 900);

    if (!Number.isFinite(target) || reducedMotion) {
      setFinalValue(element);
      return;
    }

    var startTime;
    element.textContent = "0";

    function frame(timestamp) {
      if (!startTime) {
        startTime = timestamp;
      }

      var progress = Math.min((timestamp - startTime) / duration, 1);
      var easedProgress = 1 - Math.pow(1 - progress, 3);
      element.textContent = Math.round(target * easedProgress).toLocaleString("ro-RO");

      if (progress < 1) {
        window.requestAnimationFrame(frame);
      } else {
        setFinalValue(element);
      }
    }

    window.requestAnimationFrame(frame);
  }

  if (!("IntersectionObserver" in window)) {
    counters.forEach(setFinalValue);
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { rootMargin: "0px 0px -5%", threshold: 0.2 });

  counters.forEach(function (counter) {
    setFinalValue(counter);
    observer.observe(counter);
  });
}());

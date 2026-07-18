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
        '<a class="next-logo" href="/" aria-label="CAB-IT Expert — Acasă"><img src="/img/logo.png" alt="Simbol CAB-IT Expert SRL" width="278" height="256"></a>' +
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
      '<div class="next-mobile-menu" id="mobile-menu" hidden><a href="/">Acasă</a><a href="/servicii/">Servicii</a><a href="/portofoliu/">Proiecte</a><a href="/despre-noi/">Despre noi</a><a href="/blog/">Blog</a><a href="/preturi/">Prețuri</a><a href="/contact/">Contact</a><a class="button button-primary" href="/#audit">Cere un audit gratuit <span aria-hidden="true">→</span></a></div>' +
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
      '<div class="section-shell footer-bottom"><span>© <span data-current-year>' + new Date().getFullYear() + '</span> CAB IT EXPERT SRL. Toate drepturile rezervate.</span><div class="footer-socials"><a href="https://www.facebook.com/profile.php?id=61592087996523" rel="noopener" target="_blank" aria-label="CAB-IT pe Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v8h4v-8h3.5l.5-4h-4V9c0-.7.3-1 1-1Z"/></svg></a><a href="https://www.instagram.com/cabitexpert/" rel="noopener" target="_blank" aria-label="CAB-IT pe Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a><a href="https://www.linkedin.com/company/cab-it-expert/" rel="noopener" target="_blank" aria-label="CAB-IT pe LinkedIn"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v10M5 5.5v.1M10 19V9h4v1.5c1-1.5 5-2 5 2.5v6M10 13c0-2.2 1.3-4 4-4"/></svg></a></div></div>' +
      '<div class="footer-cta"><span>Ai o idee? Hai să o transformăm într-un proiect care produce rezultate.</span><a href="/contact/">Hai să discutăm <b>→</b></a></div>' +
    '</footer>';
  }

  function ensureShell() {
    if (body.classList.contains("cabit-home")) return;
    body.classList.add("cabit-next-shell");
    if (!doc.querySelector(".next-header")) body.insertAdjacentHTML("afterbegin", headerMarkup());
    if (!doc.querySelector(".next-footer")) body.insertAdjacentHTML("beforeend", footerMarkup());
    if (!doc.querySelector(".mobile-contact")) body.insertAdjacentHTML("beforeend", '<a class="mobile-contact" href="https://wa.me/40771532949?text=Bun%C4%83%2C%20a%C8%99%20dori%20mai%20multe%20detalii" target="_blank" rel="noopener" aria-label="Scrie-ne pe WhatsApp: Bună, aș dori mai multe detalii"><span class="mobile-contact__icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path fill="currentColor" d="M16.1 4.2A11.5 11.5 0 0 0 6.2 21.6L4.7 27l5.5-1.4a11.5 11.5 0 1 0 5.9-21.4Zm0 20.7c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.2.8.9-3.1-.2-.4a9.2 9.2 0 1 1 7.9 4.3Zm5.1-6.8c-.3-.1-1.7-.8-1.9-.9-.3-.1-.5-.1-.7.2l-.9 1.1c-.2.2-.4.2-.7.1a7.5 7.5 0 0 1-2.2-1.4 8.4 8.4 0 0 1-1.5-1.9c-.2-.3 0-.5.1-.6l.5-.6.3-.5c.1-.2 0-.4 0-.6l-.9-2.1c-.2-.5-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.4-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.5c.2.2 2.4 3.7 5.9 5.1.8.3 1.5.5 2 .6.8.3 1.6.2 2.2.1.7-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3Z"/></svg></span><span class="mobile-contact__tooltip" role="tooltip"><span class="mobile-contact__dots" aria-hidden="true"><i></i><i></i><i></i></span><span>Hai să discutăm</span></span></a>');
  }

  ensureShell();
  localizeRootPaths(doc);

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
    mobileMenu.insertAdjacentHTML("afterbegin", '<div class="next-mobile-menu__head"><a class="next-mobile-menu__brand" href="/" aria-label="CAB-IT Expert — Acasă"><img src="/assets/img/brand/cab-it-c-symbol-app-v7.png" alt="" width="192" height="192"><span><strong>Meniu</strong><small>CAB-IT Expert</small></span></a><button class="next-mobile-menu__close" type="button" aria-label="Închide meniul"><span></span><span></span></button></div>');
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

  var currentPath = window.location.pathname.replace(/index\.(html|php)$/i, "");
  doc.querySelectorAll(".next-nav > a").forEach(function (link) {
    var path = new URL(link.href, window.location.href).pathname;
    var isHome = path === "/" && currentPath === "/";
    var isSection = path !== "/" && currentPath.indexOf(path) === 0;
    link.classList.toggle("is-active", isHome || isSection);
  });

  var reducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
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

  var conversationForm = doc.querySelector("[data-conversation-form]");
  if (conversationForm) {
    conversationForm.querySelectorAll('input[name="service"]').forEach(function (choice) {
      choice.addEventListener("change", function () {
        conversationForm.classList.add("has-choice");
        var firstField = conversationForm.querySelector('.conversation-fields input[name="name"]');
        if (firstField && window.innerWidth > 760) firstField.focus();
      });
    });
  }

  var faqItems = doc.querySelectorAll(".faq-list details");
  function finishFaqMotion(item, shouldOpen) {
    item.open = shouldOpen;
    item.style.height = "";
    item.style.overflow = "";
    item.removeAttribute("data-faq-animating");
  }

  function animateFaq(item, shouldOpen) {
    if (item.hasAttribute("data-faq-animating") || item.open === shouldOpen) return;
    var summary = item.querySelector("summary");
    if (!summary || reducedMotion || typeof item.animate !== "function") {
      finishFaqMotion(item, shouldOpen);
      return;
    }

    item.setAttribute("data-faq-animating", "true");
    var startHeight = item.offsetHeight;
    if (shouldOpen) item.open = true;
    var endHeight = shouldOpen ? item.offsetHeight : summary.offsetHeight;
    item.style.height = startHeight + "px";
    item.style.overflow = "hidden";
    var animation = item.animate(
      [{ height: startHeight + "px" }, { height: endHeight + "px" }],
      { duration: shouldOpen ? 390 : 330, easing: "cubic-bezier(.2,.8,.2,1)" }
    );
    animation.onfinish = function () { finishFaqMotion(item, shouldOpen); };
    animation.oncancel = function () { finishFaqMotion(item, shouldOpen); };
  }

  faqItems.forEach(function (item) {
    var summary = item.querySelector("summary");
    if (!summary) return;
    summary.addEventListener("click", function (event) {
      event.preventDefault();
      var shouldOpen = !item.open;
      if (shouldOpen) {
        faqItems.forEach(function (other) {
          if (other !== item && other.open) animateFaq(other, false);
        });
      }
      animateFaq(item, shouldOpen);
    });
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

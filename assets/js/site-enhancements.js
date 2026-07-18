(function () {
  "use strict";

  var localSiteRoot = window.location.pathname.indexOf("/cab-it.ro/") === 0 ? "/cab-it.ro" : "";

  function siteUrl(path) {
    return localSiteRoot + path;
  }

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

  var simpleHeader = document.querySelector(".cabit-simple-header");
  if (simpleHeader) {
    simpleHeader.outerHTML =
      '<header class="cabit-unified-header">' +
        '<div class="cabit-unified-header__inner">' +
          '<a class="cabit-unified-logo" href="' + siteUrl("/") + '" aria-label="Cab-IT Expert — Acasă">' +
            '<img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert" width="560" height="195">' +
          '</a>' +
          '<details class="cabit-nav-drawer">' +
            '<summary aria-label="Deschide navigarea"><span></span><span></span><span></span></summary>' +
            '<div class="cabit-nav-panel">' +
              '<a href="' + siteUrl("/") + '">Acasă</a>' +
              '<a href="' + siteUrl("/despre-noi/") + '">Despre noi</a>' +
              '<a href="' + siteUrl("/servicii/") + '">Servicii</a>' +
              '<a href="' + siteUrl("/preturi/") + '">Prețuri</a>' +
              '<a href="' + siteUrl("/portofoliu/") + '">Portofoliu</a>' +
              '<a href="' + siteUrl("/blog/") + '">Blog</a>' +
              '<a href="' + siteUrl("/glosar-seo/") + '">Ghid SEO</a>' +
            '</div>' +
          '</details>' +
          '<nav class="cabit-unified-nav" aria-label="Navigare principală">' +
            '<a href="' + siteUrl("/") + '">Acasă</a>' +
            '<details class="cabit-nav-menu"><summary>Meniu</summary><div>' +
              '<a href="' + siteUrl("/despre-noi/") + '">Despre noi</a>' +
              '<a href="' + siteUrl("/servicii/") + '">Servicii</a>' +
              '<a href="' + siteUrl("/preturi/") + '">Prețuri</a>' +
              '<a href="' + siteUrl("/portofoliu/") + '">Portofoliu</a>' +
              '<a href="' + siteUrl("/blog/") + '">Blog</a>' +
              '<a href="' + siteUrl("/glosar-seo/") + '">Ghid SEO</a>' +
            '</div></details>' +
            '<a href="' + siteUrl("/contact/") + '">Contact</a>' +
          '</nav>' +
          '<div class="cabit-unified-actions">' +
            '<a href="' + siteUrl("/contact/") + '">Contact</a>' +
            '<a href="tel:+40771532949" aria-label="Apelează Cab-IT Expert">☎ Apelează</a>' +
          '</div>' +
        '</div>' +
      '</header>';
  }

  var simpleFooter = document.querySelector(".cabit-site-footer");
  if (simpleFooter) {
    var xIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.657l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/></svg>';
    var youtubeIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="4" fill="currentColor"/><path d="m10 9 6 3-6 3Z" fill="#101010"/></svg>';
    var instagramIcon = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>';
    simpleFooter.outerHTML =
      '<footer class="cabit-unified-footer">' +
        '<div class="container cabit-unified-footer__grid">' +
          '<section class="cabit-unified-footer__brand" aria-label="Cab-IT Expert">' +
            '<a href="' + siteUrl("/") + '"><img src="' + siteUrl("/img/logo_home.png") + '" alt="Cab-IT Expert" width="560" height="195"></a>' +
            '<p>Servicii de dezvoltare web și promovare online construite în jurul obiectivelor și datelor fiecărui client.</p>' +
            '<div class="cabit-unified-social">' +
              '<a href="https://www.youtube.com/@cabitexpert" aria-label="Cab-IT Expert pe YouTube">' + youtubeIcon + '</a>' +
              '<a href="https://www.facebook.com/profile.php?id=61592087996523" aria-label="Cab-IT Expert pe Facebook"><strong>f</strong></a>' +
              '<a href="https://www.instagram.com/cabitexpert/" aria-label="Cab-IT Expert pe Instagram">' + instagramIcon + '</a>' +
              '<a href="https://www.linkedin.com/company/cab-it-expert/" aria-label="Cab-IT Expert pe LinkedIn"><strong>in</strong></a>' +
              '<a href="https://x.com/cabitexpert" aria-label="Cab-IT Expert pe X">' + xIcon + '</a>' +
            '</div>' +
          '</section>' +
          '<section><h2>Linkuri rapide</h2><nav aria-label="Linkuri footer">' +
            '<a href="' + siteUrl("/") + '">Acasă</a>' +
            '<a href="' + siteUrl("/despre-noi/") + '">Despre noi</a>' +
            '<a href="' + siteUrl("/servicii/") + '">Servicii</a>' +
            '<a href="' + siteUrl("/portofoliu/") + '">Portofoliu</a>' +
            '<a href="' + siteUrl("/blog/") + '">Blog</a>' +
            '<a href="' + siteUrl("/glosar-seo/") + '">Ghid SEO</a>' +
            '<a href="' + siteUrl("/contact/") + '">Contact</a>' +
          '</nav></section>' +
          '<section><h2>Contact</h2><address>' +
            '<a href="https://www.google.com/maps/search/?api=1&amp;query=Intrarea+Humule%C8%99ti+6A+052034+Bucure%C8%99ti+Rom%C3%A2nia">Intrarea Humulești 6A,<br>052034 București, România</a>' +
            '<span>Program non-stop</span>' +
            '<a href="mailto:contact@cab-it.ro">contact@cab-it.ro</a>' +
            '<a href="tel:+40771532949">+40 771 532 949</a>' +
          '</address></section>' +
          '<section class="cabit-unified-footer__newsletter"><h2>Newsletter</h2>' +
            '<p>Primește ghiduri despre SEO, promovare online și website-uri, fără promisiuni spectaculoase.</p>' +
            '<form action="' + siteUrl("/newsletter-subscribe.php") + '" method="post"><label class="visually-hidden" for="cabit-footer-email">Adresa de email</label><input id="cabit-footer-email" name="email" type="email" placeholder="Adresa ta de email" autocomplete="email" required><input type="hidden" name="source" value="footer-pagini-noi"><input class="cabit-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"><button type="submit" aria-label="Abonează-te la newsletter">→</button></form>' +
          '</section>' +
        '</div>' +
        '<div class="container cabit-unified-footer__bottom"><span>©Copyright <span data-current-year>2026</span> All Rights Reserved</span><a href="' + siteUrl("/termeni-si-conditii/") + '">Termeni și condiții</a></div>' +
      '</footer>';
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
    fonts.href = "https://fonts.googleapis.com/css2?family=Montserrat+Alternates:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=optional";
    document.head.appendChild(fonts);
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

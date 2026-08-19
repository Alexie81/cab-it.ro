(function () {
  "use strict";

  var documentRoot = document;
  var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var track = documentRoot.querySelector("[data-project-track]");
  var previousButton = documentRoot.querySelector("[data-project-prev]");
  var nextButton = documentRoot.querySelector("[data-project-next]");
  var currentLabel = documentRoot.querySelector("[data-project-current]");
  var cards = track ? Array.prototype.slice.call(track.querySelectorAll(".web-project")) : [];
  var frame = 0;

  function cardStep() {
    if (!cards.length) return 0;
    var styles = window.getComputedStyle(track);
    var gap = parseFloat(styles.columnGap || styles.gap) || 0;
    return cards[0].getBoundingClientRect().width + gap;
  }

  function activeCardIndex() {
    var step = cardStep();
    if (!track || !step) return 0;
    return Math.max(0, Math.min(cards.length - 1, Math.round(track.scrollLeft / step)));
  }

  function updateCarousel() {
    frame = 0;
    if (!track || !cards.length) return;
    var index = activeCardIndex();
    var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    if (currentLabel) currentLabel.textContent = String(index + 1);
    if (previousButton) previousButton.disabled = track.scrollLeft <= 2;
    if (nextButton) nextButton.disabled = track.scrollLeft >= maxScroll - 2;
  }

  function moveCarousel(direction) {
    if (!track || !cards.length) return;
    var step = cardStep();
    var maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    var target = Math.max(0, Math.min(maxScroll, track.scrollLeft + (direction * step)));
    track.scrollTo({
      left: target,
      behavior: reducedMotion ? "auto" : "smooth"
    });
  }

  if (track && cards.length) {
    track.scrollLeft = 0;
    track.addEventListener("scroll", function () {
      if (frame) return;
      frame = window.requestAnimationFrame(updateCarousel);
    }, { passive: true });

    if (previousButton) {
      previousButton.addEventListener("click", function () {
        moveCarousel(-1);
      });
    }

    if (nextButton) {
      nextButton.addEventListener("click", function () {
        moveCarousel(1);
      });
    }

    window.addEventListener("resize", updateCarousel, { passive: true });
    window.addEventListener("load", function () {
      track.scrollLeft = 0;
      updateCarousel();
    }, { once: true });

    window.requestAnimationFrame(updateCarousel);
  }

  var revealItems = documentRoot.querySelectorAll("[data-web-reveal]");
  if (reducedMotion || !("IntersectionObserver" in window)) {
    revealItems.forEach(function (item) {
      item.classList.add("is-visible");
    });
  } else {
    var revealObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -35px" });

    revealItems.forEach(function (item) {
      revealObserver.observe(item);
    });
  }

  function sendInteraction(method, placement) {
    if (typeof window.gtag !== "function") return;
    window.gtag("event", "generate_lead", {
      method: method,
      placement: placement
    });
  }

  documentRoot.querySelectorAll('a[href^="tel:"]').forEach(function (link) {
    link.addEventListener("click", function () {
      sendInteraction("phone", link.getAttribute("data-placement") || "page");
    });
  });

  documentRoot.querySelectorAll('a[href*="wa.me"]').forEach(function (link) {
    link.addEventListener("click", function () {
      sendInteraction("whatsapp", link.getAttribute("data-placement") || "page");
    });
  });
})();

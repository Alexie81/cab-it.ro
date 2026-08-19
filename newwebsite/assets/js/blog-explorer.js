(function () {
  "use strict";

  var root = document.querySelector("[data-blog-explorer]");
  if (!root) return;

  var form = root.querySelector("[data-blog-search-form]");
  var input = root.querySelector("[data-blog-search]");
  var clearButton = root.querySelector("[data-blog-search-clear]");
  var suggestions = root.querySelector("[data-blog-suggestions]");
  var pageSize = root.querySelector("[data-blog-page-size]");
  var results = root.querySelector("[data-blog-results]");
  var resultCount = root.querySelector("[data-blog-result-count]");
  var resultContext = root.querySelector("[data-blog-result-context]");
  var pagination = root.querySelector("[data-blog-pagination]");
  var indexUrl = root.getAttribute("data-index-url") || "../blog-search-index.json";
  var articles = [];
  var filtered = [];
  var activeSuggestion = -1;
  var inputTimer = 0;
  var recentSearches = [];

  var synonymGroups = [
    ["site", "website", "web", "pagina"],
    ["magazin", "ecommerce", "comert", "shop"],
    ["promovare", "marketing", "reclame", "ads", "publicitate"],
    ["seo", "organic", "google", "cautare", "vizibilitate"],
    ["agentie", "firma", "echipa", "specialist"],
    ["cost", "pret", "buget", "tarif"],
    ["ai", "inteligenta", "artificiala", "automatizare"],
    ["client", "lead", "cerere", "conversie"],
    ["social", "facebook", "instagram", "tiktok", "meta"],
    ["masurare", "tracking", "analytics", "ga4", "raportare"]
  ];

  var mobileHead = document.createElement("div");
  mobileHead.className = "cabit-blog-search__mobile-head";
  mobileHead.innerHTML = '<button class="cabit-blog-search__mobile-close" type="button" aria-label="Închide căutarea">←</button><span>Căutare inteligentă CAB-IT</span>';
  form.insertBefore(mobileHead, form.firstChild);
  var mobileClose = mobileHead.querySelector("button");

  function isMobileSearch() {
    return window.matchMedia && window.matchMedia("(max-width: 680px)").matches;
  }

  function loadRecentSearches() {
    try {
      var stored = JSON.parse(window.localStorage.getItem("cabit-blog-recent-searches") || "[]");
      recentSearches = Array.isArray(stored) ? stored.filter(function (item) {
        return typeof item === "string" && normalize(item);
      }).slice(0, 5) : [];
    } catch (error) {
      recentSearches = [];
    }
  }

  function saveRecentSearch(query) {
    query = String(query || "").trim();
    if (normalize(query).length < 2) return;
    recentSearches = [query].concat(recentSearches.filter(function (item) {
      return normalize(item) !== normalize(query);
    })).slice(0, 5);
    try {
      window.localStorage.setItem("cabit-blog-recent-searches", JSON.stringify(recentSearches));
    } catch (error) {}
  }

  function openMobileSearch() {
    if (!isMobileSearch()) return;
    document.documentElement.classList.add("cabit-blog-search-open");
    document.body.classList.add("cabit-blog-search-open");
  }

  function closeMobileSearch() {
    document.documentElement.classList.remove("cabit-blog-search-open");
    document.body.classList.remove("cabit-blog-search-open");
    closeSuggestions();
    input.blur();
  }

  function normalize(value) {
    return String(value || "")
      .toLocaleLowerCase("ro-RO")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-z0-9]+/g, " ")
      .trim();
  }

  function escapeHtml(value) {
    return String(value || "").replace(/[&<>'"]/g, function (character) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[character];
    });
  }

  function tokens(value) {
    return normalize(value).split(/\s+/).filter(function (token) { return token.length > 1; });
  }

  function expandToken(token) {
    var expanded = [token];
    synonymGroups.forEach(function (group) {
      if (group.indexOf(token) !== -1) expanded = expanded.concat(group);
    });
    return expanded.filter(function (value, index, list) { return list.indexOf(value) === index; });
  }

  function editDistance(left, right) {
    if (left === right) return 0;
    if (!left.length) return right.length;
    if (!right.length) return left.length;
    var previous = Array.from({ length: right.length + 1 }, function (_, index) { return index; });
    for (var leftIndex = 1; leftIndex <= left.length; leftIndex++) {
      var current = [leftIndex];
      for (var rightIndex = 1; rightIndex <= right.length; rightIndex++) {
        var substitution = previous[rightIndex - 1] + (left[leftIndex - 1] === right[rightIndex - 1] ? 0 : 1);
        current[rightIndex] = Math.min(current[rightIndex - 1] + 1, previous[rightIndex] + 1, substitution);
      }
      previous = current;
    }
    return previous[right.length];
  }

  function fuzzyTokenScore(token, words) {
    if (token.length < 4) return 0;
    var threshold = token.length > 7 ? 2 : 1;
    var best = threshold + 1;
    words.some(function (word) {
      if (Math.abs(word.length - token.length) > threshold) return false;
      best = Math.min(best, editDistance(token, word));
      return best === 0;
    });
    return best <= threshold ? 28 - (best * 4) : 0;
  }

  function articleScore(article, rawQuery) {
    var query = normalize(rawQuery);
    if (!query) return 1;
    var queryTokens = tokens(query);
    var title = normalize(article.title);
    var keywords = normalize((article.keywords || []).join(" "));
    var cluster = normalize(article.cluster);
    var excerpt = normalize(article.excerpt);
    var searchText = normalize(article.search_text);
    var allWords = (title + " " + keywords + " " + cluster + " " + excerpt).split(/\s+/);
    var score = 0;

    if (title === query) score += 260;
    if (title.indexOf(query) === 0) score += 150;
    else if (title.indexOf(query) !== -1) score += 110;
    if (keywords.indexOf(query) !== -1) score += 80;
    if (cluster.indexOf(query) !== -1) score += 35;
    if (excerpt.indexOf(query) !== -1) score += 28;

    queryTokens.forEach(function (token) {
      var variants = expandToken(token);
      var matched = false;
      variants.forEach(function (variant) {
        if (title.split(" ").some(function (word) { return word === variant || word.indexOf(variant) === 0; })) {
          score += variant === token ? 34 : 18;
          matched = true;
        } else if (keywords.indexOf(variant) !== -1) {
          score += variant === token ? 24 : 13;
          matched = true;
        } else if (cluster.indexOf(variant) !== -1) {
          score += 15;
          matched = true;
        } else if (excerpt.indexOf(variant) !== -1) {
          score += 10;
          matched = true;
        } else if (searchText.indexOf(variant) !== -1) {
          score += 5;
          matched = true;
        }
      });
      if (!matched) score += fuzzyTokenScore(token, allWords);
    });

    var minimum = queryTokens.length <= 1 ? 12 : 45 + Math.max(0, queryTokens.length - 2) * 13;
    return score >= minimum ? score : 0;
  }

  function ranked(query) {
    if (!normalize(query)) return articles.slice();
    return articles.map(function (article, index) {
      return { article: article, score: articleScore(article, query), index: index };
    }).filter(function (item) {
      return item.score > 0;
    }).sort(function (left, right) {
      return right.score - left.score || String(right.article.date).localeCompare(String(left.article.date)) || left.index - right.index;
    }).map(function (item) { return item.article; });
  }

  function cardHtml(article) {
    return '<article class="cabit-blog-card">' +
      '<img src="' + escapeHtml(article.image) + '" alt="' + escapeHtml(article.image_alt || article.title) + '" width="600" height="315" loading="lazy" decoding="async">' +
      '<div class="cabit-blog-card__body"><div class="cabit-blog-card__meta"><span>' + escapeHtml(article.cluster || "Articol") + '</span><time datetime="' + escapeHtml(article.date) + '">' + escapeHtml(article.date_label) + '</time></div>' +
      '<h3>' + escapeHtml(article.title) + '</h3><p>' + escapeHtml(article.excerpt) + '</p>' +
      '<a class="cabit-text-link" href="' + escapeHtml(article.url) + '">Citește articolul <span aria-hidden="true">→</span></a></div></article>';
  }

  function currentState() {
    var parameters = new URLSearchParams(window.location.search);
    var requestedSize = parameters.get("per_page") || pageSize.value || "10";
    var perPage = requestedSize === "all" ? Math.max(1, filtered.length) : Math.max(1, Number(requestedSize) || 10);
    var page = Math.max(1, Number(parameters.get("page")) || 1);
    return { query: parameters.get("q") || input.value || "", requestedSize: requestedSize, perPage: perPage, page: page };
  }

  function pageHref(page, query, requestedSize) {
    var parameters = new URLSearchParams();
    if (normalize(query)) parameters.set("q", query.trim());
    if (page > 1) parameters.set("page", String(page));
    if (requestedSize !== "10") parameters.set("per_page", requestedSize);
    var suffix = parameters.toString();
    return window.location.pathname + (suffix ? "?" + suffix : "");
  }

  function paginationItems(current, total) {
    var items = [];
    for (var page = 1; page <= total; page++) {
      if (page === 1 || page === total || Math.abs(page - current) <= 2) items.push(page);
      else if (items[items.length - 1] !== "…") items.push("…");
    }
    return items;
  }

  function render(options) {
    options = options || {};
    var state = currentState();
    if (Object.prototype.hasOwnProperty.call(options, "query")) state.query = options.query;
    filtered = ranked(state.query);
    var totalPages = Math.max(1, Math.ceil(filtered.length / state.perPage));
    state.page = Math.min(state.page, totalPages);
    var start = (state.page - 1) * state.perPage;
    var visible = filtered.slice(start, start + state.perPage);
    input.value = state.query;
    pageSize.value = state.requestedSize === "all" ? "all" : (["10", "20", "50", "100"].indexOf(state.requestedSize) !== -1 ? state.requestedSize : "10");
    clearButton.hidden = !normalize(state.query);

    if (visible.length) {
      results.innerHTML = visible.map(cardHtml).join("");
    } else {
      results.innerHTML = '<div class="cabit-blog-empty"><h3>Nu am găsit un articol exact pentru această căutare.</h3><p>Încearcă o formulare mai scurtă, de exemplu „cost site”, „SEO local”, „Google Ads” sau „automatizare AI”.</p></div>';
    }

    resultCount.innerHTML = '<strong>' + filtered.length + '</strong> ' + (filtered.length === 1 ? "articol relevant" : "articole relevante");
    resultContext.textContent = state.query ? 'pentru „' + state.query.trim() + '”' : "ordonate de la cele mai recente";

    if (totalPages <= 1) {
      pagination.hidden = true;
      pagination.innerHTML = "";
    } else {
      var links = [];
      links.push(state.page > 1 ? '<a href="' + escapeHtml(pageHref(state.page - 1, state.query, state.requestedSize)) + '" data-blog-page="' + (state.page - 1) + '" aria-label="Pagina anterioară">←</a>' : '<span class="is-disabled" aria-hidden="true">←</span>');
      paginationItems(state.page, totalPages).forEach(function (page) {
        if (page === "…") links.push('<span class="is-disabled">…</span>');
        else links.push('<a href="' + escapeHtml(pageHref(page, state.query, state.requestedSize)) + '" data-blog-page="' + page + '"' + (page === state.page ? ' aria-current="page"' : '') + '>' + page + '</a>');
      });
      links.push(state.page < totalPages ? '<a href="' + escapeHtml(pageHref(state.page + 1, state.query, state.requestedSize)) + '" data-blog-page="' + (state.page + 1) + '" aria-label="Pagina următoare">→</a>' : '<span class="is-disabled" aria-hidden="true">→</span>');
      pagination.innerHTML = links.join("");
      pagination.hidden = false;
    }

    if (options.scroll) root.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  function setUrl(query, page, requestedSize, replace) {
    var url = pageHref(page, query, requestedSize);
    window.history[replace ? "replaceState" : "pushState"]({}, "", url);
  }

  function closeSuggestions() {
    suggestions.hidden = true;
    suggestions.innerHTML = "";
    activeSuggestion = -1;
    input.setAttribute("aria-expanded", "false");
  }

  function renderSuggestions() {
    var query = input.value.trim();
    if (normalize(query).length < 2) {
      closeSuggestions();
      return;
    }
    var matches = ranked(query).slice(0, isMobileSearch() ? 10 : 6);
    if (!matches.length) {
      closeSuggestions();
      return;
    }
    suggestions.innerHTML = matches.map(function (article, index) {
      return '<button class="cabit-blog-suggestion" type="button" role="option" aria-selected="false" data-suggestion="' + index + '" data-url="' + escapeHtml(article.url) + '">' +
        '<img src="' + escapeHtml(article.image) + '" alt="" width="48" height="42" loading="lazy"><span><strong>' + escapeHtml(article.title) + '</strong><small>' + escapeHtml(article.cluster || "Articol") + '</small></span><span class="cabit-blog-suggestion__arrow" aria-hidden="true">→</span></button>';
    }).join("");
    suggestions.hidden = false;
    input.setAttribute("aria-expanded", "true");
    activeSuggestion = -1;
  }

  function renderRecentSearches() {
    if (!isMobileSearch() || !recentSearches.length) {
      closeSuggestions();
      return;
    }
    suggestions.innerHTML = '<div class="cabit-blog-recent-heading"><span>Căutări recente</span><button type="button" data-clear-recents>Șterge toate</button></div>' + recentSearches.map(function (query, index) {
      return '<div class="cabit-blog-suggestion cabit-blog-recent" role="option"><span class="cabit-blog-recent__icon" aria-hidden="true">↺</span><button class="cabit-blog-recent__query" type="button" data-recent-query="' + index + '">' + escapeHtml(query) + '</button><button class="cabit-blog-recent__remove" type="button" aria-label="Șterge căutarea ' + escapeHtml(query) + '" data-remove-recent="' + index + '">×</button></div>';
    }).join("");
    suggestions.hidden = false;
    input.setAttribute("aria-expanded", "true");
  }

  function moveSuggestion(direction) {
    var options = Array.prototype.slice.call(suggestions.querySelectorAll("[data-suggestion]"));
    if (!options.length) return;
    activeSuggestion = (activeSuggestion + direction + options.length) % options.length;
    options.forEach(function (option, index) {
      var active = index === activeSuggestion;
      option.classList.toggle("is-active", active);
      option.setAttribute("aria-selected", String(active));
      if (active) option.scrollIntoView({ block: "nearest" });
    });
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    saveRecentSearch(input.value);
    if (isMobileSearch()) closeMobileSearch();
    else closeSuggestions();
    setUrl(input.value, 1, pageSize.value, false);
    render({ scroll: true });
  });

  input.addEventListener("input", function () {
    window.clearTimeout(inputTimer);
    clearButton.hidden = !normalize(input.value);
    inputTimer = window.setTimeout(function () {
      setUrl(input.value, 1, pageSize.value, true);
      render({ query: input.value });
      if (normalize(input.value).length >= 2) renderSuggestions();
      else renderRecentSearches();
    }, 180);
  });

  input.addEventListener("focus", function () {
    openMobileSearch();
    if (!articles.length) return;
    if (normalize(input.value).length >= 2) renderSuggestions();
    else renderRecentSearches();
  });

  input.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && document.body.classList.contains("cabit-blog-search-open")) {
      closeMobileSearch();
      return;
    }
    if (suggestions.hidden) return;
    if (event.key === "ArrowDown" || event.key === "ArrowUp") {
      event.preventDefault();
      moveSuggestion(event.key === "ArrowDown" ? 1 : -1);
    } else if (event.key === "Enter" && activeSuggestion >= 0) {
      event.preventDefault();
      var active = suggestions.querySelectorAll("[data-suggestion]")[activeSuggestion];
      if (active) window.location.href = active.getAttribute("data-url");
    } else if (event.key === "Escape") {
      closeSuggestions();
    }
  });

  clearButton.addEventListener("click", function () {
    var mobileWasOpen = document.body.classList.contains("cabit-blog-search-open");
    input.value = "";
    closeSuggestions();
    setUrl("", 1, pageSize.value, false);
    render();
    if (mobileWasOpen) {
      input.focus();
      renderRecentSearches();
    } else {
      input.blur();
    }
  });

  suggestions.addEventListener("click", function (event) {
    var suggestion = event.target.closest("[data-suggestion]");
    if (suggestion) {
      saveRecentSearch(input.value);
      window.location.href = suggestion.getAttribute("data-url");
      return;
    }
    var recent = event.target.closest("[data-recent-query]");
    if (recent) {
      input.value = recentSearches[Number(recent.getAttribute("data-recent-query"))] || "";
      setUrl(input.value, 1, pageSize.value, true);
      render();
      renderSuggestions();
      input.focus();
      return;
    }
    var remove = event.target.closest("[data-remove-recent]");
    if (remove) {
      recentSearches.splice(Number(remove.getAttribute("data-remove-recent")), 1);
      try { window.localStorage.setItem("cabit-blog-recent-searches", JSON.stringify(recentSearches)); } catch (error) {}
      renderRecentSearches();
      return;
    }
    if (event.target.closest("[data-clear-recents]")) {
      recentSearches = [];
      try { window.localStorage.removeItem("cabit-blog-recent-searches"); } catch (error) {}
      closeSuggestions();
    }
  });

  mobileClose.addEventListener("click", closeMobileSearch);

  pageSize.addEventListener("change", function () {
    setUrl(input.value, 1, pageSize.value, false);
    render({ scroll: true });
  });

  pagination.addEventListener("click", function (event) {
    var link = event.target.closest("[data-blog-page]");
    if (!link) return;
    event.preventDefault();
    setUrl(input.value, Number(link.getAttribute("data-blog-page")), pageSize.value, false);
    render({ scroll: true });
  });

  document.addEventListener("click", function (event) {
    if (!form.contains(event.target)) closeSuggestions();
  });
  window.addEventListener("popstate", function () { render(); });
  window.addEventListener("resize", function () {
    if (!isMobileSearch()) {
      document.documentElement.classList.remove("cabit-blog-search-open");
      document.body.classList.remove("cabit-blog-search-open");
    }
  }, { passive: true });

  loadRecentSearches();

  fetch(indexUrl, { credentials: "same-origin" })
    .then(function (response) {
      if (!response.ok) throw new Error("Indexul articolelor nu este disponibil.");
      return response.json();
    })
    .then(function (payload) {
      articles = Array.isArray(payload.articles) ? payload.articles : [];
      var parameters = new URLSearchParams(window.location.search);
      input.value = parameters.get("q") || "";
      pageSize.value = parameters.get("per_page") || "10";
      render();
    })
    .catch(function () {
      resultContext.textContent = "căutarea inteligentă este temporar indisponibilă; primele articole rămân accesibile";
      clearButton.hidden = true;
    });
})();

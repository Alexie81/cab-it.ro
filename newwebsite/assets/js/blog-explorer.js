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
  var currentScript = document.currentScript;
  if (currentScript && currentScript.src) {
    var assetVersion = new URL(currentScript.src, window.location.href).searchParams.get("v");
    if (assetVersion) {
      var versionedIndexUrl = new URL(indexUrl, window.location.href);
      versionedIndexUrl.searchParams.set("v", assetVersion);
      indexUrl = versionedIndexUrl.href;
    }
  }
  var articles = [];
  var articleSearchIndex = [];
  var filtered = [];
  var appliedQuery = "";
  var activeSuggestion = -1;
  var inputTimer = 0;
  var recentSearches = [];
  var rankCache = Object.create(null);
  var rankCacheKeys = [];

  var synonymGroups = [
    ["site", "website", "web", "pagina", "siteul", "siteuri", "websiteul", "websiteuri"],
    ["magazin", "ecommerce", "comert", "shop"],
    ["promovare", "marketing", "reclame", "ads", "publicitate"],
    ["seo", "organic", "google", "cautare", "vizibilitate"],
    ["agentie", "firma", "echipa", "specialist"],
    ["cost", "costul", "costuri", "costurile", "costa", "pret", "pretul", "preturi", "buget", "tarif"],
    ["ai", "inteligenta", "artificiala", "automatizare"],
    ["client", "lead", "cerere", "conversie"],
    ["social", "facebook", "instagram", "tiktok", "meta"],
    ["masurare", "tracking", "analytics", "ga4", "raportare"],
    ["prezentare", "prezentari", "prezentarii"]
  ];
  var synonymMap = Object.create(null);
  synonymGroups.forEach(function (group) {
    group.forEach(function (token) { synonymMap[token] = group; });
  });
  var stopWords = Object.create(null);
  "a ai al ale ca care cat cand ce cel cea cele cu cum daca de despre din e este fara fi fie in imi la mai o pe pentru pot poate prin sa sau se si sunt un unei unui unde vreau".split(" ").forEach(function (token) {
    stopWords[token] = true;
  });

  var mobileHead = document.createElement("div");
  mobileHead.className = "cabit-blog-search__mobile-head";
  mobileHead.innerHTML = '<button class="cabit-blog-search__mobile-close" type="button" aria-label="Înapoi la blog"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.5 6-6 6 6 6"></path></svg></button><span>Găsește răspunsul potrivit</span>';
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
    return synonymMap[token] || [token];
  }

  function prepareArticle(article, index) {
    var title = normalize(article.title);
    var keywords = normalize((article.keywords || []).join(" "));
    var cluster = normalize(article.cluster);
    var excerpt = normalize(article.excerpt);
    var queries = normalize((article.queries || []).join(" "));
    var entities = normalize((article.entities || []).join(" "));
    var localContext = normalize([article.city, article.county, article.region, article.industry].join(" "));
    var allWords = (title + " " + keywords + " " + cluster + " " + excerpt + " " + queries + " " + entities + " " + localContext)
      .split(/\s+/)
      .filter(function (word, wordIndex, words) { return word && words.indexOf(word) === wordIndex; });
    return {
      article: article,
      index: index,
      title: title,
      titleWords: title.split(/\s+/),
      keywords: keywords,
      cluster: cluster,
      excerpt: excerpt,
      searchText: normalize(article.search_text),
      queries: queries,
      entities: entities,
      semanticTerms: normalize((article.semantic_terms || []).join(" ")),
      boostTerms: normalize((article.boost_terms || []).join(" ")),
      localContext: localContext,
      directAnswer: normalize(article.direct_answer),
      allWords: allWords
    };
  }

  function buildSearchIndex() {
    articleSearchIndex = articles.map(prepareArticle);
    rankCache = Object.create(null);
    rankCacheKeys = [];
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

  function articleScore(prepared, query, queryTokens) {
    if (!query) return 1;
    var score = 0;
    var exactCoverage = 0;
    var exactTitleCoverage = 0;
    var semanticCoverage = 0;
    var semanticTitleCoverage = 0;

    if (prepared.title === query) score += 260;
    if (prepared.title.indexOf(query) === 0) score += 150;
    else if (prepared.title.indexOf(query) !== -1) score += 110;
    if (prepared.keywords.indexOf(query) !== -1) score += 80;
    if (prepared.queries.indexOf(query) !== -1) score += 95;
    if (prepared.boostTerms.indexOf(query) !== -1) score += 105;
    if (prepared.localContext.indexOf(query) !== -1) score += 60;
    if (prepared.entities.indexOf(query) !== -1) score += 42;
    if (prepared.cluster.indexOf(query) !== -1) score += 35;
    if (prepared.excerpt.indexOf(query) !== -1) score += 28;

    for (var phraseSize = Math.min(3, queryTokens.length); phraseSize >= 2; phraseSize--) {
      for (var phraseStart = 0; phraseStart <= queryTokens.length - phraseSize; phraseStart++) {
        var phrase = queryTokens.slice(phraseStart, phraseStart + phraseSize).join(" ");
        if (prepared.title.indexOf(phrase) !== -1) {
          score += phraseSize === 3 ? 120 : 48;
        } else if (prepared.queries.indexOf(phrase) !== -1 || prepared.boostTerms.indexOf(phrase) !== -1) {
          score += phraseSize === 3 ? 64 : 28;
        }
      }
    }

    queryTokens.forEach(function (token) {
      var variants = expandToken(token);
      var bestTokenScore = 0;
      var exactTitleMatch = prepared.titleWords.some(function (word) {
        return word === token || word.indexOf(token) === 0;
      });
      var exactStrongMatch = exactTitleMatch ||
        prepared.keywords.indexOf(token) !== -1 ||
        prepared.queries.indexOf(token) !== -1 ||
        prepared.boostTerms.indexOf(token) !== -1 ||
        prepared.cluster.indexOf(token) !== -1 ||
        prepared.entities.indexOf(token) !== -1;
      var semanticTitleMatch = variants.some(function (variant) {
        return prepared.titleWords.some(function (word) {
          return word === variant || word.indexOf(variant) === 0;
        });
      });
      var semanticStrongMatch = semanticTitleMatch || variants.some(function (variant) {
        return prepared.keywords.indexOf(variant) !== -1 ||
          prepared.queries.indexOf(variant) !== -1 ||
          prepared.boostTerms.indexOf(variant) !== -1 ||
          prepared.cluster.indexOf(variant) !== -1 ||
          prepared.entities.indexOf(variant) !== -1;
      });

      if (exactTitleMatch) exactTitleCoverage += 1;
      if (exactStrongMatch) exactCoverage += 1;
      if (semanticTitleMatch) semanticTitleCoverage += 1;
      if (semanticStrongMatch) semanticCoverage += 1;

      variants.forEach(function (variant) {
        var variantScore = 0;
        if (prepared.titleWords.some(function (word) { return word === variant || word.indexOf(variant) === 0; })) {
          variantScore = variant === token ? 38 : 16;
        } else if (prepared.keywords.indexOf(variant) !== -1) {
          variantScore = variant === token ? 27 : 11;
        } else if (prepared.queries.indexOf(variant) !== -1 || prepared.boostTerms.indexOf(variant) !== -1) {
          variantScore = variant === token ? 30 : 13;
        } else if (prepared.localContext.indexOf(variant) !== -1) {
          variantScore = variant === token ? 23 : 9;
        } else if (prepared.entities.indexOf(variant) !== -1 || prepared.semanticTerms.indexOf(variant) !== -1) {
          variantScore = variant === token ? 17 : 8;
        } else if (prepared.cluster.indexOf(variant) !== -1) {
          variantScore = variant === token ? 18 : 8;
        } else if (prepared.excerpt.indexOf(variant) !== -1) {
          variantScore = variant === token ? 12 : 6;
        } else if (prepared.searchText.indexOf(variant) !== -1) {
          variantScore = variant === token ? 7 : 3;
        } else if (prepared.directAnswer.indexOf(variant) !== -1) {
          variantScore = variant === token ? 9 : 4;
        }
        bestTokenScore = Math.max(bestTokenScore, variantScore);
      });
      if (!bestTokenScore) bestTokenScore = fuzzyTokenScore(token, prepared.allWords);
      score += bestTokenScore;
    });

    if (queryTokens.length) {
      if (exactTitleCoverage === queryTokens.length) score += 190;
      else if (exactTitleCoverage >= Math.ceil(queryTokens.length * .66)) score += 85;
      else score += exactTitleCoverage * 16;

      if (exactCoverage === queryTokens.length) score += 120;
      else if (exactCoverage >= Math.ceil(queryTokens.length * .66)) score += 48;
      else score += exactCoverage * 8;

      if (semanticTitleCoverage === queryTokens.length) score += 170;
      else if (semanticTitleCoverage >= Math.ceil(queryTokens.length * .66)) score += 72;
      else score += semanticTitleCoverage * 12;

      if (semanticCoverage === queryTokens.length) score += 95;
      else if (semanticCoverage >= Math.ceil(queryTokens.length * .66)) score += 38;
      else score += semanticCoverage * 6;
    }

    var minimum = queryTokens.length <= 1 ? 12 : 45 + Math.max(0, queryTokens.length - 2) * 13;
    return score >= minimum ? score : 0;
  }

  function ranked(query) {
    var normalizedQuery = normalize(query);
    if (!normalizedQuery) return articles.slice();
    if (rankCache[normalizedQuery]) return rankCache[normalizedQuery];
    var queryTokens = normalizedQuery.split(/\s+/).filter(function (token) {
      return token.length > 1 && !stopWords[token];
    });
    var rankedArticles = articleSearchIndex.map(function (prepared) {
      return { article: prepared.article, score: articleScore(prepared, normalizedQuery, queryTokens), index: prepared.index };
    }).filter(function (item) {
      return item.score > 0;
    }).sort(function (left, right) {
      return right.score - left.score || String(right.article.date).localeCompare(String(left.article.date)) || left.index - right.index;
    }).map(function (item) { return item.article; });
    rankCache[normalizedQuery] = rankedArticles;
    rankCacheKeys.push(normalizedQuery);
    if (rankCacheKeys.length > 40) delete rankCache[rankCacheKeys.shift()];
    return rankedArticles;
  }

  function cardHtml(article) {
    var cardLabel = article.cluster || "Articol";
    if (article.city && normalize(cardLabel).indexOf(normalize(article.city)) === -1) {
      cardLabel += " · " + article.city;
    }
    var topic = article.topic || "strategy";
    var topicLabels = { commerce: "E-commerce", local: "SEO local", ads: "Promovare", ai: "AI & automatizare", analytics: "Date & conversii", seo: "SEO", web: "Web design", strategy: "Strategie digitală" };
    var media = article.image
      ? '<div class="cabit-article-visual cabit-article-visual--card has-image"><img src="' + escapeHtml(article.image) + '" alt="' + escapeHtml(article.image_alt || article.title) + '" width="1200" height="630" loading="lazy" decoding="async"></div>'
      : '<div class="cabit-article-visual cabit-article-visual--card is-fallback is-theme-' + escapeHtml(topic) + '" role="img" aria-label="Reprezentare vizuală CAB-IT pentru ' + escapeHtml(article.title) + '"><span>Ghid CAB-IT</span>' + topicIcon(topic) + '<strong>' + escapeHtml(topicLabels[topic] || topicLabels.strategy) + '</strong><i aria-hidden="true"></i><b aria-hidden="true"></b></div>';
    return '<article class="cabit-blog-card">' +
      '<a class="cabit-blog-card__link" href="' + escapeHtml(article.url) + '">' + media +
      '<div class="cabit-blog-card__body"><div class="cabit-blog-card__meta"><span>' + escapeHtml(cardLabel) + '</span><time datetime="' + escapeHtml(article.date) + '">' + escapeHtml(article.date_label) + '</time></div>' +
      '<h3>' + escapeHtml(article.title) + '</h3><p>' + escapeHtml(article.excerpt) + '</p>' +
      '<span class="cabit-text-link">Citește articolul <span aria-hidden="true">→</span></span></div></a></article>';
  }

  function topicIcon(topic) {
    var icons = {
      commerce: '<svg viewBox="0 0 96 72" aria-hidden="true"><path d="M25 24h48l-4 37H29zM36 27c0-11 5-17 12-17s12 6 12 17M37 43h22M48 34v18"/></svg>',
      local: '<svg viewBox="0 0 96 72" aria-hidden="true"><path d="M18 58h60M24 58V31l24-13 24 13v27M34 39h8m12 0h8M34 49h8m12 0h8"/><circle cx="69" cy="18" r="9"/><path d="m76 25 8 8"/></svg>',
      ads: '<svg viewBox="0 0 96 72" aria-hidden="true"><circle cx="43" cy="36" r="24"/><circle cx="43" cy="36" r="14"/><circle cx="43" cy="36" r="4"/><path d="m61 20 18-10-7 20-11-10Z"/></svg>',
      ai: '<svg viewBox="0 0 96 72" aria-hidden="true"><rect x="24" y="15" width="48" height="42" rx="12"/><circle cx="39" cy="35" r="4"/><circle cx="57" cy="35" r="4"/><path d="M37 47h22M48 15V7M18 28h6m48 0h6M18 45h6m48 0h6"/></svg>',
      analytics: '<svg viewBox="0 0 96 72" aria-hidden="true"><path d="M18 58h62M24 54V41h12v13m8 0V29h12v25m8 0V17h12v37m-52-21 15-10 14 4 22-17"/></svg>',
      seo: '<svg viewBox="0 0 96 72" aria-hidden="true"><circle cx="39" cy="32" r="20"/><path d="m54 47 21 17M29 36l8-8 7 6 12-14"/></svg>',
      web: '<svg viewBox="0 0 96 72" aria-hidden="true"><rect x="12" y="12" width="62" height="43" rx="7"/><path d="M12 23h62M22 18h1m7 0h1m7 0h1M31 62h24M43 55v7"/><rect x="63" y="31" width="22" height="35" rx="5"/></svg>',
      strategy: '<svg viewBox="0 0 96 72" aria-hidden="true"><circle cx="24" cy="36" r="8"/><circle cx="48" cy="18" r="8"/><circle cx="72" cy="36" r="8"/><circle cx="48" cy="56" r="8"/><path d="m30 31 12-9m12 0 12 9m0 10-12 11m-12 0L30 41"/></svg>'
    };
    return icons[topic] || icons.strategy;
  }

  function currentState() {
    var parameters = new URLSearchParams(window.location.search);
    var requestedSize = parameters.get("per_page") || pageSize.value || "10";
    var perPage = requestedSize === "all" ? Math.max(1, filtered.length) : Math.max(1, Number(requestedSize) || 10);
    var page = Math.max(1, Number(parameters.get("page")) || 1);
    return { query: parameters.get("q") || appliedQuery, requestedSize: requestedSize, perPage: perPage, page: page };
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

  function suggestionHtml(article, index) {
    var suggestionMedia = article.image
      ? '<img src="' + escapeHtml(article.image) + '" alt="" width="48" height="42" loading="lazy">'
      : '<span class="cabit-blog-suggestion__fallback is-theme-' + escapeHtml(article.topic || "strategy") + '" aria-hidden="true">' + topicIcon(article.topic || "strategy") + '</span>';
    return '<button class="cabit-blog-suggestion" type="button" role="option" aria-selected="false" data-suggestion="' + index + '" data-url="' + escapeHtml(article.url) + '">' +
      suggestionMedia + '<span><strong>' + escapeHtml(article.title) + '</strong><small>' + escapeHtml(article.cluster || "Articol") + '</small></span><span class="cabit-blog-suggestion__arrow" aria-hidden="true">→</span></button>';
  }

  function recentRecommendations(limit) {
    var pools = recentSearches.map(function (query) {
      return ranked(query).slice(0, 12);
    }).filter(function (pool) { return pool.length; });
    var selected = [];
    var seen = Object.create(null);
    var depth = 0;

    while (selected.length < limit && depth < 12) {
      pools.forEach(function (pool) {
        var candidate = pool[depth];
        if (candidate && !seen[candidate.slug] && selected.length < limit) {
          seen[candidate.slug] = true;
          selected.push(candidate);
        }
      });
      depth++;
    }

    if (selected.length < limit) {
      articles.some(function (article) {
        if (!seen[article.slug]) {
          seen[article.slug] = true;
          selected.push(article);
        }
        return selected.length >= limit;
      });
    }
    return selected;
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
    suggestions.innerHTML = matches.map(suggestionHtml).join("");
    suggestions.hidden = false;
    input.setAttribute("aria-expanded", "true");
    activeSuggestion = -1;
  }

  function renderRecentSearches() {
    if (!articles.length) {
      closeSuggestions();
      return;
    }
    var recentMarkup = recentSearches.length
      ? '<div class="cabit-blog-recent-heading"><span>Căutări recente</span><button type="button" data-clear-recents>Șterge toate</button></div>' + recentSearches.map(function (query, index) {
        return '<div class="cabit-blog-suggestion cabit-blog-recent" role="option" data-recent-query="' + index + '"><span class="cabit-blog-recent__icon" aria-hidden="true">↺</span><button class="cabit-blog-recent__query" type="button">' + escapeHtml(query) + '</button><button class="cabit-blog-recent__remove" type="button" aria-label="Șterge căutarea ' + escapeHtml(query) + '" data-remove-recent="' + index + '">×</button></div>';
      }).join("")
      : '';
    var recommended = recentRecommendations(5);
    suggestions.innerHTML = recentMarkup + '<div class="cabit-blog-recent-heading cabit-blog-suggestion-heading"><span>Sugestii</span></div>' + recommended.map(suggestionHtml).join("");
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
    appliedQuery = input.value.trim();
    saveRecentSearch(input.value);
    if (isMobileSearch()) closeMobileSearch();
    else closeSuggestions();
    setUrl(appliedQuery, 1, pageSize.value, false);
    render({ query: appliedQuery, scroll: true });
  });

  input.addEventListener("input", function () {
    window.clearTimeout(inputTimer);
    clearButton.hidden = !normalize(input.value);
    inputTimer = window.setTimeout(function () {
      if (normalize(input.value).length >= 2) renderSuggestions();
      else renderRecentSearches();
    }, 35);
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
    } else if (event.key === "Enter") {
      event.preventDefault();
      form.requestSubmit();
    } else if (event.key === "Escape") {
      closeSuggestions();
    }
  });

  clearButton.addEventListener("click", function () {
    var mobileWasOpen = document.body.classList.contains("cabit-blog-search-open");
    input.value = "";
    appliedQuery = "";
    closeSuggestions();
    setUrl("", 1, pageSize.value, false);
    render();
    if (mobileWasOpen) {
      input.focus();
      renderRecentSearches();
    } else {
      input.focus({ preventScroll: true });
      renderRecentSearches();
    }
  });

  suggestions.addEventListener("click", function (event) {
    var suggestion = event.target.closest("[data-suggestion]");
    if (suggestion) {
      saveRecentSearch(input.value);
      window.location.href = suggestion.getAttribute("data-url");
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
      renderRecentSearches();
      return;
    }
    var recent = event.target.closest("[data-recent-query]");
    if (recent) {
      input.value = recentSearches[Number(recent.getAttribute("data-recent-query"))] || "";
      clearButton.hidden = !normalize(input.value);
      appliedQuery = input.value.trim();
      saveRecentSearch(appliedQuery);
      setUrl(appliedQuery, 1, pageSize.value, false);
      openMobileSearch();
      renderSuggestions();
      input.focus({ preventScroll: true });
      window.requestAnimationFrame(renderSuggestions);
    }
  });

  mobileClose.addEventListener("click", closeMobileSearch);

  pageSize.addEventListener("change", function () {
    setUrl(appliedQuery, 1, pageSize.value, false);
    render({ scroll: true });
  });

  pagination.addEventListener("click", function (event) {
    var link = event.target.closest("[data-blog-page]");
    if (!link) return;
    event.preventDefault();
    setUrl(appliedQuery, Number(link.getAttribute("data-blog-page")), pageSize.value, false);
    render({ scroll: true });
  });

  document.addEventListener("click", function (event) {
    if (!form.contains(event.target)) closeSuggestions();
  });
  window.addEventListener("popstate", function () {
    appliedQuery = new URLSearchParams(window.location.search).get("q") || "";
    input.value = appliedQuery;
    render();
  });
  window.addEventListener("resize", function () {
    if (!isMobileSearch()) {
      document.documentElement.classList.remove("cabit-blog-search-open");
      document.body.classList.remove("cabit-blog-search-open");
    }
  }, { passive: true });

  loadRecentSearches();

  fetch(indexUrl, { credentials: "same-origin", cache: "force-cache" })
    .then(function (response) {
      if (!response.ok) throw new Error("Indexul articolelor nu este disponibil.");
      return response.json();
    })
    .then(function (payload) {
      articles = Array.isArray(payload.articles) ? payload.articles : [];
      buildSearchIndex();
      var parameters = new URLSearchParams(window.location.search);
      appliedQuery = parameters.get("q") || "";
      input.value = appliedQuery;
      pageSize.value = parameters.get("per_page") || "10";
      render();
      if (document.activeElement === input) {
        if (normalize(input.value).length >= 2) renderSuggestions();
        else renderRecentSearches();
      }
    })
    .catch(function () {
      resultContext.textContent = "căutarea inteligentă este temporar indisponibilă; primele articole rămân accesibile";
      clearButton.hidden = true;
    });
})();

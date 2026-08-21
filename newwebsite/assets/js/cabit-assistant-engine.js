(function (global) {
  "use strict";

  if (global.CabitAssistantEngine) return;

  var doc = global.document;
  var runtimeScript = doc && (doc.currentScript || doc.querySelector('script[src*="cabit-assistant-engine"]'));
  var rootUrl = runtimeScript ? new URL("../../", runtimeScript.src) : new URL("/", global.location.href);
  var intentsPromise = null;

  var quickIntentHints = {
    website: "website_general",
    price: "website_price",
    seo: "seo_general",
    ads: "ads_general",
    portfolio: "website_portfolio",
    contact: "contact"
  };

  var synonymGroups = [
    ["site", "website", "web", "pagina"],
    ["pret", "preturi", "cost", "costa", "buget", "tarif"],
    ["magazin", "ecommerce", "shop", "comert"],
    ["reclame", "ads", "publicitate", "promovare", "campanie"],
    ["seo", "google", "organic", "vizibilitate", "pozitionare"],
    ["contact", "telefon", "email", "whatsapp", "apel"],
    ["proiecte", "portofoliu", "lucrari", "clienti"]
  ];

  function normalize(value) {
    return String(value || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, " ")
      .trim();
  }

  function terms(value) {
    var output = new Set(normalize(value).split(/\s+/).filter(function (word) { return word.length > 1; }));
    synonymGroups.forEach(function (group) {
      if (group.some(function (word) { return output.has(word); })) {
        group.forEach(function (word) { output.add(word); });
      }
    });
    return output;
  }

  function withRoot(path) {
    return new URL(String(path).replace(/^\/+/, ""), rootUrl).href;
  }

  function signalError(signal) {
    if (signal && signal.aborted) {
      var error = new Error("Solicitarea a fost oprită.");
      error.name = "AbortError";
      throw error;
    }
  }

  async function postJson(path, payload, signal) {
    signalError(signal);
    var response = await fetch(withRoot(path), {
      method: "POST",
      credentials: "same-origin",
      cache: "no-store",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CABIT-Chat": "1",
        "X-CABIT-Compact": "1"
      },
      body: JSON.stringify(payload),
      signal: signal
    });
    var contentType = response.headers.get("content-type") || "";
    if (!response.ok || contentType.indexOf("application/json") === -1) {
      throw new Error("Serviciul local nu este disponibil momentan.");
    }
    var result = await response.json();
    if (!result || result.ok === false) {
      var apiError = result && result.error;
      throw new Error((apiError && typeof apiError === "object" ? apiError.message : apiError) || "Răspuns indisponibil.");
    }
    return result;
  }

  function loadIntents() {
    if (!intentsPromise) {
      var requestOptions = { credentials: "same-origin", cache: "force-cache", headers: { "Accept": "application/json" } };
      var loadJson = function (path) {
        return fetch(withRoot(path), requestOptions).then(function (response) {
          if (!response.ok) return [];
          return response.json();
        }).then(function (items) {
          return Array.isArray(items) ? items : [];
        }).catch(function () { return []; });
      };
      intentsPromise = Promise.all([
        loadJson("ai/data/CAB_IT_intents_100_raspunsuri_ample.json"),
        loadJson("ai/data/intents.json"),
        loadJson("ai/data/CAB_IT_1000_INTENTII_INDUSTRII_SET2.json")
      ]).then(function (groups) {
        var seen = Object.create(null);
        return groups.reduce(function (items, group) {
          group.forEach(function (item) {
            var intent = String(item && item.intent || "");
            if (intent && !seen[intent]) {
              seen[intent] = true;
              items.push(item);
            }
          });
          return items;
        }, []);
      });
    }
    return intentsPromise;
  }

  function scoreIntent(query, item, hint) {
    if (hint && item.intent === hint) return 1000;
    var queryTerms = terms(query);
    var title = normalize(item.title);
    var intent = normalize(item.intent);
    var category = normalize(item.category);
    var haystack = terms([
      item.title,
      item.tags,
      item.category,
      item.facts,
      item.intent,
      item.industry,
      item.industry_id,
      item.topic,
      item.local_relevance,
      item.compliance_note
    ].join(" "));
    var score = 0;
    queryTerms.forEach(function (word) {
      if (haystack.has(word)) score += word.length >= 6 ? 5 : 3;
      if (title.indexOf(word) !== -1) score += 4;
      if (intent.indexOf(word) !== -1) score += 3;
      if (category.indexOf(word) !== -1) score += 2;
    });
    var phrase = normalize(query);
    if (title && phrase.indexOf(title) !== -1) score += 12;
    return score;
  }

  function actionsForIntent(intent) {
    if (intent === "contact") {
      return [
        { label: "Trimite email", href: "mailto:contact@cab-it.ro", kind: "email" },
        { label: "Sună acum", href: "tel:+40771532949", kind: "phone" },
        { label: "Scrie pe WhatsApp", href: "https://wa.me/40771532949?text=Bun%C4%83%2C%20a%C8%99%20dori%20mai%20multe%20detalii", kind: "whatsapp" }
      ];
    }
    return [];
  }

  async function fallbackReply(message, hint, signal) {
    signalError(signal);
    var items = await loadIntents();
    signalError(signal);
    var normalizedHint = quickIntentHints[hint] || hint || "";
    var ranked = items.map(function (item) {
      return { item: item, score: scoreIntent(message, item, normalizedHint) };
    }).sort(function (left, right) { return right.score - left.score; });
    var match = ranked[0];
    if (!match || match.score <= 0) {
      return {
        text: "Nu sunt sigur că am înțeles corect. Spune-mi pe scurt ce serviciu te interesează sau ce rezultat vrei să obții.",
        followup: "Poți și să discuți direct cu un specialist CAB-IT.",
        intent: "not_sure",
        confidence: 0,
        actions: [{ label: "Sună acum", href: "tel:+40771532949", kind: "phone" }]
      };
    }
    var item = match.item;
    return {
      text: item.canonical_answer_long || item.long_answer || item.answer || item.facts || "Am găsit o informație relevantă.",
      followup: item.followup || item.follow_up || "",
      source: item.source_url || item.source || "",
      intent: item.intent || "",
      confidence: Math.min(0.92, Math.max(0.22, match.score / 35)),
      actions: actionsForIntent(item.intent)
    };
  }

  function normalizeReply(result) {
    var payload = result && (result.reply || result.data || result);
    var intent = payload.intent || "";
    var sourceValue = payload.source || "";
    var source = payload.source_url || (sourceValue && typeof sourceValue === "object" ? sourceValue.url : sourceValue) || "";
    var actions = Array.isArray(payload.actions) ? payload.actions.map(function (action) {
      return {
        label: String(action.label || "Deschide"),
        href: String(action.href || ""),
        kind: String(action.kind || action.type || "link")
      };
    }).filter(function (action) { return action.href !== ""; }) : [];
    if (!actions.length) actions = actionsForIntent(intent);
    return {
      text: String(payload.text || payload.answer || payload.long_answer || ""),
      followup: String(payload.followup || payload.follow_up || ""),
      source: String(source || ""),
      intent: String(intent || ""),
      confidence: Number.isFinite(Number(payload.confidence)) ? Number(payload.confidence) : 0.7,
      actions: actions,
      context: payload.context || payload.text || payload.answer || "",
      localModel: false,
      localModelUsed: false,
      localModelFallback: false,
      localModelDevice: "",
      reason: "canonical_backend"
    };
  }

  async function reply(message, options) {
    options = options || {};
    var text = String(message || "").trim();
    if (!text) throw new Error("Scrie o întrebare înainte de trimitere.");
    if (typeof options.onLocalEnhancementState === "function") options.onLocalEnhancementState("understanding");

    var response;
    try {
      if (typeof options.onLocalEnhancementState === "function") options.onLocalEnhancementState("loading");
      response = normalizeReply(await postJson("ai/api/chat-reply.php", {
        message: text,
        intent_hint: quickIntentHints[options.intentHint] || options.intentHint || null,
        history: Array.isArray(options.history) ? options.history.slice(-80).map(function (item) {
          return {
            role: item && item.role === "assistant" ? "assistant" : "user",
            content: String(item && (item.content || item.text) || "").slice(0, 2000)
          };
        }).filter(function (item) { return item.content.trim(); }) : []
      }, options.signal));
    } catch (error) {
      if (error && error.name === "AbortError") throw error;
      response = normalizeReply(await fallbackReply(text, options.intentHint, options.signal));
      response.reason = "browser_canonical_fallback";
    }

    if (typeof options.onLocalEnhancementState === "function") options.onLocalEnhancementState("complete");
    return response;
  }

  async function syncConversation(conversation) {
    if (!conversation || !Array.isArray(conversation.messages) || !conversation.messages.length) return null;
    var serverId = conversation.serverId || conversation.server_id || "";
    var deleteToken = conversation.deleteToken || conversation.delete_token || "";
    var revision = Number(conversation.revision || 0);
    if (!serverId || !deleteToken) {
      var created = await postJson("ai/api/conversations.php", {
        action: "create",
        title: conversation.title || "Conversație CAB-IT",
        consent: { improvement: Boolean(conversation.improvementConsent), notice_version: "2026-08-21" }
      });
      var createdConversation = created.conversation || {};
      serverId = createdConversation.id || "";
      deleteToken = createdConversation.delete_token || "";
      revision = Number(createdConversation.revision || 1);
    }
    var synced = await postJson("ai/api/conversations.php", {
      action: "sync",
      conversation_id: serverId,
      delete_token: deleteToken,
      base_revision: revision || null,
      title: conversation.title || "Conversație CAB-IT",
      messages: conversation.messages.map(function (message) {
        return {
          id: message.id,
          role: message.role,
          content: message.text || message.content || "",
          created_at: message.createdAt || message.created_at || new Date().toISOString()
        };
      }),
      consent: { improvement: Boolean(conversation.improvementConsent), notice_version: "2026-08-21" }
    });
    var syncedConversation = synced.conversation || {};
    return {
      ok: true,
      serverId: serverId,
      deleteToken: deleteToken,
      revision: Number(syncedConversation.revision || revision + 1),
      conversation: syncedConversation
    };
  }

  async function deleteConversation(serverId, deleteToken) {
    if (!serverId || !deleteToken) return { ok: true, local_only: true };
    return postJson("ai/api/conversations.php", {
      action: "delete",
      conversation_id: serverId,
      delete_token: deleteToken
    });
  }

  global.CabitAssistantEngine = {
    version: "2.0.0",
    reply: reply,
    syncConversation: syncConversation,
    deleteConversation: deleteConversation
  };
})(window);

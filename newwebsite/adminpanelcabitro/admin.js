(function () {
  "use strict";

  var popup = document.getElementById("admin-error-popup");
  var popupMessage = popup ? popup.querySelector("[data-popup-message]") : null;
  var lastInvalidField = null;

  function openPopup(message, invalidField) {
    if (!popup || !popupMessage) {
      window.alert(message);
      return;
    }
    lastInvalidField = invalidField || null;
    popupMessage.textContent = message;
    popup.hidden = false;
    popup.setAttribute("aria-hidden", "false");
    document.body.classList.add("admin-popup-open");
    var closeButton = popup.querySelector(".admin-popup__close");
    if (closeButton) {
      closeButton.focus();
    }
  }

  function closePopup() {
    if (!popup) {
      return;
    }
    popup.hidden = true;
    popup.setAttribute("aria-hidden", "true");
    document.body.classList.remove("admin-popup-open");
    if (lastInvalidField) {
      lastInvalidField.focus({ preventScroll: true });
      lastInvalidField.scrollIntoView({ behavior: "smooth", block: "center" });
      lastInvalidField = null;
    }
  }

  document.querySelectorAll("[data-popup-close]").forEach(function (button) {
    button.addEventListener("click", closePopup);
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && popup && !popup.hidden) {
      closePopup();
    }
  });

  if (popup && !popup.hidden) {
    document.body.classList.add("admin-popup-open");
  }

  var menuButton = document.querySelector(".admin-menu-button");
  if (menuButton) {
    menuButton.addEventListener("click", function () {
      document.body.classList.toggle("admin-menu-open");
    });
  }

  function escapeEditorHtml(value) {
    return String(value || "").replace(/[&<>\"]/g, function (character) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[character];
    });
  }

  function prettyEditorHtml(html) {
    var formatted = String(html || "").replace(/>\s*</g, ">\n<");
    var depth = 0;
    return formatted.split("\n").map(function (line) {
      var clean = line.trim();
      if (/^<\//.test(clean)) depth = Math.max(0, depth - 1);
      var output = new Array(depth + 1).join("  ") + clean;
      if (/^<[^!/][^>]*[^/]>/i.test(clean) && !/<\/(?:[^>]+)>$/.test(clean) && !/^<(?:br|hr|img|input|meta|link)\b/i.test(clean)) depth += 1;
      return output;
    }).join("\n");
  }

  function initRichEditor(original) {
    var editorForm = original.form;
    var wrapper = document.createElement("div");
    wrapper.className = "rich-editor";
    wrapper.innerHTML =
      '<div class="rich-editor__head"><div><strong>' + escapeEditorHtml(original.dataset.editorTitle || "Editor avansat") + '</strong><small>Vizual + HTML + previzualizare</small></div><div class="rich-editor__modes"><button type="button" data-editor-view="visual" class="is-active">Vizual</button><button type="button" data-editor-view="html">HTML</button><button type="button" data-editor-view="preview">Previzualizare</button></div></div>' +
      '<div class="rich-editor__toolbar" role="toolbar" aria-label="Instrumente editor">' +
        '<select data-editor-format aria-label="Stil paragraf"><option value="p">Paragraf</option><option value="h2">Titlu H2</option><option value="h3">Titlu H3</option><option value="h4">Titlu H4</option><option value="blockquote">Citat</option><option value="pre">Cod</option></select>' +
        '<span><button type="button" data-editor-command="bold" title="Aldin"><b>B</b></button><button type="button" data-editor-command="italic" title="Cursiv"><em>I</em></button><button type="button" data-editor-command="underline" title="Subliniat"><u>U</u></button><button type="button" data-editor-command="strikeThrough" title="Tăiat"><s>S</s></button></span>' +
        '<span><button type="button" data-editor-command="insertUnorderedList" title="Listă">• Listă</button><button type="button" data-editor-command="insertOrderedList" title="Listă numerotată">1. Listă</button></span>' +
        '<span><button type="button" data-editor-command="justifyLeft" title="Aliniere stânga">⇤</button><button type="button" data-editor-command="justifyCenter" title="Centrare">↔</button><button type="button" data-editor-command="justifyRight" title="Aliniere dreapta">⇥</button></span>' +
        '<span><button type="button" data-editor-action="link">Link</button><button type="button" data-editor-action="image">Imagine</button><button type="button" data-editor-action="table">Tabel</button><button type="button" data-editor-command="insertHorizontalRule">Linie</button></span>' +
        '<select data-editor-snippet aria-label="Inserează bloc"><option value="">+ Bloc modern</option><option value="lead">Introducere mare</option><option value="note">Casetă informativă</option><option value="cta">CTA turcoaz</option><option value="columns">Două coloane</option><option value="steps">Pași numerotați</option></select>' +
        '<span><button type="button" data-editor-command="undo" title="Anulează">↶</button><button type="button" data-editor-command="redo" title="Refă">↷</button><button type="button" data-editor-command="removeFormat" title="Elimină formatarea">Curăță</button><button type="button" data-editor-action="beautify" title="Formatează codul HTML">Aranjează HTML</button><button type="button" data-editor-action="fullscreen" title="Ecran complet">⛶</button></span>' +
      '</div>' +
      '<div class="rich-editor__stage"><div class="rich-editor__visual cabit-rich-content" contenteditable="true" data-editor-canvas></div><textarea class="rich-editor__source" data-editor-source spellcheck="false" aria-label="Cod HTML"></textarea><iframe class="rich-editor__preview" data-editor-preview title="Previzualizare conținut" sandbox=""></iframe></div>' +
      '<div class="rich-editor__status"><span data-editor-count>0 cuvinte · 0 caractere</span><span>HTML securizat automat la salvare</span></div>' +
      '<input type="file" accept="image/png,image/jpeg,image/webp,image/gif" data-editor-file hidden>';

    original.parentNode.insertBefore(wrapper, original);
    original.classList.add("rich-editor__original");
    var canvas = wrapper.querySelector("[data-editor-canvas]");
    var source = wrapper.querySelector("[data-editor-source]");
    var preview = wrapper.querySelector("[data-editor-preview]");
    var fileInput = wrapper.querySelector("[data-editor-file]");
    var counter = wrapper.querySelector("[data-editor-count]");
    canvas.innerHTML = original.value || "<p>Începe să scrii aici…</p>";
    source.value = original.value || canvas.innerHTML;

    function updateCount() {
      var plain = (canvas.innerText || "").trim();
      var words = plain ? plain.split(/\s+/).length : 0;
      counter.textContent = words + " cuvinte · " + plain.length + " caractere";
    }

    function syncFromVisual() {
      original.value = canvas.innerHTML;
      source.value = canvas.innerHTML;
      updateCount();
    }

    function syncFromSource() {
      canvas.innerHTML = source.value;
      original.value = source.value;
      updateCount();
    }

    function refreshPreview() {
      syncFromSource();
      preview.srcdoc = '<!doctype html><html lang="ro"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{max-width:900px;margin:0 auto;padding:35px;color:#101828;font:17px/1.7 Inter,Arial,sans-serif}h2,h3,h4{font-family:Arial,sans-serif;line-height:1.2}img{max-width:100%;height:auto;border-radius:16px}blockquote{margin:25px 0;padding:20px 24px;border-left:4px solid #00a99d;background:#eefbf9}table{width:100%;border-collapse:collapse}td,th{padding:12px;border:1px solid #dce5e9}.cabit-rich-note{padding:22px;border-radius:16px;background:#eefbf9}.cabit-rich-cta{padding:28px;border-radius:20px;color:white;background:#008f84}.cabit-rich-cta a{color:white}.cabit-rich-columns{display:grid;grid-template-columns:1fr 1fr;gap:22px}@media(max-width:600px){.cabit-rich-columns{grid-template-columns:1fr}}</style></head><body>' + canvas.innerHTML + '</body></html>';
    }

    function insertHtml(html) {
      canvas.focus();
      document.execCommand("insertHTML", false, html);
      syncFromVisual();
    }

    canvas.addEventListener("input", syncFromVisual);
    canvas.addEventListener("blur", syncFromVisual);
    source.addEventListener("input", syncFromSource);

    wrapper.querySelectorAll("[data-editor-command]").forEach(function (button) {
      button.addEventListener("click", function () {
        canvas.focus();
        document.execCommand(button.dataset.editorCommand, false, null);
        syncFromVisual();
      });
    });

    wrapper.querySelector("[data-editor-format]").addEventListener("change", function (event) {
      canvas.focus();
      document.execCommand("formatBlock", false, event.target.value);
      syncFromVisual();
    });

    wrapper.querySelector("[data-editor-snippet]").addEventListener("change", function (event) {
      var snippets = {
        lead: '<p class="cabit-rich-lead"><strong>Scrie aici introducerea principală.</strong></p>',
        note: '<div class="cabit-rich-note"><h3>De reținut</h3><p>Adaugă aici informația importantă.</p></div>',
        cta: '<div class="cabit-rich-cta"><h3>Ai nevoie de ajutor?</h3><p>Explică beneficiul și pasul următor.</p><p><a class="cabit-rich-button" href="/#audit">Cere un audit gratuit</a></p></div>',
        columns: '<div class="cabit-rich-columns"><div><h3>Coloana unu</h3><p>Conținut...</p></div><div><h3>Coloana doi</h3><p>Conținut...</p></div></div>',
        steps: '<ol><li><strong>Primul pas</strong><p>Descriere...</p></li><li><strong>Al doilea pas</strong><p>Descriere...</p></li><li><strong>Al treilea pas</strong><p>Descriere...</p></li></ol>'
      };
      if (snippets[event.target.value]) insertHtml(snippets[event.target.value]);
      event.target.value = "";
    });

    wrapper.querySelectorAll("[data-editor-view]").forEach(function (button) {
      button.addEventListener("click", function () {
        var view = button.dataset.editorView;
        if (view === "visual") syncFromSource();
        if (view === "html") syncFromVisual();
        if (view === "preview") refreshPreview();
        wrapper.dataset.view = view;
        wrapper.querySelectorAll("[data-editor-view]").forEach(function (item) { item.classList.toggle("is-active", item === button); });
      });
    });

    wrapper.querySelector('[data-editor-action="link"]').addEventListener("click", function () {
      var href = window.prompt("Adresa linkului (https://... sau /pagina/):", "https://");
      if (!href) return;
      canvas.focus();
      document.execCommand("createLink", false, href);
      syncFromVisual();
    });

    wrapper.querySelector('[data-editor-action="table"]').addEventListener("click", function () {
      var rows = Math.min(10, Math.max(1, parseInt(window.prompt("Număr de rânduri:", "3"), 10) || 3));
      var columns = Math.min(6, Math.max(1, parseInt(window.prompt("Număr de coloane:", "2"), 10) || 2));
      var html = "<table><thead><tr>";
      for (var column = 0; column < columns; column += 1) html += "<th>Titlu " + (column + 1) + "</th>";
      html += "</tr></thead><tbody>";
      for (var row = 0; row < rows; row += 1) {
        html += "<tr>";
        for (var cell = 0; cell < columns; cell += 1) html += "<td>Conținut</td>";
        html += "</tr>";
      }
      insertHtml(html + "</tbody></table><p><br></p>");
    });

    wrapper.querySelector('[data-editor-action="beautify"]').addEventListener("click", function () {
      syncFromVisual();
      source.value = prettyEditorHtml(source.value);
      syncFromSource();
      wrapper.dataset.view = "html";
      wrapper.querySelectorAll("[data-editor-view]").forEach(function (item) { item.classList.toggle("is-active", item.dataset.editorView === "html"); });
    });

    wrapper.querySelector('[data-editor-action="fullscreen"]').addEventListener("click", function () {
      wrapper.classList.toggle("is-fullscreen");
      document.body.classList.toggle("rich-editor-open", wrapper.classList.contains("is-fullscreen"));
    });

    wrapper.querySelector('[data-editor-action="image"]').addEventListener("click", function () { fileInput.click(); });
    fileInput.addEventListener("change", async function () {
      if (!fileInput.files || !fileInput.files[0] || !editorForm) return;
      var uploadData = new FormData();
      uploadData.append("action", "upload_editor_image");
      uploadData.append("editor_image", fileInput.files[0]);
      var csrf = editorForm.querySelector('input[name="csrf"]');
      if (csrf) uploadData.append("csrf", csrf.value);
      wrapper.classList.add("is-uploading");
      try {
        var uploadResponse = await fetch(window.location.href, { method: "POST", body: uploadData, credentials: "same-origin", headers: { "X-Requested-With": "XMLHttpRequest" } });
        var uploadResult = await uploadResponse.json();
        if (!uploadResponse.ok || !uploadResult.ok || !uploadResult.url) throw new Error(uploadResult.message || "Imaginea nu a putut fi încărcată.");
        var altText = window.prompt("Text alternativ pentru SEO și accesibilitate:", fileInput.files[0].name.replace(/\.[^.]+$/, "")) || "Imagine articol CAB-IT";
        insertHtml('<figure><img src="' + escapeEditorHtml(uploadResult.url) + '" alt="' + escapeEditorHtml(altText) + '" loading="lazy"><figcaption>Descriere opțională a imaginii</figcaption></figure><p><br></p>');
      } catch (uploadError) {
        openPopup(uploadError.message || "Imaginea nu a putut fi încărcată.");
      } finally {
        wrapper.classList.remove("is-uploading");
        fileInput.value = "";
      }
    });

    if (editorForm) {
      editorForm.addEventListener("submit", syncFromVisual, true);
      editorForm.addEventListener("formdata", syncFromVisual);
    }
    wrapper.dataset.view = "visual";
    updateCount();
  }

  document.querySelectorAll("textarea[data-rich-editor]").forEach(initRichEditor);

  function value(form, name) {
    var field = form.elements.namedItem(name);
    return field ? String(field.value || "").trim() : "";
  }

  function field(form, name) {
    return form.elements.namedItem(name);
  }

  function validateForm(form) {
    var action = value(form, "action");
    var checks = [];

    if (action === "save_article") {
      checks = [
        ["title", 3, "Titlul articolului trebuie să aibă cel puțin 3 caractere."],
        ["seo_title", 10, "SEO Title trebuie să aibă cel puțin 10 caractere."],
        ["meta_description", 50, "Descrierea SEO trebuie să aibă cel puțin 50 de caractere."],
        ["excerpt", 20, "Rezumatul pentru card trebuie să aibă cel puțin 20 de caractere."],
        ["content", 1, "Adaugă textul articolului înainte de publicare."]
      ];
    } else if (action === "save_work") {
      checks = [
        ["title", 3, "Numele lucrării trebuie să aibă cel puțin 3 caractere."],
        ["seo_title", 10, "SEO Title trebuie să aibă cel puțin 10 caractere."],
        ["meta_description", 50, "Descrierea SEO trebuie să aibă cel puțin 50 de caractere."],
        ["objective", 20, "Obiectivul inițial trebuie să aibă cel puțin 20 de caractere."],
        ["work_done", 20, "Descrie ce ai făcut în cel puțin 20 de caractere."],
        ["results", 20, "Rezultatele și măsurarea trebuie să aibă cel puțin 20 de caractere."]
      ];
    } else if (action === "save_category") {
      checks = [["name", 2, "Numele categoriei trebuie să aibă cel puțin 2 caractere."]];
    } else if (action === "change_password") {
      if (value(form, "new_password").length < 12) {
        return { message: "Parola nouă trebuie să aibă minimum 12 caractere.", field: field(form, "new_password") };
      }
      if (value(form, "new_password") !== value(form, "confirm_password")) {
        return { message: "Confirmarea nu este identică cu parola nouă.", field: field(form, "confirm_password") };
      }
    }

    for (var index = 0; index < checks.length; index += 1) {
      if (value(form, checks[index][0]).length < checks[index][1]) {
        return { message: checks[index][2], field: field(form, checks[index][0]) };
      }
    }

    var slug = field(form, "slug");
    if (slug && value(form, "slug") && !/^[a-z0-9-]+$/.test(value(form, "slug"))) {
      return { message: "Slugul poate conține doar litere mici fără diacritice, cifre și cratime.", field: slug };
    }

    var externalUrl = field(form, "external_url");
    if (externalUrl && externalUrl.value && !externalUrl.validity.valid) {
      return { message: "Adresa website-ului extern nu este validă.", field: externalUrl };
    }

    var nativeInvalid = form.querySelector(":invalid");
    if (nativeInvalid) {
      return { message: "Completează câmpul evidențiat înainte de salvare.", field: nativeInvalid };
    }

    return null;
  }

  document.querySelectorAll("form.admin-form").forEach(function (form) {
    form.setAttribute("novalidate", "novalidate");
    form.addEventListener("submit", async function (event) {
      event.preventDefault();

      var validationError = validateForm(form);
      if (validationError) {
        openPopup(validationError.message, validationError.field);
        return;
      }

      var submitButton = form.querySelector('button[type="submit"]');
      var originalLabel = submitButton ? submitButton.textContent : "";
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Se salvează…";
      }

      try {
        var response = await fetch(form.action || window.location.href, {
          method: "POST",
          body: new FormData(form),
          headers: { "X-Requested-With": "XMLHttpRequest" },
          credentials: "same-origin"
        });
        var responseText = await response.text();
        var result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          throw new Error("Serverul nu a returnat un răspuns valid. Datele din formular au rămas neschimbate.");
        }

        if (!response.ok || !result.ok) {
          openPopup(result.message || "Nu am putut salva. Verifică datele și încearcă din nou.");
          return;
        }

        window.location.assign(result.redirect || window.location.href);
      } catch (requestError) {
        openPopup(requestError.message || "Conexiunea cu serverul a eșuat. Modificările din formular sunt încă aici.");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = originalLabel;
        }
      }
    });
  });
}());

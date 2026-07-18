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

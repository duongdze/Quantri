document.addEventListener("DOMContentLoaded", function () {
  // ===== TOURS INDEX PAGE FUNCTIONALITY =====

  // Filter form handling with CLIENT-SIDE filtering (giống Booking)
  const filterForm = document.getElementById("tour-filters");
  if (filterForm) {
    // Prevent form submit and filter manually
    filterForm.addEventListener("submit", function(e) {
      e.preventDefault();
      filterTours();
    });

    function filterTours() {
      const keyword = filterForm.querySelector('[name="keyword"]').value.toLowerCase();
      const categoryId = filterForm.querySelector('[name="category_id"]').value;
      const status = filterForm.querySelector('[name="status"]').value;
      const sortBy = filterForm.querySelector('[name="sort_by"]').value;
      const sortDir = filterForm.querySelector('[name="sort_dir"]').value;

      const toursGrid = document.querySelector('.tours-grid');
      if (!toursGrid) return;

      const tourCards = Array.from(toursGrid.querySelectorAll('.tour-card-modern'));

      // Filter cards
      let filteredCards = tourCards.filter(card => {
        const tourName = card.querySelector('.tour-title').textContent.toLowerCase();
        const categoryBadge = card.querySelector('.category-badge');
        const tourCategory = categoryBadge ? categoryBadge.textContent.trim() : '';
        const statusBadge = card.querySelector('.badge-status');
        const tourStatus = statusBadge ? statusBadge.textContent.trim() : '';

        // Filter by keyword
        if (keyword && !tourName.includes(keyword)) {
          return false;
        }

        // Filter by category
        if (categoryId) {
          const selectedCategoryName = filterForm.querySelector(`[name="category_id"] option[value="${categoryId}"]`).textContent;
          if (tourCategory !== selectedCategoryName) {
            return false;
          }
        }

        // Filter by status
        if (status) {
          const statusMap = {
            'active': 'Đang hoạt động',
            'inactive': 'Tạm ẩn'
          };
          if (tourStatus !== statusMap[status]) {
            return false;
          }
        }

        return true;
      });

      // Sort cards
      if (sortBy) {
        filteredCards.sort((a, b) => {
          let aVal, bVal;
          if (sortBy === 'name') {
            aVal = a.querySelector('.tour-title').textContent.toLowerCase();
            bVal = b.querySelector('.tour-title').textContent.toLowerCase();
          } else if (sortBy === 'price') {
            aVal = parseFloat(a.querySelector('.price-value').textContent.replace(/[^\d]/g, '') || 0);
            bVal = parseFloat(b.querySelector('.price-value').textContent.replace(/[^\d]/g, '') || 0);
          } else if (sortBy === 'rating') {
            const aRating = a.querySelector('.rating-value');
            const bRating = b.querySelector('.rating-value');
            aVal = aRating ? parseFloat(aRating.textContent || 0) : 0;
            bVal = bRating ? parseFloat(bRating.textContent || 0) : 0;
          }
          return sortDir === 'ASC' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1);
        });
      }

      // Hide all cards
      tourCards.forEach(card => card.style.display = 'none');

      // Show filtered cards
      filteredCards.forEach(card => card.style.display = '');

      // Update count
      const countElement = document.querySelector('.tours-count .count-info');
      if (countElement) {
        countElement.textContent = `${filteredCards.length} tour`;
      }
    }
  }

  // ===== Thumbnail click and Lightbox =====
  function createLightbox() {
    // remove existing lightbox if present so we recreate clean handlers
    const existing = document.getElementById("image-lightbox");
    if (existing) existing.remove();

    const lb = document.createElement("div");
    lb.id = "image-lightbox";
    lb.className = "image-lightbox";
    lb.innerHTML =
      '\n      <div class="lightbox-inner">\n        <button class="lightbox-close" aria-label="Close">&times;</button>\n        <button class="lightbox-prev" aria-label="Previous">&#8249;</button>\n        <div class="lightbox-media">\n          <img class="lightbox-image" src="" alt="">\n        </div>\n        <div class="lightbox-thumbs" aria-hidden="false"></div>\n        <button class="lightbox-next" aria-label="Next">&#8250;</button>\n      </div>';
    document.body.appendChild(lb);

    const img = lb.querySelector(".lightbox-image");
    const btnClose = lb.querySelector(".lightbox-close");
    const btnPrev = lb.querySelector(".lightbox-prev");
    const btnNext = lb.querySelector(".lightbox-next");
    const thumbsContainer = lb.querySelector(".lightbox-thumbs");

    let currentGallery = null;
    let currentIndex = 0;

    function renderThumbs(gallery, activeIdx) {
      thumbsContainer.innerHTML = "";
      if (!gallery || !gallery.length) return;
      gallery.forEach(function (src, i) {
        const div = document.createElement("div");
        div.className = "thumb" + (i === activeIdx ? " active" : "");
        const timg = document.createElement("img");
        timg.src = src;
        timg.alt = "thumb-" + i;
        timg.dataset.index = i;
        timg.addEventListener("click", function (e) {
          e.stopPropagation();
          currentIndex = i;
          img.src = src;
          // update active
          thumbsContainer.querySelectorAll(".thumb").forEach(function (t) {
            t.classList.remove("active");
          });
          div.classList.add("active");
        });
        div.appendChild(timg);
        thumbsContainer.appendChild(div);
      });
    }

    function close() {
      lb.classList.remove("open");
      img.removeAttribute("src");
      currentGallery = null;
      currentIndex = 0;
      thumbsContainer.innerHTML = "";
    }

    btnClose.addEventListener("click", close);
    lb.addEventListener("click", function (e) {
      if (e.target === lb) close();
    });

    btnPrev.addEventListener("click", function (e) {
      e.stopPropagation();
      if (!currentGallery || !currentGallery.length) return;
      currentIndex =
        (currentIndex - 1 + currentGallery.length) % currentGallery.length;
      img.src = currentGallery[currentIndex];
      // update active thumb
      thumbsContainer.querySelectorAll(".thumb").forEach(function (t) {
        t.classList.remove("active");
      });
      const active = thumbsContainer.querySelector(
        ".thumb:nth-child(" + (currentIndex + 1) + ")"
      );
      if (active) active.classList.add("active");
    });

    btnNext.addEventListener("click", function (e) {
      e.stopPropagation();
      if (!currentGallery || !currentGallery.length) return;
      currentIndex = (currentIndex + 1) % currentGallery.length;
      img.src = currentGallery[currentIndex];
      thumbsContainer.querySelectorAll(".thumb").forEach(function (t) {
        t.classList.remove("active");
      });
      const active = thumbsContainer.querySelector(
        ".thumb:nth-child(" + (currentIndex + 1) + ")"
      );
      if (active) active.classList.add("active");
    });

    document.addEventListener("keydown", function (e) {
      if (!lb.classList.contains("open")) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") btnPrev.click();
      if (e.key === "ArrowRight") btnNext.click();
    });

    return {
      open: function (gallery, index) {
        if (!gallery || !gallery.length) return;
        currentGallery = gallery;
        currentIndex = typeof index === "number" ? index : 0;
        img.src = currentGallery[currentIndex];
        renderThumbs(currentGallery, currentIndex);
        lb.classList.add("open");
      },
      close: close,
    };
  }

  window.tourLightbox = createLightbox();
  const lightbox = window.tourLightbox;

  document.querySelectorAll(".tour-card").forEach(function (card) {
    const raw = card.dataset.gallery;
    let gallery = [];
    try {
      gallery = raw ? JSON.parse(raw) : [];
    } catch (err) {
      gallery = raw ? raw.split(",") : [];
    }

    const mainImg = card.querySelector(".tour-main img");
    const thumbImgs = card.querySelectorAll(".tour-thumbs .thumb-item img");

    // Mark first thumb as active if exists
    if (thumbImgs && thumbImgs.length) {
      card.querySelectorAll(".tour-thumbs .thumb-item").forEach(function (el) {
        el.classList.remove("active");
      });
    }

    thumbImgs.forEach(function (img) {
      img.addEventListener("click", function (e) {
        const idx = parseInt(this.dataset.index, 10);
        if (!isNaN(idx) && gallery[idx]) {
          if (mainImg) mainImg.src = gallery[idx];
          card.dataset.currentIndex = idx;
        } else if (mainImg) {
          mainImg.src = this.src;
          card.dataset.currentIndex = this.dataset.index || 0;
        }
        // active state
        card.querySelectorAll(".tour-thumbs .thumb-item").forEach(function (t) {
          t.classList.remove("active");
        });
        this.parentElement.classList.add("active");
      });
    });

    if (mainImg) {
      mainImg.style.cursor = "zoom-in";
      mainImg.addEventListener("click", function () {
        let idx = parseInt(card.dataset.currentIndex || "0", 10);
        if (isNaN(idx)) idx = 0;
        lightbox.open(gallery.length ? gallery : [this.src], idx);
      });
    }
  });

  // ===== Edit page: connect previews to lightbox =====
  const previewContainer = document.getElementById("image-preview-container");
  if (previewContainer) {
    // Open lightbox when clicking the preview image or the action button in overlay
    previewContainer.addEventListener("click", function (e) {
      // Prefer detecting the action button element (more robust than icon-only selector)
      const actionBtn = e.target.closest(".action-btn");
      if (actionBtn) {
        // If it's the eye button (view)
        if (actionBtn.classList.contains("fa-eye")) {
          e.preventDefault();
          const wrappers = Array.from(previewContainer.children);
          const imgs = Array.from(
            previewContainer.querySelectorAll("img.card-img-top")
          ).map((i) => i.src);
          const wrapper = actionBtn.closest(".col-6, .col-md-4, .col-lg-3");
          let idx = 0;
          if (wrapper) {
            idx = wrappers.indexOf(wrapper);
            if (idx === -1) idx = 0;
          }
          if (window.tourLightbox) {
            window.tourLightbox.open(imgs, idx);
          } else {
            console.warn("tourLightbox not available yet");
          }
          return;
        }
      }

      // If click directly on the preview image
      const imgEl = e.target.closest("img.card-img-top");
      if (imgEl) {
        e.preventDefault();
        const imgs = Array.from(
          previewContainer.querySelectorAll("img.card-img-top")
        ).map((i) => i.src);
        const wrappers = Array.from(previewContainer.children);
        const wrapper = imgEl.closest(".col-6, .col-md-4, .col-lg-3");
        let idx = wrappers.indexOf(wrapper);
        if (idx === -1) idx = imgs.indexOf(imgEl.src) || 0;
        if (window.tourLightbox) {
          window.tourLightbox.open(imgs, idx);
        } else {
          console.warn("tourLightbox not available yet");
        }
      }
    });
  }

  // ===== TOURS CREATE/EDIT SECTION (no CKEditor) =====

  // Attach form submit handler to serialize dynamic sections into hidden inputs.
  (function attachTourFormHandler() {
    const tourForms = document.querySelectorAll("form.tour-form");
    tourForms.forEach(function (form) {
      form.addEventListener("submit", function (e) {
        try {
          // pricing options
          const pricingOptionsList = document.getElementById("pricing-options-list");
          const pricingOptionsArr = [];
          if (pricingOptionsList) {
            pricingOptionsList
              .querySelectorAll(".pricing-option-item")
              .forEach(function (item) {
                const obj = {};
                item.querySelectorAll("[data-field]").forEach(function (f) {
                  const key = f.dataset.field;
                  if (!key) return;
                  obj[key] = f.value;
                });
                if (obj.label) pricingOptionsArr.push(obj);
              });
          }

          // dynamic pricing
          const dynamicPricingList = document.getElementById("dynamic-pricing-list");
          const dynamicPricingArr = [];
          if (dynamicPricingList) {
            dynamicPricingList
              .querySelectorAll(".dynamic-pricing-item")
              .forEach(function (item) {
                const obj = {};
                item.querySelectorAll("[data-field]").forEach(function (f) {
                  const key = f.dataset.field;
                  if (!key) return;
                  obj[key] = f.value;
                });
                if (obj.option_label && obj.price) dynamicPricingArr.push(obj);
              });
          }


          // itinerary
          const itinList = document.getElementById("itinerary-list");
          const itinArr = [];
          if (itinList) {
            itinList
              .querySelectorAll(".itinerary-item")
              .forEach(function (item) {
                const obj = {};
                item.querySelectorAll("[data-field]").forEach(function (f) {
                  const key = f.dataset.field;
                  if (!key) return;
                  obj[key] = f.value;
                });
                if (Object.keys(obj).length) itinArr.push(obj);
              });
          }

          // partners
          const partnerList = document.getElementById("partner-list");
          const partnerArr = [];
          if (partnerList) {
            partnerList
              .querySelectorAll(".partner-item")
              .forEach(function (item) {
                const obj = {};
                item.querySelectorAll("[data-field]").forEach(function (f) {
                  const key = f.dataset.field;
                  if (!key) return;
                  obj[key] = f.value;
                });
                if (Object.keys(obj).length) partnerArr.push(obj);
              });
          }

          // attach hidden inputs (replace if exist)
          function upsertHidden(name, value) {
            let input = form.querySelector('input[name="' + name + '"]');
            if (!input) {
              input = document.createElement("input");
              input.type = "hidden";
              input.name = name;
              form.appendChild(input);
            }
            input.value = value;
          }

          upsertHidden("tour_pricing_options", JSON.stringify(pricingOptionsArr));
          upsertHidden("version_dynamic_pricing", JSON.stringify(dynamicPricingArr));
          upsertHidden("tour_itinerary", JSON.stringify(itinArr));
          upsertHidden("tour_partners", JSON.stringify(partnerArr));

          // versions
          const versionsList = document.getElementById("versions-list");
          const versionsArr = [];
          if (versionsList) {
            versionsList
              .querySelectorAll(".version-item")
              .forEach(function (item) {
                const obj = {};
                item.querySelectorAll("[data-field]").forEach(function (f) {
                  const key = f.dataset.field;
                  if (!key) return;
                  obj[key] = f.value;
                });
                if (obj.name) versionsArr.push(obj);
              });
          }
          upsertHidden("tour_versions", JSON.stringify(versionsArr));
        } catch (err) {
          console.error("Error serializing dynamic sections:", err);
          e.preventDefault(); // Prevent submission on error
        }
      });
    });
  })();

  function setupDynamicSection(config) {
    const listEl = document.getElementById(config.listId);
    const template = document.getElementById(config.templateId);
    const addBtn = document.getElementById(config.addBtnId);

    if (!listEl || !template || !addBtn) {
      return;
    }

    // This is a special handler for the dynamic pricing section to populate its dropdown
    const updateDynamicPriceOptions = () => {
      if (config.listId !== 'dynamic-pricing-list') return;

      const pricingOptionsList = document.getElementById('pricing-options-list');
      if (!pricingOptionsList) return;

      // Get current option values
      const availableOptions = Array.from(
        pricingOptionsList.querySelectorAll(".dynamic-item")
      )
        .map((item) => {
          const label = item.querySelector('[data-field="label"]')?.value;
          const description = item.querySelector('[data-field="description"]')?.value;
          const price = item.querySelector('[data-field="price"]')?.value;
          return label ? { label, description, price } : null;
        })
        .filter(Boolean);

      // Update all select dropdowns in the dynamic pricing list
      const allDynamicSelects = listEl.querySelectorAll('[data-field="option_label"]');
      allDynamicSelects.forEach(select => {
        const currentVal = select.value;
        select.innerHTML = '<option value="">-- Chọn gói dịch vụ --</option>';
        availableOptions.forEach((opt) => {
          const option = document.createElement("option");
          option.value = opt.label;
          option.textContent = opt.label;
          option.setAttribute('data-description', opt.description || '');
          option.setAttribute('data-price', opt.price || '');
          select.appendChild(option);
        });
        select.value = currentVal; // try to restore previous value
      });
    };

    // Listen for changes in the main pricing options to update the dynamic ones
    pricingOptionsList.addEventListener("input", (e) => {
      if (e.target && (e.target.matches('[data-field="label"]') ||
        e.target.matches('[data-field="price"]') ||
        e.target.matches('[data-field="description"]'))) {
        updateDynamicPriceOptions();
      }
    });


    function resetItem(item) {
      item.querySelectorAll("[data-field]").forEach(function (field) {
        if (field.tagName === "SELECT") {
          field.selectedIndex = 0;
        } else {
          field.value = "";
        }
      });
    }

    function bindRemove(item) {
      var removeBtn = item.querySelector(config.removeSelector);
      if (!removeBtn) return;

      removeBtn.addEventListener("click", function () {
        const parentList = this.closest('div[id]');
        const items = parentList.querySelectorAll("." + config.itemClass);

        if (items.length > 1) {
          item.remove();
        } else {
          resetItem(item);
        }

        // After removing an item from pricing-options, update dynamic pricing dropdowns
        if (config.listId === 'pricing-options-list') {
          updateDynamicPriceOptions();
        }
      });
    }

    function hydrateItem(item, values) {
      if (!values) return;
      item.querySelectorAll("[data-field]").forEach(function (field) {
        var key = field.dataset.field;
        if (!key) return;
        var value = values[key];
        if (value === undefined || value === null) return;
        field.value = value;
      });
    }

    function appendNewItem(values) {
      var clone = template.content.cloneNode(true);
      var item =
        clone.querySelector("." + config.itemClass) || clone.children[0];

      listEl.appendChild(item); // Append first to make it part of the DOM
      hydrateItem(item, values);
      bindRemove(item);

      // If we are adding to the dynamic pricing list, populate the dropdown
      if (config.listId === 'dynamic-pricing-list') {
        updateDynamicPriceOptions();
        // If there's initial data, set the value after populating
        if (values && values.option_label) {
          const select = item.querySelector('[data-field="option_label"]');
          if (select) select.value = values.option_label;
        }
      }

      // If we add a new pricing option, we must update the dynamic list
      if (config.listId === 'pricing-options-list') {
        updateDynamicPriceOptions();
      }
    }

    // --- Initialization ---
    const initialData = listEl.dataset.initial ? JSON.parse(listEl.dataset.initial || '[]') : [];

    if (initialData.length > 0) {
      initialData.forEach(data => appendNewItem(data));
    } else {
      appendNewItem(); // Add one empty item by default
    }

    // Initial population for dynamic pricing in case of pre-filled data (e.g., edit page)
    if (config.listId === 'dynamic-pricing-list' || config.listId === 'pricing-options-list') {
      // Use a timeout to ensure all sections are loaded before linking them
      setTimeout(updateDynamicPriceOptions, 50);
    }

    addBtn.addEventListener("click", function () {
      appendNewItem();
    });
  }

  setupDynamicSection({
    listId: "pricing-options-list",
    templateId: "pricing-option-template",
    addBtnId: "add-pricing-option",
    removeSelector: ".remove-pricing-option",
    itemClass: "pricing-option-item",
  });

  setupDynamicSection({
    listId: "dynamic-pricing-list",
    templateId: "dynamic-pricing-template",
    addBtnId: "add-dynamic-price",
    removeSelector: ".remove-dynamic-price",
    itemClass: "dynamic-pricing-item",
  });

  setupDynamicSection({
    listId: "itinerary-list",
    templateId: "itinerary-item-template",
    addBtnId: "add-itinerary-item",
    removeSelector: ".remove-itinerary-item",
    itemClass: "itinerary-item",
  });

  setupDynamicSection({
    listId: "partner-list",
    templateId: "partner-item-template",
    addBtnId: "add-partner-item",
    removeSelector: ".remove-partner-item",
    itemClass: "partner-item",
  });

  setupDynamicSection({
    listId: "versions-list",
    templateId: "version-item-template",
    addBtnId: "add-version-item",
    removeSelector: ".remove-version-item",
    itemClass: "version-item",
  });

  var imageInput = document.getElementById("image");
  if (imageInput) {
    imageInput.addEventListener("change", function (e) {
      var file = e.target.files[0];
      if (!file) return;

      var reader = new FileReader();
      reader.onload = function (event) {
        var preview = document.createElement("img");
        preview.src = event.target.result;
        preview.className = "form-image-preview";

        var previewContainer = document.getElementById("image-preview");
        if (previewContainer) {
          previewContainer.innerHTML = "";
          previewContainer.appendChild(preview);
        }
      };
      reader.readAsDataURL(file);
    });
  }

  var galleryInput = document.getElementById("gallery");
  if (galleryInput) {
    galleryInput.addEventListener("change", function (e) {
      var files = Array.from(e.target.files || []);
      var previewGrid = document.getElementById("gallery-preview");
      if (!previewGrid) return;
      previewGrid.innerHTML = "";

      files.slice(0, 10).forEach(function (file) {
        if (!file.type.startsWith("image/")) return;
        var reader = new FileReader();
        reader.onload = function (evt) {
          var col = document.createElement("div");
          col.className = "col-4";
          col.innerHTML =
            '<div class="ratio ratio-4x3 rounded overflow-hidden border"><img src="' +
            evt.target.result +
            '" class="w-100 h-100 object-fit-cover" alt=""></div>';
          previewGrid.appendChild(col);
        };
        reader.readAsDataURL(file);
      });
    });
  }
  document.addEventListener('change', function (e) {
    if (e.target.classList.contains('dynamic-pricing-select')) {
      const selectedOption = e.target.selectedOptions[0];
      const dynamicItem = e.target.closest('.dynamic-item');

      if (dynamicItem && selectedOption) {
        const description = selectedOption.getAttribute('data-description') || '';
        const price = selectedOption.getAttribute('data-price') || '';

        const descDisplay = dynamicItem.querySelector('.package-description-display');
        const priceDisplay = dynamicItem.querySelector('.package-price-display');

        if (descDisplay) descDisplay.value = description;
        if (priceDisplay) {
          // Format price with thousand separators
          const formattedPrice = price ? parseInt(price).toLocaleString('vi-VN') : '';
          priceDisplay.value = formattedPrice;
        }
      }
    }
  });
});

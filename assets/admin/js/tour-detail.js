document.addEventListener("DOMContentLoaded", function () {
  // If the global tour lightbox exists (shared `tours.js`), skip custom handlers
  if (window.tourLightbox) return;
  // thumbnail click -> swap hero image
  document.querySelectorAll(".tour-gallery img").forEach(function (img) {
    img.addEventListener("click", function (e) {
      var target = e.currentTarget;
      var gallery = target.closest(".tour-card");
      var mainImg =
        gallery.querySelector(".position-relative img") ||
        gallery.querySelector("img.img-fluid.w-100");
      if (!mainImg) return;
      // if thumbnail has data-index of 0..n where source may be full URL
      var src = target.getAttribute("src");
      if (src) {
        mainImg.setAttribute("src", src);
      }
    });
  });

  // Simple lightbox when clicking main image
  document
    .querySelectorAll(".position-relative img, img.img-fluid.w-100")
    .forEach(function (img) {
      img.addEventListener("click", function () {
        var src = img.getAttribute("src");
        if (!src) return;
        var overlay = document.createElement("div");
        overlay.className = "td-lightbox";
        overlay.innerHTML =
          '<img src="' + src.replace(/\"/g, '\\"') + '" alt="preview">';
        overlay.addEventListener("click", function () {
          document.body.removeChild(overlay);
        });
        document.body.appendChild(overlay);
        // close on ESC
        var onKey = function (e) {
          if (e.key === "Escape") {
            if (document.body.contains(overlay))
              document.body.removeChild(overlay);
            document.removeEventListener("keydown", onKey);
          }
        };
        document.addEventListener("keydown", onKey);
      });
    });
});

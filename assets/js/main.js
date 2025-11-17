document.addEventListener("DOMContentLoaded", function () {
    const bottomNav = document.querySelector(".bottom-nav");

    if (!bottomNav) return;

    // Gère l'affichage/masquage de la bottom-nav
    function updateNavbarVisibility() {
        if (window.innerWidth >= 768) {
            bottomNav.style.display = "none";
            return;
        }

        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const scrollHeight = document.documentElement.scrollHeight;
        const atBottom = scrollTop + windowHeight >= scrollHeight - 5;

        bottomNav.style.display = atBottom ? "none" : "flex";
    }

            bottomNav.style.display = "flex";
    window.addEventListener("scroll", updateNavbarVisibility);
    window.addEventListener("resize", updateNavbarVisibility);

    // Init au chargement
    updateNavbarVisibility();

    // === Slider annonce ===
    if (typeof $ !== "undefined" && $(".slick-slider").length > 0) {
        $(".slick-slider").slick({
            autoplay: true,
            autoplaySpeed: 2000,
            dots: true,
            arrows: true,
            infinite: true,
            speed: 500,
            slidesToShow: 1,
            slidesToScroll: 1,
        });
    }

    // === Lightbox pour les images du slider ===
    const images = document.querySelectorAll(".slick-slider img");
    images.forEach(img => {
        img.addEventListener("click", function () {
            const lightbox = document.createElement("div");
            lightbox.style.position = "fixed";
            lightbox.style.top = "0";
            lightbox.style.left = "0";
            lightbox.style.width = "100%";
            lightbox.style.height = "100%";
            lightbox.style.background = "rgba(0,0,0,0.9)";
            lightbox.style.display = "flex";
            lightbox.style.alignItems = "center";
            lightbox.style.justifyContent = "center";
            lightbox.style.zIndex = "9999";
            lightbox.innerHTML = `<img src="${img.src}" style="max-width:90%; max-height:90%; border-radius:10px;">`;

            // Fermer au clic
            lightbox.addEventListener("click", () => lightbox.remove());

            document.body.appendChild(lightbox);
        });
    });

    // === Cacher navbar quand on écrit sur mobile ===
    document.addEventListener("focusin", (e) => {
        if (window.innerWidth < 768 && (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA")) {
            bottomNav.classList.add("hide-navbar");
        }
    });

    document.addEventListener("focusout", (e) => {
        if (window.innerWidth < 768 && (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA")) {
            bottomNav.classList.remove("hide-navbar");
        }
    });
});

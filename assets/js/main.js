document.addEventListener("DOMContentLoaded", function () {

  // Cacher la navbar du bas si on est en bas de page 
    const bottomNav = document.querySelector(".bottom-nav");

    if (!bottomNav) return;

    window.addEventListener("scroll", function () {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const scrollHeight = document.documentElement.scrollHeight;
        const atBottom = scrollTop + windowHeight >= scrollHeight - 5;

        if (atBottom) {
            bottomNav.style.display = "none";
        } else {
            bottomNav.style.display = "flex";
        }
    });

    // Slider annonce
    $('.slick-slider').slick({
        autoplay: true,
        autoplaySpeed: 2000,
        dots: true,
        arrows: true,  // Arrows disabled
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
    });

    // Lightbox pour la photo dans annonce
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

        // fermer au clic
        lightbox.addEventListener("click", () => lightbox.remove());

        document.body.appendChild(lightbox);
        });
    });
});

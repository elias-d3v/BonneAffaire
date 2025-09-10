document.addEventListener("DOMContentLoaded", function () {
  // Cacher la navbar du bas si on est en bas de page 

    const bottomNav = document.querySelector(".bottom-nav");

    if (!bottomNav) return;

    window.addEventListener("scroll", function () {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const scrollHeight = document.documentElement.scrollHeight;

        // On est en bas si on dépasse la hauteur totale - 5px
        const atBottom = scrollTop + windowHeight >= scrollHeight - 5;

        if (atBottom) {
            bottomNav.style.display = "none";
        } else {
            bottomNav.style.display = "flex"; // ou "block" selon ton CSS
        }
    });
});

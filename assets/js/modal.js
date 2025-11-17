document.addEventListener("DOMContentLoaded", () => {
  
  // Ouvrir modale
  document.querySelectorAll("[data-open]").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      const target = document.getElementById(btn.dataset.open);
      if (target) target.classList.add("show");
    });
  });

  // Fermer modale
  document.querySelectorAll("[data-close]").forEach(btn => {
    btn.addEventListener("click", () => {
      btn.closest(".modal").classList.remove("show");
    });
  });

  // Fermer clic extérieur
  document.querySelectorAll(".modal").forEach(modal => {
    modal.addEventListener("click", e => {
      if (e.target === modal) modal.classList.remove("show");
    });
  });
});

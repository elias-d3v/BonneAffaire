const STORAGE_KEY = "recent_searches";

// Récupère les recherches stockées
function getSearches() {
  return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
}

// Sauvegarde dans localStorage
function saveSearches(searches) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(searches));
}

// Ajoute une recherche
function addSearch(q, category, dept, categoryLabel, deptLabel) {
  let searches = getSearches();

  // Ajouter au début
  searches.unshift({ q, category, dept, categoryLabel, deptLabel });

  // Garde seulement 5 au total
  if (searches.length > 5) {
    searches = searches.slice(0, 5);
  }

  saveSearches(searches);
  renderSearches();
}

// Supprime une recherche (par index dans la liste complète)
function removeSearch(globalIndex) {
  let searches = getSearches();
  searches.splice(globalIndex, 1);
  saveSearches(searches);
  renderSearches();
}

// Affiche les recherches
function renderSearches() {
  const container = document.getElementById("recentSearches");
  if (!container) return;

  container.innerHTML = "";

  const allSearches = getSearches();

  if (allSearches.length === 0) {
    container.innerHTML = "<p>Aucune recherche récente</p>";
    return;
  }

  // Déterminer combien afficher en fonction de l’écran
  const maxToShow = window.innerWidth >= 768 ? 5 : 3;

  // Sélectionner les N premières recherches
  const visibleSearches = allSearches.slice(0, maxToShow);

  visibleSearches.forEach((search, index) => {
    const globalIndex = index; // correspond bien à l’ordre dans le localStorage
    const card = document.createElement("div");
    card.className = "search-card";

    const url = `/post/list?q=${encodeURIComponent(search.q)}&category=${encodeURIComponent(search.category)}&dept=${encodeURIComponent(search.dept)}`;

    card.innerHTML = `
      <button class="remove" data-index="${globalIndex}">&times;</button>
      <a href="${url}" class="search-link">
        <h3>${search.q || "Recherche"}</h3>
        <p>${search.categoryLabel}</p>
        <span class="location">${search.deptLabel}</span>
      </a>
    `;

    container.appendChild(card);
  });

  // Ajout des écouteurs pour les boutons remove
  container.querySelectorAll(".remove").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const index = parseInt(btn.getAttribute("data-index"));
      removeSearch(index);
    });
  });
}

// Re-render quand on redimensionne
window.addEventListener("resize", renderSearches);

// Init au chargement
document.addEventListener("DOMContentLoaded", () => {
  renderSearches();

  // Barre de recherche (dans le header)
  const searchForm = document.querySelector(".search form");
  if (searchForm) {
    searchForm.addEventListener("submit", function () {
      const q = this.querySelector('input[name="q"]').value || "Recherche";
      addSearch(q, "", "", "Toutes catégories", "Toute la France");
    });
  }

  // Formulaire de filtrage (liste annonces)
  const filterForm = document.querySelector('form.filters-posts');
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      // Laisse le vrai submit se faire mais ajoute avant au localStorage
      const title = filterForm.querySelector('input[name="q"]')?.value || "Recherche";

      // Catégorie
      const categorySelect = filterForm.querySelector('select[name="category"]');
      const category = categorySelect?.value || "";
      const categoryLabel = categorySelect?.value
        ? categorySelect.selectedOptions[0].text
        : "Toutes catégories";

      // Département
      const deptSelect = filterForm.querySelector('select[name="dept"]');
      const dept = deptSelect?.value || "";
      const deptLabel = deptSelect?.value
        ? deptSelect.selectedOptions[0].text
        : "Toute la France";

      // ✅ On enregistre AVANT que la page se recharge
      addSearch(title, category, dept, categoryLabel, deptLabel);
    });
  }
});

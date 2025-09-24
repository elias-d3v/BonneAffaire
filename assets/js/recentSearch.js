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

  // Garde seulement 3
  if (searches.length > 3) {
    searches = searches.slice(0, 3);
  }

  saveSearches(searches);
  renderSearches();
}

// Supprime une recherche (par index)
function removeSearch(index) {
  let searches = getSearches();
  searches.splice(index, 1);
  saveSearches(searches);
  renderSearches();
}

// Affiche les recherches
function renderSearches() {
  const container = document.getElementById("recentSearches");
  if (!container) return;

  container.innerHTML = "";

  const searches = getSearches();

  if (searches.length === 0) {
    container.innerHTML = "<p>Aucune recherche récente</p>";
    return;
  }

  searches.forEach((search, index) => {
    const card = document.createElement("div");
    card.className = "search-card";

    // Construire l'URL pour relancer la recherche (sans sort)
    const url = `/post/list?q=${encodeURIComponent(search.q)}&category=${encodeURIComponent(search.category)}&dept=${encodeURIComponent(search.dept)}`;

    card.innerHTML = `
      <button class="remove" data-index="${index}">&times;</button>
      <a href="${url}" class="search-link">
        <h3>${search.q || "Recherche"}</h3>
        <p>${search.categoryLabel}</p>
        <span class="location">${search.deptLabel}</span>
      </a>
    `;
    container.appendChild(card);
  });

  // Attache les events "supprimer"
  document.querySelectorAll(".remove").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const index = btn.getAttribute("data-index");
      removeSearch(index);
    });
  });
}

// Init au chargement
document.addEventListener("DOMContentLoaded", () => {
  renderSearches();

  // Formulaire de la barre de recherche (header)
  const searchForm = document.querySelector('.search form');
  if (searchForm) {
    searchForm.addEventListener('submit', function() {
      const q = this.querySelector('input[name="q"]').value || "Recherche";
      addSearch(q, "", "", "Toutes catégories", "Toute la France");
    });
  }

  // Formulaire de filtrage (liste annonces)
  const filterForm = document.querySelector('form.mb-4');
  if (filterForm) {
    filterForm.addEventListener('submit', function () {
      const title = this.querySelector('input[name="q"]')?.value || "Recherche";

      // Catégorie
      const categorySelect = this.querySelector('select[name="category"]');
      const category = categorySelect?.value || "";
      const categoryLabel = categorySelect?.value
        ? categorySelect.selectedOptions[0].text
        : "Toutes catégories";

      // Département
      const deptSelect = this.querySelector('select[name="dept"]');
      const dept = deptSelect?.value || "";
      const deptLabel = deptSelect?.value
        ? deptSelect.selectedOptions[0].text
        : "Toute la France";

      addSearch(title, category, dept, categoryLabel, deptLabel);
    });
  }
});

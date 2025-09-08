document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const img = btn.querySelector("img");

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            fetch(this.dataset.url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'added') {
                    img.setAttribute("src", "/images/hearth-fill.svg");
                    this.dataset.favorited = "true";
                } else if (data.status === 'removed') {
                    img.setAttribute("src", "/images/hearth-svgrepo-com.svg");
                    this.dataset.favorited = "false";
                }
            })
            .catch(err => console.error("Erreur favoris:", err));
        });
    });
});

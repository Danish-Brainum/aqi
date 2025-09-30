export function initSearch() {
    const searchInput = document.getElementById("tableSearch");
    const rows = document.querySelectorAll("#results-table tbody tr");

    if (!searchInput || !rows.length) return;

    searchInput.addEventListener("keyup", function () {
        const filter = this.value.toLowerCase();
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
}

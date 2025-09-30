export function initStatusTable() {
    const fetchBtn = document.getElementById("fetchAll");
    const tableBody = document.getElementById("aqi-body");
    if (!fetchBtn || !tableBody) return;

    let intervalId = null;

    function renderRows(cities) {
        tableBody.innerHTML = "";
        cities.forEach(city => {
            let aqiCell = "";
            if (city.status === "pending") aqiCell = `<span class="text-slate-500">-</span>`;
            else if (city.status === "processing") aqiCell = `<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-green-600 border-t-transparent"></span>`;
            else if (city.status === "done") {
                const color =
                    city.aqi <= 50 ? "bg-green-100 text-green-700" :
                    city.aqi <= 100 ? "bg-yellow-100 text-yellow-700" :
                    "bg-red-100 text-red-700";
                aqiCell = `<span class="rounded-full px-2 py-1 text-xs font-semibold ${color}">${city.aqi}</span>`;
            } else aqiCell = `<span class="text-red-600">Error</span>`;

            tableBody.insertAdjacentHTML("beforeend", `
                <tr class="hover:bg-indigo-50/40" data-id="${city.id}">
                    <td class="px-4 py-2 text-sm">${city.id}</td>
                    <td class="px-4 py-2 text-sm">${city.name}</td>
                    <td class="px-4 py-2 text-sm">${city.state}</td>
                    <td class="px-4 py-2 text-sm">${aqiCell}</td>
                    <td class="px-4 py-2 text-sm capitalize">${city.status}</td>
                </tr>
            `);
        });
    }

    function loadCities() {
        fetch("/status")
            .then(res => res.json())
            .then(data => renderRows(data));
    }

    fetchBtn.addEventListener("click", () => {
        fetch("/fetch-all")
            .then(res => res.json())
            .then(data => {
                const alertBox = `
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 flex items-center justify-between">
                        <span>${data.success}</span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">✕</button>
                    </div>
                `;
                document.getElementById("alert-container").innerHTML = alertBox;
                loadCities();
                if (!intervalId) intervalId = setInterval(loadCities, 5000);
            });
    });

    // initial load
    loadCities();
}

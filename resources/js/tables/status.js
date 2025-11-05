export function initStatusTable() {
    const fetchBtn = document.getElementById("fetchAll");
    const tableBody = document.getElementById("aqi-body");
    const alertContainer = document.getElementById("alert-container");
    if (!fetchBtn || !tableBody || !alertContainer) return;

    let intervalId = null;
    let isUpdating = false;

    function showAlert(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
        const alertBox = `
            <div class="mb-4 rounded-lg border ${bgColor} px-4 py-3 flex items-center justify-between">
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="hover:opacity-75">✕</button>
            </div>
        `;
        alertContainer.innerHTML = alertBox;
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.mb-4');
            if (alert) alert.remove();
        }, 5000);
    }

    function renderRows(cities) {
        tableBody.innerHTML = "";
        cities.forEach(city => {
            let aqiCell = "";
            
            // Check if AQI value exists (regardless of status)
            const hasValidAqi = city.aqi !== null && city.aqi !== undefined && city.aqi !== '';
            const aqiValue = hasValidAqi ? parseInt(city.aqi) : null;
            const isValidAqi = aqiValue !== null && !isNaN(aqiValue) && aqiValue > 0;
            
            if (city.status === "processing") {
                // Always show spinner when processing
                aqiCell = `<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-green-600 border-t-transparent"></span>`;
            } else if (isValidAqi) {
                // If AQI value exists and is valid, show it (regardless of status)
                const color =
                    aqiValue <= 50 ? "bg-green-100 text-green-700" :
                    aqiValue <= 100 ? "bg-yellow-100 text-yellow-700" :
                    "bg-red-100 text-red-700";
                aqiCell = `<span class="rounded-full px-2 py-1 text-xs font-semibold ${color}">${aqiValue}</span>`;
            } else if (city.status === "pending") {
                // No valid AQI and status is pending
                aqiCell = `<span class="text-slate-500">-</span>`;
            } else {
                // Error status or done but no valid AQI value
                aqiCell = `<span class="text-red-600">Error</span>`;
            }

            tableBody.insertAdjacentHTML("beforeend", `
                <tr class="hover:bg-indigo-50/40" data-id="${city.id}">
                    <td class="px-4 py-2 text-sm">${city.id}</td>
                    <td class="px-4 py-2 text-sm">${city.name}</td>
                    <td class="px-4 py-2 text-sm">${city.state || '-'}</td>
                    <td class="px-4 py-2 text-sm">${aqiCell}</td>
                    <td class="px-4 py-2 text-sm capitalize">${city.status}</td>
                </tr>
            `);
        });
    }

    function loadCities() {
        fetch("/status")
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (Array.isArray(data)) {
                    renderRows(data);
                    
                    // Check if any cities are still processing
                    const stillProcessing = data.some(city => 
                        city.status === "processing" || city.status === "pending"
                    );
                    
                    // Stop interval if all cities are done
                    if (!stillProcessing && intervalId) {
                        clearInterval(intervalId);
                        intervalId = null;
                        // Show completion message
                        showAlert("✅ All cities updated successfully!", "success");
                    }
                }
            })
            .catch(err => {
                console.error("Error loading cities:", err);
                // Don't show error alert on every interval, only log
            });
    }

    fetchBtn.addEventListener("click", () => {
        // Prevent multiple simultaneous requests
        if (isUpdating) {
            showAlert("Update is already in progress. Please wait...", "error");
            return;
        }

        isUpdating = true;
        fetchBtn.disabled = true;
        fetchBtn.textContent = "Updating...";
        
        // Clear any existing interval
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }

        fetch("/fetch-all")
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw new Error(err.message || `HTTP error! status: ${res.status}`);
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert(data.message || "🔄 Updating AQI values...", "success");
                    // Immediate refresh
                    loadCities();
                    
                    // Start polling every 2 seconds for faster updates
                    if (!intervalId) {
                        intervalId = setInterval(loadCities, 2000);
                    }
                } else {
                    showAlert(data.message || "Failed to start update process.", "error");
                }
            })
            .catch(err => {
                console.error("Error fetching AQI data:", err);
                showAlert(err.message || "Failed to connect to server. Please check your connection and try again.", "error");
            })
            .finally(() => {
                isUpdating = false;
                fetchBtn.disabled = false;
                fetchBtn.textContent = "Update";
            });
    });

    // initial load
    loadCities();
}

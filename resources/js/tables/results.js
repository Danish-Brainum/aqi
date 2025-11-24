export function initResultsTable() {
    const resultsTable = document.querySelector("#results-table tbody");
    if(!resultsTable) return;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta?.getAttribute("content")||"";
    const deletedContainer = document.getElementById('deleted-container');
    const deletedUrl = deletedContainer?.dataset.deletedUrl || '/deleted-table';

    // Function to refresh the results table with updated CSV data
    async function refreshResultsTable() {
        try {
            const res = await fetch('/csv-data', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (!res.ok) throw new Error('Failed to fetch CSV data');
            
            const json = await res.json();
            
            if (json.success && json.results) {
                // Clear existing rows
                resultsTable.innerHTML = '';
                
                // Render new rows
                if (json.results.length === 0) {
                    resultsTable.innerHTML = `
                        <tr class="no-records">
                            <td colspan="8" class="px-4 py-8 text-center text-base font-semibold text-slate-500">
                                No CSV Found
                            </td>
                        </tr>
                    `;
                } else {
                    json.results.forEach((row, index) => {
                        const aqiClass = (row.aqi ?? 0) <= 50 
                            ? 'bg-green-100 text-green-700'
                            : (row.aqi ?? 0) <= 100 
                            ? 'bg-yellow-100 text-yellow-700'
                            : 'bg-red-100 text-red-700';
                        
                        const displayId = row.display_id ?? (index + 1); // Use sequential ID for display
                        
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-indigo-50/40 transition-colors';
                        tr.dataset.id = row.id; // Keep database ID for operations
                        tr.innerHTML = `
                            <td class="px-3 py-2.5 text-sm text-slate-700 font-medium">${displayId}</td>
                            <td class="px-3 py-2.5 text-sm text-slate-800">${escapeHtml(row.name)}</td>
                            <td class="px-3 py-2.5 text-sm text-slate-700">
                                <div class="truncate max-w-[180px]" title="${escapeHtml(row.email)}">${escapeHtml(row.email)}</div>
                            </td>
                            <td class="px-3 py-2.5 text-sm text-slate-700">${escapeHtml(row.city)}</td>
                            <td class="px-3 py-2.5 text-sm text-slate-700">${escapeHtml(row.phone)}</td>
                            <td class="px-3 py-2.5 text-sm font-semibold">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ${aqiClass}">
                                    ${row.aqi ?? 'N/A'}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-sm text-slate-700">
                                <div class="message-cell max-w-[300px] truncate" 
                                     title="${escapeHtml(row.message ?? '')}"
                                     data-full-message="${escapeHtml(row.message ?? '')}">
                                    ${escapeHtml(row.message ?? 'N/A')}
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-sm">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        class="edit-btn inline-flex items-center justify-center rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors"
                                        data-index="${index}" 
                                        data-url="/records/update">
                                        Edit
                                    </button>
                                    <button 
                                        class="delete-btn inline-flex items-center justify-center rounded-md bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 transition-colors" 
                                        data-id="${row.id}" 
                                        data-url="/records/delete">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        `;
                        resultsTable.appendChild(tr);
                    });
                }
            }
        } catch (err) {
            console.error('Error refreshing results table:', err);
        }
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Expose refresh function globally so it can be called from other modules
    window.refreshResultsTable = refreshResultsTable;

    // Periodic polling to check for updates (every 10 seconds)
    let pollingInterval = null;
    let lastUpdateTime = Date.now();

    function startPolling() {
        // Only start if not already running
        if (pollingInterval) return;
        
        pollingInterval = setInterval(async () => {
            try {
                const res = await fetch('/csv-data', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (res.ok) {
                    const json = await res.json();
                    if (json.success && json.results) {
                        // Check if data has changed by comparing with current table
                        const currentRows = resultsTable.querySelectorAll('tr[data-id]');
                        let hasChanges = false;
                        
                        // Simple check: compare row count or check for AQI changes
                        if (currentRows.length !== json.results.length) {
                            hasChanges = true;
                        } else {
                            // Check if any AQI values have changed
                            currentRows.forEach((row, index) => {
                                const rowId = row.dataset.id;
                                const newRow = json.results.find(r => r.id == rowId);
                                if (newRow) {
                                    const currentAqi = row.querySelector('.rounded-full')?.textContent.trim();
                                    const newAqi = newRow.aqi ?? 'N/A';
                                    if (currentAqi !== String(newAqi)) {
                                        hasChanges = true;
                                    }
                                }
                            });
                        }
                        
                        // Only refresh if there are actual changes
                        if (hasChanges) {
                            refreshResultsTable();
                            lastUpdateTime = Date.now();
                        }
                    }
                }
            } catch (err) {
                console.error('Error polling for CSV updates:', err);
            }
        }, 10000); // Poll every 10 seconds
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    // Start polling when page loads
    startPolling();

    // Stop polling when page is hidden (to save resources)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });

    async function refreshDeletedTable(fetchUrl){
        try{
            const res = await fetch(fetchUrl, { headers:{ 'X-Requested-With':'XMLHttpRequest' } });
            if(!res.ok) throw new Error('Failed to fetch deleted table');
            const json = await res.json();
            document.querySelector('#deleted-table-wrapper').innerHTML = json.html;
        } catch(err){ console.error(err); }
    }

    resultsTable.addEventListener("click", async e=>{
        const deleteBtn = e.target.closest('.delete-btn');
        if(deleteBtn){
            e.preventDefault();
            const url = deleteBtn.dataset.url;
            const row = deleteBtn.closest('tr');
            const id = deleteBtn.dataset.id ?? row?.dataset.id;
            if(!url||!row||!id) return;

            try{
                const res = await fetch(url, {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':csrf,
                        'X-Requested-With':'XMLHttpRequest'
                    },
                    body: JSON.stringify({id})
                });
                const json = await res.json();
                if(json?.success){
                    row.remove();
                    await refreshDeletedTable(deletedUrl);
                } else console.warn('Delete failure',json);
            } catch(err){ console.error(err); }
        }
    });


    // Pagination clicks
    document.addEventListener("click", async e => {
        const link = e.target.closest("#deleted-table-wrapper a[href]");
        if(link){
            e.preventDefault();
            await refreshDeletedTable(link.href);
        }
    });

    // deleted-table pagination clicks
    document.addEventListener("click", async e => {
        const link = e.target.closest("#deleted-table-wrapper a[data-page]");
        if(link){
            e.preventDefault();
            const perPage = document.getElementById("deleted-length").value || 10;
            const page = link.dataset.page;
            const url = `${deletedUrl}?deleted_page=${page}&perPage=${perPage}`;
            await refreshDeletedTable(url);
        }
    });

    // deleted-table perPage change
    document.addEventListener("change", async e => {
        if(e.target.id === "deleted-length"){
            const perPage = e.target.value;
            const url = `${deletedUrl}?perPage=${perPage}&deleted_page=1`; // reset page to 1
            await refreshDeletedTable(url);
        }
    });

}

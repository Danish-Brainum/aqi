export function initResultsTable() {
    const resultsTable = document.querySelector("#results-table tbody");
    if(!resultsTable) return;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta?.getAttribute("content")||"";
    const deletedContainer = document.getElementById('deleted-container');
    const deletedUrl = deletedContainer?.dataset.deletedUrl || '/deleted-table';

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

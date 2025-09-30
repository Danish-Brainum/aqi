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
            const html = await res.text();
            document.querySelector('#deleted-table-wrapper').innerHTML = html;
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
}

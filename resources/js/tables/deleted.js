// export function initDeletedTable() {
//     const deletedWrapper = document.querySelector("#deleted-table-wrapper");
//     if (!deletedWrapper) return;

//     async function refreshDeletedTable(url) {
//         try {
//             const res = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
//             if (!res.ok) throw new Error("Failed to fetch deleted table");
//             const html = await res.text();
//             deletedWrapper.innerHTML = html;
//         } catch (err) {
//             console.error(err);
//         }
//     }

//     document.addEventListener("click", async e => {
//         const link = e.target.closest("#deleted-table-wrapper .pagination a");
//         if (!link) return;

//         e.preventDefault();
//         await refreshDeletedTable(link.href);
//     });
// }

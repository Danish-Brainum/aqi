import './bootstrap';

// function setActiveTab(targetId) {
// 	const tabContents = document.querySelectorAll('.tab-content');
// 	tabContents.forEach(section => section.classList.add('hidden'));
// 	const active = document.getElementById(targetId);
// 	if (active) active.classList.remove('hidden');

// 	const buttons = document.querySelectorAll('.tab-btn');
// 	buttons.forEach(btn => btn.removeAttribute('data-active'));
// 	const activeBtn = document.querySelector(`.tab-btn[data-tab="${targetId}"]`);
// 	if (activeBtn) activeBtn.setAttribute('data-active', 'true');
// }

// function initTabs() {
// 	const buttons = document.querySelectorAll('.tab-btn');
// 	if (!buttons.length) return;
// 	buttons.forEach(btn => {
// 		btn.addEventListener('click', () => {
// 			const target = btn.getAttribute('data-tab');
// 			if (target) setActiveTab(target);
// 		});
// 	});
// 	setActiveTab('upload');
// }

// function initDragDrop() {
// 	const dropzone = document.getElementById('dropzone');
// 	const fileInput = document.getElementById('file-input');
// 	const fileName = document.getElementById('file-name');
//     const fileError = document.getElementById('file-error');
//     const form = document.getElementById('upload-form');
//     const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
// 	if (!dropzone || !fileInput) return;

// 	function highlight(on) {
// 		dropzone.classList.toggle('ring-2', on);
// 		dropzone.classList.toggle('ring-indigo-300', on);
// 	}

// 	['dragenter', 'dragover'].forEach(eventName => {
// 		dropzone.addEventListener(eventName, e => {
// 			e.preventDefault();
// 			e.stopPropagation();
// 			highlight(true);
// 		});
// 	});

// 	['dragleave', 'drop'].forEach(eventName => {
// 		dropzone.addEventListener(eventName, e => {
// 			e.preventDefault();
// 			e.stopPropagation();
// 			highlight(false);
// 		});
// 	});

//     dropzone.addEventListener('drop', e => {
// 		const dt = e.dataTransfer;
// 		if (dt && dt.files && dt.files.length) {
// 			fileInput.files = dt.files;
//             if (fileName) fileName.textContent = dt.files[0].name;
//             validateSelectedFile(dt.files[0]);
// 		}
// 	});

//     fileInput.addEventListener('change', () => {
//         if (fileInput.files && fileInput.files.length && fileName) {
//             fileName.textContent = fileInput.files[0].name;
//             validateSelectedFile(fileInput.files[0]);
//         }
//     });

//     if (form) {
//         form.addEventListener('submit', (e) => {
//             if (form.dataset.csvValid !== 'true') {
//                 e.preventDefault();
//             }
//         });
//     }

//     function setValidity(ok, message, foundHeaders) {
//         if (!fileError) return;
//         if (ok) {
//             fileError.textContent = '';
//             dropzone.classList.remove('ring-2', 'ring-red-300');
//             if (submitBtn) {
//                 submitBtn.disabled = false;
//                 submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
//             }
//             if (form) {
//                 form.dataset.csvValid = 'true';
//                 // Auto-submit once when valid
//                 if (form.dataset.autoSubmitted !== 'true') {
//                     form.dataset.autoSubmitted = 'true';
//                     form.submit();
//                 }
//             }
//         } else {
//             const details = foundHeaders && foundHeaders.length ? ` Found headers: ${foundHeaders.join(', ')}` : '';
//             fileError.textContent = `${message}${details}`;
//             dropzone.classList.add('ring-2', 'ring-red-300');
//             if (submitBtn) {
//                 submitBtn.disabled = true;
//                 submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
//             }
//             if (form) {
//                 form.dataset.csvValid = 'false';
//                 form.dataset.autoSubmitted = 'false';
//             }
//         }
//     }

//     function validateSelectedFile(file) {
//         if (!file) return;
//         const reader = new FileReader();
//         reader.onload = () => {
//             const text = (reader.result || '').toString();
//             const firstLine = getFirstNonEmptyLine(text);
//             if (!firstLine) {
//                 setValidity(false, 'CSV appears to be empty. Expected headers: Name, City, Phone');
//                 return;
//             }
//             const headers = parseCsvHeaders(firstLine);
//             const normalized = headers.map(h => h.replace(/^\ufeff/, '').trim().replace(/^"|"$/g, '').toLowerCase());
//             const expected = ['name','city','phone'];
//             const extra = normalized.filter(h => !expected.includes(h));
//             const missing = expected.filter(h => !normalized.includes(h));
//             if (normalized.length !== 3 || extra.length > 0 || missing.length > 0) {
//                 setValidity(false, 'CSV headers must be exactly: Name, City, Phone.', headers);
//             } else {
//                 setValidity(true);
//             }
//         };
//         reader.onerror = () => {
//             setValidity(false, 'Could not read the file. Please try again.');
//         };
//         // Read only the first 64KB to get headers quickly
//         reader.readAsText(file.slice(0, 65536));
//     }

//     function getFirstNonEmptyLine(text) {
//         const lines = text.split(/\r?\n/);
//         for (let i = 0; i < lines.length; i++) {
//             const line = lines[i].trim();
//             if (line) return line;
//         }
//         return '';
//     }

//     function parseCsvHeaders(line) {
//         // Simple CSV split handling quotes for common cases
//         const result = [];
//         let current = '';
//         let inQuotes = false;
//         for (let i = 0; i < line.length; i++) {
//             const ch = line[i];
//             if (ch === '"') {
//                 if (inQuotes && line[i + 1] === '"') { // escaped quote
//                     current += '"';
//                     i++;
//                 } else {
//                     inQuotes = !inQuotes;
//                 }
//             } else if (ch === ',' && !inQuotes) {
//                 result.push(current.trim());
//                 current = '';
//             } else {
//                 current += ch;
//             }
//         }
//         result.push(current.trim());
//         return result;
//     }
// }

// document.addEventListener('DOMContentLoaded', () => {
// 	initTabs();
// 	initDragDrop();

//     // Profile dropdown
//     const btn = document.getElementById('profile-button');
//     const menu = document.getElementById('profile-dropdown');
//     if (btn && menu) {
//         function closeMenu(e) {
//             if (!menu || !btn) return;
//             if (e && (btn.contains(e.target) || menu.contains(e.target))) return;
//             menu.classList.add('hidden');
//             document.removeEventListener('click', closeMenu);
//         }
//         btn.addEventListener('click', (e) => {
//             e.stopPropagation();
//             menu.classList.toggle('hidden');
//             if (!menu.classList.contains('hidden')) {
//                 setTimeout(() => document.addEventListener('click', closeMenu), 0);
//             } else {
//                 document.removeEventListener('click', closeMenu);
//             }
//         });
//     }
// });
// document.addEventListener("DOMContentLoaded", () => {
//     // CSRF token (meta tag should exist in your layout)
//     const csrfMeta = document.querySelector('meta[name="csrf-token"]');
//     const csrf = csrfMeta ? csrfMeta.getAttribute("content") : "";
  
//     // Table bodies
//     const resultsTable = document.querySelector("#results-table tbody");
//     const deletedTable = document.querySelector("#deleted-table tbody");
//     const deletedContainer = document.getElementById('deleted-container');
//     const deletedUrl = deletedContainer?.dataset.deletedUrl || '/deleted-table'; // fallback
//     // Modal elements
//     const modal = document.querySelector("#editModal");
//     const modalContent = document.querySelector("#modalContent");
  
//     // Form elements
//     const editForm = document.querySelector("#editForm");
//     const editIndex = document.querySelector("#editIndex");
//     const editName = document.querySelector("#editName");
//     const editPhone = document.querySelector("#editPhone");
//     const editMessage = document.querySelector("#editMessage");
//     const saveEditBtn = document.querySelector("#saveEditBtn");
//     const spinner = document.querySelector("#loadingSpinner");
  
//     // Close controls (header X and footer Cancel)
//     const closeModalBtn = document.querySelector("#closeModal");
//     const cancelBtn = document.querySelector(".cancelBtn");
  
//     // Safety: if results table doesn't exist, stop (nothing to bind)
//     if (!resultsTable) return;
  
//     // --- Modal open/close helpers ---
//     function openModal() {
//       if (!modal || !modalContent) return;
//       modal.classList.remove("hidden");
//       // allow one frame then trigger transition classes removal
//       requestAnimationFrame(() => {
//         modalContent.classList.remove("opacity-0", "scale-95");
//         modalContent.classList.add("opacity-100", "scale-100");
//       });
//     }
  
//     function closeModalWithAnimation() {
//       if (!modal || !modalContent) return;
//       modalContent.classList.remove("opacity-100", "scale-100");
//       modalContent.classList.add("opacity-0", "scale-95");
//       // hide overlay after animation completes (match duration in CSS: 300ms)
//       setTimeout(() => {
//         modal.classList.add("hidden");
//       }, 300);
//     }
  
//     // Close handlers: header X, footer Cancel
//     if (closeModalBtn) {
//       closeModalBtn.addEventListener("click", () => closeModalWithAnimation());
//     }
//     if (cancelBtn) {
//       cancelBtn.addEventListener("click", () => closeModalWithAnimation());
//     }
  
//     // Close by clicking overlay (but not when clicking modal content)
//     if (modal) {
//       modal.addEventListener("click", (e) => {
//         if (e.target === modal) {
//           closeModalWithAnimation();
//         }
//       });
//     }
//     if (modalContent) {
//       // prevent accidental propagation (optional)
//       modalContent.addEventListener("click", (e) => e.stopPropagation());
//     }
  
//     // --- Handle clicks inside results table (delegation) ---
//     resultsTable.addEventListener("click", async (e) => {
//       // EDIT button clicked
//       const editBtn = e.target.closest(".edit-btn");
//       if (editBtn) {
//         const row = editBtn.closest("tr");
//         if (!row) return;
//         const cells = row.querySelectorAll("td");
  
//         // Fill modal inputs (ID=0, Name=1, City=2, Phone=3, AQI=4, Message=5)
//         editIndex.value = row.dataset.id ?? ""; // hidden field (id)
//         editName.value = cells[1]?.textContent.trim() ?? "";
//         editPhone.value = cells[3]?.textContent.trim() ?? "";
//         editMessage.value = cells[5]?.textContent.trim() ?? "";
//         openModal();
//         return; // don't run delete logic
//       }
  
// // helper to refresh deleted table wrapper (pass either route URL or a link href)
// async function refreshDeletedTable(fetchUrl) {
//     try {
//       const res = await fetch(fetchUrl, {
//         headers: { 'X-Requested-With': 'XMLHttpRequest' }
//       });
//       if (!res.ok) throw new Error('Failed to fetch deleted table');
//       const html = await res.text();
//       const wrapper = document.querySelector('#deleted-table-wrapper');
//       if (wrapper) wrapper.innerHTML = html;
//     } catch (err) {
//       console.error('Deleted table fetch error:', err);
//     }
//   }

//   // Delegate clicks: delete buttons and deleted-table pagination links
//   document.addEventListener('click', async (e) => {
//     // 1) Delete button (in uploaded results table)
//     const deleteBtn = e.target.closest('.delete-btn');
//     if (deleteBtn) {
//       e.preventDefault();
//       const url = deleteBtn.dataset.url;
//       const row = deleteBtn.closest('tr');
//       const id = deleteBtn.dataset.id ?? row?.dataset.id;
//       if (!url || !row || !id) return;

//       try {
//         const res = await fetch(url, {
//           method: 'POST',
//           headers: {
//             'Content-Type': 'application/json',
//             'X-CSRF-TOKEN': csrf,
//             'X-Requested-With': 'XMLHttpRequest'
//           },
//           body: JSON.stringify({ id })
//         });

//         const json = await res.json();
//         if (json && json.success) {
//           // remove the row from the uploaded results table
//           row.remove();

//           // refresh deleted table (we use the base deletedUrl so new item appears on page 1)
//           await refreshDeletedTable(deletedUrl);
//         } else {
//           console.warn('Delete request returned failure:', json);
//         }
//       } catch (err) {
//         console.error('Delete request error:', err);
//       }
//       return;
//     }

//     // 2) Intercept deleted-table pagination links for AJAX
//     const pagelink = e.target.closest('#deleted-table-wrapper .pagination a');
//     if (pagelink) {
//       e.preventDefault();
//       const href = pagelink.href;
//       await refreshDeletedTable(href);
//       return;
//     }
//   });





//     });
  
//     // --- Save Edit (modal form submit) ---
//     if (editForm) {
//       editForm.addEventListener("submit", async (e) => {
//         e.preventDefault();
//         if (!saveEditBtn) return;
  
//         const url = saveEditBtn.dataset.url;
//         if (!url) {
//           console.error("Save URL not found on Save button (data-url).");
//           return;
//         }
  
//         const data = {
//             id: editIndex.value,
//             name: editName.value,
//             phone: editPhone.value,
//             message: editMessage.value,
//         };
          
//         // show spinner + disable save
//         if (spinner) spinner.classList.remove("hidden");
//         saveEditBtn.disabled = true;
  
//         try {
//           const res = await fetch(url, {
//             method: "POST",
//             headers: {
//               "Content-Type": "application/json",
//               "X-CSRF-TOKEN": csrf,
//             },
//             body: JSON.stringify(data),
//           });
//           const json = await res.json();
  
//           if (json && json.success) {
//             const row = resultsTable.querySelector(`tr[data-id='${data.id}']`);
//             if (row) {
//               const cells = row.querySelectorAll("td");
//               if (cells[1]) cells[1].textContent = data.name;    // Name
//               if (cells[3]) cells[3].textContent = data.phone;   // Phone
//               if (cells[5]) cells[5].textContent = data.message; // Message
//             }
          
//             // close modal with animation
//             closeModalWithAnimation();
//           } else {
//             console.warn("Update request returned failure:", json);
//           }
//         } catch (err) {
//           console.error("Update request error:", err);
//         } finally {
//           if (spinner) spinner.classList.add("hidden");
//           saveEditBtn.disabled = false;
//         }
//       });
//     }
//   });
  



//   document.getElementById('open-modal').addEventListener('click', function () {
//     document.getElementById('record-modal').classList.remove('hidden');
//     document.getElementById('record-modal').classList.add('flex');
//   });

//   document.getElementById('close-modal').addEventListener('click', function () {
//     document.getElementById('record-modal').classList.add('hidden');
//     document.getElementById('record-modal').classList.remove('flex');
//   });

//   document.addEventListener("DOMContentLoaded", function () {
//     const fetchBtn = document.getElementById("fetchAll");
//     const tableBody = document.getElementById("aqi-body");
//     let intervalId = null;
  
//     function renderRows(cities) {
//         tableBody.innerHTML = "";
//         cities.forEach(city => {
//           let aqiCell = "";
//           if (city.status === "pending") {
//             aqiCell = `<span class="text-slate-500">-</span>`;
//           } else if (city.status === "processing") {
//             aqiCell = `<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-green-600 border-t-transparent"></span>`;
//           } else if (city.status === "done") {
//             const color =
//               city.aqi <= 50 ? "bg-green-100 text-green-700" :
//               city.aqi <= 100 ? "bg-yellow-100 text-yellow-700" :
//               "bg-red-100 text-red-700";
//             aqiCell = `<span class="rounded-full px-2 py-1 text-xs font-semibold ${color}">${city.aqi}</span>`;
//           } else {
//             aqiCell = `<span class="text-red-600">Error</span>`;
//           }
      
//           tableBody.insertAdjacentHTML("beforeend", `
//             <tr class="hover:bg-indigo-50/40" data-id="${city.id}">
//               <td class="px-4 py-2 text-sm">${city.id}</td>
//               <td class="px-4 py-2 text-sm">${city.name}</td>
//               <td class="px-4 py-2 text-sm">${city.state}</td>
//               <td class="px-4 py-2 text-sm">${aqiCell}</td>
//               <td class="px-4 py-2 text-sm capitalize">${city.status}</td>
//             </tr>
//           `);
//         });
//       }
      
  
//     function loadCities() {
//       fetch("/status")
//         .then(res => res.json())
//         .then(data => renderRows(data));
//     }
  
//     fetchBtn.addEventListener("click", () => {
//         fetch("/fetch-all")
//           .then(res => res.json())
//           .then(data => {
//             // show success alert
//             const alertBox = `
//               <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 flex items-center justify-between">
//                 <span>${data.success}</span>
//                 <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
//                   ✕
//                 </button>
//               </div>
//             `;
//             document.getElementById("alert-container").innerHTML = alertBox;
      
//             // refresh table + start polling
//             loadCities();
//             if (!intervalId) {
//               intervalId = setInterval(loadCities, 5000);
//             }
//           });
//       });
      
  
//     // initial load
//     loadCities();
//   });
  
  

// // 🔹 Loader CSS
// const style = document.createElement("style");
// style.innerHTML = `
//     .loader {
//     display: inline-block;
//     width: 16px;
//     height: 16px;
//     border: 2px solid #16a34a;
//     border-top: 2px solid transparent;
//     border-radius: 50%;
//     animation: spin 0.7s linear infinite;
//     }
//     @keyframes spin {
//     100% { transform: rotate(360deg); }
//     }
// `;
// document.head.appendChild(style);
    

// document.addEventListener("DOMContentLoaded", function () {
//   const searchInput = document.getElementById("tableSearch");
//   const rows = document.querySelectorAll("#results-table tbody tr");

//   searchInput.addEventListener("keyup", function () {
//     const filter = this.value.toLowerCase();

//     rows.forEach(row => {
//       const text = row.innerText.toLowerCase();
//       row.style.display = text.includes(filter) ? "" : "none";
//     });
//   });
// });


// // put this once, outside of DOMContentLoaded blocks
// document.addEventListener("click", async function (e) {
//   const link = e.target.closest("#deleted-table-wrapper .pagination a");
//   if (!link) return;

//   e.preventDefault();

//   try {
//       const res = await fetch(link.href, {
//           headers: { "X-Requested-With": "XMLHttpRequest" }
//       });
//       const html = await res.text();
//       document.querySelector("#deleted-table-wrapper").innerHTML = html;
//   } catch (err) {
//       console.error("Pagination fetch error:", err);
//   }
// });
// document.addEventListener("DOMContentLoaded", () => {
//   const sendModal = document.getElementById("send-modal");
//   const sendModalContent = document.getElementById("sendModalContent");
//   const openSendBtn = document.getElementById("open-send-modal"); // button near Download
//   const closeSendBtn = document.getElementById("closeSendModal");

//   function openSendModal() {
//     if (!sendModal || !sendModalContent) return;
//     sendModal.classList.remove("hidden");
//     requestAnimationFrame(() => {
//       sendModalContent.classList.remove("opacity-0", "scale-95");
//       sendModalContent.classList.add("opacity-100", "scale-100");
//     });
//   }

//   function closeSendModalWithAnimation() {
//     if (!sendModal || !sendModalContent) return;
//     sendModalContent.classList.remove("opacity-100", "scale-100");
//     sendModalContent.classList.add("opacity-0", "scale-95");
//     setTimeout(() => {
//       sendModal.classList.add("hidden");
//     }, 300);
//   }

//   // open
//   openSendBtn?.addEventListener("click", openSendModal);

//   // close
//   closeSendBtn?.addEventListener("click", closeSendModalWithAnimation);

//   // close on overlay click
//   sendModal?.addEventListener("click", (e) => {
//     if (e.target === sendModal) closeSendModalWithAnimation();
//   });

//   sendModalContent?.addEventListener("click", (e) => e.stopPropagation());
// });



import { initTabs } from './tabs/tabs';
import { initDragDrop } from './upload/dragDrop';
import { initProfileDropdown } from './profile/dropdown';
import { initWhatsappModal } from './modals/whatsapp';
import { initEmailModal } from './modals/email';
import { initEditModal } from './modals/edit';
import { initRecordModal } from './modals/record';
import { initResultsTable } from './tables/results';
// import { initDeletedTable } from './tables/deleted';
import { initStatusTable } from './tables/status';
import { initSearch } from './utils/search';
import { injectLoaderCss } from './utils/loader';
import { initWhatsappMessageModal } from "./modals/whatsapp-message.js";
import { initEmailMessageModal } from "./modals/email-message.js";
import { initSaveCSV } from "./upload/save-csv.js";
import { initSaveOnLogoutConfirmation } from "./confirmation/save-on-logout.js";
import { initRecipients } from "./recipients/recipients.js";
import { initIncomingMessages } from "./messages/incoming-messages.js";


document.addEventListener("DOMContentLoaded", () => {

    initTabs();
    initDragDrop();
    initProfileDropdown();
    initWhatsappModal();
    initEmailModal();
    initEditModal();
    initRecordModal();
    initResultsTable();
    initStatusTable();
    initSearch();
    injectLoaderCss();
    initWhatsappMessageModal();
    initEmailMessageModal();
    initSaveCSV();
    initSaveOnLogoutConfirmation();
    initRecipients();
    initIncomingMessages();
})

export function initRecipients() {
    const uploadForm = document.getElementById('upload-recipients-form');
    const addModal = document.getElementById('addRecipientModal');
    const addModalContent = document.getElementById('addRecipientModalContent');
    const openAddBtn = document.getElementById('open-add-recipient-modal');
    const closeAddBtn = document.getElementById('closeAddRecipientModal');
    const cancelAddBtn = document.getElementById('cancelAddRecipient');
    const addForm = document.getElementById('add-recipient-form');
    const editForm = document.getElementById('edit-recipient-form');
    const fileInput = document.getElementById('recipients-file-input');
    const fileName = document.getElementById('recipients-file-name');
    const tableEl = document.getElementById('recipients-table');
    const tbody = document.getElementById('recipients-tbody');
    const totalCountEl = document.getElementById('recipients-total-count');
    const activeCountEl = document.getElementById('recipients-active-count');
    const overlay = document.getElementById('recipients-overlay');
    const editModal = document.getElementById('editRecipientModal');
    const closeEditBtn = document.getElementById('closeEditRecipientModal');
    const cancelEditBtn = document.getElementById('cancelEditRecipient');
    function openAddModal() {
        if (!addModal) return;
        addModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            addModalContent?.classList.remove('opacity-0', 'scale-95');
            addModalContent?.classList.add('opacity-100', 'scale-100');
        });
    }

    function closeAddModal() {
        if (!addModal) return;
        addModalContent?.classList.remove('opacity-100', 'scale-100');
        addModalContent?.classList.add('opacity-0', 'scale-95');
        setTimeout(() => addModal.classList.add('hidden'), 250);
    }
    const overlayText = document.getElementById('recipients-overlay-text');

    // Attach button event listeners regardless of other elements
    openAddBtn?.addEventListener('click', openAddModal);
    closeAddBtn?.addEventListener('click', closeAddModal);
    cancelAddBtn?.addEventListener('click', closeAddModal);
    addModal?.addEventListener('click', (e) => {
        if (e.target === addModal) closeAddModal();
    });
    addModalContent?.addEventListener('click', (e) => e.stopPropagation());

    if (!uploadForm && !addForm && !tbody) {
        return;
    }

    const baseUrl = tableEl?.dataset.baseUrl || '/whatsapp-recipients';
    const defaultHeaders = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json'
    };

    const jsonHeaders = {
        ...defaultHeaders,
        'Content-Type': 'application/json'
    };
    let startIndex = parseInt(tableEl?.dataset.startIndex || '1', 10);

    function escapeHtml(str = '') {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showMessage(message, type = 'success') {
        const existing = document.getElementById('recipients-message');
        if (existing) {
            existing.remove();
        }

        const node = document.createElement('div');
        node.id = 'recipients-message';
        node.className = `fixed top-4 right-4 z-[60] rounded-lg px-4 py-3 text-sm font-medium shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        node.textContent = message;

        document.body.appendChild(node);

        setTimeout(() => {
            node.style.opacity = '0';
            node.style.transform = 'translateX(100%)';
            setTimeout(() => node.remove(), 300);
        }, 4500);
    }

    function showLoading(button, text = 'Loading...') {
        if (!button) return;
        button.disabled = true;
        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }

        button.innerHTML = `<span class="inline-flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${text}
        </span>`;
    }

    function hideLoading(button) {
        if (!button) return;
        button.disabled = false;
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
        }
    }

    function toggleOverlay(show, text = 'Please wait...') {
        if (!overlay) return;
        if (overlayText && text) {
            overlayText.textContent = text;
        }
        overlay.classList.toggle('hidden', !show);
    }

    function parseCount(el) {
        return parseInt(el?.textContent?.replace(/[^0-9]/g, '') || '0', 10);
    }

    function updateCounts(deltaTotal = 0, deltaActive = 0) {
        if (totalCountEl && deltaTotal !== 0) {
            totalCountEl.textContent = Math.max(0, parseCount(totalCountEl) + deltaTotal);
        }
        if (activeCountEl && deltaActive !== 0) {
            activeCountEl.textContent = Math.max(0, parseCount(activeCountEl) + deltaActive);
        }
    }

    function rowClasses(recipient) {
        const base = 'transition-colors';
        return recipient?.active ? base : `${base} text-red-700`;
    }

    function statusButtonHTML(recipient) {
        const isActive = !!recipient.active;
        const statusClass = isActive
            ? 'bg-green-100 text-green-700 hover:bg-green-200'
            : 'bg-red-100 text-red-700 hover:bg-red-200';
        const text = isActive ? 'Active' : 'Inactive';
        const toggleUrl = `${baseUrl}/${recipient.id}/toggle-active`;

        return `<button 
            class="toggle-active-btn inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition-colors ${statusClass}"
            data-id="${recipient.id}"
            data-url="${toggleUrl}">
            ${text}
        </button>`;
    }

    function actionButtonsHTML(recipient) {
        const destroyUrl = `${baseUrl}/${recipient.id}`;
        return `<div class="inline-flex items-center gap-2">
            <button 
                class="edit-recipient-btn inline-flex items-center justify-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors"
                data-id="${recipient.id}">
                Edit
            </button>
            <button 
                class="delete-recipient-btn inline-flex items-center justify-center rounded-md bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-200 transition-colors"
                data-id="${recipient.id}"
                data-url="${destroyUrl}">
                Delete
            </button>
        </div>`;
    }

    function buildRowHTML(recipient, displayIndex = '') {
        const name = recipient.name ? escapeHtml(recipient.name) : '—';
        return `
            <td class="px-4 py-3 text-sm text-slate-700 font-medium recipient-index-cell">${displayIndex}</td>
            <td class="px-4 py-3 text-sm text-slate-800 font-mono">${escapeHtml(recipient.phone || '')}</td>
            <td class="px-4 py-3 text-sm text-slate-700">${name}</td>
            <td class="px-4 py-3 text-sm">
                ${statusButtonHTML(recipient)}
            </td>
            <td class="px-4 py-3 text-sm">
                ${actionButtonsHTML(recipient)}
            </td>
        `;
    }

    function setRowDataset(row, recipient, displayIndex = '') {
        row.dataset.recipientId = recipient.id;
        row.dataset.phone = recipient.phone || '';
        row.dataset.name = recipient.name || '';
        row.dataset.active = recipient.active ? '1' : '0';
        if (displayIndex !== '') {
            row.dataset.displayIndex = displayIndex;
        }
    }

    function createRow(recipient, displayIndex) {
        const row = document.createElement('tr');
        row.className = rowClasses(recipient);
        setRowDataset(row, recipient, displayIndex);
        row.innerHTML = buildRowHTML(recipient, displayIndex);
        return row;
    }

    function insertRow(recipient, { prepend = false } = {}) {
        if (!tbody) return;
        const currentCount = tbody.children.length;
        const displayIndex = prepend ? startIndex : startIndex + currentCount;
        const newRow = createRow(recipient, displayIndex);
        if (prepend && tbody.firstChild) {
            tbody.prepend(newRow);
        } else {
            tbody.appendChild(newRow);
        }
        if (prepend) {
            startIndex -= 1;
        }
        renumberRows();
        highlightRow(newRow);
    }

    function getRow(id) {
        return tbody?.querySelector(`[data-recipient-id="${id}"]`);
    }

    function updateRow(recipient) {
        const row = getRow(recipient.id);
        if (!row) {
            insertRow(recipient, { prepend: false });
            return;
        }
        const displayIndex = row.dataset.displayIndex || '';
        setRowDataset(row, recipient, displayIndex);
        row.className = rowClasses(recipient);
        row.innerHTML = buildRowHTML(recipient, displayIndex);
        highlightRow(row);
    }

    function removeRow(id) {
        const row = getRow(id);
        if (!row) return { wasActive: false };
        const wasActive = row.dataset.active === '1';
        row.classList.add('opacity-0', '-translate-x-6');
        setTimeout(() => {
            row.remove();
            renumberRows();
        }, 200);
        return { wasActive };
    }

    function highlightRow(row) {
        if (!row) return;
        row.classList.add('ring-2', 'ring-indigo-200');
        setTimeout(() => {
            row.classList.remove('ring-2', 'ring-indigo-200');
        }, 1200);
    }

    function renumberRows() {
        if (!tbody) return;
        let displayIndex = startIndex;
        tbody.querySelectorAll('tr').forEach((row) => {
            row.dataset.displayIndex = displayIndex;
            const cell = row.querySelector('.recipient-index-cell');
            if (cell) {
                cell.textContent = displayIndex;
            }
            displayIndex += 1;
        });
    }

    renumberRows();

    // Function to refresh the entire table
    async function refreshTable() {
        if (!tbody) return;
        
        try {
            const response = await fetch(`${baseUrl}/list`, {
                method: 'GET',
                headers: defaultHeaders
            });
            const result = await response.json();

            if (result.success && result.recipients) {
                // Clear existing rows
                tbody.innerHTML = '';
                
                // Update counts
                if (totalCountEl) totalCountEl.textContent = result.totalCount || 0;
                if (activeCountEl) activeCountEl.textContent = result.activeCount || 0;

                // Reset start index
                startIndex = 1;

                // Add all recipients
                if (result.recipients.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-base font-semibold text-slate-500">
                                No recipients found. Add your first recipient above!
                            </td>
                        </tr>
                    `;
                } else {
                    result.recipients.forEach((recipient, index) => {
                        insertRow(recipient, { prepend: false });
                    });
                }
            }
        } catch (error) {
            console.error('Error refreshing table:', error);
        }
    }

    // CSV Upload
    let isUploadingCsv = false;

    async function handleRecipientsUpload() {
        if (!uploadForm || !fileInput) return;
        const errorDiv = document.getElementById('recipients-file-error');

        if (!fileInput.files?.length || isUploadingCsv) {
            if (!isUploadingCsv) {
                showMessage('Please select a file to upload', 'error');
            }
            return;
        }

        if (errorDiv) errorDiv.textContent = '';
        const formData = new FormData(uploadForm);

        try {
            isUploadingCsv = true;
            toggleOverlay(true, 'Uploading recipients...');
            fileInput.disabled = true;

            const response = await fetch(`${baseUrl}/upload-csv`, {
                method: 'POST',
                headers: defaultHeaders,
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showMessage(result.message || 'Recipients uploaded successfully!', 'success');
                fileInput.value = '';
                if (fileName) fileName.textContent = '';
                // Refresh table instead of reloading page
                await refreshTable();
            } else {
                const errorText = result.error || result.message || 'Failed to upload file';
                showMessage(errorText, 'error');
                if (errorDiv) errorDiv.textContent = errorText;
            }
        } catch (error) {
            const message = `Error uploading file: ${error.message}`;
            showMessage(message, 'error');
            if (errorDiv) errorDiv.textContent = message;
        } finally {
            isUploadingCsv = false;
            toggleOverlay(false);
            fileInput.disabled = false;
        }
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleRecipientsUpload();
        });
    }

    // Manual add
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = addForm.querySelector('button[type="submit"]');
            const formData = new FormData(addForm);
            const activeCheckbox = document.getElementById('active');
            const payload = {
                phone: formData.get('phone'),
                name: formData.get('name'),
                active: activeCheckbox ? activeCheckbox.checked : true
            };

            if (!payload.phone || payload.phone.trim() === '') {
                showMessage('Phone number is required', 'error');
                return;
            }

            showLoading(submitBtn, 'Adding...');

            try {
                const response = await fetch(baseUrl, {
                    method: 'POST',
                    headers: jsonHeaders,
                    body: JSON.stringify(payload)
                });
                const result = await response.json();

                    if (result.success) {
                        showMessage(result.message || 'Recipient added successfully!', 'success');
                        addForm.reset();
                        closeAddModal();
                        if (result.recipient) {
                            insertRow(result.recipient, { prepend: false });
                            updateCounts(1, result.recipient.active ? 1 : 0);
                        }
                } else {
                    showMessage(result.message || 'Failed to add recipient', 'error');
                }
            } catch (error) {
                showMessage(`Error adding recipient: ${error.message}`, 'error');
            } finally {
                hideLoading(submitBtn);
            }
        });
    }

    // Edit form submission
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = editForm.querySelector('button[type="submit"]');
            const id = document.getElementById('edit-recipient-id')?.value;
            const formData = new FormData(editForm);
            const activeCheckbox = document.getElementById('edit-active');
            const payload = {
                phone: formData.get('phone'),
                name: formData.get('name'),
                active: activeCheckbox ? activeCheckbox.checked : false
            };

            if (!payload.phone || payload.phone.trim() === '') {
                showMessage('Phone number is required', 'error');
                return;
            }

            showLoading(submitBtn, 'Updating...');

            try {
                const response = await fetch(`${baseUrl}/${id}`, {
                    method: 'PUT',
                    headers: jsonHeaders,
                    body: JSON.stringify(payload)
                });
                const result = await response.json();

                if (result.success && result.recipient) {
                    const previousActive = getRow(result.recipient.id)?.dataset.active === '1';
                    updateRow(result.recipient);
                    const deltaActive = (result.recipient.active ? 1 : 0) - (previousActive ? 1 : 0);
                    updateCounts(0, deltaActive);
                    showMessage(result.message || 'Recipient updated successfully!', 'success');
                    closeEditModal();
                } else {
                    showMessage(result.message || 'Failed to update recipient', 'error');
                }
            } catch (error) {
                showMessage(`Error updating recipient: ${error.message}`, 'error');
            } finally {
                hideLoading(submitBtn);
            }
        });
    }

    // File input UI
    if (fileInput && fileName) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                fileName.className = 'mt-2 text-sm text-green-600';
                handleRecipientsUpload();
            } else {
                fileName.textContent = '';
            }
        });
    }

    // Table actions
    if (tbody) {
        tbody.addEventListener('click', async (event) => {
            const editBtn = event.target.closest('.edit-recipient-btn');
            const deleteBtn = event.target.closest('.delete-recipient-btn');
            const toggleBtn = event.target.closest('.toggle-active-btn');

            if (editBtn) {
                const row = editBtn.closest('tr');
                if (!row) return;
                document.getElementById('edit-recipient-id').value = row.dataset.recipientId;
                document.getElementById('edit-phone').value = row.dataset.phone || '';
                document.getElementById('edit-name').value = row.dataset.name || '';
                document.getElementById('edit-active').checked = row.dataset.active === '1';
                openEditModal();
                return;
            }

            if (deleteBtn) {
                if (!confirm('Are you sure you want to delete this recipient?')) return;
                showLoading(deleteBtn, 'Deleting...');
                const id = deleteBtn.dataset.id;
                const url = deleteBtn.dataset.url || `${baseUrl}/${id}`;

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: jsonHeaders
                    });
                    const result = await response.json();

                    if (result.success) {
                        const { wasActive } = removeRow(id);
                        updateCounts(-1, wasActive ? -1 : 0);
                        showMessage(result.message || 'Recipient deleted successfully!', 'success');
                    } else {
                        showMessage(result.message || 'Failed to delete recipient', 'error');
                    }
                } catch (error) {
                    showMessage(`Error deleting recipient: ${error.message}`, 'error');
                } finally {
                    hideLoading(deleteBtn);
                }
                return;
            }

            if (toggleBtn) {
                showLoading(toggleBtn, '');
                const id = toggleBtn.dataset.id;
                const url = toggleBtn.dataset.url || `${baseUrl}/${id}/toggle-active`;
                const row = getRow(id);
                const wasActive = row?.dataset.active === '1';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: jsonHeaders
                    });
                    const result = await response.json();

                    if (result.success) {
                        const isActive = !!result.active;
                        if (row) {
                            row.dataset.active = isActive ? '1' : '0';
                            row.className = rowClasses({ active: isActive });
                        }
                        const deltaActive = isActive === wasActive ? 0 : (isActive ? 1 : -1);
                        updateCounts(0, deltaActive);
                        toggleBtn.className = `toggle-active-btn inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition-colors ${
                            isActive ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200'
                        }`;
                        toggleBtn.textContent = isActive ? 'Active' : 'Inactive';
                        toggleBtn.dataset.originalHtml = toggleBtn.innerHTML;
                        showMessage(result.message || 'Status updated successfully!', 'success');
                    } else {
                        showMessage(result.message || 'Failed to update status', 'error');
                    }
                } catch (error) {
                    showMessage(`Error updating status: ${error.message}`, 'error');
                } finally {
                    hideLoading(toggleBtn);
                }
            }
        });
    }
    function openEditModal() {
        if (!editModal) return;
        editModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            const content = editModal.querySelector('.bg-white');
            if (content) {
                content.classList.remove('opacity-0', 'scale-95');
                content.classList.add('opacity-100', 'scale-100');
            }
        });
    }

    function closeEditModal() {
        if (!editModal) return;
        const content = editModal.querySelector('.bg-white');
        if (content) {
            content.classList.remove('opacity-100', 'scale-100');
            content.classList.add('opacity-0', 'scale-95');
        }
        setTimeout(() => editModal.classList.add('hidden'), 250);
    }

    closeEditBtn?.addEventListener('click', closeEditModal);
    cancelEditBtn?.addEventListener('click', closeEditModal);
    editModal?.addEventListener('click', (e) => {
        if (e.target === editModal) closeEditModal();
    });
    editModal?.querySelector('.bg-white')?.addEventListener('click', (e) => e.stopPropagation());
}

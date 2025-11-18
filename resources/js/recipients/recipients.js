export function initRecipients() {
    // Add recipient form
    const addForm = document.getElementById('add-recipient-form');
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addForm);
            const data = {
                phone: formData.get('phone'),
                name: formData.get('name'),
                active: formData.get('active') === 'on'
            };

            try {
                const response = await fetch('/whatsapp-recipients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error adding recipient: ' + error.message);
            }
        });
    }

    // Edit recipient buttons
    const editButtons = document.querySelectorAll('.edit-recipient-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            try {
                const response = await fetch(`/whatsapp-recipients/${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const recipient = result.recipient;
                    document.getElementById('edit-recipient-id').value = recipient.id;
                    document.getElementById('edit-phone').value = recipient.phone;
                    document.getElementById('edit-name').value = recipient.name || '';
                    document.getElementById('edit-active').checked = recipient.active;
                    
                    openEditModal();
                }
            } catch (error) {
                alert('Error loading recipient: ' + error.message);
            }
        });
    });

    // Delete recipient buttons
    const deleteButtons = document.querySelectorAll('.delete-recipient-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this recipient?')) return;
            
            const id = btn.getAttribute('data-id');
            const url = btn.getAttribute('data-url');
            
            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error deleting recipient: ' + error.message);
            }
        });
    });

    // Toggle active status
    const toggleButtons = document.querySelectorAll('.toggle-active-btn');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-id');
            const url = btn.getAttribute('data-url');
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error updating status: ' + error.message);
            }
        });
    });

    // Edit form submission
    const editForm = document.getElementById('edit-recipient-form');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('edit-recipient-id').value;
            const formData = new FormData(editForm);
            const data = {
                phone: formData.get('phone'),
                name: formData.get('name'),
                active: formData.get('active') === 'on'
            };

            try {
                const response = await fetch(`/whatsapp-recipients/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    closeEditModal();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error updating recipient: ' + error.message);
            }
        });
    }

    // File upload handling
    const fileInput = document.getElementById('recipients-file-input');
    const fileName = document.getElementById('recipients-file-name');
    
    if (fileInput && fileName) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = `Selected: ${file.name}`;
            }
        });
    }
}

function openEditModal() {
    const modal = document.getElementById('editRecipientModal');
    if (modal) {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            const content = modal.querySelector('.bg-white');
            if (content) {
                content.classList.remove('opacity-0', 'scale-95');
                content.classList.add('opacity-100', 'scale-100');
            }
        });
    }
}

function closeEditModal() {
    const modal = document.getElementById('editRecipientModal');
    if (modal) {
        const content = modal.querySelector('.bg-white');
        if (content) {
            content.classList.remove('opacity-100', 'scale-100');
            content.classList.add('opacity-0', 'scale-95');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

// Close modal handlers
document.addEventListener('DOMContentLoaded', () => {
    const closeBtn = document.getElementById('closeEditRecipientModal');
    const cancelBtn = document.getElementById('cancelEditRecipient');
    const modal = document.getElementById('editRecipientModal');

    closeBtn?.addEventListener('click', closeEditModal);
    cancelBtn?.addEventListener('click', closeEditModal);
    
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeEditModal();
    });
    
    const modalContent = modal?.querySelector('.bg-white');
    modalContent?.addEventListener('click', (e) => e.stopPropagation());
});


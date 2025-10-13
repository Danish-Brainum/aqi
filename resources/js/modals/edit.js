export function initEditModal() {
    const modal = document.querySelector("#editModal");
    const modalContent = document.querySelector("#modalContent");
    const closeModalBtn = document.querySelector("#closeModal");
    const cancelBtn = document.querySelector("#cancelBtn");

    const editForm = document.querySelector("#editForm");
    const editIndex = document.querySelector("#editIndex");
    const editName = document.querySelector("#editName");
    const editEmail = document.querySelector("#editEmail");
    const editPhone = document.querySelector("#editPhone");
    const editMessage = document.querySelector("#editMessage");
    const saveEditBtn = document.querySelector("#saveEditBtn");
    const spinner = document.querySelector("#loadingSpinner");
    const resultsTable = document.querySelector("#results-table tbody");

    if (!modal || !modalContent || !editForm || !resultsTable) return;

    function openModal() {
        modal.classList.remove("hidden");
        requestAnimationFrame(() => {
            modalContent.classList.remove("opacity-0", "scale-95");
            modalContent.classList.add("opacity-100", "scale-100");
        });
    }

    function closeModalWithAnimation() {
        modalContent.classList.remove("opacity-100", "scale-100");
        modalContent.classList.add("opacity-0", "scale-95");
        setTimeout(() => modal.classList.add("hidden"), 300);
    }

    closeModalBtn?.addEventListener("click", closeModalWithAnimation);
    cancelBtn?.addEventListener("click", closeModalWithAnimation);
    modal?.addEventListener("click", e => { if (e.target === modal) closeModalWithAnimation(); });
    modalContent?.addEventListener("click", e => e.stopPropagation());

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta?.getAttribute("content") || "";

    resultsTable.addEventListener("click", e => {
        const editBtn = e.target.closest(".edit-btn");
        if (!editBtn) return;
        const row = editBtn.closest("tr");
        if (!row) return;

        const cells = row.querySelectorAll("td");
        editIndex.value = row.dataset.id ?? "";
        editName.value = cells[1]?.textContent.trim() ?? "";
        editEmail.value = cells[2]?.textContent.trim() ?? "";
        editPhone.value = cells[4]?.textContent.trim() ?? "";
        editMessage.value = cells[6]?.textContent.trim() ?? "";
        openModal();
    });

    editForm.addEventListener("submit", async e => {
        e.preventDefault();
        if (!saveEditBtn) return;
        const url = saveEditBtn.dataset.url;
        if (!url) return;

        const data = {
            id: editIndex.value,
            name: editName.value,
            email: editEmail.value,
            phone: editPhone.value,
            message: editMessage.value,
        };

        spinner?.classList.remove("hidden");
        saveEditBtn.disabled = true;

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify(data),
            });
            const json = await res.json();
            if (json?.success) {
                const row = resultsTable.querySelector(`tr[data-id='${data.id}']`);
                if (row) {
                    const cells = row.querySelectorAll("td");
                    if (cells[1]) cells[1].textContent = data.name;
                    if (cells[2]) cells[2].textContent = data.email;
                    if (cells[4]) cells[4].textContent = data.phone;
                    if (cells[6]) cells[6].textContent = data.message;
                }
                closeModalWithAnimation();
                showAlert("Record updated successfully!", "success"); // ✅ Success alert

            } else {
                console.alert("Update failed:", json);
                showAlert("Update failed! Please try again.", "error"); // ❌ Error alert
            }
        } catch (err) {
            console.error(err);
            showAlert("An error occurred. Please try again.", "error"); // ❌ Catch error

        } finally {
            spinner?.classList.add("hidden");
            saveEditBtn.disabled = false;
        }
    });
}

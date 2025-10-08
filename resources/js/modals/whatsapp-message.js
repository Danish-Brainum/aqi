export function initWhatsappMessageModal() {
    const modal = document.querySelector("#customMessageModal");
    const modalContent = document.querySelector("#customMessageContent");
    const openBtn = document.querySelector("#whatsappMessage");
    const closeBtn = document.querySelector("#closeWhatsappMessageModal");
    const closeWhatsappMessageBtn = document.querySelector("#closeWhatsappMessageBtn");

    if (!modal || !modalContent || !openBtn) return;

    function openModal() {
        modal.classList.remove("hidden");
        requestAnimationFrame(() => {
            modalContent.classList.remove("opacity-0", "scale-95");
            modalContent.classList.add("opacity-100", "scale-100");
        });
    }

    function closeModal() {
        modalContent.classList.remove("opacity-100", "scale-100");
        modalContent.classList.add("opacity-0", "scale-95");
        setTimeout(() => modal.classList.add("hidden"), 300);
    }

    openBtn.addEventListener("click", openModal);
    closeBtn?.addEventListener("click", closeModal);
    closeWhatsappMessageBtn?.addEventListener("click", closeModal);
    modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
    modalContent.addEventListener("click", e => e.stopPropagation());
}

export function initEmailMessageModal() {
    const emailModal = document.querySelector("#emailMessageModal");
    const emailModalContent = document.querySelector("#emailModalContent");
    const openEmailBtn = document.querySelector("#open-email-modal");
    const closeEmailBtn = document.querySelector("#closeEmailMessageModal");
    const closeEmailMessageBtn = document.querySelector("#closeEmailMessageBtn");

    function openEmailModal() {
        emailModal.classList.remove("hidden");
        requestAnimationFrame(() => {
            emailModalContent.classList.remove("opacity-0", "scale-95");
            emailModalContent.classList.add("opacity-100", "scale-100");
        });
    }

    function closeEmailModal() {
        emailModalContent.classList.remove("opacity-100", "scale-100");
        emailModalContent.classList.add("opacity-0", "scale-95");
        setTimeout(() => emailModal.classList.add("hidden"), 300);
    }

    openEmailBtn?.addEventListener("click", openEmailModal);
    closeEmailBtn?.addEventListener("click", closeEmailModal);
    closeEmailMessageBtn?.addEventListener("click", closeEmailModal);
    emailModal?.addEventListener("click", e => { if (e.target === emailModal) closeEmailModal(); });
    emailModalContent?.addEventListener("click", e => e.stopPropagation());
};

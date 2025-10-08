export function initWhatsappModal() {
    const whatsappModal = document.getElementById("whatsapp-modal");
    const whatsappModalContent = document.getElementById("whatsappModalContent");
    const openWhatsappBtn = document.getElementById("open-whatsapp-modal");
    const closeWhatsappModal = document.getElementById("closeWhatsappModal");
    const closeWhatsappBtn = document.getElementById("closeWhatsappBtn");

    if(!whatsappModal||!whatsappModalContent) return;

    function openWhatsappModalFunc() {
        whatsappModal.classList.remove("hidden");
        requestAnimationFrame(()=> {
            whatsappModalContent.classList.remove("opacity-0","scale-95");
            whatsappModalContent.classList.add("opacity-100","scale-100");
        });
    }

    function closeWhatsappModalFunc() {
        whatsappModalContent.classList.remove("opacity-100","scale-100");
        whatsappModalContent.classList.add("opacity-0","scale-95");
        setTimeout(()=>whatsappModal.classList.add("hidden"),300);
    }
    openWhatsappBtn?.addEventListener("click", openWhatsappModalFunc);
    closeWhatsappModal?.addEventListener("click", closeWhatsappModalFunc);
    closeWhatsappBtn?.addEventListener("click", closeWhatsappModalFunc);
    whatsappModal?.addEventListener("click", e=>{ if(e.target===whatsappModal) closeWhatsappModalFunc(); });
    whatsappModalContent?.addEventListener("click", e=>e.stopPropagation());
}

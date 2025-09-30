export function initSendModal() {
    const sendModal = document.getElementById("send-modal");
    const sendModalContent = document.getElementById("sendModalContent");
    const openSendBtn = document.getElementById("open-send-modal");
    const closeSendBtn = document.getElementById("closeSendModal");

    if(!sendModal||!sendModalContent) return;

    function openSendModalFunc() {
        sendModal.classList.remove("hidden");
        requestAnimationFrame(()=> {
            sendModalContent.classList.remove("opacity-0","scale-95");
            sendModalContent.classList.add("opacity-100","scale-100");
        });
    }

    function closeSendModalFunc() {
        sendModalContent.classList.remove("opacity-100","scale-100");
        sendModalContent.classList.add("opacity-0","scale-95");
        setTimeout(()=>sendModal.classList.add("hidden"),300);
    }

    openSendBtn?.addEventListener("click", openSendModalFunc);
    closeSendBtn?.addEventListener("click", closeSendModalFunc);
    sendModal?.addEventListener("click", e=>{ if(e.target===sendModal) closeSendModalFunc(); });
    sendModalContent?.addEventListener("click", e=>e.stopPropagation());
}

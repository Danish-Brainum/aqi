export function initEmailModal() {
    // const emailModal = document.getElementById("email-modal");
    // const emailModalContent = document.getElementById("emailModalContent");
    // const openEmailBtn = document.getElementById("open-email-modal");
    // const closeEmailModal = document.getElementById("closeEmailModal");
    // const closeEmailBtn = document.getElementById("closeEmailBtn");

    // if(!emailModal||!emailModalContent) return;

    // function openEmailModalFunc() {
    //     emailModal.classList.remove("hidden");
    //     requestAnimationFrame(()=> {
    //         emailModalContent.classList.remove("opacity-0","scale-95");
    //         emailModalContent.classList.add("opacity-100","scale-100");
    //     });
    // }

    // function closeEmailModalFunc() {
    //     emailModalContent.classList.remove("opacity-100","scale-100");
    //     emailModalContent.classList.add("opacity-0","scale-95");
    //     setTimeout(()=>emailModal.classList.add("hidden"),300);
    // }

    // openEmailBtn?.addEventListener("click", openEmailModalFunc);
    // closeEmailBtn?.addEventListener("click", closeEmailModalFunc);
    // closeEmailModal?.addEventListener("click", closeEmailModalFunc);
    // emailModal?.addEventListener("click", e=>{ if(e.target===emailModal) closeEmailModalFunc(); });
    // emailModalContent?.addEventListener("click", e=>e.stopPropagation());

    const sendBtn = document.getElementById("send-emails-btn");
    
    if(!sendBtn) return;

    sendBtn.addEventListener("click", async () => {
    console.log('fsadfasfdas');

        if(!confirm("Are you sure you want to send emails to all results?")) return;
        const url = sendBtn.dataset.url;

        try {
        const res = await fetch(url, {

            method: "POST",
            headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "X-Requested-With": "XMLHttpRequest"
            }
        });
        const data = await res.json();
        if(data.success){
            alert(`✅ Emails sent to ${data.count} recipients.`);
        } else {
            alert(`⚠️ ${data.message}`);
        }
        } catch(err){
        console.error(err);
        alert("❌ Failed to send emails.");
        }
    });
    
}

export function initRecordModal() {
    const modal = document.getElementById('record-modal');
    const openBtn = document.getElementById('open-modal');
    const closeBtn = document.getElementById('close-modal');

    if (!modal || !openBtn || !closeBtn) return;

    openBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });

    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
}

export function initTabs() {
    function setActiveTab(targetId) {
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(section => section.classList.add('hidden'));
        const active = document.getElementById(targetId);
        if (active) active.classList.remove('hidden');

        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.removeAttribute('data-active'));
        const activeBtn = document.querySelector(`.tab-btn[data-tab="${targetId}"]`);
        if (activeBtn) activeBtn.setAttribute('data-active', 'true');
    }

    const buttons = document.querySelectorAll('.tab-btn');
    if (!buttons.length) return;
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab');
            if (target) setActiveTab(target);
        });
    });

    setActiveTab('upload');
}

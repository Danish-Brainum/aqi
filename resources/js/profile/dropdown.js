export function initProfileDropdown() {
    const btn = document.getElementById('profile-button');
    const menu = document.getElementById('profile-dropdown');
    if (!btn || !menu) return;

    function closeMenu(e){
        if(e && (btn.contains(e.target)||menu.contains(e.target))) return;
        menu.classList.add('hidden');
        document.removeEventListener('click', closeMenu);
    }

    btn.addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
        if(!menu.classList.contains('hidden')) setTimeout(()=>document.addEventListener('click', closeMenu),0);
        else document.removeEventListener('click', closeMenu);
    });
}

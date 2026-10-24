const sidebar = document.getElementById('sidebar');
const menuToggler = document.getElementById('menuToggler');
const sidebarClose = document.getElementById('sidebarClose');
const overlay = document.getElementById('sidebarOverlay');

if (menuToggler) {
    menuToggler.addEventListener('click', () => {
        sidebar.classList.add('open');
        overlay.classList.add('active');
    });
}

if (sidebarClose) {
    sidebarClose.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
}
// search
const searchToggle = document.getElementById('searchToggle');
const searchBox = document.getElementById('searchBox');

if (searchToggle && searchBox) {
    searchToggle.addEventListener('click', () => {
        searchBox.classList.toggle('active');

        if (searchBox.classList.contains('active')) {
            const input = searchBox.querySelector('input');
            if (input) {
                input.focus();
            }
        }
    });

}


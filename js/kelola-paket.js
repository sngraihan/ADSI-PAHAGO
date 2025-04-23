function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('closed');
}

function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('active');
}

// Close dropdown if clicked outside
window.onclick = function(event) {
    if (!event.target.matches('.profile-pic')) {
        const dropdowns = document.getElementsByClassName("profile-dropdown");
        for (let i = 0; i < dropdowns.length; i++) {
            const openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('active')) {
                openDropdown.classList.remove('active');
            }
        }
    }
}
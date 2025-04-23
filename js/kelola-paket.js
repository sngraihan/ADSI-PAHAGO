// Toggle sidebar on mobile
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar")
  sidebar.classList.toggle("closed")
}

// Toggle profile dropdown with proper event handling
function toggleDropdown(event) {
  if (event) {
    event.stopPropagation()
  }
  const dropdown = document.getElementById("profileDropdown")
  if (dropdown) {
    dropdown.classList.toggle("active")
  }
}

// Close dropdown if clicked outside
document.addEventListener("click", (event) => {
  const profileWrapper = document.getElementById("profileWrapper")
  const profileDropdown = document.getElementById("profileDropdown")

  if (
    profileWrapper &&
    profileDropdown &&
    !profileWrapper.contains(event.target) &&
    !profileDropdown.contains(event.target)
  ) {
    profileDropdown.classList.remove("active")
  }
})

// Add event listeners when the DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Profile dropdown toggle
  const profileWrapper = document.getElementById("profileWrapper")
  if (profileWrapper) {
    profileWrapper.addEventListener("click", toggleDropdown)
  }

  // Sidebar toggle
  const toggleBtn = document.getElementById("toggleSidebar")
  if (toggleBtn) {
    toggleBtn.addEventListener("click", toggleSidebar)
  }
})

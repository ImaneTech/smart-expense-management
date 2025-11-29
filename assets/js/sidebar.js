document.addEventListener("DOMContentLoaded", () => {
    const body = document.querySelector("body");
    const sidebar = document.querySelector(".sidebar");
    const toggleSidebar = document.querySelector(".toggle-sidebar");
    const modeSwitch = document.querySelector(".toggle-switch");

    if (!sidebar || !toggleSidebar || !modeSwitch) return; // prevent errors if elements missing

    // Toggle sidebar open/close
    toggleSidebar.addEventListener("click", () => {
        sidebar.classList.toggle("close");

        // Rotate arrow dynamically
        toggleSidebar.style.transform = sidebar.classList.contains("close") 
            ? "translateY(-50%) rotate(180deg)" 
            : "translateY(-50%) rotate(0deg)";
    });

    // Toggle dark mode
    modeSwitch.addEventListener("click", () => {
        body.classList.toggle("dark");
    });
    console.log("GoTrackr Sidebar JS chargé avec succès !");
});

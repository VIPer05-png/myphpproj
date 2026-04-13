document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("cyberhut_theme") || "default";
    if (savedTheme !== "default") {
        document.documentElement.setAttribute("data-theme", savedTheme);
    }
});

function setTheme(themeName) {
    if (themeName === "default") {
        document.documentElement.removeAttribute("data-theme");
    } else {
        document.documentElement.setAttribute("data-theme", themeName);
    }
    localStorage.setItem("cyberhut_theme", themeName);
}

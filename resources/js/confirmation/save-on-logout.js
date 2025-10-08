export function initSaveOnLogoutConfirmation() {
    document.getElementById('logout-btn').addEventListener('click', function (e) {
        e.preventDefault(); // stop form from submitting
        const url = this.getAttribute('data-url'); // get route from data-url
        console.log("Logout button clicked");
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (confirm("Do you want to save your changes before logging out?")) {
            console.log("User chose YES → calling saveCSV...");
    
            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN":csrf,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({})
            })
            .then(res => {
                console.log("Fetch response status:", res.status);
                return res.json();
            })
            .then(data => {
                console.log("SaveCSV response:", data);
                console.log("Now submitting logout form...");
                document.getElementById('logout-form').submit();
            })
            .catch(err => {
                console.error("Error saving CSV:", err);
                console.log("Proceeding to logout anyway...");
                document.getElementById('logout-form').submit();
            });
    
        } else {
            console.log("User chose NO → logging out immediately");
            document.getElementById('logout-form').submit();
        }
    });
}
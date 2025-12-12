// Recherche instantanée dans le tableau
document.getElementById('searchInput').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#demandesTable tbody tr');

    rows.forEach(row => {
        let employe = row.cells[0].textContent.toLowerCase();
        if (employe.indexOf(filter) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

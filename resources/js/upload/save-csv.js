export function initSaveCSV() {
    document.getElementById('save-CSV').addEventListener('click', function () {
    let url = this.getAttribute('data-url');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Something went wrong.');
    });
});
}
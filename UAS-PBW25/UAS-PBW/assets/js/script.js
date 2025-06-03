function validateForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (username === '' || password === '') {
        alert('Please fill in both username and password.');
        return false;
    }

    // Tambahkan validasi tambahan jika diperlukan
    return true;
}
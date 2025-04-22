document.getElementById("loginBtn").addEventListener("click", function () {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    if (!email || !password) {
        Swal.fire({
            icon: 'warning',
            title: 'Form tidak lengkap',
            text: 'Email dan password harus diisi!',
        });
        return;
    }

    fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.text())
    .then(result => {
        if (result === "success") {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil masuk!',
                text: 'Kamu akan diarahkan ke beranda.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "index.php";
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal masuk',
                text: result
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: error.message
        });
    });
});
document.getElementById("submitBtn").addEventListener("click", function () {
    const name = document.getElementById("fullName").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    if (!name || !email || !phone || !password || !confirmPassword) {
        Swal.fire({
            icon: 'warning',
            title: 'Form tidak lengkap',
            text: 'Semua field wajib diisi!',
        });
        return;
    }

    if (password !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Password tidak cocok',
            text: 'Silakan pastikan konfirmasi password sesuai.',
        });
        return;
    }

    fetch("register.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `fullName=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.text())
    .then(result => {
        if (result === "success") {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil daftar!',
                text: 'Kamu akan diarahkan ke beranda.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "index.html";
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal daftar',
                text: result
            });
        }
    });
});

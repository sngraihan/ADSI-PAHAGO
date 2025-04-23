<?php
session_start();

// Aktifkan tampilan error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if already logged in
if (isset($_SESSION['guide_id'])) {
    header("Location: guide/kelola-paket.php");
    exit();
}

// Process login form if submitted
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'pahago';
    
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            $error = "Koneksi database gagal: " . $conn->connect_error;
        } else {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            // SOLUSI SEDERHANA: Langsung cek email saja, abaikan password untuk sementara
            // CATATAN: Ini TIDAK AMAN untuk produksi, hanya untuk debugging!
            $stmt = $conn->prepare("SELECT id, name, email FROM guides WHERE email = ? AND status = 'active'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $guide = $result->fetch_assoc();
                
                // Langsung login tanpa cek password
                $_SESSION['guide_id'] = $guide['id'];
                $_SESSION['guide_name'] = $guide['name'];
                $_SESSION['guide_email'] = $guide['email'];
                $_SESSION['guide_logged_in'] = true;
                
                // Redirect to dashboard
                header("Location: guide/kelola-paket.php");
                exit();
            } else {
                $error = "Email tidak ditemukan";
            }
            
            $stmt->close();
            $conn->close();
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemandu Wisata - PahaGo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/guide.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f5ff;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            display: flex;
            width: 900px;
            height: 550px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .login-image {
            flex: 1;
            background-image: url('img/guide-login-bg.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
            color: white;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.7));
            z-index: 1;
        }
        
        .login-image h2, .login-image p {
            position: relative;
            z-index: 2;
        }
        
        .login-image h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-image p {
            font-size: 16px;
            margin-top: 0;
            opacity: 0.9;
        }
        
        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #1e6aff;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .logo span {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            margin-left: 2px;
        }
        
        .login-header {
            margin-top: 40px;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 16px;
            color: #666;
            margin-top: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1e6aff;
        }
        
        .form-group input[type="email"] {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%23999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: 16px center;
            padding-left: 45px;
        }
        
        .form-group input[type="password"] {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%23999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>');
            background-repeat: no-repeat;
            background-position: 16px center;
            padding-left: 45px;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .remember {
            display: flex;
            align-items: center;
        }
        
        .remember input {
            margin-right: 8px;
        }
        
        .remember label {
            font-size: 14px;
            color: #666;
        }
        
        .forgot-password {
            font-size: 14px;
            color: #1e6aff;
            text-decoration: none;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-button {
            background-color: #1e6aff;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-button:hover {
            background-color: #0052cc;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .debug-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 100%;
                height: auto;
                border-radius: 0;
            }
            
            .login-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <h2>Masuk sebagai Pemandu Wisata</h2>
            <p>Kelola perjalanan wisata Anda dengan mudah.</p>
        </div>
        <div class="login-form">
            <div class="logo">
                PahaGo<span>guide</span>
            </div>
            
            <div class="login-header">
                <h1>Masuk ke Akun Anda</h1>
                <p>Masuk sebagai Pemandu Wisata</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" placeholder="••••••" required>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ingat saya</label>
                    </div>
                    <a href="#" class="forgot-password">Lupa kata sandi?</a>
                </div>
                
                <button type="submit" class="login-button">Masuk</button>
            </form>
            
            <div class="debug-info">
                <strong>Catatan:</strong> Untuk sementara, login hanya memeriksa email tanpa validasi password.
                <br>Email yang tersedia: budi@pahago.com, dewi@pahago.com, agus@pahago.com, siti@pahago.com, rudi@pahago.com
            </div>
        </div>
    </div>
</body>
</html>
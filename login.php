<?php
require_once 'config.php';

// Check if user is already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi.";
    } else {
        // Check if email exists
        $sql = "SELECT id, name, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_start();
                
                // Store data in session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to home page
                header("Location: index.php");
                exit();
            } else {
                $error = "Password yang Anda masukkan salah.";
            }
        } else {
            $error = "Tidak ada akun yang terdaftar dengan email tersebut.";
        }
        
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<!-- Login Form -->
<section class="py-5">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-center fw-bold mb-2">Masuk ke PahaGo</h2>
                        <p class="text-center text-muted mb-4">Masukkan email dan password Anda untuk melanjutkan</p>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="loginForm" method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">Email</label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    placeholder="nama@email.com" 
                                    required
                                >
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password" class="form-label fw-medium">Password</label>
                                    <a href="forgot-password.php" class="text-decoration-none small text-primary">Lupa password?</a>
                                </div>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    required
                                >
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Masuk</button>
                            
                            <p class="text-center text-muted mb-0">
                                Belum punya akun? <a href="register.php" class="text-decoration-none text-primary">Daftar sekarang</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

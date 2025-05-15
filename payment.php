<?php
// Database connection configuration (to be filled with actual credentials)
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "pahago_db";

// Initialize session if needed
session_start();

// Establish database connection
$conn = new mysqli("localhost", "root", "", "pahago");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Assume we have the package_id from the URL or session
$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : (isset($_SESSION['package_id']) ? $_SESSION['package_id'] : 0);

// Fetch package details from database
$package = [];
$participants = 0;
$total_payment = 0;
$bank_account = "";
$account_name = "";
$transfer_amount = 0;

if ($package_id > 0) {
    $sql = "SELECT p.*, b.account_number, b.account_name, b.bank_name 
            FROM packages p 
            LEFT JOIN bank_accounts b ON b.id = p.payment_bank_id
            WHERE p.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $package_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $package = $row;
        $participants = isset($_SESSION['participants']) ? $_SESSION['participants'] : 2;
        $total_payment = $package['price'] * $participants;
        $bank_account = $package['account_number'];
        $account_name = $package['account_name'];
        $bank_name = $package['bank_name'];
        $transfer_amount = $total_payment - 50000; // Example: discount or fee adjustment
    }
}

// Handle payment confirmation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $upload_dir = "uploads/payment_proofs/";
    $payment_proof = null;
    
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        // Generate a unique filename
        $filename = uniqid() . "_" . basename($_FILES['payment_proof']['name']);
        $target_file = $upload_dir . $filename;
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Move uploaded file to target directory
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
            $payment_proof = $filename;
        }
    }
    
    // Save payment information to database
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    $sql = "INSERT INTO payments (package_id, user_id, amount, payment_proof, notes, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iidss", $package_id, $user_id, $total_payment, $payment_proof, $notes);
    
    if (mysqli_stmt_execute($stmt)) {
        // Payment record saved successfully
        // Redirect to confirmation page
        header("Location: payment_confirmation.php");
        exit;
    }
}

// Close database connection when done
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Paket Wisata - PahaGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: white;
        }
        .navbar-brand {
            font-weight: bold;
            color: #2563EB;
            font-size: 1.5rem;
        }
        .payment-container {
            max-width: 1000px;
            margin: 20px auto;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .section-container {
            padding: 20px 30px;
            border-bottom: 1px solid #f1f1f1;
        }
        .payment-header {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .info-label {
            color: #6B7280;
        }
        .info-value {
            font-weight: 500;
            text-align: right;
        }
        .alert-info {
            background-color: #EFF6FF;
            border-color: #DBEAFE;
            color: #1E40AF;
        }
        .bank-container {
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .bank-logo {
            width: 80px;
            margin-bottom: 10px;
        }
        .copy-button {
            background: none;
            border: none;
            color: #2563EB;
            cursor: pointer;
        }
        .timer-container {
            color: #F59E0B;
            font-weight: 600;
        }
        .upload-container {
            border: 1px dashed #CBD5E1;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            margin: 15px 0;
        }
        .upload-icon {
            color: #94A3B8;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .btn-primary {
            background-color: #2563EB;
            border-color: #2563EB;
            padding: 10px 20px;
            font-weight: 500;
        }
        .profile-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">PahaGo</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Paket Wisata</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Status Perjalanan</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <div class="profile-icon"></div>
                <span class="text-primary">●</span>
            </div>
        </div>
    </nav>

    <!-- Payment Form -->
    <div class="container payment-container">
        <form method="POST" enctype="multipart/form-data">
            <!-- Package Payment Details -->
            <div class="section-container">
                <h2 class="payment-header">Pembayaran Paket Wisata</h2>
                <div class="info-row">
                    <div class="info-label">Nama Paket</div>
                    <div class="info-value"><?php echo htmlspecialchars($package['name'] ?? 'Paket Petualangan Pulau Pahawang'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Keberangkatan</div>
                    <div class="info-value"><?php echo htmlspecialchars($package['departure_date'] ?? '15 Maret 2025'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jumlah Peserta</div>
                    <div class="info-value"><?php echo htmlspecialchars($participants . ' Orang'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Pembayaran</div>
                    <div class="info-value fw-bold">Rp <?php echo number_format($total_payment ?? 2550000, 0, ',', '.'); ?></div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="section-container">
                <h2 class="payment-header">Informasi Pembayaran</h2>
                <div class="alert alert-info" role="alert">
                    Silakan lakukan transfer ke rekening berikut dalam waktu yang ditentukan.
                </div>

                <div class="bank-container">
                    <div class="d-flex align-items-center mb-2">
                        <img src="assets/images/bca-logo.png" alt="Bank BCA" class="bank-logo">
                        <h5 class="mb-0 ms-2">Bank BCA</h5>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nomor Rekening</div>
                        <div class="info-value d-flex align-items-center">
                            <?php echo htmlspecialchars($bank_account ?? '1234 5678 9012'); ?>
                            <button class="copy-button ms-2" onclick="copyToClipboard('<?php echo htmlspecialchars($bank_account ?? '1234 5678 9012'); ?>')">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Atas Nama</div>
                        <div class="info-value"><?php echo htmlspecialchars($account_name ?? 'Pahago Travel'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Jumlah Transfer</div>
                        <div class="info-value text-primary fw-bold">Rp <?php echo number_format($transfer_amount ?? 2500000, 0, ',', '.'); ?></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <div>Selesaikan pembayaran dalam:</div>
                    <div class="timer-container" id="payment-timer">23:59:30</div>
                </div>
            </div>

            <!-- Payment Confirmation -->
            <div class="section-container">
                <h2 class="payment-header">Konfirmasi Pembayaran</h2>
                <div class="upload-container">
                    <div class="upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825v.825c0 .138.112.25.25.25h.825c.138 0 .25-.112.25-.25v-.825h.825c.138 0 .25-.112.25-.25v-.825c0-.138-.112-.25-.25-.25H7.646v-.825A.25.25 0 0 0 7.396 3H6.571a.25.25 0 0 0-.25.25v.825h-.825A.25.25 0 0 0 5.25 4.3v.825c0 .138.112.236.255.236z"/>
                        </svg>
                    </div>
                    <p>Upload bukti transfer disini</p>
                    <label for="payment-proof" class="btn btn-outline-primary">Pilih File</label>
                    <input type="file" id="payment-proof" name="payment_proof" class="d-none" accept="image/*">
                    <p id="selected-file" class="mt-2 small text-muted"></p>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Keterangan Tambahan (Opsional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Function to copy text to clipboard
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            alert('Nomor rekening berhasil disalin!');
        }

        // Function to display selected filename
        document.getElementById('payment-proof').addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Tidak ada file yang dipilih';
            document.getElementById('selected-file').textContent = fileName;
        });

        // Countdown timer functionality
        function startCountdown() {
            let hours = 23;
            let minutes = 59;
            let seconds = 30;

            const timerElement = document.getElementById('payment-timer');
            
            function updateTimer() {
                seconds--;
                
                if (seconds < 0) {
                    seconds = 59;
                    minutes--;
                }
                
                if (minutes < 0) {
                    minutes = 59;
                    hours--;
                }
                
                if (hours < 0) {
                    // Timer expired
                    timerElement.textContent = "Waktu habis";
                    clearInterval(interval);
                    return;
                }
                
                const formattedHours = hours.toString().padStart(2, '0');
                const formattedMinutes = minutes.toString().padStart(2, '0');
                const formattedSeconds = seconds.toString().padStart(2, '0');
                
                timerElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            }
            
            // Update the timer every second
            const interval = setInterval(updateTimer, 1000);
        }
        
        // Start the countdown when the page loads
        window.addEventListener('load', startCountdown);
    </script>
</body>
</html>
<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $pass     = trim($_POST['password'] ?? '');

    if ($username === '' || $pass === '') {
        $login_error = "⚠️ Username and password are required.";
    } else {
        $stmt = $conn->prepare("
            SELECT id, username, password, role, is_active, approval_status, orgid 
            FROM users 
            WHERE username = ?
        ");

        if (!$stmt) {
            die("❌ Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $db_username, $db_password, $role, $is_active, $approval_status, $orgid);
            $stmt->fetch();

            if (password_verify($pass, $db_password)) {
                $clean_role = strtolower(trim($role));
                $clean_status = strtolower(trim($approval_status));

                if ($clean_status !== "approved") {
                    $login_error = "⏳ Your registration is pending approval.";
                } elseif ((int)$is_active !== 1) {
                    $login_error = "🚫 Your account is disabled. Contact admin.";
                } else {
                    // ✅ SESSION SETUP
                    $_SESSION['user'] = [
                        'user_id'         => $id,
                        'username'        => $db_username,
                        'role'            => $clean_role,
                        'orgid'           => $orgid,
                        'approval_status' => $clean_status
                    ];

                    $_SESSION['user_id']         = $id;
                    $_SESSION['username']        = $db_username;
                    $_SESSION['role']            = $clean_role;
                    $_SESSION['orgid']           = $orgid;
                    $_SESSION['approval_status'] = $clean_status;

                    // ✅ REDIRECT BASED ON ROLE
                    switch ($clean_role) {
                        case 'superadmin':
                            header("Location: superadmin_dashboard.php");
                            break;
                        case 'admin':
                            header("Location: admin_dashboard.php");
                            break;
                        default:
                            header("Location: user_dashboard.php");
                            break;
                    }
                    exit();
                }
            } else {
                $login_error = "❌ Invalid password.";
            }
        } else {
            $login_error = "❌ Username not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>STPI Login</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .main-container {
      display: flex;
      max-width: 960px;
      width: 100%;
      background: rgba(255, 255, 255, 0.85);
      border-radius: 18px;
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    .left-panel {
      width: 45%;
      background: #dbeafe;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }
    .left-panel img {
      width: 100px;
      margin-bottom: 20px;
    }
    .left-panel h3 {
      font-size: 20px;
      color: #1e3a8a;
      margin-bottom: 10px;
    }
    .left-panel p {
      font-size: 14px;
      color: #1e3a8a;
      opacity: 0.8;
    }
    .right-panel {
      width: 55%;
      padding: 50px 40px;
    }
    h2 {
      text-align: center;
      color: #1e3a8a;
      margin-bottom: 25px;
    }
    .input-group {
      position: relative;
      margin-bottom: 28px;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 14px 12px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background: #fff;
      outline: none;
    }
    label {
      position: absolute;
      top: 50%;
      left: 14px;
      transform: translateY(-50%);
      background: white;
      padding: 0 4px;
      font-size: 14px;
      color: #666;
      pointer-events: none;
      transition: 0.2s ease;
    }
    input:focus + label,
    input:not(:placeholder-shown) + label {
      top: -9px;
      font-size: 12px;
      color: #2563eb;
    }
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
      color: #555;
      user-select: none;
    }
    button {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      background-color: #3b82f6;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background-color: #2563eb;
    }
    .register-link {
      text-align: center;
      margin-top: 18px;
      font-size: 14px;
    }
    .register-link a {
      color: #1d4ed8;
      text-decoration: none;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }
    @media (max-width: 768px) {
      .main-container {
        flex-direction: column;
        width: 90%;
      }
      .left-panel {
        display: none;
      }
      .right-panel {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<div class="main-container">
  <!-- Left Panel -->
  <div class="left-panel">
    <img src="/wo/images/stpi.png" alt="STPI Logo" width="120">
    <h3>Software Technology Parks of India</h3>
    <p>Empowering IT exports with innovation, infrastructure, and governance. Secure login for STPI operations.</p>
  </div>

  <!-- Right Panel -->
  <div class="right-panel">
    <h2>Login to Your Account</h2>

    <?php if (!empty($login_error)) echo "<div class='error'>$login_error</div>"; ?>

    <form method="POST">
      <div class="input-group">
        <input type="text" name="username" required placeholder=" " autocomplete="off" />
        <label>Username</label>
      </div>

      <div class="input-group">
        <input type="password" name="password" id="password" required placeholder=" " />
        <label>Password</label>
        <span class="toggle-password" onclick="togglePassword()">👁️</span>
      </div>

      <button type="submit">Login</button>
    </form>

    <div class="register-link">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>
</div>

<!-- ✅ Password Toggle Script -->
<script>
  function togglePassword() {
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.querySelector(".toggle-password");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      eyeIcon.textContent = "🙈";
    } else {
      passwordInput.type = "password";
      eyeIcon.textContent = "👁️";
    }
  }
</script>

</body>
</html>

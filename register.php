<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name      = $_POST['first_name'];
    $last_name       = $_POST['last_name'];
    $email           = $_POST['email'];
    $directorate     = $_POST['directorate'];
    $stpi_center     = $_POST['stpi_center'];
    $contact_number  = $_POST['contact_number'];
    $role            = $_POST['role'];
    $gender          = $_POST['gender'];
    $username        = $_POST['username'];
    $password        = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $agreed          = isset($_POST['agreed']) ? 1 : 0;
    $approval_status = 'Pending';
    $is_active       = 1;

    // Check for existing user
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email or Username already exists!');</script>";
    } else {
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['name']) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                $target_dir  = "uploads/";
                $image_name  = time() . '_' . basename($_FILES["image"]["name"]);
                $image_path  = $target_dir . $image_name;
                move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
            } else {
                echo "<script>alert('Invalid image type. Only JPG, PNG allowed.');</script>";
                exit;
            }
        }

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, directorate, stpi_center, contact_number, role, gender, username, password, image_path, agreed, approval_status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssisi", $first_name, $last_name, $email, $directorate, $stpi_center, $contact_number, $role, $gender, $username, $password, $image_path, $agreed, $approval_status, $is_active);
        $stmt->execute();

        echo "<script>alert('Registered successfully! Please wait for approval.'); window.location='login.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modern Registration</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #dbeafe, #f0f4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        form {
            width: 700px;
            max-width: 90%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 14px 15px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #6c63ff;
            outline: none;
            background-color: #f0f8ff;
        }

        input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }

        label {
            font-size: 15px;
            color: #444;
        }

        input[type="submit"] {
            background: #6c63ff;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background: #5548e2;
        }

        .file-upload {
            margin-top: 10px;
        }

        .checkbox-container {
            margin-top: 10px;
        }

    </style>
</head>
<body>

<form method="post" enctype="multipart/form-data">
    <h2>User Registration</h2>
    <input name="first_name" required placeholder="First Name">
    <input name="last_name" required placeholder="Last Name">
    <input name="email" required type="email" placeholder="Email">
   
    <select name="directorate" id="directorate" required>
 <option>Delhi</option>
  <option>Uttar Pradesh</option>
  <option>Uttarakhand</option>
  <option>Madhya Pradesh</option>
  <option>Chhattisgarh</option>
  <option>Karnataka</option>
  <option>Telangana</option>
  <option>Andhra Pradesh</option>
  <option>Maharashtra</option>
  <option>Goa</option>
  <option>Assam</option>
  <option>Meghalaya</option>
  <option>Nagaland</option>
  <option>Manipur</option>
  <option>Tripura</option>
  <option>Arunachal Pradesh</option>
  <option>Mizoram</option>
  <option>Sikkim</option>
  <option>Tamilnadu</option>
  <option>Pondicherry</option>
  <option>Andaman & Nicobar</option>
  <option>Gujarat</option>
  <option>Daman</option>
  <option>Diu</option>
  <option>Dadra & Nagar Haveli</option>
  <option>Kerala</option>
  <option>Lakshadweep</option>
  <option>Orissa</option>
  <option>Bihar</option>
  <option>Jharkhand</option>
  <option>West Bengal</option>
  <option>Rajasthan</option>
  <option>Haryana</option>
</select>

   
    <select name="stpi_center" required>
  <option value="" disabled selected>Select STPI Center</option>
  <option value="Agartala">Agartala</option>
  <option value="Agra">Agra</option>
  <option value="Ahmedabad">Ahmedabad</option>
  <option value="Ajmer">Ajmer</option>
  <option value="Allahabad">Allahabad</option>
  <option value="Amritsar">Amritsar</option>
  <option value="Aurangabad">Aurangabad</option>
  <option value="Bangalore">Bangalore</option>
  <option value="Bhopal">Bhopal</option>
  <option value="Bhubaneswar">Bhubaneswar</option>
  <option value="Chandigarh">Chandigarh</option>
  <option value="Chennai">Chennai</option>
  <option value="Coimbatore">Coimbatore</option>
  <option value="Dehradun">Dehradun</option>
  <option value="Delhi">Delhi</option>
  <option value="Durgapur">Durgapur</option>
  <option value="Ernakulam">Ernakulam</option>
  <option value="Gangtok">Gangtok</option>
  <option value="Gandhinagar">Gandhinagar</option>
  <option value="Goa">Goa</option>
  <option value="Guwahati">Guwahati</option>
  <option value="Gwalior">Gwalior</option>
  <option value="Hubli">Hubli</option>
  <option value="Hyderabad">Hyderabad</option>
  <option value="Imphal">Imphal</option>
  <option value="Indore">Indore</option>
  <option value="Itanagar">Itanagar</option>
  <option value="Jaipur">Jaipur</option>
  <option value="Jalandhar">Jalandhar</option>
  <option value="Jammu">Jammu</option>
  <option value="Jamshedpur">Jamshedpur</option>
  <option value="Jodhpur">Jodhpur</option>
  <option value="Kanpur">Kanpur</option>
  <option value="Kolkata">Kolkata</option>
  <option value="Kozhikode">Kozhikode</option>
  <option value="Lucknow">Lucknow</option>
  <option value="Ludhiana">Ludhiana</option>
  <option value="Madurai">Madurai</option>
  <option value="Meerut">Meerut</option>
  <option value="Mohali">Mohali</option>
  <option value="Mumbai">Mumbai</option>
  <option value="Mysore">Mysore</option>
  <option value="Nagpur">Nagpur</option>
  <option value="Nasik">Nasik</option>
  <option value="Noida">Noida</option>
  <option value="Patna">Patna</option>
  <option value="Pune">Pune</option>
  <option value="Raipur">Raipur</option>
  <option value="Rajkot">Rajkot</option>
  <option value="Ranchi">Ranchi</option>
  <option value="Rourkela">Rourkela</option>
  <option value="Shimla">Shimla</option>
  <option value="Silchar">Silchar</option>
  <option value="Silvassa">Silvassa</option>
  <option value="Solapur">Solapur</option>
  <option value="Srinagar">Srinagar</option>
  <option value="Surat">Surat</option>
  <option value="Thiruvananthapuram">Thiruvananthapuram</option>
  <option value="Tirupati">Tirupati</option>
  <option value="Trichy">Trichy</option>
  <option value="Udaipur">Udaipur</option>
  <option value="Vadodara">Vadodara</option>
  <option value="Varanasi">Varanasi</option>
  <option value="Vijayawada">Vijayawada</option>
  <option value="Visakhapatnam">Visakhapatnam</option>
  <option value="Warangal">Warangal</option>
</select>

    <input name="contact_number" placeholder="Contact Number">

    <select name="role" required>
        <option value="" disabled selected>Select Role</option>
        <option value="User">User</option>
        <option value="Admin">Admin</option>
        <option value="superAdmin">superAdmin</option>
    </select>

    <select name="gender" required>
        <option value="" disabled selected>Select Gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select>

    <input name="username" required placeholder="Username">
    <input type="password" name="password" required placeholder="Password">

    <div class="file-upload">
        <label>Upload Image: </label>
        <input type="file" name="image">
    </div>

    <div class="checkbox-container">
        <label><input type="checkbox" name="agreed"> I Agree to Terms</label>
    </div>

    <input type="submit" value="Register">
</form>

</body>
</html>

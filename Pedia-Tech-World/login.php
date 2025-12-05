<?php
session_start();
include 'includes/config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email' OR employee_id = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify Password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: employee/dashboard.php");
            }
            exit;
        } else {
            $error = "Incorrect Password";
        }
    } else {
        $error = "User not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Pedia Tech World</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl w-96">
        <h2 class="text-2xl font-bold text-center mb-2 text-slate-800">Pedia Tech</h2>
        <p class="text-center text-gray-500 text-sm mb-6">Employee & Admin Portal</p>
        
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label class="block text-sm font-bold mb-1">Email or Employee ID</label>
            <input type="text" name="email" required class="w-full p-2 mb-4 border rounded focus:outline-none focus:border-orange-500">
            
            <label class="block text-sm font-bold mb-1">Password</label>
            <input type="password" name="password" required class="w-full p-2 mb-6 border rounded focus:outline-none focus:border-orange-500">
            
            <button class="w-full bg-slate-900 text-white py-2 rounded font-bold hover:bg-slate-800 transition">Login</button>
        </form>
        <a href="index.php" class="block text-center mt-4 text-sm text-orange-600 hover:underline">Back to Website</a>
    </div>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";

// --- 1. HANDLE PROFILE UPDATE (Username/Email & Name) ---
if (isset($_POST['update_profile'])) {
    $new_name = $conn->real_escape_string($_POST['name']);
    $new_email = $conn->real_escape_string($_POST['email']); // This acts as the Username
    $admin_id = $_SESSION['user_id'];

    // Check if email is already taken by another user
    $check_sql = "SELECT id FROM users WHERE email = '$new_email' AND id != $admin_id";
    $check_res = $conn->query($check_sql);

    if ($check_res->num_rows > 0) {
        $error = "This Username/Email is already taken.";
    } else {
        $update_sql = "UPDATE users SET name = '$new_name', email = '$new_email' WHERE id = $admin_id";
        if ($conn->query($update_sql)) {
            $msg = "Profile updated successfully.";
            // Update Session Data
            $_SESSION['name'] = $new_name;
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

// --- 2. HANDLE PASSWORD CHANGE ---
if (isset($_POST['change_pass'])) {
    $current = $_POST['current_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];
    $admin_id = $_SESSION['user_id'];

    if ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $sql = "SELECT password FROM users WHERE id = $admin_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if (password_verify($current, $row['password'])) {
            $new_hashed = password_hash($new, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = '$new_hashed' WHERE id = $admin_id";
            if ($conn->query($update_sql)) {
                $msg = "Password updated successfully.";
            } else {
                $error = "Database error.";
            }
        } else {
            $error = "Incorrect current password.";
        }
    }
}

// Fetch current admin details
$admin_id = $_SESSION['user_id'];
$admin_data = $conn->query("SELECT * FROM users WHERE id=$admin_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Settings</title>
    <!-- FAVICON -->
    <link rel="icon" href="../uploads/logo.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex h-screen">
    
        <!-- Sidebar -->
    <div class="w-64 bg-slate-900 text-white flex flex-col shrink-0">
        <div class="p-6 font-bold text-xl tracking-wider text-center border-b border-slate-800">ADMIN PANEL</div>
        
        <!-- Menu Links -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Overview</a>
            <a href="applications.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Applications</a>
            <a href="attendance.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Attendance</a>
            <a href="banners.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Banners</a>
            <a href="employees.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="users" class="w-4 h-4 mr-2"></i> Employees</a>
            <a href="offers.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="percent" class="w-4 h-4 mr-2"></i> Offers & Ads</a>
            <a href="projects.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="briefcase" class="w-4 h-4 mr-2"></i> Projects</a>
            <a href="promocodes.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="ticket-percent" class="w-4 h-4 mr-2"></i> Promo Codes</a>
            <a href="service_courses.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="tag" class="w-4 h-4 mr-2"></i> Service & Courses</a>
            <a href="portfolio.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Website Portfolio</a>
            <a href="settings.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="settings" class="w-4 h-4 mr-2"></i> Settings</a>
            <a href="../login.php" class="flex items-center px-4 py-2 mt-4 text-red-400 hover:bg-slate-800 rounded"><i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Logout</a>
        </nav>

       <!-- ADMIN PROFILE SECTION (Bottom of Sidebar) -->
        <div class="p-4 border-t border-slate-800 flex items-center gap-3 bg-slate-900/50">
            <div class="overflow-hidden">
                <p class="text-xs text-slate-400">Administrator</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-8">Account Settings</h2>

        <div class="max-w-xl space-y-8">
            
            <?php if($msg): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded text-sm border border-green-200"><?php echo $msg; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded text-sm border border-red-200"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- 1. CHANGE USERNAME / PROFILE -->
            <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-red-500">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                    <i data-lucide="user" class="w-5 h-5 mr-2 text-red-600"></i> Profile Settings
                </h3>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Display Name</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($admin_data['name']); ?>" class="w-full p-2 border border-slate-300 rounded focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Username / Email (Login ID)</label>
                        <input type="text" name="email" required value="<?php echo htmlspecialchars($admin_data['email']); ?>" class="w-full p-2 border border-slate-300 rounded focus:outline-none focus:border-red-500">
                    </div>
                    <button name="update_profile" class="w-full bg-red-600 text-white py-2 rounded font-bold hover:bg-red-700 transition shadow-lg mt-2">
                        Save Profile Changes
                    </button>
                </form>
            </div>

            <!-- 2. CHANGE PASSWORD -->
            <div class="bg-white p-8 rounded-xl shadow-lg border-t-4 border-orange-500">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                    <i data-lucide="lock" class="w-5 h-5 mr-2 text-orange-600"></i> Change Password
                </h3>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_pass" required class="w-full p-2 border border-slate-300 rounded focus:outline-none focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_pass" required class="w-full p-2 border border-slate-300 rounded focus:outline-none focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_pass" required class="w-full p-2 border border-slate-300 rounded focus:outline-none focus:border-orange-500">
                    </div>
                    <button name="change_pass" class="w-full bg-orange-600 text-white py-2 rounded font-bold hover:bg-orange-800 transition shadow-lg mt-2">
                        Update Password
                    </button>
                </form>
            </div>
            
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
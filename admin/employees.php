<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";
$edit_mode = false;
$edit_data = ['name' => '', 'email' => '', 'employee_id' => '', 'profile_image' => ''];

// --- 1. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    
    // Fetch image to delete file
    $res = $conn->query("SELECT profile_image FROM users WHERE id=$del_id AND role='employee'");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['profile_image']) && file_exists("../" . $row['profile_image'])) {
            unlink("../" . $row['profile_image']);
        }
        
        // Protect against accidental admin deletion, enforce role='employee'
        $sql = "DELETE FROM users WHERE id=$del_id AND role='employee'";
        if ($conn->query($sql)) {
            $msg = "Employee deleted successfully.";
        } else {
            $error = "Error deleting employee: " . $conn->error;
        }
    }
}

// --- 2. HANDLE CREATE ---
if (isset($_POST['create_emp'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $empid = $conn->real_escape_string($_POST['empid']);
    $raw_pass = $_POST['password'];
    $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);

    // Profile Image Logic
    $profile_img_path = "";
    $target_dir = "../uploads/profiles/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $img_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $img_name;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_img_path = "uploads/profiles/" . $img_name;
        }
    }

    // Check duplicates
    $check = $conn->query("SELECT id FROM users WHERE email='$email' OR employee_id='$empid'");
    if ($check->num_rows > 0) {
        $error = "Error: Email or Employee ID already exists.";
    } else {
        // This query requires the 'profile_image' column to exist in the 'users' table.
        // Run: ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL;
        $sql = "INSERT INTO users (name, email, password, employee_id, role, profile_image) VALUES ('$name', '$email', '$hashed_pass', '$empid', 'employee', '$profile_img_path')";
        if ($conn->query($sql)) {
            $msg = "Employee created successfully!";
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}

// --- 3. HANDLE UPDATE ---
if (isset($_POST['update_emp'])) {
    $id = intval($_POST['user_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $empid = $conn->real_escape_string($_POST['empid']);

    // Check duplicates (excluding current user)
    $check = $conn->query("SELECT id FROM users WHERE (email='$email' OR employee_id='$empid') AND id != $id");
    if ($check->num_rows > 0) {
        $error = "Error: Email or Employee ID is taken by another user.";
    } else {
        // Profile Image Update Logic
        $update_img_sql = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "../uploads/profiles/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            $img_name = time() . "_" . basename($_FILES['profile_image']['name']);
            $target_file = $target_dir . $img_name;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                // Delete old image
                $old_res = $conn->query("SELECT profile_image FROM users WHERE id=$id");
                if ($old_row = $old_res->fetch_assoc()) {
                    if (!empty($old_row['profile_image']) && file_exists("../" . $old_row['profile_image'])) {
                        unlink("../" . $old_row['profile_image']);
                    }
                }
                $new_path = "uploads/profiles/" . $img_name;
                $update_img_sql = ", profile_image='$new_path'";
            }
        }

        $sql = "UPDATE users SET name='$name', email='$email', employee_id='$empid' $update_img_sql WHERE id=$id";
        if ($conn->query($sql)) {
            $msg = "Employee details updated successfully!";
            // Redirect to clear URL parameters
            echo "<script>window.location.href='employees.php';</script>"; 
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}

// --- 4. HANDLE PASSWORD RESET (Existing) ---
if (isset($_POST['reset_pass'])) {
    $target_id = intval($_POST['user_id']);
    $new_pass = $_POST['new_password'];
    $hashed_reset = password_hash($new_pass, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = '$hashed_reset' WHERE id = $target_id";
    if ($conn->query($sql)) {
        $msg = "Password reset successfully.";
    } else {
        $error = "Error resetting password.";
    }
}

// --- 5. CHECK EDIT MODE ---
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM users WHERE id=$edit_id AND role='employee'");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Employees</title>
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
            <a href="employees.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="users" class="w-4 h-4 mr-2"></i> Employees</a>
            <a href="offers.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="percent" class="w-4 h-4 mr-2"></i> Offers & Ads</a>
            <a href="projects.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="briefcase" class="w-4 h-4 mr-2"></i> Projects</a>
            <a href="promocodes.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="ticket-percent" class="w-4 h-4 mr-2"></i> Promo Codes</a>
            <a href="service_courses.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="tag" class="w-4 h-4 mr-2"></i> Service & Courses</a>
            <a href="portfolio.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Website Portfolio</a>
            <a href="settings.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="settings" class="w-4 h-4 mr-2"></i> Settings</a>
            <a href="../login.php" class="flex items-center px-4 py-2 mt-4 text-red-400 hover:bg-slate-800 rounded"><i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Logout</a>
        </nav>

        <!-- ADMIN PROFILE SECTION (Bottom of Sidebar) -->
        <div class="p-4 border-t border-slate-800 flex items-center gap-3 bg-slate-900/50">
            <div class="overflow-hidden">
                <p class="text-xs text-slate-400">Administrator</p>
            </div>
        </div>
    </div>

    <div class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Employee Management</h2>

        <!-- Messages -->
        <?php if($msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6 border border-green-200"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-6 border border-red-200"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
            
            <!-- Create / Edit Form -->
            <div class="bg-white p-6 rounded-xl shadow border-t-4 <?php echo $edit_mode ? 'border-yellow-500' : 'border-orange-500'; ?>">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg flex items-center text-slate-700">
                        <i data-lucide="<?php echo $edit_mode ? 'edit' : 'user-plus'; ?>" class="w-5 h-5 mr-2 <?php echo $edit_mode ? 'text-yellow-600' : 'text-orange-600'; ?>"></i> 
                        <?php echo $edit_mode ? 'Edit Employee Details' : 'Create New Employee'; ?>
                    </h3>
                    <?php if($edit_mode): ?>
                        <a href="employees.php" class="text-sm text-red-500 hover:underline">Cancel Edit</a>
                    <?php endif; ?>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-3">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="name" placeholder="Full Name" required value="<?php echo htmlspecialchars($edit_data['name']); ?>" class="w-full p-2 border rounded">
                        <input type="text" name="empid" placeholder="Employee ID (e.g. EMP001)" required value="<?php echo htmlspecialchars($edit_data['employee_id']); ?>" class="w-full p-2 border rounded">
                    </div>
                    
                    <input type="email" name="email" placeholder="Email Address" required value="<?php echo htmlspecialchars($edit_data['email']); ?>" class="w-full p-2 border rounded">
                    
                    <!-- Profile Image Input -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Profile Image (Optional)</label>
                        <div class="flex items-center gap-4">
                            <?php if($edit_mode && !empty($edit_data['profile_image'])): ?>
                                <img src="../<?php echo $edit_data['profile_image']; ?>" class="w-10 h-10 rounded-full object-cover border">
                            <?php endif; ?>
                            <input type="file" name="profile_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                        </div>
                    </div>

                    <?php if(!$edit_mode): ?>
                        <input type="text" name="password" placeholder="Initial Password" required class="w-full p-2 border rounded">
                    <?php else: ?>
                        <p class="text-xs text-gray-400">Note: To change password, use the Reset Password panel.</p>
                    <?php endif; ?>

                    <button name="<?php echo $edit_mode ? 'update_emp' : 'create_emp'; ?>" class="w-full text-white py-2 rounded font-bold transition <?php echo $edit_mode ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-orange-600 hover:bg-orange-700'; ?>">
                        <?php echo $edit_mode ? 'Update Employee' : 'Create Account'; ?>
                    </button>
                </form>
            </div>

            <!-- Reset Password Form -->
            <div class="bg-white p-6 rounded-xl shadow border-t-4 border-red-500">
                <h3 class="font-bold text-lg mb-4 flex items-center text-slate-700">
                    <i data-lucide="key" class="w-5 h-5 mr-2 text-red-600"></i> Reset Employee Password
                </h3>
                <form method="POST" class="space-y-3">
                    <label class="block text-sm font-bold text-gray-500">Select Employee</label>
                    <select name="user_id" required class="w-full p-2 border rounded bg-white">
                        <option value="">-- Select Employee --</option>
                        <?php 
                        $users = $conn->query("SELECT id, name, employee_id FROM users WHERE role='employee'");
                        while($u = $users->fetch_assoc()):
                        ?>
                        <option value="<?php echo $u['id']; ?>"><?php echo $u['name']; ?> (<?php echo $u['employee_id']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    
                    <label class="block text-sm font-bold text-gray-500">New Password</label>
                    <input type="text" name="new_password" placeholder="Enter new password" required class="w-full p-2 border rounded">
                    
                    <button name="reset_pass" class="w-full bg-red-500 text-white py-2 rounded font-bold hover:bg-red-600 mt-4">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Employee List -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-4 bg-gray-50 border-b font-bold text-gray-700">Registered Employees</div>
            <table class="w-full text-left">
                <thead class="bg-white border-b">
                    <tr>
                        <th class="p-4 text-sm text-gray-500">Profile</th>
                        <th class="p-4 text-sm text-gray-500">ID</th>
                        <th class="p-4 text-sm text-gray-500">Name</th>
                        <th class="p-4 text-sm text-gray-500">Email</th>
                        <th class="p-4 text-sm text-gray-500">Joined</th>
                        <th class="p-4 text-sm text-gray-500 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    $res = $conn->query("SELECT * FROM users WHERE role='employee' ORDER BY created_at DESC");
                    while($row = $res->fetch_assoc()):
                        $p_img = !empty($row['profile_image']) ? "../".$row['profile_image'] : "https://ui-avatars.com/api/?name=".urlencode($row['name'])."&background=0D8ABC&color=fff";
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-4">
                            <img src="<?php echo $p_img; ?>" class="w-10 h-10 rounded-full object-cover border">
                        </td>
                        <td class="p-4 font-mono text-sm text-orange-700 font-bold"><?php echo htmlspecialchars($row['employee_id']); ?></td>
                        <td class="p-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="p-4 text-gray-500"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="p-4 text-xs text-gray-400"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td class="p-4 flex justify-center space-x-4">
                            <!-- Edit Button -->
                            <a href="?edit=<?php echo $row['id']; ?>" class="text-yellow-500 hover:text-yellow-700 font-bold text-sm flex items-center">
                                <i data-lucide="pencil" class="w-4 h-4 mr-1"></i> Edit
                            </a>
                            <!-- Delete Button -->
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to permanently delete this employee? This will also remove their attendance and task records.');" class="text-red-500 hover:text-red-700 font-bold text-sm flex items-center">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
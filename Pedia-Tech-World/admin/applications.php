<?php
session_start();
// Ensure user is logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";
$edit_mode = false;
$edit_data = [];

// --- 1. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    
    // Fetch file path to delete the attached document first
    $file_check = $conn->query("SELECT file_path FROM applications WHERE id=$del_id");
    if ($file_check && $row = $file_check->fetch_assoc()) {
        if (!empty($row['file_path'])) {
            $abs_path = "../" . $row['file_path'];
            if (file_exists($abs_path)) {
                unlink($abs_path); // Delete file from server
            }
        }
    }

    if ($conn->query("DELETE FROM applications WHERE id=$del_id")) {
        $msg = "Application deleted successfully.";
    } else {
        $error = "Error deleting application: " . $conn->error;
    }
}

// --- 2. HANDLE UPDATE ---
if (isset($_POST['update_app'])) {
    $id = intval($_POST['app_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE applications SET name='$name', email='$email', phone='$phone', status='$status' WHERE id=$id";
    
    if ($conn->query($sql)) {
        $msg = "Application updated successfully.";
        echo "<script>window.location.href='applications.php';</script>";
    } else {
        $error = "Error updating application: " . $conn->error;
    }
}

// --- 3. CHECK EDIT MODE ---
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM applications WHERE id=$edit_id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// Fetch all applications sorted by newest first
$app_res = $conn->query("SELECT * FROM applications ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Applications</title>
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
            <a href="applications.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Applications</a>
            <a href="attendance.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Attendance</a>
            <a href="banners.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Banners</a>
            <a href="employees.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="users" class="w-4 h-4 mr-2"></i> Employees</a>
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

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Recent Applications</h2>
        
        <!-- Feedback Messages -->
        <?php if($msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6 border border-green-200"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-6 border border-red-200"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Edit Form (Visible only when editing) -->
        <?php if($edit_mode): ?>
        <div class="bg-white p-6 rounded-xl shadow mb-8 border-t-4 border-yellow-500">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-slate-700 flex items-center">
                    <i data-lucide="pencil" class="w-5 h-5 mr-2 text-yellow-600"></i> Edit Application
                </h3>
                <a href="applications.php" class="text-sm text-red-500 hover:underline">Cancel Edit</a>
            </div>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="app_id" value="<?php echo $edit_data['id']; ?>">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['name']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_data['email']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_data['phone']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full p-2 border rounded bg-white">
                        <option value="New" <?php if($edit_data['status']=='New') echo 'selected'; ?>>New</option>
                        <option value="Contacted" <?php if($edit_data['status']=='Contacted') echo 'selected'; ?>>Contacted</option>
                        <option value="Interviewed" <?php if($edit_data['status']=='Interviewed') echo 'selected'; ?>>Interviewed</option>
                        <option value="Accepted" <?php if($edit_data['status']=='Accepted') echo 'selected'; ?>>Accepted</option>
                        <option value="Rejected" <?php if($edit_data['status']=='Rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <button name="update_app" class="bg-yellow-500 text-white px-6 py-2 rounded font-bold hover:bg-yellow-600 transition">Update Details</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Applications List -->
        <div class="grid gap-6">
            <?php 
            if($app_res->num_rows == 0) echo "<p class='text-gray-500 italic bg-white p-6 rounded-xl shadow'>No applications received yet.</p>";
            
            while($app = $app_res->fetch_assoc()): 
                // Determine styling based on type
                $borderColor = ($app['type'] == 'job') ? 'border-purple-500' : 'border-blue-500';
                $badgeColor = ($app['type'] == 'job') ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
                $downloadLabel = ($app['type'] == 'job') ? 'Download Resume' : 'Download ID Proof';
                
                // Status Color
                $statusClass = 'bg-gray-100 text-gray-600';
                if($app['status'] == 'New') $statusClass = 'bg-green-100 text-green-700';
                if($app['status'] == 'Contacted') $statusClass = 'bg-yellow-100 text-yellow-700';
                if($app['status'] == 'Rejected') $statusClass = 'bg-red-100 text-red-700';
            ?>
            <div class="bg-white rounded-xl shadow p-6 border-l-4 <?php echo $borderColor; ?>">
                <!-- Header: Name, Status, Date -->
                <div class="flex flex-col md:flex-row justify-between items-start mb-4">
                    <div class="mb-2 md:mb-0 w-full">
                        <h3 class="font-bold text-xl text-slate-800 flex items-center">
                            <?php echo htmlspecialchars($app['name']); ?>
                            <span class="ml-3 px-2 py-0.5 rounded text-xs font-bold uppercase <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($app['status']); ?>
                            </span>
                        </h3>
                        <div class="flex flex-wrap items-center text-sm text-gray-500 mt-1 gap-3">
                            <span class="flex items-center"><i data-lucide="mail" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($app['email']); ?></span>
                            <span class="flex items-center"><i data-lucide="phone" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($app['phone']); ?></span>
                            <!-- DATE & TIME -->
                            <span class="flex items-center text-slate-400">
                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i> 
                                <?php echo date('d M Y, h:i A', strtotime($app['created_at'])); ?>
                            </span>
                        </div>

                        <!-- PROMO CODE HIGHLIGHT (NEW) -->
                        <?php if(!empty($app['promo_code'])): ?>
                            <div class="mt-2 inline-flex items-center bg-green-50 border border-green-200 text-green-700 px-2 py-1 rounded text-xs font-bold uppercase tracking-wide shadow-sm">
                                <i data-lucide="tag" class="w-3 h-3 mr-1 fill-current"></i>
                                Promo Code: <?php echo htmlspecialchars($app['promo_code']); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $badgeColor; ?> shrink-0 mt-2 md:mt-0">
                        <?php echo htmlspecialchars($app['type']); ?>
                    </span>
                </div>

                <!-- Details Body -->
                <div class="bg-slate-50 p-4 rounded text-sm text-slate-700 mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="font-bold text-gray-500 text-xs mb-1 uppercase">Full Address</p>
                        <p><?php echo nl2br(htmlspecialchars($app['address'])); ?></p>
                    </div>
                    
                    <?php if($app['type'] == 'job'): ?>
                    <div>
                        <p class="font-bold text-gray-500 text-xs mb-1 uppercase">Education</p>
                        <p><?php echo nl2br(htmlspecialchars($app['education'])); ?></p>
                    </div>
                    <?php else: ?>
                    <!-- For Project/Course/Client -->
                    <div>
                        <p class="font-bold text-gray-500 text-xs mb-1 uppercase">Project/Course Details</p>
                        <p><?php echo nl2br(htmlspecialchars($app['education'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer: Download & Actions -->
                <div class="flex justify-between items-center border-t pt-4">
                    <?php if(!empty($app['file_path'])): ?>
                        <a href="../<?php echo $app['file_path']; ?>" download class="inline-flex items-center text-orange-600 hover:text-orange-800 font-bold text-sm border border-orange-200 bg-orange-50 px-3 py-1.5 rounded transition hover:bg-orange-100">
                            <i data-lucide="download" class="w-4 h-4 mr-2"></i> 
                            <?php echo $downloadLabel; ?>
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center text-gray-400 text-sm italic">
                            <i data-lucide="file-x" class="w-4 h-4 mr-2"></i> No file
                        </span>
                    <?php endif; ?>

                    <div class="flex space-x-3">
                        <a href="?edit=<?php echo $app['id']; ?>" class="flex items-center text-sm font-bold text-yellow-600 hover:text-yellow-800">
                            <i data-lucide="pencil" class="w-4 h-4 mr-1"></i> Edit
                        </a>
                        <a href="?delete=<?php echo $app['id']; ?>" onclick="return confirm('Delete this application permanently?');" class="flex items-center text-sm font-bold text-red-600 hover:text-red-800">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";

// --- HANDLE UPLOAD ---
if (isset($_POST['upload_banner'])) {
    $target_dir = "../uploads/banners/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    if (isset($_FILES['banners']) && count($_FILES['banners']['name']) > 0) {
        $total = count($_FILES['banners']['name']);
        $success_count = 0;

        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['banners']['error'][$i] == 0) {
                $name = time() . "_bnr_" . $i . "_" . basename($_FILES['banners']['name'][$i]);
                $target_file = $target_dir . $name;
                
                if (move_uploaded_file($_FILES['banners']['tmp_name'][$i], $target_file)) {
                    $db_path = "uploads/banners/" . $name;
                    $conn->query("INSERT INTO banners (image_path) VALUES ('$db_path')");
                    $success_count++;
                }
            }
        }
        if ($success_count > 0) {
            $msg = "$success_count banner(s) uploaded successfully!";
        } else {
            $error = "Failed to upload images.";
        }
    }
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $conn->query("SELECT image_path FROM banners WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if (file_exists("../" . $row['image_path'])) { unlink("../" . $row['image_path']); }
        $conn->query("DELETE FROM banners WHERE id=$id");
        header("Location: banners.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Banners</title>
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
            <a href="banners.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Banners</a>
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

    <div class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Homepage Banners</h2>

        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>$error</div>"; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Upload Form -->
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow h-fit border-t-4 border-orange-500">
                <h3 class="font-bold text-lg mb-4 flex items-center"><i data-lucide="upload-cloud" class="w-5 h-5 mr-2"></i> Upload Banners</h3>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500">Select Images (Multiple allowed)</label>
                        <input type="file" name="banners[]" multiple required accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"/>
                        <p class="text-xs text-gray-400 mt-1">Recommended size: 1920x600px</p>
                    </div>
                    <button name="upload_banner" class="w-full bg-orange-600 text-white py-2 rounded font-bold hover:bg-orange-700">Upload</button>
                </form>
            </div>

            <!-- Gallery -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
                <h3 class="font-bold text-lg mb-4">Active Banners</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php 
                    $res = $conn->query("SELECT * FROM banners ORDER BY created_at DESC");
                    if ($res->num_rows == 0) echo "<p class='text-gray-400'>No banners uploaded.</p>";
                    while($row = $res->fetch_assoc()):
                    ?>
                    <div class="relative group rounded-lg overflow-hidden border border-slate-200">
                        <img src="../<?php echo $row['image_path']; ?>" class="w-full h-40 object-cover">
                        <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center transition">
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this banner?')" class="bg-red-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
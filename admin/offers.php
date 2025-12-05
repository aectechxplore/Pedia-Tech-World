<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";
$edit_mode = false;
$edit_data = [];

// --- 1. HANDLE FLASH SALE SETTINGS UPDATE ---
if (isset($_POST['update_flash_sale'])) {
    $fs_desc = $conn->real_escape_string($_POST['fs_description']);
    // If date is provided, format it; otherwise set to NULL
    $fs_expiry = !empty($_POST['fs_expiry']) ? "'" . $conn->real_escape_string($_POST['fs_expiry']) . "'" : "NULL";
    
    // Ensure row 1 exists (INSERT if not, otherwise UPDATE)
    $check = $conn->query("SELECT id FROM flash_sale WHERE id=1");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO flash_sale (id, description, expiry_date) VALUES (1, '$fs_desc', $fs_expiry)");
    } else {
        $conn->query("UPDATE flash_sale SET description='$fs_desc', expiry_date=$fs_expiry WHERE id=1");
    }
    $msg = "Flash Sale settings updated successfully!";
}

// --- 2. HANDLE OFFER IMAGE UPLOAD/UPDATE ---
if (isset($_POST['save_offer'])) {
    $desc = $conn->real_escape_string($_POST['description']);
    $target_dir = "../uploads/offers/";
    
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    // Check if Updating or Creating
    if (!empty($_POST['offer_id'])) {
        // UPDATE MODE
        $id = intval($_POST['offer_id']);
        $sql_part = "description='$desc'";
        
        // If new image uploaded
        if (isset($_FILES['offer_image']) && $_FILES['offer_image']['error'] == 0) {
            // Delete old image
            $old_q = $conn->query("SELECT image_path FROM offers WHERE id=$id");
            $old_row = $old_q->fetch_assoc();
            if (!empty($old_row['image_path']) && file_exists("../" . $old_row['image_path'])) {
                unlink("../" . $old_row['image_path']);
            }
            
            // Upload new
            $name = time() . "_offer_" . basename($_FILES['offer_image']['name']);
            if (move_uploaded_file($_FILES['offer_image']['tmp_name'], $target_dir . $name)) {
                $db_path = "uploads/offers/" . $name;
                $sql_part .= ", image_path='$db_path'";
            }
        }
        
        if ($conn->query("UPDATE offers SET $sql_part WHERE id=$id")) {
            $msg = "Offer image updated successfully!";
            // Clear edit mode
            echo "<script>window.location.href='offers.php';</script>";
        } else {
            $error = "Database Error: " . $conn->error;
        }
        
    } else {
        // CREATE MODE
        if (isset($_FILES['offer_image']) && $_FILES['offer_image']['error'] == 0) {
            $name = time() . "_offer_" . basename($_FILES['offer_image']['name']);
            if (move_uploaded_file($_FILES['offer_image']['tmp_name'], $target_dir . $name)) {
                $db_path = "uploads/offers/" . $name;
                $conn->query("INSERT INTO offers (image_path, description) VALUES ('$db_path', '$desc')");
                $msg = "Offer image added successfully!";
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Image is required.";
        }
    }
}

// --- 3. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $conn->query("SELECT image_path FROM offers WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if (file_exists("../" . $row['image_path'])) { unlink("../" . $row['image_path']); }
        $conn->query("DELETE FROM offers WHERE id=$id");
        header("Location: offers.php");
    }
}

// --- CHECK EDIT MODE ---
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM offers WHERE id=$edit_id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// FETCH FLASH SALE DATA
$fs_data = $conn->query("SELECT * FROM flash_sale WHERE id=1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Offers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div class="w-64 bg-slate-900 text-white flex flex-col shrink-0">
        <div class="p-6 font-bold text-xl tracking-wider text-center border-b border-slate-800">ADMIN PANEL</div>
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Overview</a>
            <a href="applications.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Applications</a>
            <a href="attendance.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Attendance</a>
            <a href="banners.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Banners</a>
            <a href="employees.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="users" class="w-4 h-4 mr-2"></i> Employees</a>
            <a href="offers.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="percent" class="w-4 h-4 mr-2"></i> Offers & Ads</a>
            <a href="projects.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="briefcase" class="w-4 h-4 mr-2"></i> Projects</a>
            <a href="promocodes.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="ticket-percent" class="w-4 h-4 mr-2"></i> Promo Codes</a>
            <a href="service_courses.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="tag" class="w-4 h-4 mr-2"></i> Service & Courses</a>
            <a href="portfolio.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Website Portfolio</a>
            <a href="settings.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="settings" class="w-4 h-4 mr-2"></i> Settings</a>
            <a href="../login.php" class="flex items-center px-4 py-2 mt-4 text-red-400 hover:bg-slate-800 rounded"><i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Manage Offers & Flash Sales</h2>

        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4 border border-green-200'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4 border border-red-200'>$error</div>"; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- 1. FLASH SALE TIMER SETTINGS (Top Priority) -->
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow h-fit border-t-4 border-red-500">
                <h3 class="font-bold text-lg mb-4 flex items-center text-red-600">
                    <i data-lucide="clock" class="w-5 h-5 mr-2"></i> Global Flash Sale
                </h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500">Sale Description</label>
                        <input type="text" name="fs_description" value="<?php echo htmlspecialchars($fs_data['description'] ?? ''); ?>" class="w-full p-2 border rounded focus:border-red-500 outline-none" placeholder="e.g. Limited Time Offer!">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500">Expiry Date & Time</label>
                        <input type="datetime-local" name="fs_expiry" class="w-full p-2 border rounded focus:border-red-500 outline-none" value="<?php echo ($fs_data['expiry_date']) ? date('Y-m-d\TH:i', strtotime($fs_data['expiry_date'])) : ''; ?>">
                        <p class="text-[10px] text-gray-400 mt-1">Set future date to activate. Clear to disable.</p>
                    </div>
                    <button name="update_flash_sale" class="w-full bg-red-600 text-white py-2 rounded font-bold hover:bg-red-700 transition shadow-lg">
                        Update Timer
                    </button>
                </form>
            </div>

            <!-- 2. OFFER IMAGE FORM -->
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow h-fit border-t-4 <?php echo $edit_mode ? 'border-yellow-500' : 'border-orange-500'; ?>">
                <h3 class="font-bold text-lg mb-4 flex items-center text-slate-700">
                    <i data-lucide="image-plus" class="w-5 h-5 mr-2"></i> 
                    <?php echo $edit_mode ? 'Edit Banner' : 'Add Offer Banner'; ?>
                </h3>
                
                <?php if($edit_mode): ?>
                    <a href="offers.php" class="text-xs text-red-500 hover:underline mb-4 block">Cancel Edit</a>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="offer_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="text-xs font-bold text-gray-500">Banner Image <?php if(!$edit_mode) echo '*'; ?></label>
                        <?php if($edit_mode && !empty($edit_data['image_path'])): ?>
                            <img src="../<?php echo $edit_data['image_path']; ?>" class="w-full h-32 object-cover rounded mb-2 border">
                        <?php endif; ?>
                        <input type="file" name="offer_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"/>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500">Banner Caption</label>
                        <textarea name="description" required class="w-full p-2 border rounded focus:border-orange-500 outline-none" rows="2" placeholder="e.g. 50% OFF on Web Development!"><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                    </div>

                    <button name="save_offer" class="w-full text-white py-2 rounded font-bold transition <?php echo $edit_mode ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-orange-600 hover:bg-orange-700'; ?>">
                        <?php echo $edit_mode ? 'Update Banner' : 'Add Banner'; ?>
                    </button>
                </form>
            </div>

            <!-- 3. ACTIVE BANNERS LIST -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow p-6 border border-slate-100">
                <h3 class="font-bold text-lg mb-4 text-slate-700">Active Banners</h3>
                <?php 
                $res = $conn->query("SELECT * FROM offers ORDER BY created_at DESC");
                if ($res->num_rows == 0) echo "<p class='text-gray-400 italic text-sm'>No banners uploaded yet.</p>";
                ?>
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                    <?php while($row = $res->fetch_assoc()): ?>
                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition">
                        <img src="../<?php echo $row['image_path']; ?>" class="w-full h-32 object-cover">
                        
                        <div class="absolute bottom-0 inset-x-0 bg-black/60 p-2">
                            <p class="text-white text-xs truncate font-bold"><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>

                        <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                            <a href="?edit=<?php echo $row['id']; ?>" class="bg-white text-yellow-600 p-1 rounded shadow hover:bg-yellow-50" title="Edit"><i data-lucide="pencil" class="w-3 h-3"></i></a>
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this banner?')" class="bg-white text-red-600 p-1 rounded shadow hover:bg-red-50" title="Delete"><i data-lucide="trash-2" class="w-3 h-3"></i></a>
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
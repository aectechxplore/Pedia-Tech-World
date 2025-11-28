<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";
$edit_mode = false;
$edit_data = [];

// --- 1. HANDLE ADD ITEM ---
if (isset($_POST['add_project'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']); // Category: project, client, achievement
    $desc = $conn->real_escape_string($_POST['description']);
    $url = $conn->real_escape_string($_POST['url']);
    $duration = $conn->real_escape_string($_POST['duration']);
    $target_dir = "../uploads/portfolio/";
    
    // Ensure upload directory exists
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    // A. Upload Cover Image (Required)
    $cover_db_path = "";
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $c_name = time() . "_cover_" . basename($_FILES['cover_image']['name']);
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_dir . $c_name)) {
            $cover_db_path = "uploads/portfolio/" . $c_name;
        }
    } else {
        $error = "Cover image is required.";
    }

    // B. Upload Gallery Images (Optional)
    $gallery_paths = [];
    if (!$error && isset($_FILES['gallery']) && count($_FILES['gallery']['name']) > 0) {
        $total = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['gallery']['error'][$i] == 0) {
                $g_name = time() . "_gal_" . $i . "_" . basename($_FILES['gallery']['name'][$i]);
                if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $target_dir . $g_name)) {
                    $gallery_paths[] = "uploads/portfolio/" . $g_name;
                }
            }
        }
    }
    $gallery_json = json_encode($gallery_paths);

    // C. Upload Video (Optional)
    $video_db_path = "";
    if (!$error && isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $v_name = time() . "_vid_" . basename($_FILES['video']['name']);
        $allowed = ['mp4', 'webm', 'ogg'];
        $ext = strtolower(pathinfo($v_name, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && move_uploaded_file($_FILES['video']['tmp_name'], $target_dir . $v_name)) {
            $video_db_path = "uploads/portfolio/" . $v_name;
        }
    }

    if (!$error) {
        $sql = "INSERT INTO portfolio (title, category, description, project_url, duration, cover_image, image_path, video_path) 
                VALUES ('$title', '$category', '$desc', '$url', '$duration', '$cover_db_path', '$gallery_json', '$video_db_path')";
        if ($conn->query($sql)) { $msg = "Item added successfully!"; } 
        else { $error = "DB Error: " . $conn->error; }
    }
}

// --- 2. HANDLE UPDATE ITEM ---
if (isset($_POST['update_project'])) {
    $id = intval($_POST['project_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $desc = $conn->real_escape_string($_POST['description']);
    $url = $conn->real_escape_string($_POST['url']);
    $duration = $conn->real_escape_string($_POST['duration']);
    $target_dir = "../uploads/portfolio/";

    // Fetch existing data to handle file replacements
    $old_row = $conn->query("SELECT cover_image, image_path, video_path FROM portfolio WHERE id=$id")->fetch_assoc();
    
    // A. Cover Image Logic
    $cover_db_path = $old_row['cover_image']; // Default to old
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        // Delete old file if exists
        if (!empty($old_row['cover_image']) && file_exists("../" . $old_row['cover_image'])) {
            unlink("../" . $old_row['cover_image']);
        }
        // Upload new
        $c_name = time() . "_cover_" . basename($_FILES['cover_image']['name']);
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_dir . $c_name)) {
            $cover_db_path = "uploads/portfolio/" . $c_name;
        }
    }

    // B. Gallery Logic
    $current_gallery = json_decode($old_row['image_path'], true) ?? [];
    
    // Check if "Clear Gallery" was selected
    if (isset($_POST['clear_gallery'])) {
        foreach ($current_gallery as $img) { if (file_exists("../" . $img)) unlink("../" . $img); }
        $current_gallery = [];
    }

    // Add new gallery images
    if (isset($_FILES['gallery'])) {
        $total = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['gallery']['error'][$i] == 0) {
                $g_name = time() . "_gal_" . $i . "_" . basename($_FILES['gallery']['name'][$i]);
                if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $target_dir . $g_name)) {
                    $current_gallery[] = "uploads/portfolio/" . $g_name;
                }
            }
        }
    }
    $gallery_json = json_encode($current_gallery);

    // C. Video Logic
    $video_db_path = $old_row['video_path'];
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        if (!empty($old_row['video_path']) && file_exists("../" . $old_row['video_path'])) unlink("../" . $old_row['video_path']);
        $v_name = time() . "_vid_" . basename($_FILES['video']['name']);
        if (move_uploaded_file($_FILES['video']['tmp_name'], $target_dir . $v_name)) {
            $video_db_path = "uploads/portfolio/" . $v_name;
        }
    }

    $sql = "UPDATE portfolio SET title='$title', category='$category', description='$desc', project_url='$url', duration='$duration', 
            cover_image='$cover_db_path', image_path='$gallery_json', video_path='$video_db_path' WHERE id=$id";
    
    if ($conn->query($sql)) {
        $msg = "Item updated successfully!";
        echo "<script>window.location.href='portfolio.php';</script>";
    } else {
        $error = "DB Error: " . $conn->error;
    }
}

// --- 3. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $conn->query("SELECT cover_image, image_path, video_path FROM portfolio WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['cover_image']) && file_exists("../" . $row['cover_image'])) unlink("../" . $row['cover_image']);
        if (!empty($row['video_path']) && file_exists("../" . $row['video_path'])) unlink("../" . $row['video_path']);
        $imgs = json_decode($row['image_path'], true);
        if (is_array($imgs)) { foreach ($imgs as $img) { if (file_exists("../" . $img)) unlink("../" . $img); } }
        
        $conn->query("DELETE FROM portfolio WHERE id=$id");
        header("Location: portfolio.php");
    }
}

// --- 4. CHECK EDIT MODE ---
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM portfolio WHERE id=$edit_id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Content</title>
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
            <a href="portfolio.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="image" class="w-4 h-4 mr-2"></i> Website Portfolio</a>
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
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Manage Website Content</h2>

        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4 border border-green-200'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4 border border-red-200'>$error</div>"; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- CREATE / EDIT FORM -->
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow h-fit border-t-4 <?php echo $edit_mode ? 'border-yellow-500' : 'border-orange-500'; ?>">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg flex items-center text-slate-700">
                        <i data-lucide="<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?>" class="w-5 h-5 mr-2 <?php echo $edit_mode ? 'text-yellow-600' : 'text-orange-600'; ?>"></i> 
                        <?php echo $edit_mode ? 'Edit Item' : 'Add Content'; ?>
                    </h3>
                    <?php if($edit_mode): ?>
                        <a href="portfolio.php" class="text-sm text-red-500 hover:underline">Cancel</a>
                    <?php endif; ?>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php if($edit_mode): ?>
                        <input type="hidden" name="project_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>

                    <!-- Category Select -->
                    <div>
                        <label class="text-xs font-bold text-gray-500">Category</label>
                        <select name="category" class="w-full p-2 border rounded bg-gray-50 font-bold focus:border-orange-500 outline-none">
                            <option value="project" <?php if($edit_mode && $edit_data['category']=='project') echo 'selected'; ?>>Project Work</option>
                            <option value="client" <?php if($edit_mode && $edit_data['category']=='client') echo 'selected'; ?>>Happy Client (Testimonial)</option>
                            <option value="achievement" <?php if($edit_mode && $edit_data['category']=='achievement') echo 'selected'; ?>>Student Achievement</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500">Title / Name</label>
                        <input type="text" name="title" required value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>" class="w-full p-2 border rounded focus:border-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500">Duration / Subtitle</label>
                        <input type="text" name="duration" placeholder="e.g. 3 Months or 'CEO, Company X'" required value="<?php echo $edit_mode ? htmlspecialchars($edit_data['duration']) : ''; ?>" class="w-full p-2 border rounded focus:border-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500">Link URL (Optional)</label>
                        <input type="url" name="url" placeholder="https://" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['project_url']) : ''; ?>" class="w-full p-2 border rounded focus:border-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500">Description / Testimonial</label>
                        <textarea name="description" required class="w-full p-2 border rounded focus:border-orange-500 outline-none" rows="3"><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                    </div>
                    
                    <!-- Cover Image -->
                    <div>
                        <label class="text-xs font-bold text-gray-500 flex justify-between">
                            Main Image / Photo <?php if(!$edit_mode) echo '<span class="text-red-500">*</span>'; ?>
                            <?php if($edit_mode && !empty($edit_data['cover_image'])): ?>
                                <a href="../<?php echo $edit_data['cover_image']; ?>" target="_blank" class="text-blue-500 underline">View Current</a>
                            <?php endif; ?>
                        </label>
                        <input type="file" name="cover_image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?> class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"/>
                    </div>

                    <!-- Gallery Images -->
                    <div>
                        <label class="text-xs font-bold text-gray-500">Gallery Images (Multiple)</label>
                        <input type="file" name="gallery[]" multiple accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"/>
                        
                        <?php if($edit_mode): 
                            $gal = json_decode($edit_data['image_path'], true) ?? [];
                            if(count($gal) > 0): ?>
                                <div class="flex items-center mt-2 p-2 bg-red-50 rounded border border-red-100">
                                    <input type="checkbox" name="clear_gallery" id="cg" class="mr-2 accent-red-500"> 
                                    <label for="cg" class="text-xs text-red-600">Delete <?php echo count($gal); ?> existing gallery images?</label>
                                </div>
                        <?php endif; endif; ?>
                    </div>

                    <!-- Video -->
                    <div>
                        <label class="text-xs font-bold text-gray-500">Video (Optional)</label>
                        <input type="file" name="video" accept="video/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"/>
                        <?php if($edit_mode && !empty($edit_data['video_path'])): ?>
                            <p class="text-xs text-green-600 mt-1 flex items-center"><i data-lucide="video" class="w-3 h-3 mr-1"></i> Current video exists.</p>
                        <?php endif; ?>
                    </div>

                    <button name="<?php echo $edit_mode ? 'update_project' : 'add_project'; ?>" class="w-full text-white py-2 rounded font-bold transition <?php echo $edit_mode ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-orange-600 hover:bg-orange-700'; ?>">
                        <?php echo $edit_mode ? 'Update Content' : 'Add to Website'; ?>
                    </button>
                </form>
            </div>

            <!-- VIEW LIST GROUPED BY CATEGORY -->
            <div class="lg:col-span-2 space-y-8">
                <?php 
                // Define categories to display sections
                $categories = [
                    'project' => 'Projects Portfolio', 
                    'client' => 'Happy Clients', 
                    'achievement' => 'Student Achievements'
                ];

                foreach($categories as $cat_key => $cat_name):
                    $res = $conn->query("SELECT * FROM portfolio WHERE category='$cat_key' ORDER BY created_at DESC");
                ?>
                <div class="bg-white rounded-xl shadow overflow-hidden border border-slate-100">
                    <div class="p-4 bg-gray-50 font-bold text-slate-700 border-b flex justify-between items-center">
                        <span class="flex items-center text-lg">
                            <?php if($cat_key == 'project') echo '<i data-lucide="briefcase" class="w-5 h-5 mr-2"></i>'; ?>
                            <?php if($cat_key == 'client') echo '<i data-lucide="smile" class="w-5 h-5 mr-2"></i>'; ?>
                            <?php if($cat_key == 'achievement') echo '<i data-lucide="trophy" class="w-5 h-5 mr-2"></i>'; ?>
                            <?php echo $cat_name; ?>
                        </span>
                        <span class="text-xs bg-slate-200 px-2 py-1 rounded-full"><?php echo $res->num_rows; ?> Items</span>
                    </div>
                    
                    <div class="divide-y">
                        <?php if($res->num_rows == 0): ?>
                            <div class="p-4 text-center text-gray-400 italic text-sm">No content in this category yet.</div>
                        <?php endif; ?>

                        <?php while($row = $res->fetch_assoc()): 
                            // Get thumbnail
                            $thumb = !empty($row['cover_image']) ? $row['cover_image'] : '';
                        ?>
                        <div class="p-4 flex gap-4 hover:bg-slate-50 transition">
                            <div class="w-20 h-20 shrink-0 bg-gray-200 rounded overflow-hidden">
                                <?php if($thumb): ?>
                                    <img src="../<?php echo $thumb; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400"><i data-lucide="image-off"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($row['title']); ?></h4>
                                <p class="text-xs text-orange-600 font-bold uppercase mb-1"><?php echo htmlspecialchars($row['duration']); ?></p>
                                <p class="text-sm text-gray-600 line-clamp-2"><?php echo htmlspecialchars($row['description']); ?></p>
                            </div>
                            <div class="flex flex-col gap-2 text-xs justify-center min-w-[80px]">
                                <a href="?edit=<?php echo $row['id']; ?>" class="text-yellow-600 font-bold flex items-center hover:text-yellow-800">
                                    <i data-lucide="pencil" class="w-3 h-3 mr-1"></i> Edit
                                </a>
                                <a href="portfolio.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this item? All images will be removed.')" class="text-red-600 font-bold flex items-center hover:text-red-800">
                                    <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Delete
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'services';

// --- HANDLE SERVICE ACTIONS ---
if (isset($_POST['save_service'])) {
    $cat = $conn->real_escape_string($_POST['category']);
    $title = $conn->real_escape_string($_POST['title']);
    $price = $conn->real_escape_string($_POST['price']);
    $duration = $conn->real_escape_string($_POST['duration']);
    // Convert comma-separated features to JSON
    $features_arr = array_map('trim', explode(',', $_POST['features']));
    $features = $conn->real_escape_string(json_encode($features_arr));

    if (!empty($_POST['service_id'])) {
        $id = intval($_POST['service_id']);
        $sql = "UPDATE services SET category='$cat', title='$title', price='$price', duration='$duration', features='$features' WHERE id=$id";
    } else {
        $sql = "INSERT INTO services (category, title, price, duration, features) VALUES ('$cat', '$title', '$price', '$duration', '$features')";
    }
    
    if ($conn->query($sql)) { $msg = "Service saved successfully!"; } 
    else { $error = "DB Error: " . $conn->error; }
}

if (isset($_GET['delete_service'])) {
    $id = intval($_GET['delete_service']);
    $conn->query("DELETE FROM services WHERE id=$id");
    header("Location: services_courses.php?tab=services");
}

// --- HANDLE COURSE ACTIONS ---
if (isset($_POST['save_course'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $fee = $conn->real_escape_string($_POST['fee']);
    $duration = $conn->real_escape_string($_POST['duration']);
    $desc = $conn->real_escape_string($_POST['description']);

    if (!empty($_POST['course_id'])) {
        $id = intval($_POST['course_id']);
        $sql = "UPDATE courses SET title='$title', fee='$fee', duration='$duration', description='$desc' WHERE id=$id";
    } else {
        $sql = "INSERT INTO courses (title, fee, duration, description) VALUES ('$title', '$fee', '$duration', '$desc')";
    }

    if ($conn->query($sql)) { $msg = "Course saved successfully!"; $active_tab='courses'; } 
    else { $error = "DB Error: " . $conn->error; }
}

if (isset($_GET['delete_course'])) {
    $id = intval($_GET['delete_course']);
    $conn->query("DELETE FROM courses WHERE id=$id");
    header("Location: services_courses.php?tab=courses");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Services & Courses</title>
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
            <a href="service_courses.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="tag" class="w-4 h-4 mr-2"></i> Service & Courses</a>
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
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Manage Offerings</h2>

        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>$error</div>"; ?>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-6 border-b border-slate-300">
            <button onclick="switchTab('services')" class="px-4 py-2 font-bold <?php echo $active_tab=='services' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500'; ?>">Services & Pricing</button>
            <button onclick="switchTab('courses')" class="px-4 py-2 font-bold <?php echo $active_tab=='courses' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500'; ?>">Training Courses</button>
        </div>

        <!-- SERVICES TAB -->
        <div id="services-tab" class="<?php echo $active_tab=='services' ? '' : 'hidden'; ?>">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form -->
                <div class="bg-white p-6 rounded-xl shadow h-fit">
                    <h3 class="font-bold text-lg mb-4">Add/Edit Service</h3>
                    <form method="POST">
                        <input type="hidden" name="service_id" id="srv_id">
                        <div class="space-y-3">
                            <input type="text" name="category" id="srv_cat" placeholder="Category (e.g. Web Dev)" required class="w-full p-2 border rounded">
                            <input type="text" name="title" id="srv_title" placeholder="Package Title (e.g. Basic)" required class="w-full p-2 border rounded">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="price" id="srv_price" placeholder="Price (e.g. ₹10k)" required class="w-full p-2 border rounded">
                                <input type="text" name="duration" id="srv_dur" placeholder="Duration" required class="w-full p-2 border rounded">
                            </div>
                            <textarea name="features" id="srv_feat" placeholder="Features (Comma separated: 5 Pages, SEO, Mobile...)" required rows="3" class="w-full p-2 border rounded"></textarea>
                            <button name="save_service" class="w-full bg-orange-600 text-white py-2 rounded font-bold hover:bg-orange-700">Save Service</button>
                            <button type="button" onclick="resetServiceForm()" class="w-full text-xs text-gray-500 mt-2 underline">Clear Form</button>
                        </div>
                    </form>
                </div>
                <!-- List -->
                <div class="lg:col-span-2 space-y-4">
                    <?php 
                    $s_res = $conn->query("SELECT * FROM services ORDER BY category, price");
                    while($s = $s_res->fetch_assoc()): 
                        $feats = json_decode($s['features'], true);
                        $feat_str = is_array($feats) ? implode(', ', $feats) : $s['features'];
                    ?>
                    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-orange-500 flex justify-between items-center">
                        <div>
                            <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-600 font-bold"><?php echo $s['category']; ?></span>
                            <h4 class="font-bold text-lg"><?php echo $s['title']; ?> <span class="text-orange-600 text-sm">(<?php echo $s['price']; ?>)</span></h4>
                            <p class="text-xs text-gray-500"><?php echo $s['duration']; ?> | <?php echo substr($feat_str, 0, 50); ?>...</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick='editService(<?php echo json_encode($s); ?>)' class="text-yellow-600 hover:bg-yellow-50 p-2 rounded"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                            <a href="?delete_service=<?php echo $s['id']; ?>" onclick="return confirm('Delete?')" class="text-red-600 hover:bg-red-50 p-2 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- COURSES TAB -->
        <div id="courses-tab" class="<?php echo $active_tab=='courses' ? '' : 'hidden'; ?>">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form -->
                <div class="bg-white p-6 rounded-xl shadow h-fit">
                    <h3 class="font-bold text-lg mb-4">Add/Edit Course</h3>
                    <form method="POST">
                        <input type="hidden" name="course_id" id="crs_id">
                        <div class="space-y-3">
                            <input type="text" name="title" id="crs_title" placeholder="Course Title" required class="w-full p-2 border rounded">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="fee" id="crs_fee" placeholder="Fee" required class="w-full p-2 border rounded">
                                <input type="text" name="duration" id="crs_dur" placeholder="Duration" required class="w-full p-2 border rounded">
                            </div>
                            <textarea name="description" id="crs_desc" placeholder="Course Description" required rows="3" class="w-full p-2 border rounded"></textarea>
                            <button name="save_course" class="w-full bg-red-600 text-white py-2 rounded font-bold hover:bg-red-700">Save Course</button>
                            <button type="button" onclick="resetCourseForm()" class="w-full text-xs text-gray-500 mt-2 underline">Clear Form</button>
                        </div>
                    </form>
                </div>
                <!-- List -->
                <div class="lg:col-span-2 space-y-4">
                    <?php 
                    $c_res = $conn->query("SELECT * FROM courses ORDER BY id DESC");
                    while($c = $c_res->fetch_assoc()): 
                    ?>
                    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-red-500 flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-lg"><?php echo $c['title']; ?></h4>
                            <p class="text-sm text-gray-600 font-bold"><?php echo $c['fee']; ?> <span class="font-normal text-gray-400">| <?php echo $c['duration']; ?></span></p>
                            <p class="text-xs text-gray-500 truncate w-64"><?php echo $c['description']; ?></p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick='editCourse(<?php echo json_encode($c); ?>)' class="text-yellow-600 hover:bg-yellow-50 p-2 rounded"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                            <a href="?delete_course=<?php echo $c['id']; ?>" onclick="return confirm('Delete?')" class="text-red-600 hover:bg-red-50 p-2 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
        function switchTab(tab) {
            document.getElementById('services-tab').classList.add('hidden');
            document.getElementById('courses-tab').classList.add('hidden');
            document.getElementById(tab + '-tab').classList.remove('hidden');
            // Ideally update URL or buttons styling too, but simple toggle works for functionality
        }

        function editService(data) {
            switchTab('services');
            document.getElementById('srv_id').value = data.id;
            document.getElementById('srv_cat').value = data.category;
            document.getElementById('srv_title').value = data.title;
            document.getElementById('srv_price').value = data.price;
            document.getElementById('srv_dur').value = data.duration;
            // Handle JSON features to comma string
            let feats = JSON.parse(data.features);
            document.getElementById('srv_feat').value = Array.isArray(feats) ? feats.join(', ') : data.features;
        }

        function resetServiceForm() {
            document.getElementById('srv_id').value = '';
            document.getElementById('srv_cat').value = '';
            document.getElementById('srv_title').value = '';
            document.getElementById('srv_price').value = '';
            document.getElementById('srv_dur').value = '';
            document.getElementById('srv_feat').value = '';
        }

        function editCourse(data) {
            switchTab('courses');
            document.getElementById('crs_id').value = data.id;
            document.getElementById('crs_title').value = data.title;
            document.getElementById('crs_fee').value = data.fee;
            document.getElementById('crs_dur').value = data.duration;
            document.getElementById('crs_desc').value = data.description;
        }

        function resetCourseForm() {
            document.getElementById('crs_id').value = '';
            document.getElementById('crs_title').value = '';
            document.getElementById('crs_fee').value = '';
            document.getElementById('crs_dur').value = '';
            document.getElementById('crs_desc').value = '';
        }

        lucide.createIcons();
    </script>
</body>
</html>
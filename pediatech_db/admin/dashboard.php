<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

// --- HANDLE PROFILE IMAGE UPLOAD ---
if (isset($_FILES['profile_upload']) && $_FILES['profile_upload']['error'] == 0) {
    $target_dir = "../uploads/profiles/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_ext = strtolower(pathinfo($_FILES['profile_upload']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_ext, $allowed)) {
        $new_name = time() . "_admin_" . $_SESSION['user_id'] . "." . $file_ext;
        $target_file = $target_dir . $new_name;
        
        if (move_uploaded_file($_FILES['profile_upload']['tmp_name'], $target_file)) {
            // Delete old image
            $old_q = $conn->query("SELECT profile_image FROM users WHERE id=" . $_SESSION['user_id']);
            $old_row = $old_q->fetch_assoc();
            if (!empty($old_row['profile_image']) && file_exists("../" . $old_row['profile_image'])) {
                unlink("../" . $old_row['profile_image']);
            }
            
            // Update DB
            $db_path = "uploads/profiles/" . $new_name;
            $conn->query("UPDATE users SET profile_image='$db_path' WHERE id=" . $_SESSION['user_id']);
            header("Location: dashboard.php"); // Refresh
            exit;
        }
    }
}

// --- HANDLE PROFILE IMAGE DELETE ---
if (isset($_GET['del_profile'])) {
    $old_q = $conn->query("SELECT profile_image FROM users WHERE id=" . $_SESSION['user_id']);
    $old_row = $old_q->fetch_assoc();
    if (!empty($old_row['profile_image']) && file_exists("../" . $old_row['profile_image'])) {
        unlink("../" . $old_row['profile_image']);
    }
    $conn->query("UPDATE users SET profile_image=NULL WHERE id=" . $_SESSION['user_id']);
    header("Location: dashboard.php");
    exit;
}

// --- 1. FETCH DASHBOARD STATS ---
$proj_ongoing = $conn->query("SELECT COUNT(*) as c FROM projects WHERE status='Ongoing'")->fetch_assoc()['c'];
$proj_completed = $conn->query("SELECT COUNT(*) as c FROM projects WHERE status='Completed'")->fetch_assoc()['c'];

$today = date('Y-m-d');
$att_present = $conn->query("SELECT COUNT(*) as c FROM attendance WHERE date='$today' AND status='Present'")->fetch_assoc()['c'];
$total_emps = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='employee'")->fetch_assoc()['c'];
$att_absent = $total_emps - $att_present;

// --- 2. FETCH ADMIN PROFILE ---
$admin_id = $_SESSION['user_id'];
$admin_query = $conn->query("SELECT name, profile_image FROM users WHERE id=$admin_id");
$admin_data = $admin_query->fetch_assoc();

$has_custom_img = (!empty($admin_data['profile_image']) && file_exists("../" . $admin_data['profile_image']));
$admin_pic = $has_custom_img 
    ? "../" . $admin_data['profile_image'] 
    : "https://ui-avatars.com/api/?name=" . urlencode($admin_data['name']) . "&background=0D8ABC&color=fff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PTW Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <div class="w-64 bg-slate-900 text-white flex flex-col shrink-0">
        <div class="p-6 font-bold text-xl tracking-wider text-center border-b border-slate-800">ADMIN PANEL</div>
        
        <!-- Menu Links -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Overview</a>
            <a href="applications.php" class="flex items-center px-4 py-2 hover:bg-slate-800 rounded text-slate-300"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Applications</a>
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
            
            <!-- Hidden Upload Form -->
            <form id="profileForm" method="POST" enctype="multipart/form-data" class="hidden">
                <input type="file" name="profile_upload" id="profileUpload" accept="image/*" onchange="document.getElementById('profileForm').submit()">
            </form>

            <!-- Profile Image Area -->
            <div class="relative group shrink-0">
                <img src="<?php echo $admin_pic; ?>" alt="Admin" class="w-10 h-10 rounded-full object-cover border-2 border-orange-500">
                
                <!-- Hover Controls Overlay -->
                <div class="absolute inset-0 bg-black/80 rounded-full hidden group-hover:flex items-center justify-center gap-1">
                    <!-- View -->
                    <button onclick="openProfileLightbox('<?php echo $admin_pic; ?>')" title="View" class="text-white hover:text-orange-400">
                        <i data-lucide="eye" class="w-3 h-3"></i>
                    </button>
                    
                    <!-- Upload -->
                    <label for="profileUpload" class="cursor-pointer text-white hover:text-green-400" title="Upload">
                        <i data-lucide="camera" class="w-3 h-3"></i>
                    </label>

                    <!-- Delete (Only if custom image exists) -->
                    <?php if($has_custom_img): ?>
                    <a href="?del_profile=1" onclick="return confirm('Remove profile picture?')" class="text-white hover:text-red-500" title="Remove">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="overflow-hidden">
                <p class="font-bold text-sm truncate"><?php echo htmlspecialchars($admin_data['name']); ?></p>
                <p class="text-xs text-slate-400">Administrator</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-y-auto">
        <h2 class="text-3xl font-bold text-slate-800 mb-8">Dashboard Overview</h2>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-orange-500 hover:shadow-lg transition">
                <div class="text-gray-500 text-sm">Total Employees</div>
                <div class="text-3xl font-bold text-slate-800"><?php echo $total_emps; ?></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500 hover:shadow-lg transition">
                <div class="text-gray-500 text-sm">Present Today</div>
                <div class="text-3xl font-bold text-slate-800"><?php echo $att_present; ?></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500 hover:shadow-lg transition">
                <div class="text-gray-500 text-sm">Ongoing Projects</div>
                <div class="text-3xl font-bold text-slate-800"><?php echo $proj_ongoing; ?></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500 hover:shadow-lg transition">
                <div class="text-gray-500 text-sm">Completed Projects</div>
                <div class="text-3xl font-bold text-slate-800"><?php echo $proj_completed; ?></div>
            </div>
        </div>

        <!-- Graphs -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="font-bold mb-4 text-slate-700 flex items-center"><i data-lucide="pie-chart" class="w-5 h-5 mr-2 text-orange-600"></i> Project Status</h3>
                <div class="h-64">
                    <canvas id="projectChart"></canvas>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="font-bold mb-4 text-slate-700 flex items-center"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-2 text-green-600"></i> Attendance Today</h3>
                <div class="h-64">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- PROFILE IMAGE LIGHTBOX (Floating Overlay) -->
    <div id="profile-lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden flex flex-col items-center justify-center opacity-0 transition-opacity duration-300" onclick="closeProfileLightbox()">
        <button class="absolute top-6 right-6 text-white/70 hover:text-white p-2 rounded-full hover:bg-white/10 transition">
            <i data-lucide="x" class="w-10 h-10"></i>
        </button>
        <div class="relative p-2" onclick="event.stopPropagation()">
            <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[85vh] object-contain shadow-2xl rounded-md border-4 border-slate-800 bg-slate-900">
            <p class="text-center text-white/60 mt-4 font-mono text-sm uppercase tracking-widest">Admin Profile</p>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // --- LIGHTBOX LOGIC ---
        function openProfileLightbox(src) {
            const lightbox = document.getElementById('profile-lightbox');
            const img = document.getElementById('lightbox-img');
            img.src = src;
            lightbox.classList.remove('hidden');
            setTimeout(() => { lightbox.classList.remove('opacity-0'); }, 10);
        }

        function closeProfileLightbox() {
            const lightbox = document.getElementById('profile-lightbox');
            lightbox.classList.add('opacity-0');
            setTimeout(() => { lightbox.classList.add('hidden'); }, 300);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeProfileLightbox();
        });

        // --- CHARTS ---
        new Chart(document.getElementById('projectChart'), {
            type: 'doughnut',
            data: {
                labels: ['Ongoing', 'Completed'],
                datasets: [{
                    data: [<?php echo $proj_ongoing; ?>, <?php echo $proj_completed; ?>],
                    backgroundColor: ['#0891b2', '#22c55e'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('attendanceChart'), {
            type: 'bar',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    label: 'Employees',
                    data: [<?php echo $att_present; ?>, <?php echo $att_absent; ?>],
                    backgroundColor: ['#22c55e', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>
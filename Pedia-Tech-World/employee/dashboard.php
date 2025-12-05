<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$msg = "";

// --- 1. HANDLE ATTENDANCE ---
if (isset($_POST['mark_attendance'])) {
    $check_sql = "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'";
    if ($conn->query($check_sql)->num_rows == 0) {
        $now = date('H:i:s');
        $conn->query("INSERT INTO attendance (user_id, date, check_in_time, status) VALUES ($user_id, '$today', '$now', 'Present')");
        $msg = "Attendance Marked Successfully!";
    } else {
        $msg = "You are already marked present for today.";
    }
}
$is_present = $conn->query("SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'")->num_rows > 0;

// --- 2. HANDLE TASK UPDATE ---
if (isset($_POST['submit_update'])) {
    $pid = $_POST['project_id'];
    $text = $conn->real_escape_string($_POST['update_text']);
    $conn->query("INSERT INTO project_updates (project_id, user_id, update_text) VALUES ($pid, $user_id, '$text')");
    $msg = "Daily update submitted successfully.";
}

// --- 3. FETCH DATA ---
// Assigned Projects
$my_projects = $conn->query("SELECT p.id, p.name FROM projects p JOIN project_assignments pa ON p.id = pa.project_id WHERE pa.user_id = $user_id");

// Checklist & Stats
$tasks_res = $conn->query("SELECT * FROM project_checklist WHERE user_id = $user_id ORDER BY created_at DESC");
$tasks = [];
$stats = ['Pending' => 0, 'Ongoing' => 0, 'Completed' => 0];
while($t = $tasks_res->fetch_assoc()) {
    $tasks[] = $t;
    if(isset($stats[$t['status']])) {
        $stats[$t['status']]++;
    }
}

// User Profile
$user_data = $conn->query("SELECT name, profile_image FROM users WHERE id=$user_id")->fetch_assoc();
$profile_pic = (!empty($user_data['profile_image']) && file_exists("../" . $user_data['profile_image'])) 
    ? "../" . $user_data['profile_image'] 
    : "https://ui-avatars.com/api/?name=" . urlencode($user_data['name']) . "&background=0D8ABC&color=fff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 min-h-screen pb-20">
    
    <!-- Navbar -->
    <nav class="bg-slate-900 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-6xl mx-auto flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                
                <!-- Profile Image with Preview Trigger -->
                <div class="relative cursor-pointer group shrink-0" onclick="openProfileLightbox('<?php echo $profile_pic; ?>')">
                    <img src="<?php echo $profile_pic; ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover border-2 border-orange-400 shadow-sm bg-slate-800 group-hover:border-orange-300 transition">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-900 rounded-full"></span>
                    
                    <!-- Hover Hint -->
                    <div class="absolute inset-0 rounded-full bg-black/40 hidden group-hover:flex items-center justify-center transition-all">
                        <i data-lucide="eye" class="w-5 h-5 text-white drop-shadow-md"></i>
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="font-bold text-lg leading-tight truncate"><?php echo htmlspecialchars($user_data['name']); ?></div>
                    <div class="text-xs text-slate-400">Employee Portal</div>
                </div>
            </div>
            <a href="../login.php" class="flex items-center text-sm bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition shadow-md shrink-0">
                <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Logout
            </a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-4 md:p-8 space-y-8">
        <?php if($msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6 text-center border border-green-200 font-bold shadow-sm"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Attendance Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-orange-500">
                <h2 class="text-xl font-bold mb-4 flex items-center text-slate-800"><i data-lucide="clock" class="mr-2 text-orange-600"></i> Attendance</h2>
                <div class="text-center py-6">
                    <p class="text-gray-500 mb-2 font-bold text-lg"><?php echo date('l, d M Y'); ?></p>
                    <?php if($is_present): ?>
                        <div class="bg-green-100 text-green-800 px-6 py-4 rounded-lg font-bold w-full flex flex-col items-center border border-green-200">
                            <i data-lucide="check-circle" class="w-10 h-10 mb-2 text-green-600"></i>
                            You are Checked In
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <button name="mark_attendance" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-4 rounded-lg font-bold w-full transition shadow-lg flex items-center justify-center text-lg">
                                <i data-lucide="fingerprint" class="mr-2 w-6 h-6"></i> Mark Attendance
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- History -->
                <div class="mt-4 border-t pt-4">
                    <h3 class="font-bold text-sm mb-2 text-gray-500 uppercase tracking-wide">Recent Activity</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <?php 
                        $hist = $conn->query("SELECT * FROM attendance WHERE user_id = $user_id ORDER BY date DESC LIMIT 3");
                        if($hist->num_rows > 0):
                            while($h = $hist->fetch_assoc()): 
                        ?>
                        <li class="flex justify-between p-2 bg-slate-50 rounded border border-slate-100">
                            <span class="font-bold text-slate-700"><?php echo date('M d', strtotime($h['date'])); ?></span> 
                            <span class="text-green-600 font-mono bg-green-50 px-2 rounded"><?php echo date('h:i A', strtotime($h['check_in_time'])); ?></span>
                        </li>
                        <?php endwhile; else: ?>
                            <li class="text-gray-400 italic text-center py-2">No recent records found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Task Management Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-red-500">
                <h2 class="text-xl font-bold mb-4 flex items-center text-slate-800"><i data-lucide="list-todo" class="mr-2 text-red-600"></i> Daily Task Update</h2>
                <form method="POST">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Select Project</label>
                    <div class="relative mb-4">
                        <select name="project_id" required class="w-full p-3 border rounded bg-gray-50 focus:outline-none focus:border-red-500 appearance-none">
                            <?php 
                            if ($my_projects->num_rows > 0) {
                                while($p = $my_projects->fetch_assoc()) {
                                    echo "<option value='{$p['id']}'>{$p['name']}</option>";
                                }
                            } else {
                                echo "<option value=''>No Projects Assigned</option>";
                            }
                            ?>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-3.5 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                    
                    <label class="block text-sm font-bold text-gray-700 mb-2">Work Description</label>
                    <textarea name="update_text" required rows="5" class="w-full p-3 border rounded mb-4 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Describe what you worked on today..."></textarea>
                    
                    <button name="submit_update" class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg w-full font-bold transition shadow-md flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i> Submit Update
                    </button>
                </form>
            </div>
        </div>

        <!-- PROJECT STATUS GRAPH & CHECKLIST -->
        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
            <div class="p-6 bg-slate-800 text-white flex justify-between items-center">
                <h2 class="text-xl font-bold flex items-center"><i data-lucide="bar-chart-2" class="mr-2 text-orange-400"></i> Project Progress</h2>
                <span class="text-xs bg-slate-700 px-3 py-1 rounded-full font-mono"><?php echo count($tasks); ?> Topics</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3">
                
                <!-- 1. GRAPH AREA -->
                <div class="p-8 md:col-span-1 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-slate-100">
                    <?php if(count($tasks) > 0): ?>
                        <div class="w-48 h-48 relative">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-6 w-full space-y-2">
                            <div class="flex justify-between text-sm"><span class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Completed</span> <span class="font-bold text-slate-700"><?php echo $stats['Completed']; ?></span></div>
                            <div class="flex justify-between text-sm"><span class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Ongoing</span> <span class="font-bold text-slate-700"><?php echo $stats['Ongoing']; ?></span></div>
                            <div class="flex justify-between text-sm"><span class="flex items-center"><span class="w-3 h-3 rounded-full bg-gray-300 mr-2"></span>Pending</span> <span class="font-bold text-slate-700"><?php echo $stats['Pending']; ?></span></div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-gray-400">
                            <i data-lucide="pie-chart" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                            <p>No Data</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. CHECKLIST DETAILS -->
                <div class="p-0 md:col-span-2 bg-slate-50">
                    <div class="p-4 font-bold text-gray-500 text-sm uppercase tracking-wide border-b bg-white">Your Checklist</div>
                    <div class="divide-y h-80 overflow-y-auto">
                        <?php if(empty($tasks)): ?>
                            <div class="p-8 text-center text-gray-400 italic flex flex-col items-center justify-center h-full">
                                <i data-lucide="clipboard-list" class="w-10 h-10 mb-2 opacity-50"></i>
                                <p>No topics assigned by admin yet.</p>
                            </div>
                        <?php else: foreach($tasks as $t): 
                            $icon = match($t['status']) { 'Completed' => 'check-circle', 'Ongoing' => 'loader', default => 'circle' };
                            $color = match($t['status']) { 'Completed' => 'text-green-600', 'Ongoing' => 'text-blue-600', default => 'text-gray-400' };
                            $bg = match($t['status']) { 'Completed' => 'bg-green-50', 'Ongoing' => 'bg-blue-50', default => 'bg-white' };
                        ?>
                        <div class="p-4 flex items-center <?php echo $bg; ?> transition hover:bg-opacity-80">
                            <i data-lucide="<?php echo $icon; ?>" class="w-5 h-5 mr-3 <?php echo $color; ?> shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-700 truncate"><?php echo htmlspecialchars($t['topic']); ?></p>
                                <p class="text-xs text-gray-500 uppercase tracking-wider"><?php echo $t['status']; ?></p>
                            </div>
                            <?php if($t['status'] == 'Completed'): ?>
                                <span class="text-xs font-bold text-green-600 border border-green-200 px-2 py-1 rounded bg-white shrink-0">Done</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PROFILE IMAGE LIGHTBOX -->
    <div id="profile-lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden flex flex-col items-center justify-center opacity-0 transition-opacity duration-300 px-4" onclick="closeProfileLightbox()">
        <!-- Close Button -->
        <button class="absolute top-6 right-6 text-white/70 hover:text-white p-2 rounded-full hover:bg-white/10 transition">
            <i data-lucide="x" class="w-10 h-10"></i>
        </button>

        <!-- Image Container -->
        <div class="relative p-2">
            <img id="lightbox-img" src="" class="max-w-full max-h-[80vh] object-contain shadow-2xl rounded-md border-4 border-slate-800 bg-slate-900" onclick="event.stopPropagation()"> 
            <p class="text-center text-white/60 mt-4 font-mono text-sm uppercase tracking-widest">Profile Photo</p>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        // --- CHART LOGIC ---
        <?php if(count($tasks) > 0): ?>
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Ongoing', 'Pending'],
                datasets: [{
                    data: [<?php echo $stats['Completed']; ?>, <?php echo $stats['Ongoing']; ?>, <?php echo $stats['Pending']; ?>],
                    backgroundColor: ['#22c55e', '#3b82f6', '#cbd5e1'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '70%', 
                plugins: { legend: { display: false }, tooltip: { enabled: true } } 
            }
        });
        <?php endif; ?>

        // --- LIGHTBOX LOGIC ---
        function openProfileLightbox(src) {
            const lightbox = document.getElementById('profile-lightbox');
            const img = document.getElementById('lightbox-img');
            img.src = src;
            lightbox.classList.remove('hidden');
            setTimeout(() => { lightbox.classList.remove('opacity-0'); }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeProfileLightbox() {
            const lightbox = document.getElementById('profile-lightbox');
            lightbox.classList.add('opacity-0');
            setTimeout(() => { lightbox.classList.add('hidden'); }, 300);
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeProfileLightbox();
        });

        // Initialize Icons
        lucide.createIcons();
    </script>
</body>
</html>
<?php
ob_start(); // Fix for header redirection issues
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security Check: Ensure User is Logged In and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../login.php"); 
    exit; 
}

include '../includes/config.php';

$msg = "";
$error = "";

// --- HANDLE MANUAL PROGRESS UPDATE ---
if (isset($_POST['update_progress'])) {
    $p_id = intval($_POST['project_id']);
    $progress = intval($_POST['progress']);
    
    // Validate percentage
    if ($progress < 0) $progress = 0;
    if ($progress > 100) $progress = 100;

    if ($conn->query("UPDATE projects SET progress = $progress WHERE id = $p_id")) {
        $msg = "Project progress updated manually to $progress%!";
    } else {
        $error = "Failed to update progress.";
    }
}

// --- HANDLE PROJECT ASSIGNMENT ---
if (isset($_POST['assign_team'])) {
    $p_id = intval($_POST['project_id']);
    // Use assignment operator ?? to safely handle empty selection
    $emp_ids = $_POST['employees'] ?? []; 

    if ($p_id > 0) {
        // 1. Remove existing assignments for this project
        $conn->query("DELETE FROM project_assignments WHERE project_id = $p_id");

        // 2. Insert new assignments
        if (!empty($emp_ids)) {
            $stmt = $conn->prepare("INSERT INTO project_assignments (project_id, user_id) VALUES (?, ?)");
            foreach ($emp_ids as $u_id) {
                $u_id = intval($u_id);
                $stmt->bind_param("ii", $p_id, $u_id);
                $stmt->execute();
            }
            $stmt->close();
        }
        $msg = "Project team updated successfully!";
    } else {
        $error = "Please select a valid project.";
    }
}

// Fetch Projects with Assignees (ID and Name) for Display
// Using LEFT JOIN to ensure projects show up even if no one is assigned
$proj_sql = "SELECT p.*, 
             GROUP_CONCAT(u.name SEPARATOR ', ') as team_names,
             GROUP_CONCAT(u.id SEPARATOR ',') as team_ids 
             FROM projects p 
             LEFT JOIN project_assignments pa ON p.id = pa.project_id 
             LEFT JOIN users u ON pa.user_id = u.id 
             GROUP BY p.id
             ORDER BY p.id DESC";
$projects = $conn->query($proj_sql);

// Fetch All Employees for the Assignment Form
$emp_res = $conn->query("SELECT id, name, employee_id FROM users WHERE role='employee' ORDER BY name");
$all_employees = [];
if ($emp_res) {
    while($row = $emp_res->fetch_assoc()) {
        $all_employees[] = $row;
    }
}

// Fetch Recent Updates
$updates_sql = "SELECT pu.*, u.name, p.name as proj_name 
                FROM project_updates pu 
                JOIN users u ON pu.user_id = u.id 
                JOIN projects p ON pu.project_id = p.id 
                ORDER BY pu.update_date DESC LIMIT 20";
$updates = $conn->query($updates_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Project Statistics & Assignments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

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
            <a href="projects.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="briefcase" class="w-4 h-4 mr-2"></i> Projects</a>
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
    <div class="flex-1 p-4 md:p-8 overflow-y-auto w-full">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-slate-800">Project Statistics & Assignments</h2>
            
            <!-- Mobile Toggle (Hidden on desktop) -->
            <button class="md:hidden text-slate-800" onclick="document.querySelector('.w-64').classList.toggle('hidden')">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <?php if($msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 border border-green-200"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 border border-red-200"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- 1. JOB / PROJECT ASSIGNMENT SECTION -->
        <div class="bg-white p-6 rounded-xl shadow border-t-4 border-red-500 mb-8">
            <h3 class="font-bold text-lg mb-4 flex items-center text-slate-700">
                <i data-lucide="user-plus" class="w-5 h-5 mr-2 text-red-600"></i> 
                Assign Employees to Project
            </h3>
            
            <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Select Project -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2">Select Project</label>
                    <select name="project_id" required class="w-full p-3 border rounded bg-gray-50 focus:border-red-500 outline-none">
                        <option value="">-- Choose Project --</option>
                        <?php 
                        if ($projects && $projects->num_rows > 0) {
                            $projects->data_seek(0); // Reset pointer
                            while($p = $projects->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-2">Selecting a project will replace its current team.</p>
                </div>

                <!-- Select Employees (Multi-Select) -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 mb-2">Select Employees (Multiple)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 bg-gray-50 p-4 rounded border max-h-48 overflow-y-auto">
                        <?php if(!empty($all_employees)): foreach($all_employees as $emp): ?>
                        <label class="flex items-center space-x-2 p-2 bg-white rounded border border-gray-200 hover:border-red-400 cursor-pointer transition">
                            <input type="checkbox" name="employees[]" value="<?php echo $emp['id']; ?>" class="w-4 h-4 text-red-600 rounded focus:ring-red-500">
                            <div class="overflow-hidden">
                                <div class="text-sm font-bold text-slate-700 truncate"><?php echo htmlspecialchars($emp['name']); ?></div>
                                <div class="text-xs text-gray-400 font-mono truncate"><?php echo htmlspecialchars($emp['employee_id']); ?></div>
                            </div>
                        </label>
                        <?php endforeach; else: ?>
                            <p class="text-gray-400 text-sm col-span-full text-center">No employees found. Add them in Employee Management.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="lg:col-span-3 text-right">
                    <button name="assign_team" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded font-bold shadow-lg transition flex items-center ml-auto">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Assignment
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. PROJECT LIST & STATUS -->
        <h3 class="text-xl font-bold text-slate-800 mb-4">Active Projects Overview</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <?php 
            if ($projects && $projects->num_rows > 0):
                $projects->data_seek(0); // Reset pointer again
                while($p = $projects->fetch_assoc()): 
                    $team_ids = !empty($p['team_ids']) ? explode(',', $p['team_ids']) : [];
                    $team_names = !empty($p['team_names']) ? explode(', ', $p['team_names']) : [];
            ?>
            <div class="bg-white p-6 rounded-xl shadow border-t-4 border-orange-500 relative flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="font-bold">Team:</span> 
                            <?php echo $p['team_names'] ?: '<span class="italic text-red-400">Unassigned</span>'; ?>
                        </p>
                    </div>
                    <span class="bg-slate-100 px-2 py-1 rounded text-xs font-bold uppercase"><?php echo $p['status']; ?></span>
                </div>
                
                <!-- Progress Bar Display -->
                <div class="mt-2 mb-4">
                    <div class="flex justify-between text-xs mb-1"><span>Progress</span><span><?php echo $p['progress']; ?>%</span></div>
                    <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-orange-600 h-2 rounded-full" style="width: <?php echo $p['progress']; ?>%"></div></div>
                </div>

                <!-- Manual Progress Adjustment Form -->
                <form method="POST" class="mb-4 flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200">
                    <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                    <label class="text-xs font-bold text-slate-600 whitespace-nowrap">Set %:</label>
                    <input type="number" name="progress" value="<?php echo $p['progress']; ?>" min="0" max="100" class="w-16 p-1 text-sm border rounded text-center focus:border-orange-500 outline-none">
                    <button name="update_progress" class="text-xs bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded font-bold transition">Update</button>
                </form>

                <!-- MANAGE INDIVIDUAL GRAPHS -->
                <div class="mt-auto pt-3 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-wider">Manage Employee Checklists & Graphs:</p>
                    <?php if(!empty($team_ids) && !empty($team_names)): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            for($i=0; $i<count($team_ids); $i++): 
                                if(empty($team_ids[$i])) continue; 
                                $t_name = isset($team_names[$i]) ? $team_names[$i] : 'Employee';
                            ?>
                                <a href="project_tasks.php?pid=<?php echo $p['id']; ?>&uid=<?php echo $team_ids[$i]; ?>" class="text-xs bg-slate-50 hover:bg-orange-50 text-slate-600 hover:text-orange-700 border border-slate-200 px-3 py-1.5 rounded flex items-center transition">
                                    <i data-lucide="bar-chart-2" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($t_name); ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-xs text-gray-400 italic">Assign employees above to manage their tasks.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-10 text-gray-500">No projects found. Add projects first.</div>
            <?php endif; ?>
        </div>

        <!-- 3. DAILY UPDATES FEED -->
        <h3 class="text-xl font-bold text-slate-800 mb-4">Daily Work Log</h3>
        <div class="bg-white rounded-xl shadow p-6 overflow-hidden">
            <div class="max-h-96 overflow-y-auto pr-2">
                <ul class="space-y-4">
                    <?php 
                    if($updates && $updates->num_rows > 0):
                        while($upd = $updates->fetch_assoc()): ?>
                        <li class="border-b pb-4 last:border-0">
                            <div class="flex justify-between mb-1">
                                <span class="font-bold text-sm text-orange-700 flex items-center">
                                    <i data-lucide="user" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($upd['name']); ?>
                                </span>
                                <span class="text-xs text-gray-400 flex items-center">
                                    <i data-lucide="clock" class="w-3 h-3 mr-1"></i> <?php echo date('M d, H:i', strtotime($upd['update_date'])); ?>
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mb-2 font-bold">Project: <?php echo htmlspecialchars($upd['proj_name']); ?></div>
                            <p class="text-gray-700 bg-slate-50 p-3 rounded text-sm border border-slate-100"><?php echo htmlspecialchars($upd['update_text']); ?></p>
                        </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-400 italic text-center py-4">No daily updates logged yet.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
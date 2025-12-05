<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";

// --- HANDLE CREATE PROMO CODE ---
if (isset($_POST['create_code'])) {
    $code = strtoupper($conn->real_escape_string($_POST['code']));
    $discount = $conn->real_escape_string($_POST['discount']);
    $type = $conn->real_escape_string($_POST['type']);
    $expiry = $conn->real_escape_string($_POST['expiry']);
    $limit = intval($_POST['limit']);

    // Check if code exists
    $check = $conn->query("SELECT id FROM promo_codes WHERE code = '$code'");
    if ($check->num_rows > 0) {
        $error = "Promo code '$code' already exists.";
    } else {
        $sql = "INSERT INTO promo_codes (code, discount, type, expiry_date, usage_limit) 
                VALUES ('$code', '$discount', '$type', '$expiry', $limit)";
        if ($conn->query($sql)) {
            $msg = "Promo Code created successfully!";
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM promo_codes WHERE id=$id");
    header("Location: promocodes.php");
}

// --- HANDLE TOGGLE STATUS ---
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE promo_codes SET status = IF(status='active', 'inactive', 'active') WHERE id=$id");
    header("Location: promocodes.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Promo Codes</title>
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
            <a href="promocodes.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="ticket-percent" class="w-4 h-4 mr-2"></i> Promo Codes</a>
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
        <h2 class="text-3xl font-bold text-slate-800 mb-6">Promo Code Manager</h2>

        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4'>$error</div>"; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- CREATE FORM -->
            <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow h-fit border-t-4 border-orange-500">
                <h3 class="font-bold text-lg mb-4 flex items-center"><i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Create New Offer</h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500">Promo Code</label>
                        <div class="flex gap-2">
                            <input type="text" name="code" id="code_input" placeholder="e.g. SUMMER50" required class="w-full p-2 border rounded font-mono uppercase">
                            <button type="button" onclick="generateCode()" class="bg-gray-200 hover:bg-gray-300 px-3 rounded" title="Generate Random"><i data-lucide="refresh-cw" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500">Discount Value</label>
                        <input type="text" name="discount" placeholder="e.g. 10% OFF or ₹500 Cashback" required class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500">Applicable For</label>
                        <select name="type" class="w-full p-2 border rounded">
                            <option value="all">All Applications</option>
                            <option value="project">Projects Only (Clients)</option>
                            <option value="course">Courses Only (Students)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-bold text-gray-500">Expiry Date</label>
                            <input type="date" name="expiry" required class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500">Usage Limit</label>
                            <input type="number" name="limit" value="100" required class="w-full p-2 border rounded">
                        </div>
                    </div>

                    <button name="create_code" class="w-full bg-orange-600 text-white py-2 rounded font-bold hover:bg-orange-700 transition shadow-lg">Create Promo Code</button>
                </form>
            </div>

            <!-- LIST -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-hidden">
                <div class="p-4 bg-gray-50 font-bold text-slate-700 border-b">Active & Recent Codes</div>
                <table class="w-full text-left">
                    <thead class="bg-white border-b text-xs uppercase text-gray-500">
                        <tr>
                            <th class="p-4">Code</th>
                            <th class="p-4">Discount</th>
                            <th class="p-4">Usage</th>
                            <th class="p-4">Expiry</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <?php 
                        $res = $conn->query("SELECT * FROM promo_codes ORDER BY created_at DESC");
                        while($row = $res->fetch_assoc()):
                            $is_expired = strtotime($row['expiry_date']) < time();
                            $is_full = $row['used_count'] >= $row['usage_limit'];
                            $status_class = ($row['status']=='active' && !$is_expired && !$is_full) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            $status_text = $row['status'];
                            if($is_expired) $status_text = "Expired";
                            if($is_full) $status_text = "Limit Reached";
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-mono font-bold text-orange-700"><?php echo htmlspecialchars($row['code']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($row['discount']); ?></td>
                            <td class="p-4">
                                <span class="font-bold"><?php echo $row['used_count']; ?></span> / <?php echo $row['usage_limit']; ?>
                            </td>
                            <td class="p-4 <?php echo $is_expired ? 'text-red-500' : ''; ?>">
                                <?php echo date('d M Y', strtotime($row['expiry_date'])); ?>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="p-4 flex justify-center gap-2">
                                <a href="?toggle=<?php echo $row['id']; ?>" class="text-blue-500 hover:bg-blue-50 p-1 rounded" title="Toggle Status"><i data-lucide="power" class="w-4 h-4"></i></a>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this code?')" class="text-red-500 hover:bg-red-50 p-1 rounded" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function generateCode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = 'PEDIA';
            for (let i = 0; i < 5; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('code_input').value = result;
        }
        lucide.createIcons();
    </script>
</body>
</html>
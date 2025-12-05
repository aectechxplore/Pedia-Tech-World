<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

// Handle Export
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Date', 'Employee Name', 'Employee ID', 'Status', 'Check In Time'));
    
    $query = "SELECT a.date, u.name, u.employee_id, a.status, a.check_in_time 
              FROM attendance a JOIN users u ON a.user_id = u.id 
              ORDER BY a.date DESC";
    $result = $conn->query($query);
    while($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Fetch for View
$query = "SELECT a.*, u.name, u.employee_id FROM attendance a JOIN users u ON a.user_id = u.id ORDER BY a.date DESC LIMIT 50";
$res = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Attendance Records</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Added Lucide Icons Script -->
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
            <a href="attendance.php" class="flex items-center px-4 py-2 bg-orange-600 rounded text-white"><i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Attendance</a>
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-slate-800">Attendance Records</h2>
            <form method="POST">
                <button name="export" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2 shadow-lg">
                    <i data-lucide="download" class="w-4 h-4"></i> Download CSV Report
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 text-gray-600 font-bold">Date</th>
                        <th class="p-4 text-gray-600 font-bold">Employee</th>
                        <th class="p-4 text-gray-600 font-bold">Time In</th>
                        <th class="p-4 text-gray-600 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4"><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700"><?php echo htmlspecialchars($row['name']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['employee_id']); ?></div>
                        </td>
                        <td class="p-4 text-slate-600"><?php echo date('h:i A', strtotime($row['check_in_time'])); ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800 flex items-center w-fit">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Initialize Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
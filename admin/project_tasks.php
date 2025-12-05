<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../login.php"); exit; }
include '../includes/config.php';

$msg = "";
$error = "";

// Get Project ID and User ID from URL
$p_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$u_id = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

// Fetch Project & User Names for Display
$proj = $conn->query("SELECT name FROM projects WHERE id=$p_id")->fetch_assoc();
$user = $conn->query("SELECT name FROM users WHERE id=$u_id")->fetch_assoc();

if (!$proj || !$user) {
    die("Invalid Project or Employee selection. Please go back to Projects page.");
}

// --- HANDLE ADD TOPIC ---
if (isset($_POST['add_topic'])) {
    $topic = $conn->real_escape_string($_POST['topic']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "INSERT INTO project_checklist (project_id, user_id, topic, status) VALUES ($p_id, $u_id, '$topic', '$status')";
    if ($conn->query($sql)) { $msg = "Topic added successfully!"; } 
    else { $error = "DB Error: " . $conn->error; }
}

// --- HANDLE UPDATE STATUS ---
if (isset($_POST['update_status'])) {
    $task_id = intval($_POST['task_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE project_checklist SET status='$new_status' WHERE id=$task_id");
    $msg = "Status updated!";
}

// --- HANDLE DELETE ---
if (isset($_GET['del'])) {
    $del_id = intval($_GET['del']);
    $conn->query("DELETE FROM project_checklist WHERE id=$del_id");
    header("Location: project_tasks.php?pid=$p_id&uid=$u_id");
    exit;
}

// Fetch Checklist Items
$tasks = $conn->query("SELECT * FROM project_checklist WHERE project_id=$p_id AND user_id=$u_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Project Graph</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex h-screen">
    <div class="flex-1 p-4 md:p-8 overflow-y-auto max-w-5xl mx-auto">
        
        <!-- Header with Back Button -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Graph & Checklist Manager</h2>
                <p class="text-gray-600 mt-1">
                    Project: <span class="font-bold text-orange-700"><?php echo htmlspecialchars($proj['name']); ?></span> | 
                    Employee: <span class="font-bold text-purple-700"><?php echo htmlspecialchars($user['name']); ?></span>
                </p>
            </div>
            <a href="projects.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 flex items-center w-fit transition">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Projects
            </a>
        </div>

        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4 border border-green-200'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4 border border-red-200'>$error</div>"; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- ADD FORM -->
            <div class="md:col-span-1 bg-white p-6 rounded-xl shadow border-t-4 border-orange-500 h-fit">
                <h3 class="font-bold text-lg mb-4 flex items-center text-slate-700">
                    <i data-lucide="plus-circle" class="w-5 h-5 mr-2 text-orange-600"></i> Add Checklist Topic
                </h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Topic / Task Name</label>
                        <input type="text" name="topic" required class="w-full p-2 border rounded focus:ring-2 focus:ring-orange-500 outline-none mt-1" placeholder="e.g. Frontend UI Design">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wide">Initial Status</label>
                        <select name="status" class="w-full p-2 border rounded bg-slate-50 mt-1 focus:outline-none focus:border-orange-500">
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <button name="add_topic" class="w-full bg-orange-600 text-white py-2 rounded font-bold hover:bg-orange-700 shadow-lg transition">Add to Graph</button>
                </form>
            </div>

            <!-- LIST -->
            <div class="md:col-span-2 bg-white rounded-xl shadow overflow-hidden border border-slate-200">
                <div class="p-4 bg-gray-50 border-b font-bold text-gray-700 flex justify-between items-center">
                    <span>Current Checklist Topics</span>
                    <span class="text-xs bg-slate-200 px-2 py-1 rounded"><?php echo $tasks->num_rows; ?> Items</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white border-b text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="p-4">Topic</th>
                                <th class="p-4">Status (Click to Change)</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php while($row = $tasks->fetch_assoc()): 
                                $color = match($row['status']) {
                                    'Completed' => 'bg-green-100 text-green-800 border-green-200',
                                    'Ongoing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    default => 'bg-gray-100 text-gray-800 border-gray-200'
                                };
                            ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-medium text-slate-700"><?php echo htmlspecialchars($row['topic']); ?></td>
                                <td class="p-4">
                                    <form method="POST" class="flex items-center">
                                        <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded text-xs font-bold border cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-300 <?php echo $color; ?>">
                                            <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Ongoing" <?php if($row['status']=='Ongoing') echo 'selected'; ?>>Ongoing</option>
                                            <option value="Completed" <?php if($row['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="?pid=<?php echo $p_id; ?>&uid=<?php echo $u_id; ?>&del=<?php echo $row['id']; ?>" onclick="return confirm('Delete this topic?')" class="text-red-500 hover:bg-red-50 p-2 rounded-full inline-block transition" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($tasks->num_rows == 0): ?>
                    <div class="p-12 text-center text-gray-400 flex flex-col items-center">
                        <i data-lucide="clipboard-list" class="w-12 h-12 mb-2 opacity-20"></i>
                        <p>No topics added yet. The employee's graph will be empty.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
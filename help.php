<?php 
include 'includes/config.php';
include 'includes/header.php'; 

$msg = "";
$error = "";

// --- HANDLE GET PARAMETERS ---
$pre_interest = isset($_GET['interest']) ? htmlspecialchars($_GET['interest']) : '';
$pre_price = isset($_GET['price']) ? htmlspecialchars($_GET['price']) : '';
$pre_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'project';

$details_value = $pre_interest;
if($pre_price && $pre_type == 'project') {
    $details_value .= " (Price Range: " . $pre_price . ")";
}

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $type = $conn->real_escape_string($_POST['type']);
    $education = isset($_POST['education']) ? $conn->real_escape_string($_POST['education']) : '';
    
    // 1. PROMO CODE LOGIC
    $promo_code = strtoupper(trim($conn->real_escape_string($_POST['promo_code'])));
    $valid_promo = true; // Assume valid if empty (optional)

    if (!empty($promo_code)) {
        $p_query = "SELECT * FROM promo_codes WHERE code = '$promo_code'";
        $p_res = $conn->query($p_query);
        
        if ($p_res->num_rows > 0) {
            $p_data = $p_res->fetch_assoc();
            $today = date('Y-m-d');
            
            // Validation Checks
            if ($p_data['status'] != 'active') {
                $error = "Promo code is inactive.";
                $valid_promo = false;
            } elseif ($p_data['expiry_date'] < $today) {
                $error = "Promo code has expired.";
                $valid_promo = false;
            } elseif ($p_data['used_count'] >= $p_data['usage_limit']) {
                $error = "Promo code usage limit reached.";
                $valid_promo = false;
            } elseif ($p_data['type'] != 'all' && $p_data['type'] != $type && !($type == 'project' || $type == 'course')) {
                // Basic check: if code is specific to 'course' but user applies for 'client', warn them.
                // (We group project/course as primary paid services)
                if($type == 'job' || $type == 'achievement') {
                     $error = "This code is not valid for this application type.";
                     $valid_promo = false;
                }
            }
        } else {
            $error = "Invalid Promo Code.";
            $valid_promo = false;
        }
    }

    // 2. FILE UPLOAD HANDLING (Only proceed if promo is valid/empty)
    if ($valid_promo && empty($error)) {
        $upload_ok = true;
        $file_path = "";
        
        if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
            $file_name = $_FILES['document']['name'];
            $file_size = $_FILES['document']['size'];
            $file_tmp = $_FILES['document']['tmp_name'];
            
            $max_size = ($type == 'job') ? 1048576 : 10485760; 
            $target_dir = ($type == 'job') ? "uploads/resumes/" : "uploads/id_proofs/";
            
            if ($file_size > $max_size) {
                $error = "File is too large.";
                $upload_ok = false;
            } else {
                if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
                $new_filename = uniqid() . "_" . basename($file_name);
                if (move_uploaded_file($file_tmp, $target_dir . $new_filename)) {
                    $file_path = $target_dir . $new_filename;
                } else {
                    $error = "Failed to upload file.";
                    $upload_ok = false;
                }
            }
        }

        // 3. INSERT DATA
        if ($upload_ok && !$error) {
            $sql = "INSERT INTO applications (name, email, phone, address, type, education, file_path, promo_code) 
                    VALUES ('$name', '$email', '$phone', '$address', '$type', '$education', '$file_path', '$promo_code')";
            
            if($conn->query($sql)) {
                $msg = "Submitted successfully! We will contact you soon.";
                
                // 4. INCREMENT PROMO USAGE
                if (!empty($promo_code)) {
                    $conn->query("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = '$promo_code'");
                    $msg .= " Promo code applied!";
                }
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
    }
}
?>

<div class="min-h-screen bg-slate-100 py-20 px-4 flex justify-center items-center">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-2xl reveal active">
        <h2 class="text-2xl font-bold text-slate-900 mb-2 text-center">Help & Applications</h2>
        <p class="text-center text-slate-500 mb-6 text-sm">Submit projects, job applications, or share your success story.</p>
        
        <?php if($msg) echo "<div class='bg-green-100 text-green-700 p-4 rounded mb-4 text-center border border-green-200 font-bold'>$msg</div>"; ?>
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-4 rounded mb-4 text-center border border-red-200 font-bold'>$error</div>"; ?>

        <form method="POST" enctype="multipart/form-data" id="appForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                    <input type="text" name="name" required class="w-full p-3 border rounded focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    <input type="email" name="email" required class="w-full p-3 border rounded focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Contact Number</label>
                    <input type="text" name="phone" required class="w-full p-3 border rounded focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Application Type</label>
                    <select name="type" id="appType" onchange="toggleFields()" class="w-full p-3 border rounded focus:ring-2 focus:ring-orange-500 outline-none">
                        <option value="project" <?php if($pre_type == 'project') echo 'selected'; ?>>Start a Project (Client)</option>
                        <option value="course" <?php if($pre_type == 'course') echo 'selected'; ?>>Join a Course (Student)</option>
                        <option value="job" <?php if($pre_type == 'job') echo 'selected'; ?>>Apply for Job (Career)</option>
                        <option value="achievement">Submit Achievement (Student)</option>
                        <option value="client">Submit Testimonial (Client)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-slate-700 mb-2">Full Address / Location</label>
                <textarea name="address" required class="w-full p-3 border rounded focus:ring-2 focus:ring-orange-500 outline-none" rows="2"></textarea>
            </div>

            <!-- PROMO CODE SECTION (Visible for Projects/Courses) -->
            <div id="promoWrapper" class="mb-4 hidden">
                <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center">
                    <i data-lucide="tag" class="w-4 h-4 mr-2 text-orange-600"></i> Promo Code (Optional)
                </label>
                <input type="text" name="promo_code" placeholder="Enter code for discount" class="w-full p-3 border border-dashed border-orange-400 rounded bg-orange-50 focus:ring-2 focus:ring-orange-500 outline-none font-mono uppercase">
            </div>

            <!-- DYNAMIC FIELDS -->
            <div id="educationWrapper" class="mb-4 p-4 bg-slate-50 rounded border border-slate-200">
                <h3 id="eduLabel" class="font-bold text-slate-700 mb-3 flex items-center">Details</h3>
                <div class="mb-3">
                    <label id="eduInputLabel" class="block text-sm font-bold text-slate-700 mb-2">Project / Package Details</label>
                    <input type="text" name="education" id="eduInput" class="w-full p-3 border rounded" value="<?php echo $details_value; ?>">
                </div>
            </div>

            <!-- FILE UPLOAD -->
            <div id="fileWrapper" class="mb-4 p-4 bg-slate-50 rounded border border-slate-200">
                <h3 id="fileLabel" class="font-bold text-slate-700 mb-3 flex items-center"><i data-lucide="shield-check" class="w-4 h-4 mr-2"></i> Verification</h3>
                <div>
                    <label id="fileInputLabel" class="block text-sm font-bold text-slate-700 mb-2">Upload ID Proof (Image, Max 10MB)</label>
                    <input type="file" name="document" id="fileInput" class="w-full bg-white p-2 border rounded">
                </div>
            </div>

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded transition shadow-lg mt-4">Submit</button>
        </form>
    </div>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('appType').value;
        const promoWrapper = document.getElementById('promoWrapper');
        
        const eduLabel = document.getElementById('eduLabel');
        const eduInputLabel = document.getElementById('eduInputLabel');
        const eduInput = document.getElementById('eduInput');
        const fileLabel = document.getElementById('fileLabel');
        const fileInputLabel = document.getElementById('fileInputLabel');
        const fileInput = document.getElementById('fileInput');

        // Show Promo Code only for Project & Course
        if (type === 'project' || type === 'course') {
            promoWrapper.classList.remove('hidden');
        } else {
            promoWrapper.classList.add('hidden');
        }

        // Dynamic Fields Logic
        if (type === 'job') {
            eduLabel.innerHTML = '<i data-lucide="graduation-cap" class="w-4 h-4 mr-2"></i> Job Details';
            eduInputLabel.innerText = 'Educational Qualifications';
            eduInput.placeholder = 'Degree, University, Year of Passing...';
            fileLabel.innerHTML = '<i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Resume Upload';
            fileInputLabel.innerText = 'Upload Resume (PDF/Doc, Max 1MB)';
        } else if (type === 'course') {
            eduLabel.innerHTML = '<i data-lucide="book-open" class="w-4 h-4 mr-2"></i> Course Details';
            eduInputLabel.innerText = 'Interested Course Name';
            fileLabel.innerHTML = '<i data-lucide="shield-check" class="w-4 h-4 mr-2"></i> Student Verification';
            fileInputLabel.innerText = 'Upload ID Proof (Image, Max 10MB)';
        } else if (type === 'achievement') {
            eduLabel.innerHTML = '<i data-lucide="trophy" class="w-4 h-4 mr-2"></i> Achievement Details';
            eduInputLabel.innerText = 'Describe your Achievement';
            eduInput.placeholder = 'What did you win or create?';
            fileLabel.innerHTML = '<i data-lucide="image" class="w-4 h-4 mr-2"></i> Photo Evidence';
            fileInputLabel.innerText = 'Upload Photo of Certificate/Event';
        } else if (type === 'client') {
            eduLabel.innerHTML = '<i data-lucide="message-square" class="w-4 h-4 mr-2"></i> Testimonial';
            eduInputLabel.innerText = 'Your Feedback';
            eduInput.placeholder = 'Share your experience working with us...';
            fileLabel.innerHTML = '<i data-lucide="image" class="w-4 h-4 mr-2"></i> Your Photo/Logo (Optional)';
            fileInputLabel.innerText = 'Upload Photo';
        } else {
            eduLabel.innerHTML = '<i data-lucide="briefcase" class="w-4 h-4 mr-2"></i> Project Requirements';
            eduInputLabel.innerText = 'Project Details / Selected Package';
            eduInput.placeholder = 'Describe your project requirements...';
            fileLabel.innerHTML = '<i data-lucide="shield-check" class="w-4 h-4 mr-2"></i> Client Verification';
            fileInputLabel.innerText = 'Upload ID Proof (Image, Max 10MB)';
        }
        
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    }
    toggleFields();
</script>

<?php include 'includes/footer.php'; ?>
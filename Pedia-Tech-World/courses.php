<?php 
include 'includes/config.php';
include 'includes/header.php'; 

// Fetch courses from database (Managed via Admin > Services & Courses)
$courses_res = $conn->query("SELECT * FROM courses ORDER BY id DESC");
?>

<div class="bg-white py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-extrabold text-center text-slate-900 mb-4 reveal active">Training & Courses</h2>
        <p class="text-center text-slate-600 mb-16 reveal">Upgrade your skills with our industry-standard training programs.</p>

        <div class="grid md:grid-cols-3 gap-8">
            <?php 
            if($courses_res && $courses_res->num_rows > 0):
                while($course = $courses_res->fetch_assoc()): 
            ?>
                <div class="bg-slate-50 p-8 rounded-2xl shadow-lg hover:shadow-2xl transition duration-300 border border-slate-200 reveal flex flex-col h-full group">
                    
                    <!-- Icon Header -->
                    <div class="h-14 w-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-orange-200 transition">
                        <i data-lucide="book-open" class="text-orange-600 w-7 h-7"></i>
                    </div>
                    
                    <!-- Title & Desc -->
                    <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-orange-700 transition"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="text-slate-600 text-sm mb-6 line-clamp-4 flex-1 leading-relaxed"><?php echo htmlspecialchars($course['description']); ?></p>
                    
                    <!-- Details Footer -->
                    <div class="mt-auto">
                        <div class="flex justify-between items-center border-t border-slate-200 pt-4 mb-5">
                            <span class="text-sm font-bold text-slate-500 flex items-center bg-white px-2 py-1 rounded border border-slate-200">
                                <i data-lucide="clock" class="inline w-3 h-3 mr-2"></i> <?php echo htmlspecialchars($course['duration']); ?>
                            </span>
                            <span class="text-lg font-extrabold text-orange-600"><?php echo htmlspecialchars($course['fee']); ?></span>
                        </div>
                        
                        <!-- Link to Help Page with Pre-filled Data -->
                        <a href="help.php?type=course&interest=<?php echo urlencode($course['title']); ?>" class="block w-full text-center bg-slate-900 text-white py-3 rounded-lg font-bold hover:bg-orange-700 transition shadow-md">
                            Enquire Now
                        </a>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <!-- Empty State -->
                <div class="col-span-3 text-center py-16 bg-slate-50 rounded-xl border-2 border-dashed border-slate-300">
                    <i data-lucide="graduation-cap" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-bold text-slate-500">No Courses Available</h3>
                    <p class="text-slate-400">We are updating our curriculum. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Initialize Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

<?php include 'includes/footer.php'; ?>
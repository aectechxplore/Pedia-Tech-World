<?php 
// Include Configuration and Header
include 'includes/config.php';
include 'includes/header.php'; 

// 1. Fetch Ongoing/Live Projects (Internal Status)
$live_projects_sql = "SELECT * FROM projects ORDER BY id DESC";
$live_projects_res = $conn->query($live_projects_sql);

// 2. Fetch Completed Portfolio Items (Visual Gallery)
$portfolio_sql = "SELECT * FROM portfolio ORDER BY created_at DESC";
$portfolio_res = $conn->query($portfolio_sql);
?>

<!-- Page Header -->
<div class="bg-slate-900 py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 reveal active">Our Work & Status</h1>
        <p class="text-xl text-slate-400 max-w-2xl mx-auto reveal">
            Transparent progress tracking for our clients and a showcase of our finest digital solutions.
        </p>
    </div>
</div>

<div class="bg-slate-50 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- SECTION 1: LIVE PROJECT STATUS -->
        <div class="mb-24 reveal">
            <div class="flex items-center gap-3 mb-8">
                <i data-lucide="activity" class="w-8 h-8 text-orange-600"></i>
                <h2 class="text-3xl font-bold text-slate-900">Live Project Status</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php 
                if ($live_projects_res->num_rows > 0):
                    while($proj = $live_projects_res->fetch_assoc()): 
                        // Color coding based on status
                        $statusColor = 'bg-blue-100 text-blue-800';
                        if($proj['status'] == 'Completed') $statusColor = 'bg-green-100 text-green-800';
                        if($proj['status'] == 'Pending') $statusColor = 'bg-yellow-100 text-yellow-800';
                ?>
                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-orange-500 hover:shadow-lg transition">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($proj['name']); ?></h3>
                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase <?php echo $statusColor; ?>">
                            <?php echo htmlspecialchars($proj['status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-2 flex justify-between text-sm text-slate-500">
                        <span>Progress</span>
                        <span class="font-bold text-orange-600"><?php echo $proj['progress']; ?>%</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-orange-600 h-3 rounded-full transition-all duration-1000 ease-out" style="width: <?php echo $proj['progress']; ?>%"></div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <p class="text-gray-500 col-span-2 italic">No live projects currently tracked publicly.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 2: PORTFOLIO GALLERY -->
        <div class="reveal">
            <div class="flex items-center gap-3 mb-8">
                <i data-lucide="layers" class="w-8 h-8 text-purple-600"></i>
                <h2 class="text-3xl font-bold text-slate-900">Portfolio Gallery</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php 
                if ($portfolio_res->num_rows > 0):
                    while($item = $portfolio_res->fetch_assoc()): 
                ?>
                <div class="group relative rounded-xl overflow-hidden shadow-lg cursor-pointer bg-white h-[400px] border border-slate-200">
                    <!-- Image -->
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                    
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-slate-900/90 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 p-8 text-center translate-y-4 group-hover:translate-y-0">
                        <h3 class="text-white font-bold text-2xl mb-3"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <div class="w-12 h-1 bg-orange-500 mb-4 rounded"></div>
                        
                        <p class="text-slate-300 text-sm mb-6 line-clamp-4 leading-relaxed">
                            <?php echo htmlspecialchars($item['description']); ?>
                        </p>
                        
                        <div class="flex items-center gap-2 text-orange-400 text-xs font-bold uppercase tracking-widest mb-6">
                            <i data-lucide="clock" class="w-4 h-4"></i> <?php echo htmlspecialchars($item['duration']); ?>
                        </div>

                        <?php if(!empty($item['project_url'])): ?>
                            <a href="<?php echo htmlspecialchars($item['project_url']); ?>" target="_blank" class="inline-flex items-center bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-full font-bold text-sm transition shadow-lg shadow-orange-500/30">
                                View Project <i data-lucide="external-link" class="ml-2 w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="col-span-3 text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-300">
                        <i data-lucide="image" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
                        <h3 class="text-lg font-bold text-slate-700">Gallery Coming Soon</h3>
                        <p class="text-slate-500">We are curating our best work to show you.</p>
                    </div>
                <?php endif; ?>
            </div>
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
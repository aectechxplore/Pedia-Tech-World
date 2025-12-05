<?php 
include 'includes/config.php'; 
include 'includes/header.php'; 

// Helper function to create the link for the "Get Started" button
function getLink($title, $price) {
    return "help.php?type=project&interest=" . urlencode($title) . "&price=" . urlencode($price);
}

// Fetch all services ordered by category
$sql = "SELECT * FROM services ORDER BY category, id";
$result = $conn->query($sql);

// Group services by category
$services = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $services[$row['category']][] = $row;
    }
}
?>

<div class="bg-slate-50 py-16 min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-extrabold text-center text-slate-900 mb-4 reveal active">Services & Pricing</h2>
        <p class="text-center text-slate-600 mb-16 reveal">Tailored solutions for every business need.</p>

        <?php if(empty($services)): ?>
            <div class="text-center text-gray-500 py-10 border-2 border-dashed border-gray-300 rounded-xl bg-white">
                <i data-lucide="package-open" class="w-12 h-12 mx-auto mb-4 text-gray-400"></i>
                <p class="text-lg">No service packages available yet. Check back soon!</p>
            </div>
        <?php endif; ?>

        <?php foreach($services as $category => $items): ?>
        <div class="mb-16 reveal">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-8 bg-orange-500 rounded-full"></div>
                <h3 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($category); ?></h3>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($items as $item): 
                    // Decode features JSON or handle comma-separated string fallback
                    $features = json_decode($item['features'], true);
                    if(!is_array($features)) {
                        $features = array_map('trim', explode(',', $item['features']));
                    }
                ?>
                <div class="bg-white p-8 rounded-2xl shadow-xl border-t-4 border-orange-500 hover:-translate-y-2 transition duration-300 flex flex-col h-full group">
                    <h4 class="text-xl font-bold text-slate-800 group-hover:text-orange-600 transition"><?php echo htmlspecialchars($item['title']); ?></h4>
                    <div class="text-3xl font-extrabold text-orange-600 mt-4"><?php echo htmlspecialchars($item['price']); ?></div>
                    <div class="text-sm text-slate-500 mt-1 mb-6 font-medium bg-slate-100 inline-block px-2 py-1 rounded w-fit"><?php echo htmlspecialchars($item['duration']); ?></div>
                    
                    <ul class="mt-2 space-y-3 text-sm text-slate-600 mb-8 flex-1">
                        <?php foreach($features as $feat): ?>
                        <?php if(!empty(trim($feat))): ?>
                        <li class="flex items-start">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2 mt-0.5 shrink-0"></i> 
                            <span><?php echo htmlspecialchars(trim($feat)); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    
                    <a href="<?php echo getLink($item['title'], $item['price']); ?>" class="block w-full text-center bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-lg transition shadow-md hover:shadow-lg transform active:scale-95">
                        Get Started
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
    // Initialize Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

<?php include 'includes/footer.php'; ?>
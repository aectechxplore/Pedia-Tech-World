<?php include 'includes/config.php'; include 'includes/header.php'; 

// --- 1. FETCH HERO BANNERS ---
$banners_res = $conn->query("SELECT image_path FROM banners ORDER BY created_at DESC");
$banner_images = [];
if ($banners_res && $banners_res->num_rows > 0) {
    while($row = $banners_res->fetch_assoc()) {
        $clean_path = str_replace('../', '', $row['image_path']);
        $banner_images[] = $clean_path;
    }
} else {
    $banner_images[] = "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80";
}
$banners_json = json_encode($banner_images);

// --- 2. FETCH EXCLUSIVE OFFERS ---
$offers_res = $conn->query("SELECT * FROM offers ORDER BY created_at DESC");
$offers = [];
if ($offers_res && $offers_res->num_rows > 0) {
    while($off = $offers_res->fetch_assoc()) {
        $off['image_path'] = str_replace('../', '', $off['image_path']);
        $offers[] = $off;
    }
}
$offers_json = htmlspecialchars(json_encode($offers), ENT_QUOTES, 'UTF-8');

// --- 3. FETCH FLASH SALE DATA ---
$fs_data = $conn->query("SELECT * FROM flash_sale WHERE id=1")->fetch_assoc();
$flash_expiry = $fs_data['expiry_date'] ?? '';

// --- 4. DETERMINE LAYOUT LOGIC ---
$has_offers = count($offers) > 0;
$has_timer = false;

if (!empty($flash_expiry)) {
    $expiry_time = new DateTime($flash_expiry);
    $current_time = new DateTime();
    if ($expiry_time > $current_time) {
        $has_timer = true;
    }
}
?>

<!-- HERO SECTION (Slideshow) -->
<div class="relative bg-slate-900 py-32 px-4 overflow-hidden min-h-[600px] flex items-center">
    
    <!-- Background Slideshow -->
    <div id="hero-slideshow" class="absolute inset-0 z-0">
        <!-- JS injects images here -->
    </div>
    
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-slate-900/70 z-10"></div>

    <!-- Content -->
    <div class="relative z-20 max-w-7xl mx-auto text-center md:text-left reveal active w-full">
        <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 leading-tight drop-shadow-lg">
            Empowering Business with <br>
            <span class="text-orange-400">Digital Intelligence</span>
        </h1>
        <p class="text-xl text-slate-200 max-w-2xl mb-10 drop-shadow-md">
            From custom websites to complex mobile apps and stunning multimedia. Pedia Tech World is your partner in digital transformation.
        </p>
        <div class="flex gap-4 justify-center md:justify-start">
            <a href="services.php" class="bg-orange-500 hover:bg-red-600 text-white px-8 py-4 rounded-lg font-bold text-lg transition shadow-xl hover:shadow-orange-500/30 border-2 border-transparent">Our Pricing</a>
            <a href="about.php" class="border-2 border-orange-400 text-orange-400 hover:bg-red-400/20 px-8 py-4 rounded-lg font-bold text-lg transition">Learn More</a>
        </div>
    </div>
</div>

<!-- SECTION: EXCLUSIVE OFFERS & FLASH SALE (Adaptive Layout) -->
<?php if($has_offers || $has_timer): ?>
<div class="py-16 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Header -->
        <h2 class="text-3xl font-bold text-slate-800 mb-8 flex items-center justify-center md:justify-start">
            <i data-lucide="zap" class="text-yellow-500 w-8 h-8 mr-3 fill-current"></i> Exclusive Deals
        </h2>
        
        <div class="<?php 
            if($has_offers && $has_timer) echo 'grid grid-cols-1 lg:grid-cols-3 gap-8'; 
            else echo 'flex justify-center'; 
        ?>">
            
            <!-- 1. OFFERS SLIDESHOW -->
            <?php if($has_offers): ?>
            <div class="relative rounded-2xl overflow-hidden shadow-2xl group border border-slate-200 bg-slate-900 <?php echo $has_timer ? 'lg:col-span-2 h-[400px]' : 'w-full max-w-5xl h-[450px]'; ?>">
                <div id="offer-container" class="w-full h-full relative bg-slate-800">
                    <!-- JS injects images here -->
                </div>
                
                <!-- Overlay -->
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 via-black/60 to-transparent p-8 pt-24 text-white z-20">
                    <div id="offer-desc" class="text-2xl md:text-3xl font-bold drop-shadow-md transition-opacity duration-500"></div>
                    <a href="help.php" class="mt-4 inline-block bg-orange-600 text-white text-sm font-bold px-6 py-2 rounded-full hover:bg-orange-500 transition shadow-lg">View Details</a>
                </div>

                <!-- Controls -->
                <button onclick="prevOffer()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/60 text-white p-3 rounded-full z-30 opacity-0 group-hover:opacity-100 transition"><i data-lucide="chevron-left"></i></button>
                <button onclick="nextOffer()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/60 text-white p-3 rounded-full z-30 opacity-0 group-hover:opacity-100 transition"><i data-lucide="chevron-right"></i></button>
            </div>
            <?php endif; ?>

            <!-- 2. FLASH SALE TIMER -->
            <?php if($has_timer): ?>
            <div id="flash-sale-card" class="<?php echo $has_offers ? 'lg:col-span-1 h-[400px]' : 'w-full max-w-xl h-auto py-12'; ?> bg-gradient-to-br from-red-600 to-pink-700 rounded-2xl shadow-2xl p-8 text-white flex flex-col justify-center items-center text-center relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                
                <div class="relative z-10 w-full">
                    <div class="bg-white/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                        <i data-lucide="clock" class="w-8 h-8 text-white"></i>
                    </div>
                    
                    <h3 class="text-2xl font-bold mb-2 uppercase tracking-wider">Flash Sale</h3>
                    <p class="text-white/90 mb-6 font-medium text-sm"><?php echo htmlspecialchars($fs_data['description'] ?? 'Limited Time Offer'); ?></p>
                    
                    <!-- Timer Grid -->
                    <div class="grid grid-cols-4 gap-2 mb-8" id="timer-grid">
                        <div class="bg-white/20 rounded p-2 backdrop-blur-sm">
                            <span class="block text-2xl font-bold font-mono" id="d">00</span>
                            <span class="text-[10px] uppercase opacity-70">Days</span>
                        </div>
                        <div class="bg-white/20 rounded p-2 backdrop-blur-sm">
                            <span class="block text-2xl font-bold font-mono" id="h">00</span>
                            <span class="text-[10px] uppercase opacity-70">Hrs</span>
                        </div>
                        <div class="bg-white/20 rounded p-2 backdrop-blur-sm">
                            <span class="block text-2xl font-bold font-mono" id="m">00</span>
                            <span class="text-[10px] uppercase opacity-70">Min</span>
                        </div>
                        <div class="bg-white/20 rounded p-2 backdrop-blur-sm">
                            <span class="block text-2xl font-bold font-mono" id="s">00</span>
                            <span class="text-[10px] uppercase opacity-70">Sec</span>
                        </div>
                    </div>
                    
                    <a href="help.php" class="inline-block w-full bg-white text-red-600 font-bold py-3 rounded-lg shadow-lg hover:bg-gray-100 transition uppercase tracking-wide text-sm">Grab Deal Now</a>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- SECTION 1: OUR LATEST PROJECTS -->
<div class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-slate-800 reveal">Our Latest Projects</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php 
            $port_res = $conn->query("SELECT * FROM portfolio WHERE category='project' ORDER BY created_at DESC LIMIT 6");
            
            if($port_res && $port_res->num_rows > 0):
                while($work = $port_res->fetch_assoc()):
                    // Logic: Cover > Gallery[0] > Fallback
                    $thumb = !empty($work['cover_image']) ? $work['cover_image'] : '';
                    $gallery = json_decode($work['image_path'], true);
                    if (!is_array($gallery)) $gallery = [];
                    
                    if (empty($thumb)) {
                        $thumb = (count($gallery) > 0) ? $gallery[0] : $work['image_path'];
                    }

                    // Prepare Lightbox Data
                    $lightbox_images = $gallery;
                    if (!empty($work['cover_image']) && !in_array($work['cover_image'], $lightbox_images)) {
                        array_unshift($lightbox_images, $work['cover_image']);
                    }
                    $gallery_json = htmlspecialchars(json_encode($lightbox_images), ENT_QUOTES, 'UTF-8');
            ?>
                <div class="group relative rounded-xl overflow-hidden shadow-lg cursor-pointer reveal border border-slate-100 h-[450px] flex flex-col">
                    
                    <!-- Main Image Click -->
                    <div class="h-64 overflow-hidden relative bg-slate-100" onclick='openLightbox(<?php echo $gallery_json; ?>, 0)'>
                        <?php if($thumb): ?>
                            <img src="<?php echo str_replace('../', '', $thumb); ?>" alt="<?php echo htmlspecialchars($work['title']); ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <i data-lucide="maximize-2" class="text-white w-10 h-10 drop-shadow-lg"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-400"><i data-lucide="image-off"></i></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="font-bold text-xl text-slate-800 mb-2"><?php echo htmlspecialchars($work['title']); ?></h3>
                        <p class="text-orange-600 text-xs font-bold mb-3 uppercase tracking-wider"><?php echo htmlspecialchars($work['duration']); ?></p>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2 flex-1"><?php echo htmlspecialchars($work['description']); ?></p>
                        <div class="mt-auto">
                            <a href="help.php" class="text-red-600 text-sm font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="mail" class="w-3 h-3"></i> Enquire
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="col-span-3 text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                    <p class="text-gray-500 text-lg">Portfolio updating soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SECTION 2: HAPPY CLIENTS -->
<div class="py-20 bg-slate-50 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-slate-800 reveal">Our Happy Clients</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <?php 
            $res = $conn->query("SELECT * FROM portfolio WHERE category='client' ORDER BY created_at DESC LIMIT 8");
            while($client = $res->fetch_assoc()):
                $c_gallery = json_decode($client['image_path'], true) ?? [];
                $c_lightbox = $c_gallery;
                if (!empty($client['cover_image']) && !in_array($client['cover_image'], $c_lightbox)) {
                    array_unshift($c_lightbox, $client['cover_image']);
                }
                $c_json = htmlspecialchars(json_encode($c_lightbox), ENT_QUOTES, 'UTF-8');
                $c_thumb = !empty($client['cover_image']) ? $client['cover_image'] : ($c_gallery[0] ?? '');
            ?>
                <div class="bg-white p-6 rounded-xl shadow-md text-center reveal hover:-translate-y-1 transition duration-300 cursor-pointer group" onclick='openLightbox(<?php echo $c_json; ?>, 0)'>
                    <div class="relative w-20 h-20 mx-auto mb-4">
                        <?php if($c_thumb): ?>
                            <img src="<?php echo str_replace('../', '', $c_thumb); ?>" class="w-full h-full rounded-full object-cover border-4 border-orange-50 group-hover:border-orange-200 transition">
                        <?php else: ?>
                            <div class="w-full h-full rounded-full bg-slate-200 flex items-center justify-center text-slate-400"><i data-lucide="user"></i></div>
                        <?php endif; ?>
                    </div>
                    <h4 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($client['title']); ?></h4>
                    <p class="text-xs text-orange-600 font-bold uppercase mb-2"><?php echo htmlspecialchars($client['duration']); ?></p>
                    <p class="text-sm text-slate-500 italic">"<?php echo htmlspecialchars($client['description']); ?>"</p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- SECTION 3: STUDENT ACHIEVEMENTS -->
<div class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4 text-slate-800 reveal">Student Achievements</h2>
        <p class="text-center text-slate-500 mb-12 reveal">Proud moments from our learning community.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            $res = $conn->query("SELECT * FROM portfolio WHERE category='achievement' ORDER BY created_at DESC LIMIT 6");
            while($ach = $res->fetch_assoc()):
                $a_gallery = json_decode($ach['image_path'], true) ?? [];
                $a_lightbox = $a_gallery;
                if (!empty($ach['cover_image']) && !in_array($ach['cover_image'], $a_lightbox)) {
                    array_unshift($a_lightbox, $ach['cover_image']);
                }
                $a_json = htmlspecialchars(json_encode($a_lightbox), ENT_QUOTES, 'UTF-8');
                $a_thumb = !empty($ach['cover_image']) ? $ach['cover_image'] : ($a_gallery[0] ?? '');
            ?>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100 reveal flex flex-col group cursor-pointer" onclick='openLightbox(<?php echo $a_json; ?>, 0)'>
                    <div class="h-48 overflow-hidden relative">
                        <?php if($a_thumb): ?>
                            <img src="<?php echo str_replace('../', '', $a_thumb); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <i data-lucide="maximize" class="text-white w-8 h-8 drop-shadow-md"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400"><i data-lucide="image-off"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($ach['title']); ?></h3>
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded flex items-center"><i data-lucide="trophy" class="inline w-3 h-3 mr-1"></i> Winner</span>
                        </div>
                        <p class="text-sm text-slate-600 mb-4"><?php echo htmlspecialchars($ach['description']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- LIGHTBOX OVERLAY -->
<div id="lightbox" class="fixed inset-0 z-[100] bg-black/95 hidden flex flex-col items-center justify-center opacity-0 transition-opacity duration-300 px-4">
    <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white/70 hover:text-white p-2 z-50"><i data-lucide="x" class="w-10 h-10"></i></button>
    <div class="relative w-full max-w-6xl h-[85vh] flex items-center justify-center">
        <button onclick="prevImage(event)" class="absolute left-0 md:left-8 text-white/50 hover:text-white p-4 z-50"><i data-lucide="chevron-left" class="w-12 h-12"></i></button>
        <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain shadow-2xl rounded-sm">
        <button onclick="nextImage(event)" class="absolute right-0 md:right-8 text-white/50 hover:text-white p-4 z-50"><i data-lucide="chevron-right" class="w-12 h-12"></i></button>
    </div>
</div>

<script>
    const baseUrl = "<?php echo base_url(); ?>";

    // --- 1. HERO SLIDESHOW ---
    const banners = <?php echo $banners_json; ?>;
    const heroContainer = document.getElementById('hero-slideshow');
    let heroIdx = 0;

    if (banners.length > 0) {
        banners.forEach((src, i) => {
            const img = document.createElement('img');
            img.src = src.startsWith('http') ? src : baseUrl + src;
            img.className = `absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out ${i===0?'opacity-100':'opacity-0'}`;
            img.id = `hero-${i}`;
            heroContainer.appendChild(img);
        });
        setInterval(() => {
            const nextSlide = (heroIdx + 1) % banners.length;
            document.getElementById(`hero-${heroIdx}`).classList.replace('opacity-100', 'opacity-0');
            document.getElementById(`hero-${nextSlide}`).classList.replace('opacity-0', 'opacity-100');
            heroIdx = nextSlide;
        }, 5000);
    }

    // --- 2. OFFERS SLIDESHOW & TIMER ---
    const offers = <?php echo $offers_json; ?>;
    const offerContainer = document.getElementById('offer-container');
    const offerDesc = document.getElementById('offer-desc');
    const flashCard = document.getElementById('flash-sale-card');
    
    // Flash Sale Expiry from PHP
    const flashExpiry = "<?php echo $flash_expiry; ?>";

    let offerIdx = 0;
    let offerInterval;

    if (offerContainer && offers.length > 0) {
        // Inject Images
        offers.forEach((off, i) => {
            const img = document.createElement('img');
            // FIX: Use Base URL + image path to resolve relative path correctly
            img.src = baseUrl + off.image_path; 
            
            // Error Handling
            img.onerror = function() { this.style.display = 'none'; console.error('Offer image failed:', this.src); };
            
            img.className = `absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-in-out ${i===0?'opacity-100':'opacity-0'}`;
            img.id = `offer-${i}`;
            
            offerContainer.appendChild(img);
        });
        
        updateOfferDesc(0);
        startOfferLoop();
    }

    function updateOfferDesc(idx) {
        if(offerDesc) {
            offerDesc.style.opacity = 0;
            setTimeout(() => {
                offerDesc.innerText = offers[idx].description;
                offerDesc.style.opacity = 1;
            }, 300);
        }
    }

    // Flash Sale Timer Logic
    if (flashExpiry && flashCard) {
        const endDate = new Date(flashExpiry).getTime();
        const now = new Date().getTime();
        
        if (endDate > now) {
            // Timer is valid, rely on HTML layout for visibility
            const timerInterval = setInterval(() => {
                const current = new Date().getTime();
                const dist = endDate - current;
                
                if (dist < 0) {
                    clearInterval(timerInterval);
                    flashCard.innerHTML = '<div class="text-center"><h3 class="text-2xl font-bold">EXPIRED</h3><p class="text-sm opacity-80">Stay tuned!</p></div>';
                    return;
                }

                document.getElementById('d').innerText = Math.floor(dist / (1000 * 60 * 60 * 24));
                document.getElementById('h').innerText = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                document.getElementById('m').innerText = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                document.getElementById('s').innerText = Math.floor((dist % (1000 * 60)) / 1000);
            }, 1000);
        }
    }

    function showOffer(idx) {
        document.getElementById(`offer-${offerIdx}`).classList.replace('opacity-100', 'opacity-0');
        offerIdx = idx;
        document.getElementById(`offer-${offerIdx}`).classList.replace('opacity-0', 'opacity-100');
        updateOfferDesc(offerIdx);
    }

    function nextOffer() { clearInterval(offerInterval); showOffer((offerIdx + 1) % offers.length); startOfferLoop(); }
    function prevOffer() { clearInterval(offerInterval); showOffer((offerIdx - 1 + offers.length) % offers.length); startOfferLoop(); }
    function startOfferLoop() { offerInterval = setInterval(() => { showOffer((offerIdx + 1) % offers.length); }, 6000); }

    // --- 3. LIGHTBOX LOGIC ---
    let currentGallery = [];
    let currentIndex = 0;
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');

    function openLightbox(images, index) {
        if (!images || images.length === 0) return;
        if(window.event) window.event.stopPropagation();
        currentGallery = images;
        currentIndex = index;
        let src = currentGallery[currentIndex];
        lightboxImg.src = src.startsWith('http') ? src : baseUrl + src;
        lightbox.classList.remove('hidden');
        setTimeout(() => { lightbox.classList.remove('opacity-0'); }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if(window.event) window.event.stopPropagation();
        lightbox.classList.add('opacity-0');
        setTimeout(() => { lightbox.classList.add('hidden'); }, 300);
        document.body.style.overflow = 'auto';
    }

    function nextImage(e) {
        if(e) e.stopPropagation();
        currentIndex = (currentIndex + 1) % currentGallery.length;
        let src = currentGallery[currentIndex];
        lightboxImg.src = src.startsWith('http') ? src : baseUrl + src;
    }

    function prevImage(e) {
        if(e) e.stopPropagation();
        currentIndex = (currentIndex - 1 + currentGallery.length) % currentGallery.length;
        let src = currentGallery[currentIndex];
        lightboxImg.src = src.startsWith('http') ? src : baseUrl + src;
    }

    document.addEventListener('keydown', function(e) {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
    });
    
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
</script>

<?php include 'includes/footer.php'; ?>
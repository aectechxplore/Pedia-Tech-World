<footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 mt-auto">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Main Footer Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
            
            <!-- 1. Brand & Socials -->
            <div>
                <a href="index.php" class="flex items-center gap-2 mb-4">
                    <!-- If logo exists, use it, else text -->
                    <img src="<?php echo base_url('uploads/logo.png'); ?>" alt="Logo" class="h-8 w-auto rounded bg-white/10 p-0.5">
                    <span class="font-bold text-xl tracking-wider">PEDIA<span class="text-red-400">TECH</span><span class="text-red-800">WORLD</span></span>                </a>
                <p class="text-sm leading-relaxed mb-6">
                    Empowering businesses with high-quality, innovative, and affordable digital solutions. 
                    Your partner in digital transformation.
                </p>
                
                <!-- Social Media Icons -->
                <div class="flex gap-4">
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white transition" title="LinkedIn">
                        <i data-lucide="linkedin" class="w-4 h-4"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-pink-600 hover:text-white transition" title="Instagram">
                        <i data-lucide="instagram" class="w-4 h-4"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-red-600 hover:text-white transition" title="YouTube">
                        <i data-lucide="youtube" class="w-4 h-4"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-blue-500 hover:text-white transition" title="Facebook">
                        <i data-lucide="facebook" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- 2. Quick Links -->
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php" class="hover:text-red-400 transition flex items-center"><i data-lucide="chevron-right" class="w-3 h-3 mr-1"></i> Home</a></li>
                    <li><a href="about.php" class="hover:text-red-400 transition flex items-center"><i data-lucide="chevron-right" class="w-3 h-3 mr-1"></i> About Us</a></li>
                    <li><a href="services.php" class="hover:text-red-400 transition flex items-center"><i data-lucide="chevron-right" class="w-3 h-3 mr-1"></i> Services</a></li>
                    <li><a href="courses.php" class="hover:text-red-400 transition flex items-center"><i data-lucide="chevron-right" class="w-3 h-3 mr-1"></i> Training</a></li>
                    <li><a href="help.php" class="hover:text-red-400 transition flex items-center"><i data-lucide="chevron-right" class="w-3 h-3 mr-1"></i> Contact / Apply</a></li>
                    <li><a href="login.php" class="hover:text-red-400 transition flex items-center"><i data-lucide="chevron-right" class="w-3 h-3 mr-1"></i> Admin Login</a></li>
                </ul>
            </div>

            <!-- 3. Contact Info -->
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Contact Us</h4>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-start">
                        <i data-lucide="mail" class="w-5 h-5 mr-3 text-red-500 shrink-0"></i>
                        <div class="flex flex-col">
                            <a href="mailto:contact@pediatech.com" class="hover:text-white transition">contact@pediatech.com</a>
                            <span class="text-xs text-slate-500">Reply within 24 hours</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="phone" class="w-5 h-5 mr-3 text-red-500 shrink-0"></i>
                        <div class="flex flex-col">
                            <a href="tel:+919876543210" class="hover:text-white transition">+91 98765 43210</a>
                            <span class="text-xs text-slate-500">Mon-Fri, 9am - 6pm</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="map-pin" class="w-5 h-5 mr-3 text-red-500 shrink-0"></i>
                        <span>Salem, Tamil Nadu, India<br><span class="text-xs text-slate-500">Remote-First Company</span></span>
                    </li>
                </ul>
            </div>

            <!-- 4. Location Map -->
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Our Location</h4>
                <div class="rounded-lg overflow-hidden h-48 bg-slate-800 border border-slate-700 shadow-lg relative group cursor-pointer" onclick="openMapLightbox()">
                    <!-- Overlay to block direct interaction and trigger lightbox -->
                    <div class="absolute inset-0 z-10 bg-black/10 group-hover:bg-black/0 transition flex items-center justify-center">
                        <span class="bg-slate-900/80 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition transform scale-95 group-hover:scale-100">Click to Enlarge</span>
                    </div>
                    <!-- Google Map Embed with Pin -->
                    <iframe 
                        src="https://maps.google.com/maps?q=11.670080,78.016156&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        class="grayscale group-hover:grayscale-0 transition duration-500"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-slate-500">© <?php echo date('Y'); ?> Pedia Tech World. All rights reserved.</p>
            <div class="flex gap-6 text-xs text-slate-500">
                <a href="privacy_policy.php" class="hover:text-white transition">Privacy Policy</a>
                <a href="terms.php" class="hover:text-white transition">Terms of Service</a>
                <a href="cookies.php" class="hover:text-white transition">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- MAP LIGHTBOX OVERLAY -->
<div id="map-lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden flex flex-col items-center justify-center opacity-0 transition-opacity duration-300 px-4">
    <div class="w-full max-w-5xl h-[80vh] bg-slate-900 rounded-xl overflow-hidden relative flex flex-col shadow-2xl border border-slate-700">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-slate-700 bg-slate-800">
            <h3 class="text-white font-bold flex items-center"><i data-lucide="map-pin" class="w-5 h-5 mr-2 text-red-400"></i> Our Location</h3>
            <button onclick="closeMapLightbox()" class="text-slate-400 hover:text-white transition p-1 rounded hover:bg-slate-700">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Map Frame -->
        <div class="flex-1 relative">
            <iframe 
                src="https://maps.google.com/maps?q=11.670080,78.016156&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <!-- Footer with External Link -->
        <div class="p-4 bg-slate-800 border-t border-slate-700 text-right">
            <a href="https://www.google.com/maps/search/?api=1&query=11.670080,78.016156" target="_blank" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded transition">
                <i data-lucide="external-link" class="w-4 h-4 mr-2"></i> Open in Google Maps
            </a>
        </div>
    </div>
</div>

<script>
    // Initialize Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Scroll Reveal Animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('active');
        });
    });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // --- MAP LIGHTBOX LOGIC ---
    function openMapLightbox() {
        const lightbox = document.getElementById('map-lightbox');
        lightbox.classList.remove('hidden');
        setTimeout(() => {
            lightbox.classList.remove('opacity-0');
        }, 10);
        document.body.style.overflow = 'hidden'; // Prevent scroll
    }

    function closeMapLightbox() {
        const lightbox = document.getElementById('map-lightbox');
        lightbox.classList.add('opacity-0');
        setTimeout(() => {
            lightbox.classList.add('hidden');
        }, 300);
        document.body.style.overflow = 'auto'; // Restore scroll
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMapLightbox();
    });
</script>
</body>
</html>
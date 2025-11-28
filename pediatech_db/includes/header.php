<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Helper to handle paths (Admin vs User)
$root = (basename(dirname($_SERVER['PHP_SELF'])) == 'admin' || basename(dirname($_SERVER['PHP_SELF'])) == 'employee') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedia Tech World</title>
    
    <!-- FAVICON -->
    <link rel="icon" href="<?php echo $root; ?>uploads/logo.png" type="image/jpeg">

    <!-- TAILWIND CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        html { scroll-behavior: smooth; }
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }

         /* --- WATER FILLING ANIMATION CSS --- */
        .loader-container {
            position: fixed;
            inset: 0;
            background: #0f172a; /* Slate-900 background */
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease, visibility 0.5s;
        }
        
        /* Circular Jar */
        .water-jar {
            width: 150px;
            height: 150px;
            border: 4px solid #ee2270ff; /* red-400 border */
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(34, 211, 238, 0.2);
            background: rgba(255,255,255,0.05);
        }

        /* The Liquid */
        .water {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 0%;
            background: #f7760cc7; /* red-600 liquid */
            animation: fillUp 2.5s ease-in-out forwards; /* Fills in 2.5 seconds */
        }

        /* Wave Effect */
        .water::before, .water::after {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -150%;
            left: -50%;
            background: rgba(255, 115, 73, 0.2);
            border-radius: 40%;
            animation: wave 5s linear infinite;
        }
        .water::after {
            background: rgba(255, 255, 255, 0.1);
            top: -160%;
            left: -60%;
            animation: wave 6s linear infinite reverse;
        }

        /* Loading Text */
        .loader-text {
            position: absolute;
            z-index: 10;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            letter-spacing: 3px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        /* Keyframes */
        @keyframes wave {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes fillUp {
            0% { height: 0%; }
            25% { height: 30%; }
            50% { height: 60%; }
            75% { height: 85%; }
            100% { height: 100%; }
        }
        
        /* Hide Class */
        .loader-hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans flex flex-col min-h-screen">

<!-- WATER LOADER ELEMENT -->
<div id="page-loader" class="loader-container">
    <div class="water-jar flex items-center justify-center">
        <div class="water"></div>
        <!-- REPLACED TEXT WITH LOGO IMAGE -->
        <img src="<?php echo $root; ?>uploads/logo.png" alt="Loading..." class="loader-logo">
    </div>
</div>

<!-- 
<nav class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-700 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?php echo $root; ?>index.php" class="flex items-center gap-2">
                <a href="<?php echo $root; ?>index.php" class="flex items-center gap-2">
                HEADER LOGO
                <img src="<?php echo $root; ?>uploads/logo.png" alt="Pedia Tech World" class="h-30 w-20">
            </a>
                <span class="font-bold text-xl tracking-wider">PEDIA<span class="text-red-400">TECH</span><span class="text-red-800">WORLD</span></span>
            </a>
            <div class="hidden md:flex space-x-6 items-center text-sm font-medium">
                <a href="<?php echo $root; ?>index.php" class="hover:text-red-400 transition-colors">Home</a>
                <a href="<?php echo $root; ?>about.php" class="hover:text-red-400 transition-colors">About</a>
                <a href="<?php echo $root; ?>services.php" class="hover:text-red-400 transition-colors">Services</a>
                <a href="<?php echo $root; ?>projects.php" class="hover:text-red-400 transition-colors">Projects</a>
                <a href="<?php echo $root; ?>courses.php" class="hover:text-red-400 transition-colors">Courses</a>
                <a href="<?php echo $root; ?>help.php" class="hover:text-red-400 transition-colors">Help</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <a href="<?php echo $root; ?>admin/dashboard.php" class="bg-red-600 px-4 py-2 rounded-md flex items-center gap-2">Admin Panel</a>
                    <?php else: ?>
                        <a href="<?php echo $root; ?>employee/dashboard.php" class="bg-green-600 px-4 py-2 rounded-md flex items-center gap-2">My Dashboard</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo $root; ?>login.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md shadow-lg shadow-red-500/20">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
-->

<nav class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-700 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?php echo $root; ?>index.php" class="flex items-center gap-2">
                <a href="<?php echo $root; ?>index.php" class="flex items-center gap-2">
                <!-- HEADER LOGO -->
                <img src="<?php echo $root; ?>uploads/logo.png" alt="Pedia Tech World" class="h-30 w-20">
            </a>
                <span class="font-bold text-xl tracking-wider">PEDIA<span class="text-red-400">TECH</span><span class="text-red-800">WORLD</span></span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex space-x-6 items-center text-sm font-medium">
                <a href="<?php echo $root; ?>index.php" class="hover:text-red-400 transition-colors">Home</a>
                <a href="<?php echo $root; ?>about.php" class="hover:text-red-400 transition-colors">About</a>
                <a href="<?php echo $root; ?>services.php" class="hover:text-red-400 transition-colors">Services</a>
                <a href="<?php echo $root; ?>projects.php" class="hover:text-red-400 transition-colors">Projects</a>
                <a href="<?php echo $root; ?>courses.php" class="hover:text-red-400 transition-colors">Courses</a>
                <a href="<?php echo $root; ?>help.php" class="hover:text-red-400 transition-colors">Help</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <a href="<?php echo $root; ?>admin/dashboard.php" class="bg-orange-600 px-4 py-2 rounded-md flex items-center gap-2 hover:bg-red-700 transition">Admin Panel</a>
                    <?php else: ?>
                        <a href="<?php echo $root; ?>employee/dashboard.php" class="bg-green-600 px-4 py-2 rounded-md flex items-center gap-2 hover:bg-green-700 transition">My Dashboard</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo $root; ?>login.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md shadow-lg shadow-red-500/20 transition">Login</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-btn" class="text-gray-300 hover:text-white focus:outline-none">
                    <i data-lucide="menu" class="w-8 h-8"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div id="mobile-menu" class="hidden md:hidden bg-slate-800 border-t border-slate-700">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="<?php echo $root; ?>index.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-700 text-white">Home</a>
            <a href="<?php echo $root; ?>about.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-700 text-white">About</a>
            <a href="<?php echo $root; ?>services.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-700 text-white">Services</a>
            <a href="<?php echo $root; ?>projects.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-700 text-white">Projects</a>
            <a href="<?php echo $root; ?>courses.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-700 text-white">Courses</a>
            <a href="<?php echo $root; ?>help.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-700 text-white">Help</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <a href="<?php echo $root; ?>admin/dashboard.php" class="block px-3 py-2 mt-2 rounded-md text-base font-medium bg-red-800 text-white text-center">Admin Panel</a>
                <?php else: ?>
                    <a href="<?php echo $root; ?>employee/dashboard.php" class="block px-3 py-2 mt-2 rounded-md text-base font-medium bg-green-600 text-white text-center">My Dashboard</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo $root; ?>login.php" class="block px-3 py-2 mt-2 rounded-md text-base font-medium bg-red-600 text-white text-center">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Remove Loader when animation is done
    window.addEventListener('load', () => {
        const loader = document.getElementById('page-loader');
        // Wait 2.5s for the animation to complete
        setTimeout(() => {
            loader.classList.add('loader-hidden');
        }, 2500);
    });

    // Mobile Menu Toggle Logic
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    menuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
</script>
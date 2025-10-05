<?php
// Load hospital data from sample.json
$jsonFile = 'sample.json';
$jsonData = file_get_contents($jsonFile);
$hospital = json_decode($jsonData, true);

if (!$hospital) {
    die("Error loading hospital data from {$jsonFile}");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hospital['name'] ?? 'Hospital Information'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9',
                        secondary: '#06b6d4',
                        accent: '#8b5cf6',
                        medical: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            position: relative;
            overflow-x: hidden;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        /* Glassmorphism Effects */
        .glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15),
                        inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }
        
        .glass-dark {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Advanced Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(14, 165, 233, 0.4); }
            50% { box-shadow: 0 0 40px rgba(14, 165, 233, 0.8); }
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        .slide-in-up {
            animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .slide-in-right {
            animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        /* Hover Effects */
        .card-3d {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        .card-3d:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2),
                        inset 0 2px 0 rgba(255, 255, 255, 0.8);
        }
        
        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-blue {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Stat Cards with Gradient Borders */
        .stat-card {
            position: relative;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
            backdrop-filter: blur(30px);
            border-radius: 24px;
            padding: 2px;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            border-radius: 24px;
            z-index: -1;
        }
        
        .stat-inner {
            background: linear-gradient(135deg, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0.95));
            border-radius: 22px;
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        /* Icon Backgrounds */
        .icon-glow {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);
        }
        
        /* Staggered Animation */
        .delay-100 { animation-delay: 0.1s; opacity: 0; }
        .delay-200 { animation-delay: 0.2s; opacity: 0; }
        .delay-300 { animation-delay: 0.3s; opacity: 0; }
        .delay-400 { animation-delay: 0.4s; opacity: 0; }
        
        /* Floating Particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            animation: float 10s ease-in-out infinite;
            pointer-events: none;
        }
        
        /* Badge Styles */
        .badge-modern {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.9), rgba(236, 72, 153, 0.9));
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
        }
        
        /* Section Headers */
        .section-header {
            position: relative;
            display: inline-block;
            padding-bottom: 1rem;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60%;
            height: 4px;
            background: linear-gradient(90deg, #0ea5e9, #8b5cf6);
            border-radius: 2px;
        }
        
        /* Service Pills */
        .service-pill {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(139, 92, 246, 0.1));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease;
        }
        
        .service-pill:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(139, 92, 246, 0.2));
            border-color: rgba(14, 165, 233, 0.5);
            transform: translateX(8px) scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Floating Particles Background -->
    <div class="particle" style="width: 80px; height: 80px; top: 10%; left: 5%; animation-delay: 0s;"></div>
    <div class="particle" style="width: 60px; height: 60px; top: 60%; right: 10%; animation-delay: 2s;"></div>
    <div class="particle" style="width: 100px; height: 100px; bottom: 15%; left: 15%; animation-delay: 4s;"></div>

    <!-- Header with Glassmorphism -->
    <header class="relative overflow-hidden">
        <div class="glass-dark text-white">
            <div class="container mx-auto px-6 py-12 relative z-10">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="flex flex-col lg:flex-row items-center gap-6 text-center lg:text-left">
                        <div class="icon-glow p-6 rounded-3xl animate-pulse-glow animate-float">
                            <i class="fas fa-hospital text-5xl"></i>
                        </div>
                        <div>
                            <h1 class="text-5xl lg:text-6xl font-black mb-4 leading-tight">
                                <?php echo $hospital['name'] ?? 'N/A'; ?>
                            </h1>
                            <div class="flex flex-wrap justify-center lg:justify-start items-center gap-4 text-medical-200">
                                <span class="glass px-4 py-2 rounded-full flex items-center gap-2 font-medium">
                                    <i class="fas fa-building"></i>
                                    <?php echo $hospital['short_name'] ?? 'N/A'; ?>
                                </span>
                                <span class="glass px-4 py-2 rounded-full flex items-center gap-2 font-medium">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo $hospital['district'] ?? $hospital['address']['city'] ?? 'N/A'; ?>
                                </span>
                                <?php if (!empty($hospital['site_area'])): ?>
                                <span class="glass px-4 py-2 rounded-full flex items-center gap-2 font-medium">
                                    <i class="fas fa-arrows-alt"></i>
                                    <?php echo $hospital['site_area']; ?>
                                </span>
                                <?php endif; ?>
                                <span class="badge-modern px-4 py-2 rounded-full flex items-center gap-2 font-semibold text-white">
                                    <i class="fas fa-calendar"></i>
                                    Est. <?php echo $hospital['established_date'] ?? 'N/A'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($hospital['contact']['website'])): ?>
                    <a href="<?php echo $hospital['contact']['website']; ?>" target="_blank" 
                       class="glass hover:glass-card px-8 py-4 rounded-2xl transition-all transform hover:scale-105 flex items-center gap-3 font-semibold text-lg">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Visit Website</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12">
        <!-- Statistics Grid with Modern Cards -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="stat-card card-3d slide-in-up delay-100">
                <div class="stat-inner">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-blue-500 to-cyan-500 p-4 rounded-2xl shadow-lg">
                            <i class="fas fa-user-injured text-3xl text-white"></i>
                        </div>
                        <span class="badge-modern px-3 py-1 rounded-full text-xs font-bold text-white">
                            ANNUAL
                        </span>
                    </div>
                    <p class="text-4xl font-black gradient-blue mb-2">
                        <?php echo number_format($hospital['statistics']['annual_outpatients'] ?? 0); ?>
                    </p>
                    <p class="text-gray-600 font-semibold">Outpatients</p>
                    <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full" style="width: 85%"></div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card card-3d slide-in-up delay-200">
                <div class="stat-inner">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-green-500 to-emerald-500 p-4 rounded-2xl shadow-lg">
                            <i class="fas fa-procedures text-3xl text-white"></i>
                        </div>
                        <span class="badge-modern px-3 py-1 rounded-full text-xs font-bold text-white">
                            ANNUAL
                        </span>
                    </div>
                    <p class="text-4xl font-black gradient-blue mb-2">
                        <?php echo number_format($hospital['statistics']['annual_surgeries'] ?? 0); ?>
                    </p>
                    <p class="text-gray-600 font-semibold">Surgeries</p>
                    <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full" style="width: 72%"></div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card card-3d slide-in-up delay-300">
                <div class="stat-inner">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-purple-500 to-pink-500 p-4 rounded-2xl shadow-lg">
                            <i class="fas fa-bed text-3xl text-white"></i>
                        </div>
                        <span class="badge-modern px-3 py-1 rounded-full text-xs font-bold text-white">
                            ANNUAL
                        </span>
                    </div>
                    <p class="text-4xl font-black gradient-blue mb-2">
                        <?php echo number_format($hospital['statistics']['annual_admissions'] ?? 0); ?>
                    </p>
                    <p class="text-gray-600 font-semibold">Admissions</p>
                    <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: 90%"></div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card card-3d slide-in-up delay-400">
                <div class="stat-inner">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-orange-500 to-red-500 p-4 rounded-2xl shadow-lg">
                            <i class="fas fa-percentage text-3xl text-white"></i>
                        </div>
                        <span class="badge-modern px-3 py-1 rounded-full text-xs font-bold text-white">
                            RATE
                        </span>
                    </div>
                    <p class="text-4xl font-black gradient-blue mb-2">
                        <?php echo $hospital['statistics']['bed_occupancy_rate'] ?? 'N/A'; ?>%
                    </p>
                    <p class="text-gray-600 font-semibold">Occupancy</p>
                    <div class="mt-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-red-500 rounded-full" 
                             style="width: <?php echo $hospital['statistics']['bed_occupancy_rate'] ?? 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Card with Glassmorphism -->
        <div class="glass-card rounded-3xl p-10 mb-12 card-3d slide-in-up">
            <h2 class="section-header text-4xl font-black text-gray-800 mb-8">
                <i class="fas fa-map-marked-alt gradient-blue mr-3"></i>
                Location & Contact
            </h2>
            <div class="grid lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="flex items-start gap-4 p-6 rounded-2xl glass hover:glass-card transition-all">
                        <div class="icon-glow p-4 rounded-2xl">
                            <i class="fas fa-location-arrow text-2xl text-white"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-lg mb-2">Full Address</p>
                            <p class="text-gray-600 leading-relaxed">
                                <?php echo $hospital['address']['street'] ?? 'N/A'; ?><br>
                                <?php echo $hospital['address']['postcode'] ?? ''; ?> 
                                <?php echo $hospital['address']['city'] ?? ''; ?><br>
                                <?php echo $hospital['address']['state'] ?? ''; ?>, 
                                <?php echo $hospital['address']['country'] ?? ''; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-6 rounded-2xl glass hover:glass-card transition-all">
                        <div class="icon-glow p-4 rounded-2xl">
                            <i class="fas fa-globe text-2xl text-white"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-lg mb-2">GPS Coordinates</p>
                            <p class="text-gray-600 font-mono text-lg">
                                <?php echo $hospital['address']['coordinates']['latitude'] ?? 'N/A'; ?>,
                                <?php echo $hospital['address']['coordinates']['longitude'] ?? 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="glass rounded-2xl p-8 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="icon-glow p-8 rounded-3xl animate-pulse-glow">
                        <i class="fas fa-map text-6xl text-white"></i>
                    </div>
                    <p class="text-gray-600 font-medium text-lg">Interactive Map Integration</p>
                    <button class="badge-modern px-6 py-3 rounded-xl text-white font-semibold hover:scale-105 transition-transform">
                        <i class="fas fa-directions mr-2"></i>Get Directions
                    </button>
                </div>
            </div>
        </div>

        <!-- Administration with Modern Layout -->
        <div class="glass-card rounded-3xl p-10 mb-12 card-3d slide-in-up">
            <h2 class="section-header text-4xl font-black text-gray-800 mb-8">
                <i class="fas fa-user-tie gradient-blue mr-3"></i>
                Leadership & Administration
            </h2>
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="glass rounded-2xl p-6 hover:glass-card transition-all card-3d">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-gradient-to-br from-blue-500 to-purple-500 p-3 rounded-2xl">
                            <i class="fas fa-user-md text-2xl text-white"></i>
                        </div>
                        <p class="font-bold text-gray-700">Director</p>
                    </div>
                    <p class="text-gray-800 text-xl font-semibold">
                        <?php echo $hospital['administration']['director'] ?? 'N/A'; ?>
                    </p>
                </div>
                
                <?php if (!empty($hospital['administration']['deputy_director1'])): ?>
                <div class="glass rounded-2xl p-6 hover:glass-card transition-all card-3d">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-gradient-to-br from-cyan-500 to-blue-500 p-3 rounded-2xl">
                            <i class="fas fa-user-md text-2xl text-white"></i>
                        </div>
                        <p class="font-bold text-gray-700">Deputy 1</p>
                    </div>
                    <p class="text-gray-800 text-xl font-semibold">
                        <?php echo $hospital['administration']['deputy_director1']; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hospital['administration']['deputy_director2'])): ?>
                <div class="glass rounded-2xl p-6 hover:glass-card transition-all card-3d">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-gradient-to-br from-purple-500 to-pink-500 p-3 rounded-2xl">
                            <i class="fas fa-user-md text-2xl text-white"></i>
                        </div>
                        <p class="font-bold text-gray-700">Deputy 2</p>
                    </div>
                    <p class="text-gray-800 text-xl font-semibold">
                        <?php echo $hospital['administration']['deputy_director2']; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hospital['administration']['deputy_director3'])): ?>
                <div class="glass rounded-2xl p-6 hover:glass-card transition-all card-3d">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-gradient-to-br from-pink-500 to-red-500 p-3 rounded-2xl">
                            <i class="fas fa-user-md text-2xl text-white"></i>
                        </div>
                        <p class="font-bold text-gray-700">Deputy 3</p>
                    </div>
                    <p class="text-gray-800 text-xl font-semibold">
                        <?php echo $hospital['administration']['deputy_director3']; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card card-3d">
                    <div class="stat-inner flex items-center gap-4">
                        <div class="bg-gradient-to-br from-blue-500 to-cyan-500 p-5 rounded-2xl shadow-lg">
                            <i class="fas fa-users text-4xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black gradient-blue">
                                <?php echo number_format($hospital['administration']['total_staff'] ?? 0); ?>
                            </p>
                            <p class="text-gray-600 font-semibold">Total Staff</p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($hospital['administration']['contract_staff'])): ?>
                <div class="stat-card card-3d">
                    <div class="stat-inner flex items-center gap-4">
                        <div class="bg-gradient-to-br from-purple-500 to-pink-500 p-5 rounded-2xl shadow-lg">
                            <i class="fas fa-file-contract text-4xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black gradient-blue">
                                <?php echo number_format($hospital['administration']['contract_staff']); ?>
                            </p>
                            <p class="text-gray-600 font-semibold">Contract Staff</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hospital['administration']['doctors'])): ?>
                <div class="stat-card card-3d">
                    <div class="stat-inner flex items-center gap-4">
                        <div class="bg-gradient-to-br from-green-500 to-emerald-500 p-5 rounded-2xl shadow-lg">
                            <i class="fas fa-user-md text-4xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black gradient-blue">
                                <?php echo number_format($hospital['administration']['doctors']); ?>
                            </p>
                            <p class="text-gray-600 font-semibold">Doctors</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hospital['administration']['nurses'])): ?>
                <div class="stat-card card-3d">
                    <div class="stat-inner flex items-center gap-4">
                        <div class="bg-gradient-to-br from-pink-500 to-rose-500 p-5 rounded-2xl shadow-lg">
                            <i class="fas fa-user-nurse text-4xl text-white"></i>
                        </div>
                        <div>
                            <p class="text-3xl font-black gradient-blue">
                                <?php echo number_format($hospital['administration']['nurses']); ?>
                            </p>
                            <p class="text-gray-600 font-semibold">Nurses</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Medical Facilities Grid -->
        <?php if (!empty($hospital['fasili']) && is_array($hospital['fasili'])): ?>
        <div class="glass-card rounded-3xl p-10 mb-12 card-3d slide-in-up">
            <h2 class="section-header text-4xl font-black text-gray-800 mb-8">
                <i class="fas fa-hospital gradient-blue mr-3"></i>
                Medical Facilities & Departments
            </h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($hospital['fasili'] as $index => $facility): ?>
                <div class="service-pill rounded-xl p-4 flex items-center gap-3" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                    <div class="bg-gradient-to-br from-blue-500 to-cyan-500 p-3 rounded-xl shadow-lg">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                    <span class="text-gray-700 font-semibold"><?php echo htmlspecialchars($facility); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Specialties -->
        <?php if (!empty($hospital['specialties']) && is_array($hospital['specialties'])): ?>
        <div class="glass-card rounded-3xl p-10 mb-12 card-3d slide-in-up">
            <h2 class="section-header text-4xl font-black text-gray-800 mb-8">
                <i class="fas fa-stethoscope gradient-blue mr-3"></i>
                Medical Specialties
            </h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($hospital['specialties'] as $index => $specialty): ?>
                <div class="service-pill rounded-xl p-4 flex items-center gap-3" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                    <div class="bg-gradient-to-br from-purple-500 to-pink-500 p-3 rounded-xl shadow-lg">
                        <i class="fas fa-star text-white text-lg"></i>
                    </div>
                    <span class="text-gray-700 font-semibold"><?php echo htmlspecialchars($specialty); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clinical Support Services -->
        <?php if (!empty($hospital['clinical_support']) && is_array($hospital['clinical_support'])): ?>
        <div class="glass-card rounded-3xl p-10 mb-12 card-3d slide-in-up">
            <h2 class="section-header text-4xl font-black text-gray-800 mb-8">
                <i class="fas fa-hand-holding-medical gradient-blue mr-3"></i>
                Clinical Support Services
            </h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <?php foreach ($hospital['clinical_support'] as $index => $service): ?>
                <div class="service-pill rounded-xl p-4 flex items-center gap-3" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                    <div class="bg-gradient-to-br from-green-500 to-emerald-500 p-3 rounded-xl shadow-lg">
                        <i class="fas fa-plus-circle text-white text-lg"></i>
                    </div>
                    <span class="text-gray-700 font-semibold"><?php echo htmlspecialchars($service); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Additional Statistics -->
        <div class="glass-card rounded-3xl p-10 mb-12 card-3d slide-in-up">
            <h2 class="section-header text-4xl font-black text-gray-800 mb-8">
                <i class="fas fa-chart-line gradient-blue mr-3"></i>
                Key Performance Metrics
            </h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card card-3d">
                    <div class="stat-inner text-center">
                        <div class="icon-glow inline-flex p-6 rounded-3xl mb-4">
                            <i class="fas fa-clock text-4xl text-white"></i>
                        </div>
                        <p class="text-5xl font-black gradient-text mb-2">
                            <?php echo $hospital['statistics']['average_stay_days'] ?? 'N/A'; ?>
                        </p>
                        <p class="text-gray-600 font-bold">Average Stay</p>
                        <p class="text-gray-500 text-sm">days</p>
                    </div>
                </div>
                
                <?php if (!empty($hospital['facilities']['total_beds'])): ?>
                <div class="stat-card card-3d">
                    <div class="stat-inner text-center">
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-500 inline-flex p-6 rounded-3xl mb-4 shadow-lg">
                            <i class="fas fa-bed text-4xl text-white"></i>
                        </div>
                        <p class="text-5xl font-black gradient-text mb-2">
                            <?php echo number_format($hospital['facilities']['total_beds']); ?>
                        </p>
                        <p class="text-gray-600 font-bold">Total Beds</p>
                        <p class="text-gray-500 text-sm">available</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hospital['facilities']['icu_beds'])): ?>
                <div class="stat-card card-3d">
                    <div class="stat-inner text-center">
                        <div class="bg-gradient-to-br from-red-500 to-pink-500 inline-flex p-6 rounded-3xl mb-4 shadow-lg">
                            <i class="fas fa-heartbeat text-4xl text-white"></i>
                        </div>
                        <p class="text-5xl font-black gradient-text mb-2">
                            <?php echo number_format($hospital['facilities']['icu_beds']); ?>
                        </p>
                        <p class="text-gray-600 font-bold">ICU Beds</p>
                        <p class="text-gray-500 text-sm">critical care</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hospital['facilities']['operating_theaters'])): ?>
                <div class="stat-card card-3d">
                    <div class="stat-inner text-center">
                        <div class="bg-gradient-to-br from-teal-500 to-cyan-500 inline-flex p-6 rounded-3xl mb-4 shadow-lg">
                            <i class="fas fa-hospital-user text-4xl text-white"></i>
                        </div>
                        <p class="text-5xl font-black gradient-text mb-2">
                            <?php echo number_format($hospital['facilities']['operating_theaters']); ?>
                        </p>
                        <p class="text-gray-600 font-bold">Operating Theaters</p>
                        <p class="text-gray-500 text-sm">surgical units</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer with Glassmorphism -->
    <footer class="glass-dark text-white py-12 mt-16">
        <div class="container mx-auto px-6">
            <div class="text-center space-y-4">
                <div class="flex items-center justify-center gap-3 text-xl">
                    <i class="fas fa-info-circle"></i>
                    <p class="font-semibold">
                        Last Updated: <?php echo date('F j, Y', strtotime($hospital['updated_at'] ?? 'now')); ?>
                    </p>
                </div>
                <p class="text-medical-200 text-lg">Hospital Information Management System</p>
                <div class="flex justify-center gap-6 pt-4">
                    <a href="#" class="glass px-6 py-3 rounded-xl hover:glass-card transition-all">
                        <i class="fas fa-phone mr-2"></i>Contact
                    </a>
                    <a href="#" class="glass px-6 py-3 rounded-xl hover:glass-card transition-all">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </a>
                    <a href="#" class="glass px-6 py-3 rounded-xl hover:glass-card transition-all">
                        <i class="fas fa-map mr-2"></i>Directions
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

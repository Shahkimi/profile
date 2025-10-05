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
                        primary: '#0284c7',
                        secondary: '#0ea5e9',
                        accent: '#06b6d4',
                        medical: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 50%, #0ea5e9 100%);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .stat-number {
            background: linear-gradient(135deg, #0284c7 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-cyan-50 min-h-screen">

<?php
// Load JSON data
$jsonFile = 'hsb.json';
$hospital = [];

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $hospital = json_decode($jsonContent, true);
}
?>

<!-- Hero Header Section -->
<div class="gradient-bg text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex flex-col md:flex-row items-start justify-between gap-8">
            <!-- Hospital Info -->
            <div class="flex-1 animate-fade-in-up">
                <div class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full text-sm mb-4 border border-white/30">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span class="font-semibold"><?php echo $hospital['status'] ?? 'Active'; ?></span>
                    <span class="text-white/70">|</span>
                    <span><?php echo $hospital['type'] ?? ''; ?> Hospital</span>
                    <span class="text-white/70">|</span>
                    <span class="bg-yellow-400/20 px-2 py-0.5 rounded text-yellow-100"><?php echo $hospital['category'] ?? ''; ?></span>
                </div>
                
                <h1 class="text-5xl md:text-6xl font-bold mb-4 leading-tight">
                    <?php echo $hospital['name'] ?? 'Hospital Name'; ?>
                </h1>
                
                <div class="flex items-center gap-2 text-lg text-blue-100 mb-3">
                    <i class="fas fa-hospital text-2xl"></i>
                    <span class="font-semibold text-2xl"><?php echo $hospital['short_name'] ?? ''; ?></span>
                </div>
                
                <div class="flex items-center gap-2 text-blue-100 mb-2">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo $hospital['address']['street'] ?? ''; ?>, <?php echo $hospital['address']['city'] ?? ''; ?>, <?php echo $hospital['address']['state'] ?? ''; ?> <?php echo $hospital['address']['postcode'] ?? ''; ?></span>
                </div>
                
                <div class="flex items-center gap-2 text-blue-100">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Established: <?php echo date('F d, Y', strtotime($hospital['established_date'] ?? '1985-01-01')); ?></span>
                </div>
            </div>

            <!-- Quick Contact Card -->
            <div class="glass-effect rounded-2xl p-6 min-w-[300px] shadow-2xl animate-fade-in-up" style="animation-delay: 0.2s;">
                <h3 class="text-sm uppercase tracking-wider mb-4 text-blue-600 font-bold flex items-center gap-2">
                    <i class="fas fa-phone-volume"></i>
                    Quick Contact
                </h3>
                
                <a href="tel:<?php echo $hospital['contact']['phone'] ?? ''; ?>" class="block mb-4 group">
                    <div class="flex items-center gap-3 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white px-4 py-3 rounded-xl transition-all duration-300 transform group-hover:scale-105">
                        <i class="fas fa-phone-alt text-xl"></i>
                        <div class="flex-1">
                            <p class="text-xs opacity-90">Phone</p>
                            <p class="text-lg font-bold"><?php echo $hospital['contact']['phone'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                </a>
                
                <a href="mailto:<?php echo $hospital['contact']['email'] ?? ''; ?>" class="block mb-4 group">
                    <div class="flex items-center gap-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-xl transition-all duration-300">
                        <i class="fas fa-envelope text-xl text-blue-600"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-semibold truncate"><?php echo $hospital['contact']['email'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo $hospital['contact']['website'] ?? '#'; ?>" target="_blank" class="block group">
                    <div class="flex items-center gap-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-xl transition-all duration-300">
                        <i class="fas fa-globe text-xl text-blue-600"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Website</p>
                            <p class="text-sm font-semibold truncate"><?php echo parse_url($hospital['contact']['website'] ?? '', PHP_URL_HOST); ?></p>
                        </div>
                        <i class="fas fa-external-link-alt text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-10">
    
    <!-- Statistics Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Beds -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-l-4 border-blue-500 animate-fade-in-up">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-blue-100 rounded-2xl p-4">
                    <i class="fas fa-bed text-blue-600 text-3xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Beds</p>
                    <h3 class="text-4xl font-bold stat-number">
                        <?php echo number_format($hospital['facilities']['total_beds'] ?? 0); ?>
                    </h3>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span><i class="fas fa-procedures mr-1"></i> ICU: <?php echo $hospital['facilities']['icu_beds'] ?? 0; ?></span>
                <span><i class="fas fa-ambulance mr-1"></i> Emergency: <?php echo $hospital['facilities']['emergency_beds'] ?? 0; ?></span>
            </div>
        </div>

        <!-- Total Staff -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-l-4 border-green-500 animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-green-100 rounded-2xl p-4">
                    <i class="fas fa-users text-green-600 text-3xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Staff</p>
                    <h3 class="text-4xl font-bold text-green-600">
                        <?php echo number_format($hospital['administration']['total_staff'] ?? 0); ?>
                    </h3>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span><i class="fas fa-user-md mr-1"></i> Doctors: <?php echo $hospital['administration']['doctors'] ?? 0; ?></span>
                <span><i class="fas fa-user-nurse mr-1"></i> Nurses: <?php echo $hospital['administration']['nurses'] ?? 0; ?></span>
            </div>
        </div>

        <!-- Annual Admissions -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-l-4 border-purple-500 animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-100 rounded-2xl p-4">
                    <i class="fas fa-user-injured text-purple-600 text-3xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Admissions</p>
                    <h3 class="text-4xl font-bold text-purple-600">
                        <?php echo number_format($hospital['statistics']['annual_admissions'] ?? 0); ?>
                    </h3>
                </div>
            </div>
            <div class="text-xs text-gray-500">
                <i class="fas fa-chart-line mr-1"></i> Annual patient admissions
            </div>
        </div>

        <!-- Bed Occupancy -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-l-4 border-orange-500 animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-orange-100 rounded-2xl p-4">
                    <i class="fas fa-percentage text-orange-600 text-3xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Occupancy</p>
                    <h3 class="text-4xl font-bold text-orange-600">
                        <?php echo $hospital['statistics']['bed_occupancy_rate'] ?? 0; ?>%
                    </h3>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo $hospital['statistics']['bed_occupancy_rate'] ?? 0; ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Contact Information Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-blue-100 rounded-lg p-2">
                        <i class="fas fa-address-card text-blue-600"></i>
                    </div>
                    Contact Details
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="bg-blue-100 rounded-lg p-2 mt-1">
                            <i class="fas fa-phone text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Phone</p>
                            <a href="tel:<?php echo $hospital['contact']['phone'] ?? ''; ?>" class="text-gray-800 font-semibold hover:text-blue-600 transition">
                                <?php echo $hospital['contact']['phone'] ?? 'N/A'; ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="bg-blue-100 rounded-lg p-2 mt-1">
                            <i class="fas fa-envelope text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Email</p>
                            <a href="mailto:<?php echo $hospital['contact']['email'] ?? ''; ?>" class="text-gray-800 font-semibold hover:text-blue-600 transition break-all">
                                <?php echo $hospital['contact']['email'] ?? 'N/A'; ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="bg-blue-100 rounded-lg p-2 mt-1">
                            <i class="fas fa-globe text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Website</p>
                            <a href="<?php echo $hospital['contact']['website'] ?? '#'; ?>" target="_blank" class="text-blue-600 font-semibold hover:underline break-all text-sm">
                                <?php echo $hospital['contact']['website'] ?? 'N/A'; ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="bg-blue-100 rounded-lg p-2 mt-1">
                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Address</p>
                            <p class="text-gray-800 font-medium text-sm leading-relaxed">
                                <?php echo $hospital['address']['street'] ?? ''; ?>,<br>
                                <?php echo $hospital['address']['postcode'] ?? ''; ?> <?php echo $hospital['address']['city'] ?? ''; ?>,<br>
                                <?php echo $hospital['address']['state'] ?? ''; ?>, <?php echo $hospital['address']['country'] ?? ''; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Map Card (Same Size as Contact) -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-blue-100 rounded-lg p-2">
                        <i class="fas fa-map text-blue-600"></i>
                    </div>
                    Location Map
                </h2>
                <div class="bg-gray-100 rounded-xl overflow-hidden mb-4" style="height: 250px;">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        style="border:0"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?php echo $hospital['address']['coordinates']['latitude'] ?? 0; ?>,<?php echo $hospital['address']['coordinates']['longitude'] ?? 0; ?>&output=embed"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg p-3">
                        <i class="fas fa-compass text-blue-600"></i>
                        <div>
                            <span class="font-semibold">Coordinates:</span><br>
                            <span class="text-gray-500"><?php echo $hospital['address']['coordinates']['latitude'] ?? 0; ?>, <?php echo $hospital['address']['coordinates']['longitude'] ?? 0; ?></span>
                        </div>
                    </div>
                    <a href="https://www.google.com/maps?q=<?php echo $hospital['address']['coordinates']['latitude'] ?? 0; ?>,<?php echo $hospital['address']['coordinates']['longitude'] ?? 0; ?>" 
                       target="_blank" 
                       class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-4 py-3 rounded-xl transition-all duration-300 transform hover:scale-105 font-semibold">
                        <i class="fas fa-directions"></i>
                        Get Directions
                    </a>
                </div>
            </div>

            <!-- Facilities Card -->
            <div class="bg-gradient-to-br from-blue-600 to-cyan-600 rounded-2xl shadow-lg p-6 text-white card-hover">
                <h2 class="text-xl font-bold mb-5 flex items-center gap-2">
                    <i class="fas fa-building"></i>
                    Key Facilities
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20">
                        <i class="fas fa-procedures text-3xl mb-2"></i>
                        <p class="text-2xl font-bold"><?php echo $hospital['facilities']['operating_theaters'] ?? 0; ?></p>
                        <p class="text-xs text-blue-100">Theaters</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20">
                        <i class="fas fa-ambulance text-3xl mb-2"></i>
                        <p class="text-2xl font-bold"><?php echo $hospital['facilities']['ambulances'] ?? 0; ?></p>
                        <p class="text-xs text-blue-100">Ambulances</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20">
                        <i class="fas fa-parking text-3xl mb-2"></i>
                        <p class="text-2xl font-bold"><?php echo number_format($hospital['facilities']['parking_spaces'] ?? 0); ?></p>
                        <p class="text-xs text-blue-100">Parking</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/20">
                        <i class="fas fa-heartbeat text-3xl mb-2"></i>
                        <p class="text-2xl font-bold"><?php echo $hospital['facilities']['icu_beds'] ?? 0; ?></p>
                        <p class="text-xs text-blue-100">ICU Beds</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Content Area -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Services Grid -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-blue-100 rounded-lg p-2">
                        <i class="fas fa-stethoscope text-blue-600"></i>
                    </div>
                    Medical Services
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php if (!empty($hospital['services'])): ?>
                        <?php foreach ($hospital['services'] as $service): ?>
                            <div class="flex items-center gap-3 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-4 border border-blue-100 hover:shadow-md transition-all duration-300 group">
                                <div class="bg-blue-500 text-white rounded-lg p-2 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-check text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium text-sm"><?php echo htmlspecialchars($service); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 col-span-2">No services available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Specialties -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-purple-100 rounded-lg p-2">
                        <i class="fas fa-star text-purple-600"></i>
                    </div>
                    Medical Specialties
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php if (!empty($hospital['specialties'])): ?>
                        <?php foreach ($hospital['specialties'] as $specialty): ?>
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border-2 border-purple-200 hover:border-purple-400 transition-all duration-300 text-center group cursor-pointer">
                                <i class="fas fa-award text-purple-600 text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                                <p class="text-gray-700 font-semibold text-sm"><?php echo htmlspecialchars($specialty); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 col-span-3">No specialties available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-green-100 rounded-lg p-2">
                        <i class="fas fa-chart-bar text-green-600"></i>
                    </div>
                    Annual Statistics
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 text-center border border-blue-200 hover:shadow-lg transition-all">
                        <div class="bg-blue-500 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-user-injured text-xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-blue-700 mb-1">
                            <?php echo number_format($hospital['statistics']['annual_admissions'] ?? 0); ?>
                        </p>
                        <p class="text-xs text-gray-600 font-medium">Admissions</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 text-center border border-green-200 hover:shadow-lg transition-all">
                        <div class="bg-green-500 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-walking text-xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-green-700 mb-1">
                            <?php echo number_format($hospital['statistics']['annual_outpatients'] ?? 0); ?>
                        </p>
                        <p class="text-xs text-gray-600 font-medium">Outpatients</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 text-center border border-purple-200 hover:shadow-lg transition-all">
                        <div class="bg-purple-500 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-procedures text-xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-purple-700 mb-1">
                            <?php echo number_format($hospital['statistics']['annual_surgeries'] ?? 0); ?>
                        </p>
                        <p class="text-xs text-gray-600 font-medium">Surgeries</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-5 text-center border border-orange-200 hover:shadow-lg transition-all">
                        <div class="bg-orange-500 text-white rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-calendar-day text-xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-orange-700 mb-1">
                            <?php echo $hospital['statistics']['average_stay_days'] ?? 0; ?>
                        </p>
                        <p class="text-xs text-gray-600 font-medium">Avg. Stay (Days)</p>
                    </div>
                </div>
            </div>

            <!-- Administration -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-indigo-100 rounded-lg p-2">
                        <i class="fas fa-user-tie text-indigo-600"></i>
                    </div>
                    Leadership Team
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-5 border-l-4 border-indigo-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-indigo-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-medium">Director</p>
                                <p class="text-gray-800 font-bold text-lg"><?php echo $hospital['administration']['director'] ?? 'TBA'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-5 border-l-4 border-blue-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-medium">Deputy Director</p>
                                <p class="text-gray-800 font-bold text-lg"><?php echo $hospital['administration']['deputy_director'] ?? 'TBA'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-xl p-4 text-center hover:bg-gray-100 transition-colors">
                        <i class="fas fa-user-md text-blue-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($hospital['administration']['doctors'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 font-medium">Doctors</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center hover:bg-gray-100 transition-colors">
                        <i class="fas fa-user-nurse text-green-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($hospital['administration']['nurses'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 font-medium">Nurses</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center hover:bg-gray-100 transition-colors">
                        <i class="fas fa-users text-purple-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($hospital['administration']['support_staff'] ?? 0); ?></p>
                        <p class="text-xs text-gray-500 font-medium">Support Staff</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <div class="mt-10 bg-white rounded-2xl shadow-lg p-6 text-center">
        <div class="flex items-center justify-center gap-4 flex-wrap">
            <div class="flex items-center gap-2 text-gray-600">
                <i class="fas fa-calendar text-blue-600"></i>
                <span class="text-sm">Last Updated: <span class="font-semibold"><?php echo date('F d, Y \a\t H:i', strtotime($hospital['updated_at'] ?? 'now')); ?></span></span>
            </div>
            <span class="text-gray-300">|</span>
            <div class="flex items-center gap-2 text-gray-600">
                <i class="fas fa-shield-alt text-green-600"></i>
                <span class="text-sm">Hospital ID: <span class="font-semibold"><?php echo $hospital['hospital_id'] ?? 'N/A'; ?></span></span>
            </div>
        </div>
    </div>

</div>

</body>
</html>

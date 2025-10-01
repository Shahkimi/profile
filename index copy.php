<?php
/**
 * Hospital Data Display - Modern PHP Website
 * Reads and displays data from multiple hospital JSON files with responsive design
 */

// Start session for clean URL functionality
session_start();

// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to safely read and parse JSON
function loadHospitalData($filename) {
    try {
        if (!file_exists($filename)) {
            throw new Exception("JSON file not found: " . $filename);
        }
        
        $jsonContent = file_get_contents($filename);
        if ($jsonContent === false) {
            throw new Exception("Failed to read JSON file");
        }
        
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON parsing error: " . json_last_error_msg());
        }
        
        return $data;
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// Function to discover all hospital JSON files
function getHospitalFiles() {
    $files = [];
    $pattern = __DIR__ . '/*.json';
    $jsonFiles = glob($pattern);
    
    foreach ($jsonFiles as $file) {
        $filename = basename($file);
        // Load basic info to get hospital name
        $data = loadHospitalData($filename);
        if (!isset($data['error'])) {
            $files[] = [
                'filename' => $filename,
                'name' => $data['name'],
                'short_name' => $data['short_name'],
                'hospital_id' => $data['hospital_id']
            ];
        }
    }
    
    // Sort by hospital name
    usort($files, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    return $files;
}

// Determine which hospital file to load
$selectedFile = 'hsb.json'; // Default

// Handle clean URLs for all hospital selections
if (isset($_GET['hospital'])) {
    $requestedFile = $_GET['hospital'];
    // Store any hospital selection in session and redirect to clean URL
    $_SESSION['selected_hospital'] = $requestedFile;
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
} elseif (isset($_SESSION['selected_hospital'])) {
    // Use hospital from session for clean URL
    $selectedFile = $_SESSION['selected_hospital'];
}

// Validate the selected file exists and is a JSON file
if (!file_exists($selectedFile) || !preg_match('/\.json$/', $selectedFile)) {
    $selectedFile = 'hsb.json'; // Default fallback
    // Clear invalid session data
    unset($_SESSION['selected_hospital']);
}

// Load hospital data
$hospital = loadHospitalData($selectedFile);
$hasError = isset($hospital['error']);

// Get all available hospital files for navigation
$hospitalFiles = getHospitalFiles();

// Helper function to format numbers
function formatNumber($number) {
    return number_format($number, 0, '.', ',');
}

// Helper function to format dates
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $hasError ? 'Hospital Information' : htmlspecialchars($hospital['name']); ?> - Comprehensive hospital information and services">
    <title><?php echo $hasError ? 'Hospital Information' : htmlspecialchars($hospital['name']); ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .animate-slideIn {
            animation: slideIn 0.5s ease-out forwards;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .search-highlight {
            background-color: #fef3c7;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Badge styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    
    <?php if ($hasError): ?>
        <!-- Error Display -->
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6 max-w-lg w-full shadow-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800 mb-2">Error Loading Data</h3>
                        <p class="text-red-700"><?php echo htmlspecialchars($hospital['error']); ?></p>
                        <p class="text-red-600 text-sm mt-2">Please ensure the JSON file exists and is properly formatted.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
    
    <!-- Navigation Header -->
    <nav class="gradient-bg text-white shadow-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 animate-slideIn">
                    <i class="fas fa-hospital text-3xl"></i>
                    <div>
                        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($hospital['short_name']); ?></h1>
                        <p class="text-sm opacity-90"><?php echo htmlspecialchars($hospital['name']); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Hospital Selector -->
                    <div class="relative">
                        <select 
                            id="hospitalSelector" 
                            onchange="switchHospital(this.value)"
                            class="bg-white bg-opacity-20 text-white border border-white border-opacity-30 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 appearance-none cursor-pointer"
                        >
                            <?php foreach ($hospitalFiles as $file): ?>
                                <option 
                                    value="<?php echo htmlspecialchars($file['filename']); ?>" 
                                    <?php echo ($file['filename'] === $selectedFile) ? 'selected' : ''; ?>
                                    class="text-gray-800"
                                >
                                    <?php echo htmlspecialchars($file['short_name']); ?> - <?php echo htmlspecialchars($file['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-white pointer-events-none"></i>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden md:flex space-x-6">
                        <a href="#overview" class="hover:text-gray-200 transition">Overview</a>
                        <a href="#services" class="hover:text-gray-200 transition">Services</a>
                        <a href="#contact" class="hover:text-gray-200 transition">Contact</a>
                        <a href="#statistics" class="hover:text-gray-200 transition">Statistics</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Bar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-4 animate-fadeIn">
            <div class="flex items-center space-x-3">
                <i class="fas fa-search text-gray-400 text-xl"></i>
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Search services, specialties, or information..." 
                    class="flex-1 outline-none text-gray-700 placeholder-gray-400"
                    onkeyup="performSearch()"
                >
                <button onclick="clearSearch()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Hero Stats Section -->
        <section id="overview" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-fadeIn">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white card-hover shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-bed text-4xl opacity-80"></i>
                    <span class="badge bg-white text-blue-600">Active</span>
                </div>
                <h3 class="text-3xl font-bold mb-1"><?php echo formatNumber($hospital['facilities']['total_beds']); ?></h3>
                <p class="text-blue-100">Total Beds</p>
            </div>
            
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white card-hover shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-user-md text-4xl opacity-80"></i>
                    <span class="badge bg-white text-purple-600">Staff</span>
                </div>
                <h3 class="text-3xl font-bold mb-1"><?php echo formatNumber($hospital['administration']['doctors']); ?></h3>
                <p class="text-purple-100">Doctors</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white card-hover shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-procedures text-4xl opacity-80"></i>
                    <span class="badge bg-white text-green-600">ICU</span>
                </div>
                <h3 class="text-3xl font-bold mb-1"><?php echo $hospital['facilities']['icu_beds']; ?></h3>
                <p class="text-green-100">ICU Beds</p>
            </div>
            
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white card-hover shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-ambulance text-4xl opacity-80"></i>
                    <span class="badge bg-white text-red-600">24/7</span>
                </div>
                <h3 class="text-3xl font-bold mb-1"><?php echo $hospital['facilities']['ambulances']; ?></h3>
                <p class="text-red-100">Ambulances</p>
            </div>
        </section>

        <!-- Hospital Information Card -->
        <section class="bg-white rounded-xl shadow-lg p-8 animate-fadeIn">
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                        Hospital Details
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <span class="text-gray-600 w-32 flex-shrink-0">Type:</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['type']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-gray-600 w-32 flex-shrink-0">Category:</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['category']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-gray-600 w-32 flex-shrink-0">Status:</span>
                            <span class="badge badge-success"><?php echo htmlspecialchars($hospital['status']); ?></span>
                        </div>
                        <div class="flex items-start">
                            <span class="text-gray-600 w-32 flex-shrink-0">Established:</span>
                            <span class="font-medium text-gray-800"><?php echo formatDate($hospital['established_date']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-certificate text-purple-500 mr-3"></i>
                        Accreditation
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">ISO Certified</span>
                            <?php if ($hospital['accreditation']['iso_certified']): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-times mr-1"></i>No</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">MSQH Certified</span>
                            <?php if ($hospital['accreditation']['msqh_certified']): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-times mr-1"></i>No</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">Last Audit</span>
                            <span class="font-medium text-gray-800"><?php echo formatDate($hospital['accreditation']['last_audit']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="bg-white rounded-xl shadow-lg p-8 animate-fadeIn">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-heartbeat text-red-500 mr-3"></i>
                Services & Specialties
            </h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Medical Services</h3>
                <div id="servicesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($hospital['services'] as $service): ?>
                        <div class="service-item flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg card-hover border border-blue-100">
                            <i class="fas fa-check-circle text-blue-500 mr-3"></i>
                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($service); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Specialty Services</h3>
                <div id="specialtiesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($hospital['specialties'] as $specialty): ?>
                        <div class="specialty-item flex items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg card-hover border border-purple-100">
                            <i class="fas fa-star text-purple-500 mr-3"></i>
                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($specialty); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Contact & Location -->
        <section id="contact" class="grid md:grid-cols-2 gap-6 animate-fadeIn">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-phone text-green-500 mr-3"></i>
                    Contact Information
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <i class="fas fa-phone-alt text-gray-400 mt-1 mr-3 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['contact']['phone']); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-fax text-gray-400 mt-1 mr-3 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-600">Fax</p>
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['contact']['fax']); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-gray-400 mt-1 mr-3 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <a href="mailto:<?php echo htmlspecialchars($hospital['contact']['email']); ?>" class="font-medium text-blue-600 hover:underline"><?php echo htmlspecialchars($hospital['contact']['email']); ?></a>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-globe text-gray-400 mt-1 mr-3 w-5"></i>
                        <div>
                            <p class="text-sm text-gray-600">Website</p>
                            <a href="<?php echo htmlspecialchars($hospital['contact']['website']); ?>" target="_blank" class="font-medium text-blue-600 hover:underline"><?php echo htmlspecialchars($hospital['contact']['website']); ?></a>
                        </div>
                    </div>
                    <div class="flex items-start bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                        <i class="fas fa-ambulance text-red-500 mt-1 mr-3 text-xl"></i>
                        <div>
                            <p class="text-sm text-red-600 font-semibold">Emergency Hotline</p>
                            <p class="font-bold text-red-700 text-lg"><?php echo htmlspecialchars($hospital['contact']['emergency_hotline']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-map-marker-alt text-red-500 mr-3"></i>
                    Location
                </h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-700 leading-relaxed">
                            <?php echo htmlspecialchars($hospital['address']['street']); ?><br>
                            <?php echo htmlspecialchars($hospital['address']['postcode']); ?> <?php echo htmlspecialchars($hospital['address']['city']); ?><br>
                            <?php echo htmlspecialchars($hospital['address']['state']); ?>, <?php echo htmlspecialchars($hospital['address']['country']); ?>
                        </p>
                    </div>
                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-700 mb-3">Operating Hours</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Emergency</span>
                                <span class="badge badge-info"><?php echo htmlspecialchars($hospital['operating_hours']['emergency']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Outpatient (Weekdays)</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['operating_hours']['outpatient']['weekdays']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Outpatient (Weekends)</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['operating_hours']['outpatient']['weekends']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Visiting Hours</span>
                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($hospital['operating_hours']['visiting_hours']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600">Coordinates: <?php echo htmlspecialchars($hospital['address']['coordinates']['latitude']); ?>, <?php echo htmlspecialchars($hospital['address']['coordinates']['longitude']); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section id="statistics" class="bg-white rounded-xl shadow-lg p-8 animate-fadeIn">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-chart-bar text-blue-500 mr-3"></i>
                Annual Statistics
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border-l-4 border-blue-500">
                    <i class="fas fa-user-injured text-blue-500 text-3xl mb-3"></i>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo formatNumber($hospital['statistics']['annual_admissions']); ?></h3>
                    <p class="text-gray-600">Annual Admissions</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border-l-4 border-green-500">
                    <i class="fas fa-notes-medical text-green-500 text-3xl mb-3"></i>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo formatNumber($hospital['statistics']['annual_outpatients']); ?></h3>
                    <p class="text-gray-600">Annual Outpatients</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border-l-4 border-purple-500">
                    <i class="fas fa-procedures text-purple-500 text-3xl mb-3"></i>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo formatNumber($hospital['statistics']['annual_surgeries']); ?></h3>
                    <p class="text-gray-600">Annual Surgeries</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border-l-4 border-orange-500">
                    <i class="fas fa-calendar-alt text-orange-500 text-3xl mb-3"></i>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($hospital['statistics']['average_stay_days'], 1); ?></h3>
                    <p class="text-gray-600">Average Stay (Days)</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 border-l-4 border-red-500">
                    <i class="fas fa-percentage text-red-500 text-3xl mb-3"></i>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo number_format($hospital['statistics']['bed_occupancy_rate'], 1); ?>%</h3>
                    <p class="text-gray-600">Bed Occupancy Rate</p>
                </div>
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border-l-4 border-indigo-500">
                    <i class="fas fa-users text-indigo-500 text-3xl mb-3"></i>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?php echo formatNumber($hospital['administration']['total_staff']); ?></h3>
                    <p class="text-gray-600">Total Staff</p>
                </div>
            </div>
        </section>

        <!-- Administration Section -->
        <section class="bg-white rounded-xl shadow-lg p-8 animate-fadeIn">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-users-cog text-indigo-500 mr-3"></i>
                Administration & Staff
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border-l-4 border-indigo-500">
                        <i class="fas fa-user-tie text-indigo-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-sm text-gray-600">Director</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($hospital['administration']['director']); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border-l-4 border-purple-500">
                        <i class="fas fa-user-tie text-purple-500 text-2xl mr-4"></i>
                        <div>
                            <p class="text-sm text-gray-600">Deputy Director</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($hospital['administration']['deputy_director']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <i class="fas fa-user-md text-blue-500 text-3xl mb-2"></i>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo formatNumber($hospital['administration']['doctors']); ?></h3>
                        <p class="text-gray-600 text-sm">Doctors</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <i class="fas fa-user-nurse text-green-500 text-3xl mb-2"></i>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo formatNumber($hospital['administration']['nurses']); ?></h3>
                        <p class="text-gray-600 text-sm">Nurses</p>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg col-span-2">
                        <i class="fas fa-users text-purple-500 text-3xl mb-2"></i>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo formatNumber($hospital['administration']['support_staff']); ?></h3>
                        <p class="text-gray-600 text-sm">Support Staff</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Facilities Section -->
        <section class="bg-white rounded-xl shadow-lg p-8 animate-fadeIn">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-building text-blue-500 mr-3"></i>
                Facilities & Infrastructure
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg card-hover">
                    <i class="fas fa-door-open text-blue-500 text-3xl mb-2"></i>
                    <h3 class="text-xl font-bold text-gray-800"><?php echo $hospital['facilities']['operating_theaters']; ?></h3>
                    <p class="text-gray-600 text-sm">Operating Theaters</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg card-hover">
                    <i class="fas fa-parking text-green-500 text-3xl mb-2"></i>
                    <h3 class="text-xl font-bold text-gray-800"><?php echo formatNumber($hospital['facilities']['parking_spaces']); ?></h3>
                    <p class="text-gray-600 text-sm">Parking Spaces</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg card-hover">
                    <i class="fas fa-procedures text-red-500 text-3xl mb-2"></i>
                    <h3 class="text-xl font-bold text-gray-800"><?php echo $hospital['facilities']['emergency_beds']; ?></h3>
                    <p class="text-gray-600 text-sm">Emergency Beds</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg card-hover">
                    <i class="fas fa-ambulance text-yellow-600 text-3xl mb-2"></i>
                    <h3 class="text-xl font-bold text-gray-800"><?php echo $hospital['facilities']['ambulances']; ?></h3>
                    <p class="text-gray-600 text-sm">Ambulances</p>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-300">Last Updated: <?php echo formatDate($hospital['updated_at']); ?></p>
                <p class="text-gray-400 text-sm mt-2">Hospital ID: <?php echo htmlspecialchars($hospital['hospital_id']); ?></p>
                <p class="text-gray-500 text-xs mt-4">&copy; 2025 <?php echo htmlspecialchars($hospital['name']); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for Search and Interactions -->
    <script>
        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            // Search in services
            const serviceItems = document.querySelectorAll('.service-item');
            let servicesFound = 0;
            serviceItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm) || searchTerm === '') {
                    item.style.display = 'flex';
                    servicesFound++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Search in specialties
            const specialtyItems = document.querySelectorAll('.specialty-item');
            let specialtiesFound = 0;
            specialtyItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm) || searchTerm === '') {
                    item.style.display = 'flex';
                    specialtiesFound++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide sections based on results
            const servicesGrid = document.getElementById('servicesGrid');
            const specialtiesGrid = document.getElementById('specialtiesGrid');
            
            if (searchTerm && servicesFound === 0) {
                servicesGrid.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4">No services found matching your search.</p>';
            }
            
            if (searchTerm && specialtiesFound === 0) {
                specialtiesGrid.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4">No specialties found matching your search.</p>';
            }
            
            // Restore original content if search is cleared
            if (searchTerm === '') {
                location.reload();
            }
        }
        
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            performSearch();
        }
        
        // Smooth scroll for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Lazy loading animation observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.animate-fadeIn').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        });
        
        // Hospital switching functionality
        function switchHospital(filename) {
            if (filename) {
                // Show loading indicator
                const body = document.body;
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                loadingOverlay.innerHTML = `
                    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="text-gray-700 font-medium">Loading hospital data...</span>
                    </div>
                `;
                body.appendChild(loadingOverlay);
                
                // All hospitals now use clean URLs - redirect with parameter then PHP handles the clean URL
                window.location.href = '?hospital=' + encodeURIComponent(filename);
            }
        }
        
        // Add loading complete event
        window.addEventListener('load', () => {
            console.log('Hospital data display loaded successfully');
            
            // Remove any existing loading overlays
            const loadingOverlays = document.querySelectorAll('.fixed.inset-0.bg-black.bg-opacity-50');
            loadingOverlays.forEach(overlay => overlay.remove());
        });
    </script>
    
    <?php endif; ?>
</body>
</html>

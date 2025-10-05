<?php
/**
 * Hospital Data Display - Secure & Optimized PHP Website
 * High-performance hospital data display with comprehensive security and caching
 */

// Security headers - Set early to prevent any output before headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Content Security Policy
$csp = "default-src 'self'; " .
       "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
       "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
       "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
       "img-src 'self' data:; " .
       "connect-src 'self'; " .
       "frame-ancestors 'none'; " .
       "base-uri 'self'; " .
       "form-action 'self'";
header("Content-Security-Policy: $csp");

// Performance optimizations
ob_start(); // Enable output buffering
if (function_exists('gzhandler')) {
    ob_start('gzhandler'); // Enable gzip compression
}

// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600); // 1 hour session timeout

// Start session for clean URL functionality
session_start();

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Error handling configuration - Hide errors in production
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost:8080') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Cache configuration
define('CACHE_DIR', __DIR__ . '/cache');
define('CACHE_DURATION', 3600); // 1 hour cache

// Ensure cache directory exists with secure permissions
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0750, true);
}

// Security functions
function sanitizeFilename($filename) {
    // Remove any path traversal attempts and ensure only valid JSON files
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    // Ensure it ends with .json
    if (!preg_match('/\.json$/', $filename)) {
        return false;
    }
    
    // Additional security: check against whitelist of allowed files
    $allowedFiles = glob(__DIR__ . '/*.json');
    $allowedBasenames = array_map('basename', $allowedFiles);
    
    return in_array($filename, $allowedBasenames) ? $filename : false;
}

function validateInput($input, $type = 'string', $maxLength = 255) {
    if ($input === null || $input === '') {
        return false;
    }
    
    switch ($type) {
        case 'filename':
            return sanitizeFilename($input);
        case 'string':
            $input = trim($input);
            if (strlen($input) > $maxLength) {
                return false;
            }
            return htmlspecialchars(strip_tags($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL);
        default:
            return false;
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting function
function checkRateLimit($action = 'default', $limit = 60, $window = 60) {
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $current_time = time();
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'start' => $current_time];
        return true;
    }
    
    $rate_data = $_SESSION['rate_limit'][$key];
    
    // Reset if window has passed
    if ($current_time - $rate_data['start'] > $window) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'start' => $current_time];
        return true;
    }
    
    // Check if limit exceeded
    if ($rate_data['count'] >= $limit) {
        return false;
    }
    
    // Increment counter
    $_SESSION['rate_limit'][$key]['count']++;
    return true;
}

// Optimized function to safely read and parse JSON with caching and security
function loadHospitalData($filename) {
    static $cache = [];
    
    // Validate and sanitize filename
    $filename = sanitizeFilename($filename);
    if (!$filename) {
        return ['error' => 'Invalid filename provided'];
    }
    
    // Return from memory cache if available
    if (isset($cache[$filename])) {
        return $cache[$filename];
    }
    
    // Check file cache
    $cacheFile = CACHE_DIR . '/' . md5($filename) . '.cache';
    $sourceFile = __DIR__ . '/' . $filename;
    
    // Validate source file path to prevent directory traversal
    $realSourcePath = realpath($sourceFile);
    $realBasePath = realpath(__DIR__);
    
    if (!$realSourcePath || !$realBasePath || strpos($realSourcePath, $realBasePath) !== 0) {
        $errorData = ['error' => 'Access denied: Invalid file path'];
        $cache[$filename] = $errorData;
        return $errorData;
    }
    
    if (file_exists($cacheFile) && file_exists($sourceFile) && 
        filemtime($cacheFile) > filemtime($sourceFile) && 
        (time() - filemtime($cacheFile)) < CACHE_DURATION) {
        
        $serializedData = file_get_contents($cacheFile);
        if ($serializedData !== false) {
            $data = unserialize($serializedData);
            if ($data !== false) {
                $cache[$filename] = $data;
                return $data;
            }
        }
    }
    
    try {
        if (!file_exists($sourceFile)) {
            throw new Exception("JSON file not found");
        }
        
        // Check file size to prevent memory exhaustion
        $fileSize = filesize($sourceFile);
        if ($fileSize === false || $fileSize > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception("File too large or unreadable");
        }
        
        $jsonContent = file_get_contents($sourceFile);
        if ($jsonContent === false) {
            throw new Exception("Failed to read JSON file");
        }
        
        $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        
        // Validate required fields
        if (!is_array($data) || !isset($data['name'], $data['short_name'])) {
            throw new Exception("Invalid JSON structure");
        }
        
        // Sanitize data before caching
        $data = array_map(function($value) {
            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
        
        // Cache the data securely
        if (file_put_contents($cacheFile, serialize($data), LOCK_EX) === false) {
            error_log("Failed to write cache file: $cacheFile");
        }
        
        $cache[$filename] = $data;
        return $data;
        
    } catch (Exception $e) {
        error_log("Error loading hospital data for $filename: " . $e->getMessage());
        $errorData = ['error' => 'Failed to load hospital data'];
        $cache[$filename] = $errorData;
        return $errorData;
    }
}

// Optimized function to discover all hospital JSON files with caching
function getHospitalFiles() {
    static $cachedFiles = null;
    
    if ($cachedFiles !== null) {
        return $cachedFiles;
    }
    
    $cacheFile = CACHE_DIR . '/hospital_files.cache';
    
    // Check if cache is valid
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_DURATION) {
        $cachedFiles = unserialize(file_get_contents($cacheFile));
        return $cachedFiles;
    }
    
    $files = [];
    $pattern = __DIR__ . '/*.json';
    $jsonFiles = glob($pattern);
    
    foreach ($jsonFiles as $file) {
        $filename = basename($file);
        // Only load minimal data needed for listing
        $data = loadHospitalData($filename);
        if (!isset($data['error']) && isset($data['name'], $data['short_name'], $data['hospital_id'])) {
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
    
    // Cache the result
    file_put_contents($cacheFile, serialize($files), LOCK_EX);
    $cachedFiles = $files;
    
    return $files;
}

// Generate CSRF token for this session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateCSRFToken();
}

// Handle CSRF-protected hospital switching via POST
if (isset($_POST['hospital']) && isset($_POST['csrf_token'])) {
    if (validateCSRFToken($_POST['csrf_token'])) {
        $requestedHospital = validateInput($_POST['hospital'], 'filename');
        if ($requestedHospital && checkRateLimit('hospital_switch', 10, 60)) {
            $hospitalPath = __DIR__ . '/' . $requestedHospital;
            if (file_exists($hospitalPath) && is_readable($hospitalPath)) {
                $_SESSION['selected_hospital'] = $requestedHospital;
                // Regenerate session ID after successful hospital switch
                session_regenerate_id(true);
                // Clean redirect to prevent form resubmission
                header('Location: ' . $_SERVER['PHP_SELF'] . '?hospital=' . urlencode($requestedHospital));
                exit;
            } else {
                error_log("Security: Invalid hospital file access attempt: " . $requestedHospital);
            }
        }
    } else {
        error_log("Security: CSRF token validation failed for hospital switch");
    }
}

// Optimized session and URL handling with security
$selectedFile = 'hsb.json'; // Default

// Rate limiting for hospital switching
if (!checkRateLimit('hospital_switch', 30, 60)) {
    http_response_code(429);
    die('Too many requests. Please try again later.');
}

// Handle clean URLs for all hospital selections with CSRF protection
if (isset($_GET['hospital'])) {
    $requestedFile = validateInput($_GET['hospital'], 'filename');
    
    if ($requestedFile) {
        // Validate file exists and is accessible
        $fullPath = __DIR__ . '/' . $requestedFile;
        if (file_exists($fullPath) && is_readable($fullPath)) {
            $_SESSION['selected_hospital'] = $requestedFile;
        } else {
            error_log("Attempted access to non-existent file: $requestedFile");
        }
    } else {
        error_log("Invalid hospital filename provided: " . ($_GET['hospital'] ?? 'null'));
    }
    
    // Clean redirect to prevent parameter pollution
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $redirectUrl, true, 302);
    exit;
} elseif (isset($_SESSION['selected_hospital'])) {
    $selectedFile = $_SESSION['selected_hospital'];
}

// Final validation of selected file
$validatedFile = sanitizeFilename($selectedFile);
if (!$validatedFile || !file_exists(__DIR__ . '/' . $validatedFile)) {
    $selectedFile = 'hsb.json'; // Secure fallback
    unset($_SESSION['selected_hospital']); // Clear invalid session data
} else {
    $selectedFile = $validatedFile;
}

// Load hospital data and get hospital files list in parallel
$hospital = loadHospitalData($selectedFile);
$hospitalFiles = getHospitalFiles();
$hasError = isset($hospital['error']);

// Optimized helper functions
function formatNumber($number) {
    static $formatter = null;
    if ($formatter === null) {
        $formatter = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
    }
    return $formatter->format($number);
}

function formatDate($date) {
    static $cache = [];
    if (isset($cache[$date])) {
        return $cache[$date];
    }
    $formatted = date('F j, Y', strtotime($date));
    $cache[$date] = $formatted;
    return $formatted;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $hasError ? 'Hospital Information' : htmlspecialchars($hospital['name']); ?> - Comprehensive hospital information and services">
    <title><?php echo $hasError ? 'Hospital Information' : htmlspecialchars($hospital['name']); ?></title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    
    <!-- Critical CSS inline for faster loading -->
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        html { scroll-behavior: smooth; }
        .loading { opacity: 0; }
        .loaded { opacity: 1; transition: opacity 0.3s ease; }
        
        /* Critical animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
        .animate-slideIn { animation: slideIn 0.5s ease-out forwards; }
        
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        
        /* Optimized badge styles */
        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        
        /* Responsive Navbar Styles */
        .mobile-menu { 
            transform: translateX(-100%); 
            transition: transform 0.3s ease-in-out; 
        }
        .mobile-menu.active { 
            transform: translateX(0); 
        }
        
        /* Touch-friendly elements */
        .touch-target { 
            min-height: 44px; 
            min-width: 44px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        /* Hamburger menu animation */
        .hamburger-line { 
            transition: all 0.3s ease; 
        }
        .hamburger.active .hamburger-line:nth-child(1) { 
            transform: rotate(45deg) translate(5px, 5px); 
        }
        .hamburger.active .hamburger-line:nth-child(2) { 
            opacity: 0; 
        }
        .hamburger.active .hamburger-line:nth-child(3) { 
            transform: rotate(-45deg) translate(7px, -6px); 
        }
        
        /* Responsive breakpoints */
        @media (max-width: 768px) {
            .navbar-brand { font-size: 1.25rem; }
            .navbar-subtitle { font-size: 0.75rem; }
            .hospital-selector { 
                font-size: 0.875rem; 
                padding: 0.5rem 2rem 0.5rem 0.75rem; 
            }
        }
        
        @media (max-width: 480px) {
            .navbar-brand { font-size: 1.125rem; }
            .navbar-subtitle { display: none; }
            .hospital-selector { 
                font-size: 0.8rem; 
                padding: 0.375rem 1.5rem 0.375rem 0.5rem; 
                max-width: 200px; 
            }
        }
    </style>
    
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Load Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Load Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 antialiased loading" id="mainBody">
    
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 lg:py-4">
            <div class="flex items-center justify-between">
                <!-- Brand Section -->
                <div class="flex items-center space-x-3 animate-slideIn flex-shrink-0">
                    <i class="fas fa-hospital text-2xl lg:text-3xl"></i>
                    <div class="min-w-0">
                        <h1 class="navbar-brand text-xl lg:text-2xl font-bold truncate"><?php echo htmlspecialchars($hospital['short_name']); ?></h1>
                        <p class="navbar-subtitle text-xs lg:text-sm opacity-90 truncate"><?php echo htmlspecialchars($hospital['name']); ?></p>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-6">
                    <!-- Hospital Selector -->
                    <div class="relative">
                        <form id="hospitalSelectorForm" method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <select 
                                name="hospital"
                                id="hospitalSelector" 
                                onchange="this.form.submit()"
                                class="hospital-selector bg-white bg-opacity-20 text-white border border-white border-opacity-30 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 appearance-none cursor-pointer touch-target"
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
                        </form>
                        <i class="fas fa-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-white pointer-events-none"></i>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="flex space-x-6">
                        <a href="#overview" class="hover:text-gray-200 transition touch-target">Overview</a>
                        <a href="#services" class="hover:text-gray-200 transition touch-target">Services</a>
                        <a href="#contact" class="hover:text-gray-200 transition touch-target">Contact</a>
                        <a href="#statistics" class="hover:text-gray-200 transition touch-target">Statistics</a>
                    </div>
                </div>
                
                <!-- Mobile/Tablet Navigation -->
                <div class="lg:hidden flex items-center space-x-3">
                    <!-- Mobile Hospital Selector -->
                    <div class="relative">
                        <form id="mobileHospitalSelectorForm" method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <select 
                                name="hospital"
                                id="mobileHospitalSelector" 
                                onchange="this.form.submit()"
                                class="hospital-selector bg-white bg-opacity-20 text-white border border-white border-opacity-30 rounded-lg px-3 py-2 pr-6 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 appearance-none cursor-pointer touch-target text-sm"
                            >
                                <?php foreach ($hospitalFiles as $file): ?>
                                    <option 
                                        value="<?php echo htmlspecialchars($file['filename']); ?>" 
                                        <?php echo ($file['filename'] === $selectedFile) ? 'selected' : ''; ?>
                                        class="text-gray-800"
                                    >
                                        <?php echo htmlspecialchars($file['short_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <i class="fas fa-chevron-down absolute right-1 top-1/2 transform -translate-y-1/2 text-white pointer-events-none text-xs"></i>
                    </div>
                    
                    <!-- Hamburger Menu Button -->
                    <button 
                        id="mobileMenuToggle" 
                        class="hamburger touch-target p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition-colors"
                        onclick="toggleMobileMenu()"
                        aria-label="Toggle navigation menu"
                    >
                        <div class="w-6 h-5 flex flex-col justify-between">
                            <span class="hamburger-line w-full h-0.5 bg-white rounded"></span>
                            <span class="hamburger-line w-full h-0.5 bg-white rounded"></span>
                            <span class="hamburger-line w-full h-0.5 bg-white rounded"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div 
            id="mobileMenuOverlay" 
            class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 hidden"
            onclick="closeMobileMenu()"
        ></div>
        
        <!-- Mobile Menu -->
        <div 
            id="mobileMenu" 
            class="mobile-menu lg:hidden fixed top-0 left-0 h-full w-80 max-w-sm bg-white shadow-xl z-50"
        >
            <div class="p-6">
                <!-- Mobile Menu Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-hospital text-2xl text-indigo-600"></i>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($hospital['short_name']); ?></h2>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($hospital['name']); ?></p>
                        </div>
                    </div>
                    <button 
                        onclick="closeMobileMenu()" 
                        class="touch-target p-2 rounded-lg hover:bg-gray-100 transition-colors"
                        aria-label="Close menu"
                    >
                        <i class="fas fa-times text-xl text-gray-600"></i>
                    </button>
                </div>
                
                <!-- Mobile Navigation Links -->
                <nav class="space-y-4">
                    <a href="#overview" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors touch-target">
                        <i class="fas fa-info-circle text-indigo-600 w-5"></i>
                        <span class="text-gray-900 font-medium">Overview</span>
                    </a>
                    <a href="#services" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors touch-target">
                        <i class="fas fa-stethoscope text-indigo-600 w-5"></i>
                        <span class="text-gray-900 font-medium">Services</span>
                    </a>
                    <a href="#contact" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors touch-target">
                        <i class="fas fa-phone text-indigo-600 w-5"></i>
                        <span class="text-gray-900 font-medium">Contact</span>
                    </a>
                    <a href="#statistics" onclick="closeMobileMenu()" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors touch-target">
                        <i class="fas fa-chart-bar text-indigo-600 w-5"></i>
                        <span class="text-gray-900 font-medium">Statistics</span>
                    </a>
                </nav>
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

    <!-- Optimized JavaScript - Load at end for better performance -->
    <script>
        // Optimized DOM ready and performance improvements
        (function() {
            'use strict';
            
            // Performance monitoring
            const perfStart = performance.now();
            
            // Optimized search functionality with debouncing
            let searchTimeout;
            const searchCache = new Map();
            
            function performSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = document.getElementById('searchInput').value.toLowerCase().trim();
                    
                    if (searchCache.has(query)) {
                        applySearchResults(searchCache.get(query));
                        return;
                    }
                    
                    if (query === '') {
                        clearSearch();
                        return;
                    }
                    
                    const results = {
                        services: [],
                        specialties: []
                    };
                    
                    // Search services
                    document.querySelectorAll('.service-item').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        const isMatch = text.includes(query);
                        results.services.push({ element: item, match: isMatch });
                        item.style.display = isMatch ? 'flex' : 'none';
                    });
                    
                    // Search specialties
                    document.querySelectorAll('.specialty-item').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        const isMatch = text.includes(query);
                        results.specialties.push({ element: item, match: isMatch });
                        item.style.display = isMatch ? 'flex' : 'none';
                    });
                    
                    searchCache.set(query, results);
                    applySearchResults(results);
                }, 150); // Debounce delay
            }
            
            function applySearchResults(results) {
                results.services.forEach(({ element, match }) => {
                    element.style.display = match ? 'flex' : 'none';
                });
                results.specialties.forEach(({ element, match }) => {
                    element.style.display = match ? 'flex' : 'none';
                });
            }
            
            function clearSearch() {
                document.getElementById('searchInput').value = '';
                document.querySelectorAll('.service-item, .specialty-item').forEach(item => {
                    item.style.display = 'flex';
                });
                searchCache.clear();
            }
            
            // Optimized smooth scroll with passive listeners
            function initSmoothScroll() {
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
                    }, { passive: false });
                });
            }
            
            // Optimized intersection observer for animations
            function initAnimations() {
                if (!('IntersectionObserver' in window)) return;
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('loaded');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                document.querySelectorAll('.animate-fadeIn').forEach(el => {
                    observer.observe(el);
                });
            }
            
            // Hospital switching with CSRF protection (deprecated - now uses forms)
            window.switchHospital = function(filename) {
                console.warn('switchHospital function is deprecated. Hospital switching now uses secure forms with CSRF protection.');
                return false;
            };
            
            // Mobile menu functionality
            window.toggleMobileMenu = function() {
                const menu = document.getElementById('mobileMenu');
                const overlay = document.getElementById('mobileMenuOverlay');
                const hamburger = document.getElementById('mobileMenuToggle');
                
                if (menu && overlay && hamburger) {
                    const isActive = menu.classList.contains('active');
                    
                    if (isActive) {
                        closeMobileMenu();
                    } else {
                        openMobileMenu();
                    }
                }
            };
            
            window.openMobileMenu = function() {
                const menu = document.getElementById('mobileMenu');
                const overlay = document.getElementById('mobileMenuOverlay');
                const hamburger = document.getElementById('mobileMenuToggle');
                
                if (menu && overlay && hamburger) {
                    menu.classList.add('active');
                    overlay.classList.remove('hidden');
                    hamburger.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                }
            };
            
            window.closeMobileMenu = function() {
                const menu = document.getElementById('mobileMenu');
                const overlay = document.getElementById('mobileMenuOverlay');
                const hamburger = document.getElementById('mobileMenuToggle');
                
                if (menu && overlay && hamburger) {
                    menu.classList.remove('active');
                    overlay.classList.add('hidden');
                    hamburger.classList.remove('active');
                    document.body.style.overflow = ''; // Restore scrolling
                }
            };
            
            // Handle escape key to close mobile menu
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileMenu();
                }
            });
            
            // Handle window resize to close mobile menu on desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) { // lg breakpoint
                    closeMobileMenu();
                }
            });
            
            // Initialize everything when DOM is ready
            function init() {
                // Make search functions globally available
                window.performSearch = performSearch;
                window.clearSearch = clearSearch;
                
                initSmoothScroll();
                initAnimations();
                
                // Show body with fade-in effect
                const body = document.getElementById('mainBody');
                if (body) {
                    body.classList.remove('loading');
                    body.classList.add('loaded');
                }
                
                // Performance logging
                const perfEnd = performance.now();
                console.log(`Hospital display initialized in ${(perfEnd - perfStart).toFixed(2)}ms`);
            }
            
            // Use more efficient DOM ready detection
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                searchCache.clear();
            });
            
        })();
    </script>
    
    <?php endif; ?>
</body>
</html>

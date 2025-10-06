<?php
// Fetch data from Google Sheets
$sheetUrl = "https://docs.google.com/spreadsheets/d/1kQCJUxtPevd9D2XIPMphQHymwah-O5at_o3Pokj6gWw/export?format=csv&gid=0";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$csvData = @file_get_contents($sheetUrl, false, $context);
$hospitals = [];

if ($csvData !== FALSE) {
    $tempFile = tempnam(sys_get_temp_dir(), 'csv');
    file_put_contents($tempFile, $csvData);
    
    $handle = fopen($tempFile, 'r');
    $allRows = [];
    
    while (($row = fgetcsv($handle, 10000, ',', '"')) !== FALSE) {
        $allRows[] = $row;
    }
    
    fclose($handle);
    unlink($tempFile);
    
    $headerIndex = -1;
    $headers = null;
    
    foreach ($allRows as $index => $row) {
        if (isset($row[0]) && trim($row[0]) === 'PTJ') {
            $headerIndex = $index;
            $headers = array_map('trim', $row);
            break;
        }
    }
    
    if ($headerIndex !== -1 && $headers) {
        $dataRows = array_slice($allRows, $headerIndex + 1);
        
        foreach ($dataRows as $row) {
            if (empty(array_filter($row, fn($val) => !empty(trim($val ?? ''))))) {
                continue;
            }
            
            if (!isset($row[0]) || empty(trim($row[0]))) {
                continue;
            }
            
            while (count($row) < count($headers)) {
                $row[] = '';
            }
            
            $row = array_slice($row, 0, count($headers));
            
            $cleanRow = array_map(function($val) {
                $val = trim($val ?? '');
                return $val;
            }, $row);
            
            
            $hospitals[] = array_combine($headers, $cleanRow);
        }
    }
}

// Get selected hospital
$selectedIndex = isset($_GET['hospital']) ? (int)$_GET['hospital'] : 0;
$hospitalData = $hospitals[$selectedIndex] ?? [];

// Map CSV data to hs.php structure
$hospital = [
    'hospital_id' => $selectedIndex + 1,
    'name' => $hospitalData['PTJ'] ?? 'Hospital Name',
    'short_name' => $hospitalData['PTJ'] ?? '',
    'type' => 'Government',
    'category' => 'Public',
    'status' => 'Active',
    'established_date' => $hospitalData['Tahun Operasi'] ?? '1985',
    'address' => [
        'street' => $hospitalData['Alamat'] ?? '',
        'city' => $hospitalData['Daerah'] ?? '',
        'state' => 'Kedah',
        'postcode' => $hospitalData['Poskod'] ?? '',
        'country' => 'Malaysia'
    ],
    'contact' => [
        'phone' => $hospitalData['No. Tel'] ?? 'N/A',
        'fax' => $hospitalData['No. Faks'] ?? 'N/A',
        'email' => strtolower(str_replace(' ', '', $hospitalData['PTJ'] ?? 'info')) . '@moh.gov.my',
        'website' => 'https://www.moh.gov.my',
        'emergency' => $hospitalData['No. Tel'] ?? 'N/A'
    ],
    'facilities' => [
        'total_beds' => intval($hospitalData['Jumlah katil'] ?? 0),
        'icu_beds' => rand(10, 50),
        'emergency_beds' => rand(20, 80),
        'operating_rooms' => rand(5, 15),
        'parking_spaces' => rand(100, 500)
    ],
    'statistics' => [
        'annual_admissions' => rand(10000, 50000),
        'bed_occupancy_rate' => floatval(preg_replace('/[^0-9.]/', '', $hospitalData['BOR'] ?? '0')),
        'average_stay_days' => floatval(preg_replace('/[^0-9.]/', '', $hospitalData['ALOS'] ?? '0'))
    ],
    'departments' => [
        'Perubatan Am',
        'Pembedahan Am',
        'Ortopedik',
        'Pediatrik',
        'O&G',
        'Anest',
        'ORL',
        'Oftal',
        'Psy',
        'OMF',
        'Kecemasan',
        'Pergigian Pediatrik'
    ],
    'services' => [
        'Forensik',
        'Anatomical Pathology Microbiology Hematology',
        'Radiology',
        'Transfusi',
        'Dietetik',
        'Fisioterapi',
        'Pemulihan Cara Kerja',
        'Kerja Sosial'
    ],
    'administration' => [
        'director' => strtoupper($hospitalData['Ketua PTJ'] ?? 'N/A'),
        'deputy_director' => $hospitalData['Timbalan Pengarah'] ?? 'N/A',
        'doctors' => rand(20, 100),
        'nurses' => rand(50, 300),
        'support_staff' => rand(30, 150),
        'total_staff' => (intval($hospitalData['Staff Tetap'] ?? 0) + intval($hospitalData['Staff Kontrak'] ?? 0))
    ],
    'cluster' => $hospitalData['Kluster'] ?? '',
    'land_area' => $hospitalData['Keluasan Tapak (Ekar)'] ?? '',
    'updated_at' => date('Y-m-d H:i:s')
];
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

<!-- Hospital Selector -->
<div class="bg-white shadow-md border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex items-center gap-4">
            <i class="fas fa-hospital-alt text-blue-600 text-xl"></i>
            <label class="text-sm font-semibold text-gray-700">Select Hospital:</label>
            <select onchange="window.location.href='?hospital='+this.value" class="flex-1 max-w-xl px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <?php foreach ($hospitals as $index => $hosp): ?>
                    <option value="<?php echo $index; ?>" <?php echo $index === $selectedIndex ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($hosp['PTJ'] ?? "Hospital $index"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="text-sm text-gray-500">(<?php echo count($hospitals); ?> hospitals)</span>
        </div>
    </div>
</div>

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
                
                <div class="flex items-center gap-2 text-blue-100 mb-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Tahun Operasi: <?php echo $hospital['established_date'] ?? '1985'; ?></span>
                </div>
                
                <?php 
                $clusterData = $hospital['cluster'] ?? '';
                if (!empty($clusterData) && strtoupper($clusterData) !== 'NA'): 
                ?>
                <div class="flex items-center gap-2 text-blue-100 mb-2">
                    <i class="fas fa-layer-group"></i>
                    <span>Kluster: <?php echo $clusterData; ?></span>
                </div>
                <?php endif; ?>

                <?php 
                $landAreaData = $hospital['land_area'] ?? '';
                if (!empty($landAreaData) && strtoupper($landAreaData) !== 'NA'): 
                ?>
                <div class="flex items-center gap-2 text-blue-100">
                    <i class="fas fa-ruler-combined"></i>
                    <span>Keluasan Tanah: <?php echo $landAreaData; ?> Ekar</span>
                </div>
                <?php endif; ?>


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
            <div class="text-xs text-gray-500">
                <i class="fas fa-user-md mr-1"></i> Total Beds 
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
            <div class="text-xs text-gray-500">
                <i class="fas fa-user-md mr-1"></i> Healthcare professionals on duty
            </div>
        </div>

        <!-- Bed Occupancy Rate -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-l-4 border-purple-500 animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-purple-100 rounded-2xl p-4">
                    <i class="fas fa-user-injured text-purple-600 text-3xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">BOR</p>
                    <h3 class="text-4xl font-bold text-purple-600">
                        <?php echo number_format($hospital['statistics']['bed_occupancy_rate'] ?? 0, 2); ?>%
                    </h3>
                </div>
            </div>
            <div class="text-xs text-gray-500">
                <i class="fas fa-chart-line mr-1"></i> Bed Occupancy Rate
            </div>
        </div>

        <!-- Average Length of Stay -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-l-4 border-orange-500 animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-orange-100 rounded-2xl p-4">
                    <i class="fas fa-percentage text-orange-600 text-3xl"></i>
                </div>
                <div class="text-right">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">ALOS</p>
                    <h3 class="text-4xl font-bold text-orange-600">
                        <?php echo $hospital['statistics']['average_stay_days'] ?? 0; ?> %
                    </h3>
                </div>
            </div>
            <div class="text-xs text-gray-500">
                <i class="fas fa-chart-line mr-1"></i> Average Length of Stay
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Sidebar -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Administration Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-pink-100 rounded-lg p-2">
                        <i class="fas fa-user-tie text-pink-600"></i>
                    </div>
                    Administration
                </h2>
                
                <div class="mb-5 p-4 bg-gradient-to-r from-pink-50 to-purple-50 rounded-xl border border-pink-100">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-1">Ketua PTJ</p>
                    <p class="text-lg font-bold text-gray-800"><?php echo $hospital['administration']['director'] ?? 'N/A'; ?></p>
                </div>

                <div class="mb-5 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border border-blue-100">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-1">Timbalan Ketua PTJ</p>
                    <div class="text-sm font-bold text-gray-800"><?php 
                        $deputyDirector = $hospital['administration']['deputy_director'] ?? 'N/A';
                        if ($deputyDirector !== 'N/A' && !empty(trim($deputyDirector))) {
                            // Convert line breaks to HTML <br> tags
                            echo nl2br(htmlspecialchars($deputyDirector, ENT_QUOTES, 'UTF-8'));
                        } else {
                            echo 'N/A';
                        }
                    ?></div>
                </div>

                <!--
                <div class="grid grid-cols-3 gap-4">
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
                    -->
            </div>

            <!-- Facilities Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-cyan-100 rounded-lg p-2">
                        <i class="fas fa-hospital-symbol text-cyan-600"></i>
                    </div>
                    Key Facilities
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 text-center">
                        <i class="fas fa-procedures text-blue-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $hospital['facilities']['operating_rooms'] ?? 0; ?></p>
                        <p class="text-xs text-gray-600 font-medium">OR Rooms</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 text-center">
                        <i class="fas fa-parking text-green-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $hospital['facilities']['parking_spaces'] ?? 0; ?></p>
                        <p class="text-xs text-gray-600 font-medium">Parking</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Perkhidmatan Kepakaran Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-indigo-100 rounded-lg p-2">
                        <i class="fas fa-building text-indigo-600"></i>
                    </div>
                    Perkhidmatan Kepakaran
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php 
                    // Only show departments that have "Ada" status
                    $departmentFields = [
                        'Perubatan Am', 'Pembedahan Am', 'Ortopedik', 'Pediatrik', 'O&G', 
                        'Anest', 'ORL', 'Oftal', 'Psy', 'OMF', 'Kecemasan', 'Pergigian Pediatrik'
                    ];
                    
                    $availableDepartments = [];
                    foreach ($departmentFields as $field) {
                        if (isset($hospitalData[$field]) && strtolower(trim($hospitalData[$field])) === 'ada') {
                            $availableDepartments[] = $field;
                        }
                    }
                    
                    if (!empty($availableDepartments)): 
                        foreach ($availableDepartments as $dept): 
                    ?>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition-all duration-300">
                        <div class="bg-indigo-100 rounded-lg p-2">
                            <i class="fas fa-check text-indigo-600"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?php echo $dept; ?></span>
                    </div>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                    <div class="col-span-full text-center py-8">
                        <div class="bg-gray-100 rounded-lg p-4 inline-block">
                            <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                            <p class="text-gray-500 font-medium">Tiada maklumat perkhidmatan kepakaran tersedia</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sokongan Klinikal Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h2 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <div class="bg-teal-100 rounded-lg p-2">
                        <i class="fas fa-stethoscope text-teal-600"></i>
                    </div>
                    Sokongan Klinikal
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php 
                    // Only show services that have "Ada" status
                    $serviceFields = [
                        'Forensik', 'Anatomical Pathology Microbiology Hematology', 'Radiology', 'Transfusi', 
                        'Farmasi', 'Dietetik', 'Fisioterapi', 'Pemulihan cara Kerja', 'Kerja Sosial'
                    ];
                    
                    $availableServices = [];
                    foreach ($serviceFields as $field) {
                        if (isset($hospitalData[$field]) && strtolower(trim($hospitalData[$field])) === 'ada') {
                            $availableServices[] = $field;
                        }
                    }
                    
                    if (!empty($availableServices)): 
                        foreach ($availableServices as $service): 
                    ?>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-teal-50 hover:border-teal-200 border border-transparent transition-all duration-300">
                        <div class="bg-teal-100 rounded-lg p-2">
                            <i class="fas fa-heartbeat text-teal-600"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?php echo $service; ?></span>
                    </div>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                    <div class="col-span-full text-center py-8">
                        <div class="bg-gray-100 rounded-lg p-4 inline-block">
                            <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                            <p class="text-gray-500 font-medium">Tiada maklumat sokongan klinikal tersedia</p>
                        </div>
                    </div>
                    <?php endif; ?>
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

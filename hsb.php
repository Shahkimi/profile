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
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 50%, #06b6d4 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

<?php
// Load JSON data
$jsonFile = 'sample.json';
$hospital = [];

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $hospital = json_decode($jsonContent, true);
}
?>

<!-- Hero Header Section -->
<div class="gradient-bg text-white py-12 px-4 shadow-xl">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex-1">
                <div class="inline-block bg-white/20 backdrop-blur-sm px-4 py-1 rounded-full text-sm mb-3">
                    <?php echo $hospital['short_name'] ?? ''; ?>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-3">
                    <?php echo $hospital['name'] ?? 'Hospital Name'; ?>
                </h1>
                <p class="text-blue-100 text-lg flex items-center gap-2">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo $hospital['district'] ?? ''; ?>, <?php echo $hospital['address']['state'] ?? ''; ?>
                </p>
                <p class="text-blue-100 mt-2 flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    Established: <?php echo $hospital['established_date'] ?? 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-10">
    
    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Staff -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total Staff</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">
                        <?php echo number_format($hospital['administration']['total_staff'] ?? 0); ?>
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">
                        Contract: <?php echo number_format($hospital['administration']['contract_staff'] ?? 0); ?>
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Annual Admissions -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-t-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Annual Admissions</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">
                        <?php echo number_format($hospital['statistics']['annual_admissions'] ?? 0); ?>
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Patients per year</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-user-injured text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Outpatients -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Annual Outpatients</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">
                        <?php echo number_format($hospital['statistics']['annual_outpatients'] ?? 0); ?>
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Visits per year</p>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <i class="fas fa-procedures text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Bed Occupancy -->
        <div class="bg-white rounded-2xl shadow-lg p-6 card-hover border-t-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Bed Occupancy</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">
                        <?php echo $hospital['statistics']['bed_occupancy_rate'] ?? 0; ?>%
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Utilization rate</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4">
                    <i class="fas fa-bed text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        
        <!-- Left Column: Contact & Location -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Contact Information -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-address-card text-blue-600"></i>
                    Contact Information
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-phone text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Phone</p>
                            <a href="tel:<?php echo $hospital['contact']['phone'] ?? ''; ?>" class="text-gray-800 hover:text-blue-600 transition">
                                <?php echo $hospital['contact']['phone'] ?? 'N/A'; ?>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-fax text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Fax</p>
                            <p class="text-gray-800"><?php echo $hospital['contact']['fax'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-globe text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Website</p>
                            <a href="<?php echo $hospital['contact']['website'] ?? '#'; ?>" target="_blank" class="text-blue-600 hover:underline break-all">
                                <?php echo $hospital['contact']['website'] ?? 'N/A'; ?>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Address</p>
                            <p class="text-gray-800">
                                <?php echo $hospital['address']['street'] ?? ''; ?>,<br>
                                <?php echo $hospital['address']['postcode'] ?? ''; ?> <?php echo $hospital['address']['city'] ?? ''; ?>,<br>
                                <?php echo $hospital['address']['state'] ?? ''; ?>, <?php echo $hospital['address']['country'] ?? ''; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-blue-600"></i>
                    Operating Hours
                </h2>
                <div class="space-y-3">
                    <div class="bg-red-50 rounded-lg p-3 border-l-4 border-red-500">
                        <p class="text-xs text-gray-500 uppercase font-medium">Emergency</p>
                        <p class="text-red-700 font-semibold"><?php echo $hospital['operating_hours']['emergency'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 border-l-4 border-blue-500">
                        <p class="text-xs text-gray-500 uppercase font-medium">Outpatient (Weekdays)</p>
                        <p class="text-gray-800"><?php echo $hospital['operating_hours']['outpatient']['weekdays'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 border-l-4 border-blue-500">
                        <p class="text-xs text-gray-500 uppercase font-medium">Outpatient (Weekends)</p>
                        <p class="text-gray-800"><?php echo $hospital['operating_hours']['outpatient']['weekends'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 border-l-4 border-green-500">
                        <p class="text-xs text-gray-500 uppercase font-medium">Visiting Hours</p>
                        <p class="text-gray-800"><?php echo $hospital['operating_hours']['visiting_hours'] ?? 'N/A'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Site Area -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                <h2 class="text-xl font-bold mb-2 flex items-center gap-2">
                    <i class="fas fa-building"></i>
                    Site Area
                </h2>
                <p class="text-4xl font-bold"><?php echo $hospital['site_area'] ?? 'N/A'; ?></p>
            </div>

        </div>

        <!-- Right Column: Services & Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Medical Specialties -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-stethoscope text-blue-600"></i>
                    Medical Specialties
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php if (!empty($hospital['specialties'])): ?>
                        <?php foreach ($hospital['specialties'] as $specialty): ?>
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200 hover:shadow-md transition">
                                <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                                <span class="text-gray-700 text-sm"><?php echo htmlspecialchars($specialty); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 col-span-3">No specialties available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Clinical Support Services -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-hands-helping text-blue-600"></i>
                    Clinical Support Services
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php if (!empty($hospital['clinical_support'])): ?>
                        <?php foreach ($hospital['clinical_support'] as $service): ?>
                            <div class="flex items-start gap-3 bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition">
                                <i class="fas fa-chevron-right text-green-500 mt-1 text-xs"></i>
                                <span class="text-gray-700 text-sm"><?php echo htmlspecialchars($service); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 col-span-2">No clinical support services available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-600"></i>
                    Annual Statistics
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                        <i class="fas fa-procedures text-blue-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($hospital['statistics']['annual_surgeries'] ?? 0); ?>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">Annual Surgeries</p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                        <i class="fas fa-calendar-check text-purple-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo $hospital['statistics']['average_stay_days'] ?? 0; ?>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">Avg. Stay (Days)</p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl col-span-2 md:col-span-1">
                        <i class="fas fa-percentage text-green-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo $hospital['statistics']['bed_occupancy_rate'] ?? 0; ?>%
                        </p>
                        <p class="text-xs text-gray-600 mt-1">Bed Occupancy</p>
                    </div>
                </div>
            </div>

            <!-- Administration -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-tie text-blue-600"></i>
                    Administration
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Director</p>
                        <p class="text-gray-800 font-semibold"><?php echo $hospital['administration']['director'] ?? 'TBA'; ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Deputy Director 1</p>
                        <p class="text-gray-800 font-semibold"><?php echo $hospital['administration']['deputy_director1'] ?? 'TBA'; ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Deputy Director 2</p>
                        <p class="text-gray-800 font-semibold"><?php echo $hospital['administration']['deputy_director2'] ?? 'TBA'; ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Deputy Director 3</p>
                        <p class="text-gray-800 font-semibold"><?php echo $hospital['administration']['deputy_director3'] ?? 'TBA'; ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Map Section -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-map text-blue-600"></i>
            Location Map
        </h2>
        <div class="bg-gray-100 rounded-xl overflow-hidden" style="height: 400px;">
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
        <div class="mt-4 flex items-center justify-between flex-wrap gap-4">
            <div class="text-sm text-gray-600">
                <i class="fas fa-compass mr-2 text-blue-600"></i>
                <strong>Coordinates:</strong> 
                <?php echo $hospital['address']['coordinates']['latitude'] ?? 0; ?>, 
                <?php echo $hospital['address']['coordinates']['longitude'] ?? 0; ?>
            </div>
            <a href="https://www.google.com/maps?q=<?php echo $hospital['address']['coordinates']['latitude'] ?? 0; ?>,<?php echo $hospital['address']['coordinates']['longitude'] ?? 0; ?>" 
               target="_blank" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-external-link-alt"></i>
                Open in Google Maps
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
        <p class="text-gray-600 text-sm">
            <i class="fas fa-calendar mr-2"></i>
            Last Updated: <?php echo date('d M Y, H:i', strtotime($hospital['updated_at'] ?? 'now')); ?>
        </p>
    </div>

</div>

</body>
</html>

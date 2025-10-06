<?php
// Debug script to find the "Timbalan Pengarah" column in CSV data

$sheetUrl = 'https://docs.google.com/spreadsheets/d/1kQCJUxtPevd9D2XIPMphQHymwah-O5at_o3Pokj6gWw/export?format=csv&gid=0';

// Fetch CSV data
$csvData = file_get_contents($sheetUrl);
if ($csvData === false) {
    die("Failed to fetch CSV data\n");
}

// Parse CSV data
$lines = explode("\n", $csvData);
$headers = str_getcsv($lines[0]);

echo "=== CSV Headers Analysis ===\n";
echo "Total columns: " . count($headers) . "\n\n";

// Find the "Timbalan Pengarah" column
$timbalanPengarahColumnIndex = -1;
foreach ($headers as $index => $header) {
    if (stripos($header, 'Timbalan') !== false && stripos($header, 'Pengarah') !== false) {
        $timbalanPengarahColumnIndex = $index;
        echo "Found 'Timbalan Pengarah' column at index: $index\n";
        echo "Column name: '$header'\n\n";
        break;
    }
}

if ($timbalanPengarahColumnIndex === -1) {
    echo "Searching for similar columns containing 'Timbalan' or 'Pengarah':\n";
    foreach ($headers as $index => $header) {
        if (stripos($header, 'Timbalan') !== false || stripos($header, 'Pengarah') !== false) {
            echo "Index $index: '$header'\n";
        }
    }
} else {
    // Show sample data for this column
    echo "=== Sample Data for Timbalan Pengarah Column ===\n";
    for ($i = 1; $i <= min(5, count($lines) - 1); $i++) {
        if (!empty(trim($lines[$i]))) {
            $rowData = str_getcsv($lines[$i]);
            if (isset($rowData[$timbalanPengarahColumnIndex])) {
                $hospitalName = isset($rowData[0]) ? $rowData[0] : 'Unknown';
                $timbalanPengarah = $rowData[$timbalanPengarahColumnIndex];
                echo "Hospital: $hospitalName | Timbalan Pengarah: '$timbalanPengarah'\n";
            }
        }
    }
}

// Also check for "Hospital Sultanah Bahiyah" specifically
echo "\n=== Checking Hospital Sultanah Bahiyah ===\n";
foreach ($lines as $lineNum => $line) {
    if (stripos($line, 'Hospital Sultanah Bahiyah') !== false) {
        $rowData = str_getcsv($line);
        if ($timbalanPengarahColumnIndex !== -1 && isset($rowData[$timbalanPengarahColumnIndex])) {
            echo "Hospital Sultanah Bahiyah Timbalan Pengarah: '" . $rowData[$timbalanPengarahColumnIndex] . "'\n";
        }
        break;
    }
}
?>
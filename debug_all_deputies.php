<?php
// Debug script to check all deputy director data
$sheetUrl = 'https://docs.google.com/spreadsheets/d/1kQCJUxtPevd9D2XIPMphQHymwah-O5at_o3Pokj6gWw/export?format=csv&gid=0';
$csvData = file_get_contents($sheetUrl);

if ($csvData === false) {
    die('Failed to fetch CSV data');
}

$lines = explode("\n", $csvData);
$headers = str_getcsv($lines[0]);

// Find the Timbalan Pengarah column
$deputyIndex = -1;
foreach ($headers as $index => $header) {
    if (stripos($header, 'Timbalan Pengarah') !== false) {
        $deputyIndex = $index;
        break;
    }
}

if ($deputyIndex === -1) {
    die('Timbalan Pengarah column not found');
}

echo "Checking all hospitals for deputy director data...\n\n";

// Check all hospitals
foreach ($lines as $lineNum => $line) {
    if ($lineNum === 0) continue; // Skip header
    
    $data = str_getcsv($line);
    if (isset($data[0]) && !empty($data[0])) {
        $hospitalName = $data[0];
        $deputyData = $data[$deputyIndex] ?? '';
        
        if (!empty($deputyData)) {
            echo "Hospital: $hospitalName\n";
            echo "Deputy data length: " . strlen($deputyData) . "\n";
            echo "Contains \\n: " . (strpos($deputyData, "\n") !== false ? 'YES' : 'NO') . "\n";
            echo "Contains \\r: " . (strpos($deputyData, "\r") !== false ? 'YES' : 'NO') . "\n";
            echo "Raw data: '" . substr($deputyData, 0, 200) . "'\n";
            echo "---\n";
            
            // If this has line breaks, show more detail
            if (strpos($deputyData, "\n") !== false || strpos($deputyData, "\r") !== false) {
                echo "FOUND MULTI-LINE DATA!\n";
                echo "Full data: '$deputyData'\n";
                echo "Split by \\n:\n";
                $splitLines = explode("\n", $deputyData);
                foreach ($splitLines as $i => $splitLine) {
                    echo "  [$i]: '$splitLine'\n";
                }
                break;
            }
        }
    }
}
?>
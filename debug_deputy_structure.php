<?php
// Debug script to check the structure of deputy director data
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

echo "Timbalan Pengarah column found at index: $deputyIndex\n";
echo "Column header: " . $headers[$deputyIndex] . "\n\n";

// Check Hospital Sultanah Bahiyah data
foreach ($lines as $lineNum => $line) {
    if ($lineNum === 0) continue; // Skip header
    
    $data = str_getcsv($line);
    if (isset($data[0]) && stripos($data[0], 'Hospital Sultanah Bahiyah') !== false) {
        echo "Found Hospital Sultanah Bahiyah at line $lineNum\n";
        echo "Raw deputy director data:\n";
        echo "'" . ($data[$deputyIndex] ?? 'NOT FOUND') . "'\n\n";
        
        // Show the structure
        $deputyData = $data[$deputyIndex] ?? '';
        echo "Length: " . strlen($deputyData) . "\n";
        echo "Contains \\n: " . (strpos($deputyData, "\n") !== false ? 'YES' : 'NO') . "\n";
        echo "Contains \\r: " . (strpos($deputyData, "\r") !== false ? 'YES' : 'NO') . "\n";
        
        // Show each character
        echo "Character breakdown:\n";
        for ($i = 0; $i < strlen($deputyData); $i++) {
            $char = $deputyData[$i];
            $ascii = ord($char);
            echo "[$i] '$char' (ASCII: $ascii)\n";
            if ($i > 200) { // Limit output
                echo "... (truncated)\n";
                break;
            }
        }
        break;
    }
}
?>
<?php
header('Content-Type: application/json');

try {
    $sheetUrl = "https://docs.google.com/spreadsheets/d/1kQCJUxtPevd9D2XIPMphQHymwah-O5at_o3Pokj6gWw/export?format=csv&gid=0";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $csvData = @file_get_contents($sheetUrl, false, $context);
    
    if ($csvData === FALSE) {
        throw new Exception("Failed to fetch data. Check if spreadsheet is publicly accessible.");
    }
    
    if (stripos($csvData, '<!DOCTYPE html>') !== false || stripos($csvData, '<html') !== false) {
        throw new Exception("Received HTML instead of CSV. Spreadsheet must be set to 'Anyone with the link' in sharing settings.");
    }
    
    // Parse CSV properly with fgetcsv to handle quoted multi-line fields
    $tempFile = tempnam(sys_get_temp_dir(), 'csv');
    file_put_contents($tempFile, $csvData);
    
    $handle = fopen($tempFile, 'r');
    $allRows = [];
    
    // Read all rows including empty ones to preserve structure
    while (($row = fgetcsv($handle, 10000, ',', '"')) !== FALSE) {
        $allRows[] = $row;
    }
    
    fclose($handle);
    unlink($tempFile);
    
    if (empty($allRows)) {
        throw new Exception("No rows found in CSV file.");
    }
    
    // Find the header row (row with "PTJ", "Daerah", "Alamat", etc.)
    $headerIndex = -1;
    $headers = null;
    
    foreach ($allRows as $index => $row) {
        if (isset($row[0]) && trim($row[0]) === 'PTJ') {
            $headerIndex = $index;
            $headers = array_map('trim', $row);
            break;
        }
    }
    
    if ($headerIndex === -1 || !$headers) {
        throw new Exception("Could not find header row with 'PTJ' column.");
    }
    
    // Get data rows (everything after header row)
    $dataRows = array_slice($allRows, $headerIndex + 1);
    
    // Filter valid data rows (rows that start with hospital name)
    $hospitals = [];
    
    foreach ($dataRows as $row) {
        // Skip empty rows
        if (empty(array_filter($row, fn($val) => !empty(trim($val ?? ''))))) {
            continue;
        }
        
        // Only process rows that have hospital name in first column
        if (!isset($row[0]) || empty(trim($row[0]))) {
            continue;
        }
        
        // Pad row to match header count
        while (count($row) < count($headers)) {
            $row[] = '';
        }
        $row = array_slice($row, 0, count($headers));
        
        // Clean values: trim and convert line breaks to spaces
        $cleanRow = array_map(function($val) {
            $val = trim($val ?? '');
            // Replace internal line breaks with space
            $val = preg_replace('/\s*[\r\n]+\s*/', ' ', $val);
            return trim($val);
        }, $row);
        
        $hospitals[] = array_combine($headers, $cleanRow);
    }
    
    if (empty($hospitals)) {
        throw new Exception("No valid hospital data found. Found " . count($dataRows) . " rows after headers.");
    }
    
    $response = [
        'status' => 'success',
        'message' => 'Data retrieved successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'total_records' => count($hospitals),
            'hospitals' => $hospitals
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => null
    ], JSON_PRETTY_PRINT);
}
?>

<?php
// api/index.php

// 1. Get the requested path
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl = parse_url($uri);
$path = $parsedUrl['path'] ?? '/';

// 2. Normalize and check if we are accessing the root or index.php
if ($path === '/' || $path === '/index.php' || $path === '') {
    $targetFile = dirname(__DIR__) . '/index.php';
    $path = '/index.php';
} else {
    // 3. Otherwise, check for the PHP file in the parent directory
    // Prevent directory traversal attacks
    $cleanPath = str_replace(['..', "\0"], '', $path);
    $targetFile = dirname(__DIR__) . $cleanPath;
}

// 4. If the target file exists and is a PHP file, execute it
if (file_exists($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    // Override PHP_SELF, SCRIPT_NAME, and SCRIPT_FILENAME so target files behave correctly
    $_SERVER['PHP_SELF'] = $path;
    $_SERVER['SCRIPT_NAME'] = $path;
    $_SERVER['SCRIPT_FILENAME'] = $targetFile;
    
    // Set current working directory to the target file's directory so relative includes work perfectly
    chdir(dirname($targetFile));
    
    require_once $targetFile;
    exit;
}

// 5. Fallback if the file doesn't exist
http_response_code(404);
echo "404 Not Found";

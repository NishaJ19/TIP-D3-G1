<?php
session_start();

// Set the upload directory and ensure it exists
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function logMessage($message) {
    file_put_contents('process_file.log', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// Check if a file has been uploaded
if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
    $file_name = basename($_FILES['file']['name']);
    $upload_file = $upload_dir . $file_name;

    logMessage("Attempting to upload file: " . $file_name);

    // Move the uploaded file to the upload directory
    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
        logMessage("File uploaded successfully: " . $upload_file);

        // Convert Windows path to WSL path
        $wsl_script_path = '/mnt/c/xampp/htdocs/projectbu/scan_file.py';
        $wsl_upload_path = '/mnt/c/xampp/htdocs/projectbu/' . $upload_file;

        // Use WSL to run the Python script
        $command = escapeshellcmd("wsl python3 " . escapeshellarg($wsl_script_path) . " " . escapeshellarg($wsl_upload_path));
        logMessage("Executing command: " . $command);

        $output = shell_exec($command . " 2>&1"); // Capture both stdout and stderr
        logMessage("Python script raw output: " . $output);

        // Parse the output from the Python script
        $scan_results = json_decode($output, true);

        // Include the provided snippet here to handle scan results and set `fresh_results` flag
        if ($scan_results && !isset($scan_results['error'])) {
            $_SESSION['scan_results'] = $scan_results;
            $_SESSION['fresh_results'] = true;  // Set this flag to indicate fresh results
            header("Location: malware_analysis.php?scan=success");
            exit();
        } else {
            $error_message = isset($scan_results['error']) ? $scan_results['error'] : "Unknown error occurred. Raw output: " . $output;
            $_SESSION['error'] = "Failed to scan the file. Error: " . $error_message;
            header("Location: malware_analysis.php?scan=error");
            exit();
        }
    } else {
        $_SESSION['error'] = "Failed to upload the file. Please try again.";
        logMessage("Error: File upload failed.");
        header("Location: malware_analysis.php?scan=error");
        exit();
    }
} else {
    $_SESSION['error'] = "No file uploaded.";
    logMessage("Error: No file was uploaded.");
    header("Location: malware_analysis.php?scan=no_file");
    exit();
}
?>

<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    echo "Access denied! This page is for admin users only.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management - CyberSec ML Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Section -->
        <div class="sidebar">
            <h2 class="sidebar-title">CyberSec ML Platform</h2>
            <ul>
                <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="overview.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="malware_analysis.php"><i class="fas fa-shield-alt"></i> Malware Analysis</a></li>
                <li><a href="cps_analysis.php"><i class="fas fa-chart-line"></i> CPS Analysis</a></li>
                <li><a href="data_management.php" class="active"><i class="fas fa-database"></i> Data Management</a></li>
                <li><a href="alerts_and_incidents.php"><i class="fas fa-bell"></i> Alerts and Incidents</a></li>
                <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings and Configuration</a></li>
            </ul>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content Section -->
        <div class="content">
            <h1>Data Management</h1>
            <p>Manage and explore the datasets used in the CyberSec ML Platform for malware analysis, anomaly detection, and attack detection.</p>
            
            <!-- Section for BATADAL Dataset -->
            <h2>BATADAL_dataset04</h2>
            <div class="explanation">
                The <strong>BATADAL</strong> (Battle of the Attack Detection Algorithms) dataset is used to simulate cyber-physical system attacks on a water distribution system. 
                This dataset includes various physical and cyber features like flow rates, tank levels, and sensor data to train anomaly detection models.
            </div>
            <ul>
                <li><strong>Usage:</strong> Anomaly detection in water distribution systems.</li>
                <li><strong>File Name:</strong> BATADAL_dataset04.csv</li>
                <li><strong>Parameters:</strong> Tank levels (L_T1 to L_T7), Pump operations (PU*), PLC controls, and flow rates.</li>
            </ul>

            <!-- Section for UNSW-NB15 Dataset -->
            <h2>UNSW_NB15_testing-set</h2>
            <div class="explanation">
                The <strong>UNSW-NB15</strong> dataset contains network traffic data that is commonly used to train machine learning models for network intrusion detection systems (NIDS). 
                It includes features like flow duration, TCP flags, and content features to classify normal and malicious network behavior.
            </div>
            <ul>
                <li><strong>Usage:</strong> Network traffic analysis and malware classification.</li>
                <li><strong>File Name:</strong> UNSW_NB15_testing-set.csv</li>
                <li><strong>Parameters:</strong> Flow duration, service types, source/destination IPs, TCP flags, and more.</li>
            </ul>

            <!-- File Upload Section -->
            <h2>Upload New Dataset</h2>
            <div class="upload-section">
                <form action="upload_dataset.php" method="post" enctype="multipart/form-data">
                    <label for="file-upload" class="custom-file-upload">
                        <i class="fas fa-cloud-upload-alt"></i> Choose File
                    </label>
                    <input id="file-upload" type="file" name="fileToUpload">
                    <button type="submit" class="btn-submit"><i class="fas fa-upload"></i> Upload Dataset</button>
                </form>
            </div>
            
            <!-- List of Current Datasets -->
            <h2>Current Datasets</h2>
            <table>
                <tr>
                    <th>Dataset Name</th>
                    <th>File Name</th>
                    <th>Last Modified</th>
                </tr>
                <tr>
                    <td>BATADAL Dataset</td>
                    <td>BATADAL_dataset04.csv</td>
                    <td><?php echo file_exists('BATADAL_dataset04.csv') ? date("Y-m-d H:i:s", filemtime('BATADAL_dataset04.csv')) : 'File not found'; ?></td>
                </tr>
                <tr>
                    <td>UNSW-NB15 Testing Set</td>
                    <td>UNSW_NB15_testing-set.csv</td>
                    <td><?php echo file_exists('UNSW_NB15_testing-set.csv') ? date("Y-m-d H:i:s", filemtime('UNSW_NB15_testing-set.csv')) : 'File not found'; ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

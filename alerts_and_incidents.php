<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Assume anomalies and recommendations are loaded from a CPS analysis JSON file
$cps_results_file = 'cps_results.json';
$cps_results = file_exists($cps_results_file) ? json_decode(file_get_contents($cps_results_file), true) : null;

$alerts = [];
$recommendations = [];

if ($cps_results) {
    foreach ($cps_results['real_time_status'] as $param => $status) {
        if ($status['status'] == 'Anomaly') {
            $alerts[] = "Anomaly detected in {$param}. Current value: {$status['current']}.";
            $recommendations[] = "Recommended: Investigate the anomaly in {$param} and adjust settings accordingly.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts and Incidents - CyberSec ML Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Basic tab styling */
        .tabs {
            display: flex;
            justify-content: space-around;
            background-color: #444;
            color: white;
        }

        .tab {
            padding: 15px;
            cursor: pointer;
            flex: 1;
            text-align: center;
        }

        .tab.active {
            background-color: #1976d2;
            font-weight: bold;
        }

        .alert-content {
            display: none;
        }

        .alert-content.active {
            display: block;
        }

        /* Badge style */
        .badge {
            background-color: orange;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Section -->
<div class="sidebar">
    <h2 class="sidebar-title">CyberSec ML Platform</h2>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="malware_analysis.php"><i class="fas fa-shield-alt"></i> Malware Analysis</a></li>

        <!-- Only show these links to admin users -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="cps_analysis.php"><i class="fas fa-chart-line"></i> CPS Analysis</a></li>
            <li><a href="data_management.php"><i class="fas fa-database"></i> Data Management</a></li>
            <li><a href="alerts_and_incidents.php"><i class="fas fa-bell"></i> Alerts and Incidents</a></li>
            <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings and Configuration</a></li>
        <?php endif; ?>
    </ul>
    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>


        <!-- Main Content Section -->
        <div class="content">
            <h1>Alerts</h1>
            <p>Stay updated with the latest alerts and recommendations based on system behavior and threat analysis.</p>

            <!-- Tabs for General and Recommended Alerts -->
            <div class="alerts-section">
                <div class="tabs">
                    <div class="tab active" id="general-tab">
                        <span>General</span> <span class="badge"><?php echo count($alerts); ?></span>
                    </div>
                    <div class="tab" id="recommended-tab">
                        <span>Recommended</span> <span class="badge"><?php echo count($recommendations); ?></span>
                    </div>
                </div>

                <!-- General Alerts Section -->
                <div id="general-alerts" class="alert-content active">
                    <?php if (count($alerts) > 0): ?>
                        <?php foreach ($alerts as $alert): ?>
                            <div class="alert-item">
                                <p>⚠️ <?php echo htmlspecialchars($alert); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No alerts at the moment.</p>
                    <?php endif; ?>
                </div>

                <!-- Recommended Alerts Section -->
                <div id="recommended-alerts" class="alert-content">
                    <?php if (count($recommendations) > 0): ?>
                        <?php foreach ($recommendations as $recommendation): ?>
                            <div class="alert-item">
                                <p>🔔 <?php echo htmlspecialchars($recommendation); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recommendations available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to handle tab switching -->
    <script>
        document.getElementById('general-tab').addEventListener('click', function () {
            document.getElementById('general-alerts').classList.add('active');
            document.getElementById('recommended-alerts').classList.remove('active');
            this.classList.add('active');
            document.getElementById('recommended-tab').classList.remove('active');
        });

        document.getElementById('recommended-tab').addEventListener('click', function () {
            document.getElementById('recommended-alerts').classList.add('active');
            document.getElementById('general-alerts').classList.remove('active');
            this.classList.add('active');
            document.getElementById('general-tab').classList.remove('active');
        });
    </script>
</body>
</html>

<?php
// Load settings from a configuration file or define defaults here
$config_file = 'config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [
    'debug_mode' => false,
    'api_key' => '',
    'model_threshold' => 0.5,
    'user' => [
        'notifications' => false,
        'two_factor_auth' => false,
    ],
];

// Handle form submission for updating configurations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update general settings
    $config['debug_mode'] = isset($_POST['debug_mode']) ? true : false;
    $config['api_key'] = $_POST['api_key'] ?? '';
    $config['model_threshold'] = isset($_POST['model_threshold']) ? floatval($_POST['model_threshold']) : 0.5;

    // Update user settings
    $config['user']['notifications'] = isset($_POST['notifications']) ? true : false;
    $config['user']['two_factor_auth'] = isset($_POST['two_factor_auth']) ? true : false;

    // Save the updated configuration back to the config file
    file_put_contents($config_file, json_encode($config));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings and Configuration - CyberSec ML Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <h2 class="sidebar-title">CyberSec ML Platform</h2>
            <ul>
                <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="overview.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="malware_analysis.php"><i class="fas fa-shield-alt"></i> Malware Analysis</a></li>
                <li><a href="cps_analysis.php"><i class="fas fa-chart-line"></i> CPS Analysis</a></li>
                <li><a href="data_management.php"><i class="fas fa-database"></i> Data Management</a></li>
                <li><a href="alerts_and_incidents.php"><i class="fas fa-bell"></i> Alerts and Incidents</a></li>
                <li><a href="settings_and_configuration.php" class="active"><i class="fas fa-cogs"></i> Settings and Configuration</a></li>
            </ul>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Settings and Configuration</h1>

            <form method="POST" action="settings_and_configuration.php">
                <h2>General Settings</h2>
                <div>
                    <label for="debug_mode">Debug Mode</label>
                    <input type="checkbox" id="debug_mode" name="debug_mode" <?php echo $config['debug_mode'] ? 'checked' : ''; ?>>
                </div>
                <div>
                    <label for="api_key">API Key</label>
                    <input type="text" id="api_key" name="api_key" value="<?php echo htmlspecialchars($config['api_key']); ?>">
                </div>
                <div>
                    <label for="model_threshold">Model Threshold</label>
                    <input type="number" id="model_threshold" name="model_threshold" step="0.01" value="<?php echo htmlspecialchars($config['model_threshold']); ?>">
                </div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>

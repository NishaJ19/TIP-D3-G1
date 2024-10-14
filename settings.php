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
    $success_message = "Settings updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CyberSec ML Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        // Function to toggle display of settings sections
        function toggleSettings(sectionId) {
            var section = document.getElementById(sectionId);
            section.style.display = section.style.display === 'block' ? 'none' : 'block';
        }
    </script>
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
                <li><a href="data_management.php"><i class="fas fa-database"></i> Data Management</a></li>
                <li><a href="alerts_and_incidents.php"><i class="fas fa-bell"></i> Alerts and Incidents</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cogs"></i> Settings and Configuration</a></li>
            </ul>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content Section -->
        <div class="content">
            <h1>Settings and Configuration</h1>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <!-- General Settings Section -->
            <button onclick="toggleSettings('generalSettings')" class="settings-btn">General Settings</button>
            <div id="generalSettings" style="display: none;" class="bg-white p-6 rounded-lg shadow-lg mt-4">
                <form method="POST" action="settings.php">
                    <h2>General Settings</h2>
                    <div class="mb-4">
                        <label for="debug_mode" class="block font-bold mb-2">Debug Mode</label>
                        <input type="checkbox" id="debug_mode" name="debug_mode" <?php echo $config['debug_mode'] ? 'checked' : ''; ?>>
                        <span class="text-gray-600">Enable debug mode for detailed logs</span>
                    </div>
                    <div class="mb-4">
                        <label for="api_key" class="block font-bold mb-2">API Key</label>
                        <input type="text" id="api_key" name="api_key" class="p-2 border rounded w-full" value="<?php echo htmlspecialchars($config['api_key']); ?>" placeholder="Enter your API key">
                    </div>
                    <div class="mb-4">
                        <label for="model_threshold" class="block font-bold mb-2">Model Threshold</label>
                        <input type="number" step="0.01" id="model_threshold" name="model_threshold" class="p-2 border rounded w-full" value="<?php echo htmlspecialchars($config['model_threshold']); ?>">
                        <span class="text-gray-600">Set the threshold for model predictions (0.0 - 1.0)</span>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
                </form>
            </div>

            <!-- User Account Settings Section -->
            <button onclick="toggleSettings('userSettings')" class="settings-btn">User Account Settings</button>
            <div id="userSettings" style="display: none;" class="bg-white p-6 rounded-lg shadow-lg mt-4">
                <form method="POST" action="settings.php">
                    <h2>User Account Settings</h2>
                    <div class="mb-4">
                        <label>
                            <input type="checkbox" name="notifications" <?php echo $config['user']['notifications'] ? 'checked' : ''; ?>>
                            Enable Email Notifications
                        </label>
                    </div>
                    <div class="mb-4">
                        <label>
                            <input type="checkbox" name="two_factor_auth" <?php echo $config['user']['two_factor_auth'] ? 'checked' : ''; ?>>
                            Enable Two-Factor Authentication (2FA)
                        </label>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save User Settings</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

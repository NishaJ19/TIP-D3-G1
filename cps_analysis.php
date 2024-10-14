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

// Include the CPS analysis results if available
$cps_results_file = 'cps_results.json';
$cps_results = file_exists($cps_results_file) ? json_decode(file_get_contents($cps_results_file), true) : null;

// Flag to check if CPS analysis is available
$cps_analysis_available = !empty($cps_results);

// Get the last modification time of the results file
$last_updated = file_exists($cps_results_file) ? date("Y-m-d H:i:s", filemtime($cps_results_file)) : "Unknown";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPS Analysis - CyberSec ML Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
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
            <h1>CPS Analysis</h1>
            <p>Monitor real-time system status, detect anomalies, and analyze historical trends of Cyber-Physical Systems.</p>
            <p>Last updated: <?php echo $last_updated; ?></p>
            
            <!-- Real-Time System Status -->
            <h2>Real-Time System Status</h2>
            <div class="explanation">
                This table shows the current values and status of various system parameters. 
                'Normal' indicates the parameter is within expected ranges, while 'Anomaly' suggests unusual behavior that may require attention.
            </div>
            <div class="status-table">
                <table>
                    <tr>
                        <th>Parameter</th>
                        <th>Current Value</th>
                        <th>Status</th>
                    </tr>
                    <?php if ($cps_analysis_available): ?>
                        <?php foreach ($cps_results['real_time_status'] as $param => $value): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($param); ?></td>
                                <td><?php echo htmlspecialchars($value['current']); ?></td>
                                <td class="<?php echo $value['status'] == 'Normal' ? 'status-normal' : 'status-anomaly'; ?>">
                                    <?php echo htmlspecialchars($value['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No real-time status available.</td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Anomaly Detection Results -->
            <h2>Anomaly Detection Results</h2>
            <div class="explanation">
                This section lists any detected anomalies in the system. Each entry shows the time and affected parameters where unusual behavior was observed.
            </div>
            <?php if ($cps_analysis_available && !empty($cps_results['anomalies'])): ?>
                <div class="anomaly-results">
                    <h3>Detected Anomalies:</h3>
                    <ul>
                        <?php foreach ($cps_results['anomalies'] as $anomaly): ?>
                            <li><?php echo htmlspecialchars($anomaly); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p>No anomalies detected.</p>
            <?php endif; ?>

            <!-- System Behavior Predictions -->
            <h2>System Behavior Predictions</h2>
            <div class="explanation">
                These graphs show predictions for future values of tank levels (L_T1 to L_T7). 
                They can help anticipate potential issues or verify if the system is expected to return to normal after an anomaly.
            </div>
            <div id="behavior-predictions-charts"></div>
            
            <script>
                console.log('Script started');
                if (typeof Plotly === 'undefined') {
                    console.error('Plotly library is not loaded');
                    document.getElementById('behavior-predictions-charts').innerHTML = '<p>Error: Unable to load graphing library</p>';
                } else {
                    console.log('Plotly library is loaded');
                    console.log('Fetching CPS results...');
                    fetch('cps_results.json')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('CPS results loaded:', data);
                            if (!data.predictions || Object.keys(data.predictions).length === 0) {
                                throw new Error('No prediction data available');
                            }
                            const chartContainer = document.getElementById('behavior-predictions-charts');
                            Object.entries(data.predictions).forEach(([tank, predictions]) => {
                                console.log(`Processing predictions for ${tank}:`, predictions);
                                const chartDiv = document.createElement('div');
                                chartDiv.id = `${tank}-chart`;
                                chartContainer.appendChild(chartDiv);

                                const trace = {
                                    x: predictions.map(p => p.ds),
                                    y: predictions.map(p => p.yhat),
                                    type: 'scatter',
                                    mode: 'lines',
                                    name: tank
                                };
                                Plotly.newPlot(`${tank}-chart`, [trace], {
                                    title: `${tank} Predictions`,
                                    xaxis: { title: 'Time' },
                                    yaxis: { title: 'Level' }
                                })
                                .then(() => console.log(`Chart for ${tank} plotted successfully`))
                                .catch(err => console.error(`Error plotting chart for ${tank}:`, err));
                            });
                        })
                        .catch(error => {
                            console.error('Error loading or processing CPS results:', error);
                            document.getElementById('behavior-predictions-charts').innerHTML = `<p>Error: ${error.message}</p>`;
                        });
                }
            </script>
        </div>
    </div>
</body>
</html>

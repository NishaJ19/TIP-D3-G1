<?php
// Set paths for JSON files generated by the script
$metrics_file = 'metrics.json';
$plots_file = 'plots.json';
$insights_file = 'insights.json';

// Read the generated JSON files
$metrics = file_exists($metrics_file) ? json_decode(file_get_contents($metrics_file), true) : [];
$plots = file_exists($plots_file) ? json_decode(file_get_contents($plots_file), true) : [];
$insights = file_exists($insights_file) ? json_decode(file_get_contents($insights_file), true) : [];

$model_loaded = !empty($metrics) && !empty($plots) && !empty($insights);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malware Detection - Model Overview</title>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <!-- FontAwesome for Sidebar Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Section (same as home.php) -->
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
            <h1>Malware Detection - Model Overview</h1>

            <?php if (!$model_loaded): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">Model not loaded. Please ensure the model is properly trained and saved.</span>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Top 10 Important Features -->
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <div id="feature-importance-plot"></div>
                    </div>

                    <!-- Confusion Matrix -->
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <div id="confusion-matrix-plot"></div>
                    </div>

                    <!-- Classification Metrics -->
                    <div class="bg-white p-6 rounded-lg shadow-lg col-span-2">
                        <h2 class="text-xl font-bold mb-4">Classification Metrics</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <h3 class="font-semibold">Accuracy</h3>
                                <p class="text-2xl"><?php echo isset($metrics['accuracy']) ? number_format($metrics['accuracy'] * 100, 2) : 'N/A'; ?>%</p>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <h3 class="font-semibold">Precision</h3>
                                <p class="text-2xl"><?php echo isset($metrics['classification_report']['1']['precision']) ? number_format($metrics['classification_report']['1']['precision'] * 100, 2) : 'N/A'; ?>%</p>
                            </div>
                            <div class="p-4 bg-yellow-50 rounded-lg">
                                <h3 class="font-semibold">Recall</h3>
                                <p class="text-2xl"><?php echo isset($metrics['classification_report']['1']['recall']) ? number_format($metrics['classification_report']['1']['recall'] * 100, 2) : 'N/A'; ?>%</p>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg">
                                <h3 class="font-semibold">F1-Score</h3>
                                <p class="text-2xl"><?php echo isset($metrics['classification_report']['1']['f1-score']) ? number_format($metrics['classification_report']['1']['f1-score'] * 100, 2) : 'N/A'; ?>%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dataset Insights -->
                    <div class="bg-white p-6 rounded-lg shadow-lg col-span-2">
                        <h2 class="text-xl font-bold mb-4">Dataset Insights</h2>
                        <?php if (isset($insights['dataset_insights'])): ?>
                            <?php foreach ($insights['dataset_insights'] as $insight_name => $insight_data): ?>
                                <h3 class="font-semibold mt-2"><?php echo $insight_name; ?></h3>
                                <ul class="list-disc list-inside">
                                    <?php foreach ($insight_data as $key => $value): ?>
                                        <li><?php echo "$key: $value"; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No dataset insights available.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Correlation Heatmap -->
                    <div class="bg-white p-6 rounded-lg shadow-lg col-span-2">
                        <h2 class="text-xl font-bold mb-4">Correlation Heatmap</h2>
                        <img src="data:image/png;base64,<?php echo $plots['correlation_heatmap']; ?>" alt="Correlation Heatmap" class="w-full">
                    </div>
                </div>

                <script>
                    // Feature Importance Plot
                    var featureImportancePlot = <?php echo json_encode($plots['feature_importance']); ?>;
                    Plotly.newPlot('feature-importance-plot', featureImportancePlot.data, featureImportancePlot.layout);

                    // Confusion Matrix Plot
                    var confusionMatrixPlot = <?php echo json_encode($plots['confusion_matrix']); ?>;
                    Plotly.newPlot('confusion-matrix-plot', confusionMatrixPlot.data, confusionMatrixPlot.layout);
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

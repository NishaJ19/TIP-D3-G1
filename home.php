<?php
session_start();

// Check if the username and role are set in session; if not, default to 'Guest'
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
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
                <!-- Only show these links to admin users -->
                <?php if ($role === 'admin'): ?>
				
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
            <h1>Welcome back, <?php echo $username; ?>!</h1>
            <p>MALWARE STOPS HERE: SECURE, DETECT, PROTECT</p>

            <h2 class="quick-reads-heading">Quick Reads</h2>
            
            <div class="cards">
                <div class="card">
                    <h3>Tips or Best Practices</h3>
                    <p>Educate yourself on the best practices for maintaining security.</p>
                </div>
                <div class="card">
                    <h3>System Guide</h3>
                    <p>Navigate your security tools with ease—learn how to maximize your system’s protection features.</p>
                </div>
                <div class="card">
                    <h3>Threat Landscape Updates</h3>
                    <p>Stay informed about the latest cyber threats and vulnerabilities affecting your industry.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

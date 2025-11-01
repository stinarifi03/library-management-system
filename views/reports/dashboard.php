<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../models/Reports.php';
require_once '../../models/Book.php';
require_once '../../models/Member.php';
require_once '../../models/BorrowRecord.php';

$reports = new Reports();
$book = new Book();
$member = new Member();
$borrowRecord = new BorrowRecord();

// Get all statistics
$borrowingStats = $reports->getBorrowingStats();
$popularBooks = $reports->getPopularBooks(5);
$activeMembers = $reports->getActiveMembers(5);
$monthlyTrends = $reports->getMonthlyTrends();
$overdueStats = $reports->getOverdueStats();
$genrePopularity = $reports->getGenrePopularity();

// Basic counts
$totalBooks = $book->count();
$totalMembers = $member->count();
$totalBorrowed = $borrowRecord->getBorrowedCount();
$totalFines = $borrowRecord->getTotalOutstandingFines();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .tables-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Analytics Dashboard</h1>
            <a href="../../dashboard.php" class="btn" style="background: #6c757d;">📋 Main Dashboard</a>
        </div>

        <!-- Key Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalBooks; ?></div>
                <div>Total Books</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalMembers; ?></div>
                <div>Total Members</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $borrowingStats['total_borrows']; ?></div>
                <div>Total Borrows</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;">$<?php echo number_format($totalFines, 2); ?></div>
                <div>Outstanding Fines</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-container">
                <h3>📈 Monthly Borrowing Trends</h3>
                <canvas id="monthlyChart" height="250"></canvas>
            </div>
            <div class="chart-container">
                <h3>📚 Genre Popularity</h3>
                <canvas id="genreChart" height="250"></canvas>
            </div>
        </div>

        <!-- Tables -->
        <div class="tables-grid">
            <div class="table-container">
                <h3>⭐ Most Popular Books</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th>Borrows</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($book = $popularBooks->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo $book['borrow_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <h3>👥 Most Active Members</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Borrows</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($member = $activeMembers->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                            <td><?php echo $member['borrow_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="table-container" style="margin-top: 2rem;">
            <h3>📋 Detailed Statistics</h3>
            <table>
                <tr>
                    <td><strong>Active Borrows:</strong></td>
                    <td><?php echo $borrowingStats['active_borrows']; ?> books currently borrowed</td>
                </tr>
                <tr>
                    <td><strong>Completed Borrows:</strong></td>
                    <td><?php echo $borrowingStats['completed_borrows']; ?> books returned</td>
                </tr>
                <tr>
                    <td><strong>Average Borrow Duration:</strong></td>
                    <td><?php echo number_format($borrowingStats['avg_borrow_days'], 1); ?> days</td>
                </tr>
                <tr>
                    <td><strong>Overdue Books:</strong></td>
                    <td style="color: #dc3545;"><?php echo $overdueStats['total_overdue'] ?? 0; ?> books overdue</td>
                </tr>
                <tr>
                    <td><strong>Average Days Overdue:</strong></td>
                    <td><?php echo number_format($overdueStats['avg_days_overdue'] ?? 0, 1); ?> days</td>
                </tr>
            </table>
        </div>

        <br>
        <a href="../../dashboard.php">&larr; Back to Main Dashboard</a>
    </div>

    <script>
        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [<?php 
                    $months = [];
                    while($row = $monthlyTrends->fetch(PDO::FETCH_ASSOC)) {
                        $months[] = "'" . $row['month'] . "'";
                    }
                    echo implode(', ', array_reverse($months));
                ?>],
                datasets: [{
                    label: 'Books Borrowed',
                    data: [<?php 
                        $monthlyTrends = $reports->getMonthlyTrends(); // Reset pointer
                        $counts = [];
                        while($row = $monthlyTrends->fetch(PDO::FETCH_ASSOC)) {
                            $counts[] = $row['borrow_count'];
                        }
                        echo implode(', ', array_reverse($counts));
                    ?>],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Genre Popularity Chart
        const genreCtx = document.getElementById('genreChart').getContext('2d');
        const genreChart = new Chart(genreCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $genres = [];
                    $genreCounts = [];
                    while($row = $genrePopularity->fetch(PDO::FETCH_ASSOC)) {
                        $genres[] = "'" . addslashes($row['genre']) . "'";
                        $genreCounts[] = $row['borrow_count'];
                    }
                    echo implode(', ', $genres);
                ?>],
                datasets: [{
                    data: [<?php echo implode(', ', $genreCounts); ?>],
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
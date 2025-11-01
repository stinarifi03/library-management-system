<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Reports</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .export-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        .export-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .export-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .export-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .btn-export {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-pdf {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
        }
        .btn-pdf:hover {
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>📤 Export Reports</h1>
            <a href="../../dashboard.php" class="btn" style="background: #6c757d;">📊 Dashboard</a>
        </div>

        <p style="text-align: center; color: #666; margin-bottom: 2rem;">
            Generate and download library reports in various formats for analysis and record-keeping.
        </p>

        <div class="export-grid">
            <!-- Books Export -->
            <div class="export-card">
                <div class="export-icon">📚</div>
                <h3>Books Inventory</h3>
                <p>Export complete book catalog with availability status</p>
                <div class="export-actions">
                    <a href="../../controllers/ExportController.php?action=export_books_csv" class="btn-export">
                        📥 Download CSV
                    </a>
                    <a href="../../controllers/ExportController.php?action=export_books_pdf" class="btn-export btn-pdf">
                        📄 Download PDF
                    </a>
                </div>
            </div>

            <!-- Members Export -->
            <div class="export-card">
                <div class="export-icon">👥</div>
                <h3>Members List</h3>
                <p>Export complete member directory with contact information</p>
                <div class="export-actions">
                    <a href="../../controllers/ExportController.php?action=export_members_csv" class="btn-export">
                        📥 Download CSV
                    </a>
                    <a href="../../controllers/ExportController.php?action=export_members_pdf" class="btn-export btn-pdf">
                        📄 Download PDF
                    </a>
                </div>
            </div>

            <!-- Analytics Export -->
            <div class="export-card">
                <div class="export-icon">📊</div>
                <h3>Analytics Report</h3>
                <p>Export comprehensive borrowing statistics and trends</p>
                <div class="export-actions">
                    <a href="../../controllers/ExportController.php?action=export_report_csv" class="btn-export">
                        📥 Download CSV
                    </a>
                    <a href="../../controllers/ExportController.php?action=export_report_pdf" class="btn-export btn-pdf">
                        📄 Download PDF
                    </a>
                </div>
            </div>
        </div>

        <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 10px; margin-top: 2rem;">
            <h4>💡 Export Tips:</h4>
            <ul style="text-align: left; color: #666;">
                <li><strong>CSV Format:</strong> Best for data analysis in Excel or Google Sheets</li>
                <li><strong>PDF Format:</strong> Best for printing and formal reports</li>
                <li>Files are automatically named with today's date</li>
                <li>All data is exported in UTF-8 encoding</li>
            </ul>
        </div>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>
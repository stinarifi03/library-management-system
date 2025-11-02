<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ .'../models/Book.php';
require_once __DIR__ .'../models/Member.php';
require_once __DIR__ .'../models/BorrowRecord.php';
require_once __DIR__ .'../models/Reports.php';

class ExportController {
    
    // Export Books to CSV
    public function exportBooksCSV() {
        $book = new Book();
        $stmt = $book->read();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=library_books_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['ID', 'Title', 'Author', 'ISBN', 'Genre', 'Total Copies', 'Available Copies', 'Status']);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['available_copies'] > 0 ? 'Available' : 'Out of Stock';
            fputcsv($output, [
                $row['id'],
                $row['title'],
                $row['author'],
                $row['isbn'],
                $row['genre'],
                $row['total_copies'],
                $row['available_copies'],
                $status
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    // Export Members to CSV
    public function exportMembersCSV() {
        $member = new Member();
        $stmt = $member->read();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=library_members_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Registration Date']);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['phone'],
                date('Y-m-d', strtotime($row['registration_date']))
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    // Export Borrowing Report to CSV
    public function exportBorrowingReportCSV() {
        $reports = new Reports();
        $stats = $reports->getBorrowingStats();
        $popularBooks = $reports->getPopularBooks(10);
        $activeMembers = $reports->getActiveMembers(10);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=borrowing_report_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Summary Section
        fputcsv($output, ['LIBRARY BORROWING REPORT - ' . date('F Y')]);
        fputcsv($output, []); // Empty line
        fputcsv($output, ['SUMMARY STATISTICS']);
        fputcsv($output, ['Total Borrows', $stats['total_borrows']]);
        fputcsv($output, ['Active Borrows', $stats['active_borrows']]);
        fputcsv($output, ['Completed Borrows', $stats['completed_borrows']]);
        fputcsv($output, ['Average Borrow Duration', round($stats['avg_borrow_days'], 1) . ' days']);
        fputcsv($output, []); // Empty line
        
        // Popular Books Section
        fputcsv($output, ['MOST POPULAR BOOKS']);
        fputcsv($output, ['Rank', 'Book Title', 'Author', 'Borrow Count']);
        
        $rank = 1;
        while ($book = $popularBooks->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $rank,
                $book['title'],
                $book['author'],
                $book['borrow_count']
            ]);
            $rank++;
        }
        
        fputcsv($output, []); // Empty line
        
        // Active Members Section
        fputcsv($output, ['MOST ACTIVE MEMBERS']);
        fputcsv($output, ['Rank', 'Member Name', 'Borrow Count']);
        
        $rank = 1;
        while ($member = $activeMembers->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $rank,
                $member['first_name'] . ' ' . $member['last_name'],
                $member['borrow_count']
            ]);
            $rank++;
        }
        
        fclose($output);
        exit;
    }
    
    // Export Simple PDF Report (HTML-based)
    public function exportSimplePDF($type) {
        // For a real PDF, you'd use TCPDF or DomPDF
        // This creates an HTML "print" version that can be saved as PDF
        ob_start();
        
        switch($type) {
            case 'books':
                $this->generateBooksHTML();
                break;
            case 'members':
                $this->generateMembersHTML();
                break;
            case 'report':
                $this->generateReportHTML();
                break;
        }
        
        $html = ob_get_clean();
        
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.html"');
        
        echo $html;
        exit;
    }
    
    private function generateBooksHTML() {
        $book = new Book();
        $stmt = $book->read();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Library Books Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .available { color: green; }
                .unavailable { color: red; }
            </style>
        </head>
        <body>
            <h1>📚 Library Books Inventory Report</h1>
            <p>Generated on: <?php echo date('F j, Y'); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Genre</th>
                        <th>Total Copies</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($row['genre']); ?></td>
                        <td><?php echo $row['total_copies']; ?></td>
                        <td><?php echo $row['available_copies']; ?></td>
                        <td class="<?php echo $row['available_copies'] > 0 ? 'available' : 'unavailable'; ?>">
                            <?php echo $row['available_copies'] > 0 ? 'Available' : 'Out of Stock'; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <p style="margin-top: 30px; font-size: 12px; color: #666;">
                Report generated by Library Management System
            </p>
        </body>
        </html>
        <?php
    }
    
    private function generateMembersHTML() {
        $member = new Member();
        $stmt = $member->read();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Library Members Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1>👥 Library Members Report</h1>
            <p>Generated on: <?php echo date('F j, Y'); ?></p>
            <p>Total Members: <?php echo $stmt->rowCount(); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['registration_date'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <p style="margin-top: 30px; font-size: 12px; color: #666;">
                Report generated by Library Management System
            </p>
        </body>
        </html>
        <?php
    }
    
    private function generateReportHTML() {
        $reports = new Reports();
        $stats = $reports->getBorrowingStats();
        $popularBooks = $reports->getPopularBooks(5);
        $activeMembers = $reports->getActiveMembers(5);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Library Analytics Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .section { margin: 30px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
                .stat-card { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea; }
            </style>
        </head>
        <body>
            <h1>📊 Library Analytics Report</h1>
            <p>Generated on: <?php echo date('F j, Y'); ?></p>
            
            <div class="section">
                <h2>📈 Key Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Borrows</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #667eea; margin: 0;"><?php echo $stats['total_borrows']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Borrows</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #667eea; margin: 0;"><?php echo $stats['active_borrows']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Completed Borrows</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #667eea; margin: 0;"><?php echo $stats['completed_borrows']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Avg. Duration</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #667eea; margin: 0;"><?php echo round($stats['avg_borrow_days'], 1); ?> days</p>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>⭐ Popular Books</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrow Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = $popularBooks->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo $book['borrow_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="section">
                <h2>👥 Active Members</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Borrow Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($member = $activeMembers->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                            <td><?php echo $member['borrow_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <p style="margin-top: 30px; font-size: 12px; color: #666;">
                Report generated by Library Management System
            </p>
        </body>
        </html>
        <?php
    }
}

// Handle export requests
if(isset($_GET['action'])) {
    $export = new ExportController();
    
    switch($_GET['action']) {
        case 'export_books_csv':
            $export->exportBooksCSV();
            break;
        case 'export_members_csv':
            $export->exportMembersCSV();
            break;
        case 'export_report_csv':
            $export->exportBorrowingReportCSV();
            break;
        case 'export_books_pdf':
            $export->exportSimplePDF('books');
            break;
        case 'export_members_pdf':
            $export->exportSimplePDF('members');
            break;
        case 'export_report_pdf':
            $export->exportSimplePDF('report');
            break;
    }
}
?>
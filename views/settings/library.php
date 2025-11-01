<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../../index.php");
    exit;
}

require_once __DIR__ . '/../../../models/LibrarySettings.php';

$settings = new LibrarySettings();
$all_settings = $settings->getAllSettings();
$message = '';

if(isset($_POST['update_settings'])) {
    foreach($_POST['settings'] as $key => $value) {
        $settings->updateSetting($key, $value);
    }
    $message = "✅ Settings updated successfully!";
    $all_settings = $settings->getAllSettings(); // Refresh settings
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Settings</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #764ba2;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .setting-description {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Library Settings</h1>

        <?php if($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="fine_per_day">Fine Per Day ($)</label>
                <input type="number" id="fine_per_day" name="settings[fine_per_day]" 
                       step="0.01" min="0" max="10" 
                       value="<?php echo htmlspecialchars($all_settings['fine_per_day']['setting_value'] ?? '1.00'); ?>" required>
                <div class="setting-description">Amount charged per day for overdue books</div>
            </div>

            <div class="form-group">
                <label for="max_borrow_days">Maximum Borrow Days</label>
                <input type="number" id="max_borrow_days" name="settings[max_borrow_days]" 
                       min="1" max="90" 
                       value="<?php echo htmlspecialchars($all_settings['max_borrow_days']['setting_value'] ?? '14'); ?>" required>
                <div class="setting-description">Maximum number of days a book can be borrowed</div>
            </div>

            <button type="submit" name="update_settings" class="btn">Save Settings</button>
            <a href="../fines/list.php" class="btn" style="background: #6c757d;">Back to Fines</a>
        </form>

        <br>
        <a href="../../../dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>
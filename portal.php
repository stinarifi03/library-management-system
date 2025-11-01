<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .portal-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
        .portal-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }
        .portal-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .portal-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .admin-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .member-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
            <h1 style="color: #333; margin-bottom: 0.5rem;">Library Management System</h1>
            <p style="color: #666; font-size: 1.2rem;">Choose your portal to continue</p>
        </div>

        <div class="portal-cards">
            <a href="index.php" class="portal-card admin-card">
                <div class="portal-icon">🏢</div>
                <h2>Admin Portal</h2>
                <p>Manage books, members, and library operations</p>
                <div style="margin-top: 1.5rem;">
                    <div class="btn" style="background: rgba(255,255,255,0.2); color: white;">
                        Access Admin Panel
                    </div>
                </div>
            </a>

            <a href="member_login.php" class="portal-card member-card">
                <div class="portal-icon">👤</div>
                <h2>Member Portal</h2>
                <p>Borrow books, view history, and manage your account</p>
                <div style="margin-top: 1.5rem;">
                    <div class="btn" style="background: rgba(255,255,255,0.2); color: white;">
                        Access Member Portal
                    </div>
                </div>
            </a>
        </div>

        <div style="margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 15px;">
            <h3>About Our Library System</h3>
            <p style="color: #666; max-width: 600px; margin: 0 auto;">
                A complete library management solution with separate interfaces for administrators 
                and members. Admins manage the library collection while members enjoy seamless 
                borrowing experiences.
            </p>
        </div>
    </div>
</body>
</html>
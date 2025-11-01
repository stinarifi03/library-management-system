LIBRARY MANAGEMENT SYSTEM
========================

Project Description:
A complete web-based library management system built with PHP, MySQL, HTML, and CSS. 
Includes user authentication, book management, member management, and borrowing/returning functionality.

Features:
- User Authentication (Login/Logout)
- Complete CRUD for Books
- Complete CRUD for Members  
- Book Borrowing & Returning System
- Real-time Dashboard Statistics
- Search Functionality
- Automatic Inventory Management

Technology Stack:
- Frontend: HTML5, CSS3, JavaScript
- Backend: PHP 7.4+
- Database: MySQL
- Server: Apache (MAMP/XAMPP)

Installation Instructions:
1. Import the SQL file from /sql/library_database.sql into phpMyAdmin
2. Place all project files in your web server directory (htdocs for MAMP)
3. Update database credentials in /config/database.php if needed
4. Access the application via http://localhost:8888/library_project/

Default Login:
Username: admin
Password: password

Database Schema:
- books: Library book catalog
- members: Library members
- users: System administrators
- borrow_records: Book borrowing history

CRUD Operations Implemented:
- Books: Create, Read, Update, Delete
- Members: Create, Read, Update, Delete
- Borrowing: Create (borrow), Update (return)

Project Structure:
/library_project/
├── config/database.php
├── controllers/ (All business logic)
├── models/ (Database interactions)
├── views/ (HTML templates)
├── assets/css/style.css
└── sql/library_database.sql

Developed by: [Your Name]
Course: Web Programming
Date: [Current Date]
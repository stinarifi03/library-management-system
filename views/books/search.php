<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once '../../models/Book.php';
require_once '../../models/BorrowRecord.php';

$book = new Book();
$search_results = null;
$filters = [
    'search' => $_GET['search'] ?? '',
    'genre' => $_GET['genre'] ?? '',
    'availability' => $_GET['availability'] ?? '',
    'author' => $_GET['author'] ?? ''
];

// Get unique genres for filter dropdown
$genre_stmt = $book->read();
$genres = [];
while ($row = $genre_stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['genre'])) {
        $genres[$row['genre']] = $row['genre'];
    }
}

// Perform search if any filter is active
if (!empty(array_filter($filters))) {
    $search_results = $book->advancedSearch($filters);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Book Search</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .search-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .book-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .book-cover {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .book-info {
            padding: 1.5rem;
        }
        .book-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .book-author {
            color: #666;
            margin-bottom: 0.5rem;
        }
        .book-details {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .availability {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .available {
            background: #d4edda;
            color: #155724;
        }
        .unavailable {
            background: #f8d7da;
            color: #721c24;
        }
        .clear-filters {
            text-align: center;
            margin-top: 1rem;
        }
        .search-stats {
            background: #e7f3ff;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>🔍 Advanced Book Search</h1>
            <a href="list.php" class="btn" style="background: #6c757d;">📚 View All Books</a>
        </div>

        <!-- Search Filters -->
        <div class="search-header">
            <h3 style="margin-bottom: 1.5rem;">🎯 Search Filters</h3>
            <form method="GET" id="searchForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="filter-label">📖 Title/ISBN</label>
                        <input type="text" name="search" class="form-control" 
                               value="<?php echo htmlspecialchars($filters['search']); ?>" 
                               placeholder="Search by title or ISBN...">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">✍️ Author</label>
                        <input type="text" name="author" class="form-control" 
                               value="<?php echo htmlspecialchars($filters['author']); ?>" 
                               placeholder="Search by author...">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">📚 Genre</label>
                        <select name="genre" class="form-control">
                            <option value="">All Genres</option>
                            <?php foreach($genres as $genre): ?>
                                <option value="<?php echo htmlspecialchars($genre); ?>" 
                                    <?php echo $filters['genre'] === $genre ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($genre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">✅ Availability</label>
                        <select name="availability" class="form-control">
                            <option value="">All Books</option>
                            <option value="available" <?php echo $filters['availability'] === 'available' ? 'selected' : ''; ?>>Available Only</option>
                            <option value="unavailable" <?php echo $filters['availability'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable Only</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        🔍 Search Books
                    </button>
                    <button type="button" onclick="clearFilters()" class="btn" style="background: #6c757d;">
                        🗑️ Clear Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <?php if(isset($search_results)): ?>
            <div class="search-stats">
                <strong>
                    📊 Found <?php echo $search_results->rowCount(); ?> book(s) 
                    matching your search criteria
                    <?php if(!empty(array_filter($filters))): ?>
                        • <a href="javascript:void(0)" onclick="clearFilters()" style="color: #667eea;">Clear all</a>
                    <?php endif; ?>
                </strong>
            </div>

            <?php if($search_results->rowCount() > 0): ?>
                <div class="results-grid">
                    <?php while ($row = $search_results->fetch(PDO::FETCH_ASSOC)): 
                        $is_available = $row['available_copies'] > 0;
                    ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if(!empty($row['cover_image'])): ?>
                                <img src="../../uploads/<?php echo htmlspecialchars($row['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php else: ?>
                                <div style="color: #6c757d; font-size: 3rem;">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <div class="book-title"><?php echo htmlspecialchars($row['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($row['author']); ?></div>
                            <div class="book-details">
                                <span><?php echo htmlspecialchars($row['genre']); ?></span>
                                <span class="availability <?php echo $is_available ? 'available' : 'unavailable'; ?>">
                                    <?php echo $is_available ? 'Available' : 'Out of Stock'; ?>
                                </span>
                            </div>
                            <div class="book-details">
                                <span>ISBN: <?php echo htmlspecialchars($row['isbn']); ?></span>
                                <span><?php echo $row['available_copies']; ?>/<?php echo $row['total_copies']; ?> copies</span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 15px;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                    <h3>No books found</h3>
                    <p>Try adjusting your search criteria or <a href="javascript:void(0)" onclick="clearFilters()">clear all filters</a>.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; background: white; border-radius: 15px;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                <h3>Start Searching</h3>
                <p>Use the filters above to find books in the library.</p>
            </div>
        <?php endif; ?>

        <br>
        <a href="../../dashboard.php">&larr; Back to Dashboard</a>
    </div>

    <script>
        function clearFilters() {
            document.getElementById('searchForm').reset();
            window.location.href = 'search.php';
        }

        // Auto-submit form when select fields change
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        });
    </script>
</body>
</html>
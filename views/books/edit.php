<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ .'../../models/Book.php';

$book = new Book();
$book->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Fetch the book data
if(!$book->readOne()) {
    header("Location: list.php?error=Book not found");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input, select, textarea {
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
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #764ba2;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .current-image {
            max-width: 200px;
            margin: 1rem 0;
            border: 2px solid #ddd;
            padding: 1rem;
            text-align: center;
        }
        .current-image img {
            max-width: 100%;
        }
        .image-preview {
            max-width: 200px;
            margin: 1rem 0;
            border: 2px dashed #ddd;
            padding: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Edit Book</h1>

        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="../../controllers/BookController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $book->id; ?>">
            
            <?php if(!empty($book->cover_image)): ?>
                <div class="current-image">
                    <p><strong>Current Cover:</strong></p>
                    <img src="../../uploads/<?php echo htmlspecialchars($book->cover_image); ?>" 
                         alt="<?php echo htmlspecialchars($book->title); ?>">
                    <input type="hidden" name="existing_cover" value="<?php echo htmlspecialchars($book->cover_image); ?>">
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title">Book Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book->title); ?>" required>
            </div>

            <div class="form-group">
                <label for="author">Author *</label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book->author); ?>" required>
            </div>

            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book->isbn); ?>">
            </div>

            <div class="form-group">
                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($book->genre); ?>">
            </div>

            <div class="form-group">
                <label for="total_copies">Total Copies *</label>
                <input type="number" id="total_copies" name="total_copies" value="<?php echo $book->total_copies; ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="available_copies">Available Copies *</label>
                <input type="number" id="available_copies" name="available_copies" value="<?php echo $book->available_copies; ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="cover_image">Update Cover Image</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*">
                <small>Leave empty to keep current image. Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
            </div>

            <div class="image-preview" id="imagePreview">
                <span>New Image Preview</span>
            </div>

            <button type="submit" name="update_book" class="btn">Update Book</button>
            <a href="list.php" class="btn" style="background: #6c757d;">Cancel</a>
        </form>

        <script>
            // Image preview functionality
            const coverImage = document.getElementById('cover_image');
            const preview = document.getElementById('imagePreview');

            coverImage.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.addEventListener('load', function() {
                        preview.innerHTML = `<img src="${this.result}" alt="Preview">`;
                    });
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '<span>New Image Preview</span>';
                }
            });
        </script>
    </div>
</body>
</html>
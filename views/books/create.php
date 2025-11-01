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
    <title>Add New Book</title>
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
        .image-preview {
            max-width: 200px;
            max-height: 300px;
            margin: 1rem 0;
            border: 2px dashed #ddd;
            padding: 1rem;
            text-align: center;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 250px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Add New Book</h1>

        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="../../controllers/BookController.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Book Title *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="author">Author *</label>
                <input type="text" id="author" name="author" required>
            </div>

            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn">
            </div>

            <div class="form-group">
                <label for="genre">Genre</label>
                <input type="text" id="genre" name="genre" placeholder="e.g., Fiction, Science, Technology">
            </div>

            <div class="form-group">
                <label for="total_copies">Total Copies *</label>
                <input type="number" id="total_copies" name="total_copies" value="1" min="1" required>
            </div>

            <div class="form-group">
                <label for="cover_image">Book Cover Image</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*">
                <small>Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
            </div>

            <div class="image-preview" id="imagePreview">
                <span>Image Preview</span>
            </div>

            <input type="hidden" name="available_copies" id="available_copies">

            <button type="submit" name="create_book" class="btn">Add Book</button>
            <a href="list.php" class="btn" style="background: #6c757d;">Cancel</a>
        </form>

        <script>
            // Set available copies equal to total copies by default
            document.getElementById('total_copies').addEventListener('input', function() {
                document.getElementById('available_copies').value = this.value;
            });
            document.getElementById('available_copies').value = document.getElementById('total_copies').value;

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
                    preview.innerHTML = '<span>Image Preview</span>';
                }
            });
        </script>
    </div>
</body>
</html>
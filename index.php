<?php
$uploads_dir = 'uploads';

if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Função para carregar uploads
function load_uploads() {
    return json_decode(file_get_contents('uploads.json'), true) ?? [];
}

// Função para salvar uploads
function save_uploads($uploads) {
    file_put_contents('uploads.json', json_encode($uploads));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['description'])) {
        // Handle file upload
        $description = $_POST['description'];
        $file = $_FILES['file'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_name = basename($file['name']);
            $file_path = "$uploads_dir/$file_name";
            move_uploaded_file($file['tmp_name'], $file_path);

            $uploads = load_uploads();
            $uploads[] = ['filename' => $file_name, 'description' => $description];
            save_uploads($uploads);
        }
    } elseif (isset($_POST['delete'])) {
        // Handle file deletion
        $file_name = $_POST['delete'];
        $uploads = load_uploads();
        foreach ($uploads as $key => $upload) {
            if ($upload['filename'] === $file_name) {
                unlink("$uploads_dir/$file_name"); // Delete file from uploads directory
                unset($uploads[$key]); // Remove from uploads array
                break;
            }
        }
        save_uploads($uploads);
    }
}

$uploads = load_uploads();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Imagens e Vídeos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main>
    <h1>Upload de Imagens e Vídeos</h1>
    <form class="upload-form" action="index.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*,video/*" required>
        <input type="text" name="description" placeholder="Descrição" required>
        <button type="submit">Upload</button>
    </form>
    <div class="uploads">
        <?php foreach ($uploads as $upload): ?>
            <div class="upload-item">
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $upload['filename'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($upload['filename']); ?>" alt="<?php echo htmlspecialchars($upload['description']); ?>">
                <?php elseif (preg_match('/\.(mp4|webm|ogg)$/i', $upload['filename'])): ?>
                    <video controls>
                        <source src="uploads/<?php echo htmlspecialchars($upload['filename']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($upload['description']); ?></p>
                <form action="index.php" method="post" style="display:inline;">
                    <input type="hidden" name="delete" value="<?php echo htmlspecialchars($upload['filename']); ?>">
                    <button type="submit">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
  </main>
</body>
</html>

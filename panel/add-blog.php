<?php
session_start();
require_once('./class/auth.php');

$auth = new Auth();
$auth->is_login();

include './config/loader.php';
// List writers
$writer_query = "SELECT * FROM users WHERE role = ?";
$stmt = $conn->prepare($writer_query);
$stmt->bindValue(1, 'writer', PDO::PARAM_STR);
$stmt->execute();
$writers = $stmt->fetchAll(PDO::FETCH_ASSOC);


// List cat
$writer_query = "SELECT * FROM categories";
$stmt = $conn->query($writer_query);
$stmt->execute();
$cat_lists = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $target_dir = "../uploads/";
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . "file_" . time() . "_set_" . $file_name;
    $target_name = "file_" . time() . "_set_" . $file_name;
    $uploadOk = true;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        header("location: ./add-blog.php?file_format=no&message=Only JPG, JPEG, PNG, and GIF files are allowed.");
        $uploadOk = false;
    }

    // Validate file size
    if ($_FILES["fileToUpload"]["size"] > 50000000) {
        header("location: ./add-blog.php?file_large=ok&message=File size exceeds limit.");
        $uploadOk = false;
    }

    // Validate image
    if (getimagesize($_FILES["fileToUpload"]["tmp_name"]) === false) {
        header("location: ./add-blog.php?error=ok&message=File is not a valid image.");
        $uploadOk = false;
    }

    // Check for upload errors
    if ($uploadOk) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $image_s =str_replace('../', '/', $target_file);
            try {
                if (isset($_POST['writer']) && isset($_POST['title']) && isset($_POST['slug']) && isset($_POST['content'])) {
                    $query = "INSERT INTO articles (user_id, category_id, title, slug, img, content, create_time) VALUES (?, ?, ?, ?, ?, ?,?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindValue(1, $_POST['writer'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $_POST['category'], PDO::PARAM_INT);
                    $stmt->bindValue(3, $_POST['title'], PDO::PARAM_STR);
                    $stmt->bindValue(4, $_POST['slug'], PDO::PARAM_STR);
                    $stmt->bindValue(5, $image_s, PDO::PARAM_STR);
                    $stmt->bindValue(6, $_POST['content'], PDO::PARAM_STR);
                    $stmt->bindValue(7, time(), PDO::PARAM_INT);

                    $stmt->execute();

                    header("location: ./add-blog.php?file_upload=ok&url_file=" .$_ENV['site_url'] .$_POST['slug']);
                } else {
                    header("location: ./add-blog.php?error=missing_data&message=Required fields are missing.");
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                header("location: ./add-blog.php?error=db&message=Database error.");
            }
        } else {
            header("location: ./add-blog.php?error=upload_failed&message=File upload failed.");
        }
    }
}

$title = 'Upload File';
?>

<?php include 'header-main.php'; ?>
<?php require_once('./config/alerts.php'); ?>

<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.1.0/ckeditor5.css" />

<form class="space-y-5" method="post" enctype="multipart/form-data">
    <div class="flex sm:flex-row flex-col">
        <label for="title" class="mb-0 sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Title</label>
        <input id="title" name="title" type="text" placeholder="What's WordPress?" class="form-input flex-1" required />
    </div>

    <div class="flex sm:flex-row flex-col">
        <label for="slug" class="mb-0 sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Slug</label>
        <input id="slug" name="slug" type="text" placeholder="whats-wordpress" class="form-input flex-1" required />
    </div>

    <div style="padding-top: 20px !important;">
        <label for="content" class="mb-0 sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Content</label>
        <textarea name="content" id="editor" class="form-input flex-1" ></textarea>
    </div>

    <div class="flex sm:flex-row flex-col">
        <label for="fileToUpload" class="mb-0 sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Image</label>
        <input id="fileToUpload" name="fileToUpload" type="file" class="form-input flex-1" required />
    </div>

    <div class="flex sm:flex-row flex-col">
        <label class="sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Choose category</label>
        <select name="category" class="form-select flex-1" required>
            <?php foreach ($cat_lists as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <?php if($_SESSION['role']=='admin'):?>

    <div class="flex sm:flex-row flex-col">
        <label class="sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Choose Writer</label>
        <select name="writer" class="form-select flex-1" required>
            <?php foreach ($writers as $writer): ?>
                <option value="<?= $writer['id'] ?>"><?= $writer['username'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
<?php endif; ?>
    <button name="submit" type="submit" class="btn btn-primary !mt-6">Save</button>
</form>
<script src="https://cdn.ckeditor.com/ckeditor5/44.1.0/ckeditor5.umd.js"></script>

<script>
    const { ClassicEditor, Essentials, Bold, Italic, Font, Paragraph } = CKEDITOR;

    ClassicEditor.create(document.querySelector('#editor'), {
        licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3NjY5NjYzOTksImp0aSI6IjA4MjgyN2I0LTliMmUtNGFmOS04NDBmLTg2MGM4ZGJjMTg3NyIsImxpY2Vuc2VkSG9zdHMiOlsiMTI3LjAuMC4xIiwibG9jYWxob3N0IiwiMTkyLjE2OC4qLioiLCIxMC4qLiouKiIsIjE3Mi4qLiouKiIsIioudGVzdCIsIioubG9jYWxob3N0IiwiKi5sb2NhbCJdLCJ1c2FnZUVuZHBvaW50IjoiaHR0cHM6Ly9wcm94eS1ldmVudC5ja2VkaXRvci5jb20iLCJkaXN0cmlidXRpb25DaGFubmVsIjpbImNsb3VkIiwiZHJ1cGFsIl0sImxpY2Vuc2VUeXBlIjoiZGV2ZWxvcG1lbnQiLCJmZWF0dXJlcyI6WyJEUlVQIl0sInZjIjoiZTMxZjVlNGEifQ.6u0tqpkRBaPYknHs1b8TiU-LAb0x0UqrZ2AwO9Eq3b0ovV07E9KlGCku4Q2An36BNmKGpaWHhHwVlTk14aXCCw',
        plugins: [Essentials, Bold, Italic, Font, Paragraph],
        toolbar: ['undo', 'redo', '|', 'bold', 'italic', '|', 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor']
    }).catch(error => console.error(error));
</script>

<?php include 'footer-main.php'; ?>

<?php
session_start();

require_once('./class/auth.php');

$auth = new Auth();

$auth->is_login();
include './config/loader.php';

if ($_SESSION['role'] == 'admin') {
    $query_all_file = "SELECT * FROM `articles` ";
    $result = $conn->query($query_all_file);
    $result->execute();
    $blog_lists = $result->fetchAll(PDO::FETCH_OBJ);

}
$i = 1;

//delete file
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $user_id = $_SESSION['user_id'];
        $is_admin = $_SESSION['role'] === 'admin';

        // بررسی وجود فایل در دیتابیس
        $query_verify = "SELECT * FROM files WHERE id = ?";
        $stmt_verify = $conn->prepare($query_verify);
        $stmt_verify->bindValue(1, $id, PDO::PARAM_INT);
        $stmt_verify->execute();
        $file_data = $stmt_verify->fetch(PDO::FETCH_ASSOC);

        if ($file_data) {
            $file_path = '../uploads/' . $file_data['file_name'];


            // بررسی دسترسی کاربر به فایل
            if ($file_data['user_id'] === $user_id || $is_admin) {
                $query_delete = "DELETE FROM files WHERE id = ?";
                $stmt_delete = $conn->prepare($query_delete);
                $stmt_delete->bindValue(1, $id, PDO::PARAM_INT);
                $stmt_delete->execute();
                // بررسی وجود فایل در سرور
                if (is_file($file_path)) {
                    unlink($file_path); // حذف فایل از سرور
                } else {
                    header('location: ./lists-blogs.php?error-ok&message=File not found on server');
                    exit();
                }

                header('location: ./lists-blogs.php?error=ok&message=File deleted successfully');
                exit();
            } else {
                header('location: ./lists-blogs.php?error=ok&message=Access denied');
                exit();
            }
        } else {
            header('location: ./lists-blogs.php?error=ok&message=File not found in database');
            exit();
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}


$title = 'uploaded files';

?>
<?php include 'header-main.php'; ?>

<!-- start main content section -->
<div class="panel mt-6">
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <h5 class="text-lg font-semibold dark:text-white-light">all articles in script</h5>
    <?php elseif ($_SESSION['role'] == 'user'): ?>
        <h5 class="text-lg font-semibold dark:text-white-light">your articles</h5>

    <?php endif; ?>

    <?php require_once('./config/alerts.php') ?>

    <div class="dataTable-wrapper dataTable-loading no-footer fixed-columns">
        <div class="dataTable-top"></div>
        <div class="dataTable-container">
            <table id="myTable" class="table-hover whitespace-nowrap dataTable-table">
                <thead>
                <tr>
                    <th style="width: 12.7939%;">Row</th>
                    <th style="width: 12.7939%;">img</th>
                    <th style="width: 13.7884%;">title</th>
                    <th style="width: 21.0669%;">slug</th>
                    <th style="width: 22.9656%;">Actions</th>

                </tr>
                </thead>
                <tbody>
                <?php foreach ($blog_lists as $blog) { ?>

                    <tr>
                        <td><?= $i++ ?></td>

                        <td><img width="90px" height="50px" src="<?= $_ENV['SITE_URL'] . $blog->img ?>" alt=""></td>

                        <td><?= $blog->title ?></td>
                        <td><?= $blog->slug ?></td>

                        <td>
                            <a href="lists-blogs.php?delete=<?= $blog->id ?>"
                               style="background: red; padding: 4px; border-radius: 10px; color: #ffff;margin-right: 5px; ">delete</a>

                            <a style="background: blue; padding: 4px; border-radius: 10px; color: #ffff;margin-right: 5px; "
                               href="#">edit</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include 'footer-main.php'; ?>

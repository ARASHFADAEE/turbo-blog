<?php
session_start();

require_once('./class/auth.php');

$auth = new Auth();
$auth->is_login();
$auth->is_admin();


include './config/loader.php';

if ($_SESSION['role'] == 'admin' | $_SESSION['role'] == 'writer') {
    $query_all_file = "SELECT * FROM `categories` ";
    $result = $conn->query($query_all_file);
    $result->execute();
    $cat_lists = $result->fetchAll(PDO::FETCH_OBJ);

} else {
    exit;
}
$i = 1;

//delete cat
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $user_id = $_SESSION['user_id'];
        $is_admin = $_SESSION['role'] === 'admin';


        $query_delete = "DELETE FROM categories WHERE id = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bindValue(1, $id, PDO::PARAM_INT);
        $stmt_delete->execute();
        
        header('location: ./categories.php?error=ok&message=File deleted successfully');
        exit();


    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}


$title = 'categories';

?>
<?php include 'header-main.php'; ?>

<!-- start main content section -->
<div class="panel mt-6">
    <h5 class="text-lg font-semibold dark:text-white-light">categories</h5>


    <?php require_once('./config/alerts.php') ?>

    <div class="dataTable-wrapper dataTable-loading no-footer fixed-columns">
        <div class="dataTable-top"></div>
        <div class="dataTable-container">
            <a href="./add-category.php"><button type="button" class="btn btn-primary">add category</button></a> 
            <br>
            <table id="myTable" class="table-hover whitespace-nowrap dataTable-table">
                <thead>
                <tr>
                    <th style="width: 12.7939%;">Row</th>
                    <th style="width: 13.7884%;">title</th>
                    <th style="width: 21.0669%;">slug</th>
                    <th style="width: 22.9656%;">Actions</th>

                </tr>
                </thead>
                <tbody>
                <?php foreach ($cat_lists as $cat) { ?>

                    <tr>
                        <td><?= $i++ ?></td>


                        <td><?= $cat->name ?></td>
                        <td><?= $cat->slug ?></td>

                        <td>
                            <a href="categories.php?delete=<?= $cat->id ?>"
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

<?php
session_start();
require_once('./class/auth.php');

$auth = new Auth();
$auth->is_login();
$auth->is_admin();

include './config/loader.php';

//show data field in form for update
if (isset($_GET['id'])){
    $id=$_GET['id'];
    $query='SELECT * FROM categories WHERE id=?';
    $stmt=$conn->prepare($query);
    $stmt->bindValue(1,$id,PDO::PARAM_INT);
    $stmt->execute();
    $result_cat=$stmt->fetch(PDO::FETCH_OBJ);
}else{
    header("location:./index.php");
}


//update data in database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cat'])) {
    try {
        if (!empty($_POST['name']) && !empty($_POST['slug'])) {
            $query = "UPDATE `categories` SET  `name`=?,`slug`=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $_POST['name'], PDO::PARAM_STR);
            $stmt->bindValue(2, $_POST['slug'], PDO::PARAM_STR);
            $stmt->bindValue(3,$id,PDO::PARAM_INT);

            $stmt->execute();

            header("location: ./categories.php?success=ok&message=category edit succsessfully");
            exit;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("location: ./categories.php?error=db&message=Database error.");
        exit;
    }
}

$title = 'Upload File';
?>

<?php include 'header-main.php'; ?>
<?php require_once('./config/alerts.php'); ?>

<form class="space-y-5" method="post">
    <div class="flex sm:flex-row flex-col">
        <label for="name" class="mb-0 sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">name</label>
        <input id="name" name="name" type="text" placeholder="tech" class="form-input flex-1" value="<?= $result_cat->name ?>" required />
    </div>

    <div class="flex sm:flex-row flex-col">
        <label for="slug" class="mb-0 sm:w-1/4 sm:ltr:mr-2 rtl:ml-2">Slug</label>
        <input id="slug" name="slug" type="text" placeholder="tech" class="form-input flex-1" value="<?= $result_cat->slug ?>" required />
    </div>

    <button name="add_cat" type="submit" class="btn btn-primary !mt-6">Save</button>
</form>

<?php include 'footer-main.php'; ?>

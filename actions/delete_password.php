<?php
require_once '../includes/protect.php';
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // تم حذف فحص CSRF اليدوي من هنا للاعتماد على فحص protect.php العالمي
    if (isset($_POST['confirm_delete']) && isset($_POST['delete_id'])) {
        $del_id = intval($_POST["delete_id"]);
        $user_id = $_SESSION['user_id']; 
        
        $stmt = $pdo->prepare("DELETE FROM passwords WHERE id = ? AND user_id = ?");
        $stmt->execute([$del_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION["flash_success"] = "Password deleted successfully!";
        } else {
            $_SESSION["flash_error"] = "Failed to delete password. Please try again.";
        }
    }
}
header("Location: ../dashboard.php");
exit;

<?php

require_once 'includes/protect.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require "includes/db.php";

$user_id = $_SESSION['user_id'];
$master_key = $_SESSION['master_key'];

$stmt = $pdo->prepare("SELECT * FROM passwords WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$passwords = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Dashboard - Passman</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="assets/css/style.css">

    <script src="assets/js/totp.js" defer></script>
    <script src="assets/js/export.js" defer></script>
    <script src="assets/js/import.js" defer></script>

</head>

<body class="min-h-screen p-8 font-sans text-gray-200">

    <?php include 'includes/dashboard/navbar.php'; ?>

    <div class="pt-24"></div>

    <?php include 'includes/dashboard/timeout-modal.php'; ?>

    <?php include 'includes/dashboard/alerts.php'; ?>

    <?php include 'includes/dashboard/filters.php'; ?>

    <?php include 'includes/dashboard/add-password.php'; ?>

    <?php include 'includes/dashboard/password-cards.php'; ?>

    <?php include 'includes/dashboard/delete-modal.php'; ?>

    <?php include 'includes/dashboard/tools.php'; ?>

    <?php include 'includes/dashboard/terminate-modal.php'; ?>

    <?php include 'includes/dashboard/change-master-modal.php'; ?>

    <script src="assets/js/main.js"></script>

</body>

</html>

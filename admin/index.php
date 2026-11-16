<?php
session_start();
include '../databae/dbconfig.php';

if(!isset($_SESSION['user_id'])){
    header('Location: ../login.php');
    exit;
}

if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard_admin.php');
    exit;
}
header('Location: ../login.php');
exit;



?>
<?php
session_start();
require '../db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$res = $stmt->get_result();

if($row = $res->fetch_assoc()){
    if($row['password'] === $password){
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_email'] = $row['email'];
        header("Location: admin_dashboard.php");
        exit;
    }
}
echo "<script>alert('Invalid Admin Login');window.location='admin_login.php';</script>";

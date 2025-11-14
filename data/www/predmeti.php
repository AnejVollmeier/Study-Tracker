<?php 
session_start();

if(!isset($_SESSION['user_id'])){
    session_destroy();
    header("Location: login.php");
    exit();
}else{
    echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
}
?>
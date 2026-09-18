<?php

function requireRole($allowed_roles){

    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }

    if(!isset($_SESSION['role'])){
        header("Location: /medical-camp-system/login.php");
        exit();
    }

    if(!in_array($_SESSION['role'], $allowed_roles)){
        echo "<h3 style='color:red;text-align:center;margin-top:50px;'>
        Access Denied — Insufficient Permission
        </h3>";
        exit();
    }
}
?>
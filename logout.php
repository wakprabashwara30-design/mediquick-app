<?php
/**
 * MediQuick Pharmacy - Logout Script
 * University 1st-Year Web Application (PHP Session Destroy)
 */
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>

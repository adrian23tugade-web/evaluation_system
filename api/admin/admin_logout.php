<?php
include __DIR__ . "/../db.php";
session_unset();
session_destroy();
header("Location: admin_login.php");
exit();
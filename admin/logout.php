<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';

unset($_SESSION['petugas_id'], $_SESSION['petugas']);
session_destroy();
redirect(url('admin/login.php'));
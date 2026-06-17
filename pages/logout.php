<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';

session_destroy();
redirect(url());
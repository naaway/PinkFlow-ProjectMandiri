<?php
require_once '../includes/functions.php';require_once '../config/database.php';
if(empty($_SESSION['admin_id'])){header('Location: login.php');exit;}

<?php
ini_set('session.save_path', sys_get_temp_dir());
session_start();
session_destroy();   // 会话控制：销毁 Session
header('Location: login.php');
exit;
?>
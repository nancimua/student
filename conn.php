<?php
$conn = mysqli_connect(
    "127.0.0.1",   // 主机名
    "root",        // 用户名
    "root",        // 密码
    "2026student"  // 数据库名
);

// 数据库连接错误处理（PHP 基础知识：条件语句）
if (!$conn) {
    die("数据库连接失败：" . mysqli_connect_error());
}

// 设置字符集（防止中文乱码）
mysqli_set_charset($conn, "utf8mb4");

// PHP 基础知识：定义函数（可在其他页面调用）
function esc($s)
{
    global $conn;
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
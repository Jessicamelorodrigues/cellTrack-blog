<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_check();
$slug = $_POST['slug'] ?? '';
$posts = load_posts();
$left = array_values(array_filter($posts, function ($p) use ($slug) { return ($p['slug'] ?? '') !== $slug; }));
if (count($left) !== count($posts)) save_posts($left);
header('Location: index.php?excluido=1');
exit;

<?php
// Image upload for pictures inside the post text (called by the editor's 🖼 button). Returns JSON.
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}
csrf_check();
[$path, $err] = save_uploaded_image($_FILES['image'] ?? null, $_POST['hint'] ?? 'image');
if (!$path) {
    http_response_code(400);
    echo json_encode(['error' => $err ?: 'Nenhuma imagem enviada.']);
    exit;
}
echo json_encode(['path' => $path]);

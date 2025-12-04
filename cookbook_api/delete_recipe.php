<?php
// delete_recipe.php
require 'db.php';
require 'helpers.php';

$user = auth_user($pdo);
if (!$user) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

// confirm owner
$check = $pdo->prepare("SELECT user_id, image_path FROM recipes WHERE id = ?");
$check->execute([$id]); $r = $check->fetch();
if (!$r) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
if ($r['user_id'] != $user['id']) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }

try {
    $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    // optionally delete image file from uploads folder
    if (!empty($r['image_path'])) {
        $file = __DIR__ . '/' . $r['image_path'];
        if (file_exists($file)) @unlink($file);
    }
    echo json_encode(['success'=>true]);
} catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
?>

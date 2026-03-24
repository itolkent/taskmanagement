<?php
use App\Middleware\AuthMiddleware;

class AttachmentController
{
    public function index($taskId)
    {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT ta.*, u.name AS uploader_name
            FROM task_attachments ta
            JOIN users u ON u.id = ta.uploaded_by
            WHERE task_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$taskId]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function store($taskId)
    {
        global $pdo;
        $user = AuthMiddleware::handle();

        $userId = $user['id'];

        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No file uploaded']);
            return;
        }

        $file = $_FILES['file'];
        $filename = time() . "_" . basename($file['name']);
        $path = "uploads/" . $filename;

        move_uploaded_file($file['tmp_name'], $path);

        $stmt = $pdo->prepare("
            INSERT INTO task_attachments (task_id, file_path, file_name, uploaded_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$taskId, $path, $file['name'], $userId]);

        echo json_encode([
            'id' => $pdo->lastInsertId(),
            'task_id' => $taskId,
            'file_path' => $path,
            'file_name' => $file['name'],
            'uploaded_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function destroy($id)
    {
        global $pdo;

        $stmt = $pdo->prepare("SELECT file_path FROM task_attachments WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetchColumn();

        if ($file && file_exists($file)) {
            unlink($file);
        }

        $stmt = $pdo->prepare("DELETE FROM task_attachments WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
    }
} ?>
<?php
require '../../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

header("Content-Type: application/json");

$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');

try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$mail = new Mail($pdo);
$page = new Page();

$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$id = (int) end($parts);

if ($id <= 0) {
    $page->badRequest();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $mail->getMail($id);

    if (!$row) {
        $page->notFound();
        exit;
    }

    $page->item($row);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!is_array($data) || !isset($data['subject']) || !isset($data['body'])) {
        $page->badRequest();
        exit;
    }

    $updated = $mail->updateMail($id, $data['subject'], $data['body']);

    if (!$updated) {
        $page->notFound();
        exit;
    }

    $page->item(["updated" => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $deleted = $mail->deleteMail($id);

    if (!$deleted) {
        $page->notFound();
        exit;
    }

    $page->item(["deleted" => true]);
    exit;
}

$page->badRequest();
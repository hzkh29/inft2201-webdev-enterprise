<?php
require  __DIR__ . '/../../../autoload.php';

use Application\Mail;
use Application\Database;
use Application\Page;
use Application\Verifier;

$database = new Database('prod');
$page = new Page();

$mail = new Mail($database->getDb());

$verifier = new Verifier();

if (!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
    http_response_code(401);
    exit;
}

$verifier->decode($_SERVER['HTTP_AUTHORIZATION']);

if (empty($verifier->userId) || empty($verifier->role)) {
    http_response_code(401);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (array_key_exists('name', $data) && array_key_exists('message', $data)) {
        // // Saves the mail under the authenticated user's userId from the token
        $id = $mail->createMail($data['name'], $data['message'], $verifier->userId);
        $page->item(array("id" => $id));
    } else {
        $page->badRequest();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Admin sees all mail
    if ($verifier->role === 'admin') {
        $page->item($mail->listMail());
    } 
    // Regular users only see their own mail
    else {
        $page->item($mail->listMailByUserId($verifier->userId));
    }

} else {
    $page->badRequest();
}
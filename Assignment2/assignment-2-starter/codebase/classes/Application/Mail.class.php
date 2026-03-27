<?php
namespace Application;

// required for using the PDO::FETCH_ASSOC constant
use PDO;

class Mail
{
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createMail($name, $message, $userId)
    {
        $stmt = $this->db->prepare("INSERT INTO mail (name, message, userId) VALUES (:name, :message, :userId)");
        $stmt->execute([
    'name' => $name,
    'message' => $message,
    'userId' => $userId
    ]);

        return $this->db->lastInsertId();
    }

    public function listMail() 
    {
        $result = $this->db->query("SELECT id, name FROM mail ORDER BY id");

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lists only the mail records that belong to the authenticated user
    public function listMailByUserId($userId)
{
    $stmt = $this->db->prepare("SELECT id, name FROM mail WHERE userId = :userId ORDER BY id");
    $stmt->execute(['userId' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
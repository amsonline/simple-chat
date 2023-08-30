<?php

namespace App\Services;

use PDO;

class DatabaseService
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchAll($query): array
    {
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function query($sql): \PDOStatement
    {
        return $this->pdo->query($sql);
    }

    public function refreshDB(): void
    {
        if (env('APP_ENV') != 'testing') {
            return;
        }

        $this->pdo->query("DELETE FROM users");
        $this->pdo->query("DELETE FROM groups");
        $this->pdo->query("DELETE FROM group_members");
        $this->pdo->query("DELETE FROM messages");
    }

    private function createUser($name): string
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (name) VALUES (:name)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function createGroup($name): string
    {
        $stmt = $this->pdo->prepare("INSERT INTO groups (name) VALUES (:name)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function getGroupMessages($groupId, $lastMessageId): array
    {
        if (null === $lastMessageId) {
            // We should get latest 20 messages of the group
            $stmt = $this->pdo->prepare("SELECT m.*, u.name AS username FROM messages m JOIN users u ON u.id = m.user_id WHERE group_id = :group_id ORDER BY timestamp DESC LIMIT 20");
        }else{
            // It is not the first request, so we should send new messages only
            $stmt = $this->pdo->prepare("SELECT m.*, u.name AS username FROM messages m JOIN users u ON u.id = m.user_id WHERE group_id = :group_id AND m.id > :last_message ORDER BY timestamp DESC LIMIT 50");
            $stmt->bindValue(':last_message', $lastMessageId, SQLITE3_INTEGER);
        }
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getGroups($userId): array
    {
        $sql = "SELECT g.*, IIF(m.user_id IS NULL, 0, 1) isJoined
                FROM groups g
                    LEFT JOIN group_members m
                        ON (g.id = m.group_id AND m.user_id = :user_id)
                ORDER BY isJoined DESC
                ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserId($name): int
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE name = :name");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user === false) {
            // Name does not exist in the DB, we have to create it
            $userId = $this->createUser($name);
        } else {
            $userId = $user->id;
        }
        return $userId;
    }

    public function isGroupExisted(int $groupId): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM groups WHERE id = :group_id");
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->execute();

        return count($stmt->fetchAll()) > 0;
    }

    public function isJoinedGroup(int $userId, int $groupId): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM group_members WHERE group_id = :group_id AND user_id = :user_id");
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->execute();

        return count($stmt->fetchAll()) > 0;
    }

    public function isUserExisted(int $userId): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->execute();

        return count($stmt->fetchAll()) > 0;
    }

    public function joinGroup($userId, $groupId): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)");
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function sendMessage($groupId, $userId, $message): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :message)");
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        $stmt->execute();
    }
}

<?php
/**
 * کلاس Folder - مدل پوشه‌ها
 * مدیریت عملیات مربوط به سلسله‌مراتب پوشه‌ها
 */

namespace TelegramBot\Models;

use TelegramBot\Helpers\Database;
use TelegramBot\Helpers\Logger;

class Folder
{
    private $id;
    private $userId;
    private $parentId;
    private $name;
    private $description;
    private $isPublic = true;
    private $createdAt;

    /**
     * جستجو بر اساس ID
     */
    public static function findById($id)
    {
        $sql = 'SELECT * FROM folders WHERE id = ?';
        $result = Database::fetchOne($sql, [$id]);
        
        if ($result) {
            return self::fromArray($result);
        }
        
        return null;
    }

    /**
     * ایجاد پوشه جدید
     */
    public static function create($userId, $name, $parentId = null, $description = '')
    {
        $sql = 'INSERT INTO folders (user_id, parent_id, name, description) VALUES (?, ?, ?, ?)';
        
        Database::execute($sql, [
            $userId,
            $parentId,
            $name,
            $description,
        ]);

        $folderId = Database::lastInsertId();
        Logger::info('پوشه جدید ایجاد شد', ['folder_id' => $folderId, 'user_id' => $userId]);
        
        return self::findById($folderId);
    }

    /**
     * دریافت پوشه‌های یک کاربر
     */
    public static function getUserFolders($userId, $parentId = null)
    {
        if ($parentId === null) {
            $sql = 'SELECT * FROM folders WHERE user_id = ? AND parent_id IS NULL ORDER BY order_index ASC';
            $results = Database::fetchAll($sql, [$userId]);
        } else {
            $sql = 'SELECT * FROM folders WHERE user_id = ? AND parent_id = ? ORDER BY order_index ASC';
            $results = Database::fetchAll($sql, [$userId, $parentId]);
        }

        $folders = [];
        foreach ($results as $row) {
            $folders[] = self::fromArray($row);
        }

        return $folders;
    }

    /**
     * بروزرسانی پوشه
     */
    public function update($name = null, $description = null)
    {
        $updates = [];
        $params = [];

        if ($name !== null) {
            $updates[] = 'name = ?';
            $params[] = $name;
            $this->name = $name;
        }

        if ($description !== null) {
            $updates[] = 'description = ?';
            $params[] = $description;
            $this->description = $description;
        }

        if (empty($updates)) {
            return true;
        }

        $params[] = $this->id;
        $sql = 'UPDATE folders SET ' . implode(', ', $updates) . ' WHERE id = ?';
        Database::execute($sql, $params);

        return true;
    }

    /**
     * حذف پوشه
     */
    public function delete()
    {
        // حذف زیرپوشه‌ها
        $subFolders = self::getUserFolders($this->userId, $this->id);
        foreach ($subFolders as $folder) {
            $folder->delete();
        }

        // حذف فایل‌های پوشه
        $sql = 'DELETE FROM media WHERE folder_id = ?';
        Database::execute($sql, [$this->id]);

        // حذف پوشه
        $sql = 'DELETE FROM folders WHERE id = ?';
        Database::execute($sql, [$this->id]);

        Logger::info('پوشه حذف شد', ['folder_id' => $this->id]);
    }

    /**
     * تبدیل آرایه به Object
     */
    private static function fromArray($data)
    {
        $folder = new self();
        $folder->id = $data['id'];
        $folder->userId = $data['user_id'];
        $folder->parentId = $data['parent_id'];
        $folder->name = $data['name'];
        $folder->description = $data['description'];
        $folder->isPublic = (bool) $data['is_public'];
        $folder->createdAt = $data['created_at'];
        return $folder;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getParentId() { return $this->parentId; }
    public function isPublic() { return $this->isPublic; }
}

<?php
/**
 * کلاس Media - مدل رسانه‌ها
 * مدیریت عملیات مربوط به فایل‌های آپلود شده
 */

namespace TelegramBot\Models;

use TelegramBot\Helpers\Database;
use TelegramBot\Helpers\Logger;

class Media
{
    private $id;
    private $mediaCode;
    private $userId;
    private $folderId;
    private $telegramFileId;
    private $fileType;
    private $fileName;
    private $fileSize;
    private $caption;
    private $password;
    private $downloadLimit;
    private $downloadCount = 0;
    private $isVerified = false;
    private $customLink;
    private $createdAt;

    /**
     * جستجو بر اساس کد رسانه
     */
    public static function findByCode($mediaCode)
    {
        $sql = 'SELECT * FROM media WHERE media_code = ?';
        $result = Database::fetchOne($sql, [$mediaCode]);
        
        if ($result) {
            return self::fromArray($result);
        }
        
        return null;
    }

    /**
     * جستجو بر اساس ID
     */
    public static function findById($id)
    {
        $sql = 'SELECT * FROM media WHERE id = ?';
        $result = Database::fetchOne($sql, [$id]);
        
        if ($result) {
            return self::fromArray($result);
        }
        
        return null;
    }

    /**
     * ایجاد رسانه جدید
     */
    public static function create($userId, $data = [])
    {
        $mediaCode = generateMediaCode();
        
        $sql = 'INSERT INTO media (media_code, user_id, folder_id, telegram_file_id, file_type, file_name, file_size, caption, password, download_limit, is_verified, custom_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        Database::execute($sql, [
            $mediaCode,
            $userId,
            $data['folder_id'] ?? null,
            $data['telegram_file_id'] ?? '',
            $data['file_type'] ?? 'document',
            $data['file_name'] ?? null,
            $data['file_size'] ?? 0,
            $data['caption'] ?? null,
            $data['password'] ?? null,
            $data['download_limit'] ?? 0,
            $data['is_verified'] ?? 0,
            $data['custom_link'] ?? null,
        ]);

        Logger::info('رسانه جدید ایجاد شد', ['media_code' => $mediaCode, 'user_id' => $userId]);
        
        return self::findByCode($mediaCode);
    }

    /**
     * بروزرسانی کپشن
     */
    public function updateCaption($caption)
    {
        $sql = 'UPDATE media SET caption = ? WHERE id = ?';
        Database::execute($sql, [$caption, $this->id]);
        $this->caption = $caption;
    }

    /**
     * افزایش تعداد دانلود
     */
    public function incrementDownloadCount()
    {
        $sql = 'UPDATE media SET download_count = download_count + 1 WHERE id = ?';
        Database::execute($sql, [$this->id]);
        $this->downloadCount++;
    }

    /**
     * چک کردن محدودیت دانلود
     */
    public function canDownload()
    {
        if ($this->downloadLimit <= 0) {
            return true;
        }
        
        return $this->downloadCount < $this->downloadLimit;
    }

    /**
     * چک کردن رمزعبور
     */
    public function verifyPassword($inputPassword)
    {
        if (!$this->password) {
            return true;
        }
        
        return password_verify($inputPassword, $this->password);
    }

    /**
     * تنظیم رمزعبور
     */
    public function setPassword($password)
    {
        if (!$password) {
            $sql = 'UPDATE media SET password = NULL WHERE id = ?';
            Database::execute($sql, [$this->id]);
            $this->password = null;
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = 'UPDATE media SET password = ? WHERE id = ?';
        Database::execute($sql, [$hashedPassword, $this->id]);
        $this->password = $hashedPassword;
    }

    /**
     * حذف رسانه
     */
    public function delete()
    {
        $sql = 'DELETE FROM media WHERE id = ?';
        Database::execute($sql, [$this->id]);
        Logger::info('رسانه حذف شد', ['id' => $this->id]);
    }

    /**
     * دریافت رسانه‌های یک کاربر
     */
    public static function getUserMedia($userId, $folderId = null, $limit = 20, $offset = 0)
    {
        if ($folderId) {
            $sql = 'SELECT * FROM media WHERE user_id = ? AND folder_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?';
            $results = Database::fetchAll($sql, [$userId, $folderId, $limit, $offset]);
        } else {
            $sql = 'SELECT * FROM media WHERE user_id = ? AND folder_id IS NULL ORDER BY created_at DESC LIMIT ? OFFSET ?';
            $results = Database::fetchAll($sql, [$userId, $limit, $offset]);
        }

        $media = [];
        foreach ($results as $row) {
            $media[] = self::fromArray($row);
        }

        return $media;
    }

    /**
     * جستجوی رسانه
     */
    public static function search($userId, $query, $limit = 20, $offset = 0)
    {
        $searchQuery = '%' . $query . '%';
        $sql = 'SELECT * FROM media WHERE user_id = ? AND (media_code LIKE ? OR caption LIKE ? OR file_name LIKE ?) 
                ORDER BY created_at DESC LIMIT ? OFFSET ?';
        
        $results = Database::fetchAll($sql, [$userId, $searchQuery, $searchQuery, $searchQuery, $limit, $offset]);

        $media = [];
        foreach ($results as $row) {
            $media[] = self::fromArray($row);
        }

        return $media;
    }

    /**
     * تبدیل آرایه به Object
     */
    private static function fromArray($data)
    {
        $media = new self();
        $media->id = $data['id'];
        $media->mediaCode = $data['media_code'];
        $media->userId = $data['user_id'];
        $media->folderId = $data['folder_id'];
        $media->telegramFileId = $data['telegram_file_id'];
        $media->fileType = $data['file_type'];
        $media->fileName = $data['file_name'];
        $media->fileSize = $data['file_size'];
        $media->caption = $data['caption'];
        $media->password = $data['password'];
        $media->downloadLimit = $data['download_limit'];
        $media->downloadCount = $data['download_count'];
        $media->isVerified = (bool) $data['is_verified'];
        $media->customLink = $data['custom_link'];
        $media->createdAt = $data['created_at'];
        return $media;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCode() { return $this->mediaCode; }
    public function getTelegramFileId() { return $this->telegramFileId; }
    public function getFileType() { return $this->fileType; }
    public function getFileName() { return $this->fileName; }
    public function getFileSize() { return $this->fileSize; }
    public function getCaption() { return $this->caption; }
    public function getDownloadCount() { return $this->downloadCount; }
    public function getDownloadLimit() { return $this->downloadLimit; }
    public function isVerified() { return $this->isVerified; }
    public function getCustomLink() { return $this->customLink; }
    public function getCreatedAt() { return $this->createdAt; }
}

<?php
/**
 * کلاس User - مدل کاربر
 * مدیریت عملیات مربوط به کاربران
 */

namespace TelegramBot\Models;

use TelegramBot\Helpers\Database;
use TelegramBot\Helpers\Logger;

class User
{
    private $id;
    private $telegramId;
    private $username;
    private $firstName;
    private $lastName;
    private $isPremium = false;
    private $premiumExpiresAt;
    private $accessLevel = 'user';
    private $isBlocked = false;
    private $downloadsCount = 0;

    /**
     * دریافت یا ایجاد کاربر
     */
    public static function findOrCreate($telegramId, $userData = [])
    {
        $user = self::findByTelegramId($telegramId);
        
        if (!$user) {
            return self::create($telegramId, $userData);
        }
        
        return $user;
    }

    /**
     * جستجو بر اساس Telegram ID
     */
    public static function findByTelegramId($telegramId)
    {
        $sql = 'SELECT * FROM users WHERE telegram_id = ?';
        $result = Database::fetchOne($sql, [$telegramId]);
        
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
        $sql = 'SELECT * FROM users WHERE id = ?';
        $result = Database::fetchOne($sql, [$id]);
        
        if ($result) {
            return self::fromArray($result);
        }
        
        return null;
    }

    /**
     * ایجاد کاربر جدید
     */
    public static function create($telegramId, $userData = [])
    {
        $sql = 'INSERT INTO users (telegram_id, username, first_name, last_name) VALUES (?, ?, ?, ?)';
        
        Database::execute($sql, [
            $telegramId,
            $userData['username'] ?? null,
            $userData['first_name'] ?? null,
            $userData['last_name'] ?? null,
        ]);

        Logger::info('کاربر جدید ایجاد شد', ['telegram_id' => $telegramId]);
        
        return self::findByTelegramId($telegramId);
    }

    /**
     * بروزرسانی آخرین فعالیت
     */
    public function updateLastActivity()
    {
        $sql = 'UPDATE users SET last_activity = NOW() WHERE id = ?';
        Database::execute($sql, [$this->id]);
    }

    /**
     * افزایش تعداد دانلود
     */
    public function incrementDownloadCount()
    {
        $sql = 'UPDATE users SET downloads_count = downloads_count + 1 WHERE id = ?';
        Database::execute($sql, [$this->id]);
        $this->downloadsCount++;
    }

    /**
     * چک کردن وضعیت پریمیوم
     */
    public function isPremiumUser()
    {
        if (!$this->isPremium) {
            return false;
        }

        if ($this->premiumExpiresAt && strtotime($this->premiumExpiresAt) < time()) {
            $this->deactivatePremium();
            return false;
        }

        return true;
    }

    /**
     * غیرفعال کردن پریمیوم
     */
    public function deactivatePremium()
    {
        $sql = 'UPDATE users SET is_premium = 0, premium_expires_at = NULL WHERE id = ?';
        Database::execute($sql, [$this->id]);
        $this->isPremium = false;
    }

    /**
     * فعال کردن پریمیوم
     */
    public function activatePremium($days = 30)
    {
        $expiresAt = date('Y-m-d H:i:s', time() + ($days * 86400));
        $sql = 'UPDATE users SET is_premium = 1, premium_expires_at = ? WHERE id = ?';
        Database::execute($sql, [$expiresAt, $this->id]);
        $this->isPremium = true;
        $this->premiumExpiresAt = $expiresAt;
    }

    /**
     * بلاک کردن کاربر
     */
    public function block()
    {
        $sql = 'UPDATE users SET is_blocked = 1 WHERE id = ?';
        Database::execute($sql, [$this->id]);
        $this->isBlocked = true;
    }

    /**
     * آنبلاک کردن کاربر
     */
    public function unblock()
    {
        $sql = 'UPDATE users SET is_blocked = 0 WHERE id = ?';
        Database::execute($sql, [$this->id]);
        $this->isBlocked = false;
    }

    /**
     * تنظیم سطح دسترسی
     */
    public function setAccessLevel($level)
    {
        if (!in_array($level, ['user', 'admin_content', 'admin_users', 'admin_payments', 'admin_full'])) {
            return false;
        }

        $sql = 'UPDATE users SET access_level = ? WHERE id = ?';
        Database::execute($sql, [$level, $this->id]);
        $this->accessLevel = $level;
        return true;
    }

    /**
     * تبدیل آرایه به Object
     */
    private static function fromArray($data)
    {
        $user = new self();
        $user->id = $data['id'];
        $user->telegramId = $data['telegram_id'];
        $user->username = $data['username'];
        $user->firstName = $data['first_name'];
        $user->lastName = $data['last_name'];
        $user->isPremium = (bool) $data['is_premium'];
        $user->premiumExpiresAt = $data['premium_expires_at'];
        $user->accessLevel = $data['access_level'];
        $user->isBlocked = (bool) $data['is_blocked'];
        $user->downloadsCount = $data['downloads_count'];
        return $user;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTelegramId() { return $this->telegramId; }
    public function getUsername() { return $this->username; }
    public function getFirstName() { return $this->firstName; }
    public function getLastName() { return $this->lastName; }
    public function getAccessLevel() { return $this->accessLevel; }
    public function isBlocked() { return $this->isBlocked; }
    public function getDownloadsCount() { return $this->downloadsCount; }
}

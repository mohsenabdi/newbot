<?php
/**
 * فایل Helper - توابع کمکی سیستم
 * شامل توابع برای ارسال پیام، کیبورد، کوئری دیتابیس و...
 */

namespace TelegramBot\Helpers;

use GuzzleHttp\Client;
use PDO;

class TelegramHelper
{
    private static $client = null;
    private static $botToken = null;
    private static $apiUrl = 'https://api.telegram.org/bot';

    /**
     * ایجاد کلاینت HTTP
     */
    public static function getClient()
    {
        if (self::$client === null) {
            self::$client = new Client([
                'timeout' => 30,
                'verify' => false,
            ]);
        }
        return self::$client;
    }

    /**
     * ارسال پیام سادہ
     */
    public static function sendMessage($chatId, $text, $parseMode = 'HTML', $replyMarkup = null, $disableWebPagePreview = false)
    {
        $botToken = self::getBotToken();
        $url = self::$apiUrl . $botToken . '/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disableWebPagePreview,
        ];

        if ($replyMarkup) {
            $data['reply_markup'] = $replyMarkup;
        }

        try {
            $response = self::getClient()->post($url, ['json' => $data]);
            $result = json_decode($response->getBody(), true);
            return $result['ok'] ?? false;
        } catch (\Exception $e) {
            Logger::error('خطا در ارسال پیام: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ارسال کیبورد inline
     */
    public static function inlineKeyboard($buttons)
    {
        return json_encode([
            'inline_keyboard' => $buttons,
        ]);
    }

    /**
     * ارسال کیبورد عادی
     */
    public static function replyKeyboard($buttons, $oneTime = true, $resize = true)
    {
        return json_encode([
            'keyboard' => $buttons,
            'one_time_keyboard' => $oneTime,
            'resize_keyboard' => $resize,
        ]);
    }

    /**
     * حذف کیبورد
     */
    public static function removeKeyboard()
    {
        return json_encode([
            'remove_keyboard' => true,
        ]);
    }

    /**
     * ارسال فایل
     */
    public static function sendDocument($chatId, $fileId, $caption = null, $replyMarkup = null, $protectContent = false)
    {
        $botToken = self::getBotToken();
        $url = self::$apiUrl . $botToken . '/sendDocument';

        $data = [
            'chat_id' => $chatId,
            'document' => $fileId,
            'protect_content' => $protectContent ? 1 : 0,
        ];

        if ($caption) {
            $data['caption'] = $caption;
            $data['parse_mode'] = 'HTML';
        }

        if ($replyMarkup) {
            $data['reply_markup'] = $replyMarkup;
        }

        try {
            $response = self::getClient()->post($url, ['json' => $data]);
            $result = json_decode($response->getBody(), true);
            return $result['ok'] ?? false;
        } catch (\Exception $e) {
            Logger::error('خطا در ارسال فایل: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف پیام
     */
    public static function deleteMessage($chatId, $messageId)
    {
        $botToken = self::getBotToken();
        $url = self::$apiUrl . $botToken . '/deleteMessage';

        try {
            $response = self::getClient()->post($url, [
                'json' => [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]
            ]);
            $result = json_decode($response->getBody(), true);
            return $result['ok'] ?? false;
        } catch (\Exception $e) {
            Logger::error('خطا در حذف پیام: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت اطلاعات کاربر
     */
    public static function getChatMember($chatId, $userId)
    {
        $botToken = self::getBotToken();
        $url = self::$apiUrl . $botToken . '/getChatMember';

        try {
            $response = self::getClient()->post($url, [
                'json' => [
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                ]
            ]);
            $result = json_decode($response->getBody(), true);
            return $result['result'] ?? null;
        } catch (\Exception $e) {
            Logger::error('خطا در دریافت اطلاعات کاربر: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ویرایش کپشن پیام
     */
    public static function editMessageCaption($chatId, $messageId, $caption)
    {
        $botToken = self::getBotToken();
        $url = self::$apiUrl . $botToken . '/editMessageCaption';

        try {
            $response = self::getClient()->post($url, [
                'json' => [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]
            ]);
            $result = json_decode($response->getBody(), true);
            return $result['ok'] ?? false;
        } catch (\Exception $e) {
            Logger::error('خطا در ویرایش کپشن: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * دریافت توکن بوت
     */
    private static function getBotToken()
    {
        if (self::$botToken === null) {
            self::$botToken = getenv('TELEGRAM_BOT_TOKEN') ?: (include(__DIR__ . '/../Config/config.php'))['bot_token'];
        }
        return self::$botToken;
    }
}

class Logger
{
    /**
     * ثبت لاگ
     */
    public static function log($message, $level = 'info', $data = [])
    {
        $logFile = __DIR__ . '/../../logs/bot.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $logMessage = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($data) ? json_encode($data) : ''
        );

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function info($message, $data = [])
    {
        self::log($message, 'info', $data);
    }

    public static function error($message, $data = [])
    {
        self::log($message, 'error', $data);
    }

    public static function warning($message, $data = [])
    {
        self::log($message, 'warning', $data);
    }
}

class Database
{
    private static $pdo = null;

    /**
     * دریافت اتصال دیتابیس
     */
    public static function connect()
    {
        if (self::$pdo === null) {
            $config = include(__DIR__ . '/../Config/config.php');
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['db_host'],
                $config['db_port'],
                $config['db_name']
            );

            try {
                self::$pdo = new PDO(
                    $dsn,
                    $config['db_user'],
                    $config['db_password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (\Exception $e) {
                Logger::error('خطا در اتصال دیتابیس: ' . $e->getMessage());
                die('خطا در اتصال دیتابیس');
            }
        }

        return self::$pdo;
    }

    /**
     * اجرای کوئری
     */
    public static function query($sql, $params = [])
    {
        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\Exception $e) {
            Logger::error('خطا در اجرای کوئری: ' . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }

    /**
     * دریافت یک رکورد
     */
    public static function fetchOne($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * دریافت همه رکورد‌ها
     */
    public static function fetchAll($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * اضافه کردن/بروزرسانی/حذف
     */
    public static function execute($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * دریافت ID آخرین insertion
     */
    public static function lastInsertId()
    {
        return self::connect()->lastInsertId();
    }
}

// توابع عمومی

/**
 * تولید کد یکتای رسانه
 */
function generateMediaCode($length = 8)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

/**
 * اعتبارسنجی ورودی
 */
function validateInput($input, $type = 'string')
{
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        case 'number':
            return is_numeric($input);
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL);
        case 'string':
            return is_string($input) && !empty(trim($input));
        default:
            return true;
    }
}

/**
 * تبدیل سایز به بایت
 */
function sizeToBytes($size)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $size = preg_replace('/[^0-9.]/', '', $size);
    $size = (float) $size;

    $unit = strtoupper(preg_replace('/[^A-Z]/', '', $size)) ?: 'B';

    for ($i = 0; $i < count($units); $i++) {
        if ($unit === $units[$i]) {
            return round($size * pow(1024, $i));
        }
    }

    return round($size);
}

/**
 * تبدیل بایت به سایز
 */
function bytesToSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * پاکسازی متن
 */
function sanitizeText($text)
{
    return htmlspecialchars(strip_tags(trim($text)), ENT_QUOTES, 'UTF-8');
}

/**
 * چک کردن توکن Webhook
 */
function verifyWebhookToken($token)
{
    $expectedToken = getenv('WEBHOOK_SECRET') ?: 'webhook_secret';
    return hash_equals($expectedToken, $token);
}

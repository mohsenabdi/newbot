<?php
/**
 * فایل کانفیگ اصلی
 * تنظیمات دیتابیس، توکن‌ها و سایر ثابت‌ها
 */

return [
    // تنظیمات دیتابیس
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_port' => getenv('DB_PORT') ?: 3306,
    'db_name' => getenv('DB_NAME') ?: 'telegram_bot',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_password' => getenv('DB_PASSWORD') ?: '',

    // توکن بوت تلگرام
    'bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',

    // تنظیمات Webhook
    'webhook_url' => getenv('WEBHOOK_URL') ?: 'https://yoursite.com/public/webhook/',
    'webhook_secret' => getenv('WEBHOOK_SECRET') ?: 'your_secret_key',

    // تنظیمات کلی
    'app_env' => getenv('APP_ENV') ?: 'production',
    'app_debug' => getenv('APP_DEBUG') ?: false,
    'app_url' => getenv('APP_URL') ?: 'https://yoursite.com',

    // محدودیت‌های فایل
    'upload_max_size' => 2147483648, // 2GB
    'premium_max_size' => 4294967296, // 4GB
    'upload_timeout' => 3600,

    // محدودیت‌های امنیتی
    'session_timeout' => 3600,
    'admin_password' => getenv('ADMIN_PASSWORD') ?: 'change_me',

    // محدودیت‌های اسپم
    'spam_limit_seconds' => 2,
    'broadcast_delay' => 1,
    'delete_message_delay' => 30,

    // تنظیمات لاگ
    'log_level' => getenv('LOG_LEVEL') ?: 'info',
    'log_file' => __DIR__ . '/../../logs/bot.log',

    // تنظیمات بکاپ
    'backup_channel_id' => getenv('BACKUP_CHANNEL_ID') ?: '',
    'backup_interval' => 86400, // 24 ساعت

    // پوشه‌های ذخیره‌سازی
    'storage_dir' => __DIR__ . '/../../storage',
    'temp_dir' => sys_get_temp_dir(),
    'logs_dir' => __DIR__ . '/../../logs',

    // API URLs
    'telegram_api' => 'https://api.telegram.org/bot',
    'zarinpal_api' => 'https://api.zarinpal.com/pg/v4/payment',
    'zibal_api' => 'https://api.zibal.ir/v1',

    // تنظیمات پیام
    'max_message_length' => 4096,
    'max_caption_length' => 1024,
];

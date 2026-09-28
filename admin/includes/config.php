<?php
if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_name('celltrack_admin');
    session_start();
}

define('SITE_NAME', 'CellTrack');
define('SITE_DIR', dirname(__DIR__, 2));                 // site root (index.html, posts.js)
define('DATA_DIR', SITE_DIR . '/data');                  // private: users, login attempts
define('POSTS_JS', SITE_DIR . '/posts.js');              // the blog reads this file
define('BLOG_IMAGES_DIR', SITE_DIR . '/blog-images');
define('BLOG_IMAGES_URL', 'blog-images');                // relative to the site root, as posts.js expects
define('USERS_FILE', DATA_DIR . '/users.json');
define('USER_INVITE_TTL_SECONDS', 172800);               // 48h
define('LOGIN_ATTEMPTS_FILE', DATA_DIR . '/login_attempts.json');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_WINDOW_SECONDS', 900);
define('LOGIN_LOCKOUT_SECONDS', 900);
define('IMAGE_MAX_BYTES', 8 * 1024 * 1024);
define('IMAGE_MAX_WIDTH', 1600);
define('BLOG_TAGS', ['Publication', 'Funding', 'Recognition', 'Science', 'Event', 'Partnership']);

date_default_timezone_set('America/Chicago');           // CellTrack is in Madison, WI

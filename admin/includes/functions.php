<?php
require_once __DIR__ . '/config.php';

// ---------- output ----------
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function e($s) { echo h($s); }

function format_date($d) {
    $t = is_numeric($d) ? (int)$d : strtotime((string)$d);
    return $t ? date('d/m/Y', $t) : '';
}

// ---------- files ----------
function read_json($file, $default) {
    if (!is_file($file)) return $default;
    $d = json_decode((string)file_get_contents($file), true);
    return is_array($d) ? $d : $default;
}
function write_file_atomic($file, $content) {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($tmp, $content, LOCK_EX) === false) throw new RuntimeException("Não consegui gravar $file");
    if (!rename($tmp, $file)) { @unlink($tmp); throw new RuntimeException("Não consegui gravar $file"); }
}
function write_json($file, $data) {
    write_file_atomic($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

// ---------- CSRF ----------
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field() { echo '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'; }
function csrf_check() {
    $t = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(400);
        die('Sua sessão expirou. Volte, recarregue a página e tente de novo.');
    }
}

// ---------- users ----------
function load_users() { return read_json(USERS_FILE, []); }
function save_users($users) { write_json(USERS_FILE, array_values($users)); }
function find_user_by_email($email) {
    $email = strtolower(trim((string)$email));
    foreach (load_users() as $u) if (strtolower($u['email']) === $email) return $u;
    return null;
}
function find_user_by_token($token) {
    if (!is_string($token) || $token === '') return null;
    foreach (load_users() as $u) {
        if (!empty($u['verify_token']) && hash_equals($u['verify_token'], $token)) return $u;
    }
    return null;
}
function new_user($email, $role, $status) {
    return [
        'id' => 'u_' . bin2hex(random_bytes(8)),
        'email' => strtolower(trim($email)),
        'name' => '',
        'password_hash' => '',
        'role' => $role,            // owner | admin
        'status' => $status,        // active | pending
        'verify_token' => null,
        'verify_expires' => null,
        'created_at' => date('c'),
        'created_by' => $_SESSION['admin_email'] ?? '',
    ];
}
function login_user($u) {
    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $u['id'];
    $_SESSION['admin_email'] = $u['email'];
    $_SESSION['admin_name'] = $u['name'];
    $_SESSION['admin_role'] = $u['role'];
}
// The signed-in user, re-read from users.json so a removed user loses access right away
function current_user() {
    if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_id'])) return null;
    foreach (load_users() as $u) {
        if ($u['id'] === $_SESSION['admin_id'] && ($u['status'] ?? '') === 'active') return $u;
    }
    return null;
}
function is_owner() { return ($_SESSION['admin_role'] ?? '') === 'owner'; }

// ---------- login rate limit (per IP) ----------
function client_ip() { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function login_locked_until($ip) {
    $a = read_json(LOGIN_ATTEMPTS_FILE, [])[$ip] ?? null;
    return ($a && ($a['locked_until'] ?? 0) > time()) ? (int)$a['locked_until'] : 0;
}
function login_register_failure($ip) {
    $all = read_json(LOGIN_ATTEMPTS_FILE, []);
    $now = time();
    foreach ($all as $k => $v) {   // forget old entries
        if (($v['locked_until'] ?? 0) < $now && $now - ($v['first'] ?? 0) > LOGIN_WINDOW_SECONDS) unset($all[$k]);
    }
    $a = $all[$ip] ?? ['count' => 0, 'first' => $now, 'locked_until' => 0];
    if ($now - $a['first'] > LOGIN_WINDOW_SECONDS) $a = ['count' => 0, 'first' => $now, 'locked_until' => 0];
    $a['count']++;
    if ($a['count'] >= LOGIN_MAX_ATTEMPTS) $a = ['count' => 0, 'first' => $now, 'locked_until' => $now + LOGIN_LOCKOUT_SECONDS];
    $all[$ip] = $a;
    write_json(LOGIN_ATTEMPTS_FILE, $all);
}
function login_clear_failures($ip) {
    $all = read_json(LOGIN_ATTEMPTS_FILE, []);
    if (isset($all[$ip])) { unset($all[$ip]); write_json(LOGIN_ATTEMPTS_FILE, $all); }
}

// ---------- URLs / e-mail ----------
function admin_base_url() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/index.php')), '/');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir;
}
// Plain PHP mail(); delivery depends on the host, so callers always show the link as a fallback
function send_invite_email($email, $token) {
    $link = admin_base_url() . '/verificar.php?token=' . urlencode($token);
    $host = preg_replace('/^www\./', '', preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));
    $subject = '=?UTF-8?B?' . base64_encode('Convite para o painel do blog ' . SITE_NAME) . '?=';
    $body = "Olá!\n\nVocê foi convidado(a) para administrar o blog da " . SITE_NAME . ".\n\n"
          . "Para confirmar seu e-mail e criar sua senha, abra o link abaixo (válido por 48 horas):\n\n"
          . $link . "\n\nSe você não esperava este convite, ignore este e-mail.\n";
    $headers = "From: " . SITE_NAME . " Blog <no-reply@$host>\r\n"
             . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
    return @mail($email, $subject, $body, $headers);
}

// ---------- posts (stored in the public posts.js, as JSON after the "=") ----------
const POSTS_HEADER = "// =====================================================================\n"
    . "//  CELLTRACK BLOG: every post lives in this file.\n"
    . "//  It is written by the admin panel (/admin/). Post through the panel;\n"
    . "//  if you ever edit by hand, keep everything after the = valid JSON.\n"
    . "// =====================================================================\n";

function load_posts() {
    if (!is_file(POSTS_JS)) return [];
    $src = (string)file_get_contents(POSTS_JS);
    $i = strpos($src, 'window.CELLTRACK_POSTS');
    if ($i === false) return [];
    $eq = strpos($src, '=', $i);
    $end = strrpos($src, ']');
    if ($eq === false || $end === false || $end < $eq) return [];
    $posts = json_decode(substr($src, $eq + 1, $end - $eq), true);
    if (!is_array($posts)) throw new RuntimeException('O arquivo posts.js não está num formato que o painel entende.');
    return $posts;
}
function save_posts($posts) {
    usort($posts, function ($a, $b) { return strcmp($b['date'] ?? '', $a['date'] ?? ''); });
    $json = json_encode(array_values($posts), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    write_file_atomic(POSTS_JS, POSTS_HEADER . 'window.CELLTRACK_POSTS = ' . $json . ";\n");
}
function get_post($slug) {
    foreach (load_posts() as $p) if (($p['slug'] ?? '') === $slug) return $p;
    return null;
}

function slugify($s) {
    $s = (string)$s;
    if (class_exists('Normalizer')) {
        $s = preg_replace('/\p{Mn}+/u', '', Normalizer::normalize($s, Normalizer::FORM_D));
    } else {
        $s = strtr($s, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n',
            'Á'=>'a','À'=>'a','Â'=>'a','Ã'=>'a','Ä'=>'a','É'=>'e','È'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ü'=>'u','Ç'=>'c','Ñ'=>'n']);
    }
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return substr(trim($s, '-'), 0, 80);
}
function unique_slug($base, $posts, $except = null) {
    $base = $base !== '' ? $base : 'post';
    $taken = [];
    foreach ($posts as $p) if (($p['slug'] ?? '') !== $except) $taken[$p['slug']] = true;
    $slug = $base; $n = 1;
    while (isset($taken[$slug])) $slug = $base . '-' . (++$n);
    return $slug;
}

// ---------- images ----------
// Saves an uploaded image to blog-images/ (resized to IMAGE_MAX_WIDTH when GD is available).
// Returns [path, error].
function save_uploaded_image($file, $name_hint = 'image') {
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'Erro ao enviar a imagem (arquivo grande demais?).'];
    if ($file['size'] > IMAGE_MAX_BYTES) return [null, 'A imagem deve ter até 8MB.'];
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($types[$mime])) return [null, 'Formato não suportado. Use JPG, PNG, WEBP ou GIF.'];
    if (!is_dir(BLOG_IMAGES_DIR)) mkdir(BLOG_IMAGES_DIR, 0755, true);

    $ext = $types[$mime];
    $base = (slugify($name_hint) ?: 'image') . '-' . date('Ymd') . '-' . bin2hex(random_bytes(3));
    $dest = BLOG_IMAGES_DIR . '/' . $base . '.' . $ext;

    $resized = false;
    $size = @getimagesize($file['tmp_name']);
    if ($size && $size[0] > IMAGE_MAX_WIDTH && $mime !== 'image/gif' && function_exists('imagecreatetruecolor')) {
        $load = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'][$mime];
        $src = function_exists($load) ? @$load($file['tmp_name']) : false;
        if ($src) {
            $w = IMAGE_MAX_WIDTH; $hgt = (int)round($size[1] * $w / $size[0]);
            $dst = imagecreatetruecolor($w, $hgt);
            if ($mime !== 'image/jpeg') { imagealphablending($dst, false); imagesavealpha($dst, true); }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $hgt, $size[0], $size[1]);
            $resized = $mime === 'image/png' ? imagepng($dst, $dest, 6)
                     : ($mime === 'image/webp' ? imagewebp($dst, $dest, 85) : imagejpeg($dst, $dest, 85));
            imagedestroy($src); imagedestroy($dst);
        }
    }
    if (!$resized && !move_uploaded_file($file['tmp_name'], $dest)) return [null, 'Não consegui salvar a imagem no servidor.'];
    return [BLOG_IMAGES_URL . '/' . $base . '.' . $ext, null];
}

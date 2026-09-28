<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

if (empty(load_users())) {
    header('Location: instalar.php');
    exit;
}
if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = trim($_POST['email'] ?? '');
$ip = client_ip();
$locked_until = login_locked_until($ip);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked_until) {
    csrf_check();
    $u = find_user_by_email($email);
    if ($u && ($u['status'] ?? '') === 'active' && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        login_clear_failures($ip);
        login_user($u);
        header('Location: index.php');
        exit;
    }
    login_register_failure($ip);
    $locked_until = login_locked_until($ip);
    $error = $locked_until ? '' : 'E-mail ou senha incorretos.';
}
if ($locked_until) {
    $minutes = max(1, (int)ceil(($locked_until - time()) / 60));
    $error = "Muitas tentativas erradas. Tente de novo em {$minutes} minuto(s).";
}

admin_head('Entrar');
auth_page_open('Área restrita', 'Painel do Blog');
?>
      <?php alerts(!empty($_GET['saiu']) ? 'Você saiu do painel.' : '', $error ? [$error] : []); ?>
      <form method="post" class="grid gap-4">
        <?php csrf_field(); ?>
        <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" required autofocus autocomplete="username" value="<?php e($email); ?>"></div>
        <div><label class="label" for="password">Senha</label><input class="field" id="password" type="password" name="password" required autocomplete="current-password"></div>
        <button class="btn w-full mt-2" type="submit">Entrar</button>
      </form>
      <p class="hint text-center mt-5">Esqueceu a senha? Peça ao administrador principal para reenviar seu convite.</p>
<?php
auth_page_close();
admin_foot();

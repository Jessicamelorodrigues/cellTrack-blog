<?php
// Invite link landing page: confirms the e-mail and lets the person create a password.
// Also used when the owner re-sends an invite to someone who forgot their password.
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$user = find_user_by_token($token);
$valid = $user && !empty($user['verify_expires']) && $user['verify_expires'] >= time();

$errors = [];
if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($name === '') $errors[] = 'Informe seu nome.';
    if (strlen($pass) < 8) $errors[] = 'A senha precisa ter pelo menos 8 caracteres.';
    elseif ($pass !== ($_POST['password_confirm'] ?? '')) $errors[] = 'As senhas não coincidem.';

    if (!$errors) {
        $users = load_users();
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['name'] = $name;
                $u['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
                $u['status'] = 'active';
                $u['verify_token'] = null;
                $u['verify_expires'] = null;
                $user = $u;
            }
        }
        unset($u);
        save_users($users);
        login_user($user);
        header('Location: index.php?bemvindo=1');
        exit;
    }
}

admin_head('Confirmar acesso');
if (!$valid) {
    auth_page_open('Convite', 'Link inválido'); ?>
      <p class="text-sm text-slate-600 leading-relaxed mb-6">Este link de convite não é válido ou já expirou (vale por 48 horas). Peça ao administrador principal para reenviar o convite.</p>
      <a class="btn w-full text-center" href="login.php">Ir para o login</a>
<?php
} else {
    auth_page_open('Convite', 'Confirmar acesso'); ?>
      <p class="text-sm text-slate-600 leading-relaxed mb-6">Você foi convidado(a) para o painel do blog da <strong class="text-[#1B3A6B]"><?php e(SITE_NAME); ?></strong> com o e-mail <strong class="text-[#1B3A6B]"><?php e($user['email']); ?></strong>. Defina seu nome e sua senha para começar.</p>
      <?php alerts('', $errors); ?>
      <form method="post" class="grid gap-4">
        <?php csrf_field(); ?>
        <input type="hidden" name="token" value="<?php e($token); ?>">
        <div><label class="label" for="name">Seu nome</label><input class="field" id="name" name="name" required value="<?php e($_POST['name'] ?? $user['name']); ?>"></div>
        <div><label class="label" for="password">Senha <span class="normal-case font-normal text-slate-400">(mín. 8 caracteres)</span></label><input class="field" id="password" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
        <div><label class="label" for="password_confirm">Confirmar senha</label><input class="field" id="password_confirm" type="password" name="password_confirm" required minlength="8" autocomplete="new-password"></div>
        <button class="btn w-full mt-2" type="submit">Confirmar e entrar</button>
      </form>
<?php
}
auth_page_close();
admin_foot();

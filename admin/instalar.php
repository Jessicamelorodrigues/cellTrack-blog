<?php
// First run only: creates the main (owner) account. Disabled as soon as one user exists.
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

if (!empty(load_users())) {
    header('Location: login.php');
    exit;
}

$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pass = $_POST['password'] ?? '';
    if ($name === '') $errors[] = 'Informe seu nome.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail válido.';
    if (strlen($pass) < 8) $errors[] = 'A senha precisa ter pelo menos 8 caracteres.';
    elseif ($pass !== ($_POST['password_confirm'] ?? '')) $errors[] = 'As senhas não coincidem.';

    if (!$errors) {
        if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
        if (!is_writable(DATA_DIR)) {
            $errors[] = 'O servidor não deixa gravar na pasta data/. Ajuste a permissão dela para 755 no gerenciador de arquivos.';
        } else {
            $u = new_user($email, 'owner', 'active');
            $u['name'] = $name;
            $u['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
            $u['created_by'] = 'instalação';
            save_users([$u]);
            login_user($u);
            header('Location: index.php?bemvindo=1');
            exit;
        }
    }
}

admin_head('Instalação');
auth_page_open('Primeiro acesso', 'Criar sua conta');
?>
      <p class="text-sm text-slate-500 leading-relaxed mb-6">Você vai ser o administrador principal: pode postar e convidar outras pessoas. Esta tela só aparece uma vez.</p>
      <?php alerts('', $errors); ?>
      <form method="post" class="grid gap-4">
        <?php csrf_field(); ?>
        <div><label class="label" for="name">Seu nome</label><input class="field" id="name" name="name" required value="<?php e($name); ?>"></div>
        <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" required value="<?php e($email); ?>"></div>
        <div><label class="label" for="password">Senha <span class="normal-case font-normal text-slate-400">(mín. 8 caracteres)</span></label><input class="field" id="password" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
        <div><label class="label" for="password_confirm">Confirmar senha</label><input class="field" id="password_confirm" type="password" name="password_confirm" required minlength="8" autocomplete="new-password"></div>
        <button class="btn w-full mt-2" type="submit">Criar conta e entrar</button>
      </form>
<?php
auth_page_close();
admin_foot();

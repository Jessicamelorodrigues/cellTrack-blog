<?php
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $new = $_POST['new_password'] ?? '';

    if (!password_verify($_POST['current_password'] ?? '', $me['password_hash'])) $errors[] = 'A senha atual está incorreta.';
    if ($name === '') $errors[] = 'Informe seu nome.';
    if ($new !== '' && strlen($new) < 8) $errors[] = 'A nova senha precisa ter pelo menos 8 caracteres.';
    elseif ($new !== '' && $new !== ($_POST['new_password_confirm'] ?? '')) $errors[] = 'As novas senhas não coincidem.';

    if (!$errors) {
        $users = load_users();
        foreach ($users as &$u) {
            if ($u['id'] === $me['id']) {
                $u['name'] = $name;
                if ($new !== '') $u['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
                $me = $u;
            }
        }
        unset($u);
        save_users($users);
        $_SESSION['admin_name'] = $name;
        $ok = $new !== '' ? 'Nome e senha atualizados.' : 'Dados atualizados.';
    }
}

admin_head('Minha conta');
admin_bar();
?>
<main class="max-w-xl mx-auto px-4 md:px-6 py-8">
  <?php alerts($ok, $errors); ?>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-8">
    <p class="text-[#29ABE2] text-xs font-bold uppercase tracking-widest mb-1">Minha conta</p>
    <h1 class="text-2xl font-extrabold text-[#1B3A6B] mb-1"><?php e($me['name'] ?: $me['email']); ?></h1>
    <p class="text-sm text-slate-500 mb-6"><?php e($me['email']); ?> · <?php echo $me['role'] === 'owner' ? 'Administrador principal' : 'Administrador'; ?></p>
    <form method="post" class="grid gap-4">
      <?php csrf_field(); ?>
      <div><label class="label" for="name">Nome</label><input id="name" name="name" class="field" required value="<?php e($me['name']); ?>"></div>
      <div><label class="label" for="new_password">Nova senha <span class="normal-case font-normal text-slate-400">(deixe em branco para manter)</span></label><input id="new_password" type="password" name="new_password" minlength="8" class="field" autocomplete="new-password"></div>
      <div><label class="label" for="new_password_confirm">Confirmar nova senha</label><input id="new_password_confirm" type="password" name="new_password_confirm" minlength="8" class="field" autocomplete="new-password"></div>
      <div class="border-t border-slate-100 pt-4"><label class="label" for="current_password">Senha atual <span class="normal-case font-normal text-slate-400">(para confirmar)</span></label><input id="current_password" type="password" name="current_password" required class="field" autocomplete="current-password"></div>
      <button class="btn mt-2 justify-self-start" type="submit">Salvar</button>
    </form>
  </div>
</main>
<?php admin_foot();

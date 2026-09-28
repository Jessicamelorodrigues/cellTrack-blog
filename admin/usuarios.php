<?php
require_once __DIR__ . '/includes/auth.php';

if (!is_owner()) {
    http_response_code(403);
    die('Apenas o administrador principal pode gerenciar usuários.');
}

$errors = [];
$notice = '';
$invite_link = '';
$users = load_users();

// Creates a fresh 48h link for $email and tries to e-mail it. The link is always shown too,
// because shared-hosting mail() isn't guaranteed to deliver.
function issue_invite(&$u, &$notice, &$invite_link, $what) {
    $u['verify_token'] = bin2hex(random_bytes(32));
    $u['verify_expires'] = time() + USER_INVITE_TTL_SECONDS;
    $sent = send_invite_email($u['email'], $u['verify_token']);
    $invite_link = admin_base_url() . '/verificar.php?token=' . urlencode($u['verify_token']);
    $notice = $sent
        ? "$what enviado por e-mail para {$u['email']}. Se não chegar em alguns minutos, copie o link abaixo e mande por WhatsApp."
        : "Não consegui confirmar o envio do e-mail. Copie o link abaixo e mande para {$u['email']}:";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($action === 'invite') {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        } elseif (find_user_by_email($email)) {
            $errors[] = 'Já existe um usuário com esse e-mail.';
        } else {
            $u = new_user($email, ($_POST['role'] ?? '') === 'owner' ? 'owner' : 'admin', 'pending');
            issue_invite($u, $notice, $invite_link, 'Convite');
            $users[] = $u;
            save_users($users);
        }
    }

    // Re-send a pending invite, or send a "create a new password" link to an active user
    if ($action === 'resend' || $action === 'reset') {
        foreach ($users as &$u) {
            if ($u['id'] === $id) issue_invite($u, $notice, $invite_link, $action === 'reset' ? 'Link para criar nova senha' : 'Convite');
        }
        unset($u);
        save_users($users);
    }

    if ($action === 'remove') {
        $target = null;
        foreach ($users as $u) if ($u['id'] === $id) $target = $u;
        $owners = array_filter($users, function ($u) { return $u['role'] === 'owner' && $u['status'] === 'active'; });
        if (!$target) {
            // already gone
        } elseif ($target['id'] === $me['id']) {
            $errors[] = 'Você não pode remover o seu próprio acesso.';
        } elseif ($target['role'] === 'owner' && $target['status'] === 'active' && count($owners) <= 1) {
            $errors[] = 'Não é possível remover o único administrador principal.';
        } else {
            $users = array_values(array_filter($users, function ($u) use ($id) { return $u['id'] !== $id; }));
            save_users($users);
            $notice = "Acesso de {$target['email']} removido.";
        }
    }

    if ($action === 'role') {
        foreach ($users as &$u) {
            if ($u['id'] === $id && $u['id'] !== $me['id']) {
                $u['role'] = $u['role'] === 'owner' ? 'admin' : 'owner';
                $notice = $u['role'] === 'owner' ? "{$u['email']} agora é administrador principal." : "{$u['email']} agora é administrador.";
            }
        }
        unset($u);
        save_users($users);
    }

    $users = load_users();
}

admin_head('Usuários');
admin_bar();
?>
<main class="max-w-6xl mx-auto px-4 md:px-6 py-8 grid gap-6">
  <div><?php alerts($notice, $errors); ?>
  <?php if ($invite_link): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <label class="label" for="invite-link">Link (válido por 48h)</label>
      <div class="flex gap-2 flex-wrap">
        <input id="invite-link" type="text" readonly onclick="this.select()" value="<?php e($invite_link); ?>" class="field font-mono text-xs flex-1 min-w-0">
        <button type="button" class="btn-ghost" onclick="navigator.clipboard.writeText(document.getElementById('invite-link').value); this.textContent='Copiado!'">Copiar</button>
      </div>
    </div>
  <?php endif; ?>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-8">
    <p class="text-[#29ABE2] text-xs font-bold uppercase tracking-widest mb-1">Acesso ao painel</p>
    <h1 class="text-2xl md:text-3xl font-extrabold text-[#1B3A6B] mb-6">Usuários (<?php echo count($users); ?>)</h1>
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="text-left text-[11px] uppercase tracking-widest text-slate-400">
          <th class="py-3 pr-4 font-bold">Nome / e-mail</th><th class="py-3 pr-4 font-bold">Papel</th>
          <th class="py-3 pr-4 font-bold">Status</th><th class="py-3 pr-4 font-bold">Desde</th><th class="py-3 font-bold text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): $self = $u['id'] === $me['id']; ?>
        <tr class="border-t border-slate-100 align-middle">
          <td data-label="Usuário" class="py-4 pr-4">
            <span class="block font-semibold text-[#1B3A6B]"><?php e($u['name'] ?: '—'); ?><?php if ($self): ?> <span class="text-xs font-normal text-slate-400">(você)</span><?php endif; ?></span>
            <span class="text-slate-500"><?php e($u['email']); ?></span>
          </td>
          <td data-label="Papel" class="py-4 pr-4 text-slate-600"><?php echo $u['role'] === 'owner' ? 'Principal' : 'Administrador'; ?></td>
          <td data-label="Status" class="py-4 pr-4"><span class="pill <?php echo $u['status'] === 'active' ? 'pill-pub' : 'pill-draft'; ?>"><?php echo $u['status'] === 'active' ? 'Ativo' : 'Convite pendente'; ?></span></td>
          <td data-label="Desde" class="py-4 pr-4 text-slate-500 whitespace-nowrap"><?php echo format_date($u['created_at']); ?></td>
          <td data-label="Ações" class="py-4">
            <div class="flex items-center justify-end gap-4 flex-wrap font-semibold">
            <?php if (!$self): ?>
              <?php foreach ($u['status'] === 'pending'
                    ? [['resend', 'Reenviar convite', 'text-[#1B3A6B]']]
                    : [['reset', 'Nova senha', 'text-[#1B3A6B]'], ['role', $u['role'] === 'owner' ? 'Tornar admin' : 'Tornar principal', 'text-[#29ABE2]']] as [$act, $label, $cls]): ?>
                <form method="post">
                  <?php csrf_field(); ?><input type="hidden" name="action" value="<?php e($act); ?>"><input type="hidden" name="id" value="<?php e($u['id']); ?>">
                  <button type="submit" class="<?php echo $cls; ?> hover:underline"><?php e($label); ?></button>
                </form>
              <?php endforeach; ?>
              <form method="post" onsubmit="return confirm('Remover o acesso de <?php echo h(addslashes($u['email'])); ?>?');">
                <?php csrf_field(); ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?php e($u['id']); ?>">
                <button type="submit" class="text-red-600 hover:text-red-800">Remover</button>
              </form>
            <?php else: ?>
              <a href="conta.php" class="text-[#1B3A6B] hover:underline">Minha conta</a>
            <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-8">
    <h2 class="text-xl font-bold text-[#1B3A6B] mb-1">Convidar pessoa</h2>
    <p class="text-sm text-slate-500 leading-relaxed mb-5">Ela recebe um e-mail com um link válido por 48h para confirmar o e-mail e criar a própria senha.</p>
    <form method="post" class="grid sm:grid-cols-[1fr_auto_auto] gap-3 items-end">
      <?php csrf_field(); ?><input type="hidden" name="action" value="invite">
      <div><label class="label" for="email">E-mail</label><input id="email" type="email" name="email" required class="field" placeholder="pessoa@exemplo.com"></div>
      <div>
        <label class="label" for="role">Papel</label>
        <select id="role" name="role" class="field"><option value="admin">Administrador (só posta)</option><option value="owner">Principal (posta e convida)</option></select>
      </div>
      <button class="btn" type="submit">Enviar convite</button>
    </form>
  </div>
</main>
<?php admin_foot();

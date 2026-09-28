<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';
try { $posts = load_posts(); } catch (RuntimeException $ex) { $posts = []; $error = $ex->getMessage(); }

$ok = '';
if (!empty($_GET['bemvindo'])) $ok = 'Bem-vindo(a), ' . ($me['name'] ?: $me['email']) . '! Clique em "+ Novo post" para escrever.';
if (($_GET['salvo'] ?? '') === 'publicado') $ok = 'Post publicado! Ele já aparece no blog.';
if (($_GET['salvo'] ?? '') === 'rascunho') $ok = 'Rascunho salvo. Ele não aparece no site até você publicar.';
if (!empty($_GET['excluido'])) $ok = 'Post excluído.';

admin_head('Posts');
admin_bar();
?>
<main class="max-w-6xl mx-auto px-4 md:px-6 py-8">
  <?php alerts($ok, $error ? [$error] : []); ?>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 md:p-8">
    <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
      <div>
        <p class="text-[#29ABE2] text-xs font-bold uppercase tracking-widest mb-1">Blog</p>
        <h1 class="text-2xl md:text-3xl font-extrabold text-[#1B3A6B]">Posts (<?php echo count($posts); ?>)</h1>
      </div>
      <a class="btn" href="post.php">+ Novo post</a>
    </div>

    <?php if (!$posts): ?>
      <p class="text-slate-500 py-10 text-center">Nenhum post ainda. Clique em "+ Novo post" para escrever o primeiro.</p>
    <?php else: ?>
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="text-left text-[11px] uppercase tracking-widest text-slate-400">
          <th class="py-3 pr-4 font-bold">Título</th><th class="py-3 pr-4 font-bold">Categoria</th>
          <th class="py-3 pr-4 font-bold">Status</th><th class="py-3 pr-4 font-bold">Data</th><th class="py-3 font-bold text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($posts as $p): $draft = !empty($p['draft']); ?>
        <tr class="border-t border-slate-100 align-middle">
          <td data-label="Título" class="py-4 pr-4">
            <div class="flex items-center gap-3">
              <?php if (!empty($p['cover'])): ?><img src="../<?php e($p['cover']); ?>" alt="" class="hidden sm:block w-16 h-10 object-cover rounded-lg bg-[#F0F7FF] shrink-0"><?php endif; ?>
              <span class="font-semibold text-[#1B3A6B] leading-snug"><?php e($p['title'] ?? ''); ?></span>
            </div>
          </td>
          <td data-label="Categoria" class="py-4 pr-4 text-slate-500"><?php e($p['tag'] ?? ''); ?></td>
          <td data-label="Status" class="py-4 pr-4"><span class="pill <?php echo $draft ? 'pill-draft' : 'pill-pub'; ?>"><?php echo $draft ? 'Rascunho' : 'Publicado'; ?></span></td>
          <td data-label="Data" class="py-4 pr-4 text-slate-500 whitespace-nowrap"><?php echo format_date($p['date'] ?? ''); ?></td>
          <td data-label="Ações" class="py-4">
            <div class="flex items-center justify-end gap-4 font-semibold">
              <a class="text-[#1B3A6B] hover:text-[#29ABE2]" href="post.php?slug=<?php echo urlencode($p['slug']); ?>">Editar</a>
              <a class="text-[#29ABE2] hover:text-[#1B3A6B]" target="_blank" href="../post.html?p=<?php echo urlencode($p['slug']); ?><?php echo $draft ? '&preview' : ''; ?>">Ver</a>
              <form method="post" action="excluir.php" onsubmit="return confirm('Excluir este post? Essa ação não pode ser desfeita.');">
                <?php csrf_field(); ?>
                <input type="hidden" name="slug" value="<?php e($p['slug']); ?>">
                <button type="submit" class="text-red-600 hover:text-red-800">Excluir</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>
<?php admin_foot();

<?php
// Shared admin markup, in the CellTrack visual identity (same Tailwind CDN + brand colors as the site).

function admin_head($title, $extra_head = '') { ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?php e($title); ?> | Painel CellTrack</title>
<link rel="icon" type="image/png" href="../icon.png">
<script src="https://cdn.tailwindcss.com?plugins=typography"></script>
<?php echo $extra_head; ?>
<style>
  .hero-bg { background: linear-gradient(135deg, #EBF5FB 0%, #F0F7FF 60%, #EBF8FF 100%); }
  .field { width: 100%; border: 1px solid #E2E8F0; border-radius: 0.75rem; padding: 0.625rem 0.875rem; font-size: 0.9rem; background: #fff; transition: border-color .15s, box-shadow .15s; }
  .field:focus { outline: none; border-color: #29ABE2; box-shadow: 0 0 0 3px rgba(41,171,226,.18); }
  .label { display: block; font-size: 0.75rem; font-weight: 700; color: #1B3A6B; text-transform: uppercase; letter-spacing: .06em; margin-bottom: .4rem; }
  .hint { font-size: 0.75rem; color: #94A3B8; margin-top: .35rem; }
  .btn { display: inline-block; background: #1B3A6B; color: #fff; font-weight: 600; font-size: .875rem; padding: .7rem 1.4rem; border-radius: .5rem; transition: background .15s; box-shadow: 0 4px 6px -1px rgba(0,0,0,.08); }
  .btn:hover { background: #29ABE2; }
  .btn-ghost { display: inline-block; border: 2px solid #1B3A6B; color: #1B3A6B; font-weight: 600; font-size: .875rem; padding: .55rem 1.2rem; border-radius: .5rem; transition: all .15s; }
  .btn-ghost:hover { background: #1B3A6B; color: #fff; }
  .pill { display: inline-block; font-size: .72rem; font-weight: 600; padding: .2rem .65rem; border-radius: 9999px; }
  .pill-pub { background: #D1FAE5; color: #065F46; }
  .pill-draft { background: #FEF3C7; color: #92400E; }
  .alert { font-size: .875rem; border-radius: .75rem; padding: .75rem 1rem; margin-bottom: 1rem; }
  .alert-ok { background: #F0F7FF; border: 1px solid rgba(41,171,226,.4); color: #1B3A6B; }
  .alert-err { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; }
  /* Tables become stacked cards on phones */
  @media (max-width: 700px) {
    .admin-table thead { display: none; }
    .admin-table tr { display: block; border: 1px solid #F1F5F9; border-radius: 1rem; padding: .75rem 1rem; margin-bottom: .75rem; }
    .admin-table td { display: flex; justify-content: space-between; gap: 1rem; padding: .3rem 0 !important; border: 0 !important; text-align: right; }
    .admin-table td::before { content: attr(data-label); font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94A3B8; text-align: left; }
  }
</style>
</head>
<body class="font-sans text-slate-800 antialiased bg-[#F8FAFC] min-h-screen">
<?php }

function admin_bar() {
    $page = basename($_SERVER['SCRIPT_NAME']);
    $link = function ($href, $label) use ($page) {
        $active = $page === $href || ($href === 'index.php' && $page === 'post.php');
        echo '<a href="' . h($href) . '" class="px-3 py-1.5 rounded-lg ' . ($active ? 'bg-[#F0F7FF] text-[#1B3A6B] font-semibold' : 'text-slate-600 hover:text-[#1B3A6B]') . '">' . h($label) . '</a>';
    }; ?>
<nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-slate-100 shadow-sm">
  <div class="max-w-6xl mx-auto px-4 md:px-6 py-3 flex items-center gap-3 flex-wrap">
    <a href="index.php"><img src="../logo.png" alt="CellTrack" class="h-8"></a>
    <span class="text-sm font-bold text-[#1B3A6B] border-l border-slate-200 pl-3">Painel do Blog</span>
    <div class="ml-auto flex items-center gap-1 flex-wrap text-sm font-medium">
      <?php $link('index.php', 'Posts'); ?>
      <?php if (is_owner()) $link('usuarios.php', 'Usuários'); ?>
      <?php $link('conta.php', 'Minha conta'); ?>
      <a href="../blog.html?preview" target="_blank" class="px-3 py-1.5 text-slate-600 hover:text-[#1B3A6B]">Ver blog &nearr;</a>
      <a href="logout.php" class="ml-1 font-semibold text-[#1B3A6B] border border-slate-200 hover:border-[#1B3A6B] px-3 py-1.5 rounded-lg transition-colors">Sair</a>
    </div>
  </div>
</nav>
<?php }

// Centered card with the logo, for login / install / invite pages
function auth_page_open($eyebrow, $title) { ?>
<div class="hero-bg min-h-screen px-4 py-10 md:py-16 relative overflow-hidden">
  <img src="../icon.png" alt="" aria-hidden="true" style="position:absolute;right:-90px;top:50%;transform:translateY(-50%);width:420px;opacity:0.08;pointer-events:none;">
  <div class="relative max-w-md mx-auto">
    <div class="text-center mb-8">
      <img src="../logo.png" alt="CellTrack" class="h-10 mx-auto mb-6">
      <p class="text-[#29ABE2] text-xs font-bold uppercase tracking-widest mb-2"><?php e($eyebrow); ?></p>
      <h1 class="text-3xl font-extrabold text-[#1B3A6B]"><?php e($title); ?></h1>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 md:p-8">
<?php }
function auth_page_close() { ?>
    </div>
    <p class="text-center mt-8"><a href="../index.html" class="text-sm font-semibold text-[#29ABE2] hover:text-[#1B3A6B]">&larr; Voltar para o site</a></p>
  </div>
</div>
<?php }

function alerts($ok = '', $errors = []) {
    if ($ok) echo '<div class="alert alert-ok">' . h($ok) . '</div>';
    foreach ((array)$errors as $err) echo '<div class="alert alert-err">' . h($err) . '</div>';
}

function admin_foot() { ?>
</body>
</html>
<?php }

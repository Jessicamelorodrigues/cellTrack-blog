<?php
require_once __DIR__ . '/includes/auth.php';

$slug_param = $_GET['slug'] ?? $_POST['original_slug'] ?? '';
$existing = $slug_param !== '' ? get_post($slug_param) : null;
if ($slug_param !== '' && !$existing) {
    header('Location: index.php');
    exit;
}
$is_edit = (bool)$existing;

$v = $existing ?: [
    'title' => '', 'summary' => '', 'date' => date('Y-m-d'), 'tag' => '', 'author' => $me['name'] ?? '',
    'link' => '', 'slug' => '', 'cover' => '', 'body' => '', 'draft' => false,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach (['title', 'summary', 'date', 'tag', 'author', 'link', 'body'] as $k) $v[$k] = trim($_POST[$k] ?? '');
    $publish = ($_POST['action'] ?? '') === 'publish';
    $v['draft'] = !$publish;
    $slug_in = slugify($_POST['slug'] ?? '');
    $v['slug'] = $slug_in;

    if ($v['title'] === '') $errors[] = 'Dê um título ao post.';
    if ($v['body'] === '') $errors[] = 'O post está sem texto.';
    if ($publish && $v['summary'] === '') $errors[] = 'Escreva um resumo curto: ele aparece nos cards e no Google.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['date'])) $v['date'] = date('Y-m-d');
    if ($v['link'] !== '' && !filter_var($v['link'], FILTER_VALIDATE_URL)) $errors[] = 'O link externo parece inválido (comece com https://).';

    // Cover image
    if (!empty($_POST['remove_cover'])) $v['cover'] = '';
    [$cover, $img_err] = save_uploaded_image($_FILES['cover'] ?? null, $v['title'] ?: 'capa');
    if ($img_err) $errors[] = $img_err;
    if ($cover) $v['cover'] = $cover;

    if (!$errors) {
        $posts = load_posts();
        $old = $is_edit ? $existing['slug'] : null;
        $v['slug'] = unique_slug($slug_in ?: slugify($v['title']), $posts, $old);
        $post = ['slug' => $v['slug'], 'date' => $v['date']];
        foreach (['tag', 'title', 'summary', 'cover', 'author', 'link'] as $k) if ($v[$k] !== '') $post[$k] = $v[$k];
        if ($v['draft']) $post['draft'] = true;
        $post['body'] = $v['body'];

        $posts = array_values(array_filter($posts, function ($p) use ($old) { return ($p['slug'] ?? '') !== $old; }));
        $posts[] = $post;
        save_posts($posts);
        header('Location: index.php?salvo=' . ($v['draft'] ? 'rascunho' : 'publicado'));
        exit;
    }
}

$published = $is_edit && empty($existing['draft']);
$tags = BLOG_TAGS;
foreach (load_posts() as $p) if (!empty($p['tag']) && !in_array($p['tag'], $tags, true)) $tags[] = $p['tag'];

admin_head($is_edit ? 'Editar post' : 'Novo post',
    '<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.2/marked.min.js"></script>');
admin_bar();
?>
<style>
  .tool { min-width: 2.25rem; height: 2.25rem; padding: 0 .6rem; border-radius: .5rem; font-size: .85rem; color: #1B3A6B; font-weight: 600; }
  .tool:hover { background: #EBF5FB; }
  .tab.active { color: #1B3A6B; border-color: #29ABE2; }
  #body { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .875rem; line-height: 1.7; min-height: 420px; }
</style>
<main class="max-w-5xl mx-auto px-4 md:px-6 py-8">
  <a href="index.php" class="inline-block text-sm font-semibold text-[#29ABE2] hover:text-[#1B3A6B] mb-4">&larr; Todos os posts</a>
  <?php alerts('', $errors); ?>

  <form method="post" enctype="multipart/form-data" id="post-form" class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <?php csrf_field(); ?>
    <input type="hidden" name="original_slug" value="<?php e($is_edit ? $existing['slug'] : ''); ?>">

    <div class="flex items-center gap-6 border-b border-slate-100 px-6">
      <h1 class="py-4 text-lg font-extrabold text-[#1B3A6B] mr-auto"><?php echo $is_edit ? 'Editar post' : 'Novo post'; ?></h1>
      <button type="button" data-tab="write" class="tab active py-4 text-sm font-semibold text-slate-400 border-b-2 border-transparent">Escrever</button>
      <button type="button" data-tab="preview" class="tab py-4 text-sm font-semibold text-slate-400 border-b-2 border-transparent">Prévia</button>
    </div>

    <!-- WRITE -->
    <div id="tab-write" class="p-6 grid gap-5">
      <div>
        <label class="label" for="title">Título</label>
        <input id="title" name="title" class="field text-lg font-semibold text-[#1B3A6B]" required value="<?php e($v['title']); ?>" placeholder="Ex.: CellTrack wins the Pressure Chamber pitch competition">
      </div>
      <div>
        <label class="label" for="summary">Resumo</label>
        <textarea id="summary" name="summary" rows="2" class="field" placeholder="1–2 frases. Aparece nos cards e no Google."><?php e($v['summary']); ?></textarea>
        <p class="hint"><span id="summary-count">0</span> caracteres · ideal até 160</p>
      </div>
      <div class="grid sm:grid-cols-3 gap-4">
        <div><label class="label" for="date">Data</label><input id="date" name="date" type="date" class="field" value="<?php e($v['date']); ?>"></div>
        <div>
          <label class="label" for="tag">Categoria</label>
          <input id="tag" name="tag" list="tag-options" class="field" value="<?php e($v['tag']); ?>" placeholder="Ex.: Publication">
          <datalist id="tag-options"><?php foreach ($tags as $t): ?><option value="<?php e($t); ?>"><?php endforeach; ?></datalist>
        </div>
        <div><label class="label" for="author">Autor <span class="normal-case font-normal text-slate-400">(opcional)</span></label><input id="author" name="author" class="field" value="<?php e($v['author']); ?>" placeholder="Ex.: CellTrack Team"></div>
      </div>

      <div>
        <span class="label">Imagem de capa <span class="normal-case font-normal text-slate-400">(opcional)</span></span>
        <div class="flex items-center gap-4 flex-wrap">
          <div id="cover-thumb" class="<?php echo $v['cover'] ? '' : 'hidden'; ?> w-40 aspect-[16/9] rounded-xl overflow-hidden bg-[#F0F7FF] border border-slate-100">
            <img class="w-full h-full object-cover" alt="" src="<?php echo $v['cover'] ? '../' . h($v['cover']) : ''; ?>">
          </div>
          <label class="cursor-pointer btn-ghost">
            Escolher imagem<input id="cover-file" name="cover" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="hidden">
          </label>
          <?php if ($v['cover']): ?>
            <label class="flex items-center gap-2 text-sm text-slate-500"><input type="checkbox" name="remove_cover" value="1" id="remove-cover" class="accent-[#1B3A6B]"> Remover capa</label>
          <?php endif; ?>
        </div>
        <p class="hint">JPG, PNG, WEBP ou GIF, até 8MB. Fotos grandes são reduzidas automaticamente.</p>
      </div>

      <div>
        <span class="label">Texto</span>
        <div class="border border-slate-200 rounded-xl overflow-hidden focus-within:border-[#29ABE2] focus-within:ring-[3px] focus-within:ring-[#29ABE2]/20">
          <div class="flex flex-wrap items-center gap-0.5 border-b border-slate-100 bg-[#F8FAFC] px-2 py-1.5">
            <button type="button" class="tool" data-md="bold" title="Negrito (Ctrl+B)"><b>B</b></button>
            <button type="button" class="tool italic" data-md="italic" title="Itálico (Ctrl+I)">I</button>
            <span class="w-px h-5 bg-slate-200 mx-1"></span>
            <button type="button" class="tool" data-md="h2" title="Subtítulo">H2</button>
            <button type="button" class="tool" data-md="h3" title="Subtítulo menor">H3</button>
            <span class="w-px h-5 bg-slate-200 mx-1"></span>
            <button type="button" class="tool" data-md="ul">• Lista</button>
            <button type="button" class="tool" data-md="ol">1. Lista</button>
            <button type="button" class="tool" data-md="quote">❝ Citação</button>
            <span class="w-px h-5 bg-slate-200 mx-1"></span>
            <button type="button" class="tool" data-md="link" title="Link (Ctrl+K)">🔗 Link</button>
            <label class="tool cursor-pointer inline-flex items-center" title="Imagem no meio do texto">🖼 Imagem<input id="body-file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="hidden"></label>
          </div>
          <textarea id="body" name="body" class="w-full p-4 border-0 focus:outline-none resize-y" placeholder="Escreva o post aqui. Deixe uma linha em branco entre parágrafos."><?php e($v['body']); ?></textarea>
        </div>
        <p class="hint">Formatação: **negrito**, *itálico*, ## subtítulo, - item de lista, &gt; citação. Use os botões acima se preferir.</p>
      </div>

      <details>
        <summary class="cursor-pointer text-sm font-semibold text-[#1B3A6B] select-none">Opções avançadas</summary>
        <div class="grid sm:grid-cols-2 gap-4 mt-4">
          <div>
            <label class="label" for="link">Link externo <span class="normal-case font-normal text-slate-400">(opcional)</span></label>
            <input id="link" name="link" type="url" class="field" value="<?php e($v['link']); ?>" placeholder="https://… (matéria, artigo, paper)">
            <p class="hint">Mostra o botão "Read the original" no fim do post.</p>
          </div>
          <div>
            <label class="label" for="slug">Endereço do post</label>
            <input id="slug" name="slug" class="field font-mono text-xs" value="<?php e($v['slug']); ?>">
            <p class="hint">post.html?p=<span id="slug-preview"></span></p>
          </div>
        </div>
      </details>
    </div>

    <!-- PREVIEW -->
    <div id="tab-preview" class="hidden">
      <div class="hero-bg px-4 md:px-8 py-8">
        <p class="text-[#29ABE2] text-[11px] font-bold uppercase tracking-widest mb-3">Como aparece na lista</p>
        <div id="card-preview" class="max-w-sm mb-10"></div>
        <p class="text-[#29ABE2] text-[11px] font-bold uppercase tracking-widest mb-3">Página do post</p>
        <article id="article-preview" class="bg-white rounded-3xl border border-slate-100 shadow-sm px-6 py-12 md:px-12 md:py-16"></article>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="md:sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-slate-100 rounded-b-2xl px-6 py-4 flex items-center gap-3 flex-wrap">
      <a href="index.php" class="text-sm text-slate-400 hover:text-slate-700 mr-auto">Cancelar</a>
      <span class="pill <?php echo !$is_edit ? 'bg-slate-100 text-slate-600' : ($published ? 'pill-pub' : 'pill-draft'); ?>"><?php echo !$is_edit ? 'Novo' : ($published ? 'Publicado' : 'Rascunho'); ?></span>
      <button type="submit" name="action" value="draft" class="btn-ghost"><?php echo $published ? 'Despublicar (virar rascunho)' : 'Salvar rascunho'; ?></button>
      <button type="submit" name="action" value="publish" class="btn"><?php echo $published ? 'Atualizar publicação' : 'Publicar'; ?></button>
    </div>
  </form>
</main>

<script src="../blog.js"></script>
<script>
(function () {
  const $ = id => document.getElementById(id);
  const CSRF = <?php echo json_encode(csrf_token()); ?>;
  const isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;
  let dirty = false, coverUrl = null;
  // Images are stored as "blog-images/…" (relative to the site root); the panel lives one folder down
  const resolve = s => /^(https?:|data:|blob:|\/)/.test(s) ? s : '../' + s;

  const slugify = s => String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80);
  let slugTouched = isEdit;
  function meta() {
    $('summary-count').textContent = $('summary').value.length;
    $('slug-preview').textContent = $('slug').value || slugify($('title').value);
  }
  $('title').addEventListener('input', () => { if (!slugTouched) $('slug').value = slugify($('title').value); });
  $('slug').addEventListener('input', () => { slugTouched = true; });
  $('post-form').addEventListener('input', () => { dirty = true; meta(); });
  $('post-form').addEventListener('submit', () => { dirty = false; });
  window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  meta();

  // Cover preview
  $('cover-file').addEventListener('change', e => {
    const f = e.target.files[0];
    if (!f) return;
    if (coverUrl) URL.revokeObjectURL(coverUrl);
    coverUrl = URL.createObjectURL(f);
    $('cover-thumb').classList.remove('hidden');
    $('cover-thumb').querySelector('img').src = coverUrl;
    if ($('remove-cover')) $('remove-cover').checked = false;
    dirty = true;
  });

  // Markdown toolbar
  const ta = $('body');
  function edit(fn) {
    const s = ta.selectionStart, e = ta.selectionEnd;
    const r = fn(ta.value.slice(s, e));
    ta.setRangeText(r.text, s, e, 'end');
    if (r.select) ta.setSelectionRange(s + r.select[0], s + r.select[1]);
    ta.focus(); ta.dispatchEvent(new Event('input', { bubbles: true }));
  }
  function insertBlock(text) {
    const s = ta.selectionStart, v = ta.value;
    const before = v.slice(0, s).replace(/\n*$/, ''), after = v.slice(ta.selectionEnd).replace(/^\n*/, '');
    ta.value = (before ? before + '\n\n' : '') + text + '\n\n' + after;
    const pos = (before ? before.length + 2 : 0) + text.length;
    ta.setSelectionRange(pos, pos); ta.focus(); ta.dispatchEvent(new Event('input', { bubbles: true }));
  }
  const wrap = (mark, ph) => sel => { const t = sel || ph; return { text: mark + t + mark, select: [mark.length, mark.length + t.length] }; };
  const prefix = (pre, ph) => sel => {
    const text = (sel || ph).split('\n').map((l, i) => (typeof pre === 'function' ? pre(i) : pre) + l.replace(/^(#+ |[-*] |\d+\. |> )/, '')).join('\n');
    return { text, select: sel ? null : [text.length - ph.length, text.length] };
  };
  const actions = {
    bold: wrap('**', 'texto em negrito'), italic: wrap('*', 'texto em itálico'),
    h2: prefix('## ', 'Subtítulo'), h3: prefix('### ', 'Subtítulo menor'),
    ul: prefix('- ', 'Item da lista'), ol: prefix(i => `${i + 1}. `, 'Item da lista'), quote: prefix('> ', 'Citação'),
    link: sel => {
      const url = prompt('Cole o endereço do link (https://…):', 'https://');
      if (!url || url === 'https://') return { text: sel };
      const t = sel || 'texto do link';
      return { text: `[${t}](${url.trim()})`, select: [1, 1 + t.length] };
    },
  };
  document.querySelectorAll('[data-md]').forEach(b => b.addEventListener('click', () => edit(actions[b.dataset.md])));
  ta.addEventListener('keydown', e => {
    if (!(e.ctrlKey || e.metaKey)) return;
    const k = { b: 'bold', i: 'italic', k: 'link' }[e.key];
    if (k) { e.preventDefault(); edit(actions[k]); }
  });

  // Image inside the text: uploaded right away, then inserted as Markdown
  $('body-file').addEventListener('change', async e => {
    const f = e.target.files[0]; e.target.value = '';
    if (!f) return;
    const fd = new FormData();
    fd.append('image', f); fd.append('hint', $('title').value || 'imagem'); fd.append('csrf_token', CSRF);
    const label = e.target.closest('label'); label.style.opacity = .5;
    try {
      const r = await fetch('upload.php', { method: 'POST', body: fd, credentials: 'same-origin' });
      const j = await r.json().catch(() => ({ error: 'Sua sessão expirou. Salve o texto em outro lugar e entre de novo.' }));
      if (!r.ok || !j.path) throw new Error(j.error || 'Não consegui enviar a imagem.');
      const alt = prompt('Descrição curta da imagem (para acessibilidade):', '') || '';
      insertBlock(`![${alt}](${j.path})`);
    } catch (err) { alert(err.message); }
    finally { label.style.opacity = 1; }
  });

  // Preview: same renderer as the public blog
  function showTab(name) {
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
    $('tab-write').classList.toggle('hidden', name !== 'write');
    $('tab-preview').classList.toggle('hidden', name !== 'preview');
    if (name !== 'preview') return;
    const removed = $('remove-cover') && $('remove-cover').checked;
    const existingCover = <?php echo json_encode($v['cover']); ?>;
    const p = {
      title: $('title').value, summary: $('summary').value, date: $('date').value, tag: $('tag').value,
      author: $('author').value, link: $('link').value, body: $('body').value,
      slug: $('slug').value || slugify($('title').value) || 'preview',
      cover: coverUrl || (removed ? '' : existingCover),
    };
    $('card-preview').innerHTML = CT.card(p, 0, { href: '#', coverSrc: p.cover && resolve(p.cover) }).replace('reveal up', '');
    $('article-preview').innerHTML = CT.article(p, { resolve });
  }
  document.querySelectorAll('.tab').forEach(t => t.addEventListener('click', () => showTab(t.dataset.tab)));
  $('card-preview').addEventListener('click', e => e.preventDefault());
})();
</script>
<?php admin_foot();

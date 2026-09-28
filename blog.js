// Shared blog helpers for index.html, blog.html, post.html and admin.html.
// Posts come from posts.js (window.CELLTRACK_POSTS).
(function () {
  const preview = /[?&]preview\b/.test(location.search);

  const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  const fmtDate = d => {
    const [y, m, day] = String(d || '').split('-').map(Number);
    if (!y || !m) return esc(d);
    return new Date(y, m - 1, day || 1).toLocaleDateString('en-US',
      day ? { month: 'short', day: 'numeric', year: 'numeric' } : { month: 'short', year: 'numeric' });
  };

  const sortPosts = list => list.slice().sort((a, b) => String(b.date).localeCompare(String(a.date)));

  const postUrl = p => 'post.html?p=' + encodeURIComponent(p.slug) + (preview ? '&preview' : '');

  // Markdown → HTML (marked is loaded from the CDN on pages that show a full post)
  const md = text => window.marked ? window.marked.parse(String(text || '').trim()) : '<p>' + esc(text) + '</p>';

  const tagPill = p => p.tag
    ? `<span class="text-xs font-medium text-[#1B3A6B] bg-[#EBF5FB] px-2.5 py-1 rounded-full">${esc(p.tag)}</span>` : '';
  const draftPill = p => p.draft
    ? `<span class="text-xs font-semibold text-amber-800 bg-amber-100 px-2.5 py-1 rounded-full">Draft</span>` : '';

  // Post card, same look as the rest of the site's cards
  const card = (p, i, opts = {}) => `
    <a href="${esc(opts.href || postUrl(p))}" class="reveal up reveal-d${Math.min((i || 0) + 1, 4)} group bg-white rounded-2xl border border-slate-100 shadow-sm hover:border-[#29ABE2] hover:shadow-md transition-all flex flex-col overflow-hidden">
      ${p.cover ? `<div class="aspect-[16/9] bg-[#F0F7FF] overflow-hidden"><img src="${esc(opts.coverSrc || p.cover)}" alt="" class="no-scale w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" /></div>` : ''}
      <div class="p-6 flex flex-col flex-1">
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <span class="text-xs font-semibold text-white bg-[#29ABE2] px-2.5 py-1 rounded-full">${fmtDate(p.date)}</span>
          ${tagPill(p)}${draftPill(p)}
        </div>
        <h3 class="text-[#1B3A6B] font-bold text-base mb-2 leading-snug">${esc(p.title)}</h3>
        <p class="text-slate-500 text-sm leading-relaxed flex-1">${esc(p.summary)}</p>
        <span class="mt-4 text-sm font-semibold text-[#29ABE2] group-hover:text-[#1B3A6B] transition-colors">Read more &rarr;</span>
      </div>
    </a>`;

  // Full post body, used by post.html and the admin preview.
  // opts.resolve(src) lets the admin show images that aren't uploaded yet.
  const article = (p, opts = {}) => {
    const src = s => opts.resolve ? opts.resolve(s) : s;
    let body = md(p.body);
    if (opts.resolve) body = body.replace(/(<img[^>]*src=")([^"]+)"/g, (m, a, s) => a + esc(src(s)) + '"');
    return `
      <header class="max-w-3xl mx-auto text-center">
        <div class="flex flex-wrap items-center justify-center gap-2 mb-6">
          <span class="text-xs font-semibold text-white bg-[#29ABE2] px-2.5 py-1 rounded-full">${fmtDate(p.date)}</span>
          ${tagPill(p)}${draftPill(p)}
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold text-[#1B3A6B] leading-tight mb-5">${esc(p.title || 'Untitled post')}</h1>
        ${p.summary ? `<p class="text-lg text-slate-600 leading-relaxed">${esc(p.summary)}</p>` : ''}
        ${p.author ? `<p class="mt-5 text-sm text-slate-400">By <span class="font-semibold text-[#1B3A6B]">${esc(p.author)}</span></p>` : ''}
      </header>
      ${p.cover ? `<div class="max-w-4xl mx-auto mt-10 rounded-2xl overflow-hidden bg-[#F0F7FF] border border-slate-100 shadow-sm"><img src="${esc(src(p.cover))}" alt="" class="w-full max-h-[480px] object-contain mx-auto" /></div>` : ''}
      <div class="post-body prose prose-slate prose-lg max-w-3xl mx-auto mt-12
                  prose-headings:text-[#1B3A6B] prose-headings:font-bold
                  prose-a:text-[#29ABE2] prose-a:font-semibold hover:prose-a:text-[#1B3A6B]
                  prose-strong:text-[#1B3A6B]
                  prose-blockquote:border-l-[#29ABE2] prose-blockquote:bg-[#F0F7FF] prose-blockquote:rounded-r-xl prose-blockquote:py-1 prose-blockquote:not-italic prose-blockquote:text-[#1B3A6B]
                  prose-img:rounded-2xl prose-img:shadow-sm prose-li:marker:text-[#29ABE2]">
        ${body}
      </div>
      ${p.link ? `<div class="max-w-3xl mx-auto mt-10"><a href="${esc(p.link)}" target="_blank" rel="noopener noreferrer" class="inline-block bg-[#1B3A6B] text-white font-semibold px-6 py-3 rounded-lg hover:bg-[#29ABE2] transition-colors text-sm">Read the original &rarr;</a></div>` : ''}`;
  };

  const all = sortPosts(window.CELLTRACK_POSTS || []);

  window.CT = {
    preview, esc, fmtDate, sortPosts, postUrl, md, card, article, tagPill, draftPill,
    // Drafts only show up when the address has ?preview
    posts: all.filter(p => p.slug && (!p.draft || preview)),
  };

})();

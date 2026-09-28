// Builds posts.js from the post files the admin panel (Decap CMS) saves in content/posts/.
// Netlify runs this on every deploy (see netlify.toml). No dependencies: plain Node.
//   node scripts/build-posts.js
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const dir = path.join(root, 'content', 'posts');
const FIELDS = ['slug', 'date', 'tag', 'title', 'summary', 'cover', 'author', 'link', 'draft', 'body'];

const files = fs.existsSync(dir) ? fs.readdirSync(dir).filter(f => f.endsWith('.json')).sort() : [];

const posts = files.map(file => {
  let data;
  try {
    data = JSON.parse(fs.readFileSync(path.join(dir, file), 'utf8'));
  } catch (err) {
    throw new Error(`content/posts/${file} is not valid JSON: ${err.message}`);
  }
  // The file name is the post's address: post.html?p=<slug>
  data.slug = path.basename(file, '.json');
  if (data.date) data.date = String(data.date).slice(0, 10);   // keep YYYY-MM-DD
  const post = {};
  for (const k of FIELDS) {
    const v = data[k];
    if (k === 'draft') { if (v === true) post.draft = true; continue; }
    if (v !== undefined && v !== null && String(v).trim() !== '') post[k] = typeof v === 'string' ? v.trim() : v;
  }
  if (!post.body) post.body = '';
  return post;
}).sort((a, b) => String(b.date || '').localeCompare(String(a.date || '')));

const header =
  '// =====================================================================\n' +
  '//  CELLTRACK BLOG: generated from content/posts/ by scripts/build-posts.js.\n' +
  '//  Don\'t edit by hand: write posts in the admin panel (/admin/).\n' +
  '// =====================================================================\n';

fs.writeFileSync(path.join(root, 'posts.js'), header + 'window.CELLTRACK_POSTS = ' + JSON.stringify(posts, null, 2) + ';\n');
console.log(`posts.js: ${posts.length} post(s), ${posts.filter(p => !p.draft).length} published`);

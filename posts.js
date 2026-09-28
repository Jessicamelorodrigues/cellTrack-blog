// =====================================================================
//  CELLTRACK BLOG: every post lives in this file
// =====================================================================
//
//  EASIEST WAY: open admin.html (the blog admin panel), write the post
//  there and click "Publicar". The panel saves this file and the images
//  (blog-images/ folder) straight to GitHub. Without a GitHub connection
//  it offers "Baixar posts.js" to upload by hand instead.
//
//  EDITING BY HAND: copy one { ... }, block, paste it inside the [ ],
//  and change the values. Keep the quotes "..." and the comma after }.
//
//  Fields:
//    slug     the address of the post: post.html?p=<slug>
//             lowercase letters, numbers and hyphens only, must be unique
//    date     "2026-09-28" (year-month-day). Newest posts appear first.
//    tag      one short label, e.g. "Funding", "Publication", "Event"
//    title    the headline
//    summary  1–2 sentences, shown on the cards and in search results
//    cover    optional image, e.g. "blog-images/photo.jpg"
//    author   optional, e.g. "Victor Santoro Fernandes"
//    link     optional external URL (press article, paper, etc.)
//    draft    true = hidden from the site. Preview it by adding ?preview
//             to the address (blog.html?preview). Remove the line to publish.
//    body     the text, in Markdown, between the two ` backticks.
//             Don't type the ` character inside the text.
//
//  While there are no published posts, the blog link and the
//  News section on the homepage stay hidden.
// =====================================================================

window.CELLTRACK_POSTS = [

  {
    slug: "celltrack-technology-published-journal-of-medicinal-chemistry",
    date: "2026-09-28",
    tag: "Publication",
    title: "CellTrack's core technology is published in the Journal of Medicinal Chemistry",
    summary: "The peer-reviewed paper describes how glycan-based oxidation and chelator conjugation enable stable ⁸⁹Zr labeling of living cells for long-term PET imaging.",
    cover: "blog-images/paper-figure.gif",
    author: "CellTrack Team",
    link: "https://pubs.acs.org/doi/full/10.1021/acs.jmedchem.6c00538",
    draft: true,
    body: `
This is an **example draft**. Edit or delete it before publishing.

The science behind CellTrack is now peer-reviewed and published in the *Journal of Medicinal Chemistry* (Clemons et al.).

## What the paper shows

- Cell surface glycans can be **mildly oxidized** without affecting cell viability or function.
- A chelator is **covalently conjugated** to those sites, giving the radiotracer a stable anchor.
- Cells labeled with **Zirconium-89** can be followed with PET imaging for **7+ days**.

> Knowing where therapeutic cells go after injection is one of the biggest open questions in cell therapy development.

[Read the full paper](https://pubs.acs.org/doi/full/10.1021/acs.jmedchem.6c00538)
`,
  },

];

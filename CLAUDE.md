# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A marketing website for **CellTrack**, a UW-Madison spinout commercializing glycan-based ⁸⁹Zr radiolabeling for PET imaging of cell-therapy biodistribution. The site is static HTML ([index.html](index.html) + a blog, see below) with a Decap CMS admin at [admin/](admin/). Image assets sit in the repo root. No package manager, framework, or test suite; the only build step is [scripts/build-posts.js](scripts/build-posts.js) (plain Node, no dependencies).

## Developing

- **Preview:** open [index.html](index.html) directly in a browser, or serve the folder and visit it. Changes are live on refresh.
- **Deploy:** the client hosts on **Netlify, deploying from GitHub** — pushing to the repo is the release. [netlify.toml](netlify.toml) runs `node scripts/build-posts.js` and publishes the repo root.
- **Posts are commits:** the admin saves each post as a commit to `content/posts/`, so pull before editing the repo by hand.

## Architecture notes

- **Tailwind is loaded via CDN** (`https://cdn.tailwindcss.com` in `<head>`) using arbitrary-value utilities directly in markup — there is no `tailwind.config.js` or PostCSS pipeline. Style by adding Tailwind classes inline; use `bg-[#1B3A6B]`-style brackets for custom colors.
- **Brand palette** (hardcoded throughout as hex): deep blue `#1B3A6B` (primary text/buttons), cyan `#29ABE2` (accents/eyebrow labels), light blue section backgrounds `#F0F7FF` / `#EBF5FB`. Reuse these exact values rather than introducing new colors.
- **Page structure** is a vertical sequence of `<section>` blocks (hero → problem → technology → publication → why → team → contact → footer), navigated by in-page anchors (`#technology`, `#why`, `#team`, `#contact`).
- **Two animation systems** live in the `<style>` block and the bottom `<script>`:
  1. *Scroll reveal* — elements with class `reveal` (plus optional `up`/`left`/`right` direction and `reveal-d1..d4` delay) fade in once via an `IntersectionObserver`.
  2. *Continuous scroll-driven scaling* — `updateScales()` runs on every scroll and resizes the hero orb, background icon, big stat numbers, step badges, and images based on viewport proximity. It selects targets by their **Tailwind class strings** (e.g. `.text-4xl.font-extrabold.text-\\[\\#29ABE2\\]`), so renaming or restyling those elements can silently break the animation — update the selectors in the script to match.
- **Team photo cropping:** `team.jpg` is a two-person photo. `.reinier-crop` / `.victor-crop` in the `<style>` block use `background-position` + `background-size` to isolate each face; individual portraits (`vic.jpg`, `rei.jpg`) exist separately for the team cards.

## Blog

- **Data:** each post is one JSON file in `content/posts/<slug>.json` (fields `title, summary, date, tag, author, cover, link, draft, body`; the file name is the slug). [scripts/build-posts.js](scripts/build-posts.js) merges them into [posts.js](posts.js) (`window.CELLTRACK_POSTS`) on every Netlify build — posts.js is generated, don't edit it; the committed copy is only for local preview. Post images live in `blog-images/`. `draft: true` hides a post unless the URL has `?preview`. posts.js is public, drafts included, so nothing private goes in it.
- **Pages:** [blog.html](blog.html) (list + tag filter), [post.html](post.html)`?p=<slug>` (single post, Markdown rendered with `marked` from cdnjs + Tailwind typography plugin). The homepage `#news` section shows the 3 newest posts and stays hidden while nothing is published; the Blog menu link is always shown.
- **Shared code:** [blog.js](blog.js) (`window.CT`) holds the card and article renderers used by every page — change markup there, not per page. Card cover images carry `no-scale` so `updateScales()` on the homepage leaves them alone.
- **Admin ([admin/](admin/)):** [Decap CMS](https://decapcms.org) 3.x loaded from unpkg, configured in [admin/config.yml](admin/config.yml) (Portuguese UI, one `posts` folder collection, JSON format, media in `blog-images/`). The preview template in [admin/index.html](admin/index.html) + [admin/preview.css](admin/preview.css) mirrors post.html without Tailwind — keep it in sync if the post layout changes.
  - **Login** is DecapBridge (free): e-mail + password, users invited by e-mail from the DecapBridge dashboard; it commits through a GitHub token stored on DecapBridge's side. The `backend:` block in config.yml holds the DecapBridge site ID (placeholder until set up). Netlify's own Git Gateway is deprecated — don't switch back to it.
- nav/footer markup is duplicated in index/blog/post — update all three together.

## Content conventions

- Copy is investor/pharma-facing. Key claims (100× sensitivity, 7+ day tracking, 8 years / $300M / 109+ stats) recur across sections — keep them consistent if edited.
- Contact email `contact@celltrackbio.com` appears in both the CTA `mailto:` and the caption below it; update both together.

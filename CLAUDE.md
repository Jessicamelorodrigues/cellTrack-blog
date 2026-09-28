# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A static marketing website for **CellTrack**, a UW-Madison spinout commercializing glycan-based ⁸⁹Zr radiolabeling for PET imaging of cell-therapy biodistribution. The homepage is [index.html](index.html); a blog lives alongside it (see below). Image assets sit in the repo root. There is no build step, package manager, framework, or test suite.

## Developing

- **Preview:** open [index.html](index.html) directly in a browser, or serve the folder (`python -m http.server`) and visit it. Changes are live on refresh — nothing to compile.
- **Deploy:** committing to `main` is the release; there is no CI or bundler.

## Architecture notes

- **Tailwind is loaded via CDN** (`https://cdn.tailwindcss.com` in `<head>`) using arbitrary-value utilities directly in markup — there is no `tailwind.config.js` or PostCSS pipeline. Style by adding Tailwind classes inline; use `bg-[#1B3A6B]`-style brackets for custom colors.
- **Brand palette** (hardcoded throughout as hex): deep blue `#1B3A6B` (primary text/buttons), cyan `#29ABE2` (accents/eyebrow labels), light blue section backgrounds `#F0F7FF` / `#EBF5FB`. Reuse these exact values rather than introducing new colors.
- **Page structure** is a vertical sequence of `<section>` blocks (hero → problem → technology → publication → why → team → contact → footer), navigated by in-page anchors (`#technology`, `#why`, `#team`, `#contact`).
- **Two animation systems** live in the `<style>` block and the bottom `<script>`:
  1. *Scroll reveal* — elements with class `reveal` (plus optional `up`/`left`/`right` direction and `reveal-d1..d4` delay) fade in once via an `IntersectionObserver`.
  2. *Continuous scroll-driven scaling* — `updateScales()` runs on every scroll and resizes the hero orb, background icon, big stat numbers, step badges, and images based on viewport proximity. It selects targets by their **Tailwind class strings** (e.g. `.text-4xl.font-extrabold.text-\\[\\#29ABE2\\]`), so renaming or restyling those elements can silently break the animation — update the selectors in the script to match.
- **Team photo cropping:** `team.jpg` is a two-person photo. `.reinier-crop` / `.victor-crop` in the `<style>` block use `background-position` + `background-size` to isolate each face; individual portraits (`vic.jpg`, `rei.jpg`) exist separately for the team cards.

## Blog

- **Data:** every post is an object in [posts.js](posts.js) (`window.CELLTRACK_POSTS`), body in Markdown inside a template literal. Post images live in `blog-images/`. `draft: true` hides a post unless the URL has `?preview`.
- **Pages:** [blog.html](blog.html) (list + tag filter), [post.html](post.html)`?p=<slug>` (single post, Markdown rendered with `marked` from cdnjs + Tailwind typography plugin). The homepage `#news` section shows the 3 newest posts and stays hidden while nothing is published; the Blog menu link is always shown.
- **Shared code:** [blog.js](blog.js) (`window.CT`) holds the card and article renderers used by every page and by the admin preview — change markup there, not per page. Card cover images carry `no-scale` so `updateScales()` on the homepage leaves them alone.
- **Admin:** [admin.html](admin.html) (Portuguese UI, `noindex`) edits `posts.js`. The panel stays hidden behind a login screen with two real access checks, and there are no passwords or secrets in the code (it's public once the repo is): (1) **GitHub** — a fine-grained token that GitHub itself must confirm has push access to the repo (kept in sessionStorage, or localStorage with "Manter conectado"); commits go through the Contents API; (2) **local folder** — the File System Access API (Chrome/Edge), where the browser asks for write permission; the handle is remembered in IndexedDB until "Sair". Saves re-read `posts.js` first and apply only the one change. It regenerates the whole file, so hand edits survive only as data (the header comment above `window.CELLTRACK_POSTS` is preserved).
- nav/footer markup is duplicated in index/blog/post — update all three together.

## Content conventions

- Copy is investor/pharma-facing. Key claims (100× sensitivity, 7+ day tracking, 8 years / $300M / 109+ stats) recur across sections — keep them consistent if edited.
- Contact email `contact@celltrackbio.com` appears in both the CTA `mailto:` and the caption below it; update both together.

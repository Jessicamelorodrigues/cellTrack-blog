# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A marketing website for **CellTrack**, a UW-Madison spinout commercializing glycan-based ⁸⁹Zr radiolabeling for PET imaging of cell-therapy biodistribution. The public site is static HTML ([index.html](index.html) + a blog, see below); only the blog admin panel in [admin/](admin/) is PHP. Image assets sit in the repo root. There is no build step, package manager, framework, or test suite.

## Developing

- **Preview:** open [index.html](index.html) directly in a browser, or serve the folder and visit it. Changes are live on refresh — nothing to compile. The admin needs a PHP server (`php -S localhost:8000` from the repo root, then `/admin/`).
- **Deploy:** the site is hosted on **Hostinger (PHP)**; upload the files there. GitHub only stores the code — GitHub Pages can't run the admin.
- **Never overwrite live content on deploy:** once live, the server's `posts.js`, `blog-images/` and `data/` are written by the admin and are newer than the repo. When uploading code changes, skip those.

## Architecture notes

- **Tailwind is loaded via CDN** (`https://cdn.tailwindcss.com` in `<head>`) using arbitrary-value utilities directly in markup — there is no `tailwind.config.js` or PostCSS pipeline. Style by adding Tailwind classes inline; use `bg-[#1B3A6B]`-style brackets for custom colors.
- **Brand palette** (hardcoded throughout as hex): deep blue `#1B3A6B` (primary text/buttons), cyan `#29ABE2` (accents/eyebrow labels), light blue section backgrounds `#F0F7FF` / `#EBF5FB`. Reuse these exact values rather than introducing new colors.
- **Page structure** is a vertical sequence of `<section>` blocks (hero → problem → technology → publication → why → team → contact → footer), navigated by in-page anchors (`#technology`, `#why`, `#team`, `#contact`).
- **Two animation systems** live in the `<style>` block and the bottom `<script>`:
  1. *Scroll reveal* — elements with class `reveal` (plus optional `up`/`left`/`right` direction and `reveal-d1..d4` delay) fade in once via an `IntersectionObserver`.
  2. *Continuous scroll-driven scaling* — `updateScales()` runs on every scroll and resizes the hero orb, background icon, big stat numbers, step badges, and images based on viewport proximity. It selects targets by their **Tailwind class strings** (e.g. `.text-4xl.font-extrabold.text-\\[\\#29ABE2\\]`), so renaming or restyling those elements can silently break the animation — update the selectors in the script to match.
- **Team photo cropping:** `team.jpg` is a two-person photo. `.reinier-crop` / `.victor-crop` in the `<style>` block use `background-position` + `background-size` to isolate each face; individual portraits (`vic.jpg`, `rei.jpg`) exist separately for the team cards.

## Blog

- **Data:** every post is an object in [posts.js](posts.js): `window.CELLTRACK_POSTS = <JSON array>;`. Everything after the `=` must stay **valid JSON** because the PHP admin parses and rewrites it. Fields: `slug, date, tag, title, summary, cover, author, link, draft, body` (Markdown). Post images live in `blog-images/`. `draft: true` hides a post unless the URL has `?preview`. posts.js is public, drafts included, so nothing private goes in it.
- **Pages:** [blog.html](blog.html) (list + tag filter), [post.html](post.html)`?p=<slug>` (single post, Markdown rendered with `marked` from cdnjs + Tailwind typography plugin). The homepage `#news` section shows the 3 newest posts and stays hidden while nothing is published; the Blog menu link is always shown.
- **Shared code:** [blog.js](blog.js) (`window.CT`) holds the card and article renderers used by every page and by the admin preview — change markup there, not per page. Card cover images carry `no-scale` so `updateScales()` on the homepage leaves them alone.
- **Admin ([admin/](admin/), PHP, Portuguese UI):** modeled on the Fofoca Real admin. E-mail + password login with per-IP lockout (5 tries / 15 min), CSRF on every form, session cookie httponly/SameSite=Lax.
  - Users live in `data/users.json` (`owner` = can manage users, `admin` = posts only). First visit with no users → [instalar.php](admin/instalar.php) creates the owner; it disables itself once a user exists.
  - [usuarios.php](admin/usuarios.php) (owner only): invite by e-mail (48h link to [verificar.php](admin/verificar.php), where the person sets name + password), resend, "Nova senha" (same link mechanism for forgotten passwords), promote/demote, remove. Invites use plain `mail()`; the link is always shown on screen too, since shared-hosting mail isn't guaranteed.
  - [post.php](admin/post.php) edits one post; images go through `save_uploaded_image()` (MIME-checked, renamed, resized to 1600px with GD when available). [upload.php](admin/upload.php) handles images inserted in the text.
  - All logic is in [admin/includes/functions.php](admin/includes/functions.php); markup helpers in `layout.php`; every protected page starts with `require includes/auth.php` (sets `$me`).
  - `.htaccess` files deny web access to `data/` and `admin/includes/`, and block script execution in `blog-images/`. `data/*.json` is git-ignored.
- nav/footer markup is duplicated in index/blog/post — update all three together.

## Content conventions

- Copy is investor/pharma-facing. Key claims (100× sensitivity, 7+ day tracking, 8 years / $300M / 109+ stats) recur across sections — keep them consistent if edited.
- Contact email `contact@celltrackbio.com` appears in both the CTA `mailto:` and the caption below it; update both together.

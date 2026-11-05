---
name: tailwind-css
description: General best practices for writing and structuring Tailwind CSS v4, including the CSS-first config approach (@theme, no tailwind.config.js by default), utility-first markup, responsive/state variants, dark mode, avoiding arbitrary values, and when/how to extract reusable components vs using @apply. Use this skill whenever writing or reviewing any HTML, Vue, JSX/TSX, or other markup that contains Tailwind utility classes, whenever setting up or configuring Tailwind CSS v4 in a project, or whenever the user asks for help with styling, CSS architecture, responsive design, dark mode, or component extraction in a Tailwind project. Trigger even if the user doesn't say "Tailwind" explicitly but is clearly writing utility classes (e.g. `flex items-center gap-2 rounded-lg`) or asking about CSS organization in a project that uses Tailwind.
---

# Tailwind CSS v4 Best Practices

Guidance for writing idiomatic, maintainable Tailwind CSS v4. Tailwind v4 changed
significantly from v3 — it's CSS-first, not JS-config-first. Don't reach for v3
patterns (`tailwind.config.js`, `@tailwind base/components/utilities`) unless the
project specifically still uses v3.

## 1. Setup — CSS-first configuration (v4 default)

Tailwind v4 is configured in CSS, not JavaScript. There is no `tailwind.config.js`
by default.

**Entry CSS file** (e.g. `app.css` / `main.css`):
```css
@import "tailwindcss";
```
That's it for a minimal setup. No `@tailwind base;`, `@tailwind components;`,
`@tailwind utilities;` — those are v3 syntax and will not work correctly in v4.

**Customizing the theme** — use the `@theme` directive directly in CSS instead of
`theme.extend` in a JS config:
```css
@import "tailwindcss";

@theme {
  --font-display: "Satoshi", sans-serif;
  --color-brand-50: oklch(0.98 0.02 250);
  --color-brand-500: oklch(0.6 0.18 250);
  --color-brand-900: oklch(0.3 0.12 250);
  --breakpoint-3xl: 1920px;
  --spacing-18: 4.5rem;
}
```
Every `--color-*`, `--font-*`, `--spacing-*`, `--breakpoint-*`, etc. variable
defined in `@theme` automatically generates corresponding utilities
(`bg-brand-500`, `text-brand-900`, `3xl:flex`, `p-18`, ...).

**Only reach for `tailwind.config.js`** if the project needs JS-level config (e.g.
plugins that require JS, or a legacy v3 codebase). If one exists, use the
`@config` directive to point to it. Don't create one by default in a fresh v4 setup.

**Vite plugin** (preferred over PostCSS in v4 for new projects):
```js
// vite.config.js
import tailwindcss from '@tailwindcss/vite'

export default {
  plugins: [tailwindcss()],
}
```

## 2. Utility-first, not premature abstraction

Write utilities directly in markup. Don't reflexively extract a CSS class for
every repeated pattern — duplication of a few utility classes is cheaper than an
indirection layer, especially early on.

```html
<!-- Prefer this -->
<button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
  Save
</button>
```

**When to extract instead:**
- In component frameworks (Vue/React), the natural extraction unit is a
  **component**, not a CSS class. If a button pattern repeats 3+ times, make a
  `<Button>` / `Button.vue` component — don't write a `.btn` CSS class.
- Only use `@apply` for things that genuinely aren't component-shaped (e.g. a
  `prose` block for markdown content, or third-party markup you don't control).
  Don't use `@apply` as a default way to "keep markup clean" — it reintroduces
  the indirection Tailwind is designed to avoid and creates a parallel
  stylesheet to maintain.

## 3. Avoid arbitrary values — use theme tokens

Arbitrary values (`w-[37px]`, `text-[#3b82f6]`, `mt-[13px]`) bypass the design
system and produce inconsistent UI. Default to theme tokens; only use arbitrary
values for genuinely one-off, non-reusable cases (e.g. matching a fixed external
asset's exact pixel size).

```html
<!-- Avoid -->
<div class="mt-[13px] text-[#3b82f6] w-[280px]">

<!-- Prefer -->
<div class="mt-3 text-blue-500 w-72">
```

If a value is needed repeatedly but isn't in the default scale, add it to
`@theme` once rather than repeating the arbitrary value everywhere:
```css
@theme {
  --spacing-sidebar: 17.5rem;
}
```
```html
<div class="w-sidebar">
```

## 4. Responsive design — mobile-first

Unprefixed utilities apply at all sizes; breakpoint prefixes apply at that size
**and up**. Always design mobile-first, then layer breakpoints upward.

```html
<div class="flex flex-col gap-4 md:flex-row md:gap-8 lg:gap-12">
```

Default breakpoints: `sm` (40rem), `md` (48rem), `lg` (64rem), `xl` (80rem),
`2xl` (96rem). Don't hardcode breakpoint values elsewhere (JS, media queries) —
reference the same `--breakpoint-*` theme vars if needed in custom CSS.

## 5. State variants — compose, don't duplicate

Stack variants directly rather than writing separate rules:
```html
<input
  class="border border-gray-300 px-3 py-2
         focus:border-blue-500 focus:ring-2 focus:ring-blue-200
         disabled:cursor-not-allowed disabled:opacity-50
         dark:border-gray-600 dark:bg-gray-800"
/>
```

Common variants: `hover:`, `focus:`, `focus-visible:`, `active:`, `disabled:`,
`group-hover:` (on a parent with `group`), `peer-checked:` (on a sibling with
`peer`), `first:`, `last:`, `odd:`, `even:`, `has-[...]:`.

For a parent/child hover relationship:
```html
<div class="group">
  <span class="text-gray-500 group-hover:text-gray-900">Label</span>
</div>
```

## 6. Dark mode

v4 dark mode works via the `dark:` variant, driven by `prefers-color-scheme` by
default. For class-based toggling (e.g. a manual theme switcher), opt in
explicitly in CSS:
```css
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));
```
Then toggling a `dark` class on `<html>` (e.g. via Pinia store / localStorage)
controls `dark:` utilities. Don't mix both strategies in the same project —
pick system-driven or class-driven and be consistent.

## 7. Class ordering and readability

Keep a consistent, predictable order so diffs stay clean and classes are easy to
scan. A common convention (not enforced by Tailwind itself, but widely used):
layout → box model → typography → visual → state/misc.
```html
<div class="flex items-center justify-between w-full p-4 text-sm font-medium text-gray-900 bg-white rounded-lg shadow-sm hover:shadow-md">
```
If the project uses Prettier, the official `prettier-plugin-tailwindcss` plugin
auto-sorts classes — prefer installing it over manually maintaining order.

## 8. Conditional / dynamic classes (Vue 3 specific)

Avoid string-concatenating partial class names — Tailwind's compiler scans for
**complete, literal class strings**, so a dynamically built class like
`` `bg-${color}-500` `` will not be generated and will silently fail to style.

```vue
<!-- Wrong: Tailwind can't detect this string at build time -->
<div :class="`bg-${color}-500`">

<!-- Right: map to complete, literal class names -->
<script setup>
const colorClasses = {
  blue: 'bg-blue-500',
  red: 'bg-red-500',
  green: 'bg-green-500',
}
</script>
<div :class="colorClasses[color]">
```

For conditional classes, use Vue's object/array binding syntax rather than
manual string building:
```vue
<div :class="[
  'rounded-lg px-4 py-2 font-medium',
  isActive ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700',
  disabled && 'opacity-50 cursor-not-allowed',
]">
```

For merging conflicting classes passed via props (e.g. a reusable component that
accepts a `class` prop that should override defaults), use `tailwind-merge`:
```bash
npm install tailwind-merge
```
```ts
import { twMerge } from 'tailwind-merge'
twMerge('px-4 py-2 bg-blue-500', props.class) // later classes win conflicts cleanly
```

## 9. shadcn-vue interplay

If the project uses shadcn-vue (as GymOS does), its components already encode
Tailwind + `cva` (class-variance-authority) conventions. Follow the same pattern
for new custom components rather than introducing `@apply`-based styling:
- Use `cva` for variant-based components (size, intent/color, state).
- Use `cn()` (typically a `twMerge` + `clsx` wrapper, usually in `lib/utils.ts`)
  to merge passed-in classes with component defaults.
- Keep design tokens (colors, radii, spacing) defined once in `@theme` so both
  shadcn-vue primitives and custom components draw from the same source.

## 10. Common v3 → v4 pitfalls to flag in review

- `@tailwind base/components/utilities` left in CSS → should be `@import "tailwindcss"`.
- `tailwind.config.js` with `theme.extend` for things that could be `@theme` in CSS.
- `bg-opacity-50`, `text-opacity-75` etc. (v3 opacity utilities) → use the
  slash syntax instead: `bg-black/50`, `text-gray-900/75`.
- Manually-specified `purge`/`content` glob arrays — v4 auto-detects content,
  usually no config needed for standard project layouts.
- Excessive arbitrary values where a theme token would do (see §3).

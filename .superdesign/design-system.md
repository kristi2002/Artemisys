# Artemisys — Design System

Gestionale scolastico (school management). Server-rendered PHP + **Bootstrap 5.3.2** +
**FontAwesome 6.5.1**. Italian UI. Admin dashboard with fixed left navy sidebar (265px)
and a light content area.

## Colors
- **Sidebar navy**: `#0c1a3a` (bg), hover `#162d5e`, active `#1e3f80`, accent border `#60a5fa`
- **Primary blue**: `#1e40af` (buttons, icons, links, active accents)
- **Primary gradient**: `linear-gradient(135deg, #1e40af, #3b82f6)` (and `#1e40af → #60a5fa`)
- **Heading navy**: `#0c1a3a`
- **Content background**: `#f1f5f9`
- **Card surface**: `#ffffff`
- **Subtle blue chip**: bg `#e8eef8`, text `#1e40af` (used for badges/counters)
- **Table head bg**: `#f8fafc`
- **Muted text**: `#64748b`; body text `#374151`
- **Success**: bootstrap `bg-success-subtle text-success`; **Danger**: `#dc3545`

## Typography
- Font family: `'Segoe UI', Tahoma, Geneva, Verdana, sans-serif`
- Page title: `h4` bold, color `#0c1a3a`, leading icon in `#1e40af`
- Card title: `h6` fw-semibold, color `#0c1a3a`
- Labels: `.form-label small fw-semibold`
- Small meta: `.small text-muted`

## Components
- **Card**: `.card.border-0.shadow-sm`, white. Header: `.card-header.bg-white.border-bottom.py-3.px-4`
  with an `h6` title (leading icon) on the left and a blue count badge (or action button) on the right.
- **Buttons**: primary = `.btn.btn-primary` (blue, gradient on login); secondary/outline =
  `.btn-outline-secondary` / `.btn-outline-primary` / `.btn-outline-danger`; small = `.btn-sm`.
  Buttons carry a leading FontAwesome icon (`<i class="fas fa-... me-1/2">`).
- **Badge / chip**: `<span class="badge" style="background:#e8eef8;color:#1e40af;font-weight:600;">`
- **Table**: `.table.table-hover.mb-0`; `thead` bg `#f8fafc`; row click navigates (`cursor:pointer`);
  first cell `ps-4`, last actions cell `text-end pe-4`.
- **Empty state**: centered `.empty-state` with circular icon, `h5` title, muted `p`.
- **Modals**: `.modal-dialog.modal-dialog-centered`, `.modal-content.border-0.shadow`;
  header/footer bg `#f8fafc`; confirm-delete uses danger button.
- **Alerts**: `.alert.alert-success` / `.alert-danger`, dismissible, leading icon.

## Layout
- Fixed navy sidebar 265px (`position:fixed`), content offset by `margin-left`.
- Page pattern: title row (`h4` + count badge) → optional alert → content cards in a
  Bootstrap `.row.g-4` grid. Use `col-lg-*` for side-by-side cards; full-width cards use `col-12`.
- Spacing: cards `mb-4`, grid gutters `g-3`/`g-4`, card body `p-4` (or `p-0` for tables).
- Border radius: Bootstrap default (~.5rem) on cards; chips pill/rounded.

## Conventions
- Filters/toolbars belong in a dedicated card at the **top** of the page (not floating).
- Creation of primary entities lives on a **dedicated page** or in the card header (top-right
  action button), not as a large inline form competing with the list.
- Everything is Italian, professional, clean, information-dense but airy.

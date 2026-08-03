# Phase 7 — Dish Variations CRUD

## Scope
Quantity, unit, price, and single (cartable) / bulk (WhatsApp) types for dish portions.

## Done (nested on Dishes)
Variations ship inside **Phase 6** dish create/edit/show — no standalone Variations page.

- Fields: label, quantity, unit (`serving`|`kg`|`persons`), price, type (`single`|`bulk`), status, sort order
- Cartability derived from `type=single` (no legacy boolean flags)
- Channel column on form: **Cart** vs **WhatsApp**
- Sync on create/update; orphans force-deleted; covered by `DishesCrudTest`

## Exit
- Edit a dish → add/remove single + bulk rows → save
- Next was Phase 8 (completed together)

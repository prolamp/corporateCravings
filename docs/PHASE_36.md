# Phase 36 — FAQ page

## Scope
Public FAQ with accordion UI. Content comes from the published CMS page `slug=faq` (`h3` + paragraph pairs), with built-in defaults if the CMS body has no parseable Q&A.

## Done
- Route: `GET /faq` → `FaqController` → `public/faq`
- `FaqService`: parse CMS body or fall back to `defaultItems()`
- Accordion UI + Contact / WhatsApp CTA
- Demo seed FAQ body updated to structured Q&A HTML
- Footer already linked to `/faq`

## Tests
`php artisan test --filter=PublicFaq`

## Exit
- `/faq` renders accordion answers
- Next: **Phase 38 — Google Analytics + Consent**

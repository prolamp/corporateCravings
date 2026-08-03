# Phase 35 — Contact page

## Scope
Public **Contact** page with brand details from site settings and a simple inquiry form (Phase 1.5).

## Done
- Routes: `GET /contact`, `POST /contact` (throttled `contact`)
- `ContactController` + `ContactService` + `StoreContactRequest`
- Form emails `ContactInquiryMail` to `brand.email` (default mailer: log)
- UI: brand hero, reach-us details (address/phone/email/hours/WhatsApp), message form
- Nav/footer already linked to `/contact`

## Tests
`php artisan test --filter=PublicContact`

## Exit
- `/contact` renders and form submits with success flash
- Next: **Phase 37 — Legal pages + 404**

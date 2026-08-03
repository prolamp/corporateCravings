# Phase 22 — Site Settings page

## Scope
Admin page to edit site-wide brand/contact and analytics settings stored in `settings` (not user profile `/settings`). Keys: brand name, phone, WhatsApp, email, address, hours, GA4 measurement ID.

## Done
- Service: `SiteSettingsService` (definitions, defaults, form payload, update)
- Permission: `settings.manage` (admin / super_admin only)
- Routes: `GET/PUT /admin/settings`
- Page: grouped form (Brand & contact, Analytics)
- Nav **Content → Settings** live
- Demo seed includes `brand.hours`

## Tests
`php artisan test --filter=SiteSettings`

## Exit
- Admin saves brand WhatsApp / GA4 ID
- Manager and assistant: 403
- Next: **Phase 23 — Reports pages**

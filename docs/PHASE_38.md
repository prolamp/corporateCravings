# Phase 38 — Google Analytics + Consent

## Scope
Consent-aware **GA4** on the public site: load scripts only after Accept, fire key commerce/WhatsApp events without PII.

## Done
- Measurement ID from `.env` `GA_MEASUREMENT_ID` (preferred) or Admin → Site Settings `analytics.ga_measurement_id`
- Shared on Inertia as `site.analytics.ga_measurement_id` (null when empty/invalid)
- Cookie banner on public layout when an ID is configured and no choice stored
- Footer **Cookie preferences** clears local consent so the banner can show again
- Events (only when consent granted): `page_view`, `add_to_cart`, `begin_checkout`, `purchase`, `whatsapp_click`
- No names, phones, addresses, or order numbers sent to analytics

## Configure
```env
GA_MEASUREMENT_ID=G-XXXXXXXXXX
```
Or set the same value in Admin → Site Settings → Analytics.

## Tests
`php artisan test --filter=PublicAnalytics`

## Exit
- Banner appears when GA ID is set; Decline never loads gtag
- Accept loads GA4; commerce/WhatsApp events fire without PII
- Next was **Phase 39 — SEO** (now complete; public website phases done)

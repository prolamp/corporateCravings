# Phase 1.5 — Single Stitch prompt

Paste the following **one prompt** into Google Stitch. It defines the complete design system and screen set.

```text
Design a complete, cohesive, implementation-ready responsive web application for “Corporate Cravings” (corporatecravings.pk), a Pakistani corporate catering business in Islamabad. Currency is PKR. The application will be built with Laravel, Inertia React, Tailwind, and reusable shadcn-style components.

Create one unified design system and apply it consistently across the public website, admin application, customer portal, subscriber portal, and authentication screens. Produce every key screen in light and dark mode at mobile 375px and desktop 1280px widths, with tablet behavior inferred. Use minimum 44px touch targets and no unintended horizontal scrolling.

BRAND AND VISUAL DIRECTION
- Personality: warm Pakistani hospitality, reliable catering operations, and professional corporate service—not a trendy cafe or generic delivery-app clone.
- Primary brand color suggestion (from the logo): rgba(246, 198, 12, 1). Use this as the primary/brand accent. Choose all other colors yourself—backgrounds, surfaces, text, borders, success, warning, danger, WhatsApp treatment, and supporting neutrals—for cohesive light and dark modes that work well with that primary yellow.
- Choose typography yourself for clear hierarchy: one expressive display option for marketing brand/headlines and one clean UI sans for application screens.
- Use authentic food photography: biryani, karahi, bulk trays, and office lunch setups.
- Marketing heroes should be full-bleed, confident compositions with no floating food cards, stickers, stat strips, or promo badges.
- Use subtle radii, restrained shadows, clear hierarchy, and only a few purposeful interactions.
- Avoid generic purple SaaS styling, neon glow excess, newspaper/broadsheet layouts, pill-shaped everything, and card-heavy public pages.

SHARED COMPONENTS
Define reusable light/dark variants for public navigation, admin sidebar, portal navigation, footer, buttons, WhatsApp CTA, fields, selects, image uploader, dialogs, responsive tables, mobile list rows, pagination, empty/loading states, StatusBadge, FlashToast, money display, and sticky mobile action bars. Display money as “Rs. 1,250”. Show friendly status labels such as Pending, Confirmed, Leave for delivery, Delivered, Completed, and Cancelled—never raw codes such as leave_for_delivery. Errors and success messages appear through consistent toasts or inline validation.

LOCKED BUSINESS RULES
1. The public site has three unmistakable paths: Menu for cartable single servings, Corporate Packages ordered through WhatsApp, and Bulk Orders with kg tiers ordered through WhatsApp.
2. Only dish variations whose type is “single” can enter the cart and COD checkout. Bulk variations must never have Add to cart.
3. Public package pages show only the final total price per person. Never expose base price, discount, package_amount, or private agreed_total publicly.
4. Subscriber contract pricing is private and visible only to authorized staff and that subscriber.
5. Admin navigation is role-aware. Manager and assistant variants must not show revenue, earnings, profit/loss, or expense totals. Customer and subscriber portals never use the admin shell.

PUBLIC WEBSITE
Create a shared desktop navigation with Corporate Cravings wordmark, Home, About, Menu, Packages, Bulk Orders, Cart with count, Track Order, Contact, and Login. On mobile use a menu drawer while keeping Cart accessible. Create a full-width footer containing brand statement, Islamabad address, phone, email, hours, WhatsApp CTA, social links, FAQ, Privacy, Terms, Delivery Policy, and Cancellation & Refund.

Create these public screens:
1. Home: one full-bleed food hero with the Corporate Cravings brand prominent, one catering headline, one supporting sentence, and only two CTAs—Browse Menu and WhatsApp Us. Below it, clearly separate the Menu, Corporate Packages, and Bulk Orders paths; then featured dishes, a concise trust/about section, and the footer.
2. Menu: category filter chips and a dish grid with image, title, short description, and starting single-serving price. Do not show bulk tiers here.
3. Dish detail: dominant image, title, description, category; a Single Serving section with variation price, quantity, addons, and Add to cart; a separate Bulk Offerings section with 5 kg, 8 kg, and 10 kg examples and Quote on WhatsApp.
4. Packages list and detail: image, title, description, included dishes, final public “Rs. X / person” only, and WhatsApp CTA.
5. Bulk Orders: dishes with kg price tiers and WhatsApp CTA only—no cart controls.
6. Cart: single-serving lines only, variation, addons, quantity controls, line total, subtotal, empty state, and sticky mobile Checkout CTA.
7. Checkout: name, required phone, optional email, address, delivery zone with fee, optional notes, order summary, delivery fee, total, fixed Cash on Delivery method, and Place Order. Do not design card payment.
8. Order success: order number, Pending badge, confirmation, tracking link, login link, and WhatsApp support.
9. Track order: order number/phone search and status timeline.
10. About, Contact with simple form, FAQ accordion, Privacy, Terms, Delivery Policy, Cancellation & Refund, and branded 404.

ADMIN APPLICATION
Create a compact responsive sidebar that becomes a mobile drawer. Navigation groups:
- Overview: Dashboard
- Catalog: Categories, Dishes, Addons
- Packages: Packages, Subscribers, Schedules
- Orders: unified Orders
- Operations: Delivery Zones, Expenses, Cost Calculator
- Content: Pages, Settings
- People: Users
- System: Statuses & Errors
The top bar contains page title, light/dark toggle, and user menu.

Create the Dashboard with today’s orders, pending subscriber deliveries, paid revenue and expense information for authorized admins, pending actions, recent orders, and today’s subscriber schedule. Include loading and empty states. Also show a manager/assistant variant without sensitive finance widgets.

Create one reusable CRUD visual pattern: index page with title, Add button, search, filters, responsive table/mobile rows, statuses, actions and pagination; create/edit form with clear validation, responsive columns, image preview/replace/delete, Cancel and Save actions; show page with details and related records.

Apply that pattern to:
- Categories: name, auto slug, description, one image, status, sort order.
- Dishes: category, name, description, one image, status, sort order; nested variation rows with label, quantity, unit, PKR price, type single or bulk, status, and sort order. Clearly mark single as cartable and bulk as WhatsApp-only. Do not invent legacy boolean flags.
- Addons: name, price, optional image, status.
- Packages: title, description, image, status, ordered dishes, base amount/person, discount, and computed final public total/person.
- Subscribers: user/company, package, private package_amount, discount, agreed_total, delivery notes, status, and subtle Staff Only treatment around confidential pricing.
- Schedules: date/calendar and mobile list views with subscriber, package, status, filters, and quick status updates.
- Unified Orders: filters for menu COD, package WhatsApp, bulk WhatsApp, status, date, phone, and order number; detail with items, addons, delivery zone/fee, read-only amount paid, notes, status transitions, and WhatsApp link.
- Delivery Zones: name, PKR fee, status, notes.
- Expense Categories and Expenses: category, title, amount, optional unit for groceries, date, notes, status, date filters, and authorized-admin totals.
- Dish Cost Calculator: a stateless tool that selects a dish, grocery expenses, and quantities, then compares calculated cost with variation selling price. Do not design raw-material or recipe database pages.
- Users: name, phone, optional email, role, company, image, status.
- CMS Pages, business Settings, and a staff-facing Status/Error catalog with label and color previews.

CUSTOMER PORTAL
Use a simple top navigation—not the admin sidebar—with My Orders, Account, theme toggle, and Logout. Create a dashboard with welcome, Order from Menu CTA, recent orders, statuses, and reorder. Create order history/detail with items, addons, delivery info, status timeline, and reorder of single-serving items only. Create account forms for name, phone, email, address, and password. Never show admin data or subscriber pricing.

SUBSCRIBER PORTAL
Use a simple top navigation with My Schedule, My Package, Account, theme toggle, and Logout. Create a dashboard with company/name, today’s and upcoming delivery status, their package summary, their own agreed contract total, and WhatsApp support. Create schedule calendar/mobile list, leave or holiday request, My Package with included dishes and only their contract price, and account details. Never show other subscribers’ prices, all orders, expenses, or admin tools.

AUTHENTICATION
Create focused Login and Forgot Password screens using phone or email and password, theme toggle, brand presence, and WhatsApp support. Keep the form as the single job rather than a marketing collage.

DELIVERABLE
Return a coherent screen collection using one shared token and component system. Organize the result into Public, Admin, Customer Portal, Subscriber Portal, and Auth sections. Include representative desktop and mobile layouts and light/dark variants. Do not add features or pricing fields outside this specification.
```

## Approval checklist
- [ ] Light and dark design system approved
- [ ] Public Menu, Packages, and Bulk paths are visually distinct
- [ ] Cart, checkout, and tracking approved
- [ ] Admin shell, dashboard, and representative CRUD approved
- [ ] Customer and subscriber portal dashboards approved
- [ ] Mobile layouts approved

Phase 2 begins only after this screen set is approved.

## Catalog reference (seed names for realistic UI copy)

Use these real category and dish names in Stitch mockups instead of placeholder food labels.

### Categories
1. Rice
2. Chicken
3. Beef
4. Mutton
5. Vegetable
6. Daal
7. Salad
8. BBQ
9. Dessert

### Dishes by category

**Rice**
- Chicken Biryani
- Chicken Pulao
- Vegetable Pulao
- Chana Pulao
- Chicken Manchurian
- Daal Chawal
- Afgani Pulao
- Kabuli Pulao

**Chicken**
- Chicken Karahi
- Chicken Qorma
- Matter Qeema

**Vegetable**
- Mixed Vegetable
- Aloo Gobi
- Aloo Bhujia
- Aloo Anda

**Daal**
- Daal Kadu
- Daal Chana
- Daal Mash
- Daal Mixed
- Lahori Chana
- Lobia

**Salad**
- Russian Salad
- Fresh Salad
- Special Salad
- Kachumer Salad

**Dessert**
- Kheer
- Custard

**Beef / Mutton / BBQ**
- Categories exist in the catalog; no seeded dishes yet — leave empty-state or omit sample items for those filters.

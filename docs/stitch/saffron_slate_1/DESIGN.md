---
name: Saffron & Slate
colors:
  surface: '#fbf9f5'
  surface-dim: '#dbdad6'
  surface-bright: '#fbf9f5'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3ef'
  surface-container: '#efeeea'
  surface-container-high: '#eae8e4'
  surface-container-highest: '#e4e2de'
  on-surface: '#1b1c1a'
  on-surface-variant: '#4e4632'
  inverse-surface: '#30312e'
  inverse-on-surface: '#f2f0ed'
  outline: '#807660'
  outline-variant: '#d1c5ab'
  surface-tint: '#745c00'
  primary: '#745c00'
  on-primary: '#ffffff'
  primary-container: '#f6c60c'
  on-primary-container: '#695300'
  inverse-primary: '#f0c100'
  secondary: '#575e70'
  on-secondary: '#ffffff'
  secondary-container: '#d9dff5'
  on-secondary-container: '#5c6274'
  tertiary: '#006d2f'
  on-tertiary: '#ffffff'
  tertiary-container: '#45e878'
  on-tertiary-container: '#00642b'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffe089'
  primary-fixed-dim: '#f0c100'
  on-primary-fixed: '#241a00'
  on-primary-fixed-variant: '#574500'
  secondary-fixed: '#dce2f7'
  secondary-fixed-dim: '#c0c6db'
  on-secondary-fixed: '#141b2b'
  on-secondary-fixed-variant: '#404758'
  tertiary-fixed: '#66ff8e'
  tertiary-fixed-dim: '#3de273'
  on-tertiary-fixed: '#002109'
  on-tertiary-fixed-variant: '#005322'
  background: '#fbf9f5'
  on-background: '#1b1c1a'
  surface-variant: '#e4e2de'
  success-emerald: '#10B981'
  danger-ruby: '#E11D48'
  surface-cream: '#FDFBF7'
  charcoal-text: '#111827'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
  title-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-bold:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  container-max: 1280px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 40px
---

## Brand & Style

This design system is built to balance the rigorous demands of corporate logistics with the rich, inviting spirit of Pakistani hospitality. It moves away from sterile, "tech-first" aesthetics, opting instead for a **Modern-Heritage** approach. The brand personality is dependable, flavorful, and attentive. 

The visual language emphasizes clarity for busy procurement officers while using warmth to remind users of the artisanal quality of the food. It employs a **Refined Professional** style—utilizing ample whitespace, high-contrast serif typography for storytelling, and a sophisticated color palette that feels both institutional and appetizing.

## Colors

The palette is anchored by **Vibrant Saffron**, a color that evokes the richness of traditional spices and serves as the primary energetic driver for actions. This is balanced by **Deep Charcoal**, providing the "Corporate" weight necessary for contracts and invoices.

- **Primary (Saffron):** Used for primary buttons, active states, and highlights. It should be treated as a precious resource—used boldly but selectively.
- **Secondary (Charcoal):** The foundation for typography and dark-mode surfaces. It ensures the interface feels grounded and professional.
- **Tertiary (WhatsApp Green):** Reserved exclusively for direct communication triggers, leveraging the ubiquity of the platform in local business culture.
- **Neutral (Cream):** Replaces clinical white with a warm, off-white surface to soften the digital experience and evoke a "fine-dining linen" feel.

## Typography

The typographic hierarchy employs a "High-Low" strategy:
- **Serif Display (Playfair Display):** Used for headlines and section titles. The high contrast of the strokes adds an editorial, premium feel that differentiates the product from standard SaaS tools.
- **Sans-Serif UI (Plus Jakarta Sans):** A friendly but modern geometric sans used for all functional data, inputs, and long-form body text. It ensures maximum legibility during the rapid "ordering" phase of the user journey.

Use **Label-Bold** in all-caps for technical metadata (e.g., SKU numbers or Status tags) to create a distinct visual texture from conversational text.

## Layout & Spacing

The layout follows a **Hybrid Grid** system. Marketing and landing pages utilize a 12-column fluid grid with generous gutters (24px) to allow the "Saffron" brand elements to breathe. Functional dashboard views transition to a fixed-width sidebar (280px) with a fluid content area.

- **Mobile:** Single column with 16px side margins. Key actions are moved to a "Sticky Footer" or "Action Bar" to ensure they are always within thumb-reach.
- **Rhythm:** All spacing (margins, padding, gap) must be multiples of the 8px base unit to maintain a rigorous, professional cadence.

## Elevation & Depth

This design system avoids heavy drop-shadows in favor of **Tonal Layering** and **Soft Plinth** elevation.

- **Surfaces:** Most "Cards" should use a subtle 1px border (#E5E7EB) instead of a shadow.
- **Active Elevation:** Only use shadows for interactive floating elements like Modals, Dropdowns, or Hover states on product cards. The shadow should be highly diffused: `0px 4px 20px rgba(17, 24, 39, 0.08)`.
- **Contrast:** Depth is primarily created by placing `White` cards on a `Surface-Cream` background.

## Shapes

The shape language is "Soft-Rectilinear." While circular "Pill" shapes are popular in consumer apps, this system uses a **fixed 8px (0.5rem) radius** for most components to maintain a disciplined, corporate structure.

- **Buttons & Inputs:** 8px corner radius.
- **Cards:** 12px corner radius for a slightly softer container feel.
- **Status Badges:** 4px (Soft) radius to distinguish them from interactive buttons.

## Components

### Buttons
- **Primary:** Saffron background, Charcoal text, 8px radius. Bold weight.
- **Secondary:** Charcoal background, Cream text.
- **WhatsApp CTA:** Emerald background (#25D366) with a white icon and text. This should be the most prominent secondary action on mobile.

### Status Badges
Used for order tracking. 
- **Pending:** Soft Charcoal text on a Light Gray background.
- **Confirmed:** Emerald text on an ultra-light Green tint.
- **Delivering:** Saffron text on an ultra-light Yellow tint.

### Data Tables & Lists
- **Desktop:** Responsive tables with alternating row backgrounds (Cream and White). 
- **Mobile:** Tables collapse into "List Rows" where the Order ID is the primary label and the Price/Status are secondary metadata on the right.

### Sticky Action Bar
On mobile product pages, a sticky footer bar contains the "Add to Order" button and a quantity selector. This bar should have a subtle top-border and a slight backdrop-blur if placed over scrolling content.

### Inputs
Field labels should always be visible (never placeholder-only). Use the `Label-Bold` style for labels to ensure they are distinct from the user's typed input.
---
name: Saffron & Slate Mobile
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1c1b1b'
  on-surface-variant: '#524435'
  inverse-surface: '#313030'
  inverse-on-surface: '#f3f0ef'
  outline: '#857463'
  outline-variant: '#d7c3af'
  surface-tint: '#855300'
  primary: '#855300'
  on-primary: '#ffffff'
  primary-container: '#e89b2d'
  on-primary-container: '#5b3800'
  inverse-primary: '#ffb95f'
  secondary: '#516169'
  on-secondary: '#ffffff'
  secondary-container: '#d2e2ec'
  on-secondary-container: '#55656d'
  tertiary: '#5f5e59'
  on-tertiary: '#ffffff'
  tertiary-container: '#adaba5'
  on-tertiary-container: '#40403b'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffddb8'
  primary-fixed-dim: '#ffb95f'
  on-primary-fixed: '#2a1700'
  on-primary-fixed-variant: '#653e00'
  secondary-fixed: '#d5e5ef'
  secondary-fixed-dim: '#b9c9d3'
  on-secondary-fixed: '#0e1d25'
  on-secondary-fixed-variant: '#3a4951'
  tertiary-fixed: '#e5e2db'
  tertiary-fixed-dim: '#c9c6c0'
  on-tertiary-fixed: '#1c1c18'
  on-tertiary-fixed-variant: '#474742'
  background: '#fcf9f8'
  on-background: '#1c1b1b'
  surface-variant: '#e5e2e1'
typography:
  headline-lg:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-md:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Source Sans 3
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Source Sans 3
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Source Sans 3
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Source Sans 3
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  margin-mobile: 20px
  gutter-mobile: 16px
  touch-target-min: 48px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 24px
---

## Brand & Style

This design system translates a premium, editorial aesthetic into a high-performance mobile-first experience. It balances the timeless sophistication of luxury publishing with the utilitarian precision required for handheld interaction. The brand personality is poised, discerning, and intentional.

The visual style is **Minimalist with Tactile Accents**. It leverages generous whitespace to create a "gallery" feel, ensuring that content—rather than the interface—remains the focus. While the foundation is clean and flat, subtle depth is introduced through tonal layering to guide the user's thumb toward primary interaction zones. The emotional response should be one of "effortless luxury"—a frictionless journey that feels curated rather than automated.

## Colors

The palette is anchored by "Saffron" (#E89B2D), used strategically for primary calls-to-action and critical brand moments. "Slate" (#2F3E46) provides a grounded, professional contrast for secondary elements and iconography. 

- **Primary (Saffron):** Used for active states, primary buttons, and progress indicators. 
- **Secondary (Slate):** Used for navigation labels, secondary icons, and header text.
- **Surface (Tertiary):** A warm, off-white "Paper" tone (#F4F1EA) serves as the primary background to reduce eye strain and enhance the premium feel.
- **On-Surface (Neutral):** A deep carbon (#1A1A1A) ensures maximum legibility for body copy and dense information.

## Typography

Typography is the primary vehicle for the brand’s premium identity. **Playfair Display** is reserved for headlines and hero statements, providing an editorial "voice." For all functional and high-legibility needs, **Source Sans 3** is employed.

On mobile, headline sizes are strictly capped to ensure they do not push critical content below the fold. Body text maintains a comfortable 16px minimum to ensure accessibility in various lighting conditions. Labels and small metadata use increased letter spacing and semi-bold weights to maintain clarity at small scales.

## Layout & Spacing

This design system utilizes a **Fluid Mobile Grid** based on a 4-column structure for phone-sized devices. 

- **Safe Zones:** A standard 20px horizontal margin is maintained to prevent content from hitting the screen edges.
- **Vertical Rhythm:** Spacing follows an 8px base unit. Interaction elements are separated by at least 16px to prevent accidental taps.
- **Touch Targets:** All interactive elements (buttons, links, inputs) must maintain a minimum height/width of 48px, even if the visual element appears smaller.
- **Bottom-Heavy Ergonomics:** Critical navigation and primary actions are placed in the "Thumb Zone" (the bottom 40% of the screen).

## Elevation & Depth

Depth is communicated through **Tonal Layers** rather than heavy shadows to maintain a clean, modern aesthetic.

1.  **Base (Level 0):** The Tertiary "Paper" background.
2.  **Card/Surface (Level 1):** Pure white (#FFFFFF) surfaces used for content grouping, featuring a subtle 1px border in a lightened Slate tint.
3.  **Floating/Overlay (Level 2):** Use a soft, ambient shadow (0px 4px 12px rgba(47, 62, 70, 0.08)) for bottom sheets and floating action buttons to distinguish them from the content beneath.

Backdrop blurs (10px - 20px) are used behind fixed bottom navigation bars to maintain context while ensuring legibility of the navigation icons.

## Shapes

The shape language is **Soft and Sophisticated**. A subtle 4px (Soft) radius is applied to buttons and input fields to bridge the gap between the sharp serifs of the typography and the organic nature of mobile hardware.

- **Cards:** Use `rounded-lg` (8px) to create a distinct container feel.
- **Buttons:** Use a consistent 4px radius unless it is a Floating Action Button (FAB), which may use a pill-shape for maximum distinction.

## Components

### Bottom Navigation
The primary navigation pattern. Icons are 24px, accompanied by `label-sm` text. The active state uses the Primary Saffron color for both the icon and the text.

### Buttons
- **Primary:** Saffron background with Neutral (Carbon) text. Fixed height of 52px for mobile.
- **Secondary:** Slate outline (1px) with Slate text. 
- **Text Link:** Semi-bold Source Sans 3 with a 1px underline.

### Input Fields
Inputs use a "floating label" pattern to save vertical space. The border is a light Slate grey, turning to Saffron on focus. Error states use a muted crimson (not defined in primary palette but reserved for utility).

### Bottom Sheets
Used for filtered views, selection menus, and quick actions. They should never exceed 90% of the screen height and must include a visual "grabber" handle at the top.

### Chips
Used for filtering and tags. These use the Tertiary (Paper) color as a base with a 1px border. When selected, they transition to a solid Slate background with white text.

### Progress Indicators
Thin, 2px horizontal bars in Saffron at the very top of the viewport or wrapping the primary button during submission.
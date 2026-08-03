---
name: Saffron & Slate
colors:
  surface: '#121411'
  surface-dim: '#121411'
  surface-bright: '#383a36'
  surface-container-lowest: '#0d0f0c'
  surface-container-low: '#1a1c19'
  surface-container: '#1e201d'
  surface-container-high: '#292a27'
  surface-container-highest: '#333532'
  on-surface: '#e3e3de'
  on-surface-variant: '#d1c5ab'
  inverse-surface: '#e3e3de'
  inverse-on-surface: '#2f312e'
  outline: '#9a9078'
  outline-variant: '#4e4632'
  surface-tint: '#f0c100'
  primary: '#ffe6a3'
  on-primary: '#3d2f00'
  primary-container: '#f6c60c'
  on-primary-container: '#695300'
  inverse-primary: '#745c00'
  secondary: '#c6c6c9'
  on-secondary: '#2f3133'
  secondary-container: '#454749'
  on-secondary-container: '#b4b5b7'
  tertiary: '#e6e8ec'
  on-tertiary: '#2e3134'
  tertiary-container: '#caccd0'
  on-tertiary-container: '#53565a'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffe089'
  primary-fixed-dim: '#f0c100'
  on-primary-fixed: '#241a00'
  on-primary-fixed-variant: '#574500'
  secondary-fixed: '#e2e2e5'
  secondary-fixed-dim: '#c6c6c9'
  on-secondary-fixed: '#1a1c1e'
  on-secondary-fixed-variant: '#454749'
  tertiary-fixed: '#e1e2e6'
  tertiary-fixed-dim: '#c5c6ca'
  on-tertiary-fixed: '#191c1f'
  on-tertiary-fixed-variant: '#44474a'
  background: '#121411'
  on-background: '#e3e3de'
  surface-variant: '#333532'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 36px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-sm:
    fontFamily: Playfair Display
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.05em
  caption:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '400'
    lineHeight: '1.4'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 8px
  sm: 16px
  md: 24px
  lg: 48px
  xl: 80px
  container-max: 1200px
  gutter: 24px
---

## Brand & Style

This design system evokes the atmosphere of a high-end, candle-lit fine dining experience. The visual narrative is built on intimacy, prestige, and warmth. By combining deep, nocturnal slate tones with the glow of saffron accents, the interface achieves a "low-key" lighting effect common in luxury environments.

The design style is **Sophisticated Minimalism with Glassmorphic touches**. It prioritizes high-quality typography and generous whitespace to ensure the gold accents feel like precious highlights rather than overwhelming elements. The emotional response should be one of calm, exclusivity, and focused attention.

## Colors

The palette is engineered to simulate depth and warmth in a dark environment.

- **Primary (Saffron Gold):** Used sparingly for high-intent actions (CTAs), active states, and critical highlights. It represents the "candlelight" against the dark surroundings.
- **Surface (Deep Slate):** The foundational layer (#1A1C1E). It provides a neutral, cool-toned base that allows the gold to pop without vibration.
- **Surface Variant (Charcoal):** Used for elevated cards and containers (#2D3033) to create a sense of physical layering.
- **Text (Cream):** An off-white (#F5F5F0) used for primary body text to reduce eye strain and maintain the "warm" luxury aesthetic, avoiding the harshness of pure #FFFFFF.

## Typography

The typographic scale uses a traditional serif for headlines to anchor the design in heritage and elegance, while a modern geometric sans-serif handles the functional body copy for optimal legibility.

- **Headlines:** Playfair Display should be used for titles and editorial sections. Use "display-lg" for hero sections and "headline-md/sm" for section headers.
- **Body:** Plus Jakarta Sans is the workhorse for all interface elements, descriptions, and inputs.
- **Labels:** Use the "label-md" style for small navigational elements or overlines. The slight tracking (letter spacing) and uppercase transformation add a refined, architectural feel.

## Layout & Spacing

The layout philosophy follows a **fixed grid** model for desktop and a **fluid fluid** model for mobile, emphasizing breathing room to mirror the spaciousness of a fine-dining floor plan.

- **Grid:** 12-column grid on desktop with 24px gutters. Use wide margins (80px+) to center content and create an editorial feel.
- **Mobile:** 4-column grid with 16px margins.
- **Rhythm:** Use increments of 8px for vertical rhythm. "lg" (48px) and "xl" (80px) spacing should be used between major sections to prevent the dark interface from feeling cramped or heavy.

## Elevation & Depth

Depth in this design system is achieved through **Tonal Layering and Soft Ambient Shadows**.

1.  **Level 0 (Base):** Deep Slate (#1A1C1E).
2.  **Level 1 (Cards/Modals):** Charcoal (#2D3033). These elements use a very subtle, 1px border in a slightly lighter slate (#3D4043) to define edges against the base.
3.  **Shadows:** Use large, diffused shadows with low opacity (15-20%) and a slight saffron tint (#F6C60C) for active elements to simulate a "glow" rather than a traditional drop shadow.
4.  **Glassmorphism:** For top navigation bars or floating menus, use a 70% opacity version of the Deep Slate with a 20px background blur.

## Shapes

The shape language is sophisticated and approachable. A **16px (1rem) corner radius** is the standard for primary containers, buttons, and input fields.

- **Standard (rounded-md):** 16px for buttons, cards, and large containers.
- **Small (rounded-sm):** 8px for smaller interactive elements like checkboxes or nested tags.
- **Large (rounded-lg):** 24px for feature banners or prominent hero images.

## Components

### Buttons
- **Primary:** Saffron Gold background with Slate text. 16px rounded corners. Use "label-md" typography.
- **Secondary:** Transparent background with a 1.5px Saffron Gold border.
- **Ghost:** Cream text with no border. Gold color on hover.

### Inputs & Fields
- **Fields:** Charcoal background (#2D3033) with a subtle 1px border (#3D4043). On focus, the border transitions to Saffron Gold with a soft outer glow.
- **Placeholders:** Muted slate-gray text to maintain hierarchy.

### Cards
- **Structure:** Charcoal background, 16px corner radius. Padding should be generous (min 24px).
- **Interactive State:** On hover, cards should lift slightly (translate Y -4px) and increase shadow diffusion.

### Selection Controls
- **Checkboxes/Radio Buttons:** Use Saffron Gold for the "checked" state. The unchecked state is a simple Charcoal circle/square with a 1px border.

### Lists & Navigation
- **Active States:** Indicated by a small vertical Saffron bar to the left of the item or a text color change to Gold.
- **Dividers:** Very thin (1px) with low opacity (10%) Cream color to prevent visual clutter.
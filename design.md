---
name: Industrial Logic
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#45464d'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#0058be'
  on-secondary: '#ffffff'
  secondary-container: '#2170e4'
  on-secondary-container: '#fefcff'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#0b1c30'
  on-tertiary-container: '#75859d'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#adc6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#004395'
  tertiary-fixed: '#d3e4fe'
  tertiary-fixed-dim: '#b7c8e1'
  on-tertiary-fixed: '#0b1c30'
  on-tertiary-fixed-variant: '#38485d'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  data-mono:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  unit-1: 0.25rem
  unit-2: 0.5rem
  unit-4: 1rem
  unit-6: 1.5rem
  unit-8: 2rem
  container-margin: 2rem
  gutter: 1rem
---

## Brand & Style

This design system is built for the high-stakes environment of enterprise logistics. The brand personality is authoritative, systematic, and resilient. It prioritizes utility over decoration, following an **Industrial Professional** aesthetic that blends modern SaaS clarity with the rugged reliability required for warehouse operations.

The UI avoids trends in favor of longevity and accessibility. It employs a refined **Minimalist** approach with a focus on data density, ensuring that warehouse managers and floor supervisors can scan complex information rapidly without cognitive fatigue. The visual language communicates precision through strict alignment and a systematic use of color.

## Colors

The color palette is grounded in "Deep Slate" and "Industrial Blue" to evoke a sense of professional stability. 

- **Primary:** Used for high-level navigation, primary actions, and brand identification.
- **Secondary:** Reserved for interactive elements like text links and secondary buttons.
- **Neutral:** A range of cool grays used for backgrounds, borders, and subtle structural divisions to keep the interface feeling open.
- **Status Colors:** These are non-negotiable semantic markers. Success Green indicates completed shipments or stocked items; Warning Amber signals low stock or pending approvals; Alert Red marks overdue tasks or system errors.

Use background layering (Slate 50 to Slate 200) to define distinct work zones without relying on heavy borders.

## Typography

Typography is the backbone of this design system. **Inter** is used for its exceptional legibility and neutral character. **JetBrains Mono** is introduced specifically for SKU numbers, tracking codes, and quantities to prevent character confusion (e.g., distinguishing between '0' and 'O').

- **Hierarchy:** Use `headline-md` for view titles and `body-sm` for secondary metadata in tables.
- **Data Density:** In data-heavy views, prefer `body-md` over `body-lg` to maximize the information visible on a single screen.
- **Labels:** Use `label-caps` (uppercase) for table headers and section labels to create a clear visual distinction from the data itself.

## Layout & Spacing

This design system utilizes a **12-column fluid grid** for the main content area, anchored by a fixed **240px sidebar navigation**.

- **Sidebar:** Remains fixed to the left. Icons should be paired with text labels for immediate recognition.
- **Data Tables:** Use a 40px row height for standard density and 32px for high-density views.
- **Margins:** Maintain a 32px (unit-8) margin around the main content container to provide breathing room in an otherwise data-rich environment.
- **Breakpoints:** On tablets, the sidebar collapses into an icon-only rail. On mobile, the system shifts to a single-column stacked layout with full-width action buttons.

## Elevation & Depth

To maintain a "flat and functional" feel, depth is communicated through **Tonal Layers** and **Low-contrast Outlines** rather than heavy shadows.

- **Level 0 (Surface):** Background (#F8FAFC).
- **Level 1 (Cards/Tables):** White (#FFFFFF) with a 1px border (#E2E8F0).
- **Level 2 (Modals/Popovers):** White with a soft, 8% opacity neutral shadow to separate the element from the workspace.
- **Interactive States:** Use subtle shifts in background color (e.g., Slate 50 to Slate 100) to indicate hover states on list items and table rows. Avoid glowing effects or high-intensity transitions.

## Shapes

The shape language is **Soft (0.25rem)**, reflecting a balance between industrial precision and modern software usability. 

- **Buttons & Inputs:** Use a 4px corner radius. This is sharp enough to feel professional but rounded enough to feel accessible.
- **Status Badges:** Use a 2px radius or "Soft" setting to distinguish them from interactive buttons.
- **Large Containers:** Use `rounded-lg` (0.5rem) for main dashboard widgets to slightly soften the overall interface.

## Components

### Buttons
- **Primary:** Solid #0F172A with white text. High contrast for critical actions (e.g., "Confirm Shipment").
- **Secondary:** Outline #64748B. For routine actions.
- **Ghost:** No border, blue text. Used for "Cancel" or "Go Back."

### Data Tables
Tables are the most critical component. They must feature:
- **Fixed Headers:** Always visible during scroll.
- **Zebra Striping:** Use Slate 50 on even rows for better horizontal tracking.
- **Alignment:** Numbers and dates are right-aligned; text is left-aligned.

### Status Badges
Small, pill-like indicators with light background tints and dark text of the same hue (e.g., Success: #D1FAE5 background with #065F46 text).

### Input Fields
- Standard height: 36px.
- Focus state: 1px solid #3B82F6 with a subtle 2px blue outer glow (20% opacity).
- Labels: Always positioned above the input, never as placeholders only.

### Sidebar
- Icons: 20px stroke-based icons.
- Active State: A 3px vertical primary-blue bar on the far left edge of the active navigation item.
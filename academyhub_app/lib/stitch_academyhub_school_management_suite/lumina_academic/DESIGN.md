---
name: Lumina Academic
colors:
  surface: '#0b1326'
  surface-dim: '#0b1326'
  surface-bright: '#31394d'
  surface-container-lowest: '#060e20'
  surface-container-low: '#131b2e'
  surface-container: '#171f33'
  surface-container-high: '#222a3d'
  surface-container-highest: '#2d3449'
  on-surface: '#dae2fd'
  on-surface-variant: '#d8c3ad'
  inverse-surface: '#dae2fd'
  inverse-on-surface: '#283044'
  outline: '#a08e7a'
  outline-variant: '#534434'
  surface-tint: '#ffb95f'
  primary: '#ffc174'
  on-primary: '#472a00'
  primary-container: '#f59e0b'
  on-primary-container: '#613b00'
  inverse-primary: '#855300'
  secondary: '#adc6ff'
  on-secondary: '#002e6a'
  secondary-container: '#0566d9'
  on-secondary-container: '#e6ecff'
  tertiary: '#d8c3ff'
  on-tertiary: '#3f008e'
  tertiary-container: '#c0a1ff'
  on-tertiary-container: '#5600be'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffddb8'
  primary-fixed-dim: '#ffb95f'
  on-primary-fixed: '#2a1700'
  on-primary-fixed-variant: '#653e00'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#adc6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#004395'
  tertiary-fixed: '#eaddff'
  tertiary-fixed-dim: '#d2bbff'
  on-tertiary-fixed: '#25005a'
  on-tertiary-fixed-variant: '#5a00c6'
  background: '#0b1326'
  on-background: '#dae2fd'
  surface-variant: '#2d3449'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-padding: 24px
  gutter: 16px
  section-gap: 40px
  stack-sm: 12px
  stack-md: 24px
---

## Brand & Style
The design system for this product is centered on the intersection of academic authority and modern technological precision. The brand personality is professional and reliable, yet evokes a sense of high-end exclusivity. The design style utilizes a **Modern-Corporate** base with **Neomorphic** influences in light mode and **Luminescent** accents in dark mode.

The visual narrative focuses on "clarity through depth," using significant whitespace and layered surfaces to organize complex institutional data into a digestible, premium experience. The emotional response should be one of calm control and institutional trust.

## Colors
This design system utilizes a sophisticated Slate-based foundation to ground the interface. The **Primary Accent (Amber)** is reserved for high-priority actions and brand moments, creating a "golden hour" glow against the deep Slate backgrounds.

- **Primary:** Amber (#F59E0B) for CTAs, focus states, and progress.
- **Secondary:** Royal Blue (#3B82F6) for informational links and student records.
- **Surface Strategy:** In dark mode, use Slate-900 for the base and Slate-800 for elevated containers. In light mode, use Slate-50 for the base and pure White for containers.
- **Glow Effects:** In dark mode, primary elements should emit a subtle 15% opacity outer glow of their own hex value to simulate luminosity.

## Typography
The system uses **Plus Jakarta Sans** across all levels to maintain a contemporary, friendly, yet professional character. 

- **Weight Strategy:** Headlines use Bold (700) or ExtraBold (800) to create a strong visual anchor. Body text stays at Regular (400) for maximum legibility.
- **Vertical Rhythm:** A generous line-height (1.5x for body) is required to ensure the dense information of a school management app remains approachable.
- **Hierarchy:** Use the Display size for dashboard greetings and major section headings. Labels should use SemiBold (600) to stand out against data values.

## Layout & Spacing
The layout follows a **Fluid Grid** model with high-margin "safe zones" to emphasize the premium feel.

- **Desktop:** 12-column grid, 24px gutters, and 48px side margins.
- **Tablet:** 8-column grid, 16px gutters, 32px side margins.
- **Mobile:** 4-column grid, 16px gutters, 20px side margins.
- **Philosophy:** Favor white space over information density. If a screen feels cluttered, increase the `stack-md` spacing to `stack-lg` (32px).

## Elevation & Depth
This design system employs a dual-mode depth philosophy:

**Light Mode (Neomorphic & Soft):**
- Use dual shadows for cards: a light source shadow (White, -4px -4px, 10px blur) and a soft drop shadow (Slate-200, 4px 4px, 12px blur).
- Backgrounds remain flat Slate-50, while interactive surfaces appear "pushed" or "extruded."

**Dark Mode (Luminescent Layers):**
- Depth is achieved via **Tonal Layers**. Slate-900 (Base) -> Slate-800 (Cards) -> Slate-700 (Popovers).
- Borders are 1px solid with 10% opacity White to define edges without adding visual weight.
- Primary buttons use a 20px blur Amber glow when focused.

## Shapes
The shape language is "Hyper-Rounded" to soften the institutional nature of school management.

- **Cards & Large Containers:** Use 24px (`rounded-xl` variant) to create a friendly, modern frame for data.
- **Buttons & Inputs:** Use 12px or 16px to maintain a cohesive look with the larger containers.
- **Icons:** Use a 2px stroke weight with rounded terminals and joins.

## Components

### Buttons
- **Primary:** Large (min-height 56px), Amber background, White or Slate-900 text (Bold). Soft ambient shadow matching the button color at 30% opacity.
- **Secondary:** Transparent background with a 2px Royal Blue border.

### Inputs
- **Dark Mode:** Recessed style. Background Slate-950, 1px Slate-700 border, 16px corner radius.
- **Light Mode:** White background, subtle 1px Slate-200 border, soft inner shadow to indicate interactivity.

### Cards
- Standardize at 24px corner radius. In dark mode, use a top-down linear gradient (Slate-800 to Slate-900) to add subtle dimension.

### Navigation
- **Sidebar:** Fixed width (280px), using glassmorphic blur (20px) over background elements. 
- **Active State:** A pill-shaped highlight using the Primary Amber color at 10% opacity with a 4px left-side accent bar.

### Status Indicators
- Use small, high-saturation circular badges (8px) paired with Label-Sm text. Categorization should be color-coded (Violet for Arts, Emerald for Science, Royal Blue for Admin).
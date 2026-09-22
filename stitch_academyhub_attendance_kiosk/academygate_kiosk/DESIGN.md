---
name: AcademyGate Kiosk
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
  on-surface-variant: '#534434'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#867461'
  outline-variant: '#d8c3ad'
  surface-tint: '#855300'
  primary: '#855300'
  on-primary: '#ffffff'
  primary-container: '#f59e0b'
  on-primary-container: '#613b00'
  inverse-primary: '#ffb95f'
  secondary: '#565e74'
  on-secondary: '#ffffff'
  secondary-container: '#dae2fd'
  on-secondary-container: '#5c647a'
  tertiary: '#545f73'
  on-tertiary: '#ffffff'
  tertiary-container: '#a6b1c8'
  on-tertiary-container: '#394457'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffddb8'
  primary-fixed-dim: '#ffb95f'
  on-primary-fixed: '#2a1700'
  on-primary-fixed-variant: '#653e00'
  secondary-fixed: '#dae2fd'
  secondary-fixed-dim: '#bec6e0'
  on-secondary-fixed: '#131b2e'
  on-secondary-fixed-variant: '#3f465c'
  tertiary-fixed: '#d8e3fb'
  tertiary-fixed-dim: '#bcc7de'
  on-tertiary-fixed: '#111c2d'
  on-tertiary-fixed-variant: '#3c475a'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-kiosk:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.03em
  display-kiosk-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '800'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-xl:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '400'
    lineHeight: 28px
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
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  label-numeric-keypad:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 32px
  label-badge:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-action:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '700'
    lineHeight: 20px
    letterSpacing: 0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  margin: 2rem
  space-xs: 0.375rem
  space-sm: 0.75rem
  space-md: 1.25rem
  space-lg: 1.75rem
  space-xl: 2.5rem
---

## Brand & Style

This design system powers a high-throughput physical attendance terminal and campus access kiosk designed for educational institutions, academies, and administrative centers. The target audience spans elementary to high school students, faculty members, visiting parents, and front-desk security officers.

The aesthetic fuses modern civic utility with warm, authoritative institutionality—termed **Amber & Deep Slate Modern**. It blends deep institutional foundation anchors with high-energy amber wayfinding accents to create an interface that feels dependable, inviting, and glanceable from across an entryway.

### Visual Principles
- **Instant Glanceability:** High contrast between deep slate architectural frames and high-visibility status indicators guarantees clarity under fluorescent school lighting and indirect outdoor sunlight.
- **Dignified & Accessible:** Avoids overly playful toy-like motifs; maintains an elevated educational atmosphere while retaining approachable tactile affordances for users wearing gloves, carrying backpacks, or interacting quickly.
- **State-Dominant Architecture:** Operational state (success, late, signed out, exception) overrides decoration. The UI shifts from calm neutrality to unmistakable color signals during badge scans, QR reads, or PIN entry.

## Colors

The color architecture balances a heavy neutral slate base with functional signal colors and an amber core identity.

### Palette Architecture
- **Primary Brand Amber:** `#F59E0B` (Amber Core), `#D97706` (Amber Dark / Active Focus), `#FFFBEB` (Amber Light Tint / Ambient Highlight). Used for primary actionable elements, scanner target highlights, active tabs, and identity badges.
- **Deep Slate Structural Base:** `#0F172A` (Terminal Header / Top Bar / Dominant Headings), `#1E293B` (Slate Navy / Secondary Structural Surfaces / Keypads), `#64748B` (Muted Slate / Metadata / Placeholder text).
- **Backgrounds & Canvas:** `#F8FAFC` (Canvas Neutral), `#FFFFFF` (Pure Card & Tile Surface), `#E2E8F0` (Structural Border Ring).

### Semantic & Kiosk Status Tokens
- **On-Time / Success:** Text & Stroke `#059669`, Fill Surface `#ECFDF5`.
- **Tardy / Late Alert:** Text & Stroke `#F97316`, Fill Surface `#FFF7ED`.
- **Signed Out / Departure:** Text & Stroke `#2563EB`, Fill Surface `#EFF6FF`.
- **Absent / Security Flag:** Text & Stroke `#E11D48`, Fill Surface `#FFF1F2`.

All status colors are strictly paired as high-contrast pairs (deep tone on 50-tint surface) to guarantee WCAG AAA compliance for fast verification in ambient environments.

## Typography

The type system pairs **Plus Jakarta Sans** for structural display, headings, interactive buttons, and keypad labels with **Inter** for dense transactional information, time-logs, and administrative records.

### Usage Rules
- `display-kiosk`: Exclusively for real-time digital clocks, live welcome messages, and pass/fail scan summaries visible from 1.5 to 3 meters away.
- `label-numeric-keypad`: High-weight, central-aligned numbers for student ID pin-pads with strict vertical centering.
- `label-badge`: Rendered in all-caps or title case with increased letter spacing (`0.04em`) to optimize scanning accuracy on small pill elements.

## Layout & Spacing

Attendance kiosks demand large physical touch zones and robust spacing models to avoid erroneous taps on high-speed hardware touchscreens.

### Grid & Canvas Structure
- **Kiosk Mode (Fixed Portrait & Landscape Tablet/Terminal):** Centered operational shell fixed at a maximum canvas width of 1280px. Dual-zone split in landscape (40% Left Navigation & Live Scanner / 60% Active Verification Flow or Roster Feed).
- **Touch-First Guardrails:** Minimum tap target across all touch zones is `56px x 56px`, with key interactive controls set to `64px` height.
- **Rhythm Model:** A strict 4px/8px incremental rhythm. Internal card padding uses `space-md` (`1.25rem`) for compact tiles and `space-xl` (`2.5rem`) for primary check-in hero states.

## Elevation & Depth

Visual depth is achieved through structural border rings, pure white foreground surfaces, and subtle, low-slung ambient shadows tuned specifically for bright industrial displays.

### Elevation Levels
- **Base Canvas (Level 0):** Flat `#F8FAFC`, non-elevated.
- **Interactive Card Surface (Level 1):** Solid `#FFFFFF`, framed with a clean border ring (`1px solid #E2E8F0`), elevated by a soft dual shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.05)`.
- **Focused / Scanning / Modal Surface (Level 2):** `#FFFFFF` with a distinct amber or semantic ring glow (`2px solid #F59E0B` or matching status color) and deep ambient shadow: `0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05)`.
- **Header & Navigation Frame:** Solid `#0F172A` with a crisp baseline edge (`1px solid #1E293B`), casting no shadow to anchor the terminal securely.

## Shapes

The interface balances soft human touch with structured geometric order. Standard containers utilize balanced rounding, while distinct elements take specific shape treatments:

- **Cards & Primary Modules:** Strict `rounded-2xl` (`1rem` / `16px`) creating friendly, non-aggressive perimeter lines that isolate records into digestable visual pods.
- **Buttons & Input Targets:** Strict `rounded-xl` (`0.75rem` / `12px`) providing distinct tactile buttons without transitioning into full oval forms.
- **Status Badges & Live State Pills:** Complete pill curves (`rounded-full` / `9999px`) to immediately distinguish informative metadata from actionable square-cornered buttons.

## Components

### Buttons
- **Primary Amber Action:** Fill `#F59E0B`, text `#0F172A` (high-contrast deep slate text for superior visibility over amber), hover/pressed `#D97706`. Height: 56px (terminal) or 48px (desktop). Rounded-xl (`12px`).
- **Secondary Structural:** Surface `#1E293B`, text `#FFFFFF`, hover `#0F172A`. Height matches primary.
- **Ghost / Action Secondary:** Border `1.5px solid #E2E8F0`, surface `#FFFFFF`, text `#0F172A`.

### Status Badges (Pills)
- Compact, fully rounded (`rounded-full`) badges featuring an 8px circular indicator dot alongside bold `label-badge` text.
- **On-Time:** `#ECFDF5` background, `#059669` text and indicator dot.
- **Late:** `#FFF7ED` background, `#F97316` text and indicator dot.
- **Signed Out:** `#EFF6FF` background, `#2563EB` text and indicator dot.
- **Absent:** `#FFF1F2` background, `#E11D48` text and indicator dot.

### Cards & Tile Containers
- Outer shape `rounded-2xl`, background `#FFFFFF`, border ring `1px solid #E2E8F0`.
- Hover/Active states introduce a `2px solid #F59E0B` ring offset by 2px white space.

### Kiosk Numeric Keypad
- Grid layout with 12px gap. Keys are `rounded-xl` surfaces in `#FFFFFF` with `1.5px solid #E2E8F0`, text `#0F172A` in `label-numeric-keypad`.
- Active press state shifts surface to `#FFFBEB` with border `#F59E0B`.

### Live Verification Feed (Roster List)
- Rows housed in cards with horizontal layout: Student Photo Avatar (48px rounded-full), Full Name (`headline-sm`), Grade/ID (`body-sm` in `#64748B`), Timestamp (`Inter`, 14px tabular figures), and right-aligned Status Badge Pill.
- Alternating subtle row separator using `#F1F5F9`.

### Identity Scan Target Frame
- Embedded camera/RFID viewport with `rounded-2xl` border. Resting border `2px dashed #94A3B8`. Active scanning triggers animated amber sweeping gradient and `#F59E0B` solid corner guides.
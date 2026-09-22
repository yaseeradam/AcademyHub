# 📱 AcademyHub Flutter App — Full Design & UX Guide

## 1. Design Philosophy
The app should feel like a premium school companion — not a corporate dashboard. Think soft, breathable, calm. Parents and students should feel at ease, teachers should feel efficient. Every screen should load fast, feel native, and never feel cluttered.

Three words that define the feel: **Soft. Clean. Trustworthy.**

---

## 2. Color System

### Primary Palette
| Color | Hex | Purpose / Description |
| :--- | :--- | :--- |
| **Primary Blue** | `#1E3A5F` | Headers, navigation bar, primary buttons |
| **Soft Blue** | `#2D6A9F` | Secondary actions, links |
| **Accent Amber** | `#F59E0B` | Badges, highlights, Call to Actions (CTAs) |
| **Success Green** | `#22C55E` | Sync indicator (synced), present attendance |
| **Warning Orange** | `#F97316` | Overdue homework, late attendance |
| **Danger Red** | `#EF4444` | Sync indicator (unsynced), absent, errors |

### Background & Surface
| Color | Hex | Purpose / Description |
| :--- | :--- | :--- |
| **App Background** | `#F4F6FA` | Main screen background (very light blue-grey) |
| **Card Surface** | `#FFFFFF` | All cards, modals, bottom sheets |
| **Divider** | `#E8ECF2` | Subtle separators |
| **Input Fill** | `#F0F4F8` | Text field backgrounds |

### Text
| Color | Hex | Purpose / Description |
| :--- | :--- | :--- |
| **Primary Text** | `#1A2B45` | Headings, names |
| **Secondary Text** | `#6B7A99` | Subtitles, labels, timestamps |
| **Disabled Text** | `#B0BAD0` | Placeholders, inactive elements |

### Role Accent Colors
*Used on home screen header gradient:*
*   **Student:** `#1E3A5F` → `#2D6A9F` *(Deep to medium blue)*
*   **Parent:** `#1E3A5F` → `#6B4FA0` *(Blue to soft purple)*
*   **Teacher:** `#1E3A5F` → `#0F766E` *(Blue to teal)*
*   **Admin:** `#1E3A5F` → `#B45309` *(Blue to warm brown)*

---

## 3. Typography
Use **Inter** (Google Fonts) throughout. It is clean, modern, and highly readable on small screens.

*   **Display / Name:** Inter Bold, 24sp
*   **Section Header:** Inter SemiBold, 18sp
*   **Card Title:** Inter SemiBold, 15sp
*   **Body:** Inter Regular, 14sp
*   **Label / Caption:** Inter Medium, 12sp
*   **Tiny / Timestamp:** Inter Regular, 11sp

> [!NOTE]
> Line height should be **1.5x** for body and **1.3x** for headings. Never go below 11sp.

---

## 4. Spacing & Shape System

### Border Radius
*   **Cards:** 16px
*   **Buttons:** 12px
*   **Input fields:** 10px
*   **Chips / badges:** 20px (pill)
*   **Bottom sheets:** 24px (top corners only)

### Padding
*   **Screen edge:** 20px horizontal
*   **Card inner:** 16px
*   **Between cards:** 12px
*   **Section gap:** 24px

### Elevation
*   **Cards:** `shadow: 0 2px 8px rgba(30, 58, 95, 0.08)`
*   **Bottom nav:** `shadow: 0 -2px 12px rgba(30, 58, 95, 0.10)`
*   **Modals:** `shadow: 0 8px 32px rgba(30, 58, 95, 0.16)`

---

## 5. Login Flow

### Screen 1 — School Finder
```text
┌─────────────────────────────────┐
│                                 │
│        [AcademyHub Logo]        │
│     Your School, In Your Pocket │
│                                 │
│  ┌───────────────────────────┐  │
│  │  🏫  Enter school slug    │  │
│  │  e.g.  greenwood          │  │
│  └───────────────────────────┘  │
│                                 │
│  ┌─────────────────────────┐    │
│  │       Continue →        │    │
│  └─────────────────────────┘    │
│                                 │
│  Hint text below field:         │
│  "Your school gave you this"    │
└─────────────────────────────────┘
```
*   **Background:** Soft gradient `#F4F6FA` → `#E8EEF8`.
*   **Logo:** Centered, 80px, with a soft drop shadow.
*   **Slug Field:** Has a school icon prefix and live validation (calls `/api/tenant/{slug}`).
*   **On Valid Slug:** School name + logo fades in below the field as a confirmation chip.
*   **On Invalid Slug:** Gentle shake animation + red border (no harsh error modal).

### Screen 2 — Role & Credentials
```text
┌─────────────────────────────────┐
│  ← Greenwood High School        │  ← school name confirmed
│     [school logo small]         │
│                                 │
│  Who are you?                   │
│                                 │
│  ┌──────┐  ┌──────┐  ┌──────┐  │
│  │  👨‍🎓  │  │  👨‍👩‍👧  │  │  👨‍🏫  │  │
│  │Student│  │Parent│  │ Staff│  │
│  └──────┘  └──────┘  └──────┘  │
│                                 │
│  [Admission No. / Email field]  │
│  [Password field]               │
│                                 │
│  [        Sign In        ]      │
└─────────────────────────────────┘
```
*   **Role Selector:** Uses pill-shaped toggle cards; selected role gets **Primary Blue** fill + white text.
*   **Field Dynamic Label:** Student role shows "Admission Number" label, other roles show "Email".
*   **Password Field:** Has a show/hide toggle.
*   **Sign In Button:** Full-width, Primary Blue, 52px tall, rounded 12px.
*   **Loading State:** Button shrinks to a spinner circle with a smooth transition.

---

## 6. Global App Shell

### Top Bar
```text
┌──────────────────────────────────────────┐
│  [☰ or ←]   Page Title        [●] [🔔]  │
└──────────────────────────────────────────┘
```
`[●]` is the **sync indicator dot** (10px circle):
*   🟢 **Green** (`#22C55E`) — everything synced.
*   🔴 **Red** (`#EF4444`) — pending unsynced mutations in queue.
*   🟡 **Pulsing Amber** (`#F59E0B`) — currently syncing (animated pulse).
*   *Tapping the dot* opens a small bottom sheet showing sync status (e.g., *"3 changes pending sync"* or *"All data synced · Last sync 2 min ago"*).

`[🔔]` is the **notification bell** with an unread count badge (amber dot).
*   **Top Bar Background:** White, with a very subtle bottom shadow.

### Offline Banner
When no internet connection is detected, a slim banner slides down from the top bar:
```text
┌──────────────────────────────────────────┐
│  📡  Offline — changes saved locally     │
└──────────────────────────────────────────┘
```
*   **Background:** `#FEF3C7` (soft amber)
*   **Text:** `#92400E`
*   **Height:** 32px, slides in with a smooth 200ms ease.
*   **Return Online:** Disappears automatically when connection restores, replaced by a brief green *"Back online ✓"* flash.

### Bottom Navigation Bar
```text
┌──────────────────────────────────────────┐
│  🏠 Home  │ 📋 My Work │ 💬 Chat │ 👤 Me │
└──────────────────────────────────────────┘
```
*   **Background:** White, with a top shadow.
*   **Active Tab:** Primary Blue icon + label, with a small 4px rounded indicator bar above the icon.
*   **Inactive Tab:** `#B0BAD0` grey.
*   **Role Specificity:** Each user role gets different tabs (see Section 9).

---

## 7. Card Design Language
All content lives in cards. Cards are the core visual unit.

### Standard Card
```text
┌─────────────────────────────────────┐
│  [Icon/Avatar]  Title               │
│                 Subtitle · time     │
│                                     │
│  [optional body content]            │
│                                [→]  │
└─────────────────────────────────────┘
```
*   White background, 16px border radius, 16px padding on all sides.
*   `shadow: 0 2px 8px rgba(30, 58, 95, 0.08)`.
*   **Tap ripple:** Very light blue `rgba(45, 106, 159, 0.08)`.

### Stat Card (Dashboard Grid)
```text
┌──────────────────┐
│  [Icon]          │
│  84%             │  ← large number, Bold 28sp
│  Attendance      │  ← label, 12sp secondary
└──────────────────┘
```
*   2-column grid layout on dashboards.
*   Icon in a soft tinted circle (e.g., green circle for attendance).
*   Number in **Primary Text** color.
*   Subtle left border accent (4px, role accent color).

### List Item Card
```text
┌─────────────────────────────────────┐
│  [Avatar]  Full Name          [A]   │
│            JSS 2A · STU20240001     │
└─────────────────────────────────────┘
```
*   **Avatar:** 44px circle, initials fallback with soft colored background.
*   **Grade Badge:** Right-aligned, pill shape, color-coded (A=green, B=blue, C=amber, F=red).

---

## 8. Screen-by-Screen Design

### Home Screen (Student)
```text
┌─────────────────────────────────────┐
│  Good morning, Amina 👋             │  ← personalized greeting
│  Term 2 · 2024/2025  [●sync]  [🔔] │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  JSS 2A · Section B          │   │  ← class card, gradient bg
│  │  Position: 3rd of 28         │   │
│  │  Avg Score: 74.2             │   │
│  └──────────────────────────────┘   │
│                                     │
│  Quick Actions                      │
│  ┌────────┐ ┌────────┐ ┌────────┐  │
│  │Results │ │Attend. │ │Homework│  │
│  └────────┘ └────────┘ └────────┘  │
│                                     │
│  📢 Announcements                   │
│  ┌──────────────────────────────┐   │
│  │ School closes Friday         │   │
│  │ 2 hours ago                  │   │
│  └──────────────────────────────┘   │
└─────────────────────────────────────┘
```

### Home Screen (Teacher)
```text
┌─────────────────────────────────────┐
│  Hello, Mr. Hassan 👋               │
│  Term 2 · 2024/2025  [●sync]  [🔔] │
│                                     │
│  Today's Classes                    │
│  ┌──────────────────────────────┐   │
│  │  📚 Mathematics              │   │
│  │  JSS 2A · 9:00 – 10:00am    │   │
│  └──────────────────────────────┘   │
│  ┌──────────────────────────────┐   │
│  │  📚 Physics                  │   │
│  │  SS 1B · 11:00 – 12:00pm    │   │
│  └──────────────────────────────┘   │
│                                     │
│  Pending                            │
│  ┌──────────┐  ┌──────────────┐    │
│  │ 2 unsub. │  │ 5 ungraded   │    │
│  │ homework │  │ submissions  │    │
│  └──────────┘  └──────────────┘    │
└─────────────────────────────────────┘
```

### Home Screen (Parent)
```text
┌─────────────────────────────────────┐
│  Welcome, Mrs. Fatima 👋            │
│  Term 2 · 2024/2025  [●sync]  [🔔] │
│                                     │
│  Your Children                      │
│  ┌──────────────────────────────┐   │
│  │  [photo]  Amina Hassan       │   │
│  │  JSS 2A · Avg: 74 · 92% att │   │
│  └──────────────────────────────┘   │
│  ┌──────────────────────────────┐   │
│  │  [photo]  Bilal Hassan       │   │
│  │  Primary 5 · Avg: 68 · 88%  │   │
│  └──────────────────────────────┘   │
│                                     │
│  Fee Alert 🔴                       │
│  ┌──────────────────────────────┐   │
│  │  Amina — ₦15,000 outstanding │   │
│  └──────────────────────────────┘   │
└─────────────────────────────────────┘
```

### Attendance Screen (Teacher — Offline-Capable)
```text
┌─────────────────────────────────────┐
│  ← Attendance                       │
│  JSS 2A · Monday, 14 Jul 2025       │
│                                     │
│  [Mark All Present]  [Mark All Abs] │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  [photo] Amina Hassan        │   │
│  │  ● Present  ○ Absent  ○ Late │   │
│  └──────────────────────────────┘   │
│  ┌──────────────────────────────┐   │
│  │  [photo] Bilal Garba         │   │
│  │  ○ Present  ● Absent  ○ Late │   │
│  └──────────────────────────────┘   │
│                                     │
│  [      Save Attendance      ]      │
│  📡 Offline — will sync later       │
└─────────────────────────────────────┘
```
*   **Status Selector:** Uses segmented pill buttons per student row.
*   **Colors:** Present = green fill, Absent = red fill, Late = amber fill.
*   **Save Button:** Queues to local database if offline and turns the sync dot red.
*   **Offline Toast:** A *"Saved offline"* toast alert appears at the bottom.

### Scores Entry Screen (Teacher)
```text
┌─────────────────────────────────────┐
│  ← Enter Scores                     │
│  Mathematics · JSS 2A · Term 2      │
│                                     │
│  Student          CA1  CA2  Exam    │
│  ─────────────────────────────────  │
│  Amina Hassan      18   17   52     │
│  Bilal Garba       15   [_]  [_]    │  ← tappable cells
│  Hassan Abubakar  [_]  [_]  [_]    │
│                                     │
│  [      Save Scores      ]          │
└─────────────────────────────────────┘
```
*   Spreadsheet-style interface customized for mobile screens.
*   **Tapping a Cell:** Opens a numeric bottom sheet keypad (not the system keyboard).
*   **Values:** Filled cells show values in **Primary Blue**; empty cells show placeholder dashes.
*   **Row Alert:** Highlights amber when any score is missing.

### Results Screen (Student)
```text
┌─────────────────────────────────────┐
│  ← My Results                       │
│  Term 2 · 2024/2025                 │
│                                     │
│  Overall: 74.2 avg · Position 3/28  │
│  ████████████░░  74%                │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  Mathematics          82  A  │   │
│  │  CA1: 18  CA2: 17  Exam: 47  │   │
│  └──────────────────────────────┘   │
│  ┌──────────────────────────────┐   │
│  │  English Language     71  B  │   │
│  │  CA1: 15  CA2: 14  Exam: 42  │   │
│  └──────────────────────────────┘   │
└─────────────────────────────────────┘
```
*   Progress bar at top uses the role accent color.
*   **Grade Badges:** Color-coded: A=green, B=blue, C=amber, D/E=orange, F=red.
*   **CA Breakdown:** Shown in smaller text below the subject name.

### Timetable Screen
```text
┌─────────────────────────────────────┐
│  ← Timetable                        │
│  [Mon] [Tue] [Wed] [Thu] [Fri]      │  ← day tabs, pill style
│                                     │
│  8:00   Mathematics                 │
│         Room 12 · Mr. Hassan        │
│  ─────────────────────────────────  │
│  9:00   ☕ Break                    │  ← break shown differently
│  ─────────────────────────────────  │
│  9:30   English Language            │
│         Room 7 · Mrs. Aisha         │
└─────────────────────────────────────┘
```
*   Day tabs scroll horizontally; today's tab is auto-selected.
*   Current period is highlighted with a soft left blue border.
*   Break rows use a lighter background and a coffee/rest icon.

### Messaging Screen
```text
┌─────────────────────────────────────┐
│  ← Messages                         │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  [photo]  Mr. Hassan         │   │
│  │  "Amina did well this week"  │   │
│  │                    2:30 PM ✓ │   │
│  └──────────────────────────────┘   │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  [photo]  Mrs. Aisha         │   │
│  │  "Please see homework note"  │   │
│  │                  Yesterday   │   │
│  └──────────────────────────────┘   │
└─────────────────────────────────────┘
```
*   **Sent Messages:** Primary Blue bubble, right-aligned, white text.
*   **Received Messages:** White bubble, left-aligned, dark text, soft shadow.
*   Timestamps in tiny grey text below bubbles.
*   **Attachments:** Displayed as a document card inside the bubble.

---

## 9. Bottom Nav Per Role
*   **Student:** Home · Results · Homework · Profile
*   **Parent:** Home · Children · Messages · Profile
*   **Teacher:** Home · Attendance · Scores · Profile
*   **Admin:** Home · Students · Announcements · Profile

---

## 10. Sync Indicator — Detailed Behavior
The `[●]` dot lives in the top-right of every screen's app bar, to the left of the notification bell.

| State | Indicator Color | Hex | Animation |
| :--- | :--- | :--- | :--- |
| **All Synced** | Green | `#22C55E` | Static |
| **Pending Queue** | Red | `#EF4444` | Static |
| **Syncing Now** | Amber | `#F59E0B` | Slow pulse (800ms) |
| **No Internet** | Red | `#EF4444` | Static |

*Tapping the dot opens a bottom sheet:*
```text
┌─────────────────────────────────────┐
│  ▬                                  │
│  Sync Status                        │
│                                     │
│  🔴  3 changes waiting to sync      │
│                                     │
│  • Attendance · JSS 2A · Today      │
│  • Score · Bilal · Mathematics      │
│  • Score · Amina · Physics          │
│                                     │
│  Last synced: 14 min ago            │
│                                     │
│  [      Retry Sync Now      ]       │
└─────────────────────────────────────┘
```
*When fully synced:*
```text
│  ✅  Everything is up to date       │
│  Last synced: just now              │
```

---

## 11. Micro-interactions & Animations
*   **Page Transitions:** Slide from right (forward), slide to right (back). Duration: 250ms with an ease-out curve.
*   **Card Tap:** Scale down to `0.97` on press, spring back on release.
*   **Button Press:** Slight darken + scale to `0.98`.
*   **Skeleton Loaders:** Shimmer effect (left-to-right light sweep) on all cards while loading. Never show empty screens.
*   **Success Actions:** A small green checkmark animates in (scale from `0` → `1.1` → `1.0`) after saving attendance or scores.
*   **Sync Dot Transition:** Color changes fade over 400ms.
*   **Offline Banner:** Slides down (200ms ease-in), slides up (200ms ease-out).
*   **Bottom Sheets:** Slide up with spring physics, feature a drag handle at top, and are dismissible by swiping.
*   **Pull to Refresh:** Custom indicator using the school's primary blue color.

---

## 12. Empty States
Every list screen must feature a thoughtful empty state — never a blank white screen.
```text
┌─────────────────────────────────────┐
│                                     │
│         [Soft illustration]         │
│                                     │
│      No homework assigned yet       │
│   Your teacher hasn't posted any    │
│        homework this term.          │
│                                     │
└─────────────────────────────────────┘
```
*   **Illustration:** Simple, flat, school-themed SVG (books, pencil, calendar).
*   **Title:** 16sp SemiBold, Primary Text.
*   **Subtitle:** 13sp Regular, Secondary Text.
*   No buttons unless there is an active step the user can take.

---

## 13. Notifications Screen
```text
┌─────────────────────────────────────┐
│  ← Notifications          Mark all  │
│                                     │
│  Today                              │
│  ┌──────────────────────────────┐   │
│  │  🔵  New homework posted     │   │  ← unread: blue left border
│  │  Mathematics · Due Friday    │   │
│  │  2 hours ago                 │   │
│  └──────────────────────────────┘   │
│                                     │
│  Yesterday                          │
│  ┌──────────────────────────────┐   │
│  │  ✅  Results published       │   │  ← read: no border
│  │  Term 2 scores are out       │   │
│  │  Yesterday 3:00 PM           │   │
│  └──────────────────────────────┘   │
└─────────────────────────────────────┘
```
*   Grouped by date (Today, Yesterday, Older by date).
*   **Unread Notification:** White card with a 3px left blue border.
*   **Read Notification:** Slightly grey background `#F8FAFC`, no border.
*   Swipe left to dismiss.

---

## 14. Profile Screen
```text
┌─────────────────────────────────────┐
│                                     │
│         [Profile Photo 80px]        │
│           Amina Hassan              │
│        Student · JSS 2A             │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  Admission No.  STU20240001  │   │
│  │  Class          JSS 2A       │   │
│  │  Section        B            │   │
│  │  Term           2            │   │
│  └──────────────────────────────┘   │
│                                     │
│  ┌──────────────────────────────┐   │
│  │  🔔  Notifications     On >  │   │
│  │  🌐  Open in Browser      >  │   │
│  │  🚪  Sign Out             >  │   │
│  └──────────────────────────────┘   │
└─────────────────────────────────────┘
```
*   Profile photo in a circle with a soft ring border (role accent color).
*   Info section grouped in a single list card.
*   **"Open in Browser"** deep-links to the web portal for features not implemented in the native app.
*   **"Sign Out"** triggers a confirmation bottom sheet.

---

## 15. Flutter Package Recommendations

| Purpose | Package | Description |
| :--- | :--- | :--- |
| **Local database** | `drift` | Type-safe SQLite database |
| **HTTP client** | `dio` | Powerful HTTP client with interceptors and cancel tokens |
| **Secure token storage** | `flutter_secure_storage` | Keychain/Keystore wrapper |
| **Connectivity** | `connectivity_plus` | Listen to cellular/wifi network state changes |
| **Background sync** | `workmanager` | Headless execution of background tasks |
| **Push notifications** | `firebase_messaging` | Firebase Cloud Messaging client |
| **State management** | `flutter_riverpod` | Type-safe state management |
| **Navigation** | `go_router` | Declarative routing system |
| **PDF viewer** | `flutter_pdfview` | Native PDF renderer |
| **File picker** | `file_picker` | File selector |
| **Animations** | `flutter_animate` | High-performance animation sequences |
| **Shimmer loading** | `shimmer` | Content skeleton loaders |
| **Google Fonts** | `google_fonts` | Load Inter dynamically |
| **Bottom sheets** | `modal_bottom_sheet` | Interactive bottom sheets |
| **Image caching** | `cached_network_image` | Image loader with automatic cache |

---

## 16. What Stays Web-Only

| Feature | Reason for Web-Only |
| :--- | :--- |
| **Report card PDF generation** | Server-side `dompdf` + `GD` dependencies, not suitable for mobile |
| **Certificate design** | Template designer needs desktop resolution |
| **CBT exam creation** | Complex question builder and rich-text editing flow |
| **Fee structure setup** | Heavy configuration workflow, admin-specific task |
| **Class/Section/Subject config** | Structural database configuration, requires careful UX |
| **Bulk student import** | Large Excel/CSV file parsing, desktop-oriented |
| **Timetable builder** | Drag-and-drop schedule grid requires desktop screen |
| **Analytics dashboards** | Intricate charts and custom report exports |
| **Promotions (bulk class move)** | Irreversible, high-risk administrative action |
| **Marketplace / Plugins** | Subadmin marketplace billing and licensing checks |
| **Audit logs** | Detailed table-heavy admin investigation tool |
| **WhatsApp bot config** | Technical system settings |
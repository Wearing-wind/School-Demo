# 🎓 Apex Model Secondary School - Digital Web Portal & Examination Subsystem

An enterprise-grade, high-performance web platform designed for **Apex Model Secondary School** (Itahari-5, Sunsari, Koshi Province, Nepal). This portal combines an interactive institutional school website with a completely decoupled, high-speed **Examination & Result Management Subsystem** built to handle multi-batch national grading (Nepal National Grading System - GPA 4.0 scale).

---

## 🌟 Key Highlights & Architecture

- **Decoupled Result Subsystem**: The examination portal resides exclusively inside the `/results/` directory with its own lightweight **SQLite database** (`results/database/results.sqlite`), leaving the main institutional website completely untouched and lightweight.
- **Academic Batch vs. Class Differentiation**: Full distinction between **Academic Year Batches** (e.g. `2083 B.S.`, `2082 B.S.`), **Examination Sessions** (e.g. `Second Mid-Terminal Examination 2083`), and **Classes/Grades** (`Grade: 1` through `Grade: 10`).
- **Nepal National Grading Algorithm**: Automatic computation of Subject Grade Points (GP) and overall Grade Point Average (GPA 4.0 scale: A+, A, B+, B, C+, C, D, NG) following National Curriculum Development Center (CDC) guidelines.
- **Interactive Storage Analytics**: Controller dashboard features an interactive SQLite storage inspector showing exact KB footprint and percentage distribution broken down by Academic Year Batch, Exam Session, and Grade.
- **Modern UI & Motion Dynamics**: Styled with custom Tailwind CSS palette (Navy Blue `#0f172a`, Crimson `#991b1b`, Gold `#f59e0b`), glassmorphism, responsive mobile cards, and Framer Motion-style `AnimatedBackground` sliding tab dock.
- **PWA Ready**: Offline-capable Progressive Web Application (PWA) with Service Worker (`results/sw.js`) scoped to `/results/`.

---

## 📁 Repository Structure

```text
School-Demo/
├── index.php                      # Main School Homepage (Hero, Pamphlet Modal, Stats, News)
├── about.php                      # Institutional Overview & Leadership Profiles
├── events.php                     # School Events, Competitions & Science Fair Gallery
├── notices.php                    # Official Announcements & Downloadable Circulars
├── contact.php                    # Contact Details, Inquiry Form & Google Map Embed
├── assets/
│   ├── css/
│   │   └── style.css              # Glassmorphism, animations & mobile dock styles
│   ├── js/
│   │   └── main.js                # Mobile navigation, dock tab math & interactive UI
│   └── images/                    # School logo, principal portraits, facility visual assets
├── results/                       # 🔒 ISOLATED EXAMINATION & RESULT SUBSYSTEM
│   ├── index.php                  # Public Student Result Verification & Marksheet Portal
│   ├── api_search.php             # JSON API Endpoint for AJAX result verification
│   ├── manifest.json              # Result Portal PWA Manifest
│   ├── sw.js                      # Result Portal Service Worker
│   ├── database/
│   │   ├── db_init.php            # SQLite PDO Connection & Table Schema Auto-Initializer
│   │   ├── seed_dummy_data.php    # Multi-Batch Bulk Student Result Generator script
│   │   └── results.sqlite         # SQLite Database File (Indexed for fast lookup)
│   └── management/
│       ├── dashboard.php          # Examination Controller Analytics Dashboard & Storage Inspector
│       └── upload_csv.php         # Bulk CSV Marksheet Data Importer with Auto-GPA calculator
└── README.md                      # Comprehensive Project Documentation
```

---

## 🔥 Features Breakdown

### 1. Main Institutional Website
- **Home Page (`index.php`)**:
  - Hero Section with school tagline, CTA buttons, and quick navigation.
  - Interactive **School Pamphlet & Prospectus Modal** with quick view & download options.
  - Key Statistics Counters (Students, Teachers, Pass Rate, Estd 1994 B.S.).
  - Academic Programs (Playgroup to Grade 10, Science & Management streams).
  - Campus Facilities Showcase (Computer Lab, Robotics Hub, Science Labs, Library, Transport).
  - Dynamic News & Event Cards.
  - Interactive Mobile Bottom Dock with sliding active tab indicator (`AnimatedBackground`).
- **About Us (`about.php`)**:
  - Leadership Messages from Principal, Vice Principal, and Academic Director.
  - Core Values (Excellence, Integrity, Innovation, Discipline).
  - Visual Facility Tour with high-resolution imagery.
- **Events & News (`events.php`)**:
  - Filterable events catalog (Academic, Sports, Cultural, Science & Tech).
  - Detailed event metadata (Date, Location, Organizer, Description).
- **Notice Board (`notices.php`)**:
  - Categorized notices (Urgent, Exam, Routine, Fee Structure).
  - Downloadable PDF notice attachments and instant search filter.
- **Contact Us (`contact.php`)**:
  - Interactive inquiry submission form with client validation.
  - Embedded Google Map of Itahari-5 campus.
  - Direct contact links (Phone, Email, Social Media).

---

### 2. Examination & Result Subsystem (`/results/`)

#### 🎓 Public Result Verification (`/results/index.php`)
- **Multi-Filter Verification**: Search by **Academic Batch** (e.g. `2083 B.S.`), **Exam Session**, **Grade / Class**, and **Symbol Number** or **Student Name**.
- **Official Digital Grade-Sheet**:
  - Verified institutional header with school seal and Controller signature.
  - Student details (Name, Class, Symbol No, Roll No, Section, Attendance, Academic Batch).
  - Itemized Subject Marks Table with Full Marks, Final Letter Grade, and Grade Point (GP).
  - Automatic GPA calculation with official Nepal CDC Grading Rubric reference table.
  - One-click **Download / Print Marksheet** button (`window.print()`).

#### 📊 Controller Dashboard (`/results/management/dashboard.php`)
- **Overview Cards**:
  - Total Academic Batches in system.
  - Total Examination Sessions recorded.
  - Total Classes/Grades covered.
  - SQLite Database Storage Size.
- **Clickable Storage Inspector Modal**:
  - Displays real-time database storage breakdown in **KB** and **Percentage Share**.
  - Hierarchical grouping: Academic Batch → Exam Session → Grade / Class.
  - Interactive progress bars indicating heavy data batches.
- **Data Table & Responsive Layout**:
  - Fast search by Student Name or Symbol Number.
  - Academic Batch tag display for each record.
  - Automatic `@media (max-width: 640px)` CSS transformation converting table rows into mobile card stacks.

#### 📥 Bulk CSV Importer (`/results/management/upload_csv.php`)
- Batch import student records via standard CSV files.
- Select target **Academic Year Batch** (`2083 B.S.`, `2082 B.S.`, etc.).
- Automated parsing of subject marks, automatic grade assignment, and PDO transaction protection (atomic commit/rollback).

#### ⚡ Data Seeder (`/results/database/seed_dummy_data.php`)
- Command-line generator seeding **400+ realistic Nepali student results**.
- Generates data for **Grades 1 to 10** across **Academic Batches 2083 B.S. and 2082 B.S.**.
- Differentiates Primary (5 core subjects) and Secondary (7 subjects including Compulsory Math, Optional Math, Science & Tech, Computer Science) curricula.

---

## 🇳🇵 Nepal National Grading System Scale (GPA 4.0)

The subsystem computes grades using the official National Curriculum Development Center standards:

| Percentage Interval | Letter Grade | Grade Point (GP) | Performance Description |
| :--- | :---: | :---: | :--- |
| **90% - 100%** | `A+` | **4.0** | Outstanding |
| **80% - 89%** | `A` | **3.6** | Excellent |
| **70% - 79%** | `B+` | **3.2** | Very Good |
| **60% - 69%** | `B` | **2.8** | Good |
| **50% - 59%** | `C+` | **2.4** | Satisfactory |
| **40% - 49%** | `C` | **2.0** | Acceptable |
| **35% - 39%** | `D` | **1.6** | Basic |
| **Below 35%** | `NG` | **0.0** | Not Graded |

---

## ⚙️ Requirements & Installation Setup

### Prerequisites
- **Web Server**: Apache (XAMPP / WAMP / MAMP / Linux Apache)
- **PHP**: PHP 8.0 or higher
- **Extensions Required**: `pdo`, `pdo_sqlite`, `sqlite3`, `json`

### Step-by-Step Setup Guide

1. **Clone or Copy Repository into Web Root**:
   Place the project directory inside your web server root (e.g. `/Applications/XAMPP/xamppfiles/htdocs/School-Demo` or `C:\xampp\htdocs\School-Demo`).

2. **Verify SQLite Database Permissions**:
   Ensure write permissions on `results/database/` folder so PHP PDO can create and update `results.sqlite`.

3. **Initialize Database Schema**:
   Access the result search page or run the initialization via CLI:
   ```bash
   php results/database/db_init.php
   ```

4. **Seed Multi-Batch Dummy Data**:
   To populate the system with 400+ sample student marksheets across Grades 1–10:
   ```bash
   php results/database/seed_dummy_data.php
   ```

5. **Launch in Web Browser**:
   - **Main School Website**: `http://localhost/School-Demo/`
   - **Result Verification Portal**: `http://localhost/School-Demo/results/`
   - **Controller Dashboard**: `http://localhost/School-Demo/results/management/dashboard.php`
   - **CSV Import Tool**: `http://localhost/School-Demo/results/management/upload_csv.php`

---

## 📱 Mobile Dock & Motion Physics

The site features an animated mobile navigation dock powered by `assets/js/main.js` and `assets/css/style.css`:
- Computes exact element bounds with `getBoundingClientRect()`.
- Smooth sliding background tab highlight (`.dock-tab-bg`) with CSS spring transition (`cubic-bezier(0.34, 1.56, 0.64, 1)`).
- Works dynamically across screen resizes and active page routes.

---

## 📄 License & Credits

- **Institution**: Apex Model Secondary School, Itahari-5, Sunsari, Nepal.
- **Design System**: Tailwind CSS CDN, Google Fonts (Inter, Outfit), Custom Glassmorphism.
- **Engine**: PHP 8 + SQLite3 (PDO).

---
*Developed for Apex Model Secondary School - Result & Examination Subsystem.*

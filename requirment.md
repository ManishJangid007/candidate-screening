# Candidate Screening Tool — Requirement Document

## 1. Objective

Build a lightweight internal tool to manage candidate screening after importing an Excel sheet with initial scores. Built with **Laravel Blade** using **shadcn/ui-inspired theming** (Tailwind CSS with shadcn design tokens, color palette, border-radius, shadows, and component styling patterns).

---

## 2. Tech Stack

| Layer       | Choice                                                     |
|-------------|-------------------------------------------------------------|
| Backend     | Laravel (PHP)                                               |
| Frontend    | Laravel Blade + Tailwind CSS (shadcn/ui theme)              |
| Database    | MySQL                                                       |
| Auth        | Laravel built-in auth with role-based access                |
| Excel       | Laravel Excel / PhpSpreadsheet                              |

### shadcn/ui Theming in Blade

- Use Tailwind CSS with shadcn design tokens (`--radius`, `--primary`, `--secondary`, `--muted`, `--accent`, `--destructive`, `--border`, `--input`, `--ring`, etc.)
- Replicate shadcn component styles: Card, Button, Input, Select, Table, Badge, Dialog, Dropdown Menu, Pagination
- Dark/light mode support via CSS variables
- Consistent `rounded-md`, `shadow-sm`, `border` patterns across all components
- Font: Inter (or system sans-serif stack)

---

## 3. Authentication & Role-Based Access

### 3.1 Roles

| Role          | Description                                                        |
|---------------|--------------------------------------------------------------------|
| **Admin**     | Full access — upload Excel, manage candidates, assign interviewers, view all data |
| **Interviewer** | Can only view assigned candidates, submit evaluations, and add remarks |

### 3.2 Login Flow

- Individual login per user (email + password)
- Laravel built-in authentication (session-based)
- After login, redirect based on role:
  - **Admin** → Candidate List Dashboard
  - **Interviewer** → Filtered list showing only their assigned candidates

### 3.3 Permissions Matrix

| Feature                        | Admin | Interviewer |
|--------------------------------|:-----:|:-----------:|
| Login                          |  Yes  |     Yes     |
| Upload Excel                   |  Yes  |     No      |
| View all candidates            |  Yes  |     No      |
| View assigned candidates only  |  Yes  |     Yes     |
| Assign interviewer             |  Yes  |     No      |
| Submit evaluation / remarks    |  Yes  |     Yes     |
| Advance candidate round        |  Yes  |     No      |
| Manage interviewer list        |  Yes  |     No      |

### 3.4 Login Screen

```
+----------------------------------------------------------+
|              CANDIDATE SCREENING TOOL                     |
|                                                           |
|  +----------------------------------------------------+  |
|  |                   Sign In                           |  |
|  |                                                    |  |
|  |  Email       [______________________________]      |  |
|  |  Password    [______________________________]      |  |
|  |                                                    |  |
|  |  [ Sign In ]                                       |  |
|  |                                                    |  |
|  +----------------------------------------------------+  |
|           shadcn Card component styling                   |
+----------------------------------------------------------+
```

---

## 4. Excel Upload

### 4.1 Upload Rules

| Column        | Required | Notes                              |
|---------------|:--------:|------------------------------------|
| ID            |   Yes    | Unique candidate identifier        |
| Student Name  |   Yes    | Full name of the candidate         |
| Aptitude Score|   No     | Stored as NULL if blank             |
| Test Score    |   No     | Stored as NULL if blank             |
| Video Score   |   No     | Stored as NULL if blank             |

### 4.2 Upload Behavior

1. Admin clicks "Upload Excel" button
2. System validates required columns (`ID`, `Student Name`)
3. On success: import candidate records, redirect to candidate list
4. On failure: show inline validation errors (missing columns, duplicate IDs, etc.)
5. Duplicate ID handling: update existing record (upsert)

---

## 5. Candidate List Screen

### 5.1 Table Columns

| Column            | Description                                      |
|-------------------|--------------------------------------------------|
| ID                | Candidate ID from Excel                          |
| Student Name      | Clickable — opens candidate detail page           |
| Aptitude Score    | From Excel (may be blank)                        |
| Test Score        | From Excel (may be blank)                        |
| Video Score       | From Excel (may be blank)                        |
| Current Round     | Round 1 / Round 2 / Round 3 / Round 4            |
| Round Status      | Pending / Cleared / Not Cleared (color-coded)    |
| Final Result      | In Progress / Rejected / Final Selected           |
| Interviewer       | Assigned interviewer name (Admin can change)      |
| Last Updated      | Timestamp of last evaluation                      |
| Action            | View button                                       |

### 5.2 Status Color Coding (shadcn Badge component)

| Status        | Badge Style                            |
|---------------|----------------------------------------|
| Pending       | `bg-yellow-100 text-yellow-800` (Warning variant)  |
| Cleared       | `bg-green-100 text-green-800` (Success variant)    |
| Not Cleared   | `bg-red-100 text-red-800` (Destructive variant)    |

### 5.3 Final Result Color Coding

| Result          | Badge Style                            |
|-----------------|----------------------------------------|
| In Progress     | `bg-blue-100 text-blue-800`            |
| Rejected        | `bg-red-100 text-red-800`              |
| Final Selected  | `bg-green-100 text-green-800`          |

### 5.4 Filters

- Student Name (text search)
- Aptitude Score (min/max range)
- Test Score (min/max range)
- Video Score (min/max range)
- Current Interview Round (dropdown)
- Round Status (dropdown)
- Interviewer (dropdown)
- Final Result (dropdown)

### 5.5 Actions

- **Upload Excel** (Admin only) — opens file upload dialog
- **Filter / Reset** — apply or clear filters
- **Assign Interviewer** (Admin only) — inline dropdown on list
- **View** — click student name or View button to open detail page

### 5.6 Pagination

- Server-side pagination (15 records per page)
- shadcn-style pagination component

### 5.7 Screen Layout

```
+---------------------------------------------------------------------------------------------------------------+
| Candidate Screening Dashboard                                              [User Name] [Role Badge] [Logout]  |
+---------------------------------------------------------------------------------------------------------------+
| [Upload Excel]   Search: [__________]   Round: [Select v]   Status: [Select v]   Interviewer: [Select v]      |
|                  Aptitude: [Min]-[Max]   Test: [Min]-[Max]   Video: [Min]-[Max]   Final: [Select v]            |
|                  [Apply Filters]  [Reset]                                                                      |
+---------------------------------------------------------------------------------------------------------------+
| ID  | Student Name    | Aptitude | Test | Video | Round   | Status        | Final Result   | Interviewer | Act |
|-----|-----------------|----------|------|-------|---------|---------------|----------------|-------------|-----|
| 101 | Ayaan Shaikh    | 78       | 81   | 72    | Round 1 | [Pending]     | In Progress    | [Select v]  | View|
| 102 | Meera Joshi     | 88       | 91   | 85    | Round 2 | [Cleared]     | In Progress    | Nitesh      | View|
| 103 | Rohan Patil     | 69       | 74   | 80    | Round 1 | [Not Cleared] | Rejected       | Priya       | View|
| 104 | Sana Khan       | 92       | 89   | 90    | Round 4 | [Cleared]     | Final Selected | Amit        | View|
+---------------------------------------------------------------------------------------------------------------+
| < Prev   1   2   3   Next >                                                                                   |
+---------------------------------------------------------------------------------------------------------------+
```

---

## 6. Candidate Detail / Evaluation Form

### 6.1 Read-Only Fields

- Candidate ID
- Student Name
- Aptitude Score
- Test Score
- Video Score
- Current Round

### 6.2 Editable Fields

| Field           | Type       | Notes                                          |
|-----------------|------------|------------------------------------------------|
| Interviewer     | Dropdown   | Select from hard-coded interviewer list         |
| Round Result    | Radio      | Cleared / Not Cleared                           |
| Remarks         | Textarea   | Free-text comments                              |

### 6.3 Actions

- **Save** — save evaluation and stay on page
- **Save & Next Candidate** — save and navigate to next candidate in list
- **Back to List** — return to candidate list

### 6.4 Interview History

Below the evaluation form, display a read-only table of all past round evaluations:

| Round   | Interviewer | Result      | Remarks              | Date       |
|---------|-------------|-------------|----------------------|------------|
| Round 1 | Priya       | Cleared     | Good communication   | 2026-03-10 |
| Round 2 | Nitesh      | Cleared     | Strong technical     | 2026-03-12 |
| Round 3 | —           | Pending     | —                    | —          |

### 6.5 Screen Layout

```
+----------------------------------------------------------------------------------+
|  Candidate Detail                                    [Back to List]              |
+----------------------------------------------------------------------------------+
|                                                                                  |
|  +--Card----------------------------------------------------------------------+ |
|  | Candidate ID   : 101                                                        | |
|  | Student Name   : Ayaan Shaikh                                               | |
|  | Aptitude Score : 78                                                          | |
|  | Test Score     : 81                                                          | |
|  | Video Score    : 72                                                          | |
|  | Current Round  : Round 1                                                     | |
|  +-----------------------------------------------------------------------------+ |
|                                                                                  |
|  +--Card: Evaluation----------------------------------------------------------+  |
|  | Interviewer  : [Select Interviewer v]                                       |  |
|  | Round Result : ( ) Cleared   ( ) Not Cleared                                |  |
|  | Remarks      : [____________________________________________________________]|  |
|  |               [____________________________________________________________]|  |
|  +-----------------------------------------------------------------------------+ |
|                                                                                  |
|  +--Card: Interview History---------------------------------------------------+  |
|  | Round   | Interviewer | Result  | Remarks             | Date               |  |
|  |---------|-------------|---------|---------------------|--------------------|  |
|  | Round 1 | Priya       | Cleared | Good communication  | 2026-03-10         |  |
|  +---------------------------------------------------------------------------- + |
|                                                                                  |
|  [Save]   [Save & Next Candidate]   [Back to List]                               |
+----------------------------------------------------------------------------------+
```

---

## 7. Interview Round Progression Logic

### 7.1 Round Structure

4 rounds total: Round 1, Round 2, Round 3, Round 4. Each candidate is in one active round at a time. Initial state after Excel upload: **Round 1, Pending**.

### 7.2 On "Cleared"

| Current Round | Action                                          |
|---------------|-------------------------------------------------|
| Round 1       | Move candidate to Round 2, status = Pending     |
| Round 2       | Move candidate to Round 3, status = Pending     |
| Round 3       | Move candidate to Round 4, status = Pending     |
| Round 4       | Stay at Round 4, marked **Final Cleared**        |

### 7.3 On "Not Cleared"

| Current Round | Action                                              |
|---------------|-----------------------------------------------------|
| Round 1–4     | Stay in same round, marked **Not Cleared**           |

Candidate does not progress further. Record is retained for reference.

### 7.4 Final Result Derivation

| Condition                      | Final Result       |
|--------------------------------|--------------------|
| Not Cleared in any round       | **Rejected**       |
| Cleared in Round 4             | **Final Selected** |
| All other cases                | **In Progress**    |

---

## 8. Interviewer Master

- Hard-coded list in a config file or seeder (no admin form needed)
- Used in: interviewer dropdown, list screen assignment, list filters
- Initial list (~10 interviewers):

```php
// config/interviewers.php
return [
    'Priya Sharma',
    'Nitesh Gupta',
    'Amit Desai',
    'Sneha Kulkarni',
    'Rahul Mehta',
    'Pooja Nair',
    'Vikram Singh',
    'Anjali Verma',
    'Karan Joshi',
    'Divya Iyer',
];
```

---

## 9. Database Schema (Suggested)

### users

| Column     | Type         | Notes                           |
|------------|--------------|----------------------------------|
| id         | bigint (PK)  | Auto-increment                   |
| name       | varchar(255) |                                  |
| email      | varchar(255) | Unique                           |
| password   | varchar(255) | Hashed                           |
| role       | enum         | `admin`, `interviewer`           |
| timestamps |              |                                  |

### candidates

| Column         | Type          | Notes                          |
|----------------|---------------|--------------------------------|
| id             | bigint (PK)   | Auto-increment                 |
| candidate_id   | varchar(50)   | From Excel, unique             |
| student_name   | varchar(255)  |                                |
| aptitude_score | int (nullable)|                                |
| test_score     | int (nullable)|                                |
| video_score    | int (nullable)|                                |
| current_round  | tinyint       | 1–4, default 1                 |
| round_status   | enum          | `pending`, `cleared`, `not_cleared` |
| final_result   | enum          | `in_progress`, `rejected`, `final_selected` |
| interviewer    | varchar(255)  | Nullable                       |
| timestamps     |               |                                |

### interview_rounds

| Column       | Type          | Notes                          |
|--------------|---------------|--------------------------------|
| id           | bigint (PK)   | Auto-increment                 |
| candidate_id | bigint (FK)   | References candidates.id       |
| round_number | tinyint       | 1–4                            |
| interviewer  | varchar(255)  |                                |
| result       | enum          | `cleared`, `not_cleared`       |
| remarks      | text          | Nullable                       |
| evaluated_by | bigint (FK)   | References users.id            |
| timestamps   |               |                                |

---

## 10. Key User Flows

### Flow 1: Admin uploads Excel

```
Login (Admin) → Dashboard → Click "Upload Excel" → Select file
→ System validates → Import records → Redirect to candidate list
```

### Flow 2: Admin assigns interviewer

```
Dashboard → Find candidate → Select interviewer from dropdown → Auto-saved
```

### Flow 3: Interviewer evaluates candidate

```
Login (Interviewer) → See assigned candidates → Click student name
→ Fill evaluation (result + remarks) → Save → Auto-progression
→ Save & Next or Back to List
```

### Flow 4: Candidate clears all rounds

```
Round 1 (Cleared) → Round 2 (Cleared) → Round 3 (Cleared) → Round 4 (Cleared)
→ Final Result = "Final Selected"
```

### Flow 5: Candidate rejected

```
Round 1 (Cleared) → Round 2 (Not Cleared) → Final Result = "Rejected"
→ Candidate stays at Round 2, no further progression
```

---

## 11. UI/UX Guidelines (shadcn/ui in Blade)

| Element         | shadcn Pattern                                    |
|-----------------|---------------------------------------------------|
| Layout          | Max-width container, centered, `bg-background`    |
| Cards           | `rounded-lg border bg-card shadow-sm`             |
| Buttons         | Primary: `bg-primary text-primary-foreground`     |
| Inputs          | `border-input bg-background rounded-md`           |
| Tables          | Striped rows, `divide-y`, hover state             |
| Badges          | Rounded-full, color variants per status           |
| Dropdowns       | `rounded-md border shadow-md`                     |
| Pagination      | Button group with active state                    |
| Toast/Alerts    | Success/error feedback after actions              |
| Navigation      | Top bar with user info, role badge, logout        |

---

## 12. Out of Scope (Phase 1)

- Email notifications
- Candidate self-registration
- Report generation / export
- Multi-tenant support
- API endpoints (internal tool only)
- Interviewer form/CRUD (hard-coded list)

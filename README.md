# Candidate Screening Tool

A lightweight internal tool to manage candidate screening after importing an Excel sheet with initial scores. Built with Laravel, Blade, and Tailwind CSS (shadcn/ui-inspired theming).

## Features

- **Role-Based Access** — Admin and Interviewer roles with permission-based UI
- **Excel Upload** — Import candidates via `.xlsx`, `.xls`, or `.csv` files
- **Candidate Dashboard** — Filterable, paginated list with AJAX-powered instant filtering
- **Evaluation Form** — Mark candidates as Cleared / Not Cleared with remarks
- **Round Progression** — 4 interview rounds with automatic advancement on clearance
- **Interview History** — Full audit trail of all evaluations per candidate
- **Status Tracking** — Color-coded badges for round status and final result
- **Interviewer Assignment** — Admin can assign interviewers inline from the dashboard
- **Revert Action** — Admin can revert rejected/selected candidates back to pending

## Tech Stack

| Layer    | Technology                          |
|----------|-------------------------------------|
| Backend  | Laravel (PHP)                       |
| Frontend | Blade + Tailwind CSS (shadcn theme) |
| Database | MySQL                               |
| Auth     | Laravel built-in (session-based)    |
| Excel    | Maatwebsite/Laravel-Excel           |

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

## Installation

```bash
# Clone the repo
git clone https://github.com/ManishJangid007/candidate-screening.git
cd candidate-screening

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=screening_tool
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# Run migrations and seed
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start the server
php artisan serve
```

The app will be available at `http://localhost:8000`

## Default Login Credentials

| User           | Email                          | Password   | Role        |
|----------------|--------------------------------|------------|-------------|
| Admin          | admin@screening.com            | password   | Admin       |
| Priya Sharma   | priya.sharma@screening.com     | password   | Interviewer |
| Nitesh Gupta   | nitesh.gupta@screening.com     | password   | Interviewer |
| Amit Desai     | amit.desai@screening.com       | password   | Interviewer |
| Sneha Kulkarni | sneha.kulkarni@screening.com   | password   | Interviewer |
| Rahul Mehta    | rahul.mehta@screening.com      | password   | Interviewer |
| Pooja Nair     | pooja.nair@screening.com       | password   | Interviewer |
| Vikram Singh   | vikram.singh@screening.com     | password   | Interviewer |
| Anjali Verma   | anjali.verma@screening.com     | password   | Interviewer |
| Karan Joshi    | karan.joshi@screening.com      | password   | Interviewer |
| Divya Iyer     | divya.iyer@screening.com       | password   | Interviewer |

## Excel Upload Format

The uploaded Excel file must have these columns:

| Column         | Required |
|----------------|----------|
| ID             | Yes      |
| Student Name   | Yes      |
| Aptitude Score | No       |
| Test Score     | No       |
| Video Score    | No       |

## Permissions

| Feature                    | Admin | Interviewer |
|----------------------------|:-----:|:-----------:|
| Upload Excel               |  Yes  |     No      |
| View all candidates        |  Yes  |     No      |
| View assigned candidates   |  Yes  |     Yes     |
| Assign interviewer         |  Yes  |     No      |
| Submit evaluation          |  Yes  |     Yes     |
| Revert candidate status    |  Yes  |     No      |

## Interview Round Progression

- **Cleared (Round 1-3)** — Candidate advances to next round, status resets to Pending
- **Cleared (Round 4)** — Final Result becomes "Final Selected"
- **Not Cleared (any round)** — Final Result becomes "Rejected", no further progression
- **Revert** — Admin can reset rejected/selected candidates back to Pending

## Project Structure

```
app/
  Http/Controllers/
    AuthController.php        # Login/logout
    CandidateController.php   # List, detail, evaluate, assign, revert
    ExcelController.php       # Excel upload
  Http/Middleware/
    AdminMiddleware.php       # Admin role gate
  Imports/
    CandidatesImport.php      # Excel import logic
  Models/
    User.php                  # User with role (admin/interviewer)
    Candidate.php             # Candidate with scores, round, status
    InterviewRound.php        # Evaluation history per round
config/
  interviewers.php            # Hard-coded interviewer list
database/
  migrations/                 # Users, candidates, interview_rounds
  seeders/                    # Default users and sample candidates
resources/
  css/app.css                 # shadcn/ui design tokens and components
  views/
    auth/login.blade.php      # Login page
    candidates/index.blade.php # Dashboard with AJAX table
    candidates/show.blade.php  # Candidate detail + evaluation
    layouts/app.blade.php      # Main layout
routes/
  web.php                     # All routes with auth and admin middleware
```

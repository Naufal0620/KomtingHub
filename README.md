# KomtingHub

A website-based system that helps a **komting** (class representative) manage their class. Built with **Laravel 12**, **DDEV**, **Tailwind CSS**, **Alpine.js**, and **Vite**.

## Features

- **Authentication & role-based access** — email-verified login and three distinct roles enforced via middleware and policies:
  - `admin` — creates class rooms and manages komting/student accounts;
  - `komting` — manages the rooms assigned to them (members, subjects, groups, assignments);
  - `student` — enrolled members who join groups and submit assignments.
- **Class & subject management** — an admin assigns a class room to a komting; each class room has many subjects, and each subject owns its own groups and assignments.
- **Membership** — the komting enrolls students into a class room and into specific subjects. Each student belongs to **one class room** and can join **multiple subjects**.
- **Group management** — groups are formed per subject in one of two modes:
  - **self-selection** (`select`) — students freely join/leave a group until the komting locks them;
  - **auto-randomized** (`random`) — the komting runs a transparent, auditable shuffle.
- **Transparent randomization** — the shuffle is fully auditable:
  - a recorded seed, algorithm, version, timestamp, and a result snapshot per run;
  - a SHA-256 hash commitment of the result is computed and stored;
  - a public verification page (`/verify/{run}`) lets anyone re-compute the result from the published seed and confirm the hash matches.
- **Assignments & submissions** — the komting creates per-subject assignments in two types (individual / group) with a configurable submission mode:
  - `none` — progress tracking only;
  - `file` — students upload files (with extension/size/count limits);
  - `link` — students submit a link;
  - `file_link` — both.
  Every enrolled member gets a progress row (`pending` → `done` → `graded` with a score and feedback). Students submit; the komting grades.
- **Exports** — download group results or assignment recapitulation as Excel (`.xlsx`).
- **Notifications** — in-app (and email) alerts when groups are locked and when a new assignment is created.

## Requirements

- [DDEV](https://ddev.readthedocs.io/) (provides PHP 8.4, MariaDB, Mailpit, npm)

## Setup

```bash
# 1. Start DDEV (creates the environment + installs dependencies)
ddev start

# 2. Install PHP & JS dependencies
ddev composer install
ddev npm install

# 3. Build front-end assets
ddev npm run build

# 4. Configure environment from the example
cp .env.example .env
# (DDEV already provides DB/MAIL/QUEUE settings; set APP_URL=http://komtinghub.ddev.site)

# 5. Run migrations + seeders
ddev artisan migrate:fresh --seed
```

## Default seeded accounts

All seeded accounts use password `password`:

| Role    | Email                 | Notes |
|---------|-----------------------|-------|
| Admin   | `admin@example.com`    | creates class rooms & manages users |
| Komting | `komting@example.com`  | manages the `IF-24` demo class room |
| Student | `mhs1@example.com` … `mhs39@example.com` | demo students enrolled in the demo class |

The seeder also builds a **demo class room** (`Informatics 2024` / `IF-24`) with 20 students and three subjects exercising each feature path: `Software Engineering` (self-selection groups), `Data Structures` (randomized groups), and `Web Programming` (individual assignments).

## Usage

### Admin

1. Create **class rooms** and assign each to a komting.
2. Manage **komting & student** accounts as needed.

### Komting (class representative)

1. Open your assigned **class room** and create **subjects** under it.
2. Add members to the class room, then enroll them into subjects.
3. Manage **groups** per subject:
   - **select** mode — tell students to join a group, then **Lock Groups** when done;
   - **random** mode — run the shuffle and share the public verification link.
4. Create **assignments**, review submissions (download files / inspect links), and **grade** each member.
5. Use the **export** buttons to download results as Excel.

### Student

1. Open the **dashboard** and click your class/subject.
2. In **self-selection** mode, join or leave your group (until the komting locks it).
3. View assignments and **submit** them (file/link, depending on the assignment) for grading.

## Verifying a random shuffle (transparency)

Every randomized group run is stored with its **seed**, the **algorithm/version**, a **timestamp**, a **result snapshot**, and a **SHA-256 hash** of the result.

1. Run the shuffle from the subject page.
2. Copy the public verification link (`/verify/{run}`).
3. Anyone opening the link can see the seed, algorithm, hash, and the resulting groups, and re-verify that the hash matches the published result.

Because the commit hash is saved before/with the result and the seed is published, the run is reproducible and auditable by third parties.

## Running tests

```bash
ddev artisan test
```

The suite covers authentication/roles, class & subject CRUD, admin user management, membership, group creation/join/leave/locking, shuffle determinism + verification, assignment submissions & grading, exports, notifications, and authorization (authorized vs. 403).

## Project structure

- `app/Models` — `User`, `ClassRoom`, `Subject`, `Group`, `Assignment`, `AssignmentSubmission`, `ShuffleRun`, `ShuffleLog`
- `app/Services/GroupShuffleService` — deterministic, auditable shuffle engine
- `app/Http/Middleware/EnsureUserHasRole` + `app/Policies` — role gating and authorization (`ClassRoomPolicy`, `SubjectPolicy`, `GroupPolicy`, `AssignmentPolicy`)
- `app/Events` + `app/Listeners` + `app/Notifications` — event-driven notifications
- `app/Exports` — `GroupExport`, `AssignmentExport` (Excel)

# CDAT — Call Data Analysis Tool

## END-USER USER MANUAL

**Hyderabad City Police**

Version: 1.0 *(no version number is published inside the application; this manual is versioned independently — see Document Information)*
Date: September 2, 2026

> 📷 **Cover image** — application logo (`public/assets/images/logo.png`) — pending. Insert on the final PDF cover page alongside the title above.

---

## 1. Document Information

| Item | Details |
|---|---|
| Document Name | CDAT – End-User User Manual |
| Version | 1.0 |
| Date | September 2, 2026 |
| Intended Audience | End users of CDAT: standard users, power users (bulk upload), and administrators |
| Document Purpose | Explains how to sign in and use every screen in CDAT, module by module, in plain language |
| Screenshots | Not yet included in this version — see the note at the end of this document |

---

## 2. Table of Contents

1. Document Information
2. Table of Contents
3. Introduction
4. Getting Started
5. Signing In
6. Your Dashboard
7. Getting Around: The Sidebar Menu
8. How Searching Works (read this before the module guide)
9. Module-by-Module Guide
   9.1 Data Upload *(power user / admin)*
   9.2 Administration *(admin only)*
   9.3 Summary
   9.4 Call Details
   9.5 CDAT (Contacts)
   9.6 IMEI Search
   9.7 Address
   9.8 Day/Night Location
   9.9 Offenders List
   9.10 Others
   9.11 Interrogation Reports
   9.12 JRMS
   9.13 PD Act
10. Buttons You'll See Everywhere
11. Viewing a Detail Record
12. Common Workflows
13. Troubleshooting
14. Frequently Asked Questions
15. Best Practices
16. Feature Limitations
17. Quick Reference Guide

---

## 3. Introduction

### What is this application?

CDAT (Call Data Analysis Tool) is an internal Hyderabad City Police web application for analyzing call data records (CDR) and subscriber data records (SDR) and cross-referencing them against other police records — jail-release records (JRMS), interrogation reports, PD Act records, and offenders lists.

### Why is this application used?

Investigating officers need to quickly answer questions like "who has this suspect been calling?", "has this person been released from jail before?", "is there an interrogation report on this name?", or "which phones share this IMEI?". CDAT brings these different record types into one place so an officer can search once and follow the trail across modules instead of checking separate systems.

### Who should use it?

Serving officers and analysts who have been issued a CDAT account. Access to specific areas depends on the role assigned to the account (see [Section 7](#7-getting-around-the-sidebar-menu)).

### What can users do with it?

- Search call records, contacts, addresses, IMEIs, and location patterns for a phone number.
- Cross-reference a name or case against jail-release records, interrogation reports, and PD Act records.
- Look up offenders lists, vehicle records, and other reference data.
- (Power users and admins only) Upload new call-data files for processing.
- (Admins only) Manage user accounts, review activity logs, and run read-only diagnostic queries.

---

## 4. Getting Started

- **Application access**: CDAT is a web application — open it in a browser using the internal URL provided by your department. No installation is required.
- **Required account**: You must have a CDAT account created for you by an administrator (see [Section 9.2](#92-administration-admin-only)). There is no self-registration.
- **Browser requirements**: A modern browser with JavaScript enabled gives you the full experience (in-page search results, live previews). CDAT also works with JavaScript disabled — pages simply reload fully instead of updating in place.
- **Network requirements**: You must be connected to the network your department has configured for CDAT access (internal network or VPN, depending on your deployment).
- **Initial setup**: None beyond receiving your username and temporary password from an administrator.

> **Note:** This manual does not include or reference any real usernames, passwords, API keys, or subscriber data. Do not share your CDAT password with anyone, including other officers.

---

## 5. Signing In

### Step 1 — Open the application

Go to the CDAT web address given to you by your administrator. You will see the login page.

> 📷 **Figure 5.1 — Login page** (screenshot pending). Should show: the Username field (1), Password field with show/hide toggle (2), and the Login button (3).

### Step 2 — Enter your username

Click the **Username** field and type your assigned account name.

### Step 3 — Enter your password

Click the **Password** field and type your password. Click the eye icon to show or hide what you've typed. If Caps Lock is on, the form warns you, since passwords are case-sensitive.

### Step 4 — Click Login

Click the **Login** button.

### Expected Result

- The button changes to "Signing in…" and then "Signed in. Opening your dashboard…" before taking you to your [Dashboard](#6-your-dashboard).

### Errors You Might See

| Situation | What you'll see | What to do |
|---|---|---|
| A field was left blank | A red message under that field (e.g. "Enter your username.") | Fill in the missing field and try again |
| Wrong username or password | "Username or password is incorrect." (the password field is cleared) | Re-enter your credentials carefully. The message intentionally does not say which field was wrong |
| Account deactivated | "This account is deactivated. Contact an administrator." | Contact your administrator |
| Too many failed attempts | "Too many failed login attempts. Try again later." | Wait 15 minutes before trying again (the lockout follows 5 incorrect attempts) |

> **Note:** There is no self-service "Forgot password" option. If you are locked out or need your password reset, contact your administrator directly — do not ask a colleague to share their account with you.

### Session Timeout and Logout

- **Automatic sign-out**: If you are inactive for about 30 minutes, CDAT signs you out automatically for security. There is no warning beforehand — the next action you take simply returns you to the login page. This is expected behavior, not an error. Any search criteria you had typed but not submitted will need to be re-entered.
- **Signing out manually**: Click **Logout** at the bottom of the sidebar menu. You are signed out immediately with no confirmation prompt, and returned to the login page.

---

## 6. Your Dashboard

After signing in, you land on the **Dashboard** — your home base. The sidebar menu (covered next) is always visible alongside it.

> 📷 **Figure 6.1 — Dashboard** (screenshot pending). Annotate: (1) Sidebar navigation, (2) Quick Links grid, (3) "Add to Quick Links" button, (4) the two standing notice banners.

**1 — Sidebar Navigation.** Lists every module you have access to (see [Section 7](#7-getting-around-the-sidebar-menu)).

**2 — Quick Links grid.** Shortcut tiles to the pages you use most often. This is personal to your account — you choose what appears here.

**3 — Add to Quick Links.** A button that lets you pin the page you're currently viewing (or choose from a list) so you don't have to dig through the sidebar every time.

**4 — Standing notices.** Two informational banners: where to email raw call-data files if you need a report generated, and where to send requests for suspect-image searches.

The Dashboard does not show statistics or counters — it is a links-and-notices page, not a reporting page.

---

## 7. Getting Around: The Sidebar Menu

The sidebar on the left lists every module you have access to. It works in two layers:

- **Groups** (e.g. "JRMS", "Call Details") are buttons, not links — clicking one expands or collapses its list of screens in place. Clicking a group never navigates you away from the current page.
- **Screens** (the items inside a group, e.g. "Name Search") are links — clicking one takes you to that page.

What you see depends on your account's role. There is no badge that spells out your role anywhere in the interface — the menu itself is the indicator: if a group appears, you have access to it.

| Role | What they see |
|---|---|
| Standard user | All the everyday search modules (rows 3–13 below) |
| Power user | Everything a standard user sees, plus **Data Upload** |
| Admin | Everything, including **Administration** |

| Group | What it's for |
|---|---|
| Data Upload *(power user / admin only)* | Import CDR/SDR call records and custom tables; review and approve uploads; view upload history |
| Administration *(admin only)* | Manage user accounts; view the activity log; run read-only SQL queries |
| Summary | Call-count totals and summaries for a phone number, by date range, ISD, or state |
| Call Details | Movement tracking; comparing calls between two numbers; calls between dates |
| CDAT | Contact lookups — single number or bulk |
| IMEI Search | Cross-reference phones and IMEIs |
| Address | Single and bulk address lookups |
| Day/Night Location | Frequent locations for a number, by time of day or date range |
| Offenders List | Habitual offenders and undetected-case listings |
| Others | Cell ID search, vehicle search, common-contacts analysis, training/personnel search, offender MO sub-classification search |
| Interrogation Reports | Search interrogation records by name, name + crime head, or gender + crime head |
| JRMS | Jail release records — by name, date range, or police station |
| PD Act | PD Act records — by name, MO, or police station |

---

## 8. How Searching Works (read this before the module guide)

Almost every module in CDAT — Summary, Call Details, CDAT, IMEI Search, Address, Day/Night Location, Others, Interrogation Reports, JRMS, and PD Act — follows the **same basic pattern**. Learn it once here; [Section 9](#9-module-by-module-guide) then only lists what's different about each module (its fields and what its results show), not this whole procedure again.

**Step 1 — Open a search screen.** In the sidebar, click a group to expand it, then click a screen name. You'll see an empty form.

**Step 2 — Fill in your search criteria.** Every screen asks for just a few fields — usually a phone number, a name, or a date range. Required fields are marked; some fields are searchable dropdowns (start typing to filter the list).

**Step 3 — Click Search.** Your results appear in place a moment later without the page reloading. (If your browser has JavaScript turned off, the page reloads normally instead — it still works the same way.)

**Step 4 — Read the results.**
- A plain-language banner summarizes what was searched.
- A results table appears below it, showing the matching records.
- The table shows a manageable number of rows per page with paging controls; some screens cap results at a fixed number (e.g. the first 500 matches) and say so.
- If nothing matches, you'll see a plain "No records found" message instead of an empty table.

**Step 5 — Export or print (optional).** Every results table has two buttons above it, added automatically:
- **Export CSV** — downloads the *entire* result set (not just the page you're viewing) as a spreadsheet file.
- **Print** — expands all rows and opens your browser's print dialog.

**Step 6 — Follow a link from a result (optional).** Some cells in the table are clickable and take you into another module's detail record — for example, a phone number might link to that number's Contacts record, or an "IR Available" label might link to the matching Interrogation Report. See [Section 11](#11-viewing-a-detail-record).

**Step 7 — Start a new search.** Fill the form again, or click a different screen in the sidebar.

> **Note:** No search screen in CDAT lets you edit or delete a record from the results table. These are read-only lookup screens. Editing data only happens through the Data Upload / Verify workflow ([Section 9.1](#91-data-upload-power-user--admin)) or, for user accounts, through Administration ([Section 9.2](#92-administration-admin-only)).

---

## 9. Module-by-Module Guide

### 9.1 Data Upload *(power user / admin)*

**Purpose.** Import call-data files (CDR, SDR) or other custom tables into CDAT so they become searchable in the rest of the application.

**Who uses it.** Accounts with the power-user or admin role only. Standard users do not see this menu group.

**How to open it.** From the sidebar, click **Data Upload**, then choose **CDR Upload**, **SDR Upload**, **Custom Table Upload**, **Upload History**, or **Verify Upload**.

> 📷 **Figure 9.1 — Data Upload screen** (screenshot pending). Annotate: (1) module/network selector, (2) file chooser, (3) Preview button, (4) Upload button.

**Available Actions**

| Action | What the user does | Result |
|---|---|---|
| Choose upload type | Selects CDR, SDR, or Custom Table | Determines which file formats and fields appear |
| Choose network (CDR only) | Selects the operator (e.g. Airtel, Jio, Vi, BSNL) | Tags the upload with the correct carrier |
| Choose file | Selects a file from their computer | CDR/Custom: `.csv`, `.xls`, `.xlsx`. SDR: `.bak` (a database backup file) |
| Preview | Clicks **Preview** | Shows the first few rows of the file so you can check it looks correct before committing to a full upload |
| Upload | Clicks **Upload** | Sends the file for processing |

**Step-by-Step Procedure**

1. Open **Data Upload** and choose the upload type (CDR, SDR, or Custom Table).
2. For CDR uploads, select the network/operator.
3. Click **Choose File** and select the file from your computer.
4. Optionally click **Preview** to check the first rows before committing.
5. Click **Upload**.
6. Watch the status. It will show one of: **Success**, **Pending Verification**, **Processing**, or **Failed**.
7. If the status is **Processing**, the Job Status screen updates automatically as the file finishes processing in the background.
8. If the status is **Pending Verification**, go to **Verify Upload** to review and approve or reject it (see below).

**Verify Upload**

Some uploads are staged for manual review before they go live. On the **Verify Upload** screen, open the pending upload and choose:

- **Load to DB** — approves the staged data and adds it to the live records. Once approved, the staged copy is removed (there is nothing left to re-review).
- **Reject** — discards the staged data. The upload is marked "Rejected" and nothing is added to live records.

**Upload History**

A filterable, paginated list of past uploads — filter by uploader, module, status (Processing/Success/Pending Verification/Rejected/Partial/Failed), or date range. Click an entry to jump back into its verification screen if it's still pending.

**Expected Result.** A successful upload's data becomes searchable across the relevant modules (e.g. a CDR upload's records appear in Call Details, Summary, etc.) once approved.

**Important Notes**

- Only power users and admins can access this module.
- The **Download CSV Template** feature referenced in older documentation is no longer available — the screen that used to provide it now returns an "unavailable" message. Use operator CDR files or database backup (`.bak`) files directly instead.
- SDR uploads (`.bak` files) can take longer, since they go through a separate restore-and-migrate process.

---

### 9.2 Administration *(admin only)*

**Purpose.** Manage user accounts, review what users have been doing in the system, and (for advanced diagnostics) run read-only database queries.

**Who uses it.** Admin-role accounts only.

**How to open it.** From the sidebar, click **Administration**, then choose **User Management**, **Activity Log**, or **SQL Console**.

#### User Management

> 📷 **Figure 9.2 — User Management screen** (screenshot pending). Annotate: (1) users table, (2) Create User button, (3) role dropdown in the create/edit form.

A table of every account: username, full name, role, status, and creation date, with search/export/print.

**Available Actions**

| Action | What the user does | Result |
|---|---|---|
| Create User | Clicks **Create User**, fills in username (3–32 characters, letters/numbers/`.`/`_`/`-` only, must be unique), full name, password, and role | A new account is created immediately |
| Edit | Clicks **Edit** on a row, changes full name, role, and/or password (leave password blank to keep it unchanged) | The account is updated. If you edit your own account, your session updates immediately |
| Deactivate / Activate | Clicks the toggle, confirms in the pop-up | The account can no longer log in (or can again) |
| Delete | Clicks **Delete**, confirms in the pop-up | The account is permanently removed |

**Roles available when creating or editing a user:**

| Role | Access |
|---|---|
| User (Standard Access) | Search modules only |
| Power User (Bulk Upload Access) | Search modules + Data Upload |
| Admin (All Access) | Everything, including Administration |

**Important Notes**

- You cannot deactivate, delete, or demote your own account, or the **last remaining active admin account** — CDAT blocks this to prevent the system from being left with no administrator.
- Every create/edit/deactivate/delete action is recorded in the Activity Log.

#### Activity Log

Shows a record of user activity: filter by username and a date range (defaults to today; you cannot select a future date). Each entry shows the module, action, detail, IP address, and timestamp.

**Purpose.** Lets an administrator review who did what and when, for accountability and troubleshooting.

#### SQL Console

> ⚠️ **Warning — advanced/diagnostic tool, not a routine feature.** The SQL Console gives an administrator direct **read-only** access to the underlying database using SQL query language. It is intended for advanced diagnostics and reporting, not day-to-day investigation work — use the regular search modules for normal lookups. Every query you run is logged, and only your account's most recent 10 queries are shown for reference.

**How it's restricted:**

- Only `SELECT` (read) queries are accepted — the console actively blocks any query that would add, change, or delete data (`INSERT`, `UPDATE`, `DELETE`, `DROP`, and similar).
- Queries are capped in length, results are capped in row count, and a query that runs too long is automatically stopped.
- This feature can be turned off entirely for the whole application by your system administrator; if it's off, the page will not be available.

**Available Actions**

| Action | What the user does | Result |
|---|---|---|
| Execute Query | Types a `SELECT` query and clicks **Execute Query** | Results appear in a table below |
| Clear | Clicks **Clear** | Empties the query box |
| Copy Query | Clicks **Copy Query** | Copies the current query text |
| Export CSV / Export Excel | Clicks the export button | Downloads the result table |
| Recent Queries | Clicks an entry in the sidebar list | Reloads that earlier query |

---

### 9.3 Summary

**Purpose.** Produces a computed call-activity report for a single phone number — not a filtered list of individual records, but an aggregated analysis.

**Who uses it.** All users.

**How to open it.** Sidebar → **Summary** → choose one of the available summary screens (overall totals, date-range summary, ISD/new-contact summary, or state-wise summary).

**What you enter.** A phone number (required), plus optional filters depending on the screen: date range, state, or ISD flag.

**What you get back.** Instead of a row-per-record table, you get:

- A **subject card** at the top — the identity/address information CDAT has for that number, and its first and last call on record.
- A **contact-analysis table** below it — every number this subject was in contact with, with total incoming/outgoing call counts, total duration, first call date, and last call date, plus that contact's resolved address where available.

**Important Notes.** Because this is a computed report rather than a raw list, there is no "times released"-style drill-down from these rows the way there is in JRMS — the contact numbers shown do link out to their own Contacts record ([Section 9.5](#95-cdat-contacts)), the same as elsewhere in CDAT.

---

### 9.4 Call Details

**Purpose.** Look at calls made between specific dates, or compare/track calling patterns between two numbers.

**Who uses it.** All users.

**How to open it.** Sidebar → **Call Details** → choose **Calls Between Dates**, **Movements**, or **Movements Between Two Numbers**.

**What you enter.**

| Screen | Fields |
|---|---|
| Calls Between Dates | Phone number, date range, optional operator/state filters |
| Movements | A phone number |
| Movements Between Two Numbers | Two phone numbers, to compare their calling patterns |

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide). One exception: the **Movements** screen shows a running total of matching records and real page-by-page navigation (most other screens page through results already loaded).

---

### 9.5 CDAT (Contacts)

**Purpose.** Look up the contacts (other numbers called) associated with a phone number.

**Who uses it.** All users.

**How to open it.** Sidebar → **CDAT** → choose a single-number contact lookup or **Bulk Contacts**.

**What you enter.** A single phone number for the standard screen; for **Bulk Contacts**, paste multiple phone numbers into a text box (one per line) to look them all up at once. This is a paste-in list, not a file upload.

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

### 9.6 IMEI Search

**Purpose.** Cross-reference phones and IMEI numbers — find which IMEIs a phone number has used, or which phone numbers have used a given IMEI.

**Who uses it.** All users.

**How to open it.** Sidebar → **IMEI Search** → choose **IMEI Search** (search by IMEI) or **Phones for IMEI** (search by phone number).

**What you enter.** An IMEI number, or a phone number, depending on the screen.

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

### 9.7 Address

**Purpose.** Look up address information associated with a phone number.

**Who uses it.** All users.

**How to open it.** Sidebar → **Address** → choose the single lookup or **Bulk Address**.

**What you enter.** A single phone number, or — for **Bulk Address** — a pasted list of multiple phone numbers, one per line.

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

### 9.8 Day/Night Location

**Purpose.** Show where a phone number is most frequently located during day vs. night hours, useful for identifying likely home/work locations.

**Who uses it.** All users.

**How to open it.** Sidebar → **Day/Night Location** → choose the standard screen or the date-range variant.

**What you enter.** A phone number; optionally a date range.

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

### 9.9 Offenders List

**Purpose.** Browse pre-compiled lists of habitual offenders and undetected cases.

**Who uses it.** All users.

**How to open it.** Sidebar → **Offenders List** → choose **Habitual Offenders** or **Undetected Cases**.

> **This module does not have a search form.** Unlike every other module in this guide, opening one of these screens immediately shows a list (up to 2,000 rows) — there is nothing to fill in and no Search button. Use your browser's page search (Ctrl+F / Cmd+F) or the Export CSV button to find a specific entry.

---

### 9.10 Others

**Purpose.** A collection of smaller, specialized lookup tools that don't belong to one of the larger modules.

**Who uses it.** All users.

**How to open it.** Sidebar → **Others** → choose one of the screens below.

| Screen | What you enter | What it's for |
|---|---|---|
| Cell ID Search | A Cell ID, plus operator/state dropdowns | Finds records associated with a mobile tower cell ID |
| Common Contacts | A pasted list of phone numbers, an operator (equals/greater-than/less-than), and a threshold number | Finds numbers that appear as a contact across multiple of the pasted numbers, above your threshold |
| Vehicle Search | A vehicle registration number | Finds records associated with that vehicle |
| Offender MO Search | An MO (modus operandi) sub-classification text | Finds offenders matching that MO; links to a full offender detail record |
| Training/Personnel Search | Search criteria, employee number, or rank | Looks up training/personnel records (not crime data) |

All follow the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide), except Common Contacts, which takes a pasted list rather than a single value.

---

### 9.11 Interrogation Reports

**Purpose.** Search interrogation records by name and/or crime details.

**Who uses it.** All users.

**How to open it.** Sidebar → **Interrogation Reports** → choose **Search by Name**, **Search by Crime Head**, or **Search by Gender + Crime Head**.

**What you enter.**

| Screen | Fields |
|---|---|
| By Name | Name, crime head (searchable dropdown) |
| By Crime Head | Crime head only |
| By Gender + Crime Head | Gender (dropdown), crime head |

**Results and drill-down.** Results list matching names with a link into the full **Interrogation Report** detail page ([Section 11](#11-viewing-a-detail-record)), and — where one exists — a link into the matching PD Act record.

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

### 9.12 JRMS (Jail Release Records)

**Purpose.** Search jail-release records to see if and when a person has been released from custody.

**Who uses it.** All users.

**How to open it.** Sidebar → **JRMS** → choose **Name Search**, **Date Range Search**, or **Police Station Search**.

**What you enter.**

| Screen | Fields |
|---|---|
| Name Search | Name (required), Crime Head (required, searchable dropdown) |
| Date Range Search | Start and end date, Police Station |
| Police Station Search | Police station (searchable dropdown), Crime Head |

**Results.** A banner summarizing the search, followed by a table with prisoner photo, name, father's name, crime details, phone, ID proof, address, jail, admission and release dates. Some search variants cap results at the first 500 matches and say so on screen.

**Drill-downs.**
- The "times released" count for a prisoner links to their full jail-release history across every record sharing the same case reference.
- A phone number links to that number's record in CDAT Contacts.
- An "IR Available" label, when shown, links to the matching Interrogation Report.

Otherwise follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

### 9.13 PD Act

**Purpose.** Search PD Act (Prevention of Dangerous Activities) records.

**Who uses it.** All users.

**How to open it.** Sidebar → **PD Act** → choose **Name Search**, **MO Search**, or **Police Station Search**.

**What you enter.**

| Screen | Fields |
|---|---|
| Name Search | Name |
| MO Search | Modus operandi / crime-head text |
| Police Station Search | Police station (dropdown) |

**Results and drill-down.** Results list matching names/photos with a link into the full **PD Act** detail record ([Section 11](#11-viewing-a-detail-record)).

Follows the standard search pattern from [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

---

## 10. Buttons You'll See Everywhere

These controls appear the same way across most modules, so they're documented once here rather than repeated in every section.

### Search
Runs your query using whatever you've filled in on the form. See [Section 8](#8-how-searching-works-read-this-before-the-module-guide).

### Export CSV
Appears above every results table. Downloads the complete set of matching results (not just the rows currently visible on screen) as a spreadsheet file you can open in Excel or similar software.

### Print
Appears above every results table. Expands all result rows and opens your browser's print dialog so you can print or save the results as a PDF using your browser's own print-to-PDF option.

### Login / Logout
See [Section 5](#5-signing-in).

### Preview (Data Upload only)
Shows the first rows of a file you're about to upload, so you can confirm it's the right file before committing to a full upload.

### Upload (Data Upload only)
Sends the selected file for processing.

### Load to DB / Reject (Data Upload → Verify only)
Approves or discards a staged upload. See [Section 9.1](#91-data-upload-power-user--admin).

### Create User / Edit / Deactivate / Delete (Administration → User Management only)
Manage accounts. See [Section 9.2](#92-administration-admin-only).

### Execute Query (Administration → SQL Console only)
Runs a read-only query. See [Section 9.2](#92-administration-admin-only).

> **Note:** CDAT does not have an application-wide "Submit" or "Save" button pattern beyond what's described above — most screens are read-only lookups, not editable forms.

---

## 11. Viewing a Detail Record

A handful of pages are not search screens themselves — you reach them by clicking a link inside another module's results. They show one full record's information, organized into labeled sections.

> 📷 **Figure 11.1 — Interrogation Report detail view** (screenshot pending). Annotate the major sections, e.g. (1) Particulars, (2) Physical Features, (3) Family History, (4) Modus Operandi, (5) Arrest Details.

**Detail pages in CDAT:**

| Detail page | Reached from | Shows |
|---|---|---|
| Interrogation Report | Interrogation Reports search, or an "IR Available" link elsewhere | Full interrogation record: particulars, ID details, physical features, family history, modus operandi, arrest details, pending warrants, and a brief-facts narrative |
| PD Act Record | PD Act search, or a link from an Interrogation Report | Full PD Act record: a summary panel plus detailed key/value information |
| Jail Release History | The "times released" link in JRMS results | Every jail-release record sharing the same case reference for that person |
| Offender Detail | The Offender MO Search results (Others module) | Full detail for that offender's record |

Each of these detail pages can be exported/printed the same way as a search results table (see [Section 10](#10-buttons-youll-see-everywhere)). To go back to where you were, use your browser's Back button or return to the module via the sidebar.

---

## 12. Common Workflows

### Workflow: Run a Search and Follow a Lead

```
Sign in
   ↓
Open a module from the sidebar
   ↓
Fill in search criteria
   ↓
Click Search
   ↓
Review the results table
   ↓
Click a linked cell to open a detail record  (optional)
   ↓
Export CSV or Print, if needed  (optional)
```

### Workflow: Upload a New CDR File (power user / admin)

```
Sign in
   ↓
Open Data Upload → CDR Upload
   ↓
Select the network/operator
   ↓
Choose the file
   ↓
Preview the file  (optional)
   ↓
Click Upload
   ↓
Status: Success, Pending Verification, Processing, or Failed
   ↓
If Pending Verification: open Verify Upload → Load to DB or Reject
   ↓
Data becomes searchable
```

### Workflow: Create a New User Account (admin)

```
Sign in as admin
   ↓
Open Administration → User Management
   ↓
Click Create User
   ↓
Enter username, full name, password, and role
   ↓
Save
   ↓
Account appears in the users table, ready to sign in
```

---

## 13. Troubleshooting

| Problem | Possible Reason | What to Do |
|---|---|---|
| Can't sign in — "Username or password is incorrect." | Mistyped username or password | Re-enter carefully; check Caps Lock |
| Can't sign in — "This account is deactivated." | Your account has been deactivated by an administrator | Contact your administrator |
| Can't sign in — "Too many failed login attempts." | 5 wrong attempts within 15 minutes | Wait 15 minutes, then try again |
| Suddenly returned to the login page mid-task | 30-minute idle session timeout | This is expected — sign in again and repeat your search |
| A menu group (e.g. Data Upload, Administration) is missing | Your account's role doesn't include that access | Contact your administrator if you believe you need access |
| "No records found" after a search | No matching data exists for your criteria, or the data hasn't been uploaded yet | Double-check your search terms; broaden the date range or drop optional filters |
| A required field shows a red message | A mandatory field was left blank or in the wrong format | Fill in the field as indicated and resubmit |
| Upload shows "Failed" | The file didn't match the expected format for that upload type, or an error occurred during processing | Confirm you selected the correct upload type (CDR/SDR/Custom) and file format, then try again; contact an administrator if it keeps failing |
| Upload stuck on "Processing" | The file is still being processed in the background | Wait — the status updates automatically. Very large files take longer |
| "CSV template" download not available | This feature has been removed from the application | Use an operator CDR file or a database backup (`.bak`) file directly instead |
| SQL Console rejects your query | Only read-only `SELECT`/`WITH` queries are allowed | Rewrite the query as a `SELECT`; this console cannot add, change, or delete data |

---

## 14. Frequently Asked Questions

**Can I reset my own password?**
No. There is no self-service password reset in CDAT. Contact your administrator.

**Why can't I see the Data Upload or Administration menu?**
These are only shown to accounts with the power-user or admin role. Ask your administrator if you believe your account needs different access.

**Can I delete a record I find in a search?**
No. Search screens in CDAT are read-only. Data only changes through the Data Upload/Verify workflow (power users/admins) or account management (admins).

**Does Export CSV download only what's on my screen?**
No — it downloads the entire matching result set, even if you're only looking at one page of results on screen.

**What happens if my session times out while I'm in the middle of a search?**
You're returned to the login page automatically after about 30 minutes of inactivity. Sign in again; any unsubmitted search criteria will need to be re-entered.

**Is the SQL Console the same as a search screen?**
No. It's a read-only, admin-only diagnostic tool for writing your own database queries — not intended for routine investigation work. Use the regular search modules for day-to-day lookups.

---

## 15. Best Practices

- Double-check names, numbers, and dates before clicking **Search** — accurate input gives accurate results.
- Use the narrowest search that answers your question (e.g. add a date range) rather than the broadest one — this keeps results manageable and avoids hitting result caps.
- When uploading a file, use **Preview** first to confirm you selected the right file before running a full upload.
- Review a pending upload carefully on the **Verify Upload** screen before clicking **Load to DB** — once approved, the staged copy is gone.
- Keep your password private and do not share your account with colleagues; each officer should have their own login so activity can be attributed correctly.
- Always use **Logout** when you're finished at a shared or public workstation rather than relying on the idle timeout.

---

## 16. Feature Limitations

Documented here so users know exactly what is and isn't available — nothing below is a guess.

| Feature | Status |
|---|---|
| Self-service password reset | **Not available.** Contact an administrator. |
| CSV template download for uploads | **Not available** (feature was removed; the page now returns an "unavailable" message) |
| Editing or deleting records from search results | **Not available.** Search screens are read-only everywhere in CDAT |
| Offenders List search/filter | **Not available.** These two screens show a fixed list (up to 2,000 rows) with no search form |
| SQL Console write access | **Not available by design.** Only read-only `SELECT`/`WITH` queries are permitted, and it can be turned off entirely by system configuration |
| User profile self-editing (for standard users) | **Not available.** Only administrators can change account details, via User Management |
| Notifications/alerts inbox | **Not available.** CDAT surfaces messages only as inline form errors and the two static Dashboard notices |

---

## 17. Quick Reference Guide

| Task | Navigation | Action |
|---|---|---|
| Sign in | Login page | Enter username & password → Login |
| Sign out | Sidebar (bottom) | Logout |
| Run a search | Any module → a screen | Fill form → Search |
| Export results | Above any results table | Export CSV |
| Print results | Above any results table | Print |
| Upload a CDR/SDR file | Data Upload → CDR/SDR Upload | Select file → Preview (optional) → Upload |
| Approve/reject an upload | Data Upload → Verify Upload | Load to DB / Reject |
| Create a user | Administration → User Management | Create User → fill details |
| Deactivate a user | Administration → User Management | Deactivate on that row |
| Review activity | Administration → Activity Log | Filter by user/date |
| Run a diagnostic query | Administration → SQL Console | Type a SELECT query → Execute Query |

---

## About This Version of the Manual

This manual documents CDAT's functionality as implemented in the codebase reviewed on September 2, 2026. It intentionally does not include screenshots yet — every screen that should be illustrated is marked with a `📷 Figure` placeholder and a note of what the image should show. A follow-up pass will add real screenshots (with any sensitive or personal data removed or replaced with sample data) and produce a formatted PDF with a cover page, page numbers, and consistent styling.

No credentials, tokens, or real subscriber/personal data are included anywhere in this document.

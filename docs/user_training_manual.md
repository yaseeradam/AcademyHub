# AcademyHub Complete User Training Manual

Welcome to the **AcademyHub** Complete User Training Manual. This document is a step-by-step guide designed to help Administrators, Teachers, and Staff members learn how to use the AcademyHub school management system.

---

## Table of Contents
1. [System Roles & Access](#1-system-roles--access)
2. [Administrator Guide](#2-administrator-guide)
   - [Managing Students (Wizard Import)](#managing-students-wizard-import)
   - [Managing Teachers & Staff](#managing-teachers--staff)
   - [Plugin & Feature Management (Marketplace)](#plugin--feature-management-marketplace)
3. [Teacher Guide](#3-teacher-guide)
   - [Grade Entry & Broadsheets](#grade-entry--broadsheets)
   - [Computer Based Testing (CBT) System](#computer-based-testing-cbt-system)
   - [Theory Exam Marking & Result Release](#theory-exam-marking--result-release)
4. [Student & Parent Experience](#4-student--parent-experience)
   - [Student CBT Exam Portal](#student-cbt-exam-portal)
   - [WhatsApp Interactive Assistant (Bot)](#whatsapp-interactive-assistant-bot)

---

## 1. System Roles & Access

AcademyHub uses domain-level routing and distinct roles to ensure security:
* **Administrators (Admin/Superadmin):** Full control over configurations, imports, plugins, and billing.
* **Teachers (Staff):** Manage classroom results, attendance, assignments, and CBT exams.
* **Students/Parents:** Access exam portals, view academic dashboards, and query report cards via WhatsApp.

> [!NOTE]
> All student logins are session-based and do not require standard email credentials. Students log in using their **Admission Number** and password.

---

## 2. Administrator Guide

### Managing Students (Wizard Import)
Instead of typing student records manually one-by-one, admins can import entire class rosters at once using the robust Excel/CSV Upload Wizard.

#### How to import students:
1. Navigate to **Manage Students** from the sidebar menu.
2. Click the **📥 Import Students** button at the top right.
3. **Step 1 (Upload):** Drag & drop your `.xlsx`, `.csv`, `.ods`, or `.xls` file. Select the **Target Class** and specify if the sheet contains headers. Click **Continue**.
4. **Step 2 (Mapping):** The system automatically maps your file columns to database fields (First Name, Last Name, Admission Number, Gender). If the automatic mapping is incorrect, use the dropdown menus to select the corresponding columns manually. Click **Process Import**.
5. **Step 3 (Complete):** Review the import report. It shows the number of successful imports and any skipped or invalid records.

---

### Managing Teachers & Staff
Teachers must be registered in the system to access their dashboard, receive subject allocations, and set exams.

#### How to manage teachers:
1. Navigate to **Manage Teachers** from the sidebar.
2. Click **➕ Add Teacher** to register manually, or click **📥 Import Teachers** to use the Excel/CSV Upload Wizard.
3. For manually registered teachers:
   - Enter their **Name**, **Email**, and **Password**.
   - Ensure the account status is set to **Active**.
4. **Allocating Subjects:**
   - Go to **Subject Allocations** in the sidebar.
   - Select a Teacher, select their Class (e.g. JSS 1), and select the Subject (e.g. Mathematics).
   - Click **Save Allocation**. This authorizes the teacher to enter grades and set CBT exams for that specific class and subject.

---

### Plugin & Feature Management (Marketplace)
Feature targeting allows the school to enable or disable specific features (like CBT, WhatsApp Bot, and Parent Dashboards) on a class-by-class basis.

#### How to configure plugins:
1. Navigate to **Marketplace** (or **Installed Plugins**) from the sidebar.
2. Click on the plugin card you wish to configure (e.g., **CBT / Online Exams** or **WhatsApp Bot**).
3. **Allowed Class Targeting:**
   - Scroll down to the **Plugin Class Target Audience** panel.
   - Check the boxes next to the classes allowed to use this plugin (e.g. check **JSS 1** and **JSS 2**).
   - Click **Save Changes**.

> [!IMPORTANT]
> **Safety Restrictions:**
> * **No Uninstalls:** Once a plugin is active and targeted, the system disables the Uninstall button to prevent data loss (displays `🔒 Plugin Active & Locked`).
> * **Lock-in Protection:** Classes that were previously saved and active cannot be unchecked. They are marked as `🔒 Saved` and disabled. You can only select *new* classes to expand targeting.
> * **Disabled Save:** The "Save Changes" button remains disabled unless you select new classes that weren't target-saved before.

---

## 3. Teacher Guide

### Grade Entry & Broadsheets
Teachers can input termly continuous assessment (CA) scores and exam marks directly.

#### How to enter grades:
1. Click **Results Entry** in the sidebar.
2. Select your allocated **Class**, **Subject**, **Term**, and **Session**.
3. A spreadsheet grid will load. Click on any student row to type their **CA 1**, **CA 2**, and **Exam** marks.
4. Click **Save Marks** at the bottom of the page.
5. **Broadsheet View:** Click **Broadsheet** from the sidebar to view a grid of all subjects and averages for a class. You can download the report card PDFs directly from this screen.

---

### Computer Based Testing (CBT) System
Teachers can create online examinations, schedule them, monitor live candidate progress, and release scores.

#### How to create a CBT Exam:
1. Click **CBT Exams** in the sidebar.
2. Click **➕ Create New Exam**.
3. Configure the exam parameters:
   - **Title:** e.g., *Third Term Mathematics Exam*.
   - **Class & Subject:** Select the target class and allocated subject.
   - **Exam Type:** Select **Academic** (standard students) or **Aptitude** (candidates).
   - **Duration:** Specify in minutes (e.g. *60*).
   - **Access Code:** A unique word (e.g., `MATH101`) students type to access the exam.
   - **Exam PIN (Optional):** Add a secure pin if you want to require double-verification.
   - **Network Restriction (Optional):** Input IP addresses or CIDR blocks if the exam must only be taken on the school WiFi network.
4. Click **Create Exam**.

#### How to set questions:
1. Open the created exam from your CBT list.
2. Under the **Questions** tab, click **Add Question**.
3. Select the question type:
   - **MCQ (Multiple Choice):** Type the question prompt, set the mark weight, input the options (A, B, C, D), and click the circular radio button to select the **Correct Option**.
   - **Theory (Free Text):** Type the prompt and set the maximum marks. Students will type their answers in a textbox.
4. Click **Save Question**.

#### How to monitor live exams:
1. When the exam is live, click on **Monitor** next to the exam title.
2. You will see a list of all students taking the exam in real-time.
3. The table displays their **Name**, **Start Time**, **Last Activity**, and **Status** (*Taking*, *Submitted*, or *Terminated*).
4. **Device Lock/IP Reset:** If a student's computer crashes or their IP changes, click the **Reset IP** button next to their name. This allows them to log back in from another device without losing their progress.
5. **Terminating Attempts:** If a student is caught cheating, click **Terminate** to immediately lock them out of the test.

#### How to end the exam:
1. Once the time limit has elapsed, go to the Exam details page.
2. Click the orange **⏹️ End Exam** button.
3. **What this does:**
   - The system immediately stops all active student attempts.
   - Any student papers that were still running are automatically submitted, and their MCQs are instantly graded.
   - The exam status transitions to **Ended**, preventing any new logins.

---

### Theory Exam Marking & Result Release
If your exam contains theory questions, they must be marked manually before scores can be released.

#### How to grade theory questions:
1. Open the exam and click on the **Theory Review** tab.
2. Select a student's attempt.
3. Read the student's typed answer, and input their **Awarded Marks** (up to the maximum marks allowed).
4. Click **Submit Score**.
5. Repeat for all students. Once completed, the student's theory status will update to `Marked`.

#### How to release results:
1. Once all student papers are closed (the exam has ended) and all theory questions are marked, go to the Exam page.
2. Click the green **📢 Release Results** button.
3. Once clicked, the scores will be published. Students can now view their marked scripts, correct options, and scores in their dashboard.

---

## 4. Student & Parent Experience

### Student CBT Exam Portal
Students can log in to take their exams from any device or class computer.

#### How a student takes an exam:
1. Go to your school's exam portal page: `/cbt/portal`.
2. Type the **Exam Access Code** provided by your teacher (e.g. `MATH101`) and click **Verify**.
3. Enter your **Admission Number** (e.g. `FM-2026-1025`) and **Surname**.
4. Click **Start Exam**.
5. **Taking the Test:**
   - The exam question sheet loads with a countdown timer at the top.
   - Answers are saved automatically in the background as you click options or type.
   - If the browser crashes, simply reopen the portal and re-enter your code to resume where you left off.
6. Click **Submit Exam** at the bottom when finished.

---

### WhatsApp Interactive Assistant (Bot)
Parents can interact with the school's WhatsApp bot to query academic progress, attendance records, and pay school fees.

#### Available WhatsApp triggers:
* **`Attendance`** (or clicking attendance menu): Retrieves a summary of the student's presence and absence rate for the current term.
* **`Results`** (or clicking results menu): Lists termly score publications.
* **`Fees`** (or clicking fees menu): Shows billing summaries, invoices, and dynamic payment links.
* **`Report Card`** (or clicking select child buttons): Triggers report card compilers.
  - **Dynamic Compilation:** Compiles a fresh PDF report card from live database records (bypassing any old cached files).
  - **Meta Cache-Busting:** Transmits the PDF report card with cache-busting headers to force the WhatsApp app to fetch the latest details instead of serving older cached documents.

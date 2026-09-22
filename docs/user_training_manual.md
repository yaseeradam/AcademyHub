# AcademyHub: Ultimate ERP & School Management System Training Manual

Welcome to the **AcademyHub** Ultimate ERP & School Management System Training Manual. This comprehensive documentation provides step-by-step instructions, feature explanations, and button-by-button guides for every single module in the system. Use this manual to train your administrative staff, teachers, and school managers.

---

## Table of Contents
1. [Academics & General Setup](#1-academics--general-setup)
2. [Administrator User Directory & Security](#2-administrator-user-directory--security)
3. [Student Directory & Management](#3-student-directory--management)
4. [Classrooms, Arms & Arm Placements](#4-classrooms-arms--arm-placements)
5. [Teacher Dashboard & Subject Allocations](#5-teacher-dashboard--subject-allocations)
6. [Student Attendance Tracker](#6-student-attendance-tracker)
7. [Homework & Assignments Module](#7-homework--assignments-module)
8. [E-Learning Portal](#8-e-learning-portal)
9. [Results Management, Broadsheets & Report Cards](#9-results-management-broadsheets--report-cards)
10. [Billing, Invoicing & Payments Ledger](#10-billing-invoicing--payments-ledger)
11. [Student Promotion Manager](#11-student-promotion-manager)
12. [Certificate Generator](#12-certificate-generator)
13. [Announcements, Events & News Broadcasts](#13-announcements-events--news-broadcasts)
14. [Plugin Marketplace & Licensing](#14-plugin-marketplace--licensing)
15. [Savings & Loans Scheme](#15-savings--loans-scheme)
16. [Data Collection Module](#16-data-collection-module)
17. [Computer-Based Testing (CBT) System](#17-computer-based-testing-cbt-system)
18. [Student Dashboard Portal](#18-student-dashboard-portal)
19. [WhatsApp Integration Bot](#19-whatsapp-integration-bot)

---

## 1. Academics & General Setup
This module configures the foundational timeline of the school, including sessions (academic years) and terms.

#### How to manage academic sessions & terms:
1. Navigate to **Academics** in the sidebar.
2. **Adding a Session:**
   - Click the **➕ Add Session** button.
   - Enter the session format (e.g., `2026/2027`).
   - Click **Save**.
3. **Managing Terms:**
   - Under the terms section, view active terms (First, Second, Third Term).
   - Click **Activate** next to the term you want to set as the current active period for results entry and billing.
4. **School Info & Settings:**
   - Navigate to **Settings** in the sidebar.
   - Enter your **School Name**, **Motto**, **Address**, **Phone Number**, and **Email**.
   - Upload the **School Logo** (used for report cards and invoices).
   - Select the default **Currency** (e.g. `₦` or `$`).
   - Click **Save Settings**.

---

## 2. Administrator User Directory & Security
Manage administrative accounts, role assignments, and track all actions using the built-in system audit logging.

#### How to manage administrators:
1. Go to **Users** from the sidebar.
2. Click **➕ Add User** at the top right.
3. Fill in the user profile: **Name**, **Email**, **Password**, and select their system role:
   - **Admin:** Handles day-to-day operations, billing, and teacher/student directories.
   - **Superadmin:** Full system access, including database operations and billing settings.
   - **Bursar:** Restricted access focusing exclusively on billing, ledgers, payments, and receipts.
4. Click **Create User**.

#### How to view Audit Logs:
1. Go to **Audit Logs** in the sidebar.
2. View the table of logs recording the **User**, **Action** (e.g. `plugin_installed`, `marks_updated`), **Target Model**, **Timestamp**, and a detailed JSON payload of the **Changes** made.
3. Use the filter fields to search logs by a specific user or action type.

---

## 3. Student Directory & Management
The central directory for registering students, updating bio-data, and reviewing classroom placements.

#### How to manage students:
1. Navigate to **Students** from the sidebar.
2. **Manual Creation:**
   - Click **➕ Add Student**.
   - Input **First Name**, **Last Name**, **Admission Number**, **Gender**, **Date of Birth**, and assign their **Class** (e.g., JSS 1).
   - Click **Save Student**.
3. **Importing Students via Wizard:**
   - Click **📥 Import Students**.
   - Upload your Excel/CSV template.
   - Map spreadsheet headers (e.g. `adm_no` to *Admission Number*) using the dropdown menus.
   - Click **Process Import**.
4. **Student Statuses:**
   - Click **Edit** on a student's profile.
   - Toggle their status between **Active** (allowed in dashboards and CBT), **Suspended**, or **Graduated**. Click **Save**.

---

## 4. Classrooms, Arms & Arm Placements
Schools often divide large classes (e.g. JSS 1) into smaller arms or divisions (e.g. JSS 1 Gold, JSS 1 Silver).

#### How to manage classes & arms:
1. Go to **Classes** from the sidebar.
2. **Adding a Class:**
   - Click **➕ Add Class**.
   - Enter the name (e.g., `JSS 1`) and category (e.g., `Junior Secondary`). Click **Save**.
3. **Creating Class Arms:**
   - Under the class details, click **Manage Arms**.
   - Click **➕ Add Arm** and enter the name (e.g., `Gold`). Click **Save**.
4. **Assigning Students to Arms:**
   - Click **Arm Placements** in the menu.
   - Select the target class and arm.
   - Check the boxes next to the students' names, then click **Assign to Arm**.

---

## 5. Teacher Dashboard & Subject Allocations
Register teaching staff and delegate which subjects they teach for specific classes.

#### How to allocate subjects:
1. Navigate to **Academics** > **Subject Allocations** (or **Teacher Allocations**).
2. Click **➕ New Allocation**.
3. Select the **Teacher** from the dropdown.
4. Select the **Class** (e.g. JSS 1) and the **Subject** (e.g. Mathematics).
5. Click **Allocate**.

> [!NOTE]
> Teachers can only view, enter results, and set CBT exams for subjects they are explicitly allocated to.

---

## 6. Student Attendance Tracker
Keep track of student presence daily or by individual subject blocks.

#### How to mark daily attendance:
1. Navigate to **Attendance** in the sidebar.
2. Select the **Class**, **Arm**, and the current **Date**. Click **Load roster**.
3. A student list will load. By default, all students are marked **Present**.
4. Click the toggle buttons next to a student's name to change their status to **Absent** or **Late**.
5. Click **Save Attendance**.

#### How to view attendance statistics:
1. Under the **Attendance Report** tab, select the date range and class.
2. Review the grid showing summary statistics (presence rate, total absences) for each student.

---

## 7. Homework & Assignments Module
Create digital homework tasks, collect student uploads, and grade submissions online.

#### How to assign homework:
1. Go to **Homework** in the sidebar.
2. Click **➕ Create Homework**.
3. Enter the details:
   - **Title & Description:** Detailed guidelines for the assignment.
   - **Class & Subject:** Select the target audience.
   - **Due Date:** Calendar date when submissions close.
   - **Attachment (Optional):** Upload reference PDFs or worksheets.
4. Click **Publish**.

#### How to grade homework submissions:
1. Open the homework card from your list and click **Submissions**.
2. Click **Review** next to a student who has submitted.
3. Review their typed text or click their attachment to download it.
4. Input their **Grade/Score** and add **Teacher's Feedback**.
5. Click **Save Grade**.

---

## 8. E-Learning Portal
A digital locker where teachers can publish study guides, video lecture links, and learning materials.

#### How to upload study materials:
1. Go to **E-Learning** in the sidebar.
2. Click **➕ Upload Resource**.
3. Configure the resource:
   - **Title:** e.g., *Intro to Algebra Lecture Notes*.
   - **Category:** e.g., *Mathematics*, *Science*.
   - **Target Class:** Select which class arm has access to download it.
   - **Resource Type:** Select **File Upload** (PDFs, PPTs) or **Video Link** (YouTube/Vimeo urls).
4. Click **Publish**.

---

## 9. Results Management, Broadsheets & Report Cards
Compile student academic performances across continuous assessments (CAs) and exams to generate terminal report sheets.

#### How to configure grade weights & boundaries:
1. Go to **Results** > **Grade Settings** in the sidebar.
2. **Grade Scales:** Configure score ranges and remarks (e.g., `75 - 100` is `A - Excellent`).
3. **Assessment Weights:** Define weights for your continuous assessments (e.g. CA1 = 15%, CA2 = 15%, Exam = 70%). Click **Save Weights**.

#### How to view the Class Broadsheet:
1. Navigate to **Results** > **Broadsheet** in the sidebar.
2. Select the **Class**, **Arm**, **Term**, and **Session**, then click **Generate Broadsheet**.
3. The screen will load a complete grid displaying student names along one axis and subjects along the other.
4. Review calculated totals, averages, class positions, and outcomes.
5. Click **PDF Report Card** next to a student's row to download their individual report sheet, or click **Bulk Download** to export a ZIP folder containing reports for the entire class.

---

## 10. Billing, Invoicing & Payments Ledger
Manage school fee structures, student ledgers, invoicing, manual payment logs, and online payment integrations.

#### How to create billing structures & invoices:
1. Navigate to **Billing** in the sidebar.
2. Click **➕ Create Fee Template**.
3. Define the template name (e.g. *JSS 1 Termly Fees*) and click **Add Item** to specify line charges (e.g., Tuition = ₦50,000, Development = ₦10,000). Click **Save Template**.
4. **Generating Invoices:**
   - Click the **Invoicing** tab.
   - Select the target **Class** and the **Fee Template**.
   - Click **Generate Invoices for Class**. This automatically creates unpaid bills in each student's ledger.

#### How to log manual payments:
1. Go to **Billing** > **Collect Payment** (or click a student's ledger).
2. Search for the student by name or admission number.
3. Input the **Amount Paid**, select the **Payment Method** (Cash, POS, Bank Transfer), and enter the transaction reference.
4. Click **Confirm Payment**. The system will deduct this amount from the student's debt balance and automatically generate a **Payment Receipt** PDF.

#### How to configure Online Payments (Paystack):
1. Navigate to **Settings** > **Payment Gateway**.
2. Input your **Paystack Live Public Key** and **Secret Key**.
3. Toggle status to **Active** and click **Save configuration**. Parents can now pay invoices online directly from their dashboards.

---

## 11. Student Promotion Manager
At the end of an academic session (year), use the Promotion Manager to transition student classes.

#### How to promote students:
1. Navigate to **Promotions** in the sidebar.
2. Select the current **Source Class** (e.g. JSS 1) and target **Destination Class** (e.g. JSS 2).
3. Click **Load Students**.
4. Review the roster. For each student, select their action:
   - **Promote:** Moves them to the selected destination class.
   - **Repeat:** Retains them in their current class for the next session.
   - **Graduate:** Mark them as finished with school.
5. Click **Execute Promotions**.

---

## 12. Certificate Generator
Design and print professional student graduation, merit, or performance certificates directly from the platform.

#### How to generate certificates:
1. Go to **Certificates** in the sidebar.
2. Click **➕ Create Template**.
3. Upload a **Background Frame Image** and a **Principal/Director's Signature image**.
4. Design the layout using the drag-and-drop placeholder blocks (e.g., `{{student_name}}`, `{{class}}`, `{{date}}`, `{{certificate_title}}`). Click **Save Template**.
5. To print, select a certificate template, select a **Class**, choose the students you wish to award, and click **Generate Certificates PDF**.

---

## 13. Announcements, Events & News Broadcasts
Publish news updates and upcoming school events to student dashboards, parent feeds, and staff channels.

#### How to publish announcements:
1. Go to **Announcements** in the sidebar.
2. Click **➕ Create Announcement**.
3. Type your **Title** and **Body Content** (supports markdown formatting).
4. Set the **Target Audience** (Staff, Students, Parents, or All).
5. Click **Publish**.

#### How to manage calendar events:
1. Navigate to the **Events Calendar** tab.
2. Click on a calendar date block.
3. Input the **Event Title** (e.g., *Inter-house Sports* or *Midterm Break*), description, and start/end times.
4. Click **Save Event**. This populates the calendar for all user portals.

---

## 14. Plugin Marketplace & Licensing
Extend the features of your AcademyHub installation by enabling optional cloud plugins.

#### How to target classes and save:
1. Navigate to the **Marketplace** (or **Installed Plugins**) tab in the sidebar.
2. Click on the plugin card you want to adjust (e.g. **CBT Portal**).
3. Under the targeting section, select which classes have access and are billed under this plugin.
4. Click **Save Changes** to activate.

> [!IMPORTANT]
> **Licensing Safeguards:**
> * **No Uninstalling:** Active plugins display `🔒 Plugin Active & Locked`. You cannot uninstall them to protect database data integrity.
> * **Checkbox Locks:** Previously saved target classes are locked in the UI (`🔒 Saved`) and cannot be unchecked.
> * **Additive Saves:** The **Save Changes** button remains disabled unless you select a *new* class that wasn't target-saved before.

---

## 15. Savings & Loans Scheme
A welfare scheme module designed for staff groups to deposit monthly savings or take emergency loan advances.

#### How to manage staff welfare savings:
1. Navigate to **Savings & Welfare** in the sidebar.
2. **Log Deposit:** Select a staff member, input their monthly savings contribution, and click **Confirm Deposit**.
3. **Requesting Loans:**
   - Under the loan portal, click **Create Loan Application**.
   - Input the applicant staff name, **Principal Amount**, and **Repayment Terms** (number of months). Click **Issue Loan**.
4. **Repayments Log:** Click **Record Repayment** to post staff deductions against active loan balances.

---

## 16. Data Collection Module
Create customized digital registration forms to collect specific data (e.g. emergency contacts, health details) from parents.

#### How to collect custom data:
1. Navigate to **Data Collection** in the sidebar.
2. Click **➕ Create Form Builder**.
3. Enter the form title and click **Add Field** to build questions (Text fields, Checkboxes, File Uploads).
4. Click **Publish & Share**.
5. Copy the generated public link to send to parents.
6. Under the **Responses** tab, click **Export Excel** to download all collected parent inputs in a spreadsheet.

---

## 17. Computer-Based Testing (CBT) System
Build exams, schedule access windows, monitor live candidate progression, reset devices, and grade theory results.

#### How to create a CBT Exam:
1. Go to **CBT Exams** in the sidebar.
2. Click **➕ Create New Exam**.
3. Fill in the exam details:
   - **Title:** e.g., *Term 2 Mathematics Examination*.
   - **Class & Subject:** The class taking the test and the allocated subject.
   - **Duration:** Specify test length in minutes.
   - **Access Code:** The password students type to enter the test.
   - **PIN Check (Optional):** Enable if you want to require individual student security PINs.
   - **IP Address Lock (Optional):** Lock exam entries to a specific school computer lab IP address.
4. Click **Save**.

#### How to add questions:
1. Open the exam card and click the **Questions** tab.
2. Click **Add Question**.
3. Choose **MCQ** (Multiple Choice) or **Theory** (Written responses).
4. Input the question prompt, points value, and options. Click the radio button for the correct option (MCQ).
5. Click **Save Question**.

#### How to monitor live exams:
1. While an exam is running, click **Monitor** next to the exam title.
2. You will see a live dashboard showing taking students, timestamps, and active status.
3. **Reset IP / Device Lock:** If a student's computer freezes or their internet disconnects, click the **Reset IP** button next to their name. This allows them to resume their attempt from another device immediately without locking them out.

#### How to end exams & release results:
1. To close the exam window, click **⏹️ End Exam** on the exam details page. This immediately stops all active student timers and submits their answers.
2. If the exam includes theory questions, click the **Theory Review** tab, read student answers, award marks, and click **Submit Score**.
3. Once all theory reviews are marked, click **📢 Release Results** on the exam main page. The scores will be published instantly to student dashboards.

---

## 18. Student Dashboard Portal
A private portal where students can log in to check their grades, download learning materials, submit homework, and take CBT exams.

#### Student Portal Guide:
1. Go to the student login page: `/login`.
2. Enter the **Student Admission Number** (e.g. `FM-2026-1025`) and **Password**. Click **Login**.
3. **Portal Features:**
   - **My Results:** View published CAs, exam scores, positions, and teacher remarks.
   - **Exams:** Links to the student CBT portal.
   - **Homework:** View assigned homework, download materials, type responses, or upload file submissions.
   - **E-Learning:** View and download uploaded study notes and watch video links.
   - **Profile Settings:** Reset their default password to a new, secure password.

---

## 19. WhatsApp Integration Bot
A dynamic communication assistant allowing parents to check their children's progress, verify attendance, check outstanding bills, and fetch report cards via WhatsApp.

#### Available WhatsApp triggers:
* **`Attendance`** (or clicking attendance menu): Retrieves a summary of the student's presence and absence rate for the current term.
* **`Results`** (or clicking results menu): Lists termly score publications.
* **`Fees`** (or clicking fees menu): Shows billing summaries, invoices, and dynamic payment links.
* **`Report Card`** (or clicking select child buttons): Triggers report card compilers.
  - **Dynamic Compilation:** Compiles a fresh PDF report card from live database records (bypassing any old cached files).
  - **Meta Cache-Busting:** Transmits the PDF report card with cache-busting headers to force the WhatsApp app to fetch the latest details instead of serving older cached documents.

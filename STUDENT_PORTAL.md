# Student Portal Features

## Overview

The Student Portal is now fully functional with comprehensive features for students to access their academic information, submit homework, and track their progress.

## Features Implemented

### 1. **Student Dashboard** (`/student/dashboard`)
- Real-time statistics:
  - Attendance rate with present/total days
  - Average score across all subjects
  - Class position and ranking
  - Pending and overdue homework count
- Grade distribution chart
- Quick action buttons to navigate to other sections
- Student profile display with photo

### 2. **Homework Management** (`/student/homework`)
- View all assigned homework filtered by:
  - **Pending**: Not yet submitted, due date in future
  - **Overdue**: Not submitted, past due date
  - **Submitted**: Already submitted
  - **All**: Complete list
- Submit homework with:
  - Text answer (required, minimum 10 characters)
  - File attachment (optional, max 10MB)
- View submitted homework with timestamp
- Download attachments from submissions
- Homework automatically assigned based on class and section

### 3. **Academic Results** (`/student/results`)
- View scores by session and term
- Detailed breakdown:
  - CA1, CA2, and Exam scores
  - Total score and grade
  - Position in subject
- Summary statistics:
  - Total subjects
  - Average score
  - Highest score with subject name
- Color-coded grades (A=green, B=blue, C=yellow, D=orange, E/F=red)

### 4. **Attendance Tracking** (`/student/attendance`)
- Monthly calendar view with color-coded attendance:
  - Green: Present
  - Red: Absent
  - Yellow: Late
  - Gray: No record
- Navigate between months
- Term statistics:
  - Total days
  - Present days
  - Absent days
  - Attendance rate percentage
- Recent attendance list with notes

## Student Login

### Credentials Format
- **Username**: Admission Number (e.g., `STU20240001`)
- **Password**: `firstname + last 4 digits of admission number`
  - Example: If name is "John" and admission is "STU20240001", password is `john0001`

### Login Process
1. Go to `/login`
2. Click on "Student" tab
3. Enter admission number
4. Enter password
5. Access student portal

## Homework Assignment Flow

### For Teachers:
1. Create homework in `/homework`
2. Select class and optionally section
3. Homework is automatically available to all students in that class/section

### For Students:
1. Login to student portal
2. Navigate to "Homework" section
3. View assigned homework based on their class and section
4. Click "Submit" to provide answer and optional attachment
5. View submission confirmation

## Technical Implementation

### Models & Relationships

**Student Model** (`app/Models/Student.php`):
```php
- homeworkSubmissions(): HasMany
- attendanceMarks(): HasMany
- getHomeworkForStudent(): Collection
```

**Homework Model** (`app/Models/Homework.php`):
```php
- getStudentsForHomework(): Collection
```

### Livewire Components

1. `App\Livewire\Student\Dashboard` - Main dashboard with stats
2. `App\Livewire\Student\Homework` - Homework listing and submission
3. `App\Livewire\Student\Results` - Academic results display
4. `App\Livewire\Student\Attendance` - Attendance calendar and stats

### Routes

All student routes use session-based authentication:
- `/student/dashboard` - Main dashboard
- `/student/homework` - Homework management
- `/student/results` - Academic results
- `/student/attendance` - Attendance records

### Layout

Custom student layout (`resources/views/layouts/student.blade.php`):
- Green-themed navigation bar
- Student info display (name, admission number)
- Quick navigation links
- Logout button

## Database Schema

### Homework Submissions
```sql
homework_submissions
- id
- homework_id (FK to homework)
- student_id (FK to students)
- submission (text)
- attachment (file path)
- submitted_at (timestamp)
- created_at
- updated_at
```

### Attendance Marks
```sql
attendance_marks
- id
- sheet_id (FK to attendance_sheets)
- student_id (FK to students)
- status (Present/Absent/Late)
- note (optional)
- created_at
- updated_at
```

### Scores
```sql
scores
- id
- student_id (FK to students)
- subject_id (FK to subjects)
- class_id (FK to classes)
- term
- session
- ca1, ca2, exam
- total (auto-calculated)
- grade (auto-calculated)
- position
- created_at
- updated_at
```

## Security

- Session-based authentication (not User model)
- Middleware checks for valid student session
- Students can only view their own data
- File uploads validated (max 10MB)
- XSS protection via Blade templating

## Future Enhancements

Potential additions:
- Download report cards
- View timetable
- Access announcements
- Message teachers
- View certificates
- Take CBT exams
- Parent communication
- Fee payment history

## Support

For issues or questions about the student portal, contact the development team.

---

© 2024 MyAcademy - Student Portal

# Student Performance Tracking System

## Overview

The Student Performance Tracking System provides comprehensive analytics to track student academic progress, identify strengths and weaknesses, and monitor performance trends across terms.

## Features

### 1. **Performance Overview**
- Average scores and percentages
- Current grade and overall performance
- Subjects passed vs failed
- Highest and lowest scores

### 2. **Subject Performance Analysis**
- Detailed breakdown by subject (CA1, CA2, Exam scores)
- Grade distribution
- Percentage performance
- Position tracking (if available)

### 3. **Strengths & Weaknesses Identification**
- Top 3 performing subjects (70%+ performance)
- Bottom 3 subjects needing attention (<60% performance)
- Visual indicators for quick identification

### 4. **Term-by-Term Comparison**
- Compare performance across all three terms
- Track progress throughout the academic year
- Identify improvement or decline patterns

### 5. **Attendance Impact Analysis**
- Attendance rate calculation
- Present, absent, and late days tracking
- Correlation between attendance and academic performance
- Intelligent insights (e.g., "Poor attendance affecting performance")

### 6. **Homework Performance**
- Total assignments tracking
- Submission rate (on-time vs late)
- Average grades on homework
- Completion rate percentage

### 7. **CBT Exam Performance**
- Total CBT exams taken
- Average scores and percentages
- Pass/fail statistics
- Highest and lowest scores

### 8. **Improvement Areas**
- Subject-wise progress analysis
- Comparison with previous term
- Trend indicators (Improving, Declining, Stable)
- Flags for subjects needing immediate attention

### 9. **Progress Trends**
- Visual representation of performance across terms
- Average score trends
- Percentage performance over time

## Access Levels

### Students
- **Route:** `/student/performance`
- **Access:** Via student portal (session-based authentication)
- **Features:** View their own performance data only

### Parents
- **Route:** `/parents/performance`
- **Access:** Via parent portal (authenticated users with 'parent' role)
- **Features:** View their children's performance data

### Teachers & Admins
- **Route:** `/analytics/student-performance`
- **Access:** Authenticated users with 'admin' or 'teacher' role
- **Features:** 
  - View any student's performance
  - Filter by class
  - Search by name or admission number
  - Advanced analytics and insights
  - **Quick Access:** Available in "More Features" page
  - **Direct Access:** Click "Performance Analytics" button on student details page (next to Finance tab)

## Technical Implementation

### Core Service
**File:** `app/Support/StudentPerformanceService.php`

The service provides the following methods:
- `getPerformanceAnalysis()` - Main method returning comprehensive analytics
- `getOverview()` - Overall performance metrics
- `getSubjectPerformance()` - Subject-wise breakdown
- `getStrengthsAndWeaknesses()` - Top and bottom performing subjects
- `getTermComparison()` - Cross-term analysis
- `getAttendanceImpact()` - Attendance correlation
- `getHomeworkPerformance()` - Homework statistics
- `getCbtPerformance()` - CBT exam analytics
- `getImprovementAreas()` - Progress tracking
- `getProgressTrend()` - Trend visualization data

### Livewire Components

#### Student/Parent Component
**File:** `app/Livewire/Student/Performance.php`
- Displays performance for logged-in student or parent's child
- Term selection
- Tabbed interface for different views

#### Admin/Teacher Component
**File:** `app/Livewire/Analytics/StudentPerformance.php`
- Student selection interface
- Class and search filters
- Comprehensive analytics display

### Views

#### Student/Parent View
**File:** `resources/views/livewire/student/performance.blade.php`
- Overview cards with key metrics
- Tabbed interface:
  - Overview (strengths, weaknesses, attendance)
  - Subject Performance (detailed table)
  - Progress Trends (term comparison, CBT stats)
  - Improvement Areas (progress analysis)

#### Admin/Teacher View
**File:** `resources/views/livewire/analytics/student-performance.blade.php`
- Student selection panel
- Performance display panel
- All analytics in single scrollable view

## Data Sources

The system aggregates data from:
1. **Scores Table** - Academic results (CA1, CA2, Exam)
2. **Attendance Marks** - Daily attendance records
3. **Homework Submissions** - Assignment completion and grades
4. **CBT Attempts** - Computer-based test results
5. **Academic Terms** - Term dates and session information

## Performance Metrics

### Grading Scale
- **A:** 70-100%
- **B:** 60-69%
- **C:** 50-59%
- **D:** 45-49%
- **E:** 40-44%
- **F:** Below 40%

### Attendance Correlation Insights
- **Excellent attendance (90%+) + Excellent performance (70%+):** "Excellent attendance, excellent performance"
- **Excellent attendance (90%+) + Poor performance (<70%):** "Good attendance, needs academic improvement"
- **Poor attendance (<75%) + Poor performance (<60%):** "Poor attendance affecting performance"
- **Poor attendance (<75%):** "Attendance needs improvement"

### Trend Indicators
- **Improving:** Score increased by more than 5 points
- **Declining:** Score decreased by more than 5 points
- **Stable:** Score change within ±5 points

## Usage Examples

### For Students
```php
// Access via student portal
// Navigate to: /student/performance
// View your own performance automatically
```

### For Parents
```php
// Access via parent portal
// Navigate to: /parents/performance
// View first child's performance (can be extended for multiple children)
```

### For Teachers/Admins
```php
// Access via analytics menu
// Navigate to: /analytics/student-performance
// Select class, search for student
// Click on student to view detailed analytics

// OR Quick Access from Student Details:
// 1. Go to Students → View any student
// 2. Click "Performance Analytics" button (next to Admission Form)
// 3. Student will be auto-selected in analytics view

// OR from More Features page:
// 1. Go to More Features
// 2. Click "Student Performance Analytics" card
```

## Integration Points

### Adding to Navigation Menus

#### Student Dashboard
Add to `resources/views/livewire/student/dashboard.blade.php`:
```html
<a href="{{ route('student.performance') }}" class="nav-link">
    Performance Tracking
</a>
```

#### Parent Dashboard
Add to `resources/views/livewire/parents/dashboard.blade.php`:
```html
<a href="{{ route('parents.performance') }}" class="nav-link">
    Child Performance
</a>
```

#### Admin/Teacher Analytics Menu
Add to analytics navigation:
```html
<a href="{{ route('analytics.student-performance') }}" class="nav-link">
    Student Performance
</a>
```

## Future Enhancements

### Potential Features
1. **Export to PDF** - Generate performance reports
2. **Email Reports** - Send performance summaries to parents
3. **Predictive Analytics** - Predict future performance based on trends
4. **Peer Comparison** - Compare with class average
5. **Goal Setting** - Set and track performance goals
6. **Teacher Recommendations** - AI-powered improvement suggestions
7. **Multi-child Support** - Parents with multiple children can switch between them
8. **Historical Data** - View performance across multiple academic years
9. **Performance Alerts** - Notify when performance drops significantly
10. **Subject-specific Insights** - Deep dive into specific subject performance

## Configuration

### Customizing Score Weights
Edit `config/myacademy.php`:
```php
'results_ca1_max' => 20,
'results_ca2_max' => 20,
'results_exam_max' => 60,
```

### Customizing Thresholds
Edit `app/Support/StudentPerformanceService.php`:
```php
// Strength threshold (currently 70%)
->filter(fn($s) => ($s->total / $maxPossible) >= 0.7)

// Weakness threshold (currently 60%)
->filter(fn($s) => ($s->total / $maxPossible) < 0.6)

// Improvement/Decline threshold (currently ±5 points)
$change > 5 => 'Improving',
$change < -5 => 'Declining',
```

## Troubleshooting

### No Data Displayed
- Ensure scores are entered for the selected term
- Check that the student has assigned subjects
- Verify academic term is properly configured

### Incorrect Calculations
- Verify score configuration in `config/myacademy.php`
- Check that CA1, CA2, and Exam scores are within valid ranges
- Ensure total is calculated correctly in Score model

### Performance Issues
- Consider caching performance data for frequently accessed students
- Implement pagination for large student lists
- Use database indexing on student_id, term, and session columns

## Support

For technical support or feature requests, contact the MyAcademy development team.

---

© 2024 MyAcademy - Student Performance Tracking System

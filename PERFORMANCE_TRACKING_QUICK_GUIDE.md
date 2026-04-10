# Student Performance Tracking - Quick Reference

## What It Does

Tracks **everything** about student performance:
- ✅ Which subjects they're **strong** at (70%+)
- ❌ Which subjects they're **weak** at (<60%)
- 📈 How they're **improving or declining** over terms
- 📊 **Attendance impact** on grades
- 📝 **Homework completion** rates
- 💻 **CBT exam** performance
- 🎯 **Overall progress** trends

## Access Points

### Students
- URL: `/student/performance`
- See their own performance only

### Parents
- URL: `/parents/performance`
- See their children's performance

### Teachers/Admins
- **Primary URL:** `/analytics/student-performance`
- **Quick Access Options:**
  1. **From Student Details:** Click "Performance Analytics" button on any student's profile page
  2. **From More Features:** Navigate to More Features → Student Performance Analytics
  3. **Direct Link:** Use the analytics menu
- Search and view any student's performance

## Key Metrics Tracked

### Academic Performance
- Average score across all subjects
- Current grade (A-F)
- Subjects passed vs failed
- Highest and lowest scores
- Subject-wise breakdown (CA1, CA2, Exam)

### Attendance
- Attendance rate percentage
- Present, absent, and late days
- Correlation with academic performance

### Homework
- Total assignments
- Submission rate (on-time vs late)
- Average grades
- Completion percentage

### CBT Exams
- Total exams taken
- Average scores and percentages
- Pass/fail statistics

### Progress Analysis
- Term-by-term comparison
- Subject improvement/decline trends
- Areas needing immediate attention

## Smart Insights

The system automatically identifies:
1. **Top 3 strengths** - Best performing subjects
2. **Top 3 weaknesses** - Subjects needing attention
3. **Attendance correlation** - How attendance affects grades
4. **Improvement trends** - Which subjects are improving/declining
5. **At-risk subjects** - Subjects with failing grades or declining performance

## Files Created

### Backend
- `app/Support/StudentPerformanceService.php` - Core analytics engine
- `app/Livewire/Student/Performance.php` - Student/parent component
- `app/Livewire/Analytics/StudentPerformance.php` - Admin/teacher component

### Frontend
- `resources/views/livewire/student/performance.blade.php` - Student/parent view
- `resources/views/livewire/analytics/student-performance.blade.php` - Admin/teacher view

### Routes Added
```php
// Student portal
Route::get('/student/performance', Performance::class);

// Parent portal
Route::get('/parents/performance', Performance::class);

// Admin/Teacher analytics
Route::get('/analytics/student-performance', StudentPerformance::class);
```

## How to Use

### For Students
1. Login to student portal
2. Navigate to "Performance Tracking"
3. Select term to view
4. Explore tabs: Overview, Subjects, Trends, Improvement

### For Parents
1. Login to parent portal
2. Navigate to "Child Performance"
3. Select term to view
4. View comprehensive performance data

### For Teachers/Admins

**Method 1: From Student Details (Fastest)**
1. Go to Students → Click on any student
2. Click "Performance Analytics" button (blue button at top)
3. View instant analytics for that student

**Method 2: From More Features**
1. Go to More Features
2. Click "Student Performance Analytics" card
3. Select student from list

**Method 3: Direct Analytics Menu**
1. Login to admin/teacher portal
2. Go to Analytics → Student Performance
3. Filter by class or search by name
4. Click on student to view detailed analytics
5. Use insights to provide targeted support

## Example Insights

### Strength Example
```
Mathematics
Score: 85/100
Grade: A
Percentage: 85%
```

### Weakness Example
```
English Language
Score: 42/100
Grade: E
Percentage: 42%
⚠️ Needs Attention
```

### Progress Example
```
Physics
Previous: 55 → Current: 68
Change: +13
Trend: 📈 Improving
```

### Attendance Correlation
```
Attendance Rate: 95%
Present: 57/60 days
Insight: "Excellent attendance, excellent performance"
```

## Quick Tips

1. **Check regularly** - Monitor performance at least once per term
2. **Focus on trends** - Look for patterns, not just single scores
3. **Address weaknesses early** - Intervene when subjects start declining
4. **Celebrate strengths** - Acknowledge and encourage strong performance
5. **Monitor attendance** - Poor attendance often correlates with poor performance

## Need Help?

- Check full documentation: `STUDENT_PERFORMANCE_TRACKING.md`
- Contact MyAcademy support team
- Review system logs for technical issues

---

**Remember:** This system helps identify issues early so you can provide timely support and intervention!

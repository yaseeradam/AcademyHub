<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 8px 0; }
        .meta { font-size: 10px; color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Students Export</h1>
    <div class="meta">Generated: {{ $generatedAt }}</div>

    <table>
        @if ($showHeaders)
            <thead>
                <tr>
                    <th>ADM No</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Full Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Guardian</th>
                    <th>Guardian Phone</th>
                    <th>DOB</th>
                    <th>Blood Group</th>
                    <th>Created At</th>
                </tr>
            </thead>
        @endif
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student['admission_number'] }}</td>
                    <td>{{ $student['first_name'] }}</td>
                    <td>{{ $student['last_name'] }}</td>
                    <td>{{ $student['full_name'] }}</td>
                    <td>{{ $student['class'] }}</td>
                    <td>{{ $student['section'] }}</td>
                    <td>{{ $student['gender'] }}</td>
                    <td>{{ $student['status'] }}</td>
                    <td>{{ $student['guardian_name'] }}</td>
                    <td>{{ $student['guardian_phone'] }}</td>
                    <td>{{ $student['dob'] }}</td>
                    <td>{{ $student['blood_group'] }}</td>
                    <td>{{ $student['created_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

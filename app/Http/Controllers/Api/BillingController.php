<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Transaction::with('student:id,first_name,last_name,admission_number')
            ->where('is_void', false)
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($user->role === 'parent') {
            $childIds = $user->students()->pluck('students.id');
            $query->whereIn('student_id', $childIds);
        } elseif ($user->role === 'student') {
            abort_unless($user->student_id, 403);
            $query->where('student_id', $user->student_id);
        } elseif (!in_array($user->role, ['admin', 'bursar'])) {
            abort(403);
        }

        return response()->json($query->paginate(20));
    }
}

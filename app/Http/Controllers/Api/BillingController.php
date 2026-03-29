<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Transaction::with('student')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($user->role === 'parent') {
            // Find all transactions for children of this parent
            // $query->whereIn('student_id', $user->students()->pluck('students.id'));
        } elseif ($user->role === 'student') {
            // $query->where('student_id', $user->student_id);
        } elseif (!in_array($user->role, ['admin', 'bursar'])) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $transactions = $query->paginate(20);

        return response()->json($transactions);
    }
}

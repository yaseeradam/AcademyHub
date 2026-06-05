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

    public function checkoutUrl(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $user = $request->user();
        $studentId = $request->input('student_id');

        // Authorization check
        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $studentId)->exists(), 403);
        } elseif ($user->role === 'student') {
            abort_unless($studentId == $user->student_id || $user->id == \App\Models\Student::find($studentId)?->user_id, 403);
        } elseif (!in_array($user->role, ['admin', 'bursar'])) {
            abort(403);
        }

        $student = \App\Models\Student::findOrFail($studentId);
        $activeTermNumber = \App\Models\AcademicTerm::activeTermNumber();
        $activeSessionName = \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1);

        $feeStructure = \App\Models\FeeStructure::where('class_id', $student->class_id)
            ->where('term', $activeTermNumber)
            ->where('session', $activeSessionName)
            ->first();

        $amountDue = $feeStructure ? (float) $feeStructure->amount_due : 0.0;

        $amountPaid = (float) Transaction::where('student_id', $student->id)
            ->where('type', 'Income')
            ->where('term', $activeTermNumber)
            ->where('session', $activeSessionName)
            ->where('is_void', false)
            ->sum('amount_paid');

        $outstandingBalance = max(0.0, $amountDue - $amountPaid);
        $apiKey = config('services.whatsapp.api_key') ?: env('WHATSAPP_API_KEY');

        $paymentUrl = route('whatsapp.pay', [
            'studentId' => $student->id,
            'term'      => $activeTermNumber,
            'session'   => $activeSessionName,
            'amount'    => $outstandingBalance,
            'key'       => $apiKey
        ]);

        return response()->json([
            'checkout_url' => $paymentUrl,
            'outstanding_balance' => $outstandingBalance,
        ]);
    }
}


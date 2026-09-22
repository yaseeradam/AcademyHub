<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingExportController extends Controller
{
    public function transactions(Request $request): StreamedResponse
    {
        $includeVoided = (bool) $request->boolean('include_voided', false);
        $type = $request->query('type');
        $category = $request->query('category');
        $studentId = $request->query('student_id');
        $session = $request->query('session');
        $term = $request->query('term');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Transaction::query()
            ->with('student')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (! $includeVoided) {
            $query->where('is_void', false);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($category) {
            $query->where('category', 'like', '%'.trim((string) $category).'%');
        }

        if ($studentId) {
            $query->where('student_id', (int) $studentId);
        }

        if ($session) {
            $query->where('session', (string) $session);
        }

        if ($term !== null && $term !== '') {
            $query->where('term', (int) $term);
        }

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $filename = 'transactions-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'wb');

            fputcsv($out, [
                'date',
                'type',
                'category',
                'amount_paid',
                'payment_method',
                'receipt_number',
                'is_void',
                'voided_at',
                'void_reason',
                'student_name',
                'admission_number',
                'session',
                'term',
            ]);

            foreach ($query->cursor() as $t) {
                fputcsv($out, [
                    optional($t->date)->format('Y-m-d'),
                    $t->type,
                    $t->category,
                    (string) $t->amount_paid,
                    $t->payment_method,
                    $t->receipt_number,
                    $t->is_void ? '1' : '0',
                    $t->voided_at?->format('Y-m-d H:i:s'),
                    $t->void_reason,
                    $t->student?->full_name,
                    $t->student?->admission_number,
                    $t->session,
                    $t->term,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BillingReceiptController extends Controller
{
    public function download(Transaction $transaction): Response
    {
        abort_unless($transaction->type === 'Income', 404);
        abort_unless($transaction->receipt_number, 404);

        $transaction->load('student');

        $pdf = Pdf::loadView('pdf.receipt', [
            'transaction' => $transaction,
        ])->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$transaction->receipt_number}.pdf\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}

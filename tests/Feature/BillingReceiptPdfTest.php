<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingReceiptPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_bursar_can_download_receipt_pdf_without_intl_extension(): void
    {
        $this->seed();

        $bursar = User::query()->where('email', 'bursar@academyhub.local')->firstOrFail();
        $student = Student::query()->firstOrFail();

        $transaction = Transaction::query()->create([
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'payment_method' => 'Cash',
            'amount_paid' => 40000.50,
            'date' => '2026-02-07',
        ]);

        $resp = $this->actingAs($bursar)->get(route('billing.receipt', $transaction));
        $resp->assertOk();
        $resp->assertHeader('Content-Type', 'application/pdf');
    }
}


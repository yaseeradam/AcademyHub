<?php

namespace Tests\Feature;

use App\Livewire\Billing\Index as BillingIndex;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingVoidTest extends TestCase
{
    use RefreshDatabase;

    public function test_bursar_can_void_transaction_and_it_disappears_from_default_list(): void
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
            'amount_paid' => 1000,
            'date' => '2026-02-07',
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('voidReason', 'Duplicate')
            ->call('confirmVoid', $transaction->id);

        $transaction->refresh();
        $this->assertTrue($transaction->is_void);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->assertDontSee($transaction->receipt_number);
    }

    public function test_billing_export_excludes_voided_by_default(): void
    {
        $this->seed();

        $bursar = User::query()->where('email', 'bursar@academyhub.local')->firstOrFail();
        $student = Student::query()->firstOrFail();

        $t1 = Transaction::query()->create([
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'payment_method' => 'Cash',
            'amount_paid' => 1000,
            'date' => '2026-02-07',
        ]);

        $t2 = Transaction::query()->create([
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2026/2027',
            'payment_method' => 'Cash',
            'amount_paid' => 2000,
            'date' => '2026-02-07',
            'is_void' => true,
        ]);

        $resp = $this->actingAs($bursar)->get('/billing/export/transactions');
        $resp->assertOk();

        $content = $resp->streamedContent();
        $this->assertStringContainsString($t1->receipt_number, $content);
        $this->assertStringNotContainsString($t2->receipt_number, $content);
    }
}


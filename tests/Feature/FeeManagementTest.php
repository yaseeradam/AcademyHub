<?php

namespace Tests\Feature;

use App\Livewire\Billing\Index as BillingIndex;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_page_is_accessible_with_fees_manage_only(): void
    {
        $bursar = User::factory()->create([
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => [
                'revoke' => ['billing.transactions'],
            ],
        ]);

        $resp = $this->actingAs($bursar)->get(route('billing.index'));

        $resp->assertOk();
        $resp->assertSee('Fee Structures');
    }

    public function test_fee_structure_can_be_saved_updated_and_deleted(): void
    {
        $bursar = User::factory()->create([
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => [
                'revoke' => ['billing.transactions'],
            ],
        ]);

        $class = SchoolClass::query()->create(['name' => 'JSS FEES', 'level' => 1]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'fees')
            ->set('feeClassId', $class->id)
            ->set('feeCategory', 'Tuition')
            ->set('feeAmountDue', '1000')
            ->set('feeSession', '2025/2026')
            ->set('feeTerm', 1)
            ->call('saveFeeStructure');

        $fee = FeeStructure::query()->where('class_id', $class->id)->firstOrFail();
        $this->assertSame('Tuition', $fee->category);
        $this->assertSame('2025/2026', $fee->session);
        $this->assertSame(1, $fee->term);
        $this->assertSame('1000.00', (string) $fee->amount_due);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'fees')
            ->call('startEditFee', $fee->id)
            ->set('feeAmountDue', '2000')
            ->call('saveFeeStructure');

        $fee->refresh();
        $this->assertSame('2000.00', (string) $fee->amount_due);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'fees')
            ->call('deleteFeeStructure', $fee->id);

        $this->assertDatabaseMissing('fee_structures', [
            'id' => $fee->id,
        ]);
    }

    public function test_debtors_prefers_specific_fee_and_filters_paid_by_term_and_session(): void
    {
        $bursar = User::factory()->create([
            'role' => 'bursar',
            'is_active' => true,
            'permissions' => [
                'revoke' => ['billing.transactions'],
            ],
        ]);

        $class = SchoolClass::query()->create(['name' => 'JSS DEBT', 'level' => 1]);
        $section = Section::query()->create(['class_id' => $class->id, 'name' => 'A']);

        $student = Student::query()->create([
            'admission_number' => 'ADM-DEBT-0001',
            'first_name' => 'Amina',
            'last_name' => 'Yusuf',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'gender' => 'Female',
            'status' => 'Active',
        ]);

        FeeStructure::query()->create([
            'class_id' => $class->id,
            'category' => 'Tuition',
            'term' => null,
            'session' => null,
            'amount_due' => 100,
        ]);

        FeeStructure::query()->create([
            'class_id' => $class->id,
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2025/2026',
            'amount_due' => 150,
        ]);

        Transaction::query()->create([
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 1,
            'session' => '2025/2026',
            'payment_method' => 'Cash',
            'amount_paid' => 20,
            'date' => '2026-02-07',
        ]);

        Transaction::query()->create([
            'student_id' => $student->id,
            'type' => 'Income',
            'category' => 'Tuition',
            'term' => 2,
            'session' => '2025/2026',
            'payment_method' => 'Cash',
            'amount_paid' => 50,
            'date' => '2026-02-07',
        ]);

        Livewire::actingAs($bursar)
            ->test(BillingIndex::class)
            ->set('tab', 'debtors')
            ->set('debtorsCategory', 'Tuition')
            ->set('debtorsSession', '2025/2026')
            ->set('debtorsTerm', 1)
            ->assertSee('150.00')
            ->assertSee('20.00')
            ->assertSee('130.00');
    }
}


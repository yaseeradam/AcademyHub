<?php

namespace App\Livewire\SavingsLoan;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Savings / Loan')]
class Index extends Component
{
    public function render()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'bursar'], true), 403);

        return view('livewire.savings-loan.index');
    }
}


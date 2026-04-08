<?php

namespace App\Livewire\Marketplace;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Marketplace')]
class Index extends Component
{
    public $selectedFeature = null;
    public $licenseFile = null;

    public function getLicenseStateProperty()
    {
        return [
            'ok' => false,
            'reason' => 'No license required.',
            'data' => []
        ];
    }

    public function isCbtInstalled()
    {
        return file_exists(app_path('Livewire/Cbt'));
    }

    public function isSavingsLoanInstalled()
    {
        return file_exists(app_path('Livewire/SavingsLoan'));
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        return view('livewire.marketplace.index');
    }
}
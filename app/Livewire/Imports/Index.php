<?php

namespace App\Livewire\Imports;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Imports')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.imports.index');
    }
}


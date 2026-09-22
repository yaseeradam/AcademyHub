<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class GlobalModal extends Component
{
    public $show = false;
    public $type = 'success'; // success, error, warning, info
    public $title = '';
    public $message = '';

    #[On('showModal')]
    public function showModal($type = 'success', $title = '', $message = '')
    {
        // Support both named params and single-array dispatch
        if (is_array($type) && isset($type['type'])) {
            $this->type = $type['type'] ?? 'success';
            $this->title = $type['title'] ?? '';
            $this->message = $type['message'] ?? '';
        } else {
            $this->type = $type;
            $this->title = $title;
            $this->message = $message;
        }

        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.global-modal');
    }
}

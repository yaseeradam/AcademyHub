<?php

namespace App\Traits;

trait DispatchesModals
{
    protected function dispatchSuccessModal(string $title, string $message): void
    {
        $this->dispatch('showModal', [
            'type' => 'success',
            'title' => $title,
            'message' => $message,
        ]);
    }

    protected function dispatchErrorModal(string $title, string $message): void
    {
        $this->dispatch('showModal', [
            'type' => 'error',
            'title' => $title,
            'message' => $message,
        ]);
    }

    protected function dispatchWarningModal(string $title, string $message): void
    {
        $this->dispatch('showModal', [
            'type' => 'warning',
            'title' => $title,
            'message' => $message,
        ]);
    }

    protected function dispatchInfoModal(string $title, string $message): void
    {
        $this->dispatch('showModal', [
            'type' => 'info',
            'title' => $title,
            'message' => $message,
        ]);
    }
}

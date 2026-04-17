<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\AgentProService;
use Illuminate\Support\Facades\Auth;

class AgentProChat extends Component
{
    public $isOpen = false;
    public $messages = [];
    public $question = '';

    public function mount()
    {
        $this->messages = [
            ['role' => 'assistant', 'content' => 'Hello! I am AgentPro. How can I help you today?']
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        $this->validate(['question' => 'required|string|max:1000']);

        $userQuestion = $this->question;
        $this->messages[] = ['role' => 'user', 'content' => $userQuestion];
        $this->question = '';

        $user = Auth::user();
        if (!$user) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'Please log in to use AgentPro.'];
            return;
        }

        try {
            $agentPro = new AgentProService($user);
            $answer = $agentPro->ask($userQuestion);
            $this->messages[] = ['role' => 'assistant', 'content' => $answer];
        } catch (\Exception $e) {
            $this->messages[] = ['role' => 'assistant', 'content' => 'Sorry, I encountered an error while processing your request.'];
        }
    }

    public function render()
    {
        return view('livewire.agent-pro-chat');
    }
}

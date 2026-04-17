<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueWhatsappAiKey extends Command
{
    protected $signature = 'whatsapp:ai-key {parent_email} {--reset : Reset and issue a new key}';
    protected $description = 'Issue a WhatsApp AI key for a parent account';

    public function handle(): int
    {
        $email = trim((string) $this->argument('parent_email'));
        if ($email === '') {
            $this->error('Parent email is required.');
            return self::FAILURE;
        }

        $parent = User::query()
            ->where('role', 'parent')
            ->where('email', $email)
            ->first();

        if (!$parent) {
            $this->error('Parent not found for email: ' . $email);
            return self::FAILURE;
        }

        if (!$this->option('reset') && !empty($parent->whatsapp_ai_key_hash)) {
            $this->warn('AI key already issued. Use --reset to create a new one.');
            return self::SUCCESS;
        }

        $plainKey = strtoupper(Str::random(10));
        $parent->whatsapp_ai_key_hash = hash('sha256', $plainKey);
        $parent->save();

        $this->info('AI key issued for ' . $parent->email . ': ' . $plainKey);
        $this->line('Share this key with the parent. Usage: ai [key] [your question]');

        return self::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Messages\Index as MessagesIndex;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MessageAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_attachment_and_download_it(): void
    {
        Storage::fake('local');

        $sender = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $recipient = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

        $conversation = Conversation::query()->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        Livewire::actingAs($sender)
            ->test(MessagesIndex::class)
            ->set('conversationId', $conversation->id)
            ->set('body', '')
            ->set('attachment', UploadedFile::fake()->create('song.mp3', 100, 'audio/mpeg'))
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->latest('id')->firstOrFail();
        $this->assertNotNull($message->attachment_path);

        $this->actingAs($recipient)
            ->get(route('messages.attachments.download', $message))
            ->assertOk();
    }

    public function test_non_participant_cannot_download_attachment(): void
    {
        Storage::fake('local');

        $sender = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $recipient = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'bursar', 'is_active' => true]);

        $conversation = Conversation::query()->create(['created_by' => $sender->id]);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => '',
            'attachment_path' => 'academyhub/messages/'.$conversation->id.'/file.txt',
            'attachment_name' => 'file.txt',
            'attachment_mime' => 'text/plain',
            'attachment_size' => 10,
        ]);

        Storage::disk('local')->put($message->attachment_path, 'hello');

        $this->actingAs($other)
            ->get(route('messages.attachments.download', $message))
            ->assertForbidden();
    }
}


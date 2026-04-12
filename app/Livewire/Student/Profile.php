<?php

namespace App\Livewire\Student;

use App\Models\Student;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public Student $student;

    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_address = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public $photo = null;

    public function mount(): void
    {
        $studentId = session('student_id');
        if (! $studentId) {
            redirect()->route('login');
            return;
        }

        $this->student = Student::findOrFail($studentId);
        $this->guardian_name    = $this->student->guardian_name    ?? '';
        $this->guardian_phone   = $this->student->guardian_phone   ?? '';
        $this->guardian_address = $this->student->guardian_address ?? '';
    }

    public function saveInfo(): void
    {
        $this->validate([
            'guardian_name'    => ['nullable', 'string', 'max:255'],
            'guardian_phone'   => ['nullable', 'string', 'max:30'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
        ]);

        $this->student->update([
            'guardian_name'    => $this->guardian_name    ?: null,
            'guardian_phone'   => $this->guardian_phone   ?: null,
            'guardian_address' => $this->guardian_address ?: null,
        ]);

        session()->flash('info_success', 'Contact info updated.');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password'          => ['required'],
            'new_password'              => ['required', 'min:6', 'confirmed'],
        ]);

        // Student password is firstname + last4digits of admission number
        $expected = strtolower($this->student->first_name) . substr($this->student->admission_number, -4);

        if (strtolower($this->current_password) !== $expected && ! Hash::check($this->current_password, $this->student->password ?? '')) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        $this->student->update(['password' => Hash::make($this->new_password)]);

        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        session()->flash('password_success', 'Password updated successfully.');
    }

    public function uploadPhoto(): void
    {
        $this->validate(['photo' => ['required', 'image', 'max:2048']]);

        File::ensureDirectoryExists(public_path('uploads/passports'));

        $ext = $this->photo->getClientOriginalExtension() ?: 'jpg';
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $this->student->admission_number);
        $filename = trim($safe, '-') . '-' . now()->format('YmdHis') . '.' . $ext;
        $path = $this->photo->storeAs('passports', $filename, 'uploads');
        $path = str_replace('\\', '/', $path);

        if ($this->student->passport_photo && $this->student->passport_photo !== $path) {
            Storage::disk('uploads')->delete($this->student->passport_photo);
        }

        $this->student->update(['passport_photo' => $path]);
        $this->photo = null;

        session()->flash('photo_success', 'Photo updated.');
    }

    public function render()
    {
        return view('livewire.student.profile')
            ->layout('layouts.student');
    }
}

<?php

namespace App\Livewire\Cbt\Portal;

use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.portal')]
#[Title('CBT Portal')]
class Start extends Component
{
    public string $examCode = '';
    public string $admissionNumber = '';
    public string $surname = '';
    public string $pin = '';

    public function mount(): void
    {
        $code = trim((string) request('code', ''));
        if ($code !== '') {
            $this->examCode = strtoupper($code);
        }
    }

    public function start()
    {
        $data = $this->validate([
            'examCode' => ['required', 'string', 'max:32'],
            'admissionNumber' => ['required', 'string', 'max:50'],
            'surname' => ['nullable', 'string', 'max:100'],
            'pin' => ['nullable', 'string', 'max:20'],
        ], [
            'examCode.required' => 'Exam code is missing. Please use the correct exam link provided by your teacher.',
        ]);

        $code = strtoupper(trim($data['examCode']));
        $admission = trim($data['admissionNumber']);
        $surname = trim((string) ($data['surname'] ?? ''));
        $pin = trim((string) ($data['pin'] ?? ''));

        $exam = CbtExam::query()
            ->where('access_code', $code)
            ->whereIn('status', ['live', 'approved'])
            ->whereNotNull('published_at')
            ->first();

        if (! $exam) {
            $this->addError('examCode', 'Invalid or inactive exam code.');
            return;
        }

        $ip = (string) request()->ip();
        $allowedCidrs = trim((string) ($exam->allowed_cidrs ?? ''));
        if ($allowedCidrs !== '') {
            $cidrs = collect(preg_split('/[,\s]+/', $allowedCidrs) ?: [])
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->values()
                ->all();

            if ($cidrs && ! \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $cidrs)) {
                $this->addError('examCode', 'This exam is restricted to the school network.');
                return;
            }
        }

        if ($exam->starts_at && now()->lt($exam->starts_at)) {
            $this->addError('examCode', 'This exam has not started yet.');
            return;
        }

        if ($exam->ends_at) {
            $grace = (int) ($exam->grace_minutes ?? 0);
            $end = $exam->ends_at->copy()->addMinutes(max(0, $grace));
            if (now()->gt($end)) {
                $this->addError('examCode', 'This exam has ended.');
                return;
            }
        }

        if ($exam->pin) {
            if ($pin === '' || ! hash_equals((string) $exam->pin, $pin)) {
                $this->addError('pin', 'Invalid exam PIN.');
                return;
            }
        }
        $student = Student::query()->where('admission_number', $admission)->first();
        if (! $student || $student->status !== 'Active') {
            $this->addError('admissionNumber', 'Student not found or inactive.');
            return;
        }

        if ($surname !== '' && strcasecmp(trim((string) $student->last_name), $surname) !== 0) {
            $this->addError('surname', 'Surname does not match this admission number.');
            return;
        }

        if ($exam->exam_type !== 'aptitude' && (int) $student->class_id !== (int) $exam->class_id) {
            $this->addError('admissionNumber', 'Student is not in the exam class.');
            return;
        }

        $attempt = CbtAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $attempt) {
            try {
                $attempt = CbtAttempt::query()->create([
                    'uuid' => (string) Str::uuid(),
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'started_at' => now(),
                    'last_activity_at' => now(),
                    'ip_address' => $ip,
                ]);
            } catch (QueryException) {
                $attempt = CbtAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->first();
            }
        }

        abort_unless($attempt, 500);

        if ($attempt->terminated_at) {
            $this->addError('admissionNumber', 'Your attempt was terminated by an admin.');
            return;
        }

        if ($attempt->submitted_at) {
            return redirect()->route('cbt.student.take', ['attempt' => $attempt, 'code' => $code]);
        }

        if (! $attempt->started_at) {
            $attempt->forceFill(['started_at' => now()])->save();
        }

        $lockedIp = trim((string) ($attempt->ip_address ?? ''));
        $allowedIp = trim((string) ($attempt->allowed_ip ?? ''));

        if ($lockedIp === '') {
            $attempt->forceFill(['ip_address' => $ip])->save();
        } elseif ($lockedIp !== $ip) {
            if ($allowedIp !== '' && $allowedIp === $ip) {
                $attempt->forceFill(['ip_address' => $ip, 'allowed_ip' => null])->save();
            } else {
                $this->addError('admissionNumber', 'This attempt is locked to another device/IP. Ask an admin to update your IP or reset your attempt.');
                return;
            }
        }

        return redirect()->route('cbt.student.take', ['attempt' => $attempt, 'code' => $code]);
    }

    public function render()
    {
        return view('livewire.cbt.portal.start');
    }
}

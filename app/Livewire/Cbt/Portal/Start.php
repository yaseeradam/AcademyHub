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
use Livewire\Attributes\Computed;

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

        // Pre-fill admission number when a student is already logged in.
        if (session('login_type') === 'student' && session('student_admission')) {
            $this->admissionNumber = (string) session('student_admission');
        }
    }

    #[Computed]
    public function exam()
    {
        if (trim($this->examCode) === '') {
            return null;
        }
        return CbtExam::query()
            ->where('access_code', strtoupper(trim($this->examCode)))
            ->first();
    }

    public function start()
    {
        $data = $this->validate([
            'examCode' => ['required', 'string', 'max:32'],
            'admissionNumber' => ['required', 'string', 'max:100'],
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

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $cbtPlugin = null;
        if ($tenant) {
            $cbtPlugin = $tenant->activeMarketplaceComponents()->where('slug', 'cbt')->first();
            if (! $cbtPlugin) {
                $this->addError('examCode', 'The CBT feature is not active for this school.');
                return;
            }
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

        if ($exam->exam_type === 'aptitude') {
            // Aptitude tests: no Student record needed, store name on the attempt
            $candidateName = trim(preg_replace('/\s+/', ' ', $admission));

            if ($candidateName === '') {
                $this->addError('admissionNumber', 'Please enter your full name.');
                return;
            }

            // Look for an existing attempt for this exam by this candidate name
            $attempt = CbtAttempt::query()
                ->where('exam_id', $exam->id)
                ->whereRaw('LOWER(candidate_name) = ?', [mb_strtolower($candidateName)])
                ->first();

            // Also try swapped name order (e.g. "John Doe" vs "Doe John")
            if (! $attempt) {
                $parts = explode(' ', $candidateName, 2);
                if (isset($parts[1])) {
                    $swapped = trim($parts[1]) . ' ' . trim($parts[0]);
                    $attempt = CbtAttempt::query()
                        ->where('exam_id', $exam->id)
                        ->whereRaw('LOWER(candidate_name) = ?', [mb_strtolower($swapped)])
                        ->first();
                }
            }

            if (! $attempt) {
                try {
                    $attempt = CbtAttempt::query()->create([
                        'uuid' => (string) Str::uuid(),
                        'exam_id' => $exam->id,
                        'student_id' => null,
                        'candidate_name' => $candidateName,
                        'started_at' => now(),
                        'last_activity_at' => now(),
                        'ip_address' => $ip,
                    ]);
                } catch (QueryException) {
                    $attempt = CbtAttempt::query()
                        ->where('exam_id', $exam->id)
                        ->whereRaw('LOWER(candidate_name) = ?', [mb_strtolower($candidateName)])
                        ->first();
                }
            }

            abort_unless($attempt, 500);

            // Establish a session so the student.session middleware allows access
            session([
                'tenant_id'         => $exam->tenant_id ?? \App\Support\TenantSettings::tenantId(),
                'student_id'        => $attempt->id, // Use attempt ID as pseudo-student for session
                'student_name'      => $candidateName,
                'student_admission' => 'APT-' . strtoupper(substr(md5($candidateName), 0, 6)),
                'student_class'     => 'Aptitude',
                'login_type'        => 'aptitude',
                'aptitude_attempt_id' => $attempt->id,
            ]);

            if ($attempt->terminated_at) {
                $this->addError('admissionNumber', 'Your attempt was terminated by an admin.');
                return;
            }

            if (! $attempt->started_at) {
                $attempt->forceFill(['started_at' => now()])->save();
            }

            $lockedIp = trim((string) ($attempt->ip_address ?? ''));
            $allowedIp = trim((string) ($attempt->allowed_ip ?? ''));
            $isLocalOrLoopback = !config('academyhub.cbt_ip_lock', true) ||
                                app()->environment('local', 'testing') ||
                                (($lockedIp === '127.0.0.1' || $lockedIp === '::1' || $lockedIp === '') &&
                                 ($ip === '127.0.0.1' || $ip === '::1'));

            if ($lockedIp === '' || $isLocalOrLoopback) {
                if ($lockedIp !== $ip) {
                    $attempt->forceFill(['ip_address' => $ip])->save();
                }
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

        // Academic exams: require a valid Student record
        $student = Student::query()->where('admission_number', $admission)->first();
        if (! $student || $student->status !== 'Active') {
            $this->addError('admissionNumber', 'Student not found or inactive.');
            return;
        }

        if ($tenant && $cbtPlugin) {
            $allowedClassIds = $cbtPlugin->pivot->allowed_class_ids ?? [];
            if (is_string($allowedClassIds)) {
                $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
            }
            $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];
            $studentClassId = (string) $student->class_id;
            $allowedClassIds = array_map('strval', $allowedClassIds);
            if (!in_array($studentClassId, $allowedClassIds, true)) {
                $this->addError('admissionNumber', 'The CBT feature is not active for your class.');
                return;
            }
        }

        if ($surname !== '' && strcasecmp(trim((string) $student->last_name), $surname) !== 0) {
            $this->addError('surname', 'Surname does not match this admission number.');
            return;
        }

        if ((int) $student->class_id !== (int) $exam->class_id) {
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

        // Establish student session so they can access the exam take page
        session([
            'tenant_id'         => $student->tenant_id ?? \App\Support\TenantSettings::tenantId(),
            'student_id'        => $student->id,
            'student_name'      => $student->full_name,
            'student_admission' => $student->admission_number,
            'student_class'     => $student->schoolClass?->name,
            'login_type'        => 'student',
        ]);

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
        $isLocalOrLoopback = !config('academyhub.cbt_ip_lock', true) ||
                            app()->environment('local', 'testing') ||
                            (($lockedIp === '127.0.0.1' || $lockedIp === '::1' || $lockedIp === '') &&
                             ($ip === '127.0.0.1' || $ip === '::1'));

        if ($lockedIp === '' || $isLocalOrLoopback) {
            if ($lockedIp !== $ip) {
                $attempt->forceFill(['ip_address' => $ip])->save();
            }
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

<?php

namespace App\Livewire\Imports;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Layout('layouts.app')]
#[Title('Import Students')]
class Students extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    public bool $createMissingClasses = false;
    public bool $createMissingSections = false;
    public bool $updateExisting = false;

    public array $summary       = [];
    public array $errorsPreview = [];

    // AI confirmation state
    public bool  $showConfirmation = false;
    public bool  $aiAnalyzing      = false;
    public array $aiRows           = [];   // AI-normalized rows ready to import
    public array $aiChanges        = [];   // list of human-readable change descriptions
    public array $aiRemoved        = [];   // rows AI decided to drop (with reason)

    public function updatedFile(): void
    {
        $this->reset(['summary', 'errorsPreview', 'showConfirmation', 'aiRows', 'aiChanges', 'aiRemoved']);
    }

    // ─── Analyze (dry-run) ────────────────────────────────────────────────────

    public function analyze(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        [$summary, $errors] = $this->parseFile(dryRun: true);
        $this->summary       = $summary;
        $this->errorsPreview = array_slice($errors, 0, 10);
    }

    // ─── AI Analyze ───────────────────────────────────────────────────────────

    public function analyzeWithAI(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        $this->aiAnalyzing = true;
        $this->reset(['aiRows', 'aiChanges', 'aiRemoved', 'showConfirmation', 'summary', 'errorsPreview']);

        // Read raw rows from file (no validation)
        $rawRows = $this->readRawRows();

        if (empty($rawRows)) {
            $this->aiAnalyzing = false;
            throw ValidationException::withMessages(['file' => 'Could not read any rows from the file.']);
        }

        // Build a compact CSV sample for the AI (max 100 rows to stay within token limits)
        $sample    = array_slice($rawRows, 0, 100);
        $csvSample = $this->rowsToCsvString($sample);

        $prompt = <<<PROMPT
You are a data cleaning assistant for a school management system.

The required columns for student import are:
admission_number, first_name, last_name, gender (Male/Female), class_name, section_name, dob (YYYY-MM-DD or blank), blood_group, guardian_name, guardian_phone, guardian_address, status (Active/Graduated/Expelled or blank)

Here is the uploaded file data (CSV format):
{$csvSample}

Your task:
1. Map the file's columns to the required columns (handle typos, alternate names, different order).
2. Normalize values: capitalize names, fix gender to "Male"/"Female", fix status to "Active"/"Graduated"/"Expelled", format dob to YYYY-MM-DD.
3. Remove rows that are completely empty or clearly invalid (e.g. header duplicates, test data).
4. For each change you make, add a short human-readable description.
5. For each removed row, explain why.

Respond ONLY with valid JSON in this exact structure (no markdown, no explanation outside JSON):
{
  "rows": [
    {"admission_number":"","first_name":"","last_name":"","gender":"","class_name":"","section_name":"","dob":"","blood_group":"","guardian_name":"","guardian_phone":"","guardian_address":"","status":""}
  ],
  "changes": ["description of change 1", "description of change 2"],
  "removed": [
    {"row": 3, "data": "...", "reason": "..."}
  ]
}
PROMPT;

        $result = $this->callAI($prompt);

        if (! $result) {
            $this->aiAnalyzing = false;
            throw ValidationException::withMessages(['file' => 'AI service unavailable. Use standard Analyze instead.']);
        }

        // Strip markdown code fences if present
        $result = preg_replace('/^```(?:json)?\s*/i', '', trim($result));
        $result = preg_replace('/\s*```$/', '', $result);

        $decoded = json_decode($result, true);

        if (! is_array($decoded) || ! isset($decoded['rows'])) {
            $this->aiAnalyzing = false;
            throw ValidationException::withMessages(['file' => 'AI returned an unexpected response. Try again.']);
        }

        $this->aiRows    = $decoded['rows']    ?? [];
        $this->aiChanges = $decoded['changes'] ?? [];
        $this->aiRemoved = $decoded['removed'] ?? [];

        // Build summary counts
        $adms     = collect($this->aiRows)->pluck('admission_number')->filter()->unique()->values();
        $existing = $adms->isEmpty() ? collect() : Student::query()->whereIn('admission_number', $adms)->pluck('admission_number');

        $this->summary = [
            'rows_valid'        => count($this->aiRows),
            'to_create'         => max(0, $adms->count() - $existing->count()),
            'to_update'         => $this->updateExisting ? $existing->count() : 0,
            'to_skip_existing'  => $this->updateExisting ? 0 : $existing->count(),
            'errors'            => count($this->aiRemoved),
        ];

        $this->aiAnalyzing     = false;
        $this->showConfirmation = true;
    }

    // ─── Confirm AI import ────────────────────────────────────────────────────

    public function confirmAiImport(): void
    {
        if (empty($this->aiRows)) {
            throw ValidationException::withMessages(['file' => 'No AI-processed rows to import.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            foreach ($this->aiRows as $row) {
                $row = array_map('trim', $row);
                if (empty($row['admission_number']) || empty($row['first_name']) || empty($row['last_name']) || empty($row['class_name']) || empty($row['section_name'])) {
                    continue;
                }

                $class   = $this->resolveClass($row['class_name']);
                $section = $this->resolveSection($class->id, $row['section_name']);

                $payload = [
                    'first_name'       => $row['first_name'],
                    'last_name'        => $row['last_name'],
                    'class_id'         => (int) $class->id,
                    'section_id'       => (int) $section->id,
                    'gender'           => $row['gender'] ?? '',
                    'dob'              => $row['dob'] ?: null,
                    'blood_group'      => $row['blood_group'] ?: null,
                    'guardian_name'    => $row['guardian_name'] ?: null,
                    'guardian_phone'   => $row['guardian_phone'] ?: null,
                    'guardian_address' => $row['guardian_address'] ?: null,
                    'status'           => $row['status'] ?: 'Active',
                ];

                $existing = Student::query()->where('admission_number', $row['admission_number'])->first();
                if ($existing) {
                    if ($this->updateExisting) {
                        $existing->fill($payload)->save();
                    }
                    continue;
                }

                Student::query()->create(['admission_number' => strtoupper($row['admission_number'])] + $payload);
            }
        });

        $this->reset(['aiRows', 'aiChanges', 'aiRemoved', 'showConfirmation', 'summary', 'errorsPreview', 'file']);
        $this->dispatch('alert', message: 'Students imported successfully via AI.', type: 'success');
    }

    public function cancelAiImport(): void
    {
        $this->reset(['showConfirmation', 'aiRows', 'aiChanges', 'aiRemoved']);
    }

    // ─── Standard Import ──────────────────────────────────────────────────────

    public function import(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        [$summary, $errors, $rows] = $this->parseFile(dryRun: false, returnRows: true);
        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => 'Fix errors before importing.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $class   = $this->resolveClass($row['class_name']);
                $section = $this->resolveSection($class->id, $row['section_name']);

                $payload = [
                    'first_name'       => $row['first_name'],
                    'last_name'        => $row['last_name'],
                    'class_id'         => (int) $class->id,
                    'section_id'       => (int) $section->id,
                    'gender'           => $row['gender'],
                    'dob'              => $row['dob'] ?: null,
                    'blood_group'      => $row['blood_group'] ?: null,
                    'guardian_name'    => $row['guardian_name'] ?: null,
                    'guardian_phone'   => $row['guardian_phone'] ?: null,
                    'guardian_address' => $row['guardian_address'] ?: null,
                    'status'           => $row['status'] ?: 'Active',
                ];

                $existing = Student::query()->where('admission_number', $row['admission_number'])->first();
                if ($existing) {
                    if ($this->updateExisting) {
                        $existing->fill($payload)->save();
                    }
                    continue;
                }

                Student::query()->create(['admission_number' => $row['admission_number']] + $payload);
            }
        });

        $this->dispatch('alert', message: 'Students imported.', type: 'success');
    }

    // ─── File Parsing ─────────────────────────────────────────────────────────

    /**
     * @return array{0:array,1:array,2?:array<int,array<string,string>>}
     */
    private function parseFile(bool $dryRun, bool $returnRows = false): array
    {
        $path = $this->file?->getRealPath();
        if (! $path) {
            throw ValidationException::withMessages(['file' => 'Invalid upload.']);
        }

        $ext = strtolower($this->file->getClientOriginalExtension());

        $allRows = in_array($ext, ['xlsx', 'xls', 'ods'])
            ? $this->readSpreadsheetRows($path)
            : $this->readCsvRows($path);

        if (empty($allRows)) {
            throw ValidationException::withMessages(['file' => 'File has no data rows.']);
        }

        // First row = headers
        $rawHeaders = array_shift($allRows);
        $headers    = array_map(fn ($h) => Str::of((string) $h)->trim()->lower()->toString(), $rawHeaders);
        $map        = array_flip($headers);

        $required = ['admission_number', 'first_name', 'last_name', 'gender', 'class_name', 'section_name'];
        foreach ($required as $col) {
            if (! array_key_exists($col, $map)) {
                throw ValidationException::withMessages(['file' => "Missing column: {$col}"]);
            }
        }

        $optional = ['dob', 'blood_group', 'guardian_name', 'guardian_phone', 'guardian_address', 'status'];
        $errors   = [];
        $rows     = [];
        $line     = 1;

        foreach ($allRows as $data) {
            $line++;
            if (! is_array($data) || $data === [] || implode('', $data) === '') {
                continue;
            }

            $get = fn (string $key) => isset($map[$key]) ? trim((string) ($data[$map[$key]] ?? '')) : '';
            $row = [
                'admission_number' => strtoupper($get('admission_number')),
                'first_name'       => $get('first_name'),
                'last_name'        => $get('last_name'),
                'gender'           => ucfirst(strtolower($get('gender'))),
                'class_name'       => $get('class_name'),
                'section_name'     => strtoupper($get('section_name')),
            ];

            foreach ($optional as $col) {
                $row[$col] = $get($col);
            }

            if ($row['admission_number'] === '' || $row['first_name'] === '' || $row['last_name'] === '' || $row['class_name'] === '' || $row['section_name'] === '') {
                $errors[] = "Line {$line}: missing required fields.";
                continue;
            }

            if (! in_array($row['gender'], ['Male', 'Female'], true)) {
                $errors[] = "Line {$line}: gender must be Male or Female.";
                continue;
            }

            if ($row['status'] !== '' && ! in_array($row['status'], ['Active', 'Graduated', 'Expelled'], true)) {
                $errors[] = "Line {$line}: invalid status.";
                continue;
            }

            if ($dryRun) {
                $class = SchoolClass::query()->where('name', $row['class_name'])->first();
                if (! $class && ! $this->createMissingClasses) {
                    $errors[] = "Line {$line}: class not found ({$row['class_name']}).";
                    continue;
                }
                if ($class) {
                    $section = Section::query()->where('class_id', $class->id)->where('name', $row['section_name'])->first();
                    if (! $section && ! $this->createMissingSections) {
                        $errors[] = "Line {$line}: section not found ({$row['section_name']}) for class {$row['class_name']}.";
                        continue;
                    }
                }
            }

            $rows[] = $row;
        }

        $adms     = collect($rows)->pluck('admission_number')->filter()->unique()->values();
        $existing = $adms->isEmpty() ? collect() : Student::query()->whereIn('admission_number', $adms)->pluck('admission_number');
        $toUpdate = $existing->count();
        $toCreate = max(0, $adms->count() - $toUpdate);

        $summary = [
            'rows_valid'       => count($rows),
            'to_create'        => $toCreate,
            'to_update'        => $this->updateExisting ? $toUpdate : 0,
            'to_skip_existing' => $this->updateExisting ? 0 : $toUpdate,
            'errors'           => count($errors),
        ];

        if ($returnRows) {
            return [$summary, $errors, $rows];
        }

        return [$summary, $errors];
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw ValidationException::withMessages(['file' => 'Unable to read file.']);
        }
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }

    private function readSpreadsheetRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = [];

        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getFormattedValue();
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    private function readRawRows(): array
    {
        $path = $this->file?->getRealPath();
        if (! $path) {
            return [];
        }

        $ext = strtolower($this->file->getClientOriginalExtension());

        return in_array($ext, ['xlsx', 'xls', 'ods'])
            ? $this->readSpreadsheetRows($path)
            : $this->readCsvRows($path);
    }

    private function rowsToCsvString(array $rows): string
    {
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row));
        }
        return implode("\n", $lines);
    }

    // ─── AI Helpers ───────────────────────────────────────────────────────────

    private function callAI(string $prompt): ?string
    {
        return $this->tryGeminiAPI($prompt) ?? $this->tryGroqAPI($prompt);
    }

    private function tryGeminiAPI(string $prompt): ?string
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            if (empty($apiKey)) {
                return null;
            }

            $response = Http::withOptions(['verify' => false])
                ->timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            return $response->successful()
                ? ($response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null)
                : null;
        } catch (\Exception $e) {
            Log::warning('Gemini API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function tryGroqAPI(string $prompt): ?string
    {
        try {
            $apiKey = env('GROQ_API_KEY');
            if (empty($apiKey)) {
                return null;
            }

            // Support comma-separated keys — pick the first one
            $key = trim(explode(',', $apiKey)[0]);

            $response = Http::withOptions(['verify' => false])
                ->timeout(60)
                ->withHeaders(['Authorization' => "Bearer {$key}", 'Content-Type' => 'application/json'])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.3-70b-versatile',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'You are a data cleaning assistant. Always respond with valid JSON only, no markdown.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens'  => 4000,
                ]);

            return $response->successful()
                ? ($response->json()['choices'][0]['message']['content'] ?? null)
                : null;
        } catch (\Exception $e) {
            Log::error('Groq API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ─── Class / Section Resolution ───────────────────────────────────────────

    private function resolveClass(string $name): SchoolClass
    {
        $class = SchoolClass::query()->where('name', $name)->first();
        if ($class) {
            return $class;
        }

        if (! $this->createMissingClasses) {
            throw ValidationException::withMessages(['file' => "Class not found: {$name}"]);
        }

        return SchoolClass::query()->create(['name' => $name, 'level' => 1]);
    }

    private function resolveSection(int $classId, string $name): Section
    {
        $section = Section::query()->where('class_id', $classId)->where('name', $name)->first();
        if ($section) {
            return $section;
        }

        if (! $this->createMissingSections) {
            throw ValidationException::withMessages(['file' => "Section not found: {$name}"]);
        }

        return Section::query()->create(['class_id' => $classId, 'name' => $name]);
    }

    public function render()
    {
        return view('livewire.imports.students');
    }
}

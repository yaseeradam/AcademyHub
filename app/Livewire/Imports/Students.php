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
    public bool  $showChanges      = false; // toggle for showing AI changes
    public bool  $showChat         = false; // toggle for AI chat
    public string $chatMessage     = '';    // user's chat message
    public array $chatHistory      = [];    // chat conversation history
    public bool  $chatProcessing   = false; // chat processing state

    public function updatedFile(): void
    {
        $this->reset(['summary', 'errorsPreview', 'showConfirmation', 'aiRows', 'aiChanges', 'aiRemoved', 'showChanges', 'showChat', 'chatHistory']);
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
        $this->reset(['aiRows', 'aiChanges', 'aiRemoved', 'showConfirmation', 'summary', 'errorsPreview', 'showChanges', 'showChat', 'chatHistory']);

        // Read raw rows from file (no validation)
        $rawRows = $this->readRawRows();

        if (empty($rawRows)) {
            $this->aiAnalyzing = false;
            throw ValidationException::withMessages(['file' => 'Could not read any rows from the file.']);
        }

        // Analyze existing data patterns
        $existingPatterns = $this->analyzeExistingDataPatterns();
        
        // Build a compact CSV sample for the AI (max 100 rows to stay within token limits)
        $sample    = array_slice($rawRows, 0, 100);
        $csvSample = $this->rowsToCsvString($sample);

        $prompt = $this->buildEnhancedAIPrompt($csvSample, $existingPatterns);

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
                // Handle custom_fields separately since it's an array
                $customFields = $row['custom_fields'] ?? [];
                unset($row['custom_fields']);
                
                // Trim string values only
                $row = array_map(function($value) {
                    return is_string($value) ? trim($value) : $value;
                }, $row);
                
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
                    'custom_fields'    => $customFields,
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
        $this->reset(['showConfirmation', 'aiRows', 'aiChanges', 'aiRemoved', 'showChanges', 'showChat', 'chatHistory']);
    }

    // ─── Inline Editing ──────────────────────────────────────────────────────

    public function updateCell(int $rowIndex, string $field, string $value): void
    {
        if (isset($this->aiRows[$rowIndex])) {
            $this->aiRows[$rowIndex][$field] = $value;
            
            // Add to changes log
            $this->aiChanges[] = "Manual edit: Updated {$field} in row " . ($rowIndex + 1) . " to '{$value}'";
            
            // Recalculate summary
            $adms = collect($this->aiRows)->pluck('admission_number')->filter()->unique()->values();
            $existing = $adms->isEmpty() ? collect() : Student::query()->whereIn('admission_number', $adms)->pluck('admission_number');
            
            $this->summary = [
                'rows_valid'        => count($this->aiRows),
                'to_create'         => max(0, $adms->count() - $existing->count()),
                'to_update'         => $this->updateExisting ? $existing->count() : 0,
                'to_skip_existing'  => $this->updateExisting ? 0 : $existing->count(),
                'errors'            => count($this->aiRemoved),
            ];
        }
    }

    // ─── AI Chat ─────────────────────────────────────────────────────────────

    public function toggleChat(): void
    {
        $this->showChat = !$this->showChat;
        if (!$this->showChat) {
            $this->chatMessage = '';
        }
    }

    public function sendChatMessage(): void
    {
        if (empty(trim($this->chatMessage ?? ''))) {
            return;
        }

        $this->chatProcessing = true;
        $userMessage = trim($this->chatMessage ?? '');
        $this->chatMessage = '';
        
        // Add user message to history
        $this->chatHistory[] = [
            'type' => 'user',
            'message' => $userMessage,
            'timestamp' => now()->format('H:i')
        ];

        // Build context for AI
        $context = $this->buildChatContext($userMessage);
        $prompt = $this->buildChatPrompt($userMessage, $context);
        
        $result = $this->callAI($prompt);
        
        if (!$result) {
            $this->chatHistory[] = [
                'type' => 'ai',
                'message' => 'Sorry, I\'m having trouble connecting to the AI service right now. Please try again.',
                'timestamp' => now()->format('H:i')
            ];
            $this->chatProcessing = false;
            return;
        }

        // Parse AI response
        $this->processChatResponse($result, $userMessage);
        $this->chatProcessing = false;
    }

    private function buildChatContext(string $userMessage): array
    {
        return [
            'total_rows' => count($this->aiRows),
            'sample_data' => array_slice($this->aiRows, 0, 3),
            'existing_patterns' => $this->analyzeExistingDataPatterns(),
            'recent_changes' => array_slice($this->aiChanges, -5),
        ];
    }

    private function buildChatPrompt(string $userMessage, array $context): string
    {
        $sampleData = json_encode($context['sample_data'], JSON_PRETTY_PRINT);
        $recentChanges = implode("\n", $context['recent_changes']);
        
        return <<<PROMPT
You are an AI assistant helping with student data import. The user has {$context['total_rows']} rows of student data ready for import.

CURRENT DATA SAMPLE:
{$sampleData}

RECENT CHANGES MADE:
{$recentChanges}

USER REQUEST: {$userMessage}

If the user wants to modify the data format or structure, respond with JSON in this format:
{
  "action": "modify_data",
  "changes": [
    {"row_index": 0, "field": "admission_number", "new_value": "NEW001", "reason": "Changed format as requested"}
  ],
  "message": "I've updated the admission number format as requested."
}

If it's just a question or conversation, respond with:
{
  "action": "chat",
  "message": "Your response here"
}

Respond ONLY with valid JSON, no markdown.
PROMPT;
    }

    private function processChatResponse(string $result, string $userMessage): void
    {
        // Strip markdown if present
        $result = preg_replace('/^```(?:json)?\s*/i', '', trim($result));
        $result = preg_replace('/\s*```$/', '', $result);
        
        $decoded = json_decode($result, true);
        
        if (!is_array($decoded)) {
            $this->chatHistory[] = [
                'type' => 'ai',
                'message' => 'I understand your request, but I\'m having trouble processing it right now. Could you try rephrasing?',
                'timestamp' => now()->format('H:i')
            ];
            return;
        }

        $action = $decoded['action'] ?? 'chat';
        $message = $decoded['message'] ?? 'Done!';
        
        if ($action === 'modify_data' && isset($decoded['changes'])) {
            // Apply the changes
            foreach ($decoded['changes'] as $change) {
                $rowIndex = $change['row_index'] ?? null;
                $field = $change['field'] ?? null;
                $newValue = $change['new_value'] ?? '';
                $reason = $change['reason'] ?? 'AI modification';
                
                if ($rowIndex !== null && $field && isset($this->aiRows[$rowIndex])) {
                    $this->aiRows[$rowIndex][$field] = $newValue;
                    $this->aiChanges[] = "AI Chat: {$reason} (Row " . ($rowIndex + 1) . ", {$field})";
                }
            }
            
            // Recalculate summary
            $adms = collect($this->aiRows)->pluck('admission_number')->filter()->unique()->values();
            $existing = $adms->isEmpty() ? collect() : Student::query()->whereIn('admission_number', $adms)->pluck('admission_number');
            
            $this->summary = [
                'rows_valid'        => count($this->aiRows),
                'to_create'         => max(0, $adms->count() - $existing->count()),
                'to_update'         => $this->updateExisting ? $existing->count() : 0,
                'to_skip_existing'  => $this->updateExisting ? 0 : $existing->count(),
                'errors'            => count($this->aiRemoved),
            ];
        }
        
        $this->chatHistory[] = [
            'type' => 'ai',
            'message' => $message,
            'timestamp' => now()->format('H:i')
        ];
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
                    'custom_fields'    => $row['custom_fields'] ?? [],
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
        $allKnownColumns = array_merge($required, $optional);
        $customColumns = array_diff($headers, $allKnownColumns);
        
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

            // Handle custom fields
            $customFields = [];
            foreach ($customColumns as $customCol) {
                $value = $get($customCol);
                if ($value !== '') {
                    $customFields[$customCol] = $value;
                }
            }
            $row['custom_fields'] = $customFields;

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

    private function analyzeExistingDataPatterns(): array
    {
        // Get existing students to understand patterns
        $existingStudents = Student::query()
            ->with(['schoolClass', 'section'])
            ->limit(100)
            ->get();

        $patterns = [
            'admission_number_format' => [],
            'class_names' => [],
            'section_names' => [],
            'custom_fields' => [],
            'next_admission_number' => null,
        ];

        if ($existingStudents->isEmpty()) {
            return $patterns;
        }

        // Analyze admission number patterns
        $admissionNumbers = $existingStudents->pluck('admission_number')->filter();
        if ($admissionNumbers->isNotEmpty()) {
            // Find common patterns like STU2024001, 2024001, etc.
            $patterns['admission_number_format'] = $this->detectAdmissionNumberPattern($admissionNumbers);
            $patterns['next_admission_number'] = $this->generateNextAdmissionNumber($admissionNumbers);
        }

        // Get existing class and section names
        $patterns['class_names'] = $existingStudents->pluck('schoolClass.name')->filter()->unique()->values()->toArray();
        $patterns['section_names'] = $existingStudents->pluck('section.name')->filter()->unique()->values()->toArray();

        // Analyze custom fields usage
        $customFields = $existingStudents->pluck('custom_fields')->filter()->flatten(1);
        if ($customFields->isNotEmpty()) {
            $patterns['custom_fields'] = array_keys($customFields->toArray());
        }

        return $patterns;
    }

    private function detectAdmissionNumberPattern(\Illuminate\Support\Collection $admissionNumbers): array
    {
        $patterns = [];
        
        foreach ($admissionNumbers as $number) {
            // Check for common patterns
            if (preg_match('/^([A-Z]+)(\d{4})(\d+)$/', $number, $matches)) {
                $patterns[] = [
                    'type' => 'prefix_year_sequence',
                    'prefix' => $matches[1],
                    'year' => $matches[2],
                    'example' => $number
                ];
            } elseif (preg_match('/^(\d{4})(\d+)$/', $number, $matches)) {
                $patterns[] = [
                    'type' => 'year_sequence',
                    'year' => $matches[1],
                    'example' => $number
                ];
            } elseif (preg_match('/^([A-Z]+)(\d+)$/', $number, $matches)) {
                $patterns[] = [
                    'type' => 'prefix_sequence',
                    'prefix' => $matches[1],
                    'example' => $number
                ];
            }
        }

        // Return the most common pattern
        $patternCounts = array_count_values(array_column($patterns, 'type'));
        $mostCommon = array_key_first($patternCounts);
        
        return array_filter($patterns, fn($p) => $p['type'] === $mostCommon);
    }

    private function generateNextAdmissionNumber(\Illuminate\Support\Collection $admissionNumbers): ?string
    {
        $currentYear = date('Y');
        $patterns = $this->detectAdmissionNumberPattern($admissionNumbers);
        
        if (empty($patterns)) {
            return "STU{$currentYear}001";
        }

        $pattern = $patterns[0];
        
        switch ($pattern['type']) {
            case 'prefix_year_sequence':
                $prefix = $pattern['prefix'];
                $matching = $admissionNumbers->filter(fn($num) => str_starts_with($num, $prefix . $currentYear));
                $maxNumber = $matching->map(fn($num) => (int)substr($num, strlen($prefix . $currentYear)))->max() ?? 0;
                return $prefix . $currentYear . str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
                
            case 'year_sequence':
                $matching = $admissionNumbers->filter(fn($num) => str_starts_with($num, $currentYear));
                $maxNumber = $matching->map(fn($num) => (int)substr($num, 4))->max() ?? 0;
                return $currentYear . str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
                
            case 'prefix_sequence':
                $prefix = $pattern['prefix'];
                $matching = $admissionNumbers->filter(fn($num) => str_starts_with($num, $prefix));
                $maxNumber = $matching->map(fn($num) => (int)substr($num, strlen($prefix)))->max() ?? 0;
                return $prefix . str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return null;
    }

    private function buildEnhancedAIPrompt(string $csvSample, array $patterns): string
    {
        $customFieldsInfo = empty($patterns['custom_fields']) 
            ? "No custom fields are currently used."
            : "Custom fields in use: " . implode(', ', $patterns['custom_fields']);

        $admissionNumberInfo = "";
        if (!empty($patterns['admission_number_format'])) {
            $format = $patterns['admission_number_format'][0];
            $admissionNumberInfo = "\nExisting admission number pattern: {$format['type']} (example: {$format['example']})";
            if ($patterns['next_admission_number']) {
                $admissionNumberInfo .= "\nNext suggested admission number: {$patterns['next_admission_number']}";
            }
        }

        $classInfo = empty($patterns['class_names']) 
            ? "No existing classes found."
            : "Existing classes: " . implode(', ', $patterns['class_names']);

        $sectionInfo = empty($patterns['section_names']) 
            ? "No existing sections found."
            : "Existing sections: " . implode(', ', $patterns['section_names']);

        return <<<PROMPT
You are an intelligent data cleaning assistant for a school management system.

SCHOOL DATA CONTEXT:
{$classInfo}
{$sectionInfo}
{$customFieldsInfo}{$admissionNumberInfo}

REQUIRED COLUMNS:
admission_number, first_name, last_name, gender (Male/Female), class_name, section_name, dob (YYYY-MM-DD or blank), blood_group, guardian_name, guardian_phone, guardian_address, status (Active/Graduated/Expelled or blank)

UPLOADED FILE DATA (CSV format):
{$csvSample}

INTELLIGENT PROCESSING TASKS:
1. COLUMN MAPPING: Map file columns to required columns (handle typos, alternate names, different order)
2. PATTERN RECOGNITION: Follow existing admission number patterns when generating missing numbers
3. DATA NORMALIZATION: 
   - Capitalize names properly (Title Case)
   - Fix gender to "Male"/"Female" 
   - Fix status to "Active"/"Graduated"/"Expelled"
   - Format dates to YYYY-MM-DD
   - Standardize phone numbers
4. SMART COMPLETION:
   - Generate missing admission numbers following the detected pattern
   - Auto-complete missing but inferrable data
   - Map class/section names to existing ones when similar
5. CUSTOM FIELDS: Extract any additional columns not in the required list into custom_fields object
6. DATA CLEANING: Remove empty rows, duplicates, test data, header repeats

Respond ONLY with valid JSON (no markdown, no explanation outside JSON):
{
  "rows": [
    {"admission_number":"","first_name":"","last_name":"","gender":"","class_name":"","section_name":"","dob":"","blood_group":"","guardian_name":"","guardian_phone":"","guardian_address":"","status":"","custom_fields":{}}
  ],
  "changes": ["Generated missing admission number STU2024001", "Capitalized name 'john doe' to 'John Doe'", "Added phone number to custom fields"],
  "removed": [
    {"row": 3, "data": "empty row", "reason": "Row was completely empty"}
  ]
}
PROMPT;
    }

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

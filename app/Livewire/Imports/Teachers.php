<?php

namespace App\Livewire\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Layout('layouts.app')]
#[Title('Import Teachers')]
class Teachers extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;

    // Wizard options
    public bool $updateExisting = false;
    public bool $defaultActive = true;

    // Wizard state
    public int $step = 1; // 1 = Upload, 2 = Map, 3 = Summary
    public bool $aiAnalyzing = false;
    public array $headers = [];
    public array $sampleRows = [];
    public array $columnMapping = []; // field_key => csv_header
    public array $importReport = []; // results summary

    // Standard import state
    public array $summary = [];
    public array $errorsPreview = [];

    public function updatedFile(): void
    {
        $this->reset(['summary', 'errorsPreview', 'headers', 'sampleRows', 'columnMapping', 'importReport']);
        $this->step = 1;
    }

    // ─── Standard (Dry Run) Analyze ───────────────────────────────────────────

    public function analyze(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240'],
        ]);

        try {
            $rawRows = $this->readRawRows();
            if (empty($rawRows)) {
                throw new \Exception('File has no data.');
            }

            $rawHeaders = array_shift($rawRows);
            $headers = array_map(fn($h) => Str::of((string)$h)->trim()->lower()->toString(), $rawHeaders);
            $map = array_flip($headers);

            // In standard analyze, we assume simple matching
            $mapping = [
                'name' => $this->findMatchingHeader($headers, 'name'),
                'email' => $this->findMatchingHeader($headers, 'email'),
                'password' => $this->findMatchingHeader($headers, 'password'),
                'is_active' => $this->findMatchingHeader($headers, 'active'),
            ];

            if (empty($mapping['name']) || empty($mapping['email'])) {
                throw new \Exception('CSV/Excel must contain name and email columns for Standard Analyze.');
            }

            $errors = [];
            $validCount = 0;
            $line = 1;

            foreach ($rawRows as $data) {
                $line++;
                if (!is_array($data) || empty(array_filter($data))) {
                    continue;
                }

                $get = function($field) use ($data, $map, $mapping) {
                    $header = $mapping[$field] ?? '';
                    return !empty($header) && isset($map[$header]) ? trim((string)($data[$map[$header]] ?? '')) : '';
                };

                $name = $get('name');
                $email = $get('email');

                if (empty($name) || empty($email)) {
                    $errors[] = "Line {$line}: Missing name or email.";
                    continue;
                }

                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[] = "Line {$line}: Invalid email format ({$email}).";
                    continue;
                }

                $validCount++;
            }

            $emails = collect($rawRows)->map(function($data) use ($map, $mapping) {
                $header = $mapping['email'] ?? '';
                return !empty($header) && isset($map[$header]) ? strtolower(trim((string)($data[$map[$header]] ?? ''))) : null;
            })->filter()->unique();

            $existing = $emails->isEmpty() ? collect() : User::query()->whereIn('email', $emails)->pluck('email');

            $this->summary = [
                'rows_valid' => $validCount,
                'to_create' => max(0, $emails->count() - $existing->count()),
                'to_update' => $this->updateExisting ? $existing->count() : 0,
                'to_skip_existing' => $this->updateExisting ? 0 : $existing->count(),
                'errors' => count($errors),
            ];

            $this->errorsPreview = array_slice($errors, 0, 10);
            
            // Set mapping so that standard import can proceed
            $this->columnMapping = $mapping;
            $this->headers = $headers;
            
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }
    }

    // ─── AI Map Columns ──────────────────────────────────────────────────────

    public function analyzeWithAI(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        $this->aiAnalyzing = true;
        $this->reset(['summary', 'errorsPreview', 'columnMapping', 'importReport']);

        $rawRows = $this->readRawRows();
        if (empty($rawRows)) {
            $this->aiAnalyzing = false;
            throw ValidationException::withMessages(['file' => 'Could not read any rows from the file.']);
        }

        $rawHeaders = array_shift($rawRows);
        $this->headers = array_values(array_filter(
            array_map(fn($h) => trim((string)$h), $rawHeaders),
            fn($h) => $h !== ''
        ));

        // Grab up to 5 non-empty sample rows
        $this->sampleRows = [];
        foreach ($rawRows as $row) {
            if (count($this->sampleRows) >= 5) break;
            if (is_array($row) && !empty(array_filter($row))) {
                $this->sampleRows[] = array_map(fn($v) => trim((string)$v), $row);
            }
        }

        $prompt = $this->buildMappingPrompt($this->headers, $this->sampleRows);
        $result = $this->callAI($prompt);

        if ($result) {
            $cleaned = preg_replace('/^```json\s*/i', '', trim($result));
            $cleaned = preg_replace('/```$/', '', $cleaned);
            $parsed = json_decode($cleaned, true);

            if (isset($parsed['column_mapping']) && is_array($parsed['column_mapping'])) {
                $this->columnMapping = $parsed['column_mapping'];
            }
        }

        if (empty($this->columnMapping)) {
            $this->columnMapping = [
                'name' => $this->findMatchingHeader($this->headers, 'name'),
                'email' => $this->findMatchingHeader($this->headers, 'email'),
                'password' => $this->findMatchingHeader($this->headers, 'password'),
                'is_active' => $this->findMatchingHeader($this->headers, 'active'),
            ];
        }

        $this->aiAnalyzing = false;
        $this->step = 2;
    }

    // ─── Execution ──────────────────────────────────────────────────────────

    public function import(): void
    {
        $this->validate([
            'columnMapping.name' => ['required', 'string'],
            'columnMapping.email' => ['required', 'string'],
        ]);

        $rawRows = $this->readRawRows();
        if (empty($rawRows)) {
            throw ValidationException::withMessages(['file' => 'File has no data.']);
        }

        $rawHeaders = array_shift($rawRows);
        $headers = array_map(fn($h) => trim((string)$h), $rawHeaders);
        $map = array_flip($headers);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;

        $mapping = $this->columnMapping;

        DB::transaction(function() use ($rawRows, $map, $mapping, &$created, &$updated, &$skipped, &$errors, &$line) {
            foreach ($rawRows as $data) {
                $line++;
                if (!is_array($data) || empty(array_filter($data))) {
                    continue;
                }

                $get = function($field) use ($data, $map, $mapping) {
                    $header = $mapping[$field] ?? '';
                    return !empty($header) && isset($map[$header]) ? trim((string)($data[$map[$header]] ?? '')) : '';
                };

                $name = $get('name');
                $email = strtolower($get('email'));
                $password = $get('password');
                $isActiveStr = $get('is_active');

                if (empty($name) || empty($email)) {
                    $errors[] = "Line {$line}: Missing required fields (Name or Email).";
                    continue;
                }

                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[] = "Line {$line}: Invalid email format ({$email}).";
                    continue;
                }

                $isActive = $this->defaultActive;
                if ($isActiveStr !== '') {
                    $isActive = filter_var($isActiveStr, FILTER_VALIDATE_BOOLEAN);
                }

                // Check if user exists
                $existing = User::query()->where('email', $email)->first();
                if ($existing) {
                    if ($this->updateExisting) {
                        $existing->name = $name;
                        $existing->is_active = $isActive;
                        if (!empty($password)) {
                            $existing->password = \Illuminate\Support\Facades\Hash::make($password);
                        }
                        $existing->save();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    continue;
                }

                User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'role' => 'teacher',
                    'is_active' => $isActive,
                    'password' => !empty($password) ? \Illuminate\Support\Facades\Hash::make($password) : \Illuminate\Support\Facades\Hash::make(Str::password(12)),
                ]);
                $created++;
            }
        });

        $this->importReport = [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
        $this->step = 3;
        $this->dispatch('alert', message: 'Import Complete.', type: 'success');
    }

    // ─── Excel / CSV Readers ──────────────────────────────────────────────────

    private function readCsvRows(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function readSpreadsheetRows(string $path): array
    {
        try {
            $spreadsheet = IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            return $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            Log::error('PhpSpreadsheet read error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function readRawRows(): array
    {
        $path = $this->file?->getRealPath();
        if (!$path) return [];

        $ext = strtolower($this->file->getClientOriginalExtension());
        return in_array($ext, ['xlsx', 'xls', 'ods'])
            ? $this->readSpreadsheetRows($path)
            : $this->readCsvRows($path);
    }

    // ─── AI Mapping Prompts ──────────────────────────────────────────────────

    private function buildMappingPrompt(array $headers, array $sampleRows): string
    {
        $headersJson = json_encode($headers);
        $sampleRowsJson = json_encode($sampleRows);

        return <<<PROMPT
You are an intelligent data mapper for a school management system.
We have an uploaded file with the following column headers:
{$headersJson}

Here is a sample of the first few rows of data (as arrays corresponding to the headers):
{$sampleRowsJson}

We need to map these columns to our system's standard teacher fields:
1. `name` (teacher's full name)
2. `email` (teacher's email address)
3. `password` (optional password for logging in)
4. `is_active` (optional status/active state, e.g. true, false, 1, 0, active, inactive)

INSTRUCTIONS:
1. Map each standard field to exactly ONE header from the file. If no column fits a field, map it to empty string "".
2. Respond ONLY with a valid JSON object in this format. Do not wrap it in markdown code blocks. Do not add any explanation:
{
  "column_mapping": {
    "name": "CSV_HEADER_OR_EMPTY",
    "email": "CSV_HEADER_OR_EMPTY",
    "password": "CSV_HEADER_OR_EMPTY",
    "is_active": "CSV_HEADER_OR_EMPTY"
  }
}
PROMPT;
    }

    private function callAI(string $prompt): ?string
    {
        return $this->tryGroqAPI($prompt);
    }

    private function tryGroqAPI(string $prompt): ?string
    {
        $rawKeys = config('services.groq.key');
        if (empty($rawKeys)) {
            return null;
        }

        $keys = array_filter(array_map('trim', explode(',', $rawKeys)));
        shuffle($keys);

        foreach ($keys as $key) {
            try {
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

                if ($response->successful()) {
                    $text = $response->json('choices.0.message.content');
                    if ($text) {
                        return $text;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Groq API error', ['error' => $e->getMessage()]);
            }
        }
        return null;
    }

    private function findMatchingHeader(array $headers, string $match): string
    {
        foreach ($headers as $h) {
            if (str_contains(strtolower($h), $match)) {
                return $h;
            }
        }
        return '';
    }

    public function render()
    {
        return view('livewire.imports.teachers');
    }
}

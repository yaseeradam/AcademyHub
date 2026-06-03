<?php

namespace App\Livewire\Imports;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\CustomField;
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

    // Wizard options
    public bool $createMissingClasses = true;
    public bool $createMissingSections = true;
    public bool $updateExisting = false;

    // Wizard state
    public int $step = 1; // 1 = Upload, 2 = Map, 3 = Summary
    public bool $aiAnalyzing = false;
    public array $headers = [];
    public array $sampleRows = [];
    public array $columnMapping = []; // field_key => csv_header
    public array $detectedCustomFields = []; // list of [csv_header, name, label, type]
    public array $customFieldToggles = []; // name => bool
    public array $importReport = []; // results summary

    // Standard import state
    public array $summary = [];
    public array $errorsPreview = [];

    public function updatedFile(): void
    {
        $this->reset(['summary', 'errorsPreview', 'headers', 'sampleRows', 'columnMapping', 'detectedCustomFields', 'customFieldToggles', 'importReport']);
        $this->step = 1;
    }

    // ─── Standard (Dry Run) Analyze ───────────────────────────────────────────

    public function analyze(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        try {
            [$summary, $errors] = $this->parseFile(dryRun: true);
            $this->summary       = $summary;
            $this->errorsPreview = array_slice($errors, 0, 10);
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }
    }

    // ─── AI Map Columns ──────────────────────────────────────────────────────

    public function analyzeWithAI(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        $this->aiAnalyzing = true;
        $this->reset(['summary', 'errorsPreview', 'columnMapping', 'detectedCustomFields', 'customFieldToggles', 'importReport']);

        $rawRows = $this->readRawRows();
        if (empty($rawRows)) {
            $this->aiAnalyzing = false;
            throw ValidationException::withMessages(['file' => 'Could not read any rows from the file.']);
        }

        $this->headers = array_values(array_filter(
            array_map(fn($h) => trim((string)$h), array_shift($rawRows)),
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

        // Ask AI which header maps to which standard field
        $prompt = $this->buildMappingPrompt($this->headers, $this->sampleRows);
        $result = $this->callAI($prompt);

        $decoded = null;
        if ($result) {
            $result = preg_replace('/^```(?:json)?\s*/i', '', trim($result));
            $result = preg_replace('/\s*```$/', '', $result);
            $decoded = json_decode($result, true);
        }

        // Build mapping: standard_field => csv_header (AI result or heuristic fallback)
        $aiMapping = (is_array($decoded) && isset($decoded['column_mapping']))
            ? $decoded['column_mapping']
            : $this->getHeuristicMapping($this->headers);

        // Clean invalid values
        foreach ($aiMapping as $field => $mappedHeader) {
            if ($mappedHeader !== '' && !in_array($mappedHeader, $this->headers)) {
                $aiMapping[$field] = '';
            }
        }

        // Ensure all standard fields exist in mapping
        $standardFields = [
            'admission_number', 'first_name', 'last_name', 'full_name', 'gender',
            'class_name', 'section_name', 'dob', 'blood_group',
            'guardian_name', 'guardian_phone', 'guardian_address', 'status'
        ];
        foreach ($standardFields as $field) {
            if (!isset($aiMapping[$field])) {
                $aiMapping[$field] = '';
            }
        }

        $this->columnMapping = $aiMapping;

        // All unmapped headers automatically become custom fields
        $this->recalculateCustomFields();
        // Auto-enable all detected custom fields (no toggle needed)
        foreach ($this->detectedCustomFields as $field) {
            $this->customFieldToggles[$field['name']] = true;
        }

        $this->aiAnalyzing = false;
        $this->step = 2;
    }

    // ─── Execute Import (PHP-Based Using Mapping) ─────────────────────────────

    public function importMappedData(): void
    {
        $path = $this->file?->getRealPath();
        if (! $path) {
            throw ValidationException::withMessages(['file' => 'Invalid file upload.']);
        }

        $allRows = $this->readRawRows();
        if (empty($allRows)) {
            throw ValidationException::withMessages(['file' => 'File is empty.']);
        }

        $rawHeaders = array_shift($allRows);
        $headers = array_map(fn ($h) => trim((string)$h), $rawHeaders);

        // Core fields mapping
        $fieldIndices = [];
        foreach ($this->columnMapping as $field => $mappedHeader) {
            if ($mappedHeader !== '') {
                $idx = array_search($mappedHeader, $headers);
                if ($idx !== false) {
                    $fieldIndices[$field] = $idx;
                }
            }
        }

        // Custom fields mapping
        $customFieldIndices = [];
        $customFieldsCreatedCount = 0;

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $classesCreatedCount = 0;
        $sectionsCreatedCount = 0;
        $errors = [];

        // Fetch patterns to auto-generate missing admission numbers
        $existingPatterns = $this->analyzeExistingDataPatterns();
        $nextAdmissionNum = $existingPatterns['next_admission_number'] ?: 'STU' . date('Y') . '0001';

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $allRows,
            $fieldIndices,
            $headers,
            &$customFieldIndices,
            &$customFieldsCreatedCount,
            &$createdCount,
            &$updatedCount,
            &$skippedCount,
            &$classesCreatedCount,
            &$sectionsCreatedCount,
            &$errors,
            &$nextAdmissionNum
        ) {
            // Delete all existing student custom fields for this school/tenant
            CustomField::query()->where('form_type', 'student')->delete();

            // Auto-provision custom fields in the DB first
            $order = 1;
            foreach ($this->detectedCustomFields as $field) {
                $fieldName = $field['name'];
                if ($this->customFieldToggles[$fieldName] ?? false) {
                    $idx = array_search($field['csv_header'], $headers);
                    if ($idx !== false) {
                        $customFieldIndices[$fieldName] = $idx;

                        CustomField::create([
                            'name' => $fieldName,
                            'label' => $field['label'],
                            'type' => $field['type'] ?? 'text',
                            'form_type' => 'student',
                            'required' => false,
                            'is_active' => true,
                            'order' => $order++,
                        ]);
                        $customFieldsCreatedCount++;
                    }
                }
            }

            $line = 1;
            foreach ($allRows as $data) {
                $line++;
                if (! is_array($data) || $data === [] || implode('', $data) === '') {
                    continue;
                }

                $get = function (string $key) use ($fieldIndices, $data) {
                    if (isset($fieldIndices[$key])) {
                        return trim((string)($data[$fieldIndices[$key]] ?? ''));
                    }
                    return '';
                };

                $firstName = $get('first_name');
                $lastName = $get('last_name');
                $fullName = $get('full_name');

                // Parse and split full name if first/last names are empty
                if ($firstName === '' && $lastName === '') {
                    if ($fullName !== '') {
                        $parts = preg_split('/\s+/', $fullName, 2);
                        $firstName = $parts[0];
                        $lastName = $parts[1] ?? 'Student';
                    } else {
                        $firstName = 'Student';
                        $lastName = 'Imported';
                    }
                } elseif ($firstName !== '' && $lastName === '') {
                    $lastName = 'Student';
                } elseif ($firstName === '' && $lastName !== '') {
                    $firstName = 'Student';
                }

                $className = $get('class_name') ?: 'Default Class';
                $sectionName = $get('section_name') ?: 'A';

                // Standard field validators (should always pass now due to name & class defaults)
                if ($firstName === '' || $lastName === '' || $className === '' || $sectionName === '') {
                    $errors[] = "Line {$line}: Missing required field (First Name, Last Name, Class Name, or Section Name).";
                    continue;
                }

                $gender = ucfirst(strtolower($get('gender')));
                if (!in_array($gender, ['Male', 'Female'], true)) {
                    $gender = 'Male';
                }

                // Resolve class & section
                $classObj = SchoolClass::query()->where('name', $className)->first();
                if (!$classObj) {
                    if ($this->createMissingClasses) {
                        $classObj = SchoolClass::query()->create(['name' => $className, 'level' => 1]);
                        $classesCreatedCount++;
                    } else {
                        $errors[] = "Line {$line}: Class '{$className}' does not exist and auto-creation is disabled.";
                        continue;
                    }
                }

                $sectionObj = Section::query()->where('class_id', $classObj->id)->where('name', $sectionName)->first();
                if (!$sectionObj) {
                    if ($this->createMissingSections) {
                        $sectionObj = Section::query()->create(['class_id' => $classObj->id, 'name' => $sectionName]);
                        $sectionsCreatedCount++;
                    } else {
                        $errors[] = "Line {$line}: Section '{$sectionName}' does not exist for class '{$className}' and auto-creation is disabled.";
                        continue;
                    }
                }

                // Resolve/Generate Admission Number
                $admNo = strtoupper($get('admission_number'));
                if ($admNo === '') {
                    $admNo = $nextAdmissionNum;
                    $nextAdmissionNum = $this->incrementAdmissionNumber($nextAdmissionNum);
                }

                // Dynamic custom fields payload
                $customFieldsPayload = [];
                foreach ($customFieldIndices as $name => $idx) {
                    $val = trim((string)($data[$idx] ?? ''));
                    if ($val !== '') {
                        $customFieldsPayload[$name] = $val;
                    }
                }

                $payload = [
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'class_id'         => (int) $classObj->id,
                    'section_id'       => (int) $sectionObj->id,
                    'gender'           => $gender,
                    'dob'              => $get('dob') ?: null,
                    'blood_group'      => $get('blood_group') ?: null,
                    'guardian_name'    => $get('guardian_name') ?: null,
                    'guardian_phone'   => $get('guardian_phone') ?: null,
                    'guardian_address' => $get('guardian_address') ?: null,
                    'status'           => $get('status') ?: 'Active',
                    'custom_fields'    => $customFieldsPayload,
                ];

                $existingStudent = Student::query()->where('admission_number', $admNo)->first();
                if ($existingStudent) {
                    if ($this->updateExisting) {
                        $existingStudent->fill($payload)->save();
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                    continue;
                }

                Student::query()->create(['admission_number' => $admNo] + $payload);
                $createdCount++;
            }
        });

        $this->importReport = [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'classes_created' => $classesCreatedCount,
            'sections_created' => $sectionsCreatedCount,
            'custom_fields_created' => $customFieldsCreatedCount,
            'errors' => $errors,
        ];

        $this->step = 3;
        $this->dispatch('alert', message: 'Import completed successfully!', type: 'success');
    }

    public function resetWizard(): void
    {
        $this->reset(['file', 'step', 'headers', 'sampleRows', 'columnMapping', 'detectedCustomFields', 'customFieldToggles', 'importReport', 'errorsPreview', 'summary']);
    }

    // ─── Heuristic Column Mapping Fallback ────────────────────────────────────

    private function getHeuristicMapping(array $headers): array
    {
        $mapping = [];
        $rules = [
            'admission_number' => ['adm', 'number', 'roll', 'reg', 'id'],
            'first_name'       => ['first', 'fname', 'given'],
            'last_name'        => ['last', 'lname', 'sur', 'family'],
            'full_name'        => ['full name', 'fullname', 'student name'],
            'gender'           => ['gender', 'sex'],
            'class_name'       => ['class', 'grade', 'level'],
            'section_name'     => ['section', 'arm', 'stream'],
            'dob'              => ['dob', 'birth', 'date'],
            'blood_group'      => ['blood'],
            'guardian_name'    => ['guardian', 'parent', 'father', 'mother'],
            'guardian_phone'   => ['phone', 'mobile', 'tel'],
            'guardian_address' => ['address', 'residence'],
            'status'           => ['status', 'active'],
        ];

        foreach ($rules as $field => $keywords) {
            $mapping[$field] = '';
            foreach ($headers as $header) {
                $lowerHeader = strtolower(trim((string)$header));

                // Special check for 'full_name' when column is named exactly 'name'
                if ($field === 'full_name' && $lowerHeader === 'name') {
                    $mapping[$field] = $header;
                    break;
                }

                foreach ($keywords as $kw) {
                    if (str_contains($lowerHeader, $kw)) {
                        $mapping[$field] = $header;
                        break 2;
                    }
                }
            }
        }
        return $mapping;
    }

    private function incrementAdmissionNumber(string $number): string
    {
        if (preg_match('/^(.*?)(\d+)$/', $number, $matches)) {
            $prefix = $matches[1];
            $digits = $matches[2];
            $length = strlen($digits);
            $nextVal = (int)$digits + 1;
            return $prefix . str_pad($nextVal, $length, '0', STR_PAD_LEFT);
        }
        return $number . '1';
    }

    // ─── Standard Heuristic CSV/Excel Importer ───────────────────────────────

    public function import(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls,ods', 'max:10240']]);

        try {
            [$summary, $errors, $rows] = $this->parseFile(dryRun: false, returnRows: true);
            if ($errors !== []) {
                throw ValidationException::withMessages(['file' => 'Fix file errors before manual import.']);
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

            $this->dispatch('alert', message: 'Students manual import complete.', type: 'success');
            $this->reset(['file', 'summary', 'errorsPreview']);
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }
    }

    // ─── File Utilities ───────────────────────────────────────────────────────

    private function parseFile(bool $dryRun, bool $returnRows = false): array
    {
        $path = $this->file?->getRealPath();
        if (! $path) {
            throw new \Exception('Invalid upload.');
        }

        $ext = strtolower($this->file->getClientOriginalExtension());
        $allRows = in_array($ext, ['xlsx', 'xls', 'ods'])
            ? $this->readSpreadsheetRows($path)
            : $this->readCsvRows($path);

        if (empty($allRows)) {
            throw new \Exception('File has no data.');
        }

        $rawHeaders = array_shift($allRows);
        $headers    = array_map(fn ($h) => Str::of((string) $h)->trim()->lower()->toString(), $rawHeaders);
        $map        = array_flip($headers);

        $required = ['admission_number', 'first_name', 'last_name', 'gender', 'class_name', 'section_name'];
        foreach ($required as $col) {
            if (! array_key_exists($col, $map)) {
                throw new \Exception("Missing column: {$col}");
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
            throw new \Exception('Unable to read file.');
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

    // ─── AI Helper Prompts ────────────────────────────────────────────────────

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

We need to map these columns to our system's standard student fields:
1. `admission_number` (student's unique ID/roll number/registration number)
2. `first_name` (first name/given name)
3. `last_name` (last name/surname/family name)
4. `gender` (gender/sex)
5. `class_name` (class/grade/level)
6. `section_name` (section/arm/stream)
7. `dob` (date of birth/birthday)
8. `blood_group` (blood group/type)
9. `guardian_name` (parent/guardian name)
10. `guardian_phone` (parent/guardian phone/mobile)
11. `guardian_address` (address/residence)
12. `status` (status/active state)

INSTRUCTIONS:
1. Map each standard field to exactly ONE header from the file. If no column fits a field, map it to empty string "".
2. Any column in the file that does NOT map to a standard field, but contains student information (e.g. parent email, previous school, occupation, religion, age, medical history) must be classified as a custom field.
3. For each custom field, generate:
   - `csv_header`: The exact header name from the file.
   - `name`: A snake_case identifier using only lowercase letters and underscores (e.g., `fathers_occupation`).
   - `label`: A clean display label (e.g., `Father's Occupation`).
   - `type`: Either `text`, `number`, `date`, or `checkbox`.

Respond ONLY with a valid JSON object in this format. Do not wrap it in markdown code blocks. Do not add any explanation:
{
  "column_mapping": {
    "admission_number": "CSV_HEADER_OR_EMPTY",
    "first_name": "CSV_HEADER_OR_EMPTY",
    "last_name": "CSV_HEADER_OR_EMPTY",
    "gender": "CSV_HEADER_OR_EMPTY",
    "class_name": "CSV_HEADER_OR_EMPTY",
    "section_name": "CSV_HEADER_OR_EMPTY",
    "dob": "CSV_HEADER_OR_EMPTY",
    "blood_group": "CSV_HEADER_OR_EMPTY",
    "guardian_name": "CSV_HEADER_OR_EMPTY",
    "guardian_phone": "CSV_HEADER_OR_EMPTY",
    "guardian_address": "CSV_HEADER_OR_EMPTY",
    "status": "CSV_HEADER_OR_EMPTY"
  },
  "custom_fields": [
    {
      "csv_header": "Header Name",
      "name": "snake_case_name",
      "label": "Display Label",
      "type": "text"
    }
  ]
}
PROMPT;
    }

    private function analyzeExistingDataPatterns(): array
    {
        $existingStudents = Student::query()
            ->with(['schoolClass', 'section'])
            ->limit(100)
            ->get();

        $patterns = [
            'admission_number_format' => [],
            'class_names'             => [],
            'section_names'           => [],
            'custom_fields'           => [],
            'next_admission_number'   => null,
        ];

        if ($existingStudents->isEmpty()) {
            return $patterns;
        }

        $admissionNumbers = $existingStudents->pluck('admission_number')->filter();
        if ($admissionNumbers->isNotEmpty()) {
            $patterns['admission_number_format'] = $this->detectAdmissionNumberPattern($admissionNumbers);
            $patterns['next_admission_number']   = $this->generateNextAdmissionNumber($admissionNumbers);
        }

        $patterns['class_names']   = $existingStudents->pluck('schoolClass.name')->filter()->unique()->values()->toArray();
        $patterns['section_names'] = $existingStudents->pluck('section.name')->filter()->unique()->values()->toArray();

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
            if (preg_match('/^([A-Z]+)(\d{4})(\d+)$/', $number, $matches)) {
                $patterns[] = [
                    'type'    => 'prefix_year_sequence',
                    'prefix'  => $matches[1],
                    'year'    => $matches[2],
                    'example' => $number
                ];
            } elseif (preg_match('/^(\d{4})(\d+)$/', $number, $matches)) {
                $patterns[] = [
                    'type'    => 'year_sequence',
                    'year'    => $matches[1],
                    'example' => $number
                ];
            } elseif (preg_match('/^([A-Z]+)(\d+)$/', $number, $matches)) {
                $patterns[] = [
                    'type'    => 'prefix_sequence',
                    'prefix'  => $matches[1],
                    'example' => $number
                ];
            }
        }

        $patternCounts = array_count_values(array_column($patterns, 'type'));
        if (empty($patternCounts)) {
            return [];
        }
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

        $pattern = array_values($patterns)[0];
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

    private function callAI(string $prompt): ?string
    {
        return $this->tryGroqAPI($prompt);
    }

    private function tryGroqAPI(string $prompt): ?string
    {
        $rawKeys = env('GROQ_API_KEY');
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

    // ─── Class / Section Resolution ───────────────────────────────────────────

    private function resolveClass(string $name): SchoolClass
    {
        $class = SchoolClass::query()->where('name', $name)->first();
        if ($class) {
            return $class;
        }
        return SchoolClass::query()->create(['name' => $name, 'level' => 1]);
    }

    private function resolveSection(int $classId, string $name): Section
    {
        $section = Section::query()->where('class_id', $classId)->where('name', $name)->first();
        if ($section) {
            return $section;
        }
        return Section::query()->create(['class_id' => $classId, 'name' => $name]);
    }

    public function updatedColumnMapping(): void
    {
        $this->recalculateCustomFields();
    }

    private function recalculateCustomFields(): void
    {
        $mappedHeaders = array_values(array_filter($this->columnMapping));
        $newCustomFields = [];
        $newToggles = [];

        foreach ($this->headers as $header) {
            if (in_array($header, $mappedHeaders, true)) continue;

            $name = Str::slug($header, '_') ?: 'field_' . md5($header);
            $lowerHeader = strtolower($header);

            $type = 'text';
            if (str_contains($lowerHeader, 'date') || str_contains($lowerHeader, 'dob')) {
                $type = 'date';
            } elseif (str_contains($lowerHeader, 'phone') || str_contains($lowerHeader, 'age') || str_contains($lowerHeader, 'number') || str_contains($lowerHeader, 'fee')) {
                $type = 'number';
            }

            $newCustomFields[] = [
                'csv_header' => $header,
                'name'       => $name,
                'label'      => $header,
                'type'       => $type,
            ];

            // Always enabled — no toggle needed
            $newToggles[$name] = true;
        }

        $this->detectedCustomFields = $newCustomFields;
        $this->customFieldToggles   = $newToggles;
    }

    public function render()
    {
        return view('livewire.imports.students');
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentProService
{
    protected ?User $user;
    protected string $systemInstruction;

    public function __construct(?User $user)
    {
        $this->user = $user;
        $this->buildSystemInstruction();
    }

    private function buildSystemInstruction(): void
    {
        $role = $this->user?->role ?? 'guest';
        $userId = $this->user?->id ?? 0;
        $userName = $this->user?->name ?? 'User';

        $rbacRules = match ($role) {
            'admin', 'bursar' => "You have full read access to every table in the database.",
            'teacher' => "You are a teacher (users.id = {$userId}). You may only query:
- students and their data for classes where you hold a subject_allocation (subject_allocations.teacher_id = {$userId}).
- scores, attendance, homework for those students.
- You CANNOT access transactions, fee_structures, or any financial data.
Always add the appropriate JOIN/WHERE clause to restrict to your classes.",
            'parent' => "You are a parent (users.id = {$userId}). You may only query:
- Students linked to you via the parent_student pivot table (parent_student.user_id = {$userId}).
- scores, attendance, homework for ONLY those students.
- You CANNOT access financial transactions, salary data, or other students.
Always JOIN or WHERE-filter through parent_student to restrict results.",
            default => "You have no special permissions. Only answer general greetings.",
        };

        $this->systemInstruction = <<<EOT
You are AgentPro, a warm, calm, and highly capable human-like assistant for a school management platform. Speak naturally and politely — never robotic. Do NOT announce you are an AI.

--- CURRENT USER ---
Name: {$userName}
Role: {$role}
User ID: {$userId}

--- YOUR CAPABILITIES ---
You have a tool called `run_sql_query` that lets you execute any read-only SELECT query against the school database. Use it freely whenever a user asks for any school-related data. You may call it multiple times in a conversation to build up an answer.

--- SECURITY RULES ---
{$rbacRules}

--- DATABASE SCHEMA ---
# users (id, name, email, role[admin|bursar|teacher|parent], is_active, whatsapp_number, created_at)
# classes (id, name)  — e.g. "JSS 1", "JSS 2", "SSS 1", "SSS 2"
# sections (id, name, class_id)
# students (id, admission_number, first_name, last_name, class_id, section_id, gender, dob, blood_group, guardian_name, guardian_phone, status[Active|Inactive])
# parent_student (user_id, student_id)  — links parents to their children
# subjects (id, name)
# class_subject (class_id, subject_id) — subjects assigned to a class by default
# subject_allocations (id, teacher_id, class_id, subject_id, session, term)
# scores (id, student_id, subject_id, class_id, term, session, ca_score, exam_score, total_score, grade, created_at)
# transactions (id, student_id, amount, type, status[paid|pending|void], reference, created_at)
# fee_structures (id, class_id, session, term, amount, label)
# attendance_sheets (id, class_id, date, session, term)
# attendance_marks (id, sheet_id, student_id, status[present|absent|late])
# homework (id, teacher_id, class_id, section_id, subject_id, title, content, due_date, created_at)
# homework_submissions (id, homework_id, student_id, submission, attachment, grade, feedback, submitted_at, graded_at)
# announcements (id, title, body, audience, created_at)
# school_events (id, title, description, start_date, end_date)
# academic_sessions (id, name, is_current)
# academic_terms (id, session_id, name, is_current, start_date, end_date)
# cbt_exams (id, title, class_id, subject_id, duration_minutes, total_marks, status, scheduled_at)
# cbt_questions (id, exam_id, question, marks)
# cbt_attempts (id, exam_id, student_id, score, status, started_at, submitted_at)
# in_app_notifications (id, user_id, title, message, is_read, created_at)
# audit_logs (id, user_id, action, model_type, model_id, changes, created_at)
# timetable_entries (id, class_id, section_id, subject_id, teacher_id, day_of_week, start_time, end_time)
# whatsapp_logs (id, phone, direction, message, status, created_at)

--- GUIDELINES ---
- Use `run_sql_query` freely for any school data question; call it multiple times if needed.
- Class names in the DB are stored with spaces: "JSS 1", "JSS 2", "SSS 1" etc. When a user says "JSS1", search with LIKE '%JSS%1%' or REPLACE(name,' ','').
- Always respond warmly and clearly. Format lists, numbers, and tables nicely in your text reply.
- For greetings/small talk, reply warmly WITHOUT calling any tool.
- For anything completely unrelated to school (politics, cooking, general trivia), politely say you can only help with school matters.
- Never expose raw IDs in your final answer to the user.
- Limit results to a reasonable amount (max 20 rows) unless the user asks for everything.
EOT;
    }

    public function ask(string $question): string
    {
        $keys = array_filter(array_map('trim', explode(',', env('GROQ_API_KEY', ''))));
        if (empty($keys)) {
            return "Groq API key not configured.";
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemInstruction],
            ['role' => 'user', 'content' => $question],
        ];

        $tools = $this->getTools();

        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $messages,
            'temperature' => 0.2,
            'tools' => $tools,
            'tool_choice' => 'auto',
        ];

        return $this->callGroqWithTools($keys, $payload, $messages);
    }

    private function callGroqWithTools(array $keys, array $payload, array &$messages, int $depth = 0): string
    {
        if ($depth > 8) {
            return "I've gathered all the information I can — let me know if you need anything else!";
        }

        $response = null;
        foreach ($keys as $apiKey) {
            $response = Http::withOptions(['verify' => false])
                ->timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', $payload);

            if ($response->successful())
                break;
            if ($response->status() !== 429)
                break;

            Log::warning('Groq key quota exceeded, trying next key');
        }

        if (!$response || !$response->successful()) {
            Log::error('Groq AI request failed', [
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);
            return "An error occurred while processing your request.";
        }

        $data = $response->json();
        $message = $data['choices'][0]['message'] ?? null;

        if (!$message) {
            return "Invalid response from AI.";
        }

        $messages[] = $message;

        if (!empty($message['tool_calls'])) {
            foreach ($message['tool_calls'] as $toolCall) {
                $functionName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true) ?? [];
                $result = $this->executeTool($functionName, $arguments);

                $messages[] = [
                    'tool_call_id' => $toolCall['id'],
                    'role' => 'tool',
                    'name' => $functionName,
                    'content' => json_encode($result),
                ];
            }

            $payload['messages'] = $messages;
            return $this->callGroqWithTools($keys, $payload, $messages, $depth + 1);
        }

        return trim($message['content'] ?? '');
    }

    private function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'run_sql_query',
                    'description' => 'Execute a read-only SELECT SQL query against the school database. Use this for ANY question about students, results, attendance, fees, homework, staff, timetables, events, etc. You may call this multiple times. Always use LIMIT to avoid huge result sets.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'sql' => [
                                'type' => 'string',
                                'description' => 'A valid SELECT SQL statement. Must be read-only. Do NOT use INSERT, UPDATE, DELETE, DROP, TRUNCATE, ALTER, or any data-modifying statement.',
                            ],
                        ],
                        'required' => ['sql'],
                    ],
                ],
            ],
        ];
    }

    private function executeTool(string $name, array $args): mixed
    {
        if ($name === 'run_sql_query') {
            return $this->runSqlQuery($args['sql'] ?? '');
        }
        return ['error' => 'Unknown tool: ' . $name];
    }

    private function runSqlQuery(string $sql): mixed
    {
        $sql = trim($sql);

        // ── Safety: allow only SELECT statements ──────────────────────────────
        if (!preg_match('/^\s*SELECT\s/i', $sql)) {
            Log::warning('AgentPro blocked non-SELECT query', ['sql' => $sql]);
            return ['error' => 'Only SELECT statements are allowed.'];
        }

        // Block dangerous keywords regardless of position
        $blocked = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'CREATE', 'REPLACE', 'GRANT', 'REVOKE', 'EXEC', 'EXECUTE', '--', 'xp_'];
        foreach ($blocked as $kw) {
            if (stripos($sql, $kw) !== false) {
                Log::warning('AgentPro blocked dangerous keyword in query', ['sql' => $sql, 'keyword' => $kw]);
                return ['error' => "Statement contains a disallowed keyword: {$kw}"];
            }
        }

        // ── RBAC enforcement ─────────────────────────────────────────────────
        $role = $this->user?->role ?? 'guest';
        $userId = $this->user?->id ?? 0;

        // For parents: query must reference parent_student to scope data
        if ($role === 'parent') {
            if (
                stripos($sql, 'transactions') !== false ||
                stripos($sql, 'fee_structures') !== false ||
                stripos($sql, 'audit_logs') !== false ||
                stripos($sql, 'users') !== false
            ) {
                return ['error' => 'You do not have permission to access financial or system records.'];
            }

            // Auto-inject parent scoping if parent_student is missing
            if (
                (stripos($sql, 'students') !== false || stripos($sql, 'scores') !== false || stripos($sql, 'attendance') !== false) &&
                stripos($sql, 'parent_student') === false
            ) {
                // Wrap in a subquery to enforce parent scoping
                $sql = "SELECT * FROM ({$sql}) AS __agent_sub WHERE student_id IN (SELECT student_id FROM parent_student WHERE user_id = {$userId})";
            }
        }

        // For teachers: block financial data
        if ($role === 'teacher') {
            if (
                stripos($sql, 'transactions') !== false ||
                stripos($sql, 'fee_structures') !== false
            ) {
                return ['error' => 'Teachers do not have access to financial data.'];
            }
        }

        // Enforce a row limit (safety net)
        if (!preg_match('/LIMIT\s+\d+/i', $sql)) {
            $sql .= ' LIMIT 50';
        }

        try {
            Log::info('AgentPro SQL query', ['user_id' => $this->user?->id, 'role' => $role, 'sql' => $sql]);
            $results = DB::select($sql);

            if (empty($results)) {
                return ['message' => 'No records found matching your query.'];
            }

            // Convert to plain arrays
            return array_map(fn($row) => (array) $row, $results);
        } catch (\Exception $e) {
            Log::error('AgentPro SQL error', ['sql' => $sql, 'error' => $e->getMessage()]);
            return ['error' => 'Query failed: ' . $e->getMessage()];
        }
    }
}

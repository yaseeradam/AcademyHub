<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CbtQuestionImporter
{
    /**
     * Generate questions via AI (Groq).
     */
    public static function fromAi(string $topic, string $subject, int $count, string $type, int $marks): array
    {
        $typeLabel = match ($type) {
            'theory' => 'theory/essay (open-ended, no options)',
            default  => 'multiple choice (MCQ) with 4 options and one correct answer',
        };

        $prompt = <<<PROMPT
Generate exactly {$count} {$typeLabel} exam questions about "{$topic}" for a {$subject} exam.

Return ONLY a valid JSON array. No markdown, no explanation, no code fences.

For MCQ:
[{"type":"mcq","prompt":"Question text?","marks":{$marks},"options":["Option A","Option B","Option C","Option D"],"correct":0}]
(correct = 0-based index of the correct option)

For theory:
[{"type":"theory","prompt":"Question text?","marks":{$marks}}]
PROMPT;

        $raw = self::callGroq($prompt);

        if (! $raw) {
            throw new \RuntimeException('AI service unavailable. Please try again.');
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/', '', $raw);

        $questions = json_decode(trim($raw), true);
        if (! is_array($questions)) {
            throw new \RuntimeException('AI returned invalid JSON. Please try again.');
        }

        return self::normalise($questions);
    }

    /**
     * Parse an uploaded .txt file into questions.
     *
     * ONE FORMAT — teachers write it like this:
     *
     *   1. What is the powerhouse of the cell?
     *   A. Nucleus
     *   B. Mitochondria
     *   C. Ribosome
     *   D. Golgi body
     *   ANS: B
     *   MARKS: 2
     *
     *   2. Define osmosis.
     *   TYPE: theory
     *   MARKS: 5
     *
     * Rules:
     *  - Question starts with a number and dot/bracket: 1. or 1)
     *  - Options are A. B. C. D. (dot, bracket, colon, or space all work)
     *  - ANS: is the correct option letter
     *  - MARKS: is optional (defaults to 1)
     *  - TYPE: theory marks it as a theory question
     *  - Blank line separates questions
     *  - If no options are given, question is auto-treated as theory
     */
    public static function fromFile(UploadedFile $file): array
    {
        $text = file_get_contents($file->getRealPath());
        if ($text === false) {
            throw new \RuntimeException('Could not read uploaded file.');
        }

        $text   = str_replace(["\r\n", "\r"], "\n", $text);
        $blocks = preg_split('/\n{2,}/', trim($text));
        $questions = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') continue;
            $q = self::parseBlock(array_map('trim', explode("\n", $block)));
            if ($q !== null) {
                $questions[] = $q;
            }
        }

        if (empty($questions)) {
            throw new \RuntimeException('No questions found. Check the file format.');
        }

        return $questions;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function parseBlock(array $lines): ?array
    {
        $prompt       = '';
        $type         = 'mcq';
        $marks        = 1;
        $options      = [];
        $correctIndex = 0;
        $map          = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];

        foreach ($lines as $line) {
            // Numbered question: "1. text" or "1) text"
            if ($prompt === '' && preg_match('/^\d+[\.\)]\s*(.+)/u', $line, $m)) {
                $prompt = trim($m[1]);
            }
            // Q: prefix fallback
            elseif ($prompt === '' && preg_match('/^Q[:\.\)]\s*(.+)/i', $line, $m)) {
                $prompt = trim($m[1]);
            }
            // Options: "A. text" or "A) text" or "A: text"
            elseif (preg_match('/^([A-D])[\.\):\s]\s*(.+)/i', $line, $m)) {
                $options[strtoupper($m[1])] = trim($m[2]);
            }
            // Answer line
            elseif (preg_match('/^ANS(?:WER)?[:\s]\s*([A-D])/i', $line, $m)) {
                $correctIndex = $map[strtoupper($m[1])] ?? 0;
            }
            // Type override
            elseif (preg_match('/^TYPE[:\s]\s*(\w+)/i', $line, $m)) {
                $type = strtolower($m[1]) === 'theory' ? 'theory' : 'mcq';
            }
            // Marks
            elseif (preg_match('/^MARKS?[:\s]\s*(\d+)/i', $line, $m)) {
                $marks = max(1, (int) $m[1]);
            }
        }

        if ($prompt === '') return null;

        // Explicit theory or no options found → theory
        if ($type === 'theory' || count($options) < 2) {
            return ['type' => 'theory', 'prompt' => $prompt, 'marks' => $marks];
        }

        $list = [
            $options['A'] ?? '',
            $options['B'] ?? '',
            $options['C'] ?? '',
            $options['D'] ?? '',
        ];

        return [
            'type'    => 'mcq',
            'prompt'  => $prompt,
            'marks'   => $marks,
            'options' => $list,
            'correct' => min($correctIndex, 3),
        ];
    }

    private static function normalise(array $raw): array
    {
        $out = [];
        foreach ($raw as $q) {
            if (! isset($q['prompt']) || trim((string) $q['prompt']) === '') continue;

            $type = strtolower((string) ($q['type'] ?? 'mcq'));

            if ($type === 'theory') {
                $out[] = [
                    'type'   => 'theory',
                    'prompt' => trim((string) $q['prompt']),
                    'marks'  => max(1, (int) ($q['marks'] ?? 1)),
                ];
                continue;
            }

            $options = array_values((array) ($q['options'] ?? []));
            if (count($options) < 2) continue;
            while (count($options) < 4) $options[] = '';

            $out[] = [
                'type'    => 'mcq',
                'prompt'  => trim((string) $q['prompt']),
                'marks'   => max(1, (int) ($q['marks'] ?? 1)),
                'options' => array_slice($options, 0, 4),
                'correct' => min(max(0, (int) ($q['correct'] ?? 0)), 3),
            ];
        }
        return $out;
    }

    private static function callGroq(string $prompt): ?string
    {
        $rawKeys = config('services.groq.key');
        $keys = array_filter(array_map('trim', explode(',', $rawKeys)));

        if (empty($keys)) {
            Log::error('CBT AI: No Groq API keys configured.');
            return null;
        }

        shuffle($keys);

        foreach ($keys as $apiKey) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$apiKey,
                        'Content-Type'  => 'application/json',
                    ])
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'    => 'llama-3.3-70b-versatile',
                        'messages' => [
                            [
                                'role'    => 'system',
                                'content' => 'You are an expert exam question writer. Always respond with valid JSON only. No markdown, no explanation.',
                            ],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.7,
                        'max_tokens'  => 3000,
                    ]);

                if ($response->successful()) {
                    $text = $response->json('choices.0.message.content');
                    if ($text) {
                        Log::info('CBT AI: Groq success');
                        return $text;
                    }
                }

                Log::warning('CBT AI: Groq API key failed or rate-limited', [
                    'key_preview' => substr($apiKey, 0, 8) . '...',
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Throwable $e) {
                Log::error('CBT AI: Groq key error', [
                    'key_preview' => substr($apiKey, 0, 8) . '...',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return null;
    }
}

<?php

namespace App\Support;

use App\Models\SchoolClass;
use Illuminate\Support\Str;

class PsychomotorService
{
    public const STAGE_EARLY_YEARS = 'early_years';
    public const STAGE_PRIMARY     = 'primary';
    public const STAGE_SECONDARY   = 'secondary';

    public const SCALE_5_POINT     = '5_point';
    public const SCALE_LETTER      = 'letter';
    public const SCALE_DESCRIPTIVE = 'descriptive';

    /**
     * Detect educational stage based on class name or level.
     */
    public static function detectStage(SchoolClass|string|int|null $class): string
    {
        if (is_numeric($class)) {
            $class = SchoolClass::find((int) $class);
        }

        $className = '';
        $level = 0;

        if ($class instanceof SchoolClass) {
            $className = strtolower((string) $class->name);
            $level = (int) $class->level;
        } elseif (is_string($class)) {
            $className = strtolower($class);
        }

        // 1. Keyword check for Early Years / Nursery / Kindergarten
        if (preg_match('/\b(nursery|creche|kindergarten|kg|reception|pre-school|preschool|playgroup|toddler|eyfs|early\s*years)\b/i', $className)) {
            return self::STAGE_EARLY_YEARS;
        }

        // 2. Keyword check for Secondary / College / High School / JSS / SSS
        if (preg_match('/\b(jss|sss|js|ss|secondary|college|high\s*school|senior|junior|form|grade\s*(7|8|9|10|11|12))\b/i', $className)) {
            return self::STAGE_SECONDARY;
        }

        // 3. Keyword check for Primary / Basic / Elementary
        if (preg_match('/\b(primary|basic|grade\s*(1|2|3|4|5|6)|elementary|class\s*(1|2|3|4|5|6))\b/i', $className)) {
            return self::STAGE_PRIMARY;
        }

        // 4. Fallback based on level integer if present
        if ($level > 0) {
            if ($level <= 3) {
                return self::STAGE_EARLY_YEARS;
            }
            if ($level <= 9) {
                return self::STAGE_PRIMARY;
            }
            return self::STAGE_SECONDARY;
        }

        return self::STAGE_PRIMARY;
    }

    /**
     * Human-friendly label for stage.
     */
    public static function stageLabel(string $stage): string
    {
        return match ($stage) {
            self::STAGE_EARLY_YEARS => 'Early Years / Nursery / KG',
            self::STAGE_SECONDARY   => 'Secondary / High School',
            default                 => 'Primary / Basic Curriculum',
        };
    }

    /**
     * Default Affective and Psychomotor profiles by educational stage.
     *
     * @return array<string, array{label: string, affective: string[], psychomotor: string[]}>
     */
    public static function defaultProfiles(): array
    {
        return [
            self::STAGE_EARLY_YEARS => [
                'label' => 'Early Years / Nursery / KG',
                'affective' => [
                    'Sharing & Turn-Taking',
                    'Emotional Expression & Calmness',
                    'Independence & Self-Care',
                    'Obedience & Following Instructions',
                    'Neatness & Personal Hygiene',
                ],
                'psychomotor' => [
                    'Pencil Grip & Control',
                    'Scissor Cutting & Pasting',
                    'Coloring within Lines',
                    'Balancing & Body Coordination',
                    'Ball Throwing & Catching',
                ],
            ],
            self::STAGE_PRIMARY => [
                'label' => 'Primary / Basic Curriculum',
                'affective' => [
                    'Punctuality',
                    'Neatness & Uniform Deportment',
                    'Politeness & Courtesy',
                    'Honesty & Truthfulness',
                    'Attentiveness in Class',
                    'Relationship with Peers',
                    'Self-Control & Discipline',
                ],
                'psychomotor' => [
                    'Handwriting & Legibility',
                    'Verbal Fluency & Reading',
                    'Games, Sports & Athletics',
                    'Drawing, Arts & Crafts',
                    'Musical & Performance Skills',
                ],
            ],
            self::STAGE_SECONDARY => [
                'label' => 'Secondary / High School',
                'affective' => [
                    'Punctuality & Time Management',
                    'Neatness & Uniform Deportment',
                    'Politeness & Respect for Staff',
                    'Integrity & Honesty',
                    'Leadership & Initiative',
                    'Self-Discipline & Focus',
                    'Teamwork & Peer Cooperation',
                ],
                'psychomotor' => [
                    'Handwriting & Presentation',
                    'Speech Delivery & Debating',
                    'Sports, Athletics & Fitness',
                    'Science Laboratory Dexterity',
                    'Technical Drawing / Workshop Tools',
                    'Computer & Keyboard Skills',
                ],
            ],
        ];
    }

    /**
     * Get configured traits for a specific class, merging tenant customizations with defaults.
     *
     * @return array{
     *   stage: string,
     *   stage_label: string,
     *   scale: string,
     *   affective: string[],
     *   psychomotor: string[],
     *   all: string[],
     *   trait_map: array<string, string>,
     *   trait_domain: array<string, string>
     * }
     */
    public static function getTraitsForClass(SchoolClass|string|int|null $class, ?int $tenantId = null): array
    {
        $stage = self::detectStage($class);
        $defaults = self::defaultProfiles()[$stage] ?? self::defaultProfiles()[self::STAGE_PRIMARY];

        $tenantId = $tenantId ?? TenantSettings::tenantId();
        $scale = TenantSettings::getForTenant('rc_psychomotor_scale', $tenantId) ?: self::SCALE_5_POINT;
        $customProfiles = TenantSettings::getForTenant('rc_psychomotor_profiles', $tenantId);

        if (is_string($customProfiles)) {
            $customProfiles = json_decode($customProfiles, true);
        }

        $affective = $defaults['affective'];
        $psychomotor = $defaults['psychomotor'];

        if (is_array($customProfiles) && isset($customProfiles[$stage])) {
            if (!empty($customProfiles[$stage]['affective']) && is_array($customProfiles[$stage]['affective'])) {
                $affective = array_values(array_filter(array_map('trim', $customProfiles[$stage]['affective'])));
            }
            if (!empty($customProfiles[$stage]['psychomotor']) && is_array($customProfiles[$stage]['psychomotor'])) {
                $psychomotor = array_values(array_filter(array_map('trim', $customProfiles[$stage]['psychomotor'])));
            }
        }

        $all = array_merge($affective, $psychomotor);

        $traitMap = [];
        $traitDomain = [];
        foreach ($affective as $label) {
            $slug = Str::slug($label, '_');
            $traitMap[$slug] = $label;
            $traitDomain[$label] = 'affective';
        }
        foreach ($psychomotor as $label) {
            $slug = Str::slug($label, '_');
            $traitMap[$slug] = $label;
            $traitDomain[$label] = 'psychomotor';
        }

        return [
            'stage'        => $stage,
            'stage_label'  => self::stageLabel($stage),
            'scale'        => $scale,
            'affective'    => $affective,
            'psychomotor'  => $psychomotor,
            'all'          => $all,
            'trait_map'    => $traitMap,
            'trait_domain' => $traitDomain,
        ];
    }

    /**
     * Available rating scales for settings.
     */
    public static function scales(): array
    {
        return [
            self::SCALE_5_POINT => [
                'name'    => '5-Point Numeric Scale (5 = Excellent, 1 = Poor)',
                'options' => [
                    '5' => '5 · Distinction / Excellent',
                    '4' => '4 · Commendable / Good',
                    '3' => '3 · Satisfactory / Average',
                    '2' => '2 · Fair / Developing',
                    '1' => '1 · Poor / Needs Attention',
                ],
                'short'   => ['5' => '5', '4' => '4', '3' => '3', '2' => '2', '1' => '1'],
            ],
            self::SCALE_LETTER => [
                'name'    => 'Letter Grade Scale (A = Distinction, E = Poor)',
                'options' => [
                    'A' => 'A · Distinction / Excellent',
                    'B' => 'B · Very Good',
                    'C' => 'C · Credit / Good',
                    'D' => 'D · Pass / Fair',
                    'E' => 'E · Unsatisfactory / Poor',
                ],
                'short'   => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E'],
            ],
            self::SCALE_DESCRIPTIVE => [
                'name'    => 'Descriptive Word Scale (Excellent, Good, Average, Fair, Poor)',
                'options' => [
                    'Excellent' => 'Excellent',
                    'Good'      => 'Good',
                    'Average'   => 'Average',
                    'Fair'      => 'Fair',
                    'Poor'      => 'Poor',
                ],
                'short'   => ['Excellent' => 'Exc', 'Good' => 'Good', 'Average' => 'Avg', 'Fair' => 'Fair', 'Poor' => 'Poor'],
            ],
        ];
    }

    /**
     * Normalize rating value to standard 1-5 integer score for uniform arithmetic and grid plotting.
     */
    public static function ratingToScore(mixed $rating): int
    {
        if (is_numeric($rating)) {
            $num = (int) $rating;
            return max(1, min(5, $num));
        }

        $clean = strtolower(trim((string) $rating));

        return match ($clean) {
            '5', 'a', 'distinction', 'excellent' => 5,
            '4', 'b', 'very good', 'good'        => 4,
            '3', 'c', 'average', 'satisfactory'  => 3,
            '2', 'd', 'fair', 'developing'       => 2,
            '1', 'e', 'f', 'poor', 'needs work'  => 1,
            default                              => 4,
        };
    }

    /**
     * Convert 1-5 score to target scale representation.
     */
    public static function scoreToRating(int $score, string $scale = self::SCALE_5_POINT): string
    {
        $score = max(1, min(5, $score));

        return match ($scale) {
            self::SCALE_LETTER => match ($score) {
                5 => 'A',
                4 => 'B',
                3 => 'C',
                2 => 'D',
                default => 'E',
            },
            self::SCALE_DESCRIPTIVE => match ($score) {
                5 => 'Excellent',
                4 => 'Good',
                3 => 'Average',
                2 => 'Fair',
                default => 'Poor',
            },
            default => (string) $score,
        };
    }

    /**
     * Get key-value options for a given scale.
     */
    public static function scaleOptions(?string $scale = null): array
    {
        $scales = self::scales();
        $scale = $scale ?: (TenantSettings::get('rc_psychomotor_scale') ?: self::SCALE_5_POINT);
        return $scales[$scale]['options'] ?? $scales[self::SCALE_5_POINT]['options'];
    }

    /**
     * Get short labels for a given scale (e.g. for compact column headers).
     */
    public static function scaleShort(?string $scale = null): array
    {
        $scales = self::scales();
        $scale = $scale ?: (TenantSettings::get('rc_psychomotor_scale') ?: self::SCALE_5_POINT);
        return $scales[$scale]['short'] ?? $scales[self::SCALE_5_POINT]['short'];
    }

    /**
     * Sensible default rating value for a scale.
     */
    public static function defaultRating(?string $scale = null): string
    {
        $scale = $scale ?: (TenantSettings::get('rc_psychomotor_scale') ?: self::SCALE_5_POINT);
        return match ($scale) {
            self::SCALE_LETTER => 'B',
            self::SCALE_DESCRIPTIVE => 'Good',
            default => '4',
        };
    }
}

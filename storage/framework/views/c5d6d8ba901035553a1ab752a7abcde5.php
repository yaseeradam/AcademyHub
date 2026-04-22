<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => '',
    'size' => 56,
    'rounded' => 'rounded-full',
    'class' => '',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name' => '',
    'size' => 56,
    'rounded' => 'rounded-full',
    'class' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $palette = [
        ['#DBEAFE', '#1D4ED8'],
        ['#DCFCE7', '#047857'],
        ['#EDE9FE', '#6D28D9'],
        ['#FFE4E6', '#BE123C'],
        ['#FFEDD5', '#C2410C'],
        ['#E0F2FE', '#0369A1'],
    ];

    $hash = abs(crc32((string) $name));
    [$bg, $fg] = $palette[$hash % count($palette)];

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$size" height="$size" viewBox="0 0 64 64">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="$bg" />
      <stop offset="1" stop-color="#FFFFFF" />
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="32" fill="url(#g)" />
  <circle cx="32" cy="26" r="10" fill="#FFFFFF" opacity="0.9" />
  <path d="M14 56c3-12 13-18 18-18s15 6 18 18" fill="#FFFFFF" opacity="0.9"/>
  <text x="32" y="38" text-anchor="middle" font-family="Inter, ui-sans-serif, system-ui" font-size="16" font-weight="700" fill="$fg">$initials</text>
</svg>
SVG;

    $src = 'data:image/svg+xml;base64,'.base64_encode($svg);
?>

<img
    src="<?php echo e($src); ?>"
    alt="<?php echo e($name); ?>"
    width="<?php echo e($size); ?>"
    height="<?php echo e($size); ?>"
    class="<?php echo e($rounded); ?> <?php echo e($class); ?>"
/>

<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/components/avatar.blade.php ENDPATH**/ ?>
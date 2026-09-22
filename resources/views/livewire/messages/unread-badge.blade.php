<span wire:init="refreshCount" wire:poll.5s="refreshCount" class="inline-flex items-center">
    @if($count > 0)
        <span class="ml-2 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-black leading-none text-white shadow-sm">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</span>


<div class="space-y-6">
    <x-page-header
        title="Score Submissions"
        subtitle="Review teacher submissions and approve or reject"
        accent="results"
    >
        <x-slot:actions>
            <a href="{{ route('results.entry') }}" class="btn-outline">Score Entry</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card-padded">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-900">Filter by Status</div>
            <select wire:model.live="statusFilter" class="select w-48">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <div class="grid gap-4">
        @forelse($submissions as $submission)
            @php
                $variant = match($submission->status) {
                    'approved' => 'success',
                    'rejected' => 'warning',
                    default => 'info'
                };
            @endphp
            <div class="card-padded">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div class="flex-1 w-full">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ $submission->subject?->name }}</h3>
                            <x-status-badge variant="{{ $variant }}">{{ ucfirst($submission->status) }}</x-status-badge>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600">
                            <div><span class="font-semibold">Teacher:</span> {{ $submission->teacher?->name }}</div>
                            <div><span class="font-semibold">Class:</span> {{ $submission->schoolClass?->name }}</div>
                            <div><span class="font-semibold">Session:</span> {{ $submission->session }} - Term {{ $submission->term }}</div>
                            <div><span class="font-semibold">Submitted:</span> {{ $submission->submitted_at?->format('M j, Y g:i A') }}</div>
                        </div>
                        @if($submission->note)
                            <div class="mt-3 rounded-lg bg-orange-50 p-3 text-sm text-orange-900">
                                <span class="font-semibold">Note:</span> {{ $submission->note }}
                            </div>
                        @endif
                        @if($submission->status !== 'pending')
                            <div class="mt-2 text-xs text-gray-500">
                                Reviewed by {{ $submission->approver?->name }} on {{ $submission->reviewed_at?->format('M j, Y g:i A') }}
                            </div>
                        @endif
                    </div>
                    @if($submission->status === 'pending')
                        <div class="flex gap-2 w-full md:w-auto justify-end mt-2 md:mt-0">
                            <button wire:click="approve({{ $submission->id }})" class="btn-primary w-full sm:w-auto justify-center">
                                Approve
                            </button>
                            <button wire:click="$dispatch('reject-modal', { id: {{ $submission->id }} })" class="btn-warning w-full sm:w-auto justify-center">
                                Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card-padded text-center text-gray-500">
                No submissions found
            </div>
        @endforelse
    </div>
</div>

@script
    $wire.on('submission-approved', () => {
        showModal('success', 'Submission Approved', 'The score submission has been approved successfully.');
    });

    $wire.on('submission-rejected', () => {
        showModal('success', 'Submission Rejected', 'The score submission has been rejected.');
    });

    $wire.on('reject-modal', (event) => {
        const id = event[0].id;
        const note = prompt('Enter rejection reason (optional):');
        if (note !== null) {
            $wire.call('reject', id, note);
        }
    });

    function showModal(type, title, message) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        
        const colors = {
            success: { bg: 'from-emerald-500 to-teal-500', icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
        };
        
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full transform" style="animation: slideUp 0.3s ease-out">
                <div class="bg-gradient-to-r ${colors[type].bg} p-6 rounded-t-3xl">
                    <div class="flex items-center gap-4">
                        <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${colors[type].icon}" />
                        </svg>
                        <h3 class="text-2xl font-bold text-white">${title}</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 text-lg leading-relaxed">${message}</p>
                </div>
                <div class="p-6 pt-0">
                    <button onclick="this.closest('.fixed').remove()" class="w-full bg-gradient-to-r ${colors[type].bg} text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all">
                        Close
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    }
@endscript

<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

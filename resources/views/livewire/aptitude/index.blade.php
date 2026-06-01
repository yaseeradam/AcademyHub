<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Aptitude Tests</h1>
                <p class="mt-1 text-sm text-slate-500">Screen, test, and admit prospective new applicants before they enter active classes.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('aptitude.questions') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition duration-200 gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Manage Exam Questions
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Panel: Add Applicant -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm h-fit">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Register New Applicant</h2>
                <form wire:submit.prevent="addApplicant" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" wire:model="first_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="e.g. Yasir">
                        @error('first_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" wire:model="last_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="e.g. Junior">
                        @error('last_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" wire:model="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="e.g. junior@mail.com">
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Guardian Phone</label>
                        <input type="text" wire:model="phone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="e.g. +234...">
                        @error('phone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Applied Class</label>
                        <select wire:model="class_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            <option value="">Select a Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 transition duration-200 shadow-md hover:shadow-lg">
                        Add Applicant
                    </button>
                </form>
            </div>

            <!-- Right Panel: Applicants List -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-6">Registered Applicants & Screening Status</h2>
                
                @if($applicants->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">No applicants yet</h3>
                        <p class="mt-1 text-sm text-slate-500">Get started by creating a new prospective student on the left panel.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Applicant Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Applied Class</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Status & Score</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action Links</th>
                                </tr>
                            </table>
                            <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
                                @foreach($applicants as $app)
                                    <div class="flex items-center justify-between py-4.5 px-2 hover:bg-slate-50/50 rounded-xl transition duration-150">
                                        <!-- Name -->
                                        <div class="w-1/3 min-w-0">
                                            <h3 class="font-bold text-slate-900 text-sm truncate">{{ $app->full_name }}</h3>
                                            <span class="text-xs text-slate-400">{{ $app->email ?: $app->phone ?: 'No contact details' }}</span>
                                        </div>

                                        <!-- Class -->
                                        <div class="w-1/5 text-slate-600 text-sm font-semibold">
                                            {{ $app->schoolClass?->name ?? 'N/A' }}
                                        </div>

                                        <!-- Status & Score -->
                                        <div class="w-1/4 text-center">
                                            <div class="flex flex-col items-center">
                                                @if($app->status === 'Pending Test')
                                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">Pending Test</span>
                                                @elseif($app->status === 'Passed')
                                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Passed ({{ number_format($app->test_score) }}%)</span>
                                                @elseif($app->status === 'Failed')
                                                    <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200">Failed ({{ number_format($app->test_score) }}%)</span>
                                                @elseif($app->status === 'Admitted')
                                                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200">Admitted</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Action link -->
                                        <div class="w-1/5 text-right flex items-center justify-end gap-2">
                                            @if($app->status === 'Pending Test')
                                                <button onclick="navigator.clipboard.writeText('{{ route('aptitude.take', $app->id) }}'); alert('Test link copied to clipboard!');" class="inline-flex items-center rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold px-3 py-2 transition duration-150 gap-1.5 border border-violet-200">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-5 10h5" />
                                                    </svg>
                                                    Copy Exam Link
                                                </button>
                                            @elseif($app->status === 'Passed')
                                                <button wire:click="admitStudent({{ $app->id }})" class="inline-flex items-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3.5 py-2 transition duration-150 shadow-md gap-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Admit Student
                                                </button>
                                            @else
                                                <span class="text-xs text-slate-400 font-semibold italic">No actions available</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </table>
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>

<div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-purple-50 to-pink-50/60 p-6 shadow-lg">
    <div class="flex items-center gap-3 mb-5">
        <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
        </div>
        <div class="text-lg font-black text-gray-900">Randomize Photos</div>
    </div>

    <div class="space-y-4">
        <p class="text-sm text-gray-600">Assign random photos from the Students and teachers folders to users.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button 
                onclick="randomizePhotos('students')" 
                id="btn-students"
                class="rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="btn-text">Randomize Students</span>
                <span class="btn-loader hidden">
                    <svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </span>
            </button>
            
            <button 
                onclick="randomizePhotos('teachers')" 
                id="btn-teachers"
                class="rounded-xl bg-pink-600 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-pink-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="btn-text">Randomize Teachers</span>
                <span class="btn-loader hidden">
                    <svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </span>
            </button>
        </div>
        
        <div id="randomize-message" class="hidden rounded-xl p-3 text-sm font-semibold"></div>
    </div>
</div>

<script>
function randomizePhotos(type) {
    const btn = document.getElementById(`btn-${type}`);
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    const messageDiv = document.getElementById('randomize-message');
    
    // Show loader
    btn.disabled = true;
    btnText.classList.add('hidden');
    btnLoader.classList.remove('hidden');
    messageDiv.classList.add('hidden');
    
    fetch('{{ route("photos.randomize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ type: type })
    })
    .then(response => response.json())
    .then(data => {
        // Hide loader
        btn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
        
        // Show message
        messageDiv.classList.remove('hidden');
        if (data.success) {
            messageDiv.className = 'rounded-xl p-3 text-sm font-semibold bg-green-100 text-green-800';
            messageDiv.textContent = data.message;
            
            // Reload page after 1 second
            setTimeout(() => window.location.reload(), 1000);
        } else {
            messageDiv.className = 'rounded-xl p-3 text-sm font-semibold bg-red-100 text-red-800';
            messageDiv.textContent = data.message || 'An error occurred';
        }
    })
    .catch(error => {
        // Hide loader on error
        btn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
        
        // Show error message
        messageDiv.classList.remove('hidden');
        messageDiv.className = 'rounded-xl p-3 text-sm font-semibold bg-red-100 text-red-800';
        messageDiv.textContent = 'Network error: ' + error.message;
    });
}
</script>

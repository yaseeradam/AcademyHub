// Add this to the browser console to check if Livewire is working
console.log('Livewire loaded:', typeof window.Livewire !== 'undefined');
console.log('Alpine loaded:', typeof window.Alpine !== 'undefined');

// Check if the parent management component is loaded
if (typeof window.Livewire !== 'undefined') {
    console.log('Livewire components:', window.Livewire.all());
}

// Test button click manually
document.addEventListener('DOMContentLoaded', function() {
    const button = document.querySelector('[wire\\:click="openCreateModal"]');
    if (button) {
        console.log('Create Parent button found:', button);
        button.addEventListener('click', function() {
            console.log('Button clicked via event listener');
        });
    } else {
        console.log('Create Parent button NOT found');
    }
});
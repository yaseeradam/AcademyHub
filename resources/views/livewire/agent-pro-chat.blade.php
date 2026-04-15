<div>
    <!-- Floating Button at bottom right -->
    <button wire:click="toggleChat" 
            class="fixed bottom-6 right-6 z-50 p-4 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 focus:outline-none transition-transform transform hover:scale-105"
            style="bottom: 2rem; right: 2rem; z-index: 9999; border-radius: 9999px; padding: 1rem; background-color: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); cursor: pointer; border: none;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.5rem; height: 1.5rem;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.38-.432 1.628-1.547 3.33-1.63 3.465A.75.75 0 004.25 22c.287 0 .584-.047.88-.145a9.664 9.664 0 004.532-1.928A9.098 9.098 0 0012 20.25z" />
        </svg>
    </button>

    <!-- Chat Modal -->
    @if($isOpen)
    <div class="fixed bottom-24 right-6 z-50 w-80 md:w-96 bg-white rounded-lg shadow-2xl flex flex-col overflow-hidden border border-gray-200"
         style="bottom: 6rem; right: 2rem; z-index: 9999; width: 350px; background-color: white; border-radius: 0.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; max-height: 600px; border: 1px solid #e5e7eb; position: fixed;">
        
        <!-- Header -->
        <div class="bg-blue-600 text-white p-4 flex justify-between items-center" style="background-color: #2563eb; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 0.5rem;">
                <!-- AI Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.5rem; height: 1.5rem;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
                <h3 class="font-bold font-sans m-0" style="margin: 0; font-family: sans-serif; font-weight: bold;">AgentPro</h3>
            </div>
            <button wire:click="toggleChat" class="text-white hover:text-gray-200" style="background: none; border: none; color: white; cursor: pointer; padding: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.5rem; height: 1.5rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col space-y-4" style="flex: 1; padding: 1rem; overflow-y: auto; background-color: #f9fafb; display: flex; flex-direction: column; gap: 1rem; height: 350px;">
            @foreach($messages as $msg)
                <div class="flex w-full {{ $msg['role'] == 'user' ? 'justify-end' : 'justify-start' }}"
                     style="display: flex; width: 100%; justify-content: {{ $msg['role'] == 'user' ? 'flex-end' : 'flex-start' }};">
                     
                    <div class="max-w-[80%] rounded-lg p-3 text-sm shadow-sm {{ $msg['role'] == 'user' ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border border-gray-100' }}"
                         style="max-width: 80%; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); word-wrap: break-word; white-space: pre-wrap; font-family: sans-serif; {{ $msg['role'] == 'user' ? 'background-color: #2563eb; color: white; border-bottom-right-radius: 0;' : 'background-color: white; color: #1f2937; border-bottom-left-radius: 0; border: 1px solid #f3f4f6;' }}">
                        {{ $msg['content'] }}
                    </div>
                </div>
            @endforeach
            <div wire:loading wire:target="sendMessage" class="text-xs text-gray-500 italic" style="font-size: 0.75rem; color: #6b7280; font-style: italic;">AgentPro is thinking...</div>
        </div>

        <!-- Input Area -->
        <form wire:submit.prevent="sendMessage" class="p-3 bg-white border-t border-gray-200 flex gap-2" style="padding: 0.75rem; background-color: white; border-top: 1px solid #e5e7eb; display: flex; gap: 0.5rem;">
            <input type="text" wire:model="question" placeholder="Ask AgentPro..." 
                   class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                   style="flex: 1; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; outline: none; font-family: sans-serif;">
            <button type="submit" 
                    class="bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
                    style="background-color: #2563eb; color: white; padding: 0.5rem; border-radius: 0.375rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                    wire:loading.attr="disabled" wire:target="sendMessage">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 1.25rem; height: 1.25rem;">
                  <path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z" />
                </svg>
            </button>
        </form>
    </div>
    @endif
</div>

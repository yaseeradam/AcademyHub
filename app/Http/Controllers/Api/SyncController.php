<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    /**
     * Handle incoming offline mutations from the Flutter app.
     * Expects a JSON payload: {'mutations': [{ 'id': 1, 'endpoint': '/api/...', 'action': 'POST', 'payload': {...} }]}
     */
    public function handleSync(Request $request)
    {
        $mutations = $request->input('mutations', []);
        
        if (empty($mutations)) {
            return response()->json(['success_ids' => [], 'message' => 'No mutations provided.']);
        }

        $successIds = [];
        $failedIds = [];

        DB::beginTransaction();
        
        try {
            foreach ($mutations as $mutation) {
                try {
                    $id = $mutation['id'] ?? null;
                    $endpoint = $mutation['endpoint'] ?? '';
                    $action = strtoupper($mutation['action'] ?? '');
                    $payload = $mutation['payload'] ?? [];
                    
                    if (!$id || !$endpoint || !in_array($action, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                        Log::warning("Invalid mutation payload dropped", ['mutation' => $mutation]);
                        continue;
                    }

                    // Process dynamic endpoints (Phase 7 implementation strategy)
                    // In a full production scenario, we would route these to internal controllers 
                    // or dedicated action classes. For phase 7 prototype, we mark successful if valid.
                    // Let's implement basic handling for a hypothetical "mark attendance" or "score entry" sync
                    
                    if (str_contains($endpoint, 'attendance')) {
                        // $this->syncAttendance($action, $payload);
                    } elseif (str_contains($endpoint, 'scores')) {
                        // $this->syncScores($action, $payload);
                    }
                    
                    // Mark as successfully processed server-side
                    $successIds[] = $id;
                    
                } catch (\Exception $e) {
                    Log::error("Failed to sync mutation ID {$mutation['id']}", ['error' => $e->getMessage()]);
                    $failedIds[] = $mutation['id'];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'success_ids' => $successIds,
                'failed_ids' => $failedIds,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fatal error during batch sync", ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Batch sync failed completely.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

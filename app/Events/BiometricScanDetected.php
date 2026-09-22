<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by ZkTecoController whenever ANY person (teacher, staff, OR student)
 * successfully scans their fingerprint or RFID card on the ZKTeco device.
 *
 * Broadcasts on:  private-biometric.scans.{tenantId}
 *
 * Accepts a pre-built payload array so it works for both User (staff) and
 * Student models without needing separate event classes.
 *
 * Uses ShouldBroadcastNow so the push is synchronous — no queue latency.
 */
class BiometricScanDetected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $scan;

    /**
     * @param  array  $payload  Pre-built scan payload (see ZkTecoController)
     */
    public function __construct(array $payload)
    {
        $this->scan = $payload;
    }

    public function broadcastOn(): array
    {
        $tenantId = $this->scan['tenant_id'] ?? 1;
        $channels = [
            new PrivateChannel('biometric.scans.' . $tenantId),
        ];
        if ((int)$tenantId !== 0) {
            $channels[] = new PrivateChannel('biometric.scans.0');
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'BiometricScanDetected';
    }

    public function broadcastWith(): array
    {
        return ['scan' => $this->scan];
    }
}

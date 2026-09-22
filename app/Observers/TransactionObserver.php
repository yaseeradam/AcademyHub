<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Support\WhatsAppService;

class TransactionObserver
{
    /**
     * Listen to the saved event of Transaction.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function saved(Transaction $transaction): void
    {
        if ($transaction->type !== 'Income') {
            return;
        }

        $student = $transaction->student;
        if (!$student) {
            return;
        }

        $tenant = $transaction->tenant;
        if ($tenant) {
            if (!app()->bound('currentTenant')) {
                app()->instance('currentTenant', $tenant);
            }
            \App\Support\TenantSettings::loadToConfig();
        }

        $parents = $student->parents()
            ->where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->get();

        foreach ($parents as $parent) {
            $apiKey = config('services.whatsapp.api_key');
            $currency = config('academyhub.currency_symbol', '₦');
            
            // Generate full absolute URL to our endpoint
            $receiptUrl = route('whatsapp.receipt', [
                'transaction' => $transaction->id,
                'key'         => $apiKey
            ]);
            
            $message = "🧾 *Payment Confirmed:*\n\n" .
                       "A payment of *{$currency}" . number_format($transaction->amount_paid, 2) . "* has been confirmed for *{$student->full_name}* ( tuition/fee category: *{$transaction->category}* ). Your official receipt is attached below.";
            
            WhatsAppService::sendMessage(
                $parent->whatsapp_phone, 
                $message, 
                $receiptUrl, 
                "receipt-{$transaction->receipt_number}.pdf",
                "Receipt #{$transaction->receipt_number}"
            );
        }
    }
}

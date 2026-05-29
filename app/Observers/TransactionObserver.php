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
        // =========================================================================
        // COMMENTED OUT FOR NOW - parents cannot make payments through our app yet
        // =========================================================================
        /*
        if ($transaction->type !== 'Income') {
            return;
        }

        $student = $transaction->student;
        if (!$student) {
            return;
        }

        $parents = $student->parents()
            ->where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->get();

        foreach ($parents as $parent) {
            $apiKey = config('services.whatsapp.api_key');
            $host = request()->schemeAndHttpHost();
            
            // Webhook payload to send the PDF invoice receipt
            $receiptUrl = "{$host}/api/whatsapp/receipt/{$transaction->id}?key={$apiKey}";
            
            $message = "🧾 *Payment Confirmed:* A payment of *NGN " . number_format($transaction->amount_paid, 2) . "* has been confirmed for *{$student->full_name}* ( tuition/fee category: *{$transaction->category}* ). Your official receipt is attached below.";
            
            WhatsAppService::sendMessage(
                $parent->whatsapp_phone, 
                $message, 
                $receiptUrl, 
                "receipt-{$transaction->receipt_number}.pdf",
                "Receipt #{$transaction->receipt_number}"
            );
        }
        */
    }
}

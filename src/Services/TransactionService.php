<?php

namespace EmilKitua\ClickPesa\Services;

use EmilKitua\ClickPesa\Models\ClickPesaPayment;

class TransactionService
{
    /**
     * Create a new payment record when initiating USSD or Card payment.
     *
     * @param array $data
     *  - reference_id (string) - required
     *  - payment_method ('ussd'|'card') - required
     *  - phone_number (string|null)
     *  - card_number_masked (string|null)
     *  - amount (float) - required
     *  - currency (string) - default 'TZS'
     *  - request_payload (array|string|null)
     *  - status (string) - optional, default 'pending'
     *
     * @return ClickPesaPayment
     */
    public static function createPayment(array $data): ClickPesaPayment
    {
        // Normalize JSON payloads if given as string
        if (isset($data['request_payload']) && is_string($data['request_payload'])) {
            $data['request_payload'] = json_decode($data['request_payload'], true);
        }

        // Fill defaults
        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['status'] = $data['status'] ?? 'pending';

        return ClickPesaPayment::create($data);
    }

    /**
     * Update payment status and response payload.
     *
     * @param string $referenceId
     * @param string $status
     * @param array|string|null $responsePayload
     * @param string|null $statusDetail
     * @param string|null $externalId
     *
     * @return bool
     */
    public static function updatePaymentStatus(string $referenceId, string $status, $responsePayload = null, ?string $statusDetail = null, ?string $externalId = null): bool
    {
        $payment = ClickPesaPayment::where('reference_id', $referenceId)->first();

        if (!$payment) {
            return false;
        }

        $payment->status = $status;
        $payment->status_detail = $statusDetail;

        if ($responsePayload) {
            if (is_string($responsePayload)) {
                $responsePayload = json_decode($responsePayload, true);
            }
            $payment->response_payload = $responsePayload;
        }

        if ($externalId) {
            $payment->external_id = $externalId;
        }

        if ($status === 'successful') {
            $payment->paid_at = now();
        }

        return $payment->save();
    }
}

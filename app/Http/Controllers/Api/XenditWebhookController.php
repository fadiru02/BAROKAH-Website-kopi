<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class XenditWebhookController extends Controller
{
    public function handleInvoice(Request $request)
    {
        $callbackToken = $request->header('x-callback-token');
        $serverToken = config('services.xendit.callback_token');

        if ($callbackToken !== $serverToken) {
            return response()->json(['message' => 'Unauthorized: Invalid Token'], 403);
        }
        // 1. Ambil Data dari Xendit
        $data = $request->all();
        $externalId = $data['external_id'];
        $status = $data['status']; // SETTLED, EXPIRED, dll

        // 2. Cari data Payment berdasarkan external_id
        $payment = Payment::where('external_id', $externalId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // 3. Update status menggunakan Database Transaction agar aman
        DB::transaction(function () use ($payment, $data, $status) {
    // 1. Update Payment (Berhasil)
            $payment->update([
                'status' => $status,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_channel' => $data['payment_channel'] ?? null,
                'paid_at' => isset($data['paid_at']) ? date('Y-m-d H:i:s', strtotime($data['paid_at'])) : null,
            ]);

            // 2. Ambil Transaksi (Induknya)
            $transaction = $payment->transaction; // Pastikan ini tidak null

            if ($transaction) {
                if (in_array($status, ['PAID', 'SETTLED'])) {
    // Panggil dengan tanda kurung () untuk mendapatkan Query Builder
                    $payment->transaction()->update([
                        'status' => 'processing',
                        'payment_status' => 'paid'
                    ]);
                }
                elseif ($status === 'EXPIRED') {
                    $payment->transaction()->update(['status' => 'cancelled']);
                }
            } else {
                // Jika masuk sini, berarti relasi di Model Payment.php belum ada
                \Log::error("Transaksi tidak ditemukan untuk Payment ID: " . $payment->id);
            }
        });


        return response()->json(['message' => 'Webhook processed']);
    }
}

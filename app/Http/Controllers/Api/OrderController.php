<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.roast_level' => 'nullable|string',
            'items.*.note' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $orderItems = [];

            // 2. Hitung Subtotal, Cek Stok & Siapkan Item
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Cek stok sebelum proses
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok untuk {$product->name} tidak mencukupi. Tersedia: {$product->stock}, Diminta: {$item['quantity']}");
                }
                
                $priceAtPurchase = $product->price;
                $lineTotal = $priceAtPurchase * $item['quantity'];
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $priceAtPurchase,
                    'roast_level' => $item['roast_level'] ?? null,
                    'note' => $item['note'] ?? null,
                ];
                
                // Kurangi stok
                $product->decrement('stock', $item['quantity']);
            }

            // 3. Simpan Transaksi
            $transaction = Transaction::create([
                'user_id' => $request->user_id ?? 1, // Sementara manual untuk test
                'invoice_number' => 'INV-' . strtoupper(Str::random(10)),
                'subtotal' => $subtotal,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'grand_total' => $subtotal + ($request->shipping_cost ?? 0),
                'status' => 'pending',
                'note' => $request->note,
            ]);

            // 4. Simpan Order Items
            $transaction->items()->createMany($orderItems);

                        $config = new Configuration();
            $config->setApiKey(config('services.xendit.api_key')); 
            $apiInstance = new InvoiceApi(null, $config);

            // PERBAIKAN: Gunakan invoice_number yang sama untuk Xendit dan Database
            $external_id = $transaction->invoice_number; 

            $create_invoice_request = new CreateInvoiceRequest([
                'external_id' => $external_id, // Sekarang sinkron
                'amount' => (float) $transaction->grand_total,
                'payer_email' => $request->user_email ?? 'customer@example.com',
                'description' => 'Pembayaran Kopi - ' . $transaction->invoice_number,
                'invoice_duration' => 86400,
            ]);

            $xendit_invoice = $apiInstance->createInvoice($create_invoice_request);

            // Simpan ke tabel Payments
            $transaction->payments()->create([
                'external_id' => $external_id, // Harus sama dengan yang dikirim ke Xendit
                'xendit_id' => $xendit_invoice['id'],
                'checkout_link' => $xendit_invoice['invoice_url'],
                'amount' => $transaction->grand_total,
                'status' => 'PENDING',
            ]);

                return response()->json([
                    'message' => 'Order created successfully',
                    'checkout_link' => $xendit_invoice['invoice_url'],
                    'data' => $transaction->load(['items.product', 'payments'])
                ], 201);
        });
    }
}
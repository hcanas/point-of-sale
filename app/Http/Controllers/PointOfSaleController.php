<?php

namespace App\Http\Controllers;

use App\Enums\MemberLedgerType;
use App\Enums\StockMovementType;
use App\Http\Requests\CompleteSaleRequest;
use App\Models\Member;
use App\Models\MemberLedger;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PointOfSaleController extends Controller
{
    public function index()
    {
        return Inertia::render('PointOfSale/Index');
    }

    public function store(CompleteSaleRequest $request)
    {
        $validated = $request->validated();

        $productIds = collect($validated['items'])->pluck('id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($validated['items'] as $index => $item) {
            $product = $products->get($item['id']);
            $validated['items'][$index]['product_id'] = $product->id;
            $validated['items'][$index]['price'] = $product->price;
            $validated['items'][$index]['product'] = $product;
        }

        return DB::transaction(function () use ($validated) {
            $member = Member::find($validated['member_id']);
            $totalAmount = collect($validated['items'])->sum(fn ($item) => $item['quantity'] * $item['price']);
            $amountTendered = $validated['amount_tendered'];
            $changeGiven = max(0, $amountTendered - $totalAmount);

            $sale = Sale::create([
                'member_id' => $member->id,
                'member_name' => $member->formal_name,
                'member_short_name' => $member->first_name,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountTendered,
                'amount_tendered' => $amountTendered,
                'change_given' => $changeGiven,
            ]);

            foreach ($validated['items'] as $item) {
                $product = $item['product'];
                $subtotal = $item['quantity'] * $item['price'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal,
                    'created_by' => Auth::id(),
                ]);

                $afterQuantity = $product->stock - $item['quantity'];
                $product->update(['stock' => $afterQuantity]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity' => -$item['quantity'],
                    'after_quantity' => $afterQuantity,
                    'reference_type' => StockMovementType::Sale,
                    'reference_id' => $sale->id,
                    'notes' => "Sale #{$sale->reference_number}",
                    'created_by' => Auth::id(),
                ]);
            }

            $balanceAfter = $member->outstanding_balance + $totalAmount;

            MemberLedger::create([
                'member_id' => $member->id,
                'amount' => $totalAmount,
                'balance_after' => $balanceAfter,
                'reference_type' => MemberLedgerType::Purchase,
                'reference_id' => $sale->id,
                'notes' => "Sale #{$sale->reference_number}",
            ]);

            if ($amountTendered > 0) {
                $paymentAmount = min($amountTendered, $totalAmount);
                $balanceAfterPayment = $balanceAfter - $paymentAmount;

                MemberLedger::create([
                    'member_id' => $member->id,
                    'amount' => -$paymentAmount,
                    'balance_after' => $balanceAfterPayment,
                    'reference_type' => MemberLedgerType::Payment,
                    'reference_id' => $sale->id,
                    'notes' => "Payment for Sale #{$sale->reference_number}",
                ]);

                $member->update(['outstanding_balance' => $balanceAfterPayment]);
            } else {
                $member->update(['outstanding_balance' => $balanceAfter]);
            }

            return response()->json([
                'sale' => $sale->load('items.product'),
                'success' => true,
            ]);
        });
    }
}

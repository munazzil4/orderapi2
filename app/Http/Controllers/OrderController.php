<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Jobs\ProcessNewOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'order_value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->order_value = $request->input('order_value');
        $order->save();

        // Dispatch the job to process the order
        ProcessNewOrder::dispatch($order);

        return response()->json([
            'order_id' => $order->id,
            'status' => 'queued',
        ], 201);
    }
}

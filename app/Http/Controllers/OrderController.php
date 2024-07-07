<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Event;
use App\Models\Order;
use App\Models\Product;
use App\Models\Session;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    public function allOrders(Request $request)
    {
        $user_id = $request->header('user_id');
        $orders = DB::table('orders')->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => true, 'orders' => [], 'count' => 0], 404);
        }

        $allOrders = $orders->map(function ($order) {
            $orderDetails = json_decode($order->order_details, true);

            $totalProductQuantity = 0;
            foreach ($orderDetails['products'] as $product) {
                $totalProductQuantity += $product['quantity'];
            }

            $user = User::where('user_id', $order->user_id)->first();
            return [
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'order_amount' => $order->order_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'order_quantity' => $totalProductQuantity + count($orderDetails['sessions']) + count($orderDetails['events']),
                'order_details' => $orderDetails
            ];
        });
        return response()->json(['success' => true, 'orders' => $allOrders, 'count' => count($allOrders)], 200);
    }

    public function index(Request $request)
    {
        $user_id = $request->header('user_id');

        $orders = DB::table('orders')->where('user_id', $user_id)->where('payment_status', 'paid')->get();

        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No orders found for this cart'], 404);
        }

        $allOrders = $orders->map(function ($order) {
            $orderDetails = json_decode($order->order_details, true);
            $parent = DB::table('users')->where('user_id', $order->user_id)->first();

            $totalProductQuantity = 0;
            foreach ($orderDetails['products'] as $product) {
                $totalProductQuantity += $product['quantity'];
            }

            return [
                'order_id' => $order->order_id,
                'user_id' => $order->user_id,
                'first_name' => $parent->first_name,
                'last_name' => $parent->last_name,
                'order_amount' => $order->order_amount,
                'order_quantity' => $totalProductQuantity + count($orderDetails['sessions']) + count($orderDetails['events']),
                'order_details' => $orderDetails,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ];
        });

        return response()->json(['success' => true, 'orders' => $allOrders, 'count' => count($allOrders)], 200);
    }

    public function myorder(Request $request)
    {
        $user_id = $request->header('user_id');

        $latestOrder = DB::table('orders')
            ->where('user_id', $user_id)
            ->orderByDesc('order_id')
            ->first();

        if (!$latestOrder) {
            return response()->json(['success' => false, 'error' => 'No orders found for this user'], 404);
        }

        $orderDetails = json_decode($latestOrder->order_details, true);

        $totalProductQuantity = 0;
        foreach ($orderDetails['products'] as $product) {
            $totalProductQuantity += $product['quantity'];
        }
        $parent = DB::table('users')->where('user_id', $latestOrder->user_id)->first();

        $responseData = [
            'order_id' => $latestOrder->order_id,
            'user_id' => $latestOrder->user_id,
            'first_name' => $parent->first_name,
            'last_name' => $parent->last_name,
            'order_quantity' =>  $totalProductQuantity + count($orderDetails['sessions']) + count($orderDetails['events']),
            'order_amount' => $latestOrder->order_amount,
            'order_details' => $orderDetails,
        ];

        return response()->json(['success' => true, 'order' => $responseData], 200);
    }

    public function updateStatus(Request $request)
    {

        $user_id = $request->header('user_id');

        $order_id = $request->input('order_id');
        $order = Order::find($order_id);

        if (!$order) {
            return response()->json(['success' => false, 'error' => 'order not found'], 404);
        }

        if ($request->filled('status')) {
            $order->status = $request->input('status');
        }

        $order->save();

        return response()->json(['success' => true, 'message' => 'order updated successfully', 'order' => $order]);
    }

    public function confirmOrder(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,cart_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }
        $cart = Cart::find($request->input('cart_id'));
        $totalAmount = $cart->total_amount;

        if ($cart->total_amount < 25) {
            return response()->json(['success' => false, 'error' => 'Minimum order amount is 25.'], 422);
        }

        $productIds = DB::table('product_cart')
            ->where('cart_id', $cart->cart_id)
            ->pluck('product_id')
            ->toArray();

        $products = Product::whereIn('products.product_id', $productIds)
            ->join('parent_product', 'products.product_id', '=', 'parent_product.product_id')
            ->where('parent_product.user_id', $cart->user_id)
            ->select('products.*', 'parent_product.quantity')
            ->get();

        $sessions = Session::where('cart_id', $cart->cart_id)->get();
        foreach ($sessions as $session) {
            $session->status = 'reserved';
            $session->save();
        }

        $eventIds = DB::table('carts')
            ->where('cart_id', $cart->cart_id)
            ->pluck('event_id')
            ->toArray();

        $events = Event::whereIn('event_id', $eventIds)->get();

        if ($events->isEmpty()) {
            $events = [];
        }

        $orderDetails = [
            'sessions' => $sessions,
            'products' => $products,
            'events' => $events,
        ];

        $totalProductQuantity = 0;
        foreach ($products as $product) {
            $totalProductQuantity += $product->quantity;
        }

        $orderDetailsJson = json_encode($orderDetails);

        $order = new Order();
        $order->order_amount = $totalAmount;
        $order->order_details = json_encode($orderDetails);
        $order->order_number = rand(1, 1000);
        $order->user_id = $user_id;
        $order->save();


        //$cart->delete();

        $response = [
            'success' => true,
            'message' => 'Order confirmed successfully',
            'orderDetails' => $orderDetails,
            'orderAmount' => $order->order_amount,
            'order_id' => $order->order_id,
            'order_number' => $order->order_number,
            'order_quantity' => count($sessions) + $totalProductQuantity + count($events),
        ];

        return response()->json($response, 200);
    }


    public function applyVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
            'voucher_code' => 'required|exists:vouchers,voucher_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $order = Order::find($request->input('order_id'));
        $voucher = Voucher::where('voucher_code', $request->input('voucher_code'))->first();

        if ($order && $voucher) {

            $cart = Cart::find($order->cart_id);

            if ($order) {
                $user_id = $order->user_id;
                if ($voucher->parents()->where('parent_voucher.user_id', $user_id)->exists()) {
                    return response()->json(['success' => false, 'error' => 'This Voucher is already applied'], 404);
                } else {
                    $voucher->parents()->attach($user_id);
                }
            }

            $order->voucher_id = $voucher->voucher_id;
            $order->order_amount = round($order->order_amount - ($order->order_amount * ($voucher->voucher_percentage)));
            $order->save();

            return response()->json(['success' => true, 'message' => 'Voucher applied successfully', 'order' => $order], 200);
        }

        return response()->json(['success' => false, 'error' => 'Order or Voucher not found'], 404);
    }

    public function cancelVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $order = Order::find($request->input('order_id'));

        if ($order) {
            if (!$order->voucher_id) {
                return response()->json(['success' => false, 'error' => 'No voucher applied to this order'], 404);
            }

            $voucher = Voucher::find($order->voucher_id);

            if ($voucher) {
                $order->voucher_id = null;

                $order->order_amount = round($order->order_amount / (1 - ($voucher->voucher_percentage)));
                $order->save();

                $user_id = $order->user_id;
                $voucher->parents()->detach($user_id);

                return response()->json(['success' => true, 'message' => 'Voucher canceled successfully', 'order' => $order], 200);
            }

            return response()->json(['success' => false, 'error' => 'Voucher not found'], 404);
        }

        return response()->json(['success' => false, 'error' => 'Order not found'], 404);
    }

}

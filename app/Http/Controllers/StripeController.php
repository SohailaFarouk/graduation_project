<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use Stripe\Stripe;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StripeController extends Controller
{
    public function checkout()
    {
        $order = Order::get();
        return response()->json(['success' => true, 'order' => $order]);
    }

    public function confirmPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $user_id = $request->header('user_id');

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,order_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
            }

            $order_id = $request->input('order_id');
            $order = DB::table('orders')->where('order_id', $order_id)->first();

            if (!$order) {
                return response()->json(['success' => false, 'error' => 'Order not found'], 404);
            }

            $checkoutSessionUrl = $this->payment($order, $user_id);

            if (is_array($checkoutSessionUrl) && isset($checkoutSessionUrl['error'])) {
                return response()->json(['success' => false, 'error' => $checkoutSessionUrl['error']], 422);
            }

            DB::commit();
            return response()->json(['success' => true, 'checkout_url' => $checkoutSessionUrl]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            $order->delete();
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function payment($order, $user_id)
    {
        try {
            Stripe::setApiKey(config('stripe.sk'));

            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'EGP',
                        'unit_amount' => $order->order_amount * 100,
                        'product_data' => [
                            'name' => 'Order Details',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ];

            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('successOrderPayment', ['order_id' => $order->order_id, 'user_id' => $user_id]),
                'cancel_url' => route('cancelOrderPayment', ['order_id' => $order->order_id]),
            ]);

            return $checkoutSession->url;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function successPayment($order_id, $user_id)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('order_id', $order_id)->first();
            $payment = new Payment;
            $payment->order_id = $order_id;
            $payment->user_id = $user_id;
            $payment->payment_amount = $order->order_amount;
            $payment->save();

            $cart = Cart::where('user_id', $user_id)->orderBy('created_at', 'desc')->first();

            $sessions = Session::where('cart_id', $cart->cart_id)->get();
            foreach ($sessions as $session) {
                $session->status = 'payment';
                $session->save();
            }

            DB::table('parent_product')
                ->where('user_id', $cart->user_id)
                ->delete();

            Cart::create([
                'user_id' => $user_id,
                'total_amount' => 0,
            ]);

            $order->payment_status = 'paid';
            $order->save();

            DB::commit();
            return redirect('http://localhost:3000/previousorders');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function cancelPayment($order_id)
    {
        try {
            DB::beginTransaction();

            $order = Order::where('order_id', $order_id)->first();
            $order->delete();

            DB::commit();
            return redirect('http://localhost:3000/cart');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}

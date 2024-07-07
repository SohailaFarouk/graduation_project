<?php
namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;

class SubscriptionController extends Controller
{
    public function subscriptionCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_plan' => 'required|in:premium,regular',
            'subscription_price' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('subscription_plan') === 'premium' && intval($value) !== 200) {
                        $fail('The subscription price must be 200 for the premium plan.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], 422);
        }

        $subscription = new Subscription();
        $subscription->subscription_plan = $request->input('subscription_plan');
        $subscription->subscription_price = $request->input('subscription_price');
        $subscription->save();

        return response()->json([
            'success' => true, 'subscription details' => [
                'subscription_plan' => $subscription->subscription_plan,
                'subscription_price' => $subscription->subscription_price,
            ]
        ]);
    }

    public function subscribe(Request $request)
    {
        try {
            DB::beginTransaction();

            $user_id = $request->header('user_id');

            $validator = Validator::make($request->all(), [
                'subscription_id' => 'required|exists:subscriptions,subscription_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
            }

            $subscription = Subscription::find($request->input('subscription_id'));
            $parent = Parents::where('user_id', $user_id)->first();

            if (!$parent) {
                return response()->json(['success' => false, 'error' => 'Parent not found.'], 404);
            }

            if ($subscription->subscription_price == 0) {
                $parent->update([
                    'subscription_id' => $subscription->subscription_id,
                ]);

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Free Subscription created successfully', 'subscription details' => $parent->subscription]);
            } else {
                $checkoutSessionUrl = $this->payment($subscription, $user_id);
                if (is_array($checkoutSessionUrl) && isset($checkoutSessionUrl['error'])) {
                    return response()->json(['success' => false, 'error' => $checkoutSessionUrl['error']], 422);
                }
                DB::commit();
                return response()->json(['success' => true, 'checkout_url' => $checkoutSessionUrl]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function payment($subscription, $user_id)
    {
        try {
            Stripe::setApiKey(config('stripe.sk'));

            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'EGP',
                        'unit_amount' => 200 * 100, // Amount in cents
                        'product_data' => [
                            'name' => 'Subscription Details',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ];

            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('successSubscribePayment', ['subscription_id' => $subscription->subscription_id, 'user_id' => $user_id]),
                'cancel_url' => 'http://localhost:3000/homepage'
            ]);

            return $checkoutSession->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function successSubscribePayment($subscription_id, $user_id)
    {
        try {
            DB::beginTransaction();

            $payment = new Payment();
            $payment->subscription_id = $subscription_id;
            $payment->user_id = $user_id;
            $payment->payment_amount = 200;
            $payment->save();

            $parent = Parents::where('user_id', $user_id)->first();
            $subscription = Subscription::where('subscription_id', $subscription_id)->first();

            if ($parent) {
                $subscriptionDate = $subscription->subscription_plan === 'free' ? null : now();
                $parent->update([
                    'subscription_id' => $subscription->subscription_id,
                    'subscription_date' => $subscriptionDate,
                ]);
            }

            DB::commit();
            return redirect('http://localhost:3000/subscriptionsuccess');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }
}

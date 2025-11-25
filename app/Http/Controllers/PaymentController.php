<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller{

    public function createCheckoutSession(Request $request){
        try{
            Stripe::setApiKey(config('services.stripe.secret'));

            $YOUR_DOMAIN = 'http://localhost:5173';

            $lineItems = [];
            foreach($request->products as $product){
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product['name'],
                        ],
                        'unit_amount' => intval($product['price']*100),
                    ],
                    'quantity' => $product['quantity'],
                ];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'locale' => 'en',
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $YOUR_DOMAIN . '/payment-sucess',
                'cancel_url' => $YOUR_DOMAIN . '/payment-cancel'
            ]);

            return response()->json(['url' => $session->url]);
        }catch(\Exception $e){
            Log::error('Stripe Checkout :' . $e->getMessage());
            return response()->json(['error' => 'Payment Initialization Failed'], 500);
        }
    }

    // public function createPayPalOrder(Request $request){
    //     $amount = $request->amount;

    //     $response = Http::withBasicAuth(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'))
    //     ->post(env('PAYPAL_API_URL').'/v2/checkout/orders', [
    //         'intent' => 'CAPTURE',
    //         'purchase_units' => [[
    //             'amount' => [
    //                 'currency_code' => 'USD',
    //                 'value' => $amount,
    //             ],
    //         ]],
    //     ]);

    //     return response()->json($response->json());
    // }

    // public function capturePayPalOrder(Request $request){
    //     $orderId = $request->orderID;
    //     $response = Http::withBasicAuth(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'))
    //     ->post(env('PAYPAL_API_URL')."/v2/checkout/orders/{$orderId}/capture");

    //     return response()->json($response->json());
    // }

    public function createPayPalOrder(Request $request){
    try {
        $amount = $request->amount;
        $paypalClientId = env('PAYPAL_CLIENT_ID');
        $paypalSecret = env('PAYPAL_SECRET');
        $paypalApiUrl = env('PAYPAL_API_URL', 'https://api-m.sandbox.paypal.com');

        // Getting Access Token
        $tokenResponse = Http::asForm()
            ->withoutVerifying()
            ->withBasicAuth($paypalClientId, $paypalSecret)
            ->post("$paypalApiUrl/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($tokenResponse->failed()) {
            return response()->json(['error' => 'Unable to generate PayPal token'], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // Creating An Order
        $orderResponse = Http::withToken($accessToken)
            ->withoutVerifying()
            ->post("$paypalApiUrl/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                ]],
            ]);

        return response()->json($orderResponse->json());
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function capturePayPalOrder(Request $request){
    try {
        $orderId = $request->orderID;
        $paypalClientId = env('PAYPAL_CLIENT_ID');
        $paypalSecret = env('PAYPAL_SECRET');
        $paypalApiUrl = env('PAYPAL_API_URL', 'https://api-m.sandbox.paypal.com');

        // Getting Access Token
        $tokenResponse = Http::asForm()
            ->withBasicAuth($paypalClientId, $paypalSecret)
            ->post("$paypalApiUrl/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($tokenResponse->failed()) {
            return response()->json(['error' => 'Unable to generate PayPal token'], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // Capturing Payment
        $captureResponse = Http::withToken($accessToken)
            ->post("$paypalApiUrl/v2/checkout/orders/$orderId/capture");

        return response()->json($captureResponse->json());
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}

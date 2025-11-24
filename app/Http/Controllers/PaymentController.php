<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

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
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $YOUR_DOMAIN . '/payment-sucess',
                'cancel_url' => $YOUR_DOMAIN . '/payment-cancel'
            ]);

            return response()->json(['url' => $session->url]);
        }catch(\Exception $e){
            Log::error('Stripe Checkout Error:' . $e->getMessage());
            return response()->json(['error' => 'Payment Initialization Failed'], 500);
        }
    }
}

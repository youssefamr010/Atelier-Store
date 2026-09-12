<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email to the newsletter and return the welcome promo code.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($request->input('email')));
        $code = Setting::get('newsletter_discount_code', 'WELCOME10');

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            [
                'discount_code' => $code,
                'ip_address' => $request->ip(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => "Welcome to the ATELIER Connoisseurs Circle. Use code {$code} for 10% off your order.",
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoyaltyPointLedger;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontFeaturesController extends Controller
{
    public function index(): View
    {
        $settings = Setting::allAsMap();
        $subscribersCount = NewsletterSubscriber::where('is_active', true)->count();
        $totalPointsAwarded = LoyaltyPointLedger::where('points', '>', 0)->sum('points');
        $totalPointsRedeemed = abs((int) LoyaltyPointLedger::where('points', '<', 0)->sum('points'));

        $recentSubscribers = NewsletterSubscriber::latest()->take(10)->get();
        $topPointHolders = User::withSum('loyaltyLedgers as points_sum', 'points')
            ->having('points_sum', '>', 0)
            ->orderByDesc('points_sum')
            ->take(8)
            ->get();

        return view('admin.storefront_features.index', compact(
            'settings',
            'subscribersCount',
            'totalPointsAwarded',
            'totalPointsRedeemed',
            'recentSubscribers',
            'topPointHolders'
        ));
    }

    public function update(Request $request)
    {
        $keys = [
            // Trust Badges
            'trust_badges_enabled',
            'trust_badge_1_icon', 'trust_badge_1_title', 'trust_badge_1_sub',
            'trust_badge_2_icon', 'trust_badge_2_title', 'trust_badge_2_sub',
            'trust_badge_3_icon', 'trust_badge_3_title', 'trust_badge_3_sub',

            // Loyalty Program
            'loyalty_enabled',
            'loyalty_earn_rate_egp',       // e.g. 10 (1 pt per 10 EGP)
            'loyalty_redeem_pts_unit',     // e.g. 100
            'loyalty_redeem_discount_egp', // e.g. 50 (100 pts = 50 EGP)

            // Newsletter Popup
            'newsletter_popup_enabled',
            'newsletter_popup_delay_sec',
            'newsletter_popup_title',
            'newsletter_popup_subtitle',
            'newsletter_discount_code',

            // Urgency
            'urgency_indicator_enabled',
            'urgency_stock_threshold',

            // Brand Story
            'homepage_story_enabled',
            'homepage_story_title',
            'homepage_story_subtitle',
            'homepage_story_body',
            'homepage_story_image',
        ];

        // Handle Image upload for Story
        if ($request->hasFile('homepage_story_image_file')) {
            $file = $request->file('homepage_story_image_file');
            $filename = 'story-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('media', $filename, 'public');
            Setting::set('homepage_story_image', '/storage/' . $path);
        }

        foreach ($keys as $k) {
            if ($request->has($k)) {
                $val = $request->input($k);
                if (is_bool($val)) {
                    $val = $val ? '1' : '0';
                }
                Setting::set($k, (string) $val);
            }
        }

        // Checkbox booleans that may be missing when unchecked
        $booleans = [
            'trust_badges_enabled',
            'loyalty_enabled',
            'newsletter_popup_enabled',
            'urgency_indicator_enabled',
            'homepage_story_enabled',
        ];
        foreach ($booleans as $b) {
            if (!$request->has($b)) {
                Setting::set($b, '0');
            } else {
                Setting::set($b, '1');
            }
        }

        AuditLog::log('settings.storefront_features', 'setting', 0, 'Updated storefront features & customer experience settings');

        return back()->with('success', 'Storefront Features & Customer Experience configurations saved successfully.');
    }

    /**
     * Manual adjustment of customer loyalty points by Admin.
     */
    public function adjustCustomerPoints(Request $request, int $userId)
    {
        $request->validate([
            'points' => 'required|integer|not_in:0',
            'notes' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($userId);
        $points = (int) $request->input('points');
        $notes = $request->input('notes');

        LoyaltyPointLedger::create([
            'user_id' => $user->id,
            'points' => $points,
            'type' => 'manual_adjust',
            'notes' => "Admin Adjustment: {$notes}",
        ]);

        AuditLog::log('loyalty.manual_adjust', 'user', $user->id, "Manually adjusted {$points} loyalty points for user #{$user->id} ({$user->name})");

        return back()->with('success', "Updated points balance for {$user->name}. New balance: {$user->loyaltyPointsBalance()} pts.");
    }
}

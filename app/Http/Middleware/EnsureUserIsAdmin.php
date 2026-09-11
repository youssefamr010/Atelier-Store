<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request: Strict Admin Access Control.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. User must be authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Administrator login required.'
                ], 401);
            }

            return redirect()->route('admin.login')->with('error', 'Authentication required. Please sign in with administrator credentials.');
        }

        $user = Auth::user();
        $userEmail = strtolower(trim($user->email ?? ''));

        // Designated Super Admin emails list
        $adminEmailsRaw = \App\Models\Setting::get('admin_google_emails', 'monoahsec@gmail.com') . ',' . (env('ADMIN_GOOGLE_EMAILS') ?: 'monoahsec@gmail.com');
        $adminEmails = array_filter(array_map('trim', explode(',', str_replace(["\r\n", "\n", ";"], ',', mb_strtolower($adminEmailsRaw)))));
        if (!in_array('monoahsec@gmail.com', $adminEmails, true)) {
            $adminEmails[] = 'monoahsec@gmail.com';
        }

        $isDesignatedAdmin = in_array($userEmail, $adminEmails, true) || $userEmail === 'monoahsec@gmail.com';

        // Auto-heal / promote designated admin in database if not already done
        if ($isDesignatedAdmin && (!$user->is_admin || $user->admin_role !== 'super_admin')) {
            $user->is_admin = true;
            $user->admin_role = 'super_admin';
            $user->save();
        }

        // 2. User must have administrative privileges
        if (!$user->isAdmin() && !$isDesignatedAdmin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Administrator privileges required.'
                ], 403);
            }

            abort(403, 'Access Denied: You do not have administrative privileges to access this area.');
        }

        // 3. If specific role constraints are given (Super Admin bypasses all role constraints)
        if (!empty($roles) && !$isDesignatedAdmin && method_exists($user, 'hasRole') && !$user->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Insufficient administrative role permissions.'
                ], 403);
            }

            abort(403, 'Access Denied: Insufficient permissions.');
        }

        return $next($request);
    }
}


<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIntakeCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isClient() && ! $user->hasCompletedIntake()) {
            return redirect()->route('budget.intake');
        }

        return $next($request);
    }
}

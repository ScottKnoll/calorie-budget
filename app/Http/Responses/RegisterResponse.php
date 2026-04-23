<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        $user = auth()->user();

        if ($user->isClient()) {
            return redirect()->route('budget.intake');
        }

        if (session()->has('macros_prefill')) {
            return redirect()->route('budget.macros');
        }

        if (session()->has('setup_prefill')) {
            return redirect()->route('budget.setup');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

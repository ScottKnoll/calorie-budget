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

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

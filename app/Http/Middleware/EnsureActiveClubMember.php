<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveClubMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->status !== UserStatus::Active) {
            abort(403, 'This account cannot access the club.');
        }

        if ($user->currentMembership() === null) {
            return redirect()->route('clubs.index');
        }

        return $next($request);
    }
}

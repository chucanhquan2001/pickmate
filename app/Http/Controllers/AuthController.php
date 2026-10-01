<?php

namespace App\Http\Controllers;

use App\Enums\SocialProvider;
use App\Services\Auth\SocialAuthService;
use App\Services\Auth\SocialIdentity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Login');
    }

    public function redirect(string $provider): RedirectResponse
    {
        $this->provider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider, SocialAuthService $auth): RedirectResponse
    {
        $socialProvider = $this->provider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $auth->login(SocialIdentity::fromSocialite($socialProvider, $socialUser));
        } catch (AuthorizationException|RuntimeException $exception) {
            return redirect()->route('login')->withErrors([
                'auth' => $exception->getMessage(),
            ]);
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'auth' => 'Google or Facebook login failed.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $home = $user->club_id === null ? route('clubs.index') : route('dashboard');

        return redirect()->intended($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function provider(string $provider): SocialProvider
    {
        return SocialProvider::from($provider);
    }
}

<?php

namespace App\Services\Auth;

use App\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialIdentity
{
    public function __construct(
        public SocialProvider $provider,
        public string $providerUserId,
        public ?string $email,
        public bool $emailVerified,
        public string $name,
        public ?string $avatar,
    ) {}

    public static function fromSocialite(SocialProvider $provider, SocialiteUser $user): self
    {
        $raw = method_exists($user, 'getRaw') ? $user->getRaw() : [];
        $email = $user->getEmail();
        $email = is_string($email) && $email !== '' ? $email : null;
        $name = $user->getName();

        $verified = match ($provider) {
            SocialProvider::Google => filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
            SocialProvider::Facebook => $email !== null,
        };

        return new self(
            provider: $provider,
            providerUserId: (string) $user->getId(),
            email: $email,
            emailVerified: $verified,
            name: is_string($name) && $name !== '' ? $name : ucfirst($provider->value).' user',
            avatar: $user->getAvatar(),
        );
    }
}

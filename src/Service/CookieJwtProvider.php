<?php

declare(strict_types=1);

namespace App\Service\Mercure;

use App\Entity\ChatMessage;
use App\Entity\Discussion;

use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class CookieJwtProvider
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function __invoke(Discussion $discussion, ChatMessage $chatMessage): string
    {
        $signer = new Sha256();
        return (new Builder())
            ->withClaim('mercure', ['subscribe' => [sprintf('http://127.0.0.1:8000/project/13/discussion/%s', $discussion->getId())]]) // Attention le claim est différent qu'avec le JWTProvider. Ici on précise le topic privé que l'on souhaite avec le droit "d'accès"
            ->getToken($signer, new Key($this->key))
            ->__toString()
            ;
    }
}
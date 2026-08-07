<?php

declare(strict_types=1);

namespace Pin\Token;

use Pin\Support\DataBag;

/**
 * @property ?int $exp
 * @property ?int $iat
 * @property ?string $jti
 * @property ?int $expires
 */
class TokenPayload extends DataBag
{
}

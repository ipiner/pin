<?php

declare(strict_types=1);

namespace Pin\Token;

use Pin\Support\DataBag;

/**
 * Token 载荷。
 *
 * @property ?int $uid 用户 ID
 * @property ?int $exp 过期时间戳
 * @property ?int $iat 签发时间戳
 * @property ?string $jti 唯一标识
 * @property ?int $expires 有效期（秒）
 */
class TokenPayload extends DataBag
{
}

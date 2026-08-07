<?php

declare(strict_types=1);

namespace Pin\Errors;

use Pin\Errors\Attribute\Group;

/**
 * 系统错误码定义（统一错误枚举）
 *
 * 用于定义全局错误码体系，并通过 IError 提供标准化访问能力：
 * - 错误码（code）
 * - 错误信息（message）
 * - HTTP 状态码映射（statusCode）
 *
 * 该枚举作为系统错误的唯一来源
 *
 * 错误码分配：
 *
 * - 20xx：验证码
 * - 30xx：上传
 * - >= 10000：应用错误码
 * - 其余：ipiner/pin内部
 */
#[Group('pin::errors')]
enum Errors: string implements IError
{
    use Errorful;

    public const None = self::Success;

    case Success = '0|success';
    case Failed = '1|failed';

    case BadRequest = '400|bad_request';
    case Forbidden = '403|forbidden';
    case ModelNotFound = '404|model_not_found';
    case TokenMismatch = '419|token_mismatch';
    case ValidationFailed = '422|validation_failed';
    case TooManyRequests = '429|too_many_requests';
    case ServerError = '500|server_error';
    case AccessDenied = '4030|403|access_denied';
    case Unknown = '9999|unknown';

    // crud
    case CreateFailed = '1000|create_failed';
    case UpdateFailed = '1001|update_failed';
    case DeleteFailed = '1002|delete_failed';
    case DataVersionMismatch = '1003|data_version_mismatch';

    // auth
    case AuthUserNotFound = '1010|401|auth_user_not_found';
    case AuthTokenExpired = '1011|401|auth_token_expired';
    case AuthTokenInvalid = '1012|401|auth_token_invalid';
    case AuthTokenMissing = '1013|401|auth_token_missing';

    // token
    case TokenExpired = '1020|token_expired';
    case TokenInvalid = '1021|500|token_invalid';
    case TokenMissing = '1022|token_missing';

    // password
    case PasswordTooShort = '1030|422|password_too_short';
    case PasswordTooLong = '1031|422|password_too_long';
    case PasswordRequiresNumber = '1032|422|password_requires_number';
    case PasswordRequiresLetter = '1033|422|password_requires_letter';
    case PasswordRequiresLowercase = '1034|422|password_requires_lowercase';
    case PasswordRequiresUppercase = '1035|422|password_requires_uppercase';
    case PasswordRequiresMixedCase = '1036|422|password_requires_mixed_case';
    case PasswordRequiresSymbol = '1037|422|password_requires_symbol';
    case PasswordInsufficientTypes = '1038|422|password_insufficient_types';
    case PasswordRequiresAllTypes = '1039|422|password_requires_all_types';
    case PasswordContainsWhitespace = '1040|422|password_contains_whitespace';
    case PasswordSequenceTooLong = '1041|422|password_sequence_too_long';
    case PasswordTooManyRepeats = '1042|422|password_too_many_repeats';

    /**
     * 根据错误码获取错误定义
     *
     * 未命中时返回 Unknown
     */
    public static function get(int $code): IError
    {
        return Registry::get($code);
    }

    /**
     * 获取错误消息（支持占位符替换）
     */
    public static function getMessage(int $code, array $replace = []): string
    {
        return Registry::get($code)->message($replace);
    }
}

<?php
namespace AppLight\Util;

use Firebase\JWT\JWT;

class TokenHandler
{

    public static function getSignedJWTForUser($user_id)
    {
        $issued_at_time = time();
        $time_to_live = TIME_TO_LIVE;
        $token_expiration = $issued_at_time + $time_to_live;
        $payload = [
            'user_id' => $user_id,
            'iss'  => URL,
            'aud' => URL,
            'iat' => 1356999524,
            'exp' =>  time() + (10 * 365 * 24 * 60 * 60),
        ];

        return JWT::encode($payload, SECRET_KEY, 'HS256');
    }

}

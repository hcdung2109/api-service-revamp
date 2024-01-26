<?php

namespace AppLight\Middlewares;

use Buki\Router\Http\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Exception;

class AuthMiddlewares extends Middleware
{
    public function handle(Request $request)
    {
        global $appSession;

        $headers = static::getAuthorizationHeader();

        if (!is_null($headers)) {
            $token = static::getBearerToken($headers);

            if (!is_null($token)) {
                return static::validateJWTFromUser($token, $appSession);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getAuthorizationHeader()
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    public static function getBearerToken($headers)
    {
        // HEADER: Get the access token from the header
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        return false;
    }

    public function validateJWTFromUser($encoded_token, $appSession)
    {
        try {
            //  $msg = $appSession->getTier()->createMessage();

            $decoded = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $encoded_token)[1]))));
            $user_id = $decoded->user_id;
            $company_id = $decoded->company_id;
            // $sql = "SELECT d1.id FROM res_user d1 WHERE d1.status = 0 AND d1.id='" . $user_id . "'";
            // $msg->add("query", $sql);
            // $seen_id = $appSession->getTier()->getValue($msg);

            // if ($seen_id == "") {
            //     return [
            //         'status' => false,
            //         'message' => "Oops! Something went wrong. Please try again."
            //     ];
            // } else {
            $appSession->getConfig()->setProperty("session_user_id", $user_id);
            $appSession->getConfig()->setProperty("session_company_id", $company_id);
            $appSession->getConfig()->save();

            return true;
            //  }
        } catch (Exception $e) {
        }
    }
}

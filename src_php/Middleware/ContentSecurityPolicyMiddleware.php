<?php

namespace App\Middleware;

class ContentSecurityPolicyMiddleware
{
    /**
     * Aplica la cabecera de Content-Security-Policy para mitigar ataques XSS y Clickjacking.
     */
    public static function handle(): void
    {
        $policy = "default-src 'self'; ";
        $policy .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://kit.fontawesome.com; ";
        $policy .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; ";
        $policy .= "font-src 'self' https://fonts.gstatic.com https://ka-f.fontawesome.com; ";
        $policy .= "img-src 'self' data: https:; ";
        $policy .= "connect-src 'self' https://ka-f.fontawesome.com; ";
        $policy .= "frame-ancestors 'none'; ";
        $policy .= "object-src 'none';";

        header("Content-Security-Policy: $policy");
    }
}

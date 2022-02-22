<?php
namespace Webman\Domain\Middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class Domain implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $bind = config('plugin.webman.domain.app.bind', []);
        $domain = $request->host(true);
        $app = $request->app;
        $check_cb = config('plugin.webman.domain.app.check');
        if (!$check_cb($bind, $domain, $app)) {
            if (function_exists('notfound')) {
                return notfound();
            }
            $content_404 = is_file($file_404 = public_path() . '/404.html') ? file_get_contents($file_404) : '<h1>404 Not Found</h1>';
            return response($content_404, 404);
        }
        return $next($request);
    }
}
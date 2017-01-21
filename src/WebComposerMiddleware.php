<?php namespace GreyDev\WebComposer;

use Closure;

/**
 * Class WebComposerMiddleware
 * Middleware class that protects background tasks requested internally.
 *
 * @package GreyDev\WebComposer
 * @author Mohyaddin Alaoddin <mo7y.66[at]gmail.com>
 */
class WebComposerMiddleware{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(env('APP_KEY') != $request->header('app-key')){
			$accessLog = fopen('storage/composer/access.log', 'a');
			fwrite($accessLog, 'Unauthorized access of '.$request->url().' from '.$request->ip()
				.' at '.date('Y-m-d h:i:s A')."\n".json_encode($request->header(), JSON_PRETTY_PRINT)."\n");
			fclose($accessLog);
			return response('Access Forbidden', 403, ['Content-Type: text/plain']);
		}
		return $next($request);
	}
}
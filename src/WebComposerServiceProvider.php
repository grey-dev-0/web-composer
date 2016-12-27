<?php namespace GreyDev\WebComposer;

use Illuminate\Support\ServiceProvider;

class WebComposerServiceProvider extends ServiceProvider{
	public function register(){}

	public function boot(){
		$this->loadViewsFrom(__DIR__.'/../views/blade', 'web-composer');
		$this->publishes([
			__DIR__.'/../assets' => public_path('vendor/grey-dev-0/web-composer'),
			__DIR__.'/../config' => config_path()
		]);
		require_once __DIR__.'/routes.php';
	}
}
<?php
Route::group([
	'prefix' => config('web-composer.prefix'),
	'middleware' => config('web-composer.middleware'),
	'namespace' => 'GreyDev\WebComposer\Controllers'
], function(){
	Route::get('/', 'MainController@getIndex');
	Route::get('installed', 'MainController@getInstalled');
	Route::get('ajax-installed/{offset}/{length}', 'MainController@getAjaxInstalled');
	Route::get('ajax-all/{offset}/{length}', 'MainController@getAjaxAll');
	Route::get('all', 'MainController@getAll');
	Route::get('cache-all-packages', 'MainController@getCacheAllPackages');
	Route::post('refresh-package', 'MainController@postRefreshPackage');
});
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
	Route::post('refresh-package', 'MainController@postRefreshPackage');
	Route::post('remove-package', 'MainController@postRemovePackage');
});

Route::group([
	'prefix' => config('web-composer.prefix'),
	'middleware' => 'web-composer.tasks',
	'namespace' => 'GreyDev\WebComposer\Controllers'
], function(){
	Route::get('cache-all-packages', 'MainController@getCacheAllPackages');
	Route::post('update-package', 'MainController@postUpdatePackage');
	Route::post('task-remove-package', 'MainController@postTaskRemovePackage');
});
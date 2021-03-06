<?php
Route::group([
	'prefix' => config('web-composer.prefix'),
	'middleware' => config('web-composer.middleware'),
	'namespace' => 'GreyDev\WebComposer\Controllers'
], function(){
	Route::get('/', 'MainController@getIndex');
	Route::get('installed', 'MainController@getInstalled');
	Route::post('ajax-installed/{offset}/{length}', 'MainController@postAjaxInstalled');
	Route::post('ajax-all/{offset}/{length}', 'MainController@postAjaxAll');
	Route::get('all', 'MainController@getAll');
	Route::post('ajax-search/{cache}/{offset}/{length}', 'MainController@postAjaxSearch');
	Route::post('refresh-package', 'MainController@postRefreshPackage');
	Route::post('upgrade-package', 'MainController@postUpgradePacakge');
	Route::post('remove-package', 'MainController@postRemovePackage');
	Route::get('console', 'MainController@getConsole');
	Route::get('clear-console', 'MainController@getClearConsole');
});

Route::group([
	'prefix' => config('web-composer.prefix'),
	'middleware' => 'web-composer.tasks',
	'namespace' => 'GreyDev\WebComposer\Controllers'
], function(){
	Route::get('cache-all-packages', 'MainController@getCacheAllPackages');
	Route::post('update-package', 'MainController@postUpdatePackage');
	Route::post('task-upgrade-package', 'MainController@postTaskUpgradePackage');
	Route::post('task-remove-package', 'MainController@postTaskRemovePackage');
});
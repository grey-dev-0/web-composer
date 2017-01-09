<?php namespace GreyDev\WebComposer\Controllers;

use App\Http\Controllers\Controller, Request;
use GreyDev\WebComposer\PackageProcessor;

class MainController extends Controller{
	/**
	 * @var $packageProcessor PackageProcessor The package reader/writer.
	 */
	private $packageProcessor;

	public function __construct(){
		$this->packageProcessor = new PackageProcessor('storage/composer', url(config('web-composer.prefix')));
	}

	public function getIndex(){
		return redirect(url(config('web-composer.prefix').'/installed'));
	}

	public function getInstalled(){
		$packagesData = $this->packageProcessor->getInstalled();
		return view('web-composer::installed', $packagesData);
	}

	public function getAjaxInstalled($offset, $length){
		$packagesData = $this->packageProcessor->getAjaxInstalled($offset, $length);
		return response()->json($packagesData);
	}

	public function getAll(){
		$packagesData = $this->packageProcessor->getAll();
		return view('web-composer::all', $packagesData);
	}

	public function getAjaxAll($offset, $length){
		$packagesData = $this->packageProcessor->getAjaxAll($offset, $length);
		return response()->json($packagesData);
	}

	public function getCacheAllPackages(){
		$this->packageProcessor->taskCacheAllPackages();
	}

	public function postRefreshPackage(){
		return response()->json($this->packageProcessor->refreshPackage(Request::input('name')));
	}

	public function postUpdatePackage(){
		$this->packageProcessor->taskURefreshPackage(Request::input('package'), Request::input('file'));
		return response()->json(['response' => 0]);
	}
}
<?php namespace GreyDev\WebComposer\Bare;

use GreyDev\WebComposer\PackageProcessor, Smarty;

class MainController{
	/**
	 * @var $viewer Smarty smarty templating engine
	 */
	private $viewer;
	/**
	 * @var $packageProcessor PackageProcessor The package reader/writer.
	 */
	private $packageProcessor;
	/**
	 * @var string Default packages cache directory.
	 */
	private $cacheDir;

	public function __construct($requestMethod, $requestUri){
		$this->viewer = new Smarty();
		$this->viewer->setTemplateDir(__DIR__.'/../../views/smarty');
		$this->viewer->setCompileDir(__DIR__.'/../../views/smarty/compiled');
		$this->viewer->setConfigDir(__DIR__.'/../../views/smarty/config');
		$this->viewer->setCacheDir(__DIR__.'/../../views/smarty/cache');
		$this->viewer->configLoad(__DIR__.'/../../views/smarty/config/master.conf');
		$this->cacheDir = __DIR__.'/../../../../..'.$this->viewer->getConfigVariable('cacheDir');
		$this->packageProcessor = new PackageProcessor(
			$this->cacheDir,
			$this->viewer->getConfigVariable('baseUrl'),
			$this->viewer->getConfigVariable('appKey')
		);
		$this->callAction($requestMethod, $requestUri);
	}

	public function getIndex(){
		header("location: http://$_SERVER[HTTP_HOST]/".$this->viewer->getConfigVariable('prefix').'/installed');
	}

	public function getInstalled(){
		$packagesData = $this->packageProcessor->getInstalled();
		$this->view('installed.tpl', $packagesData);
	}

	public function postAjaxInstalled($offset, $length){
		$this->jsonRespond($this->packageProcessor->postAjaxInstalled($offset, $length));
	}

	public function getAll(){
		$this->view('all.tpl', $this->packageProcessor->getAll());
	}

	public function postAjaxAll($offset, $length){
		$this->jsonRespond($this->packageProcessor->postAjaxAll($offset, $length));
	}

	public function postAjaxSearch($cache, $offset, $length){
		$this->jsonRespond($this->packageProcessor->postAjaxSearch($cache, $offset, $length, $_REQUEST['query']));
	}

	public function getCacheAllPackages(){
		$this->packageProcessor->taskCacheAllPackages();
	}

	public function postRefreshPackage(){
		$this->jsonRespond($this->packageProcessor->refreshPackage($_REQUEST['name']));
	}

	public function postUpdatePackage(){
		$this->packageProcessor->taskRefreshPackage($_REQUEST['package'], $_REQUEST['file']);
		$this->jsonRespond(['response' => 0]);
	}

	public function postUpgradePacakge(){
		$this->packageProcessor->upgradePackage($_REQUEST['package']);
		$this->jsonRespond(['response' => 0]);
	}

	public function postTaskUpgradePackage(){
		$this->packageProcessor->consoleLog = fopen("{$this->cacheDir}/console.log", 'a');
		$this->packageProcessor->taskUpgradePackage($_REQUEST['package'], $_REQUEST['version']);
		fclose($this->packageProcessor->consoleLog);
	}

	public function postRemovePackage(){
		$this->packageProcessor->removePackage($_REQUEST['package']);
	}

	public function postTaskRemovePackage(){
		$this->packageProcessor->consoleLog = fopen("{$this->cacheDir}/console.log", 'a');
		$this->packageProcessor->taskRemovePackage($_REQUEST['package']);
		fclose($this->packageProcessor->consoleLog);
	}

	public function getConsole(){
		$this->jsonRespond($this->packageProcessor->fetchConsoleOutput());
	}

	public function getClearConsole(){
		$this->jsonRespond($this->packageProcessor->clearConsole());
	}

	private function view($template, $data = []){
		foreach($data as $name => &$value)
			$this->viewer->assign($name, $value);
		$this->viewer->display($template);
	}

	private function jsonRespond($responseData){
		header('Content-Type: application/json');
		echo json_encode($responseData, JSON_PRETTY_PRINT);
	}

	private function callAction($method, $uri){
		$prefix = $this->viewer->getConfigVariable('prefix');
		$uri = explode('/', trim($uri, '/'));
		if($uri[0] == $prefix){
			$method = strtolower($method);
			if(isset($uri[1])){
				$command = str_replace('-', ' ', $uri[1]);
				$command = str_replace(' ', '', ucwords($command));
				$action = "$method$command";
			} else
				$action = 'getIndex';
			$argsCount = count($uri) - 2;
			switch($argsCount){
				case 1: $this->$action($uri[2]); break;
				case 2: $this->$action($uri[2], $uri[3]); break;
				case 3: $this->$action($uri[2], $uri[3], $uri[4]); break;
				default: $this->$action();
			}
			exit();
		}
	}
}
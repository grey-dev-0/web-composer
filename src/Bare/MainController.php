<?php namespace GreyDev\WebComposer\Bare;

use GreyDev\WebComposer\PackageProcessor;
use Smarty;

class MainController{
	/**
	 * @var $viewer Smarty smarty templating engine
	 */
	private $viewer;
	/**
	 * @var $packageProcessor PackageProcessor The package reader/writer.
	 */
	private $packageProcessor;

	public function __construct($requestMethod, $requestUri){
		$this->viewer = new Smarty();
		$this->viewer->setDebugging(true);
		$this->viewer->setTemplateDir(__DIR__.'/../../views/smarty');
		$this->viewer->setCompileDir(__DIR__.'/../../views/smarty/compiled');
		$this->viewer->setConfigDir(__DIR__.'/../../views/smarty/config');
		$this->viewer->setCacheDir(__DIR__.'/../../views/smarty/cache');
		$this->packageProcessor = new PackageProcessor(__DIR__.'/../../../../../storage/composer');
		$this->getInstalled();
	}

	public function getInstalled(){
		$packagesData = $this->packageProcessor->getInstalled();
		$this->view('installed.tpl', $packagesData);
	}

	public function getAjaxInstalled($offset, $length){
		$packagesData = $this->packageProcessor->getAjaxInstalled($offset, $length);
		$this->jsonRespond($packagesData);
	}

	public function getAll(){
		$this->view('all.tpl');
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
}
<?php namespace GreyDev\WebComposer;

use Composer\Console\Application;
use GreyDev\WebComposer\Models\Package;
use GreyDev\WebComposer\Models\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PackageProcessor{
	/**
	 * @var $composer Application Composer application instance.
	 */
	private $composer;
	/**
	 * @var $consoleOutput BufferedOutput Console output.
	 */
	private $consoleOutput;
	/**
	 * @var $cacheDir string Directory where the packages cache files are stored.
	 */
	private $cacheDir;
	/**
	 * @var $baseUrl string Base URL of the application including the web-composer path.
	 */
	private $baseUrl;

	public function __construct($cacheDir, $baseUrl){
		$this->cacheDir = $cacheDir;
		$this->baseUrl = $baseUrl;
	}

	public function getInstalled(){
		if(!is_file("{$this->cacheDir}/installed.cache"))
			$packages = $this->cacheInstalledPackages();
		else
			$packages = unserialize(file_get_contents("{$this->cacheDir}/installed.cache"));
		$packagesCount = $packages->count();
		$packages = $packages->get(0, 10);
		return compact('packagesCount', 'packages');
	}

	public function getAjaxInstalled($offset, $length){
		$packages = unserialize(file_get_contents("{$this->cacheDir}/installed.cache"));
		$packagesCount = $packages->count();
		$packages = $packages->get($offset, $length);
		return compact('packagesCount', 'packages');
	}

	public function getAll(){
		if(is_file("{$this->cacheDir}/all.cache"))
			$packages = unserialize(file_get_contents("{$this->cacheDir}/all.cache"));
		else
			$this->cacheAllPackages();
		return (isset($packages))? ['packagesCount' => $packages->count(), 'packages' => $packages->get(0, 20)]
			: ['packagesCount' => 0, 'packages' => []];
	}

	private function cacheInstalledPackages(){
		$this->initComposer();
		$input = new ArrayInput(['command' => 'show']);
		$this->composer->run($input, $this->consoleOutput);
		$lines = explode("\n", $this->consoleOutput->fetch());
		$packages = new Collection();
		foreach($lines as &$line){
			if(empty($line))
				continue;
			$line = preg_split('/ +/', $line);
			$packages->add(new Package($line[0], $line[1], implode(' ', array_values(array_except($line, [0, 1])))));
		}
		if(!is_dir('storage/composer'))
			mkdir('storage/composer');
		$packages->cache('storage/composer/installed.cache');
		return $packages;
	}

	public function taskCacheAllPackages(){
		set_time_limit(0);
		ignore_user_abort(true);
		$this->initComposer();
		$input = new ArrayInput(['command' => 'show', '-a' => true]);
		$this->composer->run($input, $this->consoleOutput);
		$lines = explode("\n", $this->consoleOutput->fetch());
		$packages = new Collection();
		if(!is_dir('storage/composer'))
			mkdir('storage/composer');
		$packages->cache('storage/composer/all.cache');
		foreach($lines as &$line){
			if(empty($line))
				continue;
			$line = preg_split('/ +/', $line);
			$packages->add(new Package($line[0], null, null));
		}
		$packages->cache('storage/composer/all.cache');
	}

	private function cacheAllPackages(){
		$request = curl_init("{$this->baseUrl}/cache-all-packages");
		curl_setopt_array($request, [
			CURLOPT_TIMEOUT_MS => 1002,
			CURLOPT_CONNECTTIMEOUT_MS => 1001,
			CURLOPT_RETURNTRANSFER => true
		]);
		curl_exec($request);
	}

	private function initComposer(){
		$this->composer = new Application();
		$this->composer->setAutoExit(false);
		$this->consoleOutput = new BufferedOutput();
		putenv('COMPOSER_HOME='.storage_path());
	}
}
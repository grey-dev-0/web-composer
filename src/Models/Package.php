<?php namespace GreyDev\WebComposer\Models;

class Package{
	public $name, $version, $description, $dependencies, $available_versions = [], $installed;

	public function __construct($name, $version, $description, $installed = false, $dependencies = null){
		$this->name = $name;
		$this->version = $version;
		$this->description = $description;
		$this->installed = $installed;
		$this->dependencies = $dependencies;
	}
}
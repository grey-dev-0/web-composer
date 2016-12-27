<?php namespace GreyDev\WebComposer\Models;

class Package{
	public $name, $version, $description, $dependencies;

	public function __construct($name, $version, $description, $dependencies = null){
		$this->name = $name;
		$this->version = $version;
		$this->description = $description;
		$this->dependencies = $dependencies;
	}
}
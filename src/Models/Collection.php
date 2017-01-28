<?php namespace GreyDev\WebComposer\Models;

use Iterator;

class Collection implements Iterator{
	/**
	 * @var Package[] Array of stored packages in the collection
	 */
	private $packages = [];
	/**
	 * @var int Current package reference in the collection
	 */
	private $index = 0;

	/**
	 * Collection constructor.
	 *
	 * @param Package[]|null $packages Array of packages to initialize the collection with (optional)
	 */
	public function __construct($packages = null){
		if(!is_null($packages))
			$this->packages = $packages;
	}

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current(){
		return $this->packages[$this->index];
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next(){
		++$this->index;
	}

	/**
	 * Move backward to next element
	 * @return void Any returned value is ignored.
	 */
	public function previous(){
		--$this->index;
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key(){
		return $this->index;
	}

	/**
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid(){
		return isset($this->packages[$this->index]);
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind(){
		$this->index = 0;
	}

	/**
	 * Add a package to the collection.
	 *
	 * @param $package Package package data object.
	 */
	public function add(Package $package){
		$this->packages[] = $package;
	}

	/**
	 * Remove a package from the collection.
	 *
	 * @param $package string Name of the package to be removed from the collection.
	 */
	public function remove($package){
		$package = trim($package);
		foreach($this->packages as $i => &$cPackage)
			if($package == $cPackage->name)
				unset($this->packages[$i]);
		$this->packages = array_values($this->packages);
	}

	/**
	 * Updating a package information.
	 *
	 * @param $packageData array Package new data provided for the update.
	 * @param $cacheFile string Cache filename where the packages are stored.
	 */
	public function update($packageData, $cacheFile){
		$index = array_search($packageData['name'], $this->getNames());
		$this->packages[$index]->name = $packageData['name'];
		$this->packages[$index]->description = $packageData['description'];
		$this->packages[$index]->available_versions = $packageData['available_versions'];
		$this->packages[$index]->dependencies = $packageData['dependencies'];
		$this->cache($cacheFile);
	}

	/**
	 * Save cache file of Composer packages data.
	 *
	 * @param $filename string Filename and location of the cache file to be saved.
	 */
	public function cache($filename){
		$cache = fopen($filename, 'w');
		fwrite($cache, serialize($this));
		fclose($cache);
	}

	/**
	 * Getting one, subset or, all of the packages in the collection.
	 *
	 * @param int $offset Index of the first element in the required collection OR a package index.
	 * @param int $length Number of packages required in the collection OR null OR 1 for a single package result.
	 * @return Package[]|Package Array including the requested packages OR the requested package by index.
	 */
	public function get($offset = 0, $length = null){
		return (is_null($length) && $offset == 0)? $this->packages :
			((is_null($length) || $length == 1)? $this->packages[$offset] : array_slice($this->packages, $offset, $length));
	}

	/**
	 * Getting count of all stored packages in the collection
	 *
	 * @return int Count of all packages in the collection
	 */
	public function count(){
		return count($this->packages);
	}

	/**
	 * Getting names of the packages stored in the collection.
	 *
	 * @return string[] Array of package names as in the collection.
	 */
	public function getNames(){
		$names = [];
		foreach($this->packages as &$package)
			$names[] = $package->name;
		return $names;
	}

	/**
	 * Filtering packages collection to include only the packages that matches the query provided.
	 *
	 * @param $query string Search query to be applied on the packages collection.
	 */
	public function search($query){
		$query = str_replace('/', '\/', $query);
		foreach($this->packages as $i => &$package)
			if(!preg_match("/$query/", $package->name))
				unset($this->packages[$i]);
		$this->packages = array_values($this->packages);
	}
}
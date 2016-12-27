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
	 * Getting all or subset of the packages in the collection.
	 *
	 * @param int $offset Index of the first element in the required collection
	 * @param int $length Number of packages required in the collection
	 * @return Package[] Array including the requested packages
	 */
	public function get($offset = 0, $length = null){
		return (is_null($length) && $offset == 0)? $this->packages : array_slice($this->packages, $offset, $length);
	}

	/**
	 * Getting count of all stored packages in the collection
	 *
	 * @return int Count of all packages in the collection
	 */
	public function count(){
		return count($this->packages);
	}
}
<?php namespace GreyDev\WebComposer;

use GreyDev\WebComposer\Bare\MainController;

class WebComposer{
	public static function init(){
		new MainController($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
	}
}
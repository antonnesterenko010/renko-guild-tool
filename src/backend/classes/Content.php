<?php

declare(strict_types=1);

namespace App\Classes;

class Content{

    private static $container = array();

    public static function push($key, $val)
	{
		self::$container[$key] = $val;
	}
	
	public static function get(): array
    {
        return self::$container;
    }

}
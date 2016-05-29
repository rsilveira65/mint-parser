<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */
namespace src\core\Helpers;

class RandomJokes
{
    protected static $url = 'http://tambal.azurewebsites.net/joke/random';

    /**
     * Method do get a random joke from api.
     * @return mixed
     */
    public static function getJoke()
    {
        return json_decode(file_get_contents(self::$url))->joke;
    }
}
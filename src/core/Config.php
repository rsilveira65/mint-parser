<?php
namespace src\core;

use Symfony\Component\Yaml\Yaml;

class Config
{
    protected $parameters;

    /**
     * Config constructor.
     */
    public function __construct(){
        $this->parameters = Yaml::parse(file_get_contents('config/parameters.yml'));
    }

    /**
     * @param $index
     * @return bool
     */
    public function get($index){
        $indexes = explode('.', $index);

        return $this->recursive($indexes, $this->parameters);
    }

    /**
     * @param $indexes
     * @param $parameters
     * @return bool
     */
    protected function recursive($indexes, $parameters){
        $index = array_shift($indexes);

        if (!isset($parameters[$index])) {
            return false;
        }

        if (!count($indexes)) {
            return $parameters[$index];
        }

        return $this->recursive($indexes, $parameters[$index]);
    }

}

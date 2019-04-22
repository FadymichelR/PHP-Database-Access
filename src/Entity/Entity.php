<?php


namespace Fad\Entity;

/**
 * Class Entity
 * @package Webby\Entity
 */
abstract class Entity implements EntityInterface
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @param array|null $data
     */
    public function hydrate(array $data = null): void
    {

        if ($data !== null) {
            foreach ($data as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
            }
        }

    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } elseif (property_exists($this, ($name = str_replace('_', '', lcfirst(ucwords($name, '_')))))) {
            $this->$name = $value;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {

        $vars = get_object_vars($this);
        $array = [];
        foreach ($vars as $key => $value) {

            $array [ltrim($key, '_')] = $value;
        }
        return $array;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

}
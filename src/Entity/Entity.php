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
     * @throws \ReflectionException
     */
    public function toArray(): array
    {
        $array = [];
        (new \ReflectionClass($this))->getProperties();
        foreach ((new \ReflectionClass($this))->getProperties() as $property) {
            $property->setAccessible(true);
            $array [$property->getName()] = $property->getValue($this);
        }

        return $array;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

}
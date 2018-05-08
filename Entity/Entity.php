<?php


namespace Fady\Entity;


abstract class Entity implements EntityInterface
{

    /**
     * @var int
     */
    protected $id;


    public function hydrate(array $data = null)
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

    public function toArray($without = [])
    {

        $vars = get_object_vars($this);
        $array = array();
        foreach ($vars as $key => $value) {
            if (!in_array($key,$without)) {

                $array [ltrim($key, '_')] = $value;
            }
        }
        return $array;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
<?php
/**
 * Created by Fadymichel.
 * git: https://github.com/FadymichelR
 * 2018
 */


namespace Fady\Entity;


class Annotation
{

    

    public function isMapped($className) {

        $rc = new \ReflectionClass($className);

        $properties = [];
        foreach ($rc->getProperties() as $property) {
            $mapped = new \ReflectionProperty($property->class, $property->name);

            preg_match_all('#@(.*?)\n#s', $mapped->getDocComment(), $annotations);

            $annotations[0] = array_map('trim', $annotations[0]);

            if (in_array('@Mapped', $annotations[0])) {
                $properties[] = $property->name;
            }

        }

        return $properties;

    }

}
<?php
namespace WhiteBox\Helpers;

class TraitChecker{
    //Huge props to : ulf @ http://php.net/manual/en/function.class-uses.php

    public static function getTraitsFrom(string $class, bool $autoload=true){
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_values(array_unique($traits)); //Modified here, cut the non-sense
    }

    public static function classHasTrait(string $class, string $trait){
        $traits = self::getTraitsFrom($class);
        return in_array($trait, $traits, true);
    }

    protected $classID;

    public function __construct(string $class){
        $this->classID = $class;
    }

    public function getTraits(){
        return self::getTraitsFrom($this->classID);
    }

    public function hasTrait(string $trait){
        return self::classHasTrait($this->classID, $trait);
    }
}
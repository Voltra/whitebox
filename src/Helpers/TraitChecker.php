<?php
namespace WhiteBox\Helpers;

/**A class wrapper used to check whether or not a class uses a certain trait
 * Class TraitChecker
 * @package WhiteBox\Helpers
 */
class TraitChecker{
    //Huge props to : ulf @ http://php.net/manual/en/function.class-uses.php

    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The id of the class to test on (use Class::class)
     * @var string
     */
    protected $classID;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Construct a trait checker from a class id (Class::class)
     * TraitChecker constructor.
     * @param string $class
     */
    public function __construct(string $class){
        $this->classID = $class;
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Retrieves an array constituted of the traits that this class uses
     * @return array
     */
    public function getTraits(): array{
        return self::getTraitsFrom($this->classID);
    }

    /**Determines whether or not this class uses the given trait
     * @param string $trait being the trait id to lookup for (use Trait::class)
     * @return bool
     */
    public function hasTrait(string $trait): bool{
        return self::classHasTrait($this->classID, $trait);
    }



    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**A static version of TraitChecker.getTraits()
     * @param string $class being the class id of the class to test upon (use Class::class)
     * @param bool $autoload being a flag used to use the autoload or not (if not loaded)
     * @return array
     */
    public static function getTraitsFrom(string $class, bool $autoload=true): array{
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

    /**A static version of TraitChecker.hasTrait()
     * @param string $class being the class id of the class to test upon (use Class::class)
     * @param string $trait being the trait id of the desired trait (use Trait::class)
     * @return bool
     */
    public static function classHasTrait(string $class, string $trait): bool{
        $traits = self::getTraitsFrom($class);
        return in_array($trait, $traits, true);
    }
}
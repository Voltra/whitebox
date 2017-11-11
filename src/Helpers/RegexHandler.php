<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Helpers;



/**A class wrapper around the regex functions of PHP, only keeps the interesting functionalities
 * Class RegexHandler
 * @package WhiteBox\Helpers
 */
class RegexHandler{
    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The regular expression of this RegexHandler
     * @var string
     */
    protected $re_str;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Create a RegexHandler from a regular expression string
     * RegexHandler constructor.
     * @param string $re_str being the regular expression (PHP's syntax) of this RegexHandler
     */
    public function __construct(string $re_str){
        $this->re_str = $re_str;
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Determines whether or not the string follows the pattern enunciated by the regular expression of this RegexHandler
     * @param string $str being the string to apply the regular expression on
     * @return bool
     */
    public function appliesTo(string $str): bool{
        return (bool)preg_match($this->re_str, $str);
    }

    /**Retrieve the capturing groups from the application of the regular expression of this RegexHandler on the given string
     * @param string $str being the string to apply the regular expression on
     * @return array
     */
    public function getGroups(string $str): array{
        $groups = [null];
        preg_match($this->re_str, $str, $groups);
        array_shift($groups);
        return $groups;
    }
}
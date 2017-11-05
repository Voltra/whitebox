<?php
namespace WhiteBox\Helpers;

class RegexHandler{
    protected $re_str;

    public function __construct(string $re_str){
        $this->re_str = $re_str;
    }

    public function appliesTo(string $str){
        $str = "{$str}";

        return preg_match($this->re_str, $str);
    }

    public function getGroups(string $str){
        $str = "{$str}";
        $groups = [null];
        preg_match($this->re_str, $str, $groups);
        array_shift($groups);
        return $groups;
    }
}
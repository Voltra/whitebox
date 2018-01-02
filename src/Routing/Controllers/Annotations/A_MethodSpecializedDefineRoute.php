<?php
namespace WhiteBox\Routing\Controllers\Annotations;


abstract class A_MethodSpecializedDefineRoute extends DefineRoute {
    public function __construct(array $values){
        $definedValues = array_merge($values, [
            "method" => $this->getMethod()
        ]);
        parent::__construct($definedValues);
    }

    protected abstract function getMethod(): string;
}
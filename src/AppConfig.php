<?php
namespace WhiteBox;


use Noodlehaus\AbstractConfig;

class AppConfig extends AbstractConfig {
    protected function getDefaults(): array {
        return [
            "development" => true,
            "db" => [
                "host" => "localhost",
                "port" => "3306",
                "username" => "root",
                "password" => ""
            ]
        ];
    }
}
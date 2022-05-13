<?php

namespace MyLoan\HomeCredit\OrderStates;

class CancelledState extends AbstractState
{
    const ID = "HC_CANCELLED";

    public function __construct()
    {
        parent::__construct(
            self::ID,
            [
                "en" => "HC - Order cancelled",
                "cs" => "HC - Objednávka stornována",
                "sk" => "HC - Objednávka stornována",
            ],
            "#FF0000",
            false,
            false,
            false,
            false,
            false,
            [
                "en" => "hc_cancelled",
                "cs" => "hc_cancelled",
                "sk" => "hc_cancelled",
            ]
        );
    }
}
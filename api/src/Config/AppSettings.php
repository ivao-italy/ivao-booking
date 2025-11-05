<?php
namespace App\Config;

class AppSettings implements IAppSettings
{
    private const API_KEY = "2024IoSonoCarmineAirSiciliaColBidplano25yearsOfIvaoIt";

    public function GetApiKey(): string
    {
        return self::API_KEY;
    }

    public static function getInstance(): IAppSettings
    {
        return new AppSettings();
    }
}

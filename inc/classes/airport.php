<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airport which can be origin, destination or part of the event
 */
class Airport
{
	/**
	 * Returns an airport found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return Airport
	 */
	public static function Find($icao)
	{
		global $dbNav;
		if ($query = $dbNav->Query("SELECT * FROM nav_airports WHERE icao = §", $icao))
		{
			if ($row = $query->fetch_assoc())
				return new Airport($row);
		}
		return null;
	}
	
	public $icao, $iata, $country, $latitude, $longitude, $name, $elevation, $type;
	public function __construct($row)
	{
		$this->icao = $row["icao"];
		$this->iata = $row["iata"];
		$this->country = $row["country"];
		$this->latitude = (float)$row["latitude"];
		$this->longitude = (float)$row["longitude"];
		$this->name = $row["name"];
		$this->elevation = (int)$row["elevation"];
		$this->type = $row["type"];
		
		$this->name = str_replace("International Airport", "", $this->name);
		$this->name = str_replace("Airport", "", $this->name);
		$this->name = str_replace("Airfield", "", $this->name);
		$this->name = str_replace("Air Base", "", $this->name);
		$this->name = trim($this->name);
	}
	
	/**
	 * Returns the country flag PNG if exists.
	 * @param int $size = 32
	 * @return string HTML
	 */
	public function getCountryFlag($size = 32)
	{
		return self::GetFlag($this->country, $size);
		//return sprintf('<img src="https://flagsapi.com/%s/shiny/%s.png" alt="%s" data-toggle="tooltip" title="Country: %s" class="img-fluid"> ', $this->country, $size, $this->country, $this->country);
	}

	/**
	 * Returns the country flag PNG if exists.
	 * @param int $size = 32
	 * @return string HTML
	 */
	public static function GetFlag($country, $size = 32)
	{
		return sprintf('<img src="https://flagsapi.com/%s/shiny/%s.png" alt="%s" data-toggle="tooltip" title="Country: %s" class="img-fluid"> ', $country, $size, $country, $country);
	}


	/**
	 * Returns the METAR of the airport.
	 * Not used
	 * @return string METAR
	 */
	public function getMetar()
	{
		global $config;
		return file_get_contents(sprintf('%s?type=metar&icao=%s', $config["wx_url"], $this->icao));
	}

	/**
	 * Returns the TAF of the airport.
	 * Not used
	 * @return string TAF
	 */
	public function getTaf()
	{
		global $config;
		return file_get_contents(sprintf('%s?type=taf&icao=%s', $config["wx_url"], $this->icao));
	}
	
	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * METAR and TAF fields are not used because of the high performance load!
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"countryFlag24" => $this->getCountryFlag(24),
			"countryFlag32" => $this->getCountryFlag(32),
			"countryFlag48" => $this->getCountryFlag(48),
			/*"metar" => $this->getMetar(),
			"taf" => $this->getTaf(),*/
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
 
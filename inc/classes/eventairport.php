<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airport which participates in the event
 */
class EventAirport
{
	/**
	 * Returns an airport found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return EventAirport
	 */
	public static function Find($icao)
	{
		global $db;
		if ($query = $db->Query("SELECT * FROM airports WHERE icao = §", $icao))
		{
			if ($row = $query->fetch_assoc())
				return new EventAirport($row);
		}
		return null;
	}

	/**
	 * Returns an airport found in the database based on its id, otherwise returns null
	 * @param int $id
	 * @return EventAirport
	 */
	public static function FindId($id)
	{
		global $db;
		if ($query = $db->Query("SELECT * FROM airports WHERE id = §", $id))
		{
			if ($row = $query->fetch_assoc())
				return new EventAirport($row);
		}
		return null;
	}
	
	/**
	 * Gets all EventAirports from the database
	 * @param bool $disabledsToo if false, returns only the enabled airports, otherwise all of them
	 * @return EventAirport[] 
	 */
	public static function GetAll($disabledsToo = false)
	{
		global $db;
		$apts = [];

		if ($disabledsToo)
			$sql = "SELECT * FROM airports ORDER BY `order`";
		else
			$sql = "SELECT * FROM airports WHERE enabled = true ORDER BY `order`";

		if ($query = $db->Query($sql))
		{
			while ($row = $query->fetch_assoc())
				$apts[] = new EventAirport($row);
		}
		return $apts;
	}

	/**
	 * Converts all EventAirports to JSON format
	 * Used by the admin area through AJAX
	 * @param bool $disabledsToo if false, returns only the enabled airports, otherwise all of them
	 * @return string JSON
	 */
	public static function ToJsonAll($disabledsToo = false)
	{
		$apts = [];
		foreach (EventAirport::GetAll($disabledsToo) as $apt)
			$apts[] = json_decode($apt->ToJson(), true);
		return json_encode($apts);
	}

	/**
	 * Returns the statistic numbers in an associative array about the booked/free flights
	 * @return array
	 */
	public static function getStatisticsAll()
	{ 
		global $db;
		$stat = [
			"free" => 0,
			"prebooked" => 0,
			"booked" => 0
		];

		if ($query = $db->Query("SELECT booked, COUNT(*) AS num FROM flights GROUP BY booked"))
		{
			while ($row = $query->fetch_assoc())
			{
				switch ($row["booked"])
				{
					case 0:
						$stat["free"] = $row["num"];
						break;
					case 1:
						$stat["prebooked"] = $row["num"];
						break;
					case 2:
						$stat["booked"] = $row["num"];
						break;
				}
			}
		}
		return $stat;
	}
	
	public $id, $icao, $name, $order, $enabled;
	/**
	 * @param array $row - associative array from fetch_assoc()
	 */
	public function __construct($row)
	{
		$this->id = (int)$row["id"];
		$this->icao = $row["icao"];
		$this->name = $row["name"];
		$this->order = (int)$row["order"];
		$this->enabled = $row["enabled"] == 1;
	}
	
	/**
	 * Returns the respective Airport object based on its ICAO code, otherwise returns null because of the nature of the Airport::Find() function
	 * @return Airport
	 */
	public function getAirport()
	{
		return Airport::Find($this->icao);
	}

	/**
	 * Returns departure flights from the airport
	 * @param Flight[] $allflights
	 * @return Flight[]
	 */
	public function getDepartures($allflights)
	{
		$flights = [];
		foreach($allflights as $flt)
		{
			if($flt->originIcao == $this->icao)
			{
				$flights[] = $flt;
			}	
		}
		// if ($query = $db->Query("SELECT * FROM flights WHERE origin_icao = § ORDER BY departure_time, flight_number", $this->icao))
		// {
		// 	while ($row = $query->fetch_assoc())
		// 		$flights[] = new Flight($row);
		// }
		return $flights;
	}
	
	/**
	 * Returns arrival flights from the airport
	 * @return Flight[]
	 */
	public function getArrivals($allflights)
	{
		//global $db;
		$flights = [];
		foreach($allflights as $flt)
		{
			if($flt->destinationIcao == $this->icao)
			{
				$flights[] =  $flt;
			}	
		}
		// if ($query = $db->Query("SELECT * FROM flights WHERE destination_icao = § ORDER BY arrival_time, flight_number", $this->icao))
		// {
		// 	while ($row = $query->fetch_assoc())
		// 	{
		// 		$flights[] = new Flight($row);
		// 	}
		// }
		return $flights;
	}

	/**
	 * Returns the statistic numbers in an associative array about the booked/free flights at the airport
	 * @return array
	 */
	public function getStatistics()
	{ 
		global $db;
		$stat = [
			"free" => 0,
			"prebooked" => 0,
			"booked" => 0
		];

		if ($query = $db->Query("SELECT booked, COUNT(*) AS num FROM flights WHERE origin_icao = § OR destination_icao = § GROUP BY booked", $this->icao, $this->icao))
		{ 
			while ($row = $query->fetch_assoc())
			{
				switch ($row["booked"])
				{
					case 0:
						$stat["free"] = $row["num"];
						break;
					case 1:
						$stat["prebooked"] = $row["num"];
						break;
					case 2:
						$stat["booked"] = $row["num"];
						break;
				}
			}
		}
		return $stat;
	}

	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"airport" => $this->getAirport() ? json_decode($this->getAirport()->ToJson(), true) : null,
		];
		
		return json_encode(array_merge($apt, $data));
	}

	/**
	 * Saves data about the event airport.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Update($array)
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if ($db->Query("UPDATE airports SET icao = §, name = §, `order` = §, enabled = § WHERE id = §", $array["icao"], $array["name"], $array["order"], $array["enabled"] == "true", $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Creates a new event airport.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public static function Create($array)
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if ($db->Query("INSERT INTO airports (icao, name, `order`, enabled) VALUES (§, §, §, §)", $array["icao"], $array["name"], $array["order"], $array["enabled"] == "true"))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Deletes the event airport.
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Delete()
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if ($db->Query("DELETE FROM airports WHERE id = §", $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Gets the assigned private slot timeframes for this airport
	 * @return Timeframe[]
	 */
	public function getTimeframes()
	{
		global $db;
		$tfs = [];
		if ($query = $db->Query("SELECT * FROM timeframes WHERE airport_icao = § ORDER BY time", $this->icao))
		{
			while ($row = $query->fetch_assoc())
				$tfs[] = new Timeframe($row);
		}
		return $tfs;
	}
}
  
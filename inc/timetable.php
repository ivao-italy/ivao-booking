<div class="container-fluid px-xl-5">
    <h3>The booking is now closed. The event is ongoing!<br>
    </h3>

    <div class="alert alert-success" role="alert">
		<p>
            If you need to take a look of what is scheduled today, you can check the timetable below.<br>
            Here are listed booked flights and gates to connect on the correct spot, which is a <strong>mandatory</strong> requirement for the event.
        </p>
	</div>

    <h1>Timetable</h1>

    <ul class="nav nav-tabs" id="arrDepTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a  class="nav-link active" id="sched-tab" data-toggle="tab" data-target="#sched" type="button" role="tab" aria-controls="sched" aria-selected="true">Scheduled Flights</a>
        </li>
        <li class="nav-item" role="presentation">
            <a  class="nav-link" id="slots-tab" data-toggle="tab" data-target="#slots" type="button" role="tab" aria-controls="slots" aria-selected="false">Private Slots</a>
        </li>
    </ul>
    <div class="tab-content mt-4" id="myTabContent">
        <div class="tab-pane fade show active" id="sched" role="tabpanel" aria-labelledby="sched-tab">
<?php
    $flights = Flight::GetAllBooked();

    if(count($flights) == 0)
    {
?>
    <p class="alert alert-info">
        No flights booked for the event.
    </p>
<?php
    }
    else
    {
?>
	<table class="table table-hover table-striped tblFlights" id="tblFlight">
		<thead>
            <tr>
                <th></th>
                <th>Flight</th>
                <th>Aircraft</th>
                <th>Departure</th>
                <th>EOBT</th>
                <th>Arrival</th>
                <th>EAT</th>
                <th>Stand</th>
                <th>Status</th>
            </tr>
        </thead>
		<tbody>
            
<?php
    }
    foreach ($flights as $f)
    {
?>
        <tr>
<?php
        switch($f->bookedRaw)
        {
            case 1:
                $booking_color = "warning";
                $booking_text= "Prebooked by";
                $icon = 'fas fa-lock';
                break;
            case 2: 
                $booking_color = "danger text-white";
                $booking_text= "Booked by";
                $icon = 'fas fa-lock';
                break;
            default:
                $booking_color = "success text-white";
                $booking_text= "Free";
                $icon = "fas fa-thumbs-up";
                break;
        }

        if ($airline = $f->getAirline())        
			//$result .= '<td data-toggle="tooltip" title="' . $airline->name . '" data-search="' . $airline->name . '" data-order="' . $airline->name . '">' . $airline->getLogo() . "</td>";		
			$airline_td = '<td data-toggle="tooltip" title="' . $airline->name . '" data-search="' . $airline->name . '" data-order="' . $airline->name . '"><img src="https://cdn.it.ivao.aero/airlines/'.$airline->icao.'.png" style="max-width:200px" alt="Logo"></td>';
		else
            $airline_td = '<td data-search="' . $f->flightNumber . '" data-order="' . $f->flightNumber . '"></td>';
        echo $airline_td;
?>
            <td><?=empty($f->callsign) ? $f->flightNumber : $f->callsign ?></td>
            <td><span data-toggle="tooltip" title="<?=$f->aircraftName?>"><?= $f->aircraftIcao?></span></td>
            <td><?=Airport::GetFlag($f->originCountry, 24)?> <?=$f->originIcao?> <small class="text-muted"><?=$f->originName?></small></td>
            <td data-order="<?=$f->departureTime?>"><?=getHumanDateTime($f->departureTime)?></td>
            <td><?=Airport::GetFlag($f->destinationCountry, 24)?> <?=$f->destinationIcao?> <small class="text-muted"><?=$f->destinationName?></small></td>
            <td data-order="<?=$f->arrivalTime?>"><?=getHumanDateTime($f->arrivalTime)?></td>
            <td><?=$f->getPosition()?></td>
            <td data-search="<?=$f->booked?>">
                <span class="d-block text-center alert bg-<?=$booking_color?>"><i class="<?=$icon?>"></i> <?=$booking_text?> <?=$f->bookedBy ?></span>
            </td>
        </tr>
<?php
    }
?>
    </tbody>
    </table>
    </div>

    <div class="tab-pane fade" id="slots" role="tabpanel" aria-labelledby="slots-tab">
<?php
    $slots = Slot::GetAll();

    if(count($slots) == 0)
    {
?>
    <p class="alert alert-info">
        No private slots booked for the event.
    </p>
<?php
    }
    else
    {
?>
	<table class="table table-hover table-striped tblFlights" id="tblFlight">
		<thead>
            <tr>
                <th>Flight</th>
                <th>Aircraft</th>
                <th>Departure</th>
                <th>EOBT</th>
                <th>Arrival</th>
                <th>EAT</th>
                <th>Stand</th>
                <th>Status</th>
            </tr>
        </thead>
		<tbody>
            
<?php
    }
    foreach ($slots as $f)
    {
?>
        <tr>
<?php
        switch($f->bookedRaw)
        {
            case 1:
                $booking_color = "warning";
                $booking_text= "Requested by";
                $icon = 'fas fa-lock';
                break;
            case 2: 
                $booking_color = "danger text-white";
                $booking_text= "Granted to";
                $icon = 'fas fa-lock';
                break;
            default:
                $booking_color = "success text-white";
                $booking_text= "--";
                $icon = "fas fa-thumbs-up";
                break;
        }
    
?>
            <td><?=empty($f->callsign) ? $f->flightNumber : $f->callsign ?></td>
            <td><span data-toggle="tooltip" title="<?=$f->aircraftName?>"><?= $f->aircraftIcao?></span></td>
            <td><?=Airport::GetFlag($f->originCountry, 24)?> <?=$f->originIcao?> <small class="text-muted"><?=$f->originName?></small></td>
            <td data-order="<?=$f->departureTime?>"><?=getHumanDateTime($f->departureTime)?></td>
            <td><?=Airport::GetFlag($f->destinationCountry, 24)?> <?=$f->destinationIcao?> <small class="text-muted"><?=$f->destinationName?></small></td>
            <td data-order="<?=$f->arrivalTime?>"><?=getHumanDateTime($f->arrivalTime)?></td>
            <td><?=$f->getPosition()?></td>
            <td data-search="<?=$f->booked?>">
                <span class="d-block text-center alert bg-<?=$booking_color?>"><i class="<?=$icon?>"></i> <?=$booking_text?> <?=$f->bookedBy ?></span>
            </td>
        </tr>
<?php
    }
?>
    </tbody>
    </table>
    

        </div>
    </div>
</div>
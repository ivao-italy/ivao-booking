<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */
?>

<main class="container" role="main">
<?php 
global $config;

if($config['mode'] == 0)
{
?>
	<div class="alert alert-warning" role="alert">
		<h4 class="alert-heading">Maintenance in progress</h4>
		<hr />
		<p>The system is under maintenance, therefore it can&#39;t be used for flight booking purposes currently. Please check back regularly!</p>
		<p>If you think this message has been appeared by mistake, please contact the division staff!</p>
	</div>
<?php
} else if($config['mode'] == 2)
{
?>
	<div class="alert alert-success" role="alert">
		<h4 class="alert-heading">The event is ongoing! You cannot book flights anymore.</h4>
		<hr />
		<p>If you need to take a look of what is scheduled today, you can login to get access to the full time table.</p>
	</div>
<?php
}
?> 
</main>

<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

// only for debug purposes
ini_set("display_errors", "on");
error_reporting(E_ALL);

$start_time = microtime(true);

date_default_timezone_set('Etc/UTC');
require 'config-inc.php';
require 'inc/functions.php';
require 'inc/classes/db.php';
require 'inc/classes/email.php';
require 'inc/classes/user.php';
require 'inc/classes/session.php';
require 'inc/classes/config.php';
require 'inc/classes/pages_menu.php';
require 'inc/classes/flight.php';
require 'inc/classes/airport.php';
require 'inc/classes/airline.php';
require 'inc/classes/eventairport.php';
require 'inc/classes/slot.php';
require 'inc/classes/timeframe.php';
require 'vendor/autoload.php';
session_start();

$page = $_GET["f"] ?? "";
$db = new DB(SQL_SERVER, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE);
$dbNav = new DB(SQL_SERVER, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE_NAV);

$config = Config::Get();
Session::CheckAccess();
Session::RequestProcessing();
Flight::TokenProcessing();

// adding items to the main menu
Menu::addItems([
	[
		"text" => "Briefing",
		"href" => "briefing"
	],
	[
		"text" => '<i class="fas fa-plane"></i> Flight booking',
		"href" => "flights"
	],
	[
		"text" => "Private slots",
		"href" => "slots",
		"condition" => count(Timeframe::GetAll()) > 0
	],
	[
		"text" => "Statistics",
		"href" => "statistics"
	],
	[
		"text" => '<i class="fas fa-user-ninja"></i> Admin area',
		"href" => "admin",
		"permission" => 2,
	],
	[
		"text" => "Login",
		"href" => "login",
		"loggedIn" => false
	],
]);

?>

<!DOCTYPE HTML>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Donat Marko (IVAO VID 540147)">
	<meta name="xsrf-token" content="<?=$_SESSION["xsrfToken"]; ?>">
	<title><?=$config["event_name"]; ?> Booking System | <?=$config["division_name"]; ?></title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="node_modules/leaflet/dist/leaflet.css">
	<link rel="stylesheet" href="node_modules/tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="css/style.css">
	<script>
		const XSRF_TOKEN = "<?=$_SESSION["xsrfToken"]; ?>";
	</script>


 	<!--Icons-->
 	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="favicon/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="favicon/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="favicon/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="favicon/favicon-196x196.png" sizes="196x196" />
    <link rel="icon" type="image/png" href="favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="favicon/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="favicon/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="favicon/favicon-128.png" sizes="128x128" />
    <meta name="application-name" content="IVAO IT Booking System" />
    <meta name="msapplication-TileColor" content="#0D2C99" />
    <meta name="msapplication-TileImage" content="favicon/mstile-144x144.png" />
    <meta name="msapplication-square70x70logo" content="favicon/mstile-70x70.png" />
    <meta name="msapplication-square150x150logo" content="favicon/mstile-150x150.png" />
    <meta name="msapplication-wide310x150logo" content="favicon/mstile-310x150.png" />
    <meta name="msapplication-square310x310logo" content="favicon/mstile-310x310.png" />


	<!--open graph meta tags for social sites and search engines-->
    <meta property="og:locale" content="en-US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="IVAO Italy - Booking System" />
    <meta property="og:description" content="Booking system of IVAO Italy for the event: <?=$config["event_name"]; ?>" />
    <meta property="og:url" content="https://it.ivao.aero" />
    <meta property="og:site_name" content="IVAO Italy Booking System" />
    <meta property="og:image" content="favicon/favicon-196x196.png" />
    <meta property="og:image:secure_url" content="favicon/favicon-196x196.png" />
    <meta property="og:image:width" content="196" />
    <meta property="og:image:height" content="196" />

    <meta name="theme-color" content="#0D2C99">
</head>

<body>

<?php	
switch ($page)
{
	case "login":
		Session::IVAOLogin();		
		break;
	case "logout":
		Session::IVAOLogout();
		break;
	default:
		Pages::Add($page);
		if (Session::LoggedIn() && empty(Session::User()->email) && empty($page))
			Pages::Add("modal_email");
		break;
}

echo Menu::Get();
Pages::AddJS("main");

echo Pages::Get();	
?>
	
<footer class="footer">		
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4">
				<p>&copy; <?=date("Y"); ?> <a href="<?=$config["division_web"]; ?>" target="_blank"><?=$config["division_name"]; ?></a></p>
				<p><i class="far fa-envelope-open"></i> <a href="https://it.ivao.aero/about/contacts">Contact us!</a></p>
			</div>
			<div class="col-md-4 text-md-center">
				<?php if (!empty($config["division_facebook"])): ?>
					<p><i class="fab fa-facebook-f"></i> <a href="<?=$config["division_facebook"]; ?>" target="_blank">Find us on Facebook</a></p>
				<?php endif; ?>
				<?php if (!empty($config["division_instagram"])): ?>
					<p><i class="fab fa-instagram"></i> <a href="<?=$config["division_instagram"]; ?>" target="_blank">Find us on Instagram</a></p>
				<?php endif; ?>
				<?php if (!empty($config["division_discord"])): ?>
					<p><i class="fab fa-discord"></i> <a href="<?=$config["division_discord"]; ?>" target="_blank">Join us on Discord</a></p>
				<?php endif; ?>
			</div>
			<div class="col-md-4 text-md-right">
				<p>Developed by <a href="https://www.ivao.aero/Member.aspx?ID=540147" target="_blank">Donat Marko (540147)</a></p>
				<p>Customized with ❤️ for IVAO IT by <a href="https://www.ivao.aero/Member.aspx?ID=362802" target="_blank">Emiliano Innocenti (362802)</a></p>
				<p>Loaded in <?=round((microtime(true) - $start_time) * 1000, 2); ?> ms</p>
			</div>
		</div>
	</div>
</footer>

<div class="loader"></div>
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<script src="node_modules/moment/moment.js"></script>
<script src="node_modules/moment/locale/en-gb.js"></script>
<script src="node_modules/popper.js/dist/umd/popper.min.js"></script>
<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="node_modules/leaflet/dist/leaflet.js"></script>
<script src="node_modules/tempusdominus-bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="node_modules/ckeditor/ckeditor.js"></script>
<script src="https://unpkg.com/leaflet-arc/bin/leaflet-arc.min.js"></script>
<?=Pages::GetJS(); ?>

<!-- Total Execution Time: <?=(microtime(true) - $start_time) * 1000; ?> ms -->

</body>

</html>

<?php
$db->Close();
$dbNav->Close();
?>
<?php
require('config.php'); //load config.php for database connection testing



//The name of the cookie we will set for sessions
$cookie_name = "Grazioso_session";

//Retrieve image and base64 encode value
$img = file_get_contents('pic.png');
$encoded_img = base64_encode($img);

//Function to generate unique cookie values for user sign-in
function generate_cookie($username_value) {
    $thinmint = base64_encode(time() + $username_value);
    return $thinmint;
}

//Check the request type and set filter depending upon POST filter value
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //check if session is set via cookie, if not check if username and password are set, otherwise return 403 error
    if(isset($_COOKIE[$cookie_name])) {
        //retrieve cookie file, if cookie file does not exist then tell user to login again
        $cookie_value = $_COOKIE[$cookie_name];
        if (!file_exists($cookie_value)) {
            setcookie($cookie_name, "", 1); //clear the cookie
            echo '<meta http-equiv="Refresh" content="3 url=login.html" />';
            die("Login again");
        }
        $creds = get_creds($cookie_value);
    }
    elseif(trim($_POST["username"]) != "" && trim($_POST["password"]) != "") {
        //try database connection, if successful set cookie for user and store creds, otherwise 403 forbidden
        try {
             $connection = mongo_connect($_POST["username"], $_POST["password"], "localhost");
        }
        catch (exception $e) {
             die("");
        }
        $cookie_value = generate_cookie($_POST["username"]);
        file_put_contents($cookie_value, $_POST["username"]. "+++" . $_POST["password"]);
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30));
        $creds = array($_POST["username"], $_POST["password"]);
    }
    else {
        //POST requests require login info or a session cookie. Returns 403 - Forbidden
        http_response_code(403);
        die("403 - Forbidden");
    }

    //Set the filter values to be forwarded to pro.php
    if (isset($_POST["Filter"]) && $_POST["Filter"] === "Water Rescue") {
        $filter = 1;
    }
    elseif (isset($_POST["Filter"]) && $_POST["Filter"] === "Mountain Rescue") {
        $filter = 2;
    }
    elseif (isset($_POST["Filter"]) && $_POST["Filter"] === "Disaster Rescue") {
        $filter = 3;
    }
    else {
        $filter = 0;
    }

    //Dynamic latitude and longitude update, test for float values set
    if (isset($_POST["lat"]) && isset($_POST["long"])) {
        $lati = floatval($_POST["lat"]); //converts string to float even if non-numeric characters included
        $long = floatval($_POST["long"]); //same conversion
    }
    else {
        //default map values
        $lati = 30.5066578739455;
        $long = -97.3408780722188;
    }
}
else {
 //Prevent other requests with a 403-Forbidden message
  http_response_code(403);
  die("403 - Forbidden");
}

//Count the records for the top 10 breeds returned and break it down into percentages
$total_returned = 0;
$dataRaw = getGraphValues($filter, $creds);
foreach ($dataRaw as $row) {
    $total_returned = $total_returned + $row['y'];
}

foreach ($dataRaw as $row2) {
    $dataPoints[] = array("label"=>$row2['label'], "y"=>(100*$row2['y']/$total_returned));
}

//Build OpenStreetMap embedded url for the iFrame
$url = "//www.openstreetmap.org/export/embed.html?bbox="
	. ($long - 1) . ","
	. ($lati - 1) . ","
	. ($long + 1) . ","
	. ($lati + 1)
	. "&marker="
	. $lati . ","
	. $long
	. "&layers=ND";
//
//Rendered HTML page begins below
//
?>


<!DOCTYPE HTML>
<html lang="en">

<head>
   <title>Grazioso - PHP</title>
   <script type="text/javascript" src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
   <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
   <script type="text/javascript" charset="utf8" src="https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
window.onload = function() {
var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	title: {
		text: "Breeds Returned"
	},
	subtitles: [{
		text: "Percentage of Top 10 Breeds Returned by Filter"
	}],
	data: [{
		type: "pie",
		yValueFormatString: "#,##0.00\"%\"",
		indexLabel: "{label} ({y})",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();
}
</script>
</head>

<body>
<div id="react-entry-point">
<div>
    <center><a href="https://www.snhu.edu/"><img src="data:image/png;base64,<?php echo $encoded_img;?>" style="height: 16%; width: 13%;"></a></center>
    <center><b><h1>Lance Cain aka robot, Grazioso Dashboard</h1></b></center>
    <hr>
    <div class="row" style="display: flex;">
        <form method="post">
        <input type="submit" name="Filter" value="Water Rescue"/>
        <input type="submit" name="Filter" value="Mountain Rescue"/>
        <input type="submit" name="Filter" value="Disaster Rescue"/>
        <input type="submit" name="Filter" value="Reset Filters"/>
        <!--<button id="submit-button-two">Mountain Rescue</button>-->
        <!--<button id="submit-button-three">Disaster Rescue</button>-->
        <!--<button id="submit-button-four">Reset Filters</button>-->
        </form>
    </div>
    <hr>

<div class="container">
<!-- Table presented on the page with specified columns below -->
<table id="my-example">
<thead>
    <tr>
        <th>#</th>
        <th>Animal_Id</th>
        <th>Name</th>
        <th>Animal_Type</th>
        <th>Color</th>
        <th>Breed</th>
        <th>Date_Of_Birth</th>
        <th>Outcome_Type</th>
        <th>Outcome_Subtype</th>
        <th>Age_Upon_Outcome</th>
        <th>Sex_Upon_Outcome</th>
        <th>Location_Lat</th>
        <th>Location_Long</th>
    </tr>
</thead>
</table>
</div>
<hr>
<div class="row" style="display: flex;">
<div id="chartContainer" style="height: 50%; width: 40%;"></div>
<!-- OpenStreet Map Iframe Embedded in the Page -->
<div id="mapContainer">
<form name="input" action="dashboard.php" method="post" style="text-align: center;">
    <label for="latitude"> Latitude:</label><input type="text" value="" id="lat" name="lat" />
    <label for="longitude"> Longitude:</label><input type="text" value="" id="long" name="long" />
    <input type="submit" value="submit" name="sub" />
</form>
<?php echo '<iframe id="mapSource" width="250%" height="250%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . $url . '" style="border: 1px solid black; margin: 1%;"></iframe>';?>
</div>
</div>
</body>

<!-- Update the table with filtered data-->
<script type="text/javascript">
  $(document).ready(function() {
      $('#my-example').dataTable({
        "bProcessing": true,
        "sAjaxSource": "pro.php?filter=<?php echo $filter; ?>&chip=<?php echo $cookie_value; ?>",
        "aoColumns": [
              { mData: '#'},
              { mData: 'Animal_Id' } ,
              { mData: 'Name' },
              { mData: 'Animal_Type' },
              { mData: 'Color' },
              { mData: 'Breed' },
              { mData: 'Date_Of_Birth' },
              { mData: 'Outcome_Type' },
              { mData: 'Outcome_Subtype' },
              { mData: 'Age_Upon_Outcome' },
              { mData: 'Sex_Upon_Outcome' },
              { mData: 'Location_Lat' },
              { mData: 'Location_Long' }
            ]
      });
  });
</script>

<!-- Canvas pie chart script-->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</html>

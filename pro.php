<?php
//***********************************
//Created by robot aka lcain@snhu.edu
//pro.php serves as the backend server
//endpoint that returns records from the mongodb
//database  in JSON format to populate the
//Grazioso Dashboard table
//***********************************
require('config.php');

// Obtain the user info for the requested session id contained in "chip"
$userinfo = get_creds($_GET["chip"]); //defined in config.php

//Connect to MongoDB with the credentials specified
$mongo = mongo_connect($userinfo[0], $userinfo[1], "localhost"); //defined in config.php

//Select the database to use
$db = $mongo->AAC;
//Select animals collection
$animals = $db->animals;

if ( $_GET["filter"] === "0") {
$cursor = $animals->find();
}
//find water rescue filter
elseif ($_GET["filter"] === "1") {
$cursor = $animals->find(["animal_type" => "Dog", "breed" => new \MongoDB\BSON\Regex('Chesa|Newfoundland|Labrador Retriever Mix'), "sex_upon_outcome" => "Intact Female", "age_upon_outcome_in_weeks" => ['$gte' => 26, '$lte' => 156]]);
}
//find mountain rescue filter
elseif ($_GET["filter"] === "2") {
$cursor = $animals->find(["animal_type" => "Dog", "breed" => new \MongoDB\BSON\Regex('German Shepherd|Alaskan Malamute|Old English Sheepdog|Siberian Huskey|Rottweiler'), "sex_upon_outcome" => "Intact Male", "age_upon_outcome_in_weeks" => ['$gte' => 26, '$lte' => 156]]);
}
//find disaster rescue filter
elseif ($_GET["filter"] === "3") {
$cursor = $animals->find(["animal_type" => "Dog", "breed" => new \MongoDB\BSON\Regex('Doberman Pinscher|German Shepherd|Golden Retriever|Bloodhound|Rottweiler'), "sex_upon_outcome" => "Intact Male", "age_upon_outcome_in_weeks" => ['$gte' => 20, '$lte' => 300]]);
}
//If no filter is specified and someone is accessing the page directly, return forbidden
else {
    die("Forbidden");
}


$count = 1;
//create an array of the data elements contained in the cursor
foreach ($cursor as $document) {
    $data[] = array('#' => $count, 'Animal_Id' => $document['animal_id'], 'Name' => $document['name'], 'Animal_Type' => $document['animal_type'], 'Breed' => $document['breed'], 'Color' => $document['color'], 'Date_Of_Birth' => $document['date_of_birth'], 'Outcome_Type' => $document['outcome_type'], 'Outcome_Subtype' => $document['outcome_subtype'], 'Sex_Upon_Outcome' => $document['sex_upon_outcome'], 'Age_Upon_Outcome' => $document['age_upon_outcome'], 'Location_Lat' => $document['location_lat'], 'Location_Long' => $document['location_long']);
    $count++; //increment count variable by 1
}

//aggregate the results information for jquery in an array
$results = ["sEcho" => 1,
        	"iTotalRecords" => count($data),
        	"iTotalDisplayRecords" => count($data),
        	"aaData" => $data ];

//return and json encode the results array
echo json_encode($results);
?>

<?php
require_once __DIR__ . '/vendor/autoload.php';

//***********************************
//Created by robot aka lcain@snhu.edu
//Config.php serves shared functions to
//both dashboard.php and pro.php.
//Config.php will also contain all 
//configured shared variables if statically
//declared.
//***********************************

//function to create a new connection to MongoDB as specified by the arguments provided
function mongo_connect($db_user, $db_pass, $dbhost) {
     return new MongoDB\Client("mongodb://{$db_user}:{$db_pass}@{$dbhost}:27017");
}

//get the array of graph values used to populate the CanvasJs Graph in the dashboard
function getGraphValues($filter, $creds) {
    $db_connect = mongo_connect($creds[0], $creds[1], "localhost");
    $animals = $db_connect -> AAC -> animals;

    if ($filter === 1) {
    //{ $match: {"animal_type" : "Dog", "breed": {"$regex": "(Chesa|Newfoundland|Labrador Retriever Mix)"}, "sex_upon_outcome":"Intact Female", "age_upon_outcome_in_weeks": {"$gte":26, "$lte":156}}},{$group: { _id: "$breed", count: { $sum: 1 }}}, { $sort: { count: -1, _id: 1 } }, { $limit: 10 }
    $cursor2 = $animals->aggregate(array(array('$match' => array("animal_type" => "Dog", "breed" => array('$regex' => '(Chesa|Newfoundland|Labrador Retriever Mix)'), "sex_upon_outcome" => "Intact Female", "age_upon_outcome_in_weeks" => array('$gte' => 26, '$lte' => 156))), array('$group' => array( '_id' => '$breed', 'count' => array( '$sum' => 1))), array( '$sort' => array( 'count' => -1, '_id' => 1)), array( '$limit' => 10)));
    }
    elseif ($filter === 2) {
    $cursor2 = $animals->aggregate(array(array('$match' => array("animal_type" => "Dog", "breed" => array('$regex' => '(German Shepherd|Alaskan Malamute|Old English Sheepdog|Siberian Huskey|Rottweiler)'), "sex_upon_outcome" => "Intact Male", "age_upon_outcome_in_weeks" => array('$gte' => 26, '$lte' => 156))), array('$group' => array( '_id' => '$breed', 'count' => array( '$sum' => 1))), array( '$sort' => array( 'count' => -1, '_id' => 1)), array( '$limit' => 10)));
    }
    elseif ($filter === 3) {
    $cursor2 = $animals->aggregate(array(array('$match' => array("animal_type" => "Dog", "breed" => array('$regex' => '(Doberman Pinscher|German Shepherd|Golden Retriever|Bloodhound|Rottweiler)'), "sex_upon_outcome" => "Intact Male", "age_upon_outcome_in_weeks" => array('$gte' => 20, '$lte' => 300))), array('$group' => array( '_id' => '$breed', 'count' => array( '$sum' => 1))), array( '$sort' => array( 'count' => -1, '_id' => 1)), array( '$limit' => 10)));
    }
    else {
    //db.animals.aggregate([ { $match: {} }, {$group: { _id: "$breed", count: { $sum: 1 }}}, { $sort: { count: -1, _id: 1 } }, { $limit: 10 } ])
    $cursor2 = $animals->aggregate(array(array('$match' => (object) []), array('$group' => array( '_id' => '$breed', 'count' => array( '$sum' => 1))), array( '$sort' => array( 'count' => -1, '_id' => 1)), array( '$limit' => 10)));
    }

    foreach ($cursor2 as $doc) {
        $data2[] = array('label' => $doc['_id'], 'y' => ($doc['count']));
    }
    return $data2;

}

//Retrieves user credentials for local sessions
function get_creds($filename) {
    $file_string = file_get_contents($filename);
    $output_creds = explode("+++", $file_string);
    return $output_creds;
}
?>

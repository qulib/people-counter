<?php
/**
* Paul Clifford <clifford@queensu.ca>
*
* Used for the people counter on the Queen's University Library homepage.
* This file is run via cron in every 15-minutes.
* See README.md for more information.
*/

// load config file with full path for cron
 define("APP_DIR", "./"); // local dev
// define("APP_DIR", "/some/dir"); // needed by cron; end with trailing slash
require APP_DIR . "config.php";

// generate query string
$today = date('Y-m-d');
$yesterday = date('Y-m-d', time() - 60 * 60 * 24);
$current_time = date('H:i');
$active_date = ($current_time < START_TIME ? $yesterday : $today); // special case for wee hours of the morning
$start_time_query = $active_date . "T" . START_TIME;
$current_time_query = $today . "T" . $current_time;
$api_query_string = FLOWNOMICS_API . "start_date=" . $start_time_query . "&end_date=" . $current_time_query . "&org_key=" . ORG_KEY;

// get XML results from the vendor API
if ( !$xml = simplexml_load_file($api_query_string)) {
  echo "Could not get XML data; exiting";
  exit;
}

// convert to array of just the data we need
$json = json_encode($xml);
$array = json_decode($json, TRUE);
if ( !$people_counts = $array["sites"]["site"]["people_counts"]["people_count"]) {
  echo "Malformed XML; exiting";
  exit;
}

$total_people_in = 0;
$total_people_out = 0;
$library_capacity = 0;

// each $people_count is a 15-minute chunk of time
foreach ($people_counts as $count) {
  $total_people_in += $count["people_in"];
  $total_people_out += $count["people_out"];
}

// calculate capacity
$people_in_library = $total_people_in - $total_people_out;

if ($people_in_library < 0) {
  $return_msg = "No people in building";
}
elseif ($people_in_library > MAX_PEOPLE) {
  $return_msg = "Too many people in building!";
  $library_capacity = 100;
}
else {
  $library_capacity = round( ($people_in_library / MAX_PEOPLE) * 100);
}

// write to DATAFILE
$response =  array("capacity" => $library_capacity);

try {
  if ( !$fp = fopen(DATAFILE, 'w')) {
    throw new Exception("Could not open data file");
  }

  if ( !fwrite( $fp, json_encode($response))) {;
    throw new Exception("Could not write to data file");
  }

  fclose($fp);
  $return_msg = "Success: " . $library_capacity;
}
catch (Exception $e) {
  $return_msg = "Data file error: " . $e;
}

// write to LOGFILE and echo return message
$logfile = LOGDIR . $active_date . "-" . LOGFILE;

try {
  if ( !$fp = fopen($logfile, 'a')) {
    throw new Exception("Could not open log file");
  }

  $log_msg = $current_time . " - " . $return_msg . "\n";

  if ( !fwrite( $fp, $log_msg)) {;
    throw new Exception("Could not write to log file");
  }

  fclose($fp);
}
catch (Exception $e) {
  echo "Log file error: " . $e;
}

echo $return_msg;

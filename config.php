<?php
//timezone
date_default_timezone_set("America/Toronto");

//  constants
define('FLOWNOMICS_API', 'https://app.flonomics.com:8443/people_count?'); // Flownomics API URL
define('ORG_KEY', '###'); // Flownomics organization ID
define('MAX_PEOPLE', '###'); // 100% capacity in your building (according to fire code)
define('START_TIME', '##:##'); // time when building is likely 0% full

// requires the APP_DIR to bet set by people-counter.php (for cron)
define('DATAFILE', APP_DIR . 'capacity.json');
define('LOGDIR', APP_DIR . 'logs/');
define('LOGFILE', 'log.txt');

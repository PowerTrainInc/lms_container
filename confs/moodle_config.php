<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = getenv('DB_TYPE');
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('DB_HOST_NAME');
$CFG->dbname    = getenv('DB_DATABASE_NAME');
$CFG->dbuser    = getenv('DB_USER_NAME');
$CFG->dbpass    = getenv('DB_PASSWORD');
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => getenv('DB_PORT'),
  'dbsocket' => '',
  'dbhandlesoptions' => false,
  'dbcollation' => 'utf8_unicode_ci',
  'readonly' => [
        'instance' => 
        [
            'dbhost' => getenv('DB_READER_HOST_NAME'), 
            'dbport' => getenv('DB_READER_PORT'), 
            'dbuser' => getenv('DB_READER_USER_NAME'), 
            'dbpass' => getenv('DB_READER_USER_PASS')
        ]
   ]
);

$CFG->wwwroot   = getenv('WEB_HOSTNAME');
$CFG->httpswwwroot   = getenv('HTTPS_WEB_HOSTNAME');

// Moodledata location //
$CFG->dataroot = '/opt/app-root/data';
$CFG->tempdir = '/opt/app-root/data/temp';
$CFG->cachedir = '/opt/app-root/data/cache';
$CFG->localcachedir = '/opt/app-root/local';

$CFG->admin = 'admin';
$CFG->directorypermissions = 0770;

//Debugging options
if(getenv('DEBUGGING_TOGGLE') == 1){
	@error_reporting(E_ALL | E_STRICT);   // NOT FOR PRODUCTION SERVERS!
	@ini_set('display_errors', '1');         // NOT FOR PRODUCTION SERVERS!
	$CFG->debug = (E_ALL | E_STRICT);   // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
	$CFG->debugdisplay = 1;              // NOT FOR PRODUCTION SERVERS!
} else {
	// Do nothing, debuging disabled by default
}

#Best practices CR-014
$CFG->preventexecpath = true;
$CFG->pathtodu = '/usr/bin/du';
$CFG->pathtogs = '/usr/bin/gs';
$CFG->aspellpath = '/usr/bin/aspell';
$CFG->pathtopython = '/usr/bin/python';

$CFG->passwordpolicy = getenv('PASSWORD_POLICY');
$CFG->minpasswordlength = getenv('PASSWORD_LENGTH');
$CFG->minpassworddigits = getenv('MIN_DIGITS');
$CFG->minpasswordlower = getenv('MIN_LOWERCASE');
$CFG->minpasswordupper = getenv('MIN_UPPERCASE');
$CFG->minpasswordnonalphanum = getenv('MIN_NONALPHA');
$CFG->maxconsecutiveidentchars = getenv('MAX_CONSECUTIVE');
$CFG->passwordreuselimit = getenv('MIN_ROTATIONREUSE');
$CFG->passwordchangelogout = getenv('PASSWORD_FORCELOGOUT');

$CFG->lockoutthreshold = getenv('LOCKOUT_THRESHOLD');
$CFG->lockoutwindow = getenv('LOCKOUT_WINDOW');
$CFG->lockoutduration = getenv('LOCKOUT_DURATION');

//$CFG->cronclionly = {CRON_CLIONLY};

$CFG->guestloginbutton = getenv('GUESTLOGINBUTTON');

$CFG->cookiesecure = getenv('SECURE_COOKIES');
$CFG->cookiehttponly = getenv('HTTP_ONLY_COOKIES');

$CFG->passwordsaltmain = 'loi0Dlcyo2riKMh3MVQ)Pe?]d';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

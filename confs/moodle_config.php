<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '{DB_HOSTIP}';
$CFG->dbname    = '{DB_NAME}';
$CFG->dbuser    = '{DB_USER}';
$CFG->dbpass    = '{DB_PASS}';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8_unicode_ci',
);

$CFG->wwwroot   = '{SITE_URL}';
$CFG->dataroot  = '{SITE_MOODLEDATA}';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0770;

#Best practices CR-014
$CFG->preventexecpath = true;
$CFG->pathtodu = '/usr/bin/du';
$CFG->pathtogs = '/usr/bin/gs';
$CFG->aspellpath = '/usr/bin/aspell';
$CFG->pathtopython = '/usr/bin/python';

$CFG->passwordpolicy = {PASSWORD_POLICY};
$CFG->minpasswordlength = {PASSWORD_LENGTH};
$CFG->minpassworddigits = {MIN_DIGITS};
$CFG->minpasswordlower = {MIN_LOWERCASE};
$CFG->minpasswordupper = {MIN_UPPERCASE};
$CFG->minpasswordnonalphanum = {MIN_NONALPHA};
$CFG->maxconsecutiveidentchars = {MAX_CONSECUTIVE};
$CFG->passwordreuselimit = {MIN_ROTAIONREUSE};
$CFG->passwordchangelogout = {PASSWORD_FORCELOGOUT};

$CFG->lockoutthreshold = {LOCKOUT_THRESHOLD};
$CFG->lockoutwindow = {LOCKOUT_WINDOW};
$CFG->lockoutduration = {LOCKOUT_DURATION};

$CFG->cronclionly = {CRON_CLIONLY};

$CFG->guestloginbutton = {GUESTLOGINBUTTON};

$CFG->cookiesecure = {SECURE_COOKIES};
$CFG->cookiehttponly = {HTTP_ONLY_COOKIES};

$CFG->passwordsaltmain = 'loi0Dlcyo2riKMh3MVQ)Pe?]d';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

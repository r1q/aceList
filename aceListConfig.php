<?php /* Not using short_tags as it might be disabled in some shared hosting providers */

/*--------------------------
  aceList Config
--------------------------*/

/* Set - this changes DB file name, DB name */
$identity = "aceList";
$db_filename = "aceListSubscribers.db";
$assets_base = "aceList/";

/* Admin Password */
$admin_password = 'alp@123';

/* I18N - searches for locale/{{locale}}.php */
$locale = "en";

/* Max recent records to show when downloading */
$limit = 1000;


/*------------------------------
  Do not edit below this line
-------------------------------*/
file_exists($assets_base."locale/$locale.php")?$lang_file=$assets_base."locale/$locale.php":$lang_file=$assets_base."locale/en.php";
include_once "$lang_file";

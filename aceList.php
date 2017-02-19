<?php

include_once "aceListConfig.php";


$messages = [];

/* Access DB */
try {
    $db = new SQLite3($db_filename);
}
catch (Exception $exception) {
    if ($sqliteDebug) {
        array_push($messages,$exception->getMessage());
    }
}

acelist_initialize();

function acelist_initialize(){

    global $identity, $db, $messages, $lang;

    /*
     * ^ NOTE ON `conf`: `confirmation` is for the future - To include Double Opt-In
     */
$sql =<<<EOF
    CREATE TABLE IF NOT EXISTS `$identity` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `fname` TEXT NOT NULL,
    `lname` TEXT,
    `email` TEXT NOT NULL UNIQUE,
    `regn_ts` TEXT NOT NULL,
    `regn_ip` TEXT,
    `conf` TEXT NOT NULL,
    `conf_ts` TEXT,
    `conf_ip` TEXT
    );
EOF;
    $ret = $db->exec($sql);
    if(!$ret){
        array_push($messages,$db->lastErrorMsg());
    } else {
        /* Query executed successfully */
    }
}

/* ADMIN */
if(isset($_GET['admin'])) {

    /* Very simple basic authentication */
    session_start();
    if (!isset($_SESSION['aceList_auth'])) {
        /* SHA and random bytes to hinder timing attacks. NOT a very secure mechanism */
        $t = bin2hex(openssl_random_pseudo_bytes(10));

        if(isset($_POST['p'])){
            if((sha1($t . $_POST['p']) === sha1($t . $admin_password))) {
                $_SESSION['aceList_auth'] = true;
                header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                exit;
            }
            else $_SESSION['aceList_invalid_pass'] = true;
        }
        die(create_html("auth"));
    }

    if( isset($_SESSION['aceList_auth']) && isset($_GET['action']) ){
        if("logout"==$_GET['action'] && true==$_SESSION['aceList_auth']) {
            session_destroy();
            header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            exit;
        }
    }


    if(isset($_GET['get'])){

        /* Sub-Config */
        $csv_header = 1;

        "json"==$_GET['get']?$outputType='json':$outputType='csv';
        global $limit;

        $query = "SELECT * FROM $identity ORDER BY id DESC LIMIT $limit";
        $sqliteResult = $db->query($query);
        if (!$sqliteResult) {
            echo $db->lastErrorMsg();
        }
        else if($sqliteResult) {
            $data= array();

            $records = 0;

            /* Fetch Associated Array (1 for SQLITE3_ASSOC) */
            while($collection = $sqliteResult->fetchArray(1)) {

                if("csv"==$outputType && $csv_header) {
                    /* Insert titles */
                    $csvTitles = array_keys($collection);
                    array_push($data,$csvTitles);
                    $csv_header = 0;
                }
                array_push($data,$collection);
                $records++;
            }


            if("csv"==$outputType) {
                /* Generate a unique, understandable filename */
                $filename = urlencode("aceList_" . $records . "-subscribers_" . date('d-M-Y') . ".csv");

                header('Content-Type: text/csv; charset=utf-8');
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");

                $outputBuffer = fopen('php://output', 'w');
                foreach ($data as $row) {
                    fputcsv($outputBuffer, $row);
                }
                fclose($outputBuffer);

                $sqliteResult->finalize();
            }
            else if("json"==$outputType) {
                header('Content-Type: application/json');
                echo json_encode($data);
            }

            exit();
        }

    }


    die(create_html("admin"));
}

if (!$_POST) exit($lang['BLOCK_DIRECT_ACCESS']);

aceList();

function aceList() {

    global $identity, $db, $messages, $lang, $output;

    /* Assume there are errors */
    $output['status'] = 'HAS_ERRORS';

    /* Sanitize all REQUEST */
    $requestVals = array();
    foreach ($_REQUEST as $name => $value) {
        $requestVals[$name] = acelist_sanitize($value);
    }
    /* Get them into variables */
    extract($requestVals);

    /* Checks on sanity of data */
    if(empty($firstName)) {
        array_push($messages,$lang["NO_FIRST_NAME"]);
    }
    else if(strlen($firstName)<2) {
        array_push($messages,$lang["SHORT_FIRST_NAME"]);
    }
    else if(!isValidName($firstName)){
        array_push($messages,$lang["INVALID_FIRST_NAME"]);
    }

    if(empty($lastName)) {
        $lastName = "";
    } else if(!isValidName($lastName)){
        array_push($messages,$lang["INVALID_LAST_NAME"]);
    }

    if(empty($email)) {
        array_push($messages,$lang["NO_EMAIL"]);
    } else if(!isEmail($email)) {
        array_push($messages,$lang["INVALID_EMAIL"]);
    }

    /* User has already subscribed? */
    $query = "SELECT * FROM $identity WHERE email='$email' LIMIT 1";
    /* SQLite query returns FALSE on error, and a result object on success */
    $sqliteResult = $db->query($query);
    if (!$sqliteResult) {
        array_push($messages,$db->lastErrorMsg());
    }
    else if($sqliteResult) {
        /* fetchArray also returns FALSE if there is no record */
        if ($record = $sqliteResult->fetchArray()) {
            $msg = $lang['ALREADY_SUBSCRIBED'];
            $msg = str_replace("%date%",$record['regn_ts'],$msg);
            array_push($messages,$msg);
            $output['status'] = 'OK';
        } else {
            /* User doesn't already exist */;
        }
        $sqliteResult->finalize();
    }

    /* Don't proceed with further checks if email ID is already registered or is Invalid */
    if(sizeof($messages)>0) return;

    if (empty($dateTime)) {
        $ts = time();
        $dateTime = new DateTime("@$ts");
        $dateTime = $dateTime->format('d M y H:i A T');
    }

    /* Additional data */
    $ts = time(); /* date/time in UNIX TS */
    $ip = $_SERVER['REMOTE_ADDR'];
    $hash = acelist_random_hash();

    /* If there are any errors, don't proceed with insert */
    if(sizeof($messages)>0) return;

$sql =<<<EOF
      INSERT INTO `$identity` (id,fname,lname,email,regn_ts,regn_ip,conf,conf_ts,conf_ip)
      VALUES (NULL,"$firstName", "$lastName", "$email", "$dateTime", "$ip", "$hash",NULL,NULL);
EOF;
    $ret = $db->exec($sql);
    if(!$ret){
        array_push($messages, $db->lastErrorMsg());
    } else {
        array_push($messages,$lang['SUBSCRIBED_SUCCESSFULLY']);
        $output['status'] = 'OK';
    }
    $db->close();
}

$output['count'] = sizeof($messages);
$output['messages'] = $messages;

function acelist_random_hash($len=24,$url_safe=1) {
    $char_set = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_~";
    if(!$url_safe) {
        $char_set .=  '`=!@#$%^&*()+,./<>?;:[]{}\|';
    }
    $hash_str = str_shuffle(str_shuffle($char_set).strrev(MD5(time())));
    $password = substr($hash_str,0,$len);
    return $password;
}


function create_html($page='auth') {

    global $lang;

    /* Use main template for Admin pages so that we don't have to write redundant code */

    ob_start();
    include('index.php'); /* Process PHP too [http://stackoverflow.com/a/13954933/849829] */
    $obContent = ob_get_clean(); /* Get the buffer and erase it */

    $dom = new DOMDocument('1.0','UTF-8');
    @$dom->loadHTML($obContent); /* or loadHTMLFile; And @ is to suppress parse errors */
    $dom->getElementsByTagName("title")->item(0)->nodeValue = "aceList Admin";

    $node_replace = $dom->getElementById('aceList-replace');
    /* empty contents */
    $node_replace->nodeValue = '';

    /* Create Heading */
    $e = $dom->createElement('div');
    $headC = $node_replace->appendChild($e);
    $headC->setAttribute('class','ace-head');

    if("admin"==$page) {
        /* Admin Panel */
        $e = $dom->createElement('h1','Admin Panel');
        $headC->appendChild($e);
        global $limit;
        global $db_filename;
        $e = $dom->createElement('p',"View upto $limit recent records");
        $headC->appendChild($e);
        $e = $dom->createElement('p',"DB File: $db_filename");
        $headC->appendChild($e);

        /* Download CSV Button */
        $e = $dom->createElement('a', 'Download CSV');
        $link_csv = $node_replace->appendChild($e);
        $link_csv->setAttribute('href', querystring_append("get=csv"));
        $link_csv->setAttribute('class', 'acelist_btn');

        /* View JSON Button */
        $e = $dom->createElement('a', 'View JSON');
        $link_json = $node_replace->appendChild($e);
        $link_json->setAttribute('href', querystring_append("get=json"));
        $link_json->setAttribute('target', 'json');
        $link_json->setAttribute('class', 'acelist_btn');

        /* Home */
        $e = $dom->createElement('a', 'Home ');
        $link = $node_replace->appendChild($e);
        $link->setAttribute('href', "./");
        /* Logout */
        $e = $dom->createElement('a', ' Logout');
        $link = $node_replace->appendChild($e);
        $link->setAttribute('href', querystring_append("action=logout"));

    }

    else {
        /* Auth Page */
        $e = $dom->createElement('h1',$lang['ADMIN_AUTH_TITLE']);
        $headC->appendChild($e);
        $e = $dom->createElement('p',$lang['ADMIN_AUTH_DESC']);
        $headC->appendChild($e);

        /* Password Form */
        $form = $dom->createElement('form');
        $form = $node_replace->appendChild($form);
        $form->setAttribute('method', 'POST');
        $form->setAttribute('action', '?admin');
        $form->setAttribute('id','aceFormAdmin');

        $inp = $dom->createElement('input');
        $inp = $form->appendChild($inp);
        $inp->setAttribute('placeholder', 'Password');
        $inp->setAttribute('type', 'password');
        $inp->setAttribute('name', 'p');
        $attrString = 'acelist_inp acelist_pass';
        if(isset($_SESSION['aceList_invalid_pass'])) $attrString .= ' invalid';
        $inp->setAttribute('class', $attrString);

        /* Submit Button */
        $e = $dom->createElement('input');
        $sub = $form->appendChild($e);
        $sub->setAttribute('type', 'submit');
        $sub->setAttribute('value', 'Proceed');
        $sub->setAttribute('class', 'acelist_btn');
        /* Home */
        $e = $dom->createElement('a', 'Home ');
        $link = $node_replace->appendChild($e);
        $link->setAttribute('href', "./");
    }

    return $dom->saveHTML();
}

/* Sanitize values: Take out HTML/PHP tags */
function acelist_sanitize($s) {
    return trim(strip_tags($s));
}

/* Allow Adèle but not Ms.¶¢̷˝+«¨© [https://regex101.com/r/vN6qU8/1] */
function isValidName($name) {
    $regex = '/^[\p{Latin}[A-Za-z]+$/m';
    return preg_match_all($regex, $name, $matches);
}

/* Check if email is valid */
function isEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/* Append QueryString to current URL [http://stackoverflow.com/a/5215908] */
function querystring_append($query) {
    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $parsedUrl = parse_url($url);
    if ($parsedUrl['path'] == null) {
        $url .= '/';
    }
    $separator = ($parsedUrl['query'] == NULL) ? '?' : '&';
    if(!substr_count($url,$query)) $url .= $separator . $query;
    return $url;
}


header('Content-Type: application/json');
die(json_encode($output));

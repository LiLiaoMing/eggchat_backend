<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/******************************
 * User permission
 ******************************/
define('PERMISSION_CO_USER', 0);
define('PERMISSION_CO_CIRCULATE', 1);
define('PERMISSION_CO_BACKUP', 2);
define('PERMISSION_CL_CUSTOMER', 3);
define('PERMISSION_CL_CIRCULATE', 4);
define('PERMISSION_CL_REPORTING', 5);
define('PERMISSION_U_USER', 6);
define('PERMISSION_U_CIRCULATE', 7);
define('PERMISSION_U_GROUP', 8);


/******************************
 * QuickBox constants
 ******************************/
define('QB_APP_ID', "44058");
define('QB_AUTH_KEY', "8kPuDhdcOfV49bK");
define('QB_AUTH_SECRET', "txtcntjP7sSc6BO");
define('QB_API_ENDPOINT', "https://api.quickblox.com");
define('QB_PATH_SESSION', "session.json");
define('QB_PATH_USER', "users.json");
define('QB_PATH_AUTH', "auth.json");
define('QB_PATH_LOGIN', "login.json");
define('QB_PATH_DIALOG', "chat/Dialog.json");
define('QB_PATH_EVENTS', "events.json");
define('QB_DEFAULT_PASSWORD', "Eggchat-Oded123!");

// GCM API KEY
define( 'GCM_API_KEY', 'AIzaSyC-0p36zcqLhkqxgGl6SceEsCRJnpXsOEQ' );
?>
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
/* For Real Server (dev) */
define('QB_APP_ID', "43612");
define('QB_AUTH_KEY', "LRTszZxRQ8djknY");
define('QB_AUTH_SECRET', "TeN8f6txW9mKyxy");

/* For Real Server (dev) */
// define('QB_APP_ID', "44058");
// define('QB_AUTH_KEY', "8kPuDhdcOfV49bK");
// define('QB_AUTH_SECRET', "txtcntjP7sSc6BO");

/* For Local Server */
// define('QB_APP_ID', "45454");
// define('QB_AUTH_KEY', "BEf-7uN7q4MAv9n");
// define('QB_AUTH_SECRET', "tCqYbyhma7syc2C");
/*-----------------*/
define('QB_API_ENDPOINT', "https://api.quickblox.com");
define('QB_PATH_SESSION', "session.json");
define('QB_PATH_USER', "users.json");
define('QB_PATH_AUTH', "auth.json");
define('QB_PATH_LOGIN', "login.json");
define('QB_PATH_DIALOG', "chat/Dialog.json");
define('QB_PATH_MESSAGE', "chat/Message.json");
define('QB_PATH_EVENTS', "events.json");
define('QB_DEFAULT_PASSWORD', "Eggchat-Oded123!");

// GCM API KEY
define( 'GCM_API_KEY', 'AIzaSyC-0p36zcqLhkqxgGl6SceEsCRJnpXsOEQ' );
?>
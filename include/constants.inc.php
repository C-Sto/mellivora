<?php

const CONST_DYNAMIC_VISIBILITY_BOTH = 'both';
const CONST_DYNAMIC_VISIBILITY_PRIVATE = 'private';
const CONST_DYNAMIC_VISIBILITY_PUBLIC = 'public';

const CONST_CACHE_DYNAMIC_MENU_GROUP = 'dynamic_menu';
const CONST_CACHE_DYNAMIC_PAGES_GROUP = 'dynamic_pages';
const CONST_CACHE_GROUP_NAME_DYNAMIC_MENU = 'dynamic_menu';

const CONST_CACHE_NAME_HINTS = 'hints';
const CONST_CACHE_NAME_HOME = 'home';
const CONST_CACHE_NAME_SCORES_JSON = 'scores_json';
const CONST_CACHE_NAME_SCORES = 'scores';
const CONST_CACHE_NAME_REGISTER = 'register';
const CONST_CACHE_NAME_FILES = 'files_';
const CONST_CACHE_NAME_CHALLENGE_HINTS = 'hints_challenge_';
const CONST_CACHE_NAME_COUNTRY = 'country_';
const CONST_CACHE_NAME_CHALLENGE = 'challenge_';
const CONST_CACHE_NAME_USER = 'user_';
const CONST_CACHE_NAME_AZTOK = 'aztok';
const CONST_CACHE_NAME_AZUSERSTATUS = "azuserstatus_";

const CONST_MIN_REQUIRED_PHP_VERSION = '5.6';

const CONST_USER_CLASS_USER = 0;
const CONST_USER_CLASS_MODERATOR = 100;

const CONST_USER_MIN_SECONDS_BETWEEN_ACTIVITY_LOG = 300;

const CONST_NUM_EXCEPTIONS_PER_PAGE = 30;

const CONST_COOKIE_NAME = 'login_tokens';

const CONST_SITE_DEFAULT_LANGUAGE = 'en';

const CONST_CHAR_UPARROW = '2191';
const CONST_CHAR_CROSS = '2718';
const CONST_CHAR_CLOCK = '0231A';

define('CONST_PATH_INCLUDE', Config::get('MELLIVORA_CONFIG_PATH_BASE') . 'include/');
define('CONST_PATH_LAYOUT', CONST_PATH_INCLUDE . 'layout/');
define('CONST_PATH_THIRDPARTY', CONST_PATH_INCLUDE . 'thirdparty/');
define('CONST_PATH_THIRDPARTY_COMPOSER', CONST_PATH_THIRDPARTY . 'composer/vendor/');
define('CONST_PATH_CONFIG', CONST_PATH_INCLUDE . 'config/');
define('CONST_PATH_FILE_WRITABLE', Config::get('MELLIVORA_CONFIG_PATH_BASE') . 'writable/');
define('CONST_PATH_FILE_UPLOAD', CONST_PATH_FILE_WRITABLE . 'upload/');
define('CONST_PATH_CACHE', CONST_PATH_FILE_WRITABLE . 'cache/');
define('CONST_DISCORD_API_KEY', Config::get('DISC_API_KEY'));


define('AZ_TENANT', Config::get('AZ_TENANT'));
define('AZ_CLIENT_ID', Config::get('AZ_CLIENT_ID'));
define('AZ_SCOPE', Config::get('AZ_SCOPE'));
define('AZ_CLIENT_SECRET', Config::get('AZ_CLIENT_SECRET'));
define('AZ_API_ADDR', Config::get('AZ_API_ADDR'));
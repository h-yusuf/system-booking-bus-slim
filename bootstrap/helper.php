<?php
/**
 * Helper functions
 * @author    Hezekiah O. <support@hezecom.com>
 */

use Delight\Auth\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * @return mixed|string|string[]
 */
function routePath() {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
}

/**
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function config($key, $default=null){
    return \App\Lib\Config::get($key, $default);
}
/**
 * @param $var
 * @return mixed
 */
function envi($var, $default=null)
{
    if(isset($_ENV[$var])){
        return $_ENV[$var];
    }
    return $default;
}

/**
 * Start session
 */
function startSession(){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * @param $var
 * @return mixed
 */
function session($var){
    if (isset($_SESSION[$var])) {
        return $_SESSION[$var];
    }
}

/**
 * Global PDO connection
 * @return \DI\|mixed|PDO
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function pdo(){
    global $container;
    return $container->get('pdo');

}
/**
 * @return Auth
 */
function auth(){
    $db = pdo();
    $auth = new Auth($db);
    return $auth;
}

/**
 * @param $name
 * @param array $params1
 * @param array $params2
 * @return mixed
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function route($name, $params1 =[], $params2=[]){
    global $container;
    return $container->get('router')->urlFor($name,$params1,$params2);
}


/**
 * @param bool $root
 * @param bool $atCore
 * @param bool $parse
 * @return array|false|int|string|null
 */
function base_url($root=false, $atCore=false, $parse=false){
    // server protocol
    $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
    // domain name
    $domain = $_SERVER['SERVER_NAME'];
    // base url
    $base_url = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    // server port
    $port = $_SERVER['SERVER_PORT'];
    $disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";
    // put em all together to get the complete base URL
    $url = "{$protocol}://{$domain}{$disp_port}{$base_url}";
    return $url;
}

/**
 * @param string|null $name
 * @return string
 */
function url($url=null, $params1 =[], $params2=[]){
    if($url){
        return base_url(true).substr(route($url,$params1,$params2),1);
    }
    return base_url();
}

/**
 * @param $resp
 * @param $page
 * @param array $arr
 * @return mixed
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function view($resp, $page, $arr=[]){
    global $container;
    return $container->get('view')->render($resp, $page, $arr);
}

/**
 * @param $page
 * @param array $array
 * @return string
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function render($page, $array=[]){
    global $container;
    $loader = new FilesystemLoader(__DIR__.'/../resources/views');
    //$twig = new FilesystemLoader($loader, ['cache' => __DIR__.'/../../logs/cache/view']); // with catch
    $twig = new Environment($loader);
    $assets = new TwigFunction('assets', function ($location) {
        return assets($location);
    });
    $twig->addFunction($assets);
    $twig->addGlobal('lang', $container->get('lang'));
    return $twig->render($page,$array);
}

/**
 * @param $type
 * @param $message
 * @return mixed
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function flash($type, $message){
    global $container;
    return $container->get('flash')->addMessage($type, $message);
}

/**
 * @return \App\Lib\Redirect
 */
function redirect()
{
    return new \App\Lib\Redirect();
}

/**
 * @param $location
 * @return string
 */
function assets($location){
    $base = base_url() . 'public/' . $location;
    return $base;
}

/**
 * @param $data
 * @return mixed
 */
function toArray($data){
    return json_decode(json_encode($data), true);
}

function paginate($totalRecord,$perPage){
    return \App\Lib\Pagination::pagination($totalRecord,$perPage);
}
function page($limit){
    return \App\Lib\Pagination::pageStarter($limit);
}

function csrf_field(){
    global $container;
    return '
    <input type="hidden" name="'. $container->get('csrf')->getTokenNameKey() .'"value="'. $container->get('csrf')->getTokenName() .'">
	<input type="hidden" name="'. $container->get('csrf')->getTokenValueKey() .'"value="'. $container->get('csrf')->getTokenValue() .'">
    ';
}
function randomStr(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function lang($str){
    global $container;
    $data = $container->get('lang');
    return $data[$str];
}

function timeToDate($timestamp,$format){
    $date = new \DateTime();
    // $date = new \DateTime('now', new \DateTimeZone('Europe/Helsinki'));
    $date->setTimestamp($timestamp);
    return $date->format($format);
}

function downloadXlsx($spreadsheet,$filename){
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='. $filename);
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}

function num2alpha($n) {
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
        $n -= pow(26, $i);
    }
    return $r;
}

function checkAgain(){
    if (session('app_id') === envi('APP_KEY')){
        return true;
    }
    return  false;
}

/**
 * Get base dir
 * @param null $path
 * @return bool|false|string
 */
function base_path($path='') {
    if(DIRECTORY_SEPARATOR !== '/') {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }
    $search = explode('/', $path);
    $search = array_filter($search, function($part) {
        return $part !== '.';
    });
    $append = array();
    $match = false;
    while(count($search) > 0) {
        $match = realpath(implode('/', $search));
        if($match !== false) {
            break;
        }
        array_unshift($append, array_pop($search));
    };
    if($match === false) {
        $match = getcwd();
    }
    if(count($append) > 0) {
        $match .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $append);
    }
    return $match;
}
//Public path
function public_path($dir=null){
    if($dir){
        return base_path('public/'.$dir);
    }
    return base_path('public');
}
//Delete a File
function deleteFile($file)
{
    if (is_file($file))
        unlink($file);
}

function post($var)
{
    if (isset($_POST[$var])) {
        if (is_array($_POST[$var])) {
            return $_POST[$var];
        }
        return filter_var($_POST[$var], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}
//get
function get($var)
{
    if (isset($_GET[$var])) {
        if (is_array($_GET[$var])) {
            return $_GET[$var];
        }
        return filter_var($_GET[$var], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}

function empty_folder($folder)
{
    foreach ($folder as $folder) {
        if (is_file($folder))
            unlink($folder);
    }
}

function lowercase($table)
{
    return strtolower($table);
}

//Directory
function CreateDir($dirName, $rights = 0777)
{
    $dirs = explode('/', $dirName);
    $dir = '';
    foreach ($dirs as $part) {
        $dir .= $part . '/';
        if (!is_dir($dir) && strlen($dir) > 0)
            mkdir($dir, $rights);
    }
}

//Delete Dir
function RemoveDir($dir)
{
    if(is_dir($dir)) {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) RemoveDir($file);
            else unlink($file);
        }
        rmdir($dir);
    }
}

//dir
function app_dir($folder = NULL)
{
    $base = str_replace($folder, '', dirname(__FILE__));
    return str_replace('\\', '/', $base);
}

/*copy files*/
function BuildFiles($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function ExcludedTables()
{
    return "'users','users_confirmations','users_remembered','users_resets','users_throttling','hts_uploads'";
}
/*List tables*/
function listTables()
{
    $sql = 'SHOW TABLES';
    $query = pdo()->query($sql);
    return $query->fetchAll(PDO::FETCH_COLUMN);
}
/*Display table*/
function DatabaseTables()
{
    $sqlrows = pdo()->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '" . envi('DB_DATABASE') . "' AND TABLE_NAME NOT IN(" . ExcludedTables() . ") GROUP BY TABLE_NAME ");
    return $sqlrows->fetchAll(PDO::FETCH_OBJ);
}

/*Display table*/
function TablesCount()
{
    $sqlrows = pdo()->query("SELECT COUNT(column_name) AS mycolumns FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '" . envi('DB_DATABASE') . "' AND TABLE_NAME NOT IN(" . ExcludedTables() . ") ");
    return $sqlrows->fetch(PDO::FETCH_OBJ)->mycolumns;
}

/*Table Columns*/
function DataTableColumns($table)
{
    $sqlrows = pdo()->query("SHOW COLUMNS FROM " . $table . "");
    return $sqlrows->fetchAll(PDO::FETCH_OBJ);
}

/*Table Columns*/
function TablePrimaryKey($table)
{
    $sqlrows = pdo()->query("SHOW COLUMNS FROM " . $table . " WHERE `Key` = 'PRI'");
    return $sqlrows->fetch(PDO::FETCH_OBJ);
}

//File
function delete_files($folder)
{
    if (is_file($folder))
        unlink($folder);
}
// delete files except
function deleteWithException($dir,$except=['User.php']){
    foreach( glob("$dir/*") as $file ) {
        if( !in_array(basename($file), $except) )
            unlink($file);
    }
}

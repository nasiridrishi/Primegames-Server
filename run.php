<?php

declare(strict_types=1);

namespace this_build_script;

use function array_map;
use function count;
use function dirname;
use function fgets;
use function file_exists;
use function glob;
use function implode;
use function is_dir;
use function microtime;
use function mkdir;
use function passthru;
use function preg_quote;
use function realpath;
use function round;
use function rtrim;
use function sprintf;
use function strpos;
use function trim;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;
use const SCANDIR_SORT_NONE;
use const STDIN;

/**
 * @param string[]    $strings
 * @param string|null $delim
 *
 * @return string[]
 */
function preg_quote_array(array $strings, string $delim = null) : array{
    return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
}

/**
 * @param string   $pharPath
 * @param string   $basePath
 * @param string[] $includedPaths
 * @param array    $metadata
 * @param string   $stub
 * @param int      $signatureAlgo
 * @param int|null $compression
 */
function buildPhar(string $pharPath, string $basePath, array $includedPaths, int $signatureAlgo = \Phar::SHA1, ?int $compression = null){
    $basePath = realpath($basePath);
    if(file_exists($pharPath)){
        echo "Phar file already exists, overwriting...\n";
        try{
            \Phar::unlinkArchive($pharPath);
        }catch(\PharException $e){
            //unlinkArchive() doesn't like dodgy phars
            unlink($pharPath);
        }
    }
    @mkdir(dirname($pharPath));
    echo "Adding files...\n";

    $start = microtime(true);
    $phar = new \Phar($pharPath);
    $phar->setMetadata([]);
    $phar->setStub('<?php __HALT_COMPILER();');
    $phar->setSignatureAlgorithm($signatureAlgo);
    $phar->startBuffering();

    //If paths contain any of these, they will be excluded
    $excludedSubstrings = preg_quote_array([
        realpath($pharPath), //don't add the phar to itself
    ], '/');

    $folderPatterns = preg_quote_array([
        DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR . '.' //"Hidden" files, git dirs etc
    ], '/');

    //Only exclude these within the basedir, otherwise the project won't get built if it itself is in a directory that matches these patterns
    $basePattern = preg_quote(rtrim($basePath, DIRECTORY_SEPARATOR), '/');
    foreach($folderPatterns as $p){
        $excludedSubstrings[] = $basePattern . '.*' . $p;
    }

    $regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
        implode('|', $excludedSubstrings), //String may not contain any of these substrings
        preg_quote($basePath, '/'), //String must start with this path...
        implode('|', preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
    );

    $directory = new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_PATHNAME); //can't use fileinfo because of symlinks
    $iterator = new \RecursiveIteratorIterator($directory);
    $regexIterator = new \RegexIterator($iterator, $regex);

    $count = count($phar->buildFromIterator($regexIterator, $basePath));
    echo "Added $count files\n";

    if($compression !== null){
        echo "Checking for compressible files...\n";
        foreach($phar as $file => $finfo){
            /** @var \PharFileInfo $finfo */
            if($finfo->getSize() > (1024 * 512)){
                echo "Compressing " . $finfo->getFilename() . "\n";
                $finfo->compress($compression);
            }
        }
    }
    $phar->stopBuffering();

    echo "Built $pharPath in " . round(microtime(true) - $start, 3) . "s\n";
}

$pluginsDir = __DIR__ . '/servers/$plugin/plugins/';
if(!is_dir($pluginsDir) and !@mkdir($pluginsDir)){
    die("Failed to create directory $pluginsDir");
}
foreach(scandir($pluginsDir, SCANDIR_SORT_NONE) as $file){
    if(strpos($file, "DevTools") !== false or $file === "." or $file === ".." or strpos($file, "ignore") !== false){
        continue;
    }

    if(strpos($file, "PiggyCustomEnchants") !== false or $file === "." or $file === ".."){
        continue;
    }
    echo "Removing plugin file $file\n";
    unlink($pluginsDir . "/" . $file);
}
$plugin = "";
$validArgs = false;
if($argc === 1){
    do{
        echo "Enter plugin to deploy: ";
        $plugin = trim(fgets(STDIN));
    }while(!is_dir(__DIR__ . "/plugins/$plugin"));
    $validArgs = true;
}elseif($argc === 2){
    $plugin = $argv[1];
    if(is_dir(__DIR__ . "/plugins/$plugin")){
        $validArgs = true;
    }
}
if(!$validArgs){
    die("Usage: " . PHP_BINARY . " " . __FILE__ . " [servers/$plugin type]");
}

buildPhar(__DIR__ . '/servers/$plugin/plugins/Primer.phar', 'plugins/Primer', []);
buildPhar(__DIR__ . "/servers/$plugin/plugins/$plugin.phar", "plugins/$plugin", []);

const pmPaths = [
    __DIR__ . '/servers/$plugin/PocketMine-MP.phar',
    __DIR__ . '/servers/$plugin/src/pocketmine/PocketMine.php'
];

foreach(pmPaths as $p){
    if(file_exists($p)){
        passthru("\"" . PHP_BINARY . "\" \"$p\" --data=\"" . __DIR__ . "/servers/$plugin\" --plugins=\"" . __DIR__ . "/servers/$plugin/plugins\" --no-wizard --debug.level=2 --enable-ansi --spawn-protection=-1");
        die;
    }
}

die("PocketMine-MP entry point not found (tried " . implode(", ", pmPaths));

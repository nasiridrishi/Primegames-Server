<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

use function this_build_script\preg_quote_array;

const SITE = "http://primegames.net/NxiDUSvq/";
const TEMPBACKUPDIR = __DIR__ . "/.backup";
const BACKUPDIR = "/backupdrive";
const GAMEMODES = [
    "hub",
    "prison",
    "factions",
    "skyblock",
    "survival",
    "kitpvp",
    "creative"];

if(!isset($argv[1])){
    printUsage();
    exit();
}

if($argv[1] === "setup"){
    setupServer();
}

foreach(GAMEMODES as $GAMEMODE){
    if($argv[1] === $GAMEMODE){
        if($argv[2] === "start"){
            startServer($argv[1]);
            attatchScreen($argv[1]);
        }
    }
}

function printUsage(){
    writeln("php server.php {gamemode name} start|stop|updatepm|updatplug|updateworld|backup");
}

function setupServer(){
    mkdir(getcwd() . "/servers");
    foreach (GAMEMODES as $gamemode){
        getPMMPBinary();
        updatePMMP($gamemode);
        downloadWorlds($gamemode);
        updateRepo($gamemode);
        buildPluginPhar($gamemode);
    }
}

function updatePMMP(string $gamemode) {
    @mkdir(getcwd() . "/servers/$gamemode");
    if(!isset($opts["skip-pm"])) {
        doTask("Downloading PocketMine-MP.phar", static function() use ($gamemode) {
            if(!copy("https://jenkins.pmmp.io/job/PocketMine-MP/lastStableBuild/artifact/PocketMine-MP.phar", getcwd() . "/servers/$gamemode/PocketMine-MP.phar")) {
                throw new RuntimeException("Failed to download");
            }
        });
    }
}

function downloadWorlds(string $gamemode) {
    backupGamemode($gamemode);
    $serverworlds = [
        "factions" => [
            "factions",
            "factionswild"],
        "skyblock" => [
            "skyblock",
            "skyblockpvp"],
        "prison" => [
            "prison",
            "pvpmine",
            "aresmine",
            "irismine",
            "hadesmine",
            "poseidonmine",
            "zeusmine",
            "titanmine"],
        "survival" => ["survival"],
        //todo add survival wild worlds
        "creative" => ["creative"],
        "hub" => ["hub"],
        "kitpvp" => ["kitpvp"]];
    foreach(range("A", "Z") as $mineDesignator) {
        $serverworlds += ["prison" => "Mine$mineDesignator"];
    }

    foreach($serverworlds as $server => $worlds) {
        if($gamemode === $server) {
            $serverWorlds = $worlds;
        }
    }
    writeln(PHP_EOL . "> Setting up $gamemode worlds. This may take a few minutes ... ");
    @mkdir(getcwd() . "/servers/$gamemode/worlds");
    writeln("");
    $worldDir = getcwd() . "/servers/$gamemode/worlds/";
    foreach($serverWorlds as $world) {
        if(is_dir($worldDir . $world) and is_file($worldDir . $world . "level.dat")) {
            writeln("$world found in world $worldDir! skipping...");
            continue;
        }
        doTask("Downloading $world.zip", static function() use ($worldDir, $world) {
            if(!copy(SITE . "$world.zip", $worldDir . "$world.zip")) {
                throw new RuntimeException("Failed to download $world");
            }
            write("Unpacking archive ... ");
            $zip = new ZipArchive();
            $res = $zip->open($worldDir . "$world.zip");
            if($res === false) {
                throw new RuntimeException("Damaged or corrupted file $world.zip");
            }
            @mkdir($worldDir . $world);
            $zip->extractTo($worldDir);
            $zip->close();
            unlink($worldDir . "$world.zip");
        });
    }
}

function updateRepo(string $gamemode, bool $updateCore = true) {
    foreach(GAMEMODES as $plugin) {
        if($gamemode === strtolower($plugin)) {
            writeln("> Cloning plugin $plugin");
            @mkdir(__DIR__ . "/plugins");
            syncRepo("https://github.com/PrimeGamesDevTeam/$plugin.git", __DIR__ . "/plugins/$plugin");
            if($updateCore) {
                syncRepo("https://github.com/PrimeGamesDevTeam/Primer.git", __DIR__ . "/plugins/Primer");
            }
        }
    }
}

function backupGamemode(string $gamemode) {
    writeln("Starting $gamemode backup!");
    toggleAutoSave($gamemode);
    $date = date('Y-m-d H:i:s');
    $dirLocal = getcwd() . "servers/$gamemode";
    $gamemode_acive = TEMPBACKUPDIR . "/$date.$gamemode.tar.gz";
    $backupDIR = BACKUPDIR;
    echo exec("tar zcf $gamemode_acive $dirLocal");
    echo exec("mv $gamemode_acive $backupDIR");
    toggleAutoSave($gamemode);
    writeln("$gamemode succefully backed up");
}

function attatchScreen(string $gamemode){
    exec("screen -x $gamemode");
}

function toggleAutoSave(string $gamemode){
    exec("screen -S $gamemode -p 0 -X stuff 'sws^M'");
}

function startServer(string $gamemode){
    $workingDir = getcwd() . "/servers/$gamemode";
    $bin = getcwd() . "/bin/php7/bin/php";
    writeln("Starting $gamemode");
    exec("screen -S $gamemode -x select . ; echo $?");
    exec("screen -dmS $gamemode bash -c 'cd $workingDir; $bin PocketMine-MP.phar; exec bash'");
    writeln("$gamemode sucessfully started");
}

function stopServer(string $gamemode){
    writeln("Stopping $gamemode");
    exec("screen -S $gamemode -p 0 -X stuff 'stop^Msleep 5^Mexit^M'");
    writeln("$gamemode succesfully started");
}

function getPMMPBinary(){
    $dir = getcwd();
    if(file_exists(getcwd()."/bin")){
        return;
    }
    doTask("Downloading PMMP Binary", static function() {
        if(!file_exists(getcwd() . "/bin")){
            if(!copy("https://jenkins.pmmp.io/job/PHP-7.3-Aggregate/lastSuccessfulBuild/artifact/PHP-7.3-Linux-x86_64.tar.gz", getcwd() . "/bin.tar.gz")) {
                throw new RuntimeException("Failed to download");
            }
        }
    });
    exec("tar -xvzf $dir/bin.tar.gz");
}

function buildPluginPhar(string $gamemode){
    //@mkdir(getcwd()."/servers/$gamemode/plugins");
    $plugin = strtoupper($gamemode);
    buildPhar(getcwd(). "/servers/$gamemode/plugins/Primer.phar", 'plugins/Primer', []);
    //buildPhar(getcwd()." /servers/$gamemode/plugins/$plugin.phar", "plugins/$plugin", []);
}

function doTask(string $message, Closure $task): void {
    write("> " . $message . " ... ");
    try {
        $task();
    } catch(RuntimeException $e) {
        writeln("Error: " . $e->getMessage());
        die;
    }
    writeln("done!");
}

function write(string $line): void {
    echo $line;
}

function writeln(string $line): void {
    echo $line . PHP_EOL;
}

function syncRepo(string $addr, string $output): void {
    if(!is_dir($output)) {
        @unlink($output);
        doTask("Cloning git repository $addr into $output", static function() use ($addr, $output) {
            $result = 0;
            passthru("git clone --quiet $addr \"$output\"", $result);
            if($result !== 0) {
                throw new RuntimeException("Failed to clone repo");
            }
        });
    } else {
        doTask("Updating repository $output", static function() use ($output) {
            $result = 0;
            passthru("git -C \"$output\" fetch --quiet", $result);
            if($result !== 0) {
                throw new RuntimeException("Failed to fetch repository branches for repository $output");
            }
            passthru("git -C \"$output\" merge --quiet --ff-only FETCH_HEAD", $result);
            if($result !== 0) {
                throw new RuntimeException("Failed to integrate remote changes cleanly for repository $output, please update manually");
            }
        });
    }
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

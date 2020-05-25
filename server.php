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

const PMMP_BIN = __DIR__ . "/bin/php7/bin/php";
const SITE = "http://primegames.net/NxiDUSvq/";
const SERVERLOC = __DIR__ . "/servers";
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

$server = $argv[1];
$function = $argv[2];

foreach(GAMEMODES as $GAMEMODE){
    if($server === $GAMEMODE){
        if($function === "start"){
            startServer($server);
        }
    }
}

function printUsage(){
    writeln("gamemode start|stop|updatepm|updatplug|updateworld|backup");
}

function updatePMMP(string $gamemode) {
    backupGamemode($gamemode);
    @mkdir(SERVERLOC / $gamemode);
    if(!isset($opts["skip-pm"])) {
        doTask("Downloading PocketMine-MP.phar", static function() use ($gamemode) {
            if(!copy("https://jenkins.pmmp.io/job/PocketMine-MP/lastStableBuild/artifact/PocketMine-MP.phar", getcwd() . "/servers/$gamemode/PocketMine-MP.phar")) {
                throw new RuntimeException("Failed to download");
            }
        });
    }
}

function updateWorlds(string $gamemode) {
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
            $serverworlds = $worlds;
        }
    }

    writeln(PHP_EOL . "> Setting up $gamemode worlds. This may take a few minutes ... ");
    @mkdir(SERVERLOC / $gamemode);
    @mkdir(SERVERLOC . "/$gamemode/worlds");
    writeln("");
    $worldDir = getcwd() . "/servers/$gamemode/worlds/";
    foreach($serverworlds as $world) {
        if(is_dir($worldDir . $world)) {
            unlink($worldDir . $world);
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

function updatePlugin(string $gamemode) {
    backupGamemode($gamemode);
    writeln("> Cloning plugin repository $gamemode");
    @mkdir(__DIR__ . "/plugins");
    syncRepo("https://github.com/PrimeGamesDevTeam/$gamemode.git", __DIR__ . "/plugins/$gamemode");
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
    $dirLoca = SERVERLOC . "/$gamemode";
    $gamemode_arive = TEMPBACKUPDIR . "/$date.$gamemode.tar.gz";
    $backupDIR = BACKUPDIR;
    echo exec("tar zcf $gamemode_arive $dirLoca");
    echo exec("mv $gamemode_arive $backupDIR");
    toggleAutoSave($gamemode);
    writeln("$gamemode succefully backed up");
}

function toggleAutoSave(string $gamemode){
    exec("screen -S $gamemode -p 0 -X stuff 'sws^M'");
}

function startServer(string $gamemode){
    $workingDir = SERVERLOC . "/$gamemode";
    $bin = PMMP_BIN;
    writeln("Starting $gamemode");
    exec("screen -dmS screen_session_name bash -c 'cd $workingDir; $bin PocketMine.Phar; exec bash'");
    writeln("$gamemode sucessfully started");
}

function stopServer(string $gamemode){
    writeln("Stopping $gamemode");
    exec("screen -S $gamemode -p 0 -X stuff 'stop^Msleep 5^Mexit^M'");
    writeln("$gamemode succesfully started");
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

writeln("--- Setup Completed ---");

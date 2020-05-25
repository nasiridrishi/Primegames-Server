# PhpStorm-Project

This repository contains a pre-configured PhpStorm project which will help you get started and develop PrimeGames plugins.

## Pre-requisites
- [git](https://git-scm.com) installed and in your PATH
- PhpStorm installation with correctly configured git (after you install git, you need to make PhpStorm aware of it. Go to Settings -> Version Control -> Git and configure the "path to git executable" to wherever your git (or git.exe) is)

## Setting up the project
1. Download a prebuilt binary from the [PMMP Jenkins server](https://jenkins.pmmp.io/job/PHP-7.3-Aggregate) or the [PMMP Azure DevOps project](https://dev.azure.com/pocketmine/PHP-Builds/_build/latest?definitionId=2). Choose the appropriate package for your OS.
2. Extract the package somewhere (it doesn't matter where).
3. Download this project.
4. Open it with PhpStorm. You'll see that some things are already pre-configured for you.
5. Go to Settings -> Languages and Frameworks -> PHP, select the "PHP Runtime" tab and add the path to the PHP binary you downloaded (\<wherever you extracted\>/bin/php7/bin/php on Unix or \<wherever you extracted\>/bin/php/php.exe on Windows).
6. On the top right, you'll see a menu and a Play (Run) button. Select "Setup" in the menu and click Run. This will clone all of the PrimeGames plugin git repositories into the `plugins` directory of the project.

## Running servers with different configurations
Several pre-configured run configurations are provided to make it as easy as possible for you to run whichever server type you need. In PhpStorm, choose any of the "Run" options and click the Run button to get started.

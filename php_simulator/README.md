[![Dependency Status](https://www.versioneye.com/user/projects/54e2bcf38bd69f07ae00000e/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54e2bcf38bd69f07ae00000e)

## AI SIMULATOR

Simulate fight between Artificial Intelligence

# CONTENT

* **application/** Php class to simulate AIs
* **application/resources** List of spells that are used for the simulator 
* **config.ini** Choose the game config and where to find the 2 AIs
* **sample_bots** Contains 2 simple bots to test the simulator
* **main.php** Bootstrap code to start simulator

# USAGE

Edit the **config.ini** and specifiy the 2 AIs you want to be executed and
others options like the number of turns max for the game.

Run **php main.php** and get the json result of your fight.  Play with the
**quiet** mode in the configuration to get debug informations

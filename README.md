## Synopsis
Platform to create artifical intelligence and simulate them against each other.

## Details
Each AI is instantiated as a processus and receive/send actions using STDIN/STDOUT. The list of actions possible is detailed in an xml file (php\_simulator/application/resources).
The simulation is executed on turn bases and each process had 1 second per turn to output actions.

The fight simulation engine is currently written in php and one SDK to write an AI is available (also in php).

## Motivations
This project started in 2013 with the idea to provide a platform to let people learn algorithms and AI.
The project development did slow down in 2014 but I am preparing a version 2 to redesign the architecture.

## Usage

Each folders are independant between each other and all contain on piece of the application.
- First check in details the **SDK\_php** to understand better how to write and AI
- Then you can simulate them using the **php-simulator** and save the result in a json file
- A basic **js_viewer** is available and will show the detail of the fight based on the json output

## TODO
Deployment code for a web interface
Write new SDK (like Python, Php, C++, Ruby)


## Code Architectre Documentation

Coming soon...

[![Build Status](https://travis-ci.org/cmizony/ai_wars.svg)](https://travis-ci.org/cmizony/ai\_wars)
[![Coverage Status](https://coveralls.io/repos/cmizony/ai_wars/badge.svg?branch=master)](https://coveralls.io/r/cmizony/ai_wars?branch=master)

## Synopsis
Platform to create artifical intelligence and simulate them against each other.
Project started in 2013 to provide a platform to let people learn algorithms
and AI.


## Details
Each AI is instantiated as a processus and receive/send actions using
STDIN/STDOUT. The current list of actions possible is detailed [in this xml file](php\_simulator/src/resources/spell.xml).

The simulation is executed on turn bases and each process has 1 second per turn
to output actions.

The fight simulation engine is currently written in php and one AI SDK is
available (also in php).

## Usage

Each folders are independant between each other and all contain on piece of the
application.
- First check in details the **SDK\_php** to understand better how to write and
  AI
- Then you can simulate them using the **php-simulator** and save the result in
  a json file
- A **javascript viewer** is available and will show the detail of the fight
  based on the json output


## Documentation

TODO

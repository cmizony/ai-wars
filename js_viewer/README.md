------------------
# JS GAME VIEWER #
------------------

	VERSION 0.0.1

Game viewer is a simple JS library to a ai\_univers game with a less stylesheet


------------------------
REQUIREMENTS
------------------------
* less-1.3.3+


------------------------
INSTALLATION
------------------------
Depending on the needed this library can be integrate on any application like
CI. Requirements libs can be found on the official website or on "sample/libs/"

1. Copy every src/\*.js on the js folder of your application
2. Copy every src/\*.less on the css folder of your application

Note : It is possible to compile the less files server side and provide the
css results to have a static style sheet


------------------------
USAGE
------------------------
There is an example on sample/index.html to show how to use te library with a
couple of sample datas which can be used.

1. Link only viewer.js and viewer.less files on the render page of your application
2. Link a tag using the loadGame function and give the json game data
3. Remind that this lib use a global JS variable which is "game\_viewer"


------------------------
CHANGE LOG
------------------------


#!/bin/bash

# Location AI
php_cmd=`which php`
ai_params=' main.php'
main_file='main.php'
analyze_file='stdout_ai.txt'

# Test hash lib
md5_lib=`tar c libs | md5sum`
if [ "$md5_lib" != "de585891ec900df1a7bc82fd01ceb202  -" ]
then
	echo 'Warning: Content of the "libs/" folder is not the default one'
fi

if [ ! -e "$main_file" ]
then
	echo Error: Main file doesnt exist
	exit
fi

# Create AI with Mock data
echo Info: Run AI ... 
cat << MOCK_GAME | ${php_cmd}${ai_params} > $analyze_file
id 1
team 1
turns 3
turntime 1000
loadtime 3000
go
T 1
P 2 2 1000 1000
P 1 1 1000 1000
go
T 2
P 2 2 925 1000 D (101;5) CD (101;9)
P 1 1 925 1000 D (101;5) CD (101;9)
go
T 3
P 2 2 850 1000 D (101;4) CD (101;8)
P 1 1 850 1000 D (101;4) CD (101;8)
go
MOCK_GAME

# Analyse output AI
return_val=$?
if [ $return_val -ne 0 ]
then
	echo Error: in the AI source 
	rm $analyze_file
	exit
fi

turns=`grep "go" $analyze_file | wc -l`
if [ $turns -ne 4 ]
then
	echo Error: finish turn orders are missing
	rm $analyze_file
	exit
fi

count_lines=0
while read line  
do   
	if [[ $line =~ C\ ([0-9]+)\ ([0-9]+)\ ([0-9]+) ]] || [ $line = "go" ]
	then
		((count_lines ++))
	else
		echo Error: AI output not valide -> $line
		rm $analyze_file
		exit
	fi
done < $analyze_file

echo Info: AI output, $count_lines lines valid
echo Info: Analyse AI success
rm $analyze_file

The code in this repository was created during the `codingame.com` challenge called `Code4Life`.

##### Description on codingame.com
`Code4Life` is a special contest. 
Don't be surprised or deterred if it takes you longer than usual to have your first AI working; 
it's totally normal! You can view this first part as solving a medium puzzle. 
Then, it's a classic multiplayer league based game.

* --> [Code4Life Challenge on CodinGame](https://www.codingame.com/contests/code4life)
* --> [Code4Life Source on Github](https://github.com/CodinGame/Code4Life)

##### The Goal
Produce medicines and maximize your score by transporting items across a medical complex.

Rules:
* [Wood2 League](rules/wood2.md)
* [Wood1 League](rules/wood1.md)
* [Bronze League](rules/bronze.md)

##### Useful Stuff

###### Brutal tester
* [cg-brutaltester](https://github.com/dreignier/cg-brutaltester/)
* [C4L brutaltester referee](https://github.com/KevinBusse/cg-referee-code4life)

Execute like `java -jar cg-brutaltester-0.0.1.jar -r "java -jar referee.jar" -p1 "php index.php" -p2 "php index.php"`


###### Dump all PHP files into one
* Use `./build.sh` to dump all php files into one.
* `./build.sh | pbcopy` will dump everything and copy it to your clipboard from where you can isert it to the codingame.com web page

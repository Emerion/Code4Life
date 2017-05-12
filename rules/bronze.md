# League: Bronze

* Molecules are now in limited supply!
* In order to produce the more costly medicines, you will have to acquire Expertise.
* Production raises your expertise. Accumulating enough expertise in certain molecules will let you complete Science Projects and score extra points!
* Also, robots will take several turns to arrive at their destination, plan ahead!


## Changes
* Moving from one module to another takes a number of turns depending on the distance involved (see matrix below).
* Once movement has started, it cannot be interrupted. Any commands given by the player during the turns when the robot is mobile will be ignored.

### The laboratory module
Connecting to this module with CONNECT id where id is the identifier of a diagnosed sample data the player can research, will have several effects:
* The sample data id as well as the associated molecules are removed from play.
* The players scores as many points as the sample's health points.
* **NEW:** The player acquires molecule expertise: the robot will need 1 less molecule of the type specified by the sample for producing all subsequent medicines.


## Expert Rules
### Source code
The source code of the game is available on our Github at this address: https://github.com/CodinGame/Code4Life

### Concurrency
Concurrency
* In the event that both players try to take sample data from the cloud on the same turn, only the player who had previously diagnosed this sample will successfully complete the transfer.
* In the event that both players request the last molecule of a given type, the module will provide an extra molecule but will wait for at least 2 molecules of that type to be spent in the lab before providing new ones.

### Science projects
* In addition to scoring points by helping Roche create new medicine for untreated diseases, a player may also further apply medical science by completing Science projects.
* Each science project is worth 30 health points. It can be completed by either player.
* Each game starts out with 3 random active science projects. To complete one, players must gather the required amount of molecule expertise for each type (A,B,C,D & E).

### Robot Movement Matrix
|            | SAMPLES	| DIAGNOSIS	| MOLECULES	| LABORATORY |
|------------|----------|-----------|-----------|------------|
| Start area |        2 |         2 |         2 |          2 |
| SAMPLES    |        0 |         3 |         3 |          3 |
| DIAGNOSIS  |        3 |         0 |         3 |          4 |
| MOLECULES  |        3 |         3 |         0 |          3 |
| LABORATORY |        3 |         4 |         3 |          0 | 


## Game Input
### Initialization Input
#### Line 1:
`projectCount`: number of science projects

#### Next projectCount lines:
5 integers, the required amount of molecule expertise needed for each type.

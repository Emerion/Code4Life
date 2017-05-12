# League: Wood2

## Rules
A game is played with 2 players. Each player controls one robot.

The complex is composed of 3 modules named `DIAGNOSIS`, `MOLECULES` and `LABORATORY`. 
The robots can transfer two types of items from and to the modules: sample data files and molecules.

In a nutshell, you have to optimize your robot movements to:
Collect sample data files from the cloud at the `DIAGNOSIS` module.
Gather required molecules for the medicines at the `MOLECULES` module.
Produce the medicines at the `LABORATORY` module and collect your health points.


## Details
### The robots
* Each player has one robot. Both robots have the same starting position.
* A robot can carry up to `3 sample data files` and `10 molecules`.
* A player can move their robot from one module to another by means of the `GOTO module` command.
* Once the robot is at a module's interface, it can connect to it with the `CONNECT` command. This will have a different effect for each module.

### Sample data
* A sample data file is an item representing all known data on a tissue sample collected from an untreated patient. Researching this sample may ultimately lead to the production of medicine to prolong the lives of all patients with the same ailment.
* A sample data is associated with the list of molecules needed to produce the medicine for that sample.
* A sample data will grant you a variable number of health points when you research it.

### Molecules
* A molecule can be one of five types: `A`, `B`, `C`, `D` or `E`.


## Modules
### The diagnosis machine
Connecting to this module with CONNECT id will transfer the sample data file identified by `id` from the cloud of the diagnosis machine to your robot. 

### The molecule distribution module
Connecting to this module with `CONNECT type`, where `type` is one of the molecule types, will transfer an available molecule to your robot of the desired type.

### The laboratory module
* To use this module, the player's robot must be carrying a sample data file as well as the required amount of molecules for producing that sample's medicine.
* Connecting to this module with `CONNECT id` where `id` is the identifier of a diagnosed sample data the player can research, will have several effects:
* The sample data `id` as well as the associated molecules are removed from play.
* The players scores as many points as the sample's health points.
* The player acquires molecule expertise: the robot will need 1 less molecule of the type specified by the sample for producing all subsequent medicines.


## Victory Conditions
Your robot has scored the most health points after 200 turns or once a player has at least 170 points.

## Lose Conditions
You fail to provide valid output.


## Game Input
### Initialization input

#### Line 1:
always 0. Ignore for this league and do not remove for the next leagues.

### Input for one game turn
For each player, 1 line: 1 string followed by 12 integers (you are always the first player):
* `target`: module where the player is.
* `eta`: ignore for this league.
* `score`: the player's number of health points
* `storageA`, `storageB`, `storageC`, `storageD`, `storageE`: number of molecules held by the player for each molecule type.
* `expertiseA`, `expertiseB`, `expertiseC`, `expertiseD`, `expertiseE`: ignore this for this league.


#### Next line:
`availableA`, `availableB`, `availableC`, `availableD`, `availableE`: number of available molecules. Ignore for this league, there are always enough molecules available.

#### Next line:
`sampleCount`: the number of samples currently in the game.

#### Next sampleCount lines:
* `sampleId`: unique id for the sample.
* `carriedBy`: 0 if the sample is carried by you, 1 by the other robot, -1 if the sample is in the cloud.
* `rank`: ignore for this league.
* `gain`: ignore for this league.
* `health`: number of health points you gain from this sample.
* `costA`, `costB`, `costC`, `costD`, `costE`: number of molecules of each type needed to research the sample


## Output for one game turn
Each turn issue one of the following command:
* `GOTO module`: move towards the target module.
* `CONNECT id/type`: connect to the target module with the specified sample id or molecule type.

### Constraints
* Response time for first turn ≤ 1000ms 
* Response time for one turn ≤ 50ms

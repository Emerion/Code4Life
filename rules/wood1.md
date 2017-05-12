# League: Wood1

## Changes
The complex is composed of 4 modules named `SAMPLES`, `DIAGNOSIS`, `MOLECULES` and `LABORATORY`. 

### Sample data
* Sample data file can be in one of two states: `undiagnosed` (initial state) or `diagnosed`.
* A `diagnosed` sample data is associated with the list of molecules needed to produce the medicine for that sample.
Each sample data file has a `rank`: `1`, `2` or `3`. The higher the rank, the more health points you will get from the medicine - but more molecules will be needed to produce the medicine.

### The samples module
Connecting to this module with `CONNECT rank`, where `rank` is an integer between `1 and 3`, will transfer an `undiagnosed sample` data file of rank rank to your robot.

### The diagnosis machine
Connecting to this module with `CONNECT id`:
* where `id` is the identifier of an `undiagnosed sample` data file the player is carrying, will change the sample's state to diagnosed.
* where `id` is the identifier of a `diagnosed sample` data file the player is carrying, will transfer the sample data from the player to the cloud, where it will remain until a player takes it.
* where `id` is the identifier of a `diagnosed sample` data file stored in the cloud, will transfer the sample data from the cloud to the player.

### Concurrency
In the event that both players try to take sample data from the cloud on the same turn, only the player who had previously diagnosed this sample will successfully complete the transfer.

## Output for one game turn
* `WAIT`: do nothing.

## Constraints
* Health points scored with a rank 1 sample = 1 or 10
* Health points scored with a rank 2 sample = 10, 20 or 30
* Health points scored with a rank 3 sample = 30, 40 or 50
* 3≤ Total molecule cost for a rank 1 sample ≤5
* 5≤ Total molecule cost for a rank 2 sample ≤8
* 7≤ Total molecule cost for a rank 3 sample ≤14
* Response time for first turn ≤ 1000ms 
* Response time for one turn ≤ 50ms

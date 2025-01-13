### 0.0.6
##### Features:
- Added phpstan;
- Updated README;
- Added code coverage test.
##### BC Breaks:
- Completely removed all interfaces and imploded them into one `CollectionInterface`;
- Removed a lot of methods from collection and added a few methods instead;
- Changed namespace from `Temkaa\SimpleCollections` to `Temkaa\Collections` and `temkaa/simple-collections` to `temkaa/collections`
in composer respectively.

### 0.0.5
##### Fixes:
- Renamed `Greater`, `GreaterOrEqual`, `Less` and `LessOrEqual` to `GreaterThan`, `GreaterThanOrEqual`, `LessThan`
and `LessOrEqual` respectively.

### 0.0.4
##### Features:
- Added infection to project.

### v0.0.3
##### Fixes:
- Fixed some typos in interface names.

### v0.0.2
##### Features:
- Added [MakeableInterface](src/Collection/MakeableInterface.php).

### v0.0.1
##### Features:
- Base implementation.

# Change Log
All notable changes to this project will be documented in this file.
Updates should follow the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [Unreleased][unreleased]

This section, at a high level, the differences between the original library and the first release of this fork:

### Added

 - Added the ability to use `/`-delimited paths like `foo/bar` in addition to `foo.bar`
 - Added ability to access `Data` objects like arrays (thanks to `ArrayAccess`)
 - Added optional ability for `get()` to throw exceptions on missing paths
 - Added two new exception classes: `DataException` and `InvalidPathException`
 - Added better type annotations and purity markers (verified with Psalm)

### Changed

 - `get()` now throws an exception if the path is missing and not default is provided.  If you want `null` returned in these cases you must explicitly specify that via the `$default` parameter
 - Made some minor performance tweaks

[unreleased]: https://github.com/colinodell/dot-access-data/compare/1.0.0...master

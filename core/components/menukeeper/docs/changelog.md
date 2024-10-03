# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2024-10-03

### Changed

- Cache the menu permissions too when menukeeper.cache_permissions is enabled

### Fixed

- Fix an undefined variable $menuObject on install

## [1.0.3] - 2024-06-25

### Changed

- Disable package_installer_at_top system setting on install

## [1.0.2] - 2023-10-14

### Changed

- Better handling of undefined variables
- Reset a MODX configuration variable to the previous state to avoid too frequent clearing of the cache

## [1.0.1] - 2023-09-29

### Changed

- Insert the MenuKeeper entry as last entry in 'Manage' menu.

## [1.0.0] - 2022-12-29

### Added

- Initial release

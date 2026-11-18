MenuKeeper is active right after the installation. It keeps track of the
position of each menu entry and restores the positions after a package
installation. New menu entries added during a package installation are moved to
the end of their parent menu. That way only newly installed extras change the
manager menu.

## Automatic tracking

The positions (the parent and the menu index) of all menu entries are cached
in a permanent cache. The tracking starts with the first package installation
while MenuKeeper is installed. After that, the cache is updated when a manager
page is loaded, so changes made in the menu editor are tracked automatically
and don't have to be saved manually.

After a package installation, MenuKeeper:

1. restores the cached positions of the known menu entries,
2. moves the new menu entries to the end of their parent menu,
3. caches the resulting positions.

A cached menu entry that was removed in the meantime is skipped.

## Menu Entries

The extra adds a MenuKeeper menu with two entries to manually save and load a
snapshot of the menu entry positions. The menu is located in the `Manage` menu,
in MODX 3.1 and newer in the `Admin` (gear) menu. Using the entries requires the
`settings` permission.

| Entry | Description                                                                                                                                           |
|-------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
| Save  | Save a snapshot of the current menu entry positions.                                                                                                  |
| Load  | Restore the menu entry positions of the saved snapshot and reload the manager. The restored positions are also kept after the next package installation. |

The snapshot is independent of the automatic tracking, so loading a manager
page does not overwrite it. Use it to save a menu order before you try other
changes in the menu editor and to return to that order afterward.

## System Settings

MenuKeeper uses the following system settings in the namespace `menukeeper`:

| Key                                          | Name                        | Description                                                                                                                              | Default |
|----------------------------------------------|-----------------------------|------------------------------------------------------------------------------------------------------------------------------------------|---------|
| menukeeper.cache_permissions                 | Cache Permissions           | Cache the permissions of each menu entry.                                                                                                | No      |
| menukeeper.debug                             | Debug                       | Log debug information in the MODX error log.                                                                                             | No      |
| menukeeper.package_installer_at_top_original | Original Installer Position | The value of the package_installer_at_top system setting before MenuKeeper was installed. It is restored when MenuKeeper is uninstalled. | -       |

!!! note "Cache the permissions"

    If you enable `menukeeper.cache_permissions`, the permissions of the menu
    entries are restored after a package installation too. They are cached
    with the next manager page load. Save a new snapshot with `Save`, if you
    want to load the permissions with `Load`.

!!! note "Debug"

    If `menukeeper.debug` is enabled, MenuKeeper logs the cached, restored and
    sorted menu entries in the MODX error log.

## Changed MODX System Setting

The installation of MenuKeeper disables the MODX system setting
`package_installer_at_top`, so that the Installer menu entry keeps its cached
position instead of being pinned to the top of its menu. The previous value of
that system setting is saved in `menukeeper.package_installer_at_top_original`
and restored when MenuKeeper is uninstalled.

The extra caches the menu entry positions in a permanent cache. It restores the
positions of known menu entries after a package installation. It also moves new
menu entries added during a package installation to the bottom. That way only
new installed extras change the main menu. The permanent cache can be updated
with a menu entry in the manage menu.

## Menu Entries

The extra adds two menu entries in the manage menu to manually save and load the
menu entry positions. If you manually edit the menu, you have to save the menu
afterward with `Manage` -> `MenuKeeper` -> `Save`.

## System Settings

MenuKeeper uses the following system settings in the namespace `menukeeper`:

| Key                          | Name             | Description                                  | Default |
|------------------------------|------------------|----------------------------------------------|---------|
| menukeeper.cache_permissions | Cache Permission | Cache the permission of each menu entry.     | No      |
| menukeeper.debug             | Debug            | Log debug information in the MODX error log. | No      |

!!! caution "Save the menu after system setting change"

    If you enable `menukeeper.cache_permissions`, you have to save the menu
    afterward with `Manage` -> `MenuKeeper` -> `Save`. Otherwise, the menu
    permissions can be set to empty on an existing installation.

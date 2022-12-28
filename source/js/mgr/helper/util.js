MenuKeeper.util.saveMenu = function () {
    MODx.Ajax.request({
        url: MenuKeeper.config.connectorUrl,
        params: {
            action: 'mgr/savemenu',
        },
        listeners: {
            success: {
                fn: function (response) {
                    MODx.msg.alert(_('success'), response.message);
                },
                scope: this
            },
            failure: {
                fn: function (response) {
                    MODx.msg.alert(_('error'), response.message);
                },
                scope: this
            }
        }
    });
    return true;
}

MenuKeeper.util.loadMenu = function () {
    MODx.msg.confirm({
        title: _('menukeeper.menu_load'),
        text: _('menukeeper.menu_load_confirm'),
        url: MenuKeeper.config.connectorUrl,
        params: {
            action: 'mgr/loadmenu',
        },
        listeners: {
            success: {
                fn: function () {
                    window.location.reload();
                },
                scope: true
            },
            failure: {
                fn: function (response) {
                    MODx.msg.alert(_('error'), response.message);
                },
                scope: this
            }
        }
    });
    return true;
}

var menukeeper = function (config) {
    config = config || {};
    Ext.applyIf(config, {});
    menukeeper.superclass.constructor.call(this, config);
    return this;
};
Ext.extend(menukeeper, Ext.Component, {
    initComponent: function () {
    }, page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, util: {}, form: {}
});
Ext.reg('menukeeper', menukeeper);

MenuKeeper = new menukeeper();

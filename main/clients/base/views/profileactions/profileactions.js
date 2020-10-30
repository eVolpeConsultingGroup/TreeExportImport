({
    extendsFrom: 'ProfileactionsView',

    events: {
        'click .export-record-tree': 'exportRecordTree',
        'click .import-record-tree': 'importRecordTree'
    },

    render: function () {
        var view = this;
        if (!_.isUndefined(app.user.get('id')) && app.user.get('id') == '1') {
            var lbl = app.lang.getAppString('LNK_EXPORTIMPORTTREE_EXPORT') || "Export record tree";
            var exportRecordTree = {
                'route': 'javascript:void(0)',
                'label': lbl,
                'css_class': 'profileactions-employees export-record-tree',
                'acl_action': 'list',
                'icon': 'fa-reply',
                'submenu': ''
            };
            var lbl = app.lang.getAppString('LNK_EXPORTIMPORTTREE_IMPORT') || "Import record tree";
            var importRecordTree = {
                'route': 'javascript:void(0)',
                'label': lbl,
                'css_class': 'profileactions-employees import-record-tree',
                'acl_action': 'list',
                'icon': 'fa-reply',
                'submenu': ''
            };

            if (_.isUndefined(_.findWhere(view.meta, exportRecordTree))) {
                view.meta.push(exportRecordTree);
            }
            if (_.isUndefined(_.findWhere(view.meta, importRecordTree))) {
                view.meta.push(importRecordTree);
            }
            view._super('render');
        }
        this._super('render');
    },

    exportRecordTree: function () {
        var hash = document.location.hash.split('/');
        var module = '', record_id = '';
        if (hash[0] == 'bwc') {
            var p = hash[1].split("?")[1].split("&");
            p.forEach(function (e) {
                e = e.split("=");
                if (e[0] == 'module') {
                    module = e[1];
                }
                if (e[0] == 'record') {
                    record_id = e[1];
                }
            });
        }
        if (hash[0] != 'bwc' && hash[1] != 'create' && hash.length == 2) {
            module = hash[0].split("#")[1];
            record_id = hash[1];
        }
        if (module != '' && record_id != '') {
            var siteUrl = document.location.href.split("#");
            if (siteUrl.length == 2) {
                siteUrl = siteUrl[0] + "/";
                window.location.href = siteUrl + 'index.php?entryPoint=ExportImportTreeExport&export_module=' + module + '&id=' + record_id;
            }
            else {
                SUGAR.App.router.navigate('index.php?entryPoint=ExportImportTreeExport&export_module=' + module + '&id=' + record_id, { trigger: true, replace: true });
            }
        }
        else {
            app.alert.show('tree-export-error', { level: 'error', messages: 'Can not export this data ', autoClose: true });
        }
    },
    importRecordTree: function () {
        var siteUrl = document.location.href.split("#");
        if (siteUrl.length == 2) {
            siteUrl = siteUrl[0] + "/";
            window.location.href = siteUrl + 'index.php?entryPoint=ExportImportTreeImport'
        }
        else {
            SUGAR.App.router.navigate('index.php?entryPoint=ExportImportTreeImport', { trigger: false, replace: true });
        }
        return;
    }
})
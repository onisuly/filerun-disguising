FR.customActions.disguising = {
    run: function () {
        var item = FR.UI.gridPanel.getOneSel().data;
        this.fileName = item.filename;
        this.filePath = item.path ? item.path : FR.currentPath + '/' + item.filename;
        var title = '<?php echo \S::safeJS(self::t('Disguise "%1"', ['%1']));?>'.replace('%1', this.fileName);
        if (!this.prompt) {
            this.prompt = new Ext.Window({
                title: title,
                layout: 'form', width: 380, height: 180,
                closable: false, closeAction: 'hide', resizable: false,
                bodyStyle: 'padding:10px;background-color:white',
                labelAlign: 'right', labelWidth: 20,
                items: [
                    {
                        xtype: 'checkbox',
                        fieldLabel: '',
                        boxLabel: '<?php echo \S::safeJS(self::t("Delete the source file"));?>'
                    }
                ],
                buttonAlign: 'left',
                buttons: [{
                    text: FR.T('Ok'), cls: 'fr-btn-primary',
                    handler: function () {
                        this.doAction();
                    }, scope: this
                }, {
                    text: FR.T('Cancel'), style: 'margin-left:15px',
                    handler: function () {
                        this.prompt.hide();
                    }, scope: this
                }]
            });
        } else {
            this.prompt.setTitle(title);
        }
        this.prompt.show();
    },
    doAction: function () {
        var deleteSrc = FR.customActions.disguising.prompt.items.get(0).getValue();
        var pars = {path: this.filePath, deleteSrc: deleteSrc};
        var url = FR.baseURL + '/?module=custom_actions&action=disguising&method=run';
        FR.UI.showLoading('<?php echo \S::safeJS(self::t("Processing file..."));?>');
        Ext.Ajax.request({
            url: url,
            method: 'post',
            params: pars,
            callback: function (opts, succ, req) {
                FR.UI.doneLoading();
                try {
                    var rs = Ext.util.JSON.decode(req.responseText);
                } catch (er) {
                    return false;
                }
                if (rs.success) {
                    FR.utils.reloadGrid();
                }
                FR.UI.feedback(rs.msg);
                this.prompt.hide();
            }, scope: this
        });
    }
}

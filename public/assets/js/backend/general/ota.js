define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/ota/index' + location.search,
                    add_url: 'general/ota/add',
                    edit_url: 'general/ota/edit',
                    del_url: 'general/ota/del',
                    multi_url: 'general/ota/multi',
                    import_url: 'general/ota/import',
                    table: 'ota',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'otaname', title: __('Otaname'), operate: 'LIKE'},
                        {field: 'otatype', title: __('Otatype'), operate: 'LIKE'},
                        {field: 'otastatus', title: __('Otastatus'), formatter: Table.api.formatter.status,searchList: {show: __('Show'), close: __('Close')}},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});

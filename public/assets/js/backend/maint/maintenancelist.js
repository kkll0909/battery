define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'maint/maintenancelist/index' + location.search,
                    // add_url: 'maint/maintenancelist/add',
                    // edit_url: 'maint/maintenancelist/edit',
                    // del_url: 'maint/maintenancelist/del',
                    // multi_url: 'maint/maintenancelist/multi',
                    // import_url: 'maint/maintenancelist/import',
                    table: 'maintenancelist',
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
                        {field: 'wxuser_id', title: __('Wxuser_id')},
                        {field: 'maintid', title: __('Maintid')},
                        {field: 'wxtime', title: __('Wxtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'wxstatus', title: __('Wxstatus'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'wxdesc', title: __('Wxdesc'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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

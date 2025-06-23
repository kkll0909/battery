define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'batmanage/belong/index' + location.search,
                    // add_url: 'batmanage/belong/add',
                    // edit_url: 'batmanage/belong/edit',
                    // del_url: 'batmanage/belong/del',
                    // multi_url: 'batmanage/belong/multi',
                    // import_url: 'batmanage/belong/import',
                    table: 'belong',
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
                        // {field: 'batid', title: __('Batid')},
                        // {field: 'belongid', title: __('Belongid')},
                        {field: 'nickname', title: __('Nickname'),operate:false},
                        {field: 'belongtype', title: __('Belongtype'), searchList: {"user":__('User'),"manage":__('Manage')}, formatter: Table.api.formatter.status},
                        {field: 'isuse', title: __('Isuse'), searchList: {"self":__('Self'),"sale":__('Sale'),"lease":__('Lease'),"allocation":__('Allocation')}, formatter: Table.api.formatter.status},
                        {field: 'status', title: __('Status'), searchList: {"show":__('Show'),"close":__('Close')}, formatter: Table.api.formatter.status},
                        {field: 'stime', title: __('Stime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'etime', title: __('Etime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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

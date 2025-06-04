define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'orders/cgordersub/index' + location.search,
                    add_url: 'orders/cgordersub/add',
                    edit_url: 'orders/cgordersub/edit',
                    del_url: 'orders/cgordersub/del',
                    multi_url: 'orders/cgordersub/multi',
                    import_url: 'orders/cgordersub/import',
                    table: 'cgordersub',
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
                        {field: 'oid', title: __('Oid')},
                        {field: 'batid', title: __('Batid')},
                        {field: 'batno', title: __('Batno'), operate: 'LIKE'},
                        {field: 'sum', title: __('Sum')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
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

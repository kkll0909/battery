define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/realauth/index' + location.search,
                    // add_url: 'user/realauth/add',
                    // edit_url: 'user/realauth/edit',
                    // del_url: 'user/realauth/del',
                    // multi_url: 'user/realauth/multi',
                    // import_url: 'user/realauth/import',
                    table: 'realauth',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'idcardz', title: __('Idcardz'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'idcardf', title: __('Idcardf'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'idcard', title: __('Idcard'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'realname', title: __('Realname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'status', title: __('Status'), searchList: {"1":__('Pass'),'2':__('Fail')}, formatter: Table.api.formatter.status},
                        {field: 'reaon', title: __('Reaon'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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

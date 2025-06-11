define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'department/withdraw/index' + location.search,
                    add_url: 'department/withdraw/add',
                    edit_url: 'department/withdraw/edit',
                    del_url: 'department/withdraw/del',
                    multi_url: 'department/withdraw/multi',
                    import_url: 'department/withdraw/import',
                    table: 'withdraw',
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
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'bankname', title: __('Bankname'), operate: 'LIKE'},
                        {field: 'bankno', title: __('Bankno'), operate: 'LIKE'},
                        {field: 'bankckr', title: __('Bankckr'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"100":__('Status 100')}, formatter: Table.api.formatter.status},
                        {field: 'note', title: __('Note'), operate: 'LIKE'},
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

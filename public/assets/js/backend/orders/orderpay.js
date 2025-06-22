define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'orders/orderpay/index' + location.search,
                    // add_url: 'orders/orderpay/add',
                    // edit_url: 'orders/orderpay/edit',
                    // del_url: 'orders/orderpay/del',
                    // multi_url: 'orders/orderpay/multi',
                    // import_url: 'orders/orderpay/import',
                    table: 'orderpay',
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
                        {field: 'isy', title: __('Isy'), searchList: {"0":__('Deposit'),"1":__('Stages')}, formatter: Table.api.formatter.status},
                        {field: 'paymoney', title: __('Paymoney'), operate:'BETWEEN'},
                        {field: 'paysum', title: __('Paysum')},
                        {field: 'paydate', title: __('Paydate'), operate: 'LIKE'},
                        {field: 'paystatus', title: __('Paystatus'), searchList: {"nopay":__('Nopay'),"pay":__('Pay')}, formatter: Table.api.formatter.status},
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

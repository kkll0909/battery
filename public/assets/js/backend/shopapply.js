define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shopapply/index' + location.search,
                    // add_url: 'shopapply/add',
                    edit_url: 'shopapply/edit',
                    // del_url: 'shopapply/del',
                    // multi_url: 'shopapply/multi',
                    // import_url: 'shopapply/import',
                    table: 'shopapply',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'yyzzimg', title: __('Yyzzimg'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'zlhtimg', title: __('Zlhtimg'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'cqzimg', title: __('Cqzimg'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'cdimg', title: __('Cdimg'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'jyimg', title: __('Jyimg'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'idcardz', title: __('Idcardz'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'idcardf', title: __('Idcardf'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'shopname', title: __('Shopname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'jyname', title: __('Jyname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'idcard', title: __('Idcard'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'realname', title: __('Realname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'qytype', title: __('Qytype'), searchList: {"1":__('企业'),"2":__('个体')}, formatter: Table.api.formatter.status},
                        {field: 'usetype', title: __('Usetype'), searchList: {"1":__('自有'),"2":__('租用')}, formatter: Table.api.formatter.status},
                        {field: 'status', title: __('Status'), searchList: {"apply":__('Apply'),"success":__('Success'),"fail":__('Fail')}, formatter: Table.api.formatter.status},
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

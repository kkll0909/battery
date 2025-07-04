define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            const urlParams = new URLSearchParams(location.search);
            // console.log(urlParams);
            // console.log(location.search);
            var shopid = urlParams.get('shopid');
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/shopservice/index' + location.search,
                    add_url: 'shop/shopservice/add?shopid='+shopid,
                    edit_url: 'shop/shopservice/edit?shopid='+shopid,
                    del_url: 'shop/shopservice/del',
                    multi_url: 'shop/shopservice/multi',
                    import_url: 'shop/shopservice/import',
                    table: 'shopservice',
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
                        {field: 'shopid', title: __('Shopid')},
                        {field: 'title', title: __('Title'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'status', title: __('Status'), searchList: {"show":__('Show'),"close":__('Close')}, formatter: Table.api.formatter.status},
                        {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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

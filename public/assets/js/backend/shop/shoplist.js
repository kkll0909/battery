define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            const urlParams = new URLSearchParams(location.search);
            // console.log(urlParams);
            // console.log(location.search);
            var shopid = urlParams.get('shopid');
            console.log(shopid);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/shoplist/index' + location.search,
                    add_url: 'shop/shoplist/add?shopid='+shopid,
                    edit_url: 'shop/shoplist/edit',
                    del_url: 'shop/shoplist/del',
                    multi_url: 'shop/shoplist/multi',
                    import_url: 'shop/shoplist/import',
                    table: 'shoplist',
                    // ...其他配置
                    // queryParams: function(params) {
                    //     if (shopid) params.shopid = shopid;
                    //     return params;
                    // }
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
                        {field: 'sbname', title: __('Sbname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'sbtype', title: __('Sbtype'), operate: 'LIKE'},
                        {field: 'money', title: __('Money'), operate:'BETWEEN'},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1')}, formatter: Table.api.formatter.status},
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

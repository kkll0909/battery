define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/shop/index' + location.search,
                    add_url: 'shop/shop/add',
                    edit_url: 'shop/shop/edit',
                    del_url: 'shop/shop/del',
                    multi_url: 'shop/shop/multi',
                    import_url: 'shop/shop/import',
                    table: 'shop',
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
                        {field: 'admin.nickname', title: __('Admin_id'),operate: false},
                        {field: 'spimgs', title: __('Spimgs'),operate: false,formatter: Table.api.formatter.image},
                        {field: 'spname', title: __('Spname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'isopen', title: __('Isopen'),searchList: {"1":__('Opening'),"2":__('Closing')}, formatter: Table.api.formatter.status},
                        {field: 'tag', title: __('Tag'), operate: false, formatter: Table.api.formatter.flag},
                        {field: 'status', title: __('Status'), searchList: {"show":__('Show'),"close":__('Close')}, formatter: Table.api.formatter.status},
                        {field: 'splng', title: __('Splng'), operate: false},
                        {field: 'splat', title: __('Splat'), operate: false},
                        {field: 'spaddr', title: __('Spaddr'), operate: 'LIKE'},
                        {field: 'spaddrbc', title: __('Spaddrbc'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {name:'shoplist',text:'商品',title:'商品明细',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'shop/shoplist/index?shopid={id}'},
                            ],
                            formatter: Table.api.formatter.operate}
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

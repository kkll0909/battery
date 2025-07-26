define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    
    //设置弹窗宽高
    Fast.config.openArea = ['70%', '85%'];
    
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'miniprogram/news/index' + location.search,
                    add_url: 'miniprogram/news/add',
                    edit_url: 'miniprogram/news/edit',
                    del_url: 'miniprogram/news/del',
                    multi_url: 'miniprogram/news/multi',
                    table: 'miniprogram_news',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'pic', title: __('Pic'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, operate: false, formatter: Table.api.formatter.toggle, yes: 'normal', no: 'hidden'},
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
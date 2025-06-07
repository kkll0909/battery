define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'otalog/index' + location.search,
                    //add_url: 'otalog/add',
                    //edit_url: 'otalog/edit',
                    del_url: 'otalog/del',
                    multi_url: 'otalog/multi',
                    import_url: 'otalog/import',
                    table: 'otalog',
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
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'type', title: __('Type'), operate: false},
                        {field: 'sbno', title: __('Sbno'), operate: false},
                        {field: 'messagetype', title: __('Messagetype'), operate: false},
                        {field: 'raw', title: __('Raw'), operate: false},
                        {field: 'stime', title: __('Stime'), operate: false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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

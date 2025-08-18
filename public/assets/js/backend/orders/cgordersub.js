define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            const urlParams = new URLSearchParams(location.search);
            // console.log(urlParams);
            // console.log(location.search);
            var cgid = urlParams.get('cgid');
            Table.api.init({
                extend: {
                    index_url: 'orders/cgordersub/index' + location.search,
                    add_url: 'orders/cgordersub/add?cgid='+cgid,
                    edit_url: 'orders/cgordersub/edit?cgid='+cgid,
                    // del_url: 'orders/cgordersub/del',
                    // multi_url: 'orders/cgordersub/multi',
                    // import_url: 'orders/cgordersub/import',
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
                        {field: 'realname', title: __('Realname')},
                        {field: 'batno', title: __('Batno'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {name:'unbind',text:'解绑',title:'解绑',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-ajax',url:'orders/cgordersub/munbind'},
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

define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'maint/maintenance/index' + location.search,
                    // add_url: 'maint/maintenance/add',
                    // edit_url: 'maint/maintenance/edit',
                    // del_url: 'maint/maintenance/del',
                    // multi_url: 'maint/maintenance/multi',
                    // import_url: 'maint/maintenance/import',
                    table: 'maintenance',
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
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'batid', title: __('Batid')},
                        {field: 'orderno', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'bxtype', title: __('Bxtype'), operate: 'LIKE'},
                        {field: 'bxdesc', title: __('Bxdesc'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'bxtime', title: __('Bxtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'bxstatus', title: __('Bxstatus'), searchList:{'wxup' : __('Wxup'), 'wxjd' : __('Wxjd'), 'wxing' : __('Wxing'), 'wxzd' : __('Wxzd'), 'wxwc' : __('Wxwc')} ,formatter: Table.api.formatter.status},
                        {field: 'wxuser_id', title: __('Wxuser_id')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {name:'maintlist',text:'维修记录',title:'维修记录',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'maint/maintenancelist/index?bxid={id}'},
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

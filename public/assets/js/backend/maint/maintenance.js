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
                        {field: 'batno', title: __('Batno')},
                        {field: 'orderno', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'bxtype', title: __('Bxtype'), searchList: {'sbok' : __('Sbok'), 'sbno' : __('Sbno'), 'sbnoc' : __('Sbnoc')},formatter: Table.api.formatter.status},
                        {field: 'bxdesc', title: __('Bxdesc'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'bxtime', title: __('Bxtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'isok', title: __('Isok'), searchList:{'0' : __('Wait'), '1' : __('Ok'), '2' : __('Fail')} ,formatter: Table.api.formatter.status},
                        {field: 'bxstatus', title: __('Bxstatus'), searchList:{'wxup' : __('Wxup'), 'wxjd' : __('Wxjd'), 'wxing' : __('Wxing'), 'wxzd' : __('Wxzd'), 'wxwc' : __('Wxwc')} ,formatter: Table.api.formatter.status},
                        {field: 'user.nickname', title: __('Wxuser')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {name:'maintok',text:'商家确认',title:'商家确认',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'user/user/memok?bxid={id}',visible: function (row) {
                                        // 假设 row.status 是状态字段，1 表示已确认或不可操作
                                        var statusIsOk = row.isok != 1;  // 状态不为 1 才显示

                                        // 判断是否为超级管理员（FastAdmin 默认规则：admin_id == 1）
                                        //var isSuperAdmin = Config.admin_id == 1;
                                        // var isSuperAdmin = 0;

                                        // 只有非超级管理员 && 状态不为 1 时才显示
                                        return statusIsOk;
                                    }},
                                {name:'maintlist',text:'维修记录',title:'维修记录',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'maint/maintenancelist/index?bxid={id}'},
                                {name:'maintuser',text:'维修派单',title:'维修派单',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'user/user/maintuser?bxid={id}',visible: function (row) {
                                        // 假设 row.status 是状态字段，1 表示已确认或不可操作
                                        var statusIsOk = row.isok == 1;  // 状态不为 1 才显示

                                        // 判断是否为超级管理员（FastAdmin 默认规则：admin_id == 1）
                                        // var isSuperAdmin = Config.admin_id == 1;

                                        // 只有超级管理员 && 状态不为 1 时才显示
                                        return  statusIsOk;
                                    }},
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

define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'orders/cgorders/index' + location.search,
                    // add_url: 'orders/cgorders/add',
                    // edit_url: 'orders/cgorders/edit',
                    // del_url: 'orders/cgorders/del',
                    // multi_url: 'orders/cgorders/multi',
                    // import_url: 'orders/cgorders/import',
                    table: 'cgorders',
                }
            });

            var table = $("#table");
            table.on('load-success.bs.table', function (e,data) {
                $("#totallbs").text(data.extend.totallbs);
                $("#totalbbs").text(data.extend.totalbbs);
            });

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
                        {field: 'orderno', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'fromadmin.nickname', title: __('Fromname'), operate: 'LIKE'},
                        {field: 'touser.nickname', title: __('Toname'), operate: 'LIKE'},
                        {field: 'realname', title: __('Realname'),operate: false},
                        {field: 'monay', title: __('Monay'), operate:'BETWEEN'},//m,j,n,a
                        {field: 'yjpaym', title: __('yjpaym'),operate: false},
                        {field: 'qs', title: __('Qs'),operate: false},
                        {field: 'likebatsum', title: __('Likebatsum'),operate: false},
                        {field: 'bindbatsum', title: __('Bindbatsum'),operate: false},
                        {field: 'sum', title: __('Sum'),operate: false},
                        {field: 'payway', title: __('Payway'),searchList: {"multiple":__('Multiple'),"single":__('Single')}, formatter: Table.api.formatter.status},
                        {field: 'type', title: __('Type'), searchList: {"buy":__('Buy'),"zp":__('Zp')}, formatter: Table.api.formatter.status},
                        {field: 'paytype', title: __('Paytype'), searchList: {"m":__('M'),"j":__('J'),"n":__('N'),"a":__('A')}, formatter: Table.api.formatter.status},
                        {field: 'stime', title: __('Stime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'etime', title: __('Etime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"nopay":__('Nopay'),"pay":__('Pay')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {name:'ordersub',text:'关联设备',title:'关联设备',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'orders/cgordersub/index?cgid={id}'},
                                {name:'orderpay',text:'分期',title:'分期明细',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'orders/orderpay/index?cgid={id}'},
                                {name:'addr',text:'地址',title:'地址',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'orders/cgorderaddr/index?cgid={id}'},
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

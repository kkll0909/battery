define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'batmanage/bat/index' + location.search,
                    add_url: 'batmanage/bat/add',
                    edit_url: 'batmanage/bat/edit',
                    del_url: 'batmanage/bat/del',
                    multi_url: 'batmanage/bat/multi',
                    import_url: 'batmanage/bat/import',
                    table: 'bat',
                }
            });

            var table = $("#table");
            table.on('load-success.bs.table', function (e,data) {
                $("#totalbs").text(data.extend.totalbs);
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
                        {field: 'admin.username', title: __('Admin_id'), operate: false},
                        {field: 'factory.faname', title: __('Csname'), operate: false},
                        {field: 'csid', title: __('Csid')},
                        {field: 'brand', title: __('Brand'), operate: 'LIKE'},
                        {field: 'batno', title: __('Batno'), operate: 'LIKE'},
                        {field: 'remainingcapacity', title: __('Remainingcapacity'), operate: false},
                        {field: 'soc', title: __('Soc'), operate: false},
                        {field: 'soh', title: __('Soh'), operate: false},
                        {field: 'battype', title: __('Battype'), operate: 'LIKE', searchList: {"1" : __('Ternary'),"2":__('Lithiumiron'),"3":__('Lithiumtitanium')}, formatter: Table.api.formatter.status},
                        {field: 'cyclelife', title: __('Cyclelife'), operate: false},
                        {field: 'balance', title: __('Balance'), operate: 'LIKE', searchList: {'0' : __('Proscribe'),'1':__('Enabled')}, formatter: Table.api.formatter.status},
                        {field: 'chargedischargeswitch', title: __('Chargedischargeswitch'), operate: 'LIKE', searchList: {'0' : __('Proscribe'),'1':__('Enabled'),'2':__('Nocharging'),'3':__('Nodischarging')}, formatter: Table.api.formatter.status},
                        {field: 'mosstatus', title: __('Mosstatus'), operate: 'LIKE', searchList: {'0' : __('Idle'),'1':__('Charging'),'2':__('Discharging')}, formatter: Table.api.formatter.status},
                        {field: 'voltage', title: __('Voltage'), operate: 'LIKE'},
                        {field: 'capacity', title: __('Capacity'), operate: 'LIKE'},
                        {field: 'ambienttemperature', title: __('Ambienttemperature'), operate: false},
                        {field: 'celltemperature', title: __('Celltemperature'), operate: false},
                        {field: 'boardtemperature', title: __('Boardtemperature'), operate: false},
                        // {field: 'status', title: __('Status'), operate: 'LIKE', searchList: {"show":__('Show'),"Close":__('close')}, formatter: Table.api.formatter.status},
                        {field: 'islike', title: __('Islike'), operate: false, searchList: {"auto":__('Auto'),"noauto":__('Noauto')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons:[
                                {name:'otalog',text:'日志',title:'OTA明细',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'Otalog/index?batid={id}'},
                                {name:'otaopen',text:'打开充放电',title:'打开充放电',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-ajax',url:'batmanage/bat/sendcf?deviceid={batno}&status=1',confirm: '确认要执行打开充放电吗？'},
                                {name:'otaclosecd',text:'关闭充电',title:'关闭充电',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-ajax',url:'batmanage/bat/sendcf?deviceid={batno}&status=3',confirm: '确认要执行关闭充电吗？'},
                                {name:'otaclosefd',text:'关闭放电',title:'关闭放电',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-ajax',url:'batmanage/bat/sendcf?deviceid={batno}&status=4',confirm: '确认要执行关闭放电吗？'},
                                {name:'towho',text:'所属',title:'所属关系',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'batmanage/belong/index?batid={id}'},
                                {name:'map',text:'地图',title:'地图',icon:'fa fa-list',classname:'btn btn-xs btn-primary btn-dialog',url:'batmanage/bat/map?batid={id}'},
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

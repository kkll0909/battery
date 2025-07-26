define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    //设置弹窗宽高
    Fast.config.openArea = ['70%', '85%'];
    
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'miniprogram/template/index' + location.search,
                    add_url: 'miniprogram/template/add',
                    edit_url: 'miniprogram/template/edit',
                    del_url: 'miniprogram/template/del',
                    multi_url: 'miniprogram/template/multi',
                    table: 'miniprogram_template',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'tempkey', title: __('Tempkey')},
                        {field: 'name', title: __('Name')},
                        {field: 'tempid', title: __('Tempid')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Normal'),"1":__('Hidden')}, operate: false, formatter: Table.api.formatter.toggle, yes: '1', no: '0'},
                        {field: 'add_time', title: __('Add_time'),sortable: true ,operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            // 推送示例
            $(document).on("click", ".btn-demo", function () {
                var _html = "<pre class='layui-code'>" +
                    "\/\/以下字段key要与模板内容中相对应，需要用户订阅后才能推送\r\n" +
                    "$data = array(\r\n" +
                    "\t'order_sn' => '传入order_sn，如果有{order_sn}变量则会自动替换为传入的值',\r\n" +
                    "\t'nickname' => '.....',\r\n" +
                    ");\r\n" +
                    "\$res = \\addons\\miniprogram\\library\\WechatTemplate::send(\r\n" +
                    "\t'pay_success',\/\/场景值\r\n" +
                    "\t'o-LXrv3whDgMsHZ3sTn5AmXRwO9Y',\/\/用户openid\r\n" +
                    "\t$data\r\n" +
                    ");\r\n" +
                    "if (true !== $res) {\r\n" +
                    "\t//$this->error($res);\r\n" +
                    "}" +
                    "</pre>";
                Layer.open({
                    type: 1
                    ,title: '模板消息推送PHP示例' //不显示标题栏
                    ,closeBtn: false
                    ,area: '600px;'
                    ,shade: 0.8
                    ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
                    ,btn: ['确定', '取消']
                    ,btnAlign: 'c'
                    ,moveType: 1 //拖拽模式，0或者1
                    ,content: _html
                });
            });
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
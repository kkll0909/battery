define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'miniprogram/user/index' + location.search,
                    table: 'miniprogram_user',
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
                        {field: 'user_id', title: __('user_id')},
                        {field: 'user_type', title: __('User_type')},
                        {field: 'openid', title: __('Openid')},
                        {field: 'unionid', title: __('Unionid'), formatter: function(value){
                            return value ? value : '-'
                        }},
                        {field: 'fauser.nickname', title: __('Nickname')},
                        {field: 'fauser.avatar', title: __('Headimgurl'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
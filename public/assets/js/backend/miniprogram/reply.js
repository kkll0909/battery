define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    //设置弹窗宽高
    Fast.config.openArea = ['70%', '85%'];

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'miniprogram/reply/index' + location.search,
                    add_url: 'miniprogram/reply/add',
                    edit_url: 'miniprogram/reply/edit',
                    del_url: 'miniprogram/reply/del',
                    multi_url: 'miniprogram/reply/multi',
                    table: 'miniprogram_reply',
                }
            });

            var table = $("#table");

            //修改快速搜索显示文字
            $.fn.bootstrapTable.locales[Table.defaults.locale]['formatSearch'] = function(){return "输入ID快速搜索"};

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false, sortable: true},
                        {field: 'keyword', title: __('Keyword'), operate: 'LIKE', formatter: function(val){
                            if (val == 'default' || val == 'subscribe') {
                                val = '<span style="color: red">' + __(val) + '</span>';
                            } else {
                                val = Table.api.formatter.label(val)
                            }
                            return val;
                        }},
                        {field: 'matching_type', title: __('Matching_type'), searchList: {"1":__('Matching_type 1'),"2":__('Matching_type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'reply_type', title: __('Reply_type'), searchList: {"text":__('Reply_type text'),"image":__('Reply_type image'),"link":__('Reply_type link'),"miniprogrampage":__('Reply_type miniprogrampage')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, operate: false, formatter: Table.api.formatter.toggle, yes: 'normal', no: 'hidden'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime, sortable: true},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime, sortable: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            //隐藏tab切换的搜索框
            $('select[name="reply_type"]').parents('.form-group').hide();

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

                $(document).ready(function(){
                    $('.form-horizontal').removeClass('hide');
                });

                //显示emoji表情
                $(document).on('click', '.icon_emotion', function () {
                    $('.weui-desktop-popover__inner').removeClass('hide');
                });
                //隐藏emoji表情
                $(document).bind("click", function(e) {
                    var target = $(e.target);
                    if (target.closest(".icon_emotion").length == 0 && target.closest(".weui-desktop-popover__inner").length == 0) {
                        $('.weui-desktop-popover__inner').addClass('hide');
                    }
                })
                
                //选择emoji表情
                $(document).on('click', '.emotions_item', function () {
                    var textFeildValue = $(this).data('content');
                    var textObj = $('#c-content-text')[0];
                    if (textObj.setSelectionRange) {
                        var rangeStart = textObj.selectionStart;
                        var rangeEnd = textObj.selectionEnd;
                        var tempStr1 = textObj.value.substring(0, rangeStart);
                        var tempStr2 = textObj.value.substring(rangeEnd);
                        textObj.value = tempStr1 + textFeildValue + tempStr2;
                        textObj.focus();
                        textObj.setSelectionRange((tempStr1 + textFeildValue).length, (tempStr1 + textFeildValue).length);
                    } else {
                        alert("此版本的Mozilla浏览器不支持setSelectionRange");
                    }
                });

                //插入URL
                $(document).on('click', '.icon_url', function () {
                    var that = this;
                    Layer.prompt({
                        title: '插入链接',
                        formType: 0,
                    }, function (value, index, elem){
                        let textFeildValue = '<a href="' + value + '">' + value + '</a>';
                        var textObj = $('#c-content-text')[0];
                        if (textObj.setSelectionRange) {
                            var rangeStart = textObj.selectionStart;
                            var rangeEnd = textObj.selectionEnd;
                            var tempStr1 = textObj.value.substring(0, rangeStart);
                            var tempStr2 = textObj.value.substring(rangeEnd);
                            textObj.value = tempStr1 + textFeildValue + tempStr2;
                            textObj.focus();
                            textObj.setSelectionRange((tempStr1 + textFeildValue).length, (tempStr1 + textFeildValue).length);
                        } else {
                            textObj.value += textFeildValue
                        }
                        Layer.close(index);
                    })
                });

                //插入电话
                $(document).on('click', '.icon_phone', function () {
                    var that = this;
                    Layer.prompt({
                        title: '插入电话',
                        formType: 0
                    }, function (value, index, elem){
                        let textFeildValue = '<a href="tel:' + value + '">' + value + '</a>';
                        var textObj = $('#c-content-text')[0];
                        if (textObj.setSelectionRange) {
                            var rangeStart = textObj.selectionStart;
                            var rangeEnd = textObj.selectionEnd;
                            var tempStr1 = textObj.value.substring(0, rangeStart);
                            var tempStr2 = textObj.value.substring(rangeEnd);
                            textObj.value = tempStr1 + textFeildValue + tempStr2;
                            textObj.focus();
                            textObj.setSelectionRange((tempStr1 + textFeildValue).length, (tempStr1 + textFeildValue).length);
                        } else {
                            textObj.value += textFeildValue
                        }
                        Layer.close(index);
                    })
                });
            }

        }
    };
    return Controller;
});
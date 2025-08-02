define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            const urlParams = new URLSearchParams(location.search);
            // console.log(urlParams);
            // console.log(location.search);
            var shopid = urlParams.get('shopid');
            //console.log(shopid);
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/shoplist/index' + location.search,
                    add_url: 'shop/shoplist/add?shopid='+shopid,
                    edit_url: 'shop/shoplist/edit?shopid='+shopid,
                    del_url: 'shop/shoplist/del',
                    multi_url: 'shop/shoplist/multi',
                    import_url: 'shop/shoplist/import',
                    table: 'shoplist',
                    // ...其他配置
                    // queryParams: function(params) {
                    //     if (shopid) params.shopid = shopid;
                    //     return params;
                    // }
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
                        // {field: 'shopid', title: __('Shopid')},
                        {field: 'sbimg', title: __('Sbimg'),operate: false,formatter: Table.api.formatter.image},
                        {field: 'sbname', title: __('Sbname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'sbgg', title: __('Sbgg'), operate: 'LIKE'},
                        {field: 'sbtype', title: __('Sbtype'), searchList: {"zp":__('Zp'),"buy":__('Buy')}, formatter: Table.api.formatter.status},
                        {field: 'paytype', title: __('Paytype'), searchList: {"m":__('M'),"j":__('J'),"n":__('N')}, formatter: Table.api.formatter.status},
                        {field: 'buymoney', title: __('Buymoney'), operate:'BETWEEN'},
                        {field: 'zpmoney', title: __('Zpmoney'), operate:'BETWEEN'},
                        {field: 'deposit', title: __('Deposit'), operate:'BETWEEN'},
                        {field: 'jzk', title: __('jzk'), operate:'BETWEEN'},
                        {field: 'nzk', title: __('Nzk'), operate:'BETWEEN'},
                        {field: 'usetype', title: __('Usetype'), searchList: {"payuse":__('Payuse'),"usepay":__('Usepay')}, formatter: Table.api.formatter.status},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Publish'),"0":__('Unpublish')}, formatter: Table.api.formatter.status},
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
                
                // Handle Zpmoney and Buymoney visibility
                $('input[name="row[sbtype][]"]').on('change', function() {
                    var zpChecked = $('#row\\[sbtype\\]-zp').is(':checked');
                    var buyChecked = $('#row\\[sbtype\\]-buy').is(':checked');
                    
                    $('.zpmoney-group').toggle(zpChecked);
                    $('.usetype-group').toggle(zpChecked);
                    $('.deposit-group').toggle(zpChecked);
                    $('.paytype-group').toggle(zpChecked);
                    $('.jzk-group').toggle(zpChecked);
                    $('.nzk-group').toggle(zpChecked);
                    $('.buymoney-group').toggle(buyChecked);
                });
                
                // Trigger change event on page load
                $('input[name="row[sbtype][]"]').trigger('change');
            }
        }
    };
    return Controller;
});

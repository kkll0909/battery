define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {
    var Controller = {
        index: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));

                // 复制路径
                $(document).on("click", ".btn-copy", function () {
                    let name = $(this).data('copy-name');
                    Controller.api.copy($('#c-' + name).val());
                });

                // 随机生成字符串
                $(document).on("click", ".btn-rand", function () {
                    let name = $(this).data('name');
                    $('#c-' + name).val(Controller.api.randomString($(this).data('len')));
                });
            },
            copy: function (val, msg) {
                var oInput = document.createElement('input');
                oInput.value = val;
                document.body.appendChild(oInput);
                oInput.select(); // 选择对象
                document.execCommand("Copy"); // 执行浏览器复制命令
                oInput.className = 'oInput';
                oInput.style.display='none';
                if (msg) {
                    Layer.msg('<b>' + msg + '</b><br/>' + val);
                } else {
                    Layer.msg('<b>拷贝成功，请使用Ctrl+V粘贴。</b><br/>' + val);
                }
            },
            randomString: function(length) {
                let result = '';
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                const charactersLength = characters.length;

                for (let i = 0; i < length; i++) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                Controller.api.copy(result, '已随机生成并拷贝成功，请使用Ctrl+V粘贴。');
                return result;
            }
        }
    };
    return Controller;
});
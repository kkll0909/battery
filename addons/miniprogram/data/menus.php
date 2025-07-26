<?php


return [
    [
        'name'    => 'miniprogram',
        'title'   => '微信小程序管理',
        'icon'    => 'fa fa-comments',
        'sublist' => [
            [
                'name'    => 'miniprogram/config',
                'title'   => '应用配置',
                'remark'  => '填写微信小程序开发配置，请前往微信公众平台申请小程序并完成认证，请使用已认证的微信小程序。',
                'extend'  => 'padding-left: 15px;',
                'icon'    => 'fa fa-angle-double-right',
                'sublist' => [
                    ['name' => 'miniprogram/config/index', 'title' => '查看'],
                    ['name' => 'miniprogram/config/edit',  'title' => '编辑']
                ]
            ],
            [
                'name'    => 'miniprogram/template',
                'title'   => '订阅消息',
                'remark'  => '订阅消息仅用于向用户发送重要的服务通知，只能用于符合其要求的服务场景中，如信用卡刷卡通知，商品购买成功通知等。且需要用户主动订阅后方可推送。',
                'extend'  => 'padding-left: 15px;',
                'icon'    => 'fa fa-angle-double-right',
                'sublist' => [
                    ['name' => 'miniprogram/template/index', 'title' => '查看'],
                    ['name' => 'miniprogram/template/add',   'title' => '添加'],
                    ['name' => 'miniprogram/template/edit',  'title' => '编辑'],
                    ['name' => 'miniprogram/template/multi', 'title' => '批量更新'],
                    ['name' => 'miniprogram/template/del',   'title' => '删除']
                ]
            ],
            [
                'name'    => 'miniprogram/reply',
                'title'   => '回复管理',
                'remark'  => '这里的回复是在微信小程序在线客服对话框中实现关键词匹配并自动回复。支持：文本、图片、图文和卡片消息回复。',
                'extend'  => 'padding-left: 15px;',
                'icon'    => 'fa fa-angle-double-right',
                'sublist' => [
                    ['name' => 'miniprogram/reply/index', 'title' => '查看'],
                    ['name' => 'miniprogram/reply/add', 'title' => '新增'],
                    ['name' => 'miniprogram/reply/edit', 'title' => '编辑'],
                    ['name' => 'miniprogram/reply/del', 'title' => '删除'],
                    ['name' => 'miniprogram/reply/multi', 'title' => '批量更新']
                ]
            ],
            [
                'name'    => 'miniprogram/news',
                'title'   => '图文消息',
                'remark'  => '图文消息可以通过匹配关键词的方式发送到用户端的在线客服对话框中显示。',
                'extend'  => 'padding-left: 15px;',
                'icon'    => 'fa fa-angle-double-right',
                'sublist' => [
                    ['name' => 'miniprogram/news/index', 'title' => '查看'],
                    ['name' => 'miniprogram/news/add', 'title' => '新增'],
                    ['name' => 'miniprogram/news/edit', 'title' => '编辑'],
                    ['name' => 'miniprogram/news/del', 'title' => '删除'],
                    ['name' => 'miniprogram/news/multi', 'title' => '批量更新']
                ]
            ],
            [
                'name'    => 'miniprogram/user',
                'title'   => '微信用户',
                'remark'  => '该微信用户数据列表是用户在微信端通过登录接口主动授权登录后获得。',
                'extend'  => 'padding-left: 15px;',
                'icon'    => 'fa fa-angle-double-right',
                'ismenu'  => 1,
                'sublist' => [
                    ['name' => 'miniprogram/user/index', 'title' => '查看'],
                ]
            ],
        ]
    ]
];
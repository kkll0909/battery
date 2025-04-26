<?php

return array(

    array(
        'name'    => 'accessKeyId',
        'title'   => 'accessKeyId',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'accessKeySecret',
        'title'   => 'accessKeySecret',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'bucket',
        'title'   => 'Bucket',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => 'yourbucket',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '阿里云OSS的空间名',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'endpoint',
        'title'   => 'EndPoint',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => 'oss-cn-shenzhen.aliyuncs.com',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '如果是服务器中转模式，可填写内网域名，前面不可加http://',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'cdnurl',
        'title'   => 'CDN地址',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => 'http://yourbucket.oss-cn-shenzhen.aliyuncs.com',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '请填写CDN地址，必须以http://开头',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'uploadmode',
        'title'   => '上传模式',
        'type'    => 'select',
        'content' =>
            array(
                'client' => '客户端直传(速度快,无备份)',
                'server' => '服务器中转(占用服务器带宽,有备份)',
            ),
        'value'   => 'server',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'savekey',
        'title'   => '保存文件名',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'expire',
        'title'   => '上传有效时长',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '600',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'maxsize',
        'title'   => '最大可上传',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '10M',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),

    array(
        'name'    => 'mimetype',
        'title'   => '可上传后缀格式',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
    array(
        'name'    => 'multiple',
        'title'   => '多文件上传',
        'type'    => 'bool',
        'content' =>
            array(),
        'value'   => '0',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
    array(
        'name'    => 'thumbstyle',
        'title'   => '缩略图样式',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '用于附件管理缩略图样式，可使用：?x-oss-process=image/resize,m_lfit,w_120,h_90',
        'ok'      => '',
        'extend'  => '',
    ),
    array(
        'name'    => 'chunking',
        'title'   => '分片上传',
        'type'    => 'radio',
        'content' =>
            array(
                '1' => '开启',
                '0' => '关闭',
            ),
        'value'   => '1',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
    array(
        'name'    => 'chunksize',
        'title'   => '分片大小',
        'type'    => 'number',
        'content' =>
            array(),
        'value'   => '4194304',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
    array(
        'name'    => 'syncdelete',
        'title'   => '附件删除时是否同步删除文件',
        'type'    => 'bool',
        'content' =>
            array(),
        'value'   => '0',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
    array(
        'name'    => '__tips__',
        'title'   => '温馨提示',
        'type'    => '',
        'content' =>
            array(),
        'value'   => '1、在使用之前请注册阿里云账号并进行认证和创建空间，注册链接:<a href="https://oss.console.aliyun.com/index" target="_blank">https://oss.console.aliyun.com/index</a><br>
2、如需开启分片上传，必须给对应的按钮添加上<code>data-chunking="true"</code>',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ),
);

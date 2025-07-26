<?php

namespace app\admin\controller\miniprogram;
use app\common\controller\Backend;
use addons\miniprogram\library\ConfigService;

/**
 * 系统配置
 */
class Config extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        //内容过滤
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 查看
     */
    public function index()
    {
        $domainName = request()->domain();
        $config = ConfigService::get('miniprogram');
        $config = [
            'name'                  => $config['name'] ?? '',
            'original_id'           => $config['original_id'] ?? '',
            'qr_code'               => $config['qr_code'] ?? '',
            'app_id'                => $config['app_id'] ?? '',
            'app_secret'            => $config['app_secret'] ?? '',
            'url'                   => $domainName . '/addons/miniprogram/index/wechat',
            'token'                 => $config['token'] ?? '',
            'encoding_aes_key'      => $config['encoding_aes_key'] ?? '',
            'encryption_type'       => $config['encryption_type'] ?? '1',
            'request_domain'        => str_replace(request()->scheme(), 'https', $domainName),
            'socket_domain'         => str_replace(request()->scheme(), 'wss', $domainName),
            'upload_file_domain'    => str_replace(request()->scheme(), 'https', $domainName),
            'download_file_domain'  => str_replace(request()->scheme(), 'https', $domainName),
            'udp_domain'            => str_replace(request()->scheme(), 'udp', $domainName),
            'business_domain'       => $domainName,
        ];
        $this->view->assign('mpconfig', $config);
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $params = $this->request->post('row/a');
        ConfigService::set('miniprogram','name', $params['name'] ?? '');
        ConfigService::set('miniprogram','original_id',$params['original_id'] ?? '');
        ConfigService::set('miniprogram','qr_code',$params['qr_code'] ?? '');
        ConfigService::set('miniprogram','app_id',$params['app_id']);
        ConfigService::set('miniprogram','app_secret',$params['app_secret']);
        ConfigService::set('miniprogram','token',$params['token'] ?? '');
        ConfigService::set('miniprogram','encoding_aes_key',$params['encoding_aes_key'] ?? '');
        ConfigService::set('miniprogram','encryption_type',$params['encryption_type']);
        $this->success('操作成功');
    }
}

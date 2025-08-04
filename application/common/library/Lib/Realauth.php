<?php

namespace app\common\library\Lib;

use think\Log;

class Realauth
{
    public static function realauth($name,$idcard)
    {
        $name = urlencode($name);
        $host = "https://kzidcardv1.market.alicloudapi.com";
        $path = "/api/id_card/check";
        $method = "GET";
        $appcode = "8f8cec9712dd45cdba95307e08ad429a";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "name={$name}&idcard={$idcard}";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $response = curl_exec($curl);
        curl_close($curl);
        $httpResponse = explode("\r\n\r\n", $response, 2);
        $headers = $httpResponse[0];
        $body = $httpResponse[1];
        // 解析JSON
        $data = json_decode($body, true);
        if($data['code']!=200){
            return false;
        }else{
            return $data['data'];
        }
    }

    public static function idcardocr($img_path)
    {
        $url = "https://cardnumber.market.alicloudapi.com/rest/160601/ocr/ocr_idcard.json";
        $appcode = "8f8cec9712dd45cdba95307e08ad429a";
        //$img_path = "图片本地路径或者url";
        $method = "POST";

        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");

        //如果没有configure字段，config设为空
        $config = array(
            "side" => "face"
        );

        //$img_data = img_base64($img_path);
        $img_data = "";
        if (substr($img_path, 0, strlen("http")) === "http") {
            $img_data = $img_path;
        } else {
            if ($fp = fopen($img_path, "rb", 0)) {
                $binary = fread($fp, filesize($img_path)); // 文件读取
                fclose($fp);
                $img_data = base64_encode($binary); // 转码
            }
        }

        $request = array(
            "image" => "$img_data"
        );
        if (count($config) > 0) {
            $request["configure"] = json_encode($config);
        }
        $body = json_encode($request);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode == 200) {
            $result_str = $rbody;
            //printf("result is :\n %s\n", $result_str);
            return is_array($rbody)?:json_decode($rbody,true);
        } else {
//            printf("Http error code: %d\n", $httpCode);
//            printf("Error msg in body: %s\n", $rbody);
//            printf("header: %s\n", $rheader);
            return false;
        }

    }
}
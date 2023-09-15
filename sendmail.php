<?php

/**
 * @description: 模拟post请求
 * @param string $url 链接
 * @param array $data 请求数据
 * @param array $headerArray 请求头
 * @return array 返回数组
 */
function posturl($url, $data, $headerArray = array('Content-type:application/json;charset="utf-8"', "Accept:application/json"))
{
    $data  = json_encode($data);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return json_decode($output, true);
}


var_dump(posturl('http://209.141.39.54:19656/mail_sys/send_mail_http.json', ['mail_from' => 'noply@wmsgk.com']));

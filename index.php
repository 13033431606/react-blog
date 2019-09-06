<?php
//设置运行不超时
set_time_limit(0);

//设定字符类型
header('Content-Type: text/html; charset=utf-8');

include './simple_html_dom.php';

//获取目标路径页面资源
function getResource($url, $https= false, $params= false, $ispost= false)
{
    $httpInfo = array();
    //初始化curl会话
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }

    //获取页面数据
    $response = curl_exec($ch);
    if ($response === FALSE) {
        return false;
    }

    //请求状态
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));

    //关闭curl会话
    curl_close($ch);

    return $response;
}

//下载图片文件
function downloadImg($url, $path= 'uploads/', $https= false, $suffix= false){
    //获取资源
    $file= getResource($url, $https);

    //设置文件名称
    $filename = substr(pathinfo($url, PATHINFO_BASENAME),0,strpos(pathinfo($url, PATHINFO_BASENAME),"?"));
    if($suffix){
        $filename=$filename.$suffix;
    }
    $resource = fopen($path . $filename, 'a');

    //写入文件
    fwrite($resource, $file);
    fclose($resource);
}

////******** https://www.pexels.com/zh-tw/网站抓取(开始)  ********////
//获取资源
$result = getResource('https://www.pexels.com/zh-tw/', true);
//处理返回的html数据(simple_html_dom)
$html = str_get_html($result);
//定义资源数组
$data;
//将匹配的资源集组组成数组(simple_html_dom)
foreach($html->find('.photo-item__img') as $key => $element){
    $data[$key]['src']=substr($element->src,0, strpos($element->src,"?"));
    $data[$key]['alt']=$element->alt;
}
//参数
$state="?auto=compress&cs=tinysrgb&w=500";
//下载前10张图片
$x=0;
while ($x<=10){
    downloadImg($data[$x]['src'].$state, "imgs/", true);
    $x++;
}
////******** https://www.pexels.com/zh-tw/网站抓取(结束)  ********////



////******** https://unsplash.com/网站抓取(开始)  ********////
//将str资源转为json格式
$data_2= json_decode(getResource("https://unsplash.com/napi/photos?page=4&per_page=12",true));
//遍历下载
foreach($data_2 as $key => $value){
    downloadImg($value->urls->small, "imgs/", true, ".jpg");
}
////******** https://unsplash.com/网站抓取(结束)  ********////

?>
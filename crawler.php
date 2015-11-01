<?php
header("Content-Type: text/html;charset=utf-8");


##############
# 链接数据库 #
##############
class MyDB extends SQLite3
{
	function __construct()
	{
		$this->open('crawler.db');
	}
}
$db = new MyDB();



#############################
#   获取指定url的html内容   #
#  指定url-->html内容string #
#############################
function _curl_get_file_contents($url) 
{ 
$c = curl_init(); 
$url = trim($url);
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($c, CURLOPT_BINARYTRANSFER, 1);
curl_setopt($c, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;http://www.baidu.com)'); 
curl_setopt($c, CURLOPT_URL, $url); 
$contents = curl_exec($c); 
curl_close($c); 
if ($contents) 
{
	$encode = mb_detect_encoding($contents, array("ASCII", "utf-8","gb2312","GBK","BIG5")); 
 	$contents = mb_convert_encoding($contents, "utf-8", $encode);
	return $contents;
} 
else {return FALSE;} 
} 



###############################
#      从html中筛选出链接     #
#html内容string-->url列表array#
###############################
function _filterUrl($content)
{
	$flag = preg_match_all('/<[a|A].*?href=[\'\"]?([^>\'\"\ ]*).*?>/i', $content, $match_result);
	if($flag)
	{
		foreach ($match_result[1] as $url_item) 
		{
			$url_item = trim($url_item);
		}
		return $match_result[1];
	}
	else
		return false;
}



#################################
#       从html中提取标题        #
#html内容string-->网页标题string#
#################################
function _getTitle($content)
{
	$flag = preg_match_all('/<title>(.*)<\/title>/i', $content, $match_result);
	if($flag)
		return $match_result[1][0];
	else
		return false;
}



################################################
#                修正相对路径                  #
#基础url+url列表array -->修正完成的url列表array#
################################################
function _reviseUrl($base_url, $url_list)
{
    if(is_array($url_list))
    {
        foreach ($url_list as $url_item) 
        {
           if (preg_match('/^http/', $url_item))
            {
                $result[] = $url_item;
            } 
            else
           {
             	$real_url = $base_url . '/' . $url_item;
             	$result[] = $real_url;
           } 
        } 
        return $result;
    }
    return FALSE;
}


#####################################
#  从html中提取meta标签里的Keyword  #
#  html内容string-->keyword string  #
#####################################

function _getKey($content)
{
	$str = '/< *meta *name=\"keywords\" * content=\"(.*)\">/i';
	$flag = preg_match_all($str, $content, $match_result);
	if($flag)
		return $match_result[1][0];
	else
		return false;
}



#################################################
#           将网页信息保存进数据库中            #
# id url 内容 --> id url 标题 关键字 进入数据库 # 
#################################################
function _save($id, $url, $content)
{
	$title = _getTitle($content);
	$keyword = _getKey($content);
	#没想到好的存内容的方法
	$str = <<<Eof
      INSERT INTO WEBPAGE (ID,URL,TITLE,KEYWORDS)
      VALUES ($id, "$url", "$title", "$keyword" );
Eof;
	global $db;
	$db->exec($str);
}


########
# 爬虫 #
########
function crawler($id, $url) 
{
    $content = _curl_get_file_contents($url);
    _save($id, $url, $content);
    if ($content) 
    {
        $url_list = _reviseUrl($url, _filterUrl($content));
        if ($url_list) 
            return $url_list;
        else 
        	return ;
    } 
    else
        return ;
} 


########################
#  习惯性写了一个main  #
########################
function main()
{
	unlink("url.txt");
    $current_url = 'http://www.baidu.com';
    $fp_puts = fopen("url.txt", "a");
    $fp_gets = fopen("url.txt", "r");
    $id = 1;
    do 
    {
    	$current_url = trim($current_url);
        $result_url_arr = crawler($id, $current_url);
        if ($result_url_arr) 
        {
            foreach ($result_url_arr as $url) 
            {
                fputs($fp_puts, $url . "\r\n");
            } 
        } 
        $id++;

    } while ($current_url = fgets($fp_gets, 1024));
}
main();
$db->close();

?>
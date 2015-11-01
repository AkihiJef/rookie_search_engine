<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
function _getScore($words, $webpage)
{
    $score = 0;
    $lenth = mb_strlen($words, 'utf-8');
    for($i = 0; $i <$lenth; $i++)
    {
        $single_word = mb_substr($words, $i, 1, 'utf-8');
        $single_word = '/' . $single_word . '/';
        $score += 0.05 * (preg_match_all($single_word, $webpage['TITLE'], $re));
        $score += 0.05 * (preg_match_all($single_word, $webpage['KEYWORDS'], $re));
    }
    $words = '/' . $words . '/';
    $score += 1.2 * (preg_match_all($words, $webpage['TITLE'], $re));
    $score += 1.2 * (preg_match_all($words, $webpage['KEYWORDS'], $re));
    /*$url_info = parse_url($webpage['URL']);
    if($score) 
    {
        if(!$url_info['path']) $score += 1;
        else
            $score -= 0.1 * strlen($url_info['path']);
    }*/
    if($score >= 0.65) return $score;
    return FALSE;
}
    $words = $_POST['word'];
    //$words = '/' . $words . '/';
    class MyDB extends SQLite3
    {
       function __construct()
       {
          $this->open('crawler.db');
       }
    }
    $db = new MyDB();
    $sql =<<<EOF
      SELECT * from WEBPAGE;
EOF;
    $ret = $db->query($sql);
    $url = array ();
    while($row = $ret->fetchArray(SQLITE3_ASSOC) )
    {
        $flag = _getScore($words, $row);
        if($flag)
        {
            $score[] = $flag;
            $url[] = $row['URL'];
            $title[] = $row['TITLE'];
        }
    	/*if(preg_match($words, $row['TITLE']) || preg_match($words, $row['KEYWORDS']))
    	{
        echo '<a href="' . $row['URL'] . '">' . $row['TITLE'] . '</a><br />';
     	}*/
 	}
    
    if(count($url))
    {
        array_multisort($score, SORT_DESC, $url, SORT_ASC, $title, SORT_ASC);
        echo '共' . count($url) . '个结果：<br />';
        for($i = 0; $i < count($url); $i++)
        {
            $str = '<a href="' . $url[$i]. '">' . $title[$i] . '</a><br />';
            echo $str;
        }
        echo '完成!';
    }
    else
        echo '没有结果！';
    $db->close();
?>

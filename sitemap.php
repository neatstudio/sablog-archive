<?php


if( !$_GET['sitemap'] || $_GET['sitemap'] != -1 ){
	exit('action denied');
}
define('GOOGLE_SITEMAP' , 'google.xml');
define('BAIDU_SITEMAP' , 'baidu.xml');
define('PERPAGE_NUM' , 30);
require_once('./include/common.php');
//确定URL规则
$url = array(
    'site' => $options['url'],
    'link' => $options['url'] . "show-%d-1.shtml",
	'host' => parse_url($options['url'],PHP_URL_HOST)
);
$siteUrl = $options['url'];

$sql = "SELECT * FROM {$db_prefix}articles WHERE visible = 1 AND readpassword = '' ORDER BY articleid DESC LIMIT 0," . PERPAGE_NUM  ;
$query = $DB->query( $sql );
while( $rs = $DB->fetch_array( $query )){
	$result[] = $rs;
}

if ( $result ){
    $fileList = array(
        'google' => $utl['host'] . '.google.xml',
        'baidu'  => $utl['host'] . '.baidu.xml'
    );
	echo "<pre>";
	print_r( $fileList );
	echo "</pre>";
    SiteMap::setFile( $fileList );
    SiteMap::write( $result , $url );
	echo "update successed";
}


class SiteMap 
{
    static protected $datas;
    static protected $siteFiles;
    static protected $support = array('google','baidu');
    static public function setFile ( $files )
    {
        if(!is_array($files)){
            exit('sitemap filename must be array,like this:array("google"=>"google.xml")');
        }
        foreach ( $files as $_k => $_v ){
            $_k = strtolower($_k);
            if( in_array($_k,self::$support ) && is_string( $_v )){
                self::$siteFiles[$_k] = $_v;
            }
        }
    }

    static public function write ( $data , $url )
    {
        if( empty( self::$siteFiles )){
            exit('sitemap file does not set');
        }
        foreach ( self::$siteFiles as $_k => $_v ){
            $funcName = "write" . ucfirst( $_k );
            if( method_exists( new self, $funcName ) ){
                self::$funcName( $_v , $data , $url );
            }else{
                $errors[] = "$_k sitemap doesnot support";
            }
        }
        if( $errors ){
            exit(join( "<br />", $errors ));
        }
    }

    /**
     * 生成google 的sitemap
     * google sitemap 是一个主索引文件，一个主文件
     */
    static private function writeGoogle ( $filename , $data , $url )
    {
        $bodyFile = basename($filename) . ".xml";
        $indexFileData = sprintf("%s<sitemapindex xmlns=\"http://www.google.com/schemas/sitemap/0.84\"><sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap></sitemapindex>", self::xmlHeader() ,$url['site'] . $bodyFile , date( "Y-m-d" ));
        if( file_put_contents( $filename, $indexFileData )){
            $fData = sprintf("%s<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">",self::xmlHeader());
            foreach ( $data as $_k => $_v ){
                $fData .= sprintf( "<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>always</changefreq></url>" , sprintf( $url['link'] , $_v['articleid'] ) , date('Y-m-d') );
            }
            $fData .= "</urlset>";
            file_put_contents( $bodyFile, $fData );
        }
    }

    static private function writeBaidu ( $filename , $data , $url )
    {
        $fData = sprintf("%s<document><webSite>%s</webSite><webMaster>%s</webMaster><updatePeri>%d</updatePeri>" , self::xmlHeader(), $url['site'] , 'admin' , 15 );	//15代表每15分钟更新一次
        foreach ( $data as $_k => $_v ){
            $fData .= sprintf( "<item><title>%s</title><link>%s</link><text><![CDATA[%s]]></text><image /><source>%s</source><author>%s</author><pubDate>%s</pubDate></item>" , $_v['title'] , sprintf( $url['link'] , $_v['articleid'] ) , $_v['content'] , $url['site'] , 'admin' , date( "Y-m-d H:i:s" ));
        }
        $fData .= "</document>";
        file_put_contents( $filename , $fData );
    }
	
	static private function xmlHeader()
	{
		return '<?xml version="1.0" encoding="UTF-8"?>';
	}
}
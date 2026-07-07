<!--<?php
if(!defined('SABLOG_ROOT')) {
	exit('Access Denied');
}
print <<<EOT
-->
<div id="footer">
<dl id="hjl">
  <dt><a>友情链接</a> | <a>合作站点</a> | <a>其他网站</a> | </dt>
  <dd>$stylevar[huangjinlian]</dd>
  <dd>$stylevar[huangjinlian2]</dd>
  <dd>$stylevar[huangjinlian3] </dd>
  <!--
  <dd style="width:280px"><a href='https://www.upyun.com/?utm_source=lianmeng&utm_medium=referral'><img src="https://www.upyun.com/static/img/%E6%A0%B7%E5%BC%8F%E5%9B%BE.7cf927c.png" width="100%"/></a></dd>
  -->
</dl>
https://your-domain.com ::: Copyright &copy; 2004-2006 <a href="$options[url]">$options[name]</a><!--
EOT;
if($options['show_debug']){print <<<EOT
--><br />$sa_debug<!--
EOT;
}
print <<<EOT
--> <a href="https://validator.w3.org/check?uri=referer" target="_blank">XHTML 1.0</a>. <a href="post.php?action=clearcookies">清除Cookies</a>.
<!--
EOT;
if($options['icp']){print <<<EOT
-->
<a href="https://beian.miit.gov.cn/" target="_blank">$options[icp]</a> &nbsp; <a href="$options[url]" target="_blank" style="text-decoration:none">　&nbsp;</a>
<!--
EOT;
}print <<<EOT
-->
<!--
<script type="text/javascript" src="https://tajs.qq.com/stats?sId=14042292" charset="UTF-8"></script>
-->
</div>
</div>
<script type="text/JavaScript">
//alimama_domain_auth="1268879_10700647";
jquery(function(){
   if ( jquery.support.pjax ) {
      jquery(document).pjax('a','#page');
   }
})
</script>
</body></html><!--
EOT;
?>-->
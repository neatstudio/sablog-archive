<!--<?php
if(!defined('SABLOG_ROOT')) {
	exit('Access Denied');
}
print <<<EOT
-->
<script type="text/javascript">
window.onload=function(){
	fiximage('$options[attachments_thumbs_size]');
}
</script>
<script type="text/javascript" src="include/ajax.js"></script>
<div style="display:inline-block"><h2 class="title" >$article[title]</h2></div>
<div style="float:left;font-size:14px"><a href="./index.php">首页</a> &gt; <a href="./?action=index&amp;cid=$article[cid]">$article[cname]</a> &gt; </div>
<p class="postdate">Submitted by <strong><a href="./?action=finduser&amp;userid=$article[uid]">$article[username]</a></strong> on $article[dateline]. <a href="./?action=index&amp;cid=$article[cid]">$article[cname]</a> $stylevar[sina_shared_button]
<br />$stylevar[readability]</p><!--
EOT;
if (!$article['allowread']) {print <<<EOT
-->
<div class="needpwd"><form action="./?action=show&amp;id=$article[articleid]" method="post">这篇日志被加密了。请输入密码后查看。<br /><input class="formfield" type="password" name="readpassword" style="margin-right:5px;" /> <button class="formbutton" type="submit">提交</button></form></div>
<!--
EOT;
}
else {print <<<EOT
-->
<div class="content">$article[content] </div>
<br />
$stylevar[content_bottom]
<br />
<hr width="100%" size="1" />
本站采用<a href="http://creativecommons.org/licenses/by-nc-sa/2.5/deed.zh" target="_blank">创作共享</a>版权协议, 要求署名、非商业和保持一致. 本站欢迎任何非商业应用的转载, 但须注明出自"<a href="$options[url]">$options[name]</a>", 保留原始链接, 此外还必须标注原文标题和链接.
<hr width="100%" size="1" />
<!--
EOT;
if ($article['image']) {
foreach ($article['image'] as $image) {
if($image[6]){print <<<EOT
--><p class="attach">图片附件(缩略图):<br /><a href="attachment.php?id=$image[0]" target="_blank"><img src="$image[1]" border="0" alt="大小: $image[2]&#13;尺寸: $image[3] x $image[4]&#13;浏览: $image[5] 次&#13;点击打开新窗口浏览全图" width="$image[3]" height="$image[4]" /></a></p><!--
EOT;
} else {print <<<EOT
--><p class="attach">图片附件:<br /><a href="attachment.php?id=$image[0]" target="_blank"><img src="$image[1]" border="0" alt="大小: $image[2]&#13;尺寸: $image[3] x $image[4]&#13;浏览: $image[5] 次&#13;点击打开新窗口浏览全图" width="$image[3]" height="$image[4]" /></a></p><!--
EOT;
}}}
if($article['file']){
foreach($article['file'] as $file){
if($file){print <<<EOT
--><p class="attach"><strong>附件: </strong><a href="attachment.php?id=$file[0]" target="_blank">$file[1]</a> ($file[2], 下载次数:$file[3])</p><!--
EOT;
}}}
if ($article['keywords']) {
print <<<EOT
--><p class="tags"><strong>Tags</strong>: $article[tags]</p><!--
EOT;
}
print <<<EOT
-->
<p id="article-other">&laquo; <a href="./?action=show&amp;id={$article['articleid']}&amp;goto=previous">上一篇</a> | <a href="./?action=show&amp;id={$article['articleid']}&amp;goto=next">下一篇</a> &raquo;</p>
<!--
EOT;
if ($options['related_shownum'] && $related_tatol > 1 && $relids != $articleid) {print <<<EOT
-->
<h2 class="title"><span style="float:right;padding-bottom: 2px;font-size: 12px;">只显示{$options['related_shownum']}条记录</span>相关文章</h2>
<div class="lesscontent">
<!--
EOT;
foreach($titledb as $key => $title){print <<<EOT
--><a href="./?action=show&amp;id={$title['articleid']}">{$title['title']}</a> (浏览: <font color="#CC0000">{$title['views']}</font>, 评论: <font color="#CC0000">{$title['comments']}</font>)<br />
<!--
EOT;
}print <<<EOT
-->
</div>
<!--
EOT;
}print <<<EOT
-->
<a name="comment"></a>
<!--
EOT;
if ($article['comments']) {print <<<EOT
-->
<h2 class="title"><span style="FLOAT:right;padding-bottom: 2px;font-size: 12px;">$article[comments]条记录</span>访客评论</h2>
<!--
EOT;
foreach($commentdb as $key => $comment){
$comment['author'] = strip_tags($comment['author']);
print <<<EOT
--><a name="cm{$comment['commentid']}"></a><p class="lesscontent" id="comm_{$comment['commentid']}">$comment[content]</p>
<p class="lessdate">Post by {$comment['author']} on {$comment['dateline']} <img style="cursor: hand" onclick="addquote('comm_{$comment['commentid']}','{$comment['quoteuser']}')" src="templates/{$options['templatename']}/img/quote.gif" border="0" alt="引用此文发表评论" /> <font color="#000000">#<strong>{$comment['cmtorderid']}</strong></font></p>
<!--
EOT;
}print <<<EOT
-->
$multipage
<br />
<!--
EOT;
}
if (!$article['closecomment']) {
print <<<EOT
-->
<a name="addcomment"></a>
<h2 class="title">发表评论</h2>
  <form method="post" name="form" id="form" action="post.php" onsubmit="return checkform();">
    <input type="hidden" name="articleid" value="{$article['articleid']}" />
	<input type="hidden" name="formhash" value="$formhash" />
    <div class="formbox">
<!--
EOT;
if ($sax_uid) {
print <<<EOT
-->  <p>已经登陆为 <b>$sax_user</b> [<a href="post.php?action=logout">注销</a>]</p>
<!--
EOT;
} else {print <<<EOT
-->
  <p>
    <label for="username">
    名字 (必填):<br /><input name="username" id="username" type="text" value="$_COOKIE[comment_username]" tabindex="1" class="formfield" style="width: 210px;" /></label>
  </p>
  <p>
    <label for="password">
    密码 (游客不需要密码):<br /><input name="password" id="password" type="password" value="" tabindex="2" class="formfield" style="width: 210px;" /></label>
  </p>
  <p>
    <label for="url">
    网址或电子邮件 (选填):<br /><input type="text" name="url" id="url" value="$_COOKIE[comment_url]" tabindex="3" class="formfield" style="width: 210px;" /></label>
  </p>
<!--
EOT;
}print <<<EOT
-->
  <p>评论内容 (必填):<br />
<div style="width:100%">
<div style="width:420px;">
	<textarea name="content" id="content" cols="54" rows="8" class="formfield" disabled style="display:none"></textarea>
        <textarea name="relcontent" id="relcontent" cols="54" rows="8" tabindex="4" onkeydown="ctlent(event);" class="formfield" >$_COOKIE[cmcontent]</textarea>
</div>
</div>
  </p>
<!--
EOT;
if ($options['seccode'] && $sax_group != 1 && $sax_group !=2) {print <<<EOT
-->
  <p>
    <label for="clientcode">
    验证码(*):<br /><input name="clientcode" id="clientcode" value="" tabindex="5" class="formfield" size="6" maxlength="6" /> <img id="seccode" class="codeimg" src="include/seccode.php" alt="单击图片换张图片" border="0" onclick="this.src='include/seccode.php?update=' + Math.random()" /></label>
  </p>
<!--
EOT;
}print <<<EOT
-->
      <p><input type="hidden" name="action" value="addcomment" />
          <button type="submit" id="submit" name="submit" class="formbutton">提交</button></p>
    </div>
  </form>
<div class="lesscontent">
$stylevar[ggad3]
</div>
<!--
EOT;
} else {print <<<EOT
--><p align="center"><strong>本文因为某种原因此时不允许访客进行评论</strong></p>
<!--
EOT;
}}
?>
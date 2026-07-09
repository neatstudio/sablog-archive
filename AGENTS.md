# SaBlog-X 1.6 项目记忆

## 项目背景

本项目基于 SaBlog-X Ver 1.6，原始代码为 2006-2007 年开发，针对 PHP 4.x/5.x 环境。
经过适配 PHP 7.4+，修复了大量兼容性问题和 Bug，清理了个人品牌信息后作为开源模板发布。

## 核心修复记录

### 1. PHP 7.4+ 兼容性适配

#### 1.1 `preg_replace /e` 修饰符废弃
- **影响文件**: `index.php`, `admin/*.php`, `global.php` 等多处
- **问题**: PHP 7.0+ 废弃了 `preg_replace` 的 `/e` 修饰符，导致大量正则替换失效
- **修复方案**: 全部替换为 `preg_replace_callback()`
  ```php
  // 旧代码（PHP 5.x）
  $article['content'] = preg_replace("/\[attach=(\d+)\]/ie", "upload('\\1')", $article['content']);
  
  // 新代码（PHP 7.4+）
  $article['content'] = preg_replace_callback("/\[attach=(\d+)\]/i", function($matches) {
      return upload($matches[1]);
  }, $article['content']);
  ```

#### 1.2 `mysql_*` 函数废弃
- **影响文件**: `include/db.php` 等
- **问题**: PHP 7.0+ 移除了 `mysql_*` 扩展
- **修复方案**: 使用 `mysqli_*` 或 PDO 替代

#### 1.3 `session_register()` 等函数废弃
- **影响文件**: `global.php`
- **问题**: PHP 5.4+ 废弃了 `session_register()`
- **修复方案**: 使用 `$_SESSION` 超全局数组直接操作

### 2. 前台附件解析 Bug 修复

#### 2.1 `[attach=xx]` 标签解析失败
- **影响文件**: `index.php`, `global.php`
- **问题描述**: 
  1. `index.php` 中 `require_once(SABLOG_ROOT . 'include/func_attachment.php')` 被包裹在 `if($article['attachments'])` 条件内
  2. 当文章 `attachments` 字段为空时，`func_attachment.php` 未被加载
  3. 但 `preg_replace_callback` 仍然调用 `upload()` 函数，导致 fatal error，页面返回空内容
- **修复方案**:
  - **show 模式**（文章详情页）: 将 `require_once` 移出 `if($article['attachments'])` 块
  - **normal 模式**（文章列表页）: 同上修复，并将 `preg_replace_callback` 移出条件块

#### 2.2 `upload()` 函数数据库 Fallback
- **影响文件**: `global.php`
- **问题**: 系统为性能优先，从 `attachments` 字段反序列化获取附件信息。当该字段为空或损坏时，无法解析附件
- **修复方案**: 在 `upload()` 函数中增加数据库 fallback 逻辑
  ```php
  function upload($aid){
      global $article, $attachmentids, $options, $DB, $db_prefix;
      // 优先从 $article['image'] / $article['file'] 获取（性能优先）
      if ($article['image'][$aid]) { ... }
      elseif ($article['file'][$aid]) { ... }
      else {
          // 如果文章 attachments 字段为空或损坏，直接从数据库查询
          $attachinfo = $DB->fetch_one_array("SELECT ... FROM {$db_prefix}attachments WHERE attachmentid='" . intval($aid) . "'");
          if ($attachinfo) { ... }
          return "[attach=$aid]";  // 附件不存在时保持原样
      }
  }
  ```

#### 2.3 附件反序列化错误
- **影响文件**: `index.php`, `admin/article.php`
- **问题**: `unserialize(stripslashes_array($article['attachments']))` 在 PHP 7 下导致反序列化失败
- **修复方案**: 移除 `stripslashes_array()` 包装，直接使用 `unserialize()`

### 3. 后台保存文章白屏/500 错误

#### 3.1 `redirect()` 函数缓冲区嵌套
- **影响文件**: `admin/adminfunctions.php`
- **问题**: `redirect()` 调用 `PageEnd()`，但 `PageEnd()` 在输出缓冲区嵌套时会导致 fatal error
- **修复方案**: 绕过 `PageEnd()`，直接调用 `ob_end_flush()` 输出缓冲区
  ```php
  function redirect($url) {
      // 修复：直接 flush 缓冲区，避免 PageEnd() 嵌套错误
      while (ob_get_level()) {
          ob_end_flush();
      }
      header("Location: $url");
      exit;
  }
  ```

#### 3.2 `cpfooter()` 函数同样问题
- **修复方案**: 同上，直接 `ob_end_flush()`

#### 3.3 `writelog()` 函数 `flock()` 类型错误
- **影响文件**: `admin/adminfunctions.php`
- **问题**: `fopen()` 失败返回 `false`，直接传入 `flock()` 导致 `TypeError: flock(): Argument #1 ($stream) must be of type resource, bool given`
- **修复方案**: 增加 `is_resource()` 检查
  ```php
  $handle = @fopen($logfile, 'a');
  if (is_resource($handle)) {
      flock($handle, LOCK_EX);
      fwrite($handle, $log);
      flock($handle, LOCK_UN);
      fclose($handle);
  }
  ```

### 4. 1Panel WAF 拦截问题
- **问题**: 后台提交包含 HTML 代码（如广告位）时触发 WAF 规则 "请求携带恶意参数 已被拦截"
- **解决方案**: 在 1Panel 后台关闭 WAF 或调整规则，不属于代码修复范畴

### 5. 模板清理与发布

#### 5.1 品牌信息清理
- 移除所有 `neatstudio.com`, `neatcn`, `gouki` 等个人品牌信息
- 替换为占位符或通用描述
- 保留 `4ngel` 等原始作者信息

#### 5.2 删除废弃目录
- `wap/` — 现代浏览器已无需单独 WAP 适配

#### 5.3 补充缺失文件
从旧版 sablog.zip 对比后补充：
- `install/` — 完整安装程序（含升级脚本 `upgrade.php` - `upgrade5.php`）
- `admin/backupdata/` — 后台数据库备份目录
- `admin/editor/fckeditor_php4.php`, `fckeditor_php5.php` — 编辑器兼容文件
- `trackback.php` — Trackback 接口
- `cache/index.htm`, `cache/index.php` — 缓存目录保护文件
- `cache/log/index.htm`, `cache/log/index.php` — 日志目录保护文件

#### 5.4 部署配置
创建 `deploy/` 目录包含：
- `nginx.conf` — Nginx 虚拟主机 + 伪静态规则 + 安全限制
- `.htaccess` — Apache mod_rewrite 规则
- `docker-compose.yml` — Docker 编排
- `php.ini` — 推荐 PHP 配置
- `README.md` — 部署详细指南

## 伪静态 URL 规则

| URL 示例 | 对应参数 |
|----------|----------|
| `/show-2711-1.shtml` | `action=show&id=2711&page=1` |
| `/category-1-1.shtml` | `action=index&cid=1&page=1` |
| `/archives-201701-1.shtml` | `action=index&setdate=201701&page=1` |
| `/tagslist-1.shtml` | `action=tagslist&page=1` |
| `/comments-1.shtml` | `action=comments&page=1` |
| `/search-1.shtml` | `action=search&page=1` |
| `/links.shtml` | `action=links` |
| `/reg.shtml` | `action=reg` |
| `/login.shtml` | `action=login` |
| `/rss.xml` | `rss.php` |
| `/sitemap.xml` | `sitemap.php` |

## 关键文件路径

| 文件 | 说明 |
|------|------|
| `index.php` | 入口文件，含 show/normal 两种视图模式 |
| `global.php` | 全局函数，含 `upload()`, `message()`, `PageEnd()` 等 |
| `admin/adminfunctions.php` | 后台函数，含 `redirect()`, `cpfooter()`, `writelog()` |
| `include/func_attachment.php` | 附件处理函数库 |
| `include/db.php` | 数据库操作类 |
| `config.php` | 数据库配置（需手动填写） |
| `attachment.php` | 附件下载接口 |
| `post.php` | 文章发布/编辑处理 |

## 已知限制

1. **附件 978 不存在**: 数据库最大附件 ID 为 948，文章 2711 中 `[attach=978]` 无法解析，保持原样显示（符合预期行为）
2. **WAF 拦截**: 1Panel 等面板 WAF 可能拦截含 HTML 的后台提交，需面板层面调整
3. **PHP 8.0+ 未测试**: 当前适配目标为 PHP 7.4，PHP 8.0+ 可能有额外兼容性问题

## 发布信息

- **GitHub**: https://github.com/neatstudio/sablog-archive
- **License**: MIT
- **原始作者**: 4ngel (Security Angel Team)
- **适配维护**: neatstudio

## 记忆更新日期

2025-07-09

# Changelog

All notable changes to this project will be documented in this file.

## [1.6.1] - 2025-07-09

### Fixed
- 修复前台 `[attach=xx]` 标签解析失败的问题
  - `index.php` show 模式：将 `require_once(SABLOG_ROOT . 'include/func_attachment.php')` 移出 `if($article['attachments'])` 条件块，确保无论 `attachments` 字段是否为空都能加载附件函数库
  - `index.php` normal 模式：同上修复，列表页也能正常解析附件标签
  - `global.php` `upload()` 函数：增加数据库 fallback 逻辑，当 `$article['image']` 和 `$article['file']` 数组中不存在附件时，直接从数据库查询附件信息并生成链接
- 修复后台保存文章白屏/500 错误
  - `admin/adminfunctions.php` `redirect()` 函数：绕过 `PageEnd()`，直接调用 `ob_end_flush()` 输出缓冲区，避免嵌套缓冲区导致的 fatal error
  - `admin/adminfunctions.php` `cpfooter()` 函数：同上修复
  - `admin/adminfunctions.php` `writelog()` 函数：增加 `is_resource($handle)` 检查，防止 `fopen()` 失败时传入非 resource 类型到 `flock()`
- 修复附件反序列化错误
  - 移除 `unserialize()` 调用中的 `stripslashes_array()` 包装，避免 PHP 7 下反序列化失败

### Removed
- 删除 `wap/` 目录（手机版），现代浏览器已无需单独适配

### Security
- 清理所有个人品牌信息（neatstudio / neatcn 等）
- 移除敏感配置文件中的硬编码密钥和路径

## [1.6.0] - 2025-07-07

### Initial Release
- 基于 SaBlog-X Ver 1.6 原始代码
- 适配 PHP 7.4+（移除已废弃的 `preg_replace /e` 修饰符等）
- 清理个人化内容，作为通用模板发布

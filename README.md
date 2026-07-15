# SaBlog-X 1.6

## 简介

这是基于 SaBlog-X Ver 1.6 的博客系统模板，已清理个人品牌信息和敏感配置，可直接用于部署或二次开发。

## 系统要求

- PHP 7.4+ (推荐 PHP 7.4)
- MySQL 5.7+ / MariaDB 10.3+
- Nginx / Apache
- mod_rewrite (可选，用于伪静态)

## 安装步骤

1. 将代码上传到网站根目录
2. 复制 `config.example.php` 为 `config.php`，并根据实际环境填写数据库连接信息
3. 访问 `install/` 目录进行安装
4. 安装完成后**删除 `install/` 目录**
5. 确保 `cache/` 和 `attachments/` 目录可写

## 目录结构

```
├── admin/          # 后台管理
│   ├── backupdata/ # 数据库备份目录
│   ├── editor/     # 富文本编辑器
│   └── js/         # 后台脚本
├── archives/       # 文章归档
├── attachments/    # 附件上传目录
├── cache/          # 缓存目录（需可写）
│   └── log/        # 日志缓存
├── deploy/         # 部署配置（Nginx/Apache/Docker）
├── images/         # 图片资源
│   └── smiles/     # 表情图标
├── include/        # 核心函数库
├── install/        # 安装程序（安装后删除！）
├── templates/      # 模板目录
│   ├── admin/      # 后台模板
│   └── default/    # 前台模板
├── config.example.php  # 数据库配置示例文件
├── config.php      # 数据库配置文件（由用户自行创建，已被 .gitignore 忽略）
├── index.php       # 入口文件
├── global.php      # 全局函数
├── attachment.php  # 附件下载
├── post.php        # 文章发布/编辑
├── rss.php         # RSS 订阅
├── sitemap.php     # 站点地图
├── trackback.php   # Trackback 接口
└── ...
```

## 部署配置

`deploy/` 目录包含多种部署方式的配置示例：

| 文件 | 说明 | 适用场景 |
|------|------|----------|
| `nginx.conf` | Nginx 虚拟主机 + 伪静态 | Nginx + PHP-FPM |
| `.htaccess` | Apache 重写规则 | Apache |
| `docker-compose.yml` | Docker 编排 | Docker 部署 |
| `php.ini` | PHP 推荐配置 | 所有场景 |
| `README.md` | 部署详细指南 | - |

详见 [deploy/README.md](deploy/README.md)

## 注意事项

- 首次使用前请确保 `cache/` 和 `attachments/` 目录有写入权限
- 后台路径：`/admin/admincp.php`
- 默认管理员账号需在安装时设置
- 所有硬编码的域名、品牌信息已替换为占位符，使用前请根据实际修改
- **安装完成后务必删除 `install/` 目录**
- `config.php` 已被 `.gitignore` 忽略，生产环境的真实数据库凭据不会进入版本控制。部署时请从 `config.example.php` 复制并修改

## 更新日志

详见 [CHANGELOG.md](CHANGELOG.md)

## 许可证

MIT License

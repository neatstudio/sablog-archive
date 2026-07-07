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
2. 修改 `config.php`，填写数据库连接信息
3. 访问 `install/` 目录进行安装（如果存在安装程序）
4. 安装完成后删除 `install/` 目录
5. 确保 `cache/` 目录可写

## 目录结构

```
├── admin/          # 后台管理
├── archives/       # 文章归档
├── attachment/     # 附件上传目录
├── cache/          # 缓存目录（需可写）
├── include/        # 核心函数库
├── templates/      # 模板目录
│   ├── admin/      # 后台模板
│   └── default/    # 前台模板
├── wap/            # 手机版
├── config.php      # 数据库配置文件
├── index.php       # 入口文件
└── ...
```

## 注意事项

- 首次使用前请确保 `cache/` 目录有写入权限
- 后台路径：`/admin/admincp.php`
- 默认管理员账号需在安装时设置
- 所有硬编码的域名、品牌信息已替换为占位符，使用前请根据实际修改

## 许可证

MIT License

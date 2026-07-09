# SaBlog-X 部署指南

## 目录说明

本目录包含 SaBlog-X 的各种部署配置示例，可根据实际环境选择使用。

## 文件清单

| 文件 | 说明 | 适用场景 |
|------|------|----------|
| `nginx.conf` | Nginx 虚拟主机配置 | Nginx + PHP-FPM |
| `.htaccess` | Apache 重写规则 | Apache + mod_php/mod_fcgid |
| `docker-compose.yml` | Docker Compose 编排 | Docker 部署 |
| `php.ini` | PHP 推荐配置 | 所有场景 |

## 伪静态 URL 规则

本系统支持以下伪静态 URL 格式：

| 页面类型 | URL 示例 | 对应参数 |
|----------|----------|----------|
| 文章详情 | `/show-2711-1.shtml` | `action=show&id=2711&page=1` |
| 分类列表 | `/category-1-1.shtml` | `action=index&cid=1&page=1` |
| 日期归档 | `/archives-201701-1.shtml` | `action=index&setdate=201701&page=1` |
| 标签列表 | `/tagslist-1.shtml` | `action=tagslist&page=1` |
| 评论列表 | `/comments-1.shtml` | `action=comments&page=1` |
| 搜索 | `/search-1.shtml` | `action=search&page=1` |
| 友情链接 | `/links.shtml` | `action=links` |
| 注册 | `/reg.shtml` | `action=reg` |
| 登录 | `/login.shtml` | `action=login` |
| RSS | `/rss.xml` | `rss.php` |
| 站点地图 | `/sitemap.xml` | `sitemap.php` |

## 快速部署

### 1. Nginx + PHP-FPM（推荐）

```bash
# 复制配置文件到 Nginx 配置目录
sudo cp deploy/nginx.conf /etc/nginx/sites-available/sablog
sudo ln -s /etc/nginx/sites-available/sablog /etc/nginx/sites-enabled/

# 修改配置文件中的域名和路径
sudo vim /etc/nginx/sites-available/sablog

# 测试并重载
sudo nginx -t
sudo systemctl reload nginx
```

### 2. Apache

```bash
# 确保已启用 mod_rewrite
sudo a2enmod rewrite

# 将 .htaccess 放到网站根目录（已包含在代码中）
# 确保 Apache 允许 .htaccess 覆盖
# 在虚拟主机配置中添加：
# AllowOverride All

sudo systemctl reload apache2
```

### 3. Docker Compose

```bash
# 修改配置文件
cp deploy/docker-compose.yml docker-compose.yml
vim docker-compose.yml  # 修改密码等配置

# 启动
sudo docker-compose up -d

# 查看日志
sudo docker-compose logs -f
```

## 安全建议

1. **生产环境** 务必修改所有默认密码
2. **SSL 证书** 建议使用 Let's Encrypt 免费证书
3. **目录权限** 确保 `cache/` 和 `attachments/` 可写，其他目录只读
4. **后台路径** 建议通过 Nginx/Apache 限制 `admin/` 目录的访问 IP
5. **数据库** 定期备份，建议启用二进制日志

## 常见问题

### Q: 伪静态不生效？
A: 检查 Web 服务器是否已加载重写模块，且配置文件路径正确。

### Q: 附件上传失败？
A: 检查 `attachments/` 目录权限（需 755 或 777），以及 `php.ini` 中的 `upload_max_filesize`。

### Q: 缓存无法写入？
A: 确保 `cache/` 目录对 PHP 运行用户可写。
  - Nginx/PHP-FPM: 通常是 `www-data` 用户
  - 可执行: `chown -R www-data:www-data cache/ attachments/`

### Q: 数据库连接失败？
A: 检查 `config.php` 中的数据库配置，确认 MySQL 服务已启动且允许远程连接（如使用 Docker）。

# SaBlog-X 迁移至 Laravel 重构 — 技术规格与需求文档

## 一、项目概述

将现有 SaBlog-X 1.6（PHP 4/5 时代老架构）迁移至 Laravel 框架，保留所有功能、数据、URL 结构和 SEO 权重。

---

## 二、现有系统功能清单

### 2.1 前台功能

| 功能模块 | 说明 | URL 路由 |
|----------|------|----------|
| 首页文章列表 | 支持分页、分类筛选、日期归档 | `/`, `/index.php?action=index` |
| 文章详情页 | 含评论、Trackback、相关文章 | `/show-{id}-{page}.shtml` |
| 分类列表 | 按分类筛选文章 | `/category-{cid}-{page}.shtml` |
| 日期归档 | 按年月筛选文章 | `/archives-{date}-{page}.shtml` |
| 标签系统 | 标签云、标签文章列表 | `/tagslist-{page}.shtml`, `/tag/{name}` |
| 评论列表 | 全部评论分页 | `/comments-{page}.shtml` |
| 搜索 | 标题/内容/描述全文搜索 | `/search-{page}.shtml` |
| 友情链接 | 链接展示页面 | `/links.shtml` |
| 用户注册/登录 | 简单账号系统 | `/reg.shtml`, `/login.shtml` |
| RSS 订阅 | 文章 RSS 2.0 | `/rss.xml`, `/rss.php?cid={cid}` |
| 站点地图 | XML Sitemap | `/sitemap.xml` |
| 附件下载 | 图片/文件附件 | `/attachment.php?id={id}` |
| Trackback | 引用通知接口 | `/tburl.php` |
| WAP 版 | 已废弃，不迁移 | — |

### 2.2 后台功能

| 功能模块 | 说明 |
|----------|------|
| 仪表盘 | 统计概览、快捷入口 |
| 文章管理 | 发布/编辑/删除、批量操作、附件关联 |
| 评论管理 | 审核/删除/回复、IP 搜索 |
| 分类管理 | 增删改、排序 |
| 标签管理 | 标签合并、清理 |
| 附件管理 | 上传/删除/缩略图生成 |
| 用户管理 | 用户列表、权限组、IP 搜索 |
| 链接管理 | 友情链接增删改 |
| 模板管理 | 模板变量编辑（广告位等 HTML） |
| 缓存管理 | 重建缓存、清理 |
| 数据库管理 | 备份/恢复/优化、RSS 导入 |
| 日志管理 | 登录日志、管理日志 |
| 系统设置 | 站点名称、SEO、附件、评论、时间等 |

### 2.3 数据实体

| 实体 | 表名 | 核心字段 | 关系 |
|------|------|----------|------|
| 文章 | `articles` | articleid, title, content, description, keywords, cid, uid, dateline, views, comments, trackbacks, stick, visible, readpassword, attachments | 属于分类、用户；有多条评论、附件、标签 |
| 分类 | `categories` | cid, name, articles, displayorder | 有多篇文章 |
| 评论 | `comments` | commentid, articleid, author, content, dateline, ipaddress, visible | 属于文章 |
| 标签 | `tags` | tagid, tag, aids, article_count | 多对多关联文章 |
| 附件 | `attachments` | attachmentid, articleid, filename, filetype, filesize, filepath, thumb_filepath, downloads, dateline | 属于文章 |
| 用户 | `users` | userid, username, password, groupid, logincount | 有多篇文章、评论 |
| 链接 | `links` | linkid, name, url, note, displayorder | 独立实体 |
| Trackback | `trackbacks` | trackbackid, articleid, title, excerpt, url, blog_name, dateline, visible | 属于文章 |
| 统计 | `statistics` | 单表记录站点统计数字 | 独立 |
| 设置 | `settings` | 键值对存储 | 独立 |
| 模板变量 | `stylevars` | 后台可编辑的 HTML 片段（广告位等） | 独立 |

---

## 三、URL 路由与伪静态（必须 100% 兼容）

### 3.1 前台路由（Nginx/Apache 伪静态）

```nginx
# 文章详情
rewrite ^/show-(\d+)-(\d+)\.shtml$ /index.php?action=show&id=$1&page=$2 last;
rewrite ^/show-(\d+)\.shtml$ /index.php?action=show&id=$1 last;

# 分类列表
rewrite ^/category-(\d+)-(\d+)\.shtml$ /index.php?action=index&cid=$1&page=$2 last;
rewrite ^/category-(\d+)\.shtml$ /index.php?action=index&cid=$1 last;

# 日期归档
rewrite ^/archives-(\d{6})-(\d+)\.shtml$ /index.php?action=index&setdate=$1&page=$2 last;
rewrite ^/archives-(\d{6})\.shtml$ /index.php?action=index&setdate=$1 last;
rewrite ^/archives\.shtml$ /index.php?action=index&setdate=all last;

# 标签列表
rewrite ^/tagslist-(\d+)\.shtml$ /index.php?action=tagslist&page=$1 last;
rewrite ^/tagslist\.shtml$ /index.php?action=tagslist last;

# 评论列表
rewrite ^/comments-(\d+)\.shtml$ /index.php?action=comments&page=$1 last;
rewrite ^/comments\.shtml$ /index.php?action=comments last;

# 搜索
rewrite ^/search-(\d+)\.shtml$ /index.php?action=search&page=$1 last;
rewrite ^/search\.shtml$ /index.php?action=search last;

# 其他页面
rewrite ^/links\.shtml$ /index.php?action=links last;
rewrite ^/reg\.shtml$ /index.php?action=reg last;
rewrite ^/login\.shtml$ /index.php?action=login last;

# RSS / Sitemap
rewrite ^/rss\.xml$ /rss.php last;
rewrite ^/sitemap\.xml$ /sitemap.php last;
```

### 3.2 Laravel 路由映射

```php
// routes/web.php
Route::get('/', [HomeController::class, 'index']);
Route::get('/index.php', [HomeController::class, 'index']); // 兼容旧链接

// 文章详情
Route::get('/show-{id}-{page}.shtml', [ArticleController::class, 'show']);
Route::get('/show-{id}.shtml', [ArticleController::class, 'show']);

// 分类
Route::get('/category-{cid}-{page}.shtml', [CategoryController::class, 'index']);
Route::get('/category-{cid}.shtml', [CategoryController::class, 'index']);

// 归档
Route::get('/archives-{date}-{page}.shtml', [ArchiveController::class, 'index']);
Route::get('/archives-{date}.shtml', [ArchiveController::class, 'index']);
Route::get('/archives.shtml', [ArchiveController::class, 'all']);

// 标签
Route::get('/tagslist-{page}.shtml', [TagController::class, 'list']);
Route::get('/tagslist.shtml', [TagController::class, 'list']);
Route::get('/tag/{name}', [TagController::class, 'show']);

// 评论
Route::get('/comments-{page}.shtml', [CommentController::class, 'index']);
Route::get('/comments.shtml', [CommentController::class, 'index']);

// 搜索
Route::get('/search-{page}.shtml', [SearchController::class, 'index']);
Route::get('/search.shtml', [SearchController::class, 'index']);

// 其他
Route::get('/links.shtml', [LinkController::class, 'index']);
Route::get('/reg.shtml', [AuthController::class, 'register']);
Route::get('/login.shtml', [AuthController::class, 'login']);

// RSS / Sitemap
Route::get('/rss.xml', [RssController::class, 'index']);
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// 附件下载
Route::get('/attachment.php', [AttachmentController::class, 'download']);

// Trackback
Route::post('/tburl.php', [TrackbackController::class, 'receive']);
```

---

## 四、数据库迁移方案

### 4.1 表结构迁移

现有 MySQL 表直接保留，Laravel 通过 Eloquent 模型映射。需要创建 Migration 定义现有表结构。

### 4.2 关键字段映射

| 旧字段 | Laravel 类型 | 说明 |
|--------|-------------|------|
| `articleid` | `$table->id()` | 主键，保持自增 |
| `dateline` | `$table->timestamp('dateline')` | 原用 Unix 时间戳，可保留或迁移为 datetime |
| `attachments` | `$table->json('attachments')->nullable()` | 原用 PHP serialize，改为 JSON |
| `keywords` | `$table->string('keywords', 255)->nullable()` | 逗号分隔的标签字符串 |
| `visible` | `$table->boolean('visible')->default(true)` | tinyint 改为 boolean |
| `stick` | `$table->boolean('stick')->default(false)` | 置顶标记 |

### 4.3 数据迁移脚本

```php
// database/seeders/LegacyDataSeeder.php
class LegacyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. 迁移文章数据
        // 注意：attachments 字段从 PHP serialize 转为 JSON
        // 注意：处理可能的编码问题（原系统可能是 GBK/Latin1）
        
        // 2. 迁移附件数据
        // 注意：filepath 路径格式保持兼容
        
        // 3. 迁移评论数据
        // 注意：IP 地址字段可能需要扩展为 IPv6 兼容
        
        // 4. 迁移用户数据
        // 注意：密码哈希算法！原系统可能使用 md5 或其他，需要重新哈希或兼容验证
    }
}
```

### 4.4 密码兼容方案

原系统密码哈希方式需要确认。可能的方案：
- **方案 A**: 原密码是 md5，迁移时强制所有用户重置密码
- **方案 B**: 保留原哈希算法，登录时验证成功后迁移到 Laravel 的 Bcrypt
- **方案 C**: 在 users 表增加 `password_legacy` 字段，登录时先检查新哈希，不存在则检查旧哈希并升级

---

## 五、功能实现技术要点

### 5.1 文章系统

```php
// app/Models/Article.php
class Article extends Model
{
    protected $primaryKey = 'articleid';
    public $timestamps = false; // 使用 dateline 而非 created_at/updated_at
    
    protected $casts = [
        'attachments' => 'array', // 自动 JSON 序列化
        'visible' => 'boolean',
        'stick' => 'boolean',
        'dateline' => 'datetime',
    ];
    
    // 关联
    public function category() { return $this->belongsTo(Category::class, 'cid'); }
    public function user() { return $this->belongsTo(User::class, 'uid'); }
    public function comments() { return $this->hasMany(Comment::class, 'articleid'); }
    public function attachments() { return $this->hasMany(Attachment::class, 'articleid'); }
    public function tags() { return $this->belongsToMany(Tag::class, 'article_tag', 'articleid', 'tagid'); }
    
    // 作用域
    public function scopeVisible($query) { return $query->where('visible', true); }
    public function scopeSticky($query) { return $query->where('stick', true); }
    public function scopePublished($query) { return $query->where('dateline', '<=', now()); }
}
```

### 5.2 附件系统

```php
// app/Models/Attachment.php
class Attachment extends Model
{
    protected $primaryKey = 'attachmentid';
    public $timestamps = false;
    
    // 访问器：生成完整 URL
    public function getUrlAttribute()
    {
        return asset('attachments/' . $this->filepath);
    }
    
    // 访问器：生成缩略图 URL
    public function getThumbUrlAttribute()
    {
        if ($this->thumb_filepath) {
            return asset('attachments/' . $this->thumb_filepath);
        }
        return null;
    }
}
```

**附件上传逻辑**：
- 使用 Laravel Storage 存储文件
- 生成唯一文件名（保持与原系统兼容的哈希命名）
- 图片自动压缩和生成缩略图（Intervention Image 库）
- 非图片文件统一存储，下载时强制 `Content-Disposition: attachment`

### 5.3 评论系统

```php
// 评论需要防垃圾和 XSS 过滤
class Comment extends Model
{
    protected $fillable = ['articleid', 'author', 'content', 'url', 'ipaddress'];
    
    // 全局作用域：默认只显示已审核评论
    protected static function booted()
    {
        static::addGlobalScope('visible', fn($q) => $q->where('visible', true));
    }
    
    // 设置器：自动过滤 XSS
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = clean($value); // 使用 HTMLPurifier 或类似库
    }
}
```

### 5.4 搜索功能

原系统使用 `LIKE '%keyword%'` 简单搜索。迁移方案：

**方案 A（简单）**: 继续使用 LIKE，添加全文索引
```sql
ALTER TABLE articles ADD FULLTEXT INDEX ft_search(title, content, description);
```

**方案 B（推荐）**: 使用 Laravel Scout + MeiliSearch/Elasticsearch
```php
// 配置 Scout
class Article extends Model
{
    use Searchable;
    
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'content' => strip_tags($this->content),
            'description' => $this->description,
            'keywords' => $this->keywords,
        ];
    }
}
```

### 5.5 缓存系统

原系统使用文件缓存（`cache/cache_*.php`）。迁移方案：

```php
// 使用 Laravel Cache（Redis 推荐）
// config/cache.php 配置 Redis

// 缓存键命名规范
Cache::remember('articles:category:{cid}:page:{page}', 3600, fn() => ...);
Cache::remember('articles:show:{id}', 3600, fn() => ...);
Cache::remember('tags:cloud', 3600, fn() => ...);
Cache::remember('sidebar:stats', 3600, fn() => ...);

// 缓存标签（用于批量清理）
Cache::tags(['articles'])->flush();
```

**必须缓存的数据**：
- 站点统计（文章数、评论数等）
- 分类列表
- 标签云
- 最新评论
- 归档列表
- 友情链接
- 日历数据

### 5.6 RSS / Sitemap

```php
// RSS 生成使用 spatie/laravel-feed
// Sitemap 使用 spatie/laravel-sitemap

class RssController extends Controller
{
    public function index()
    {
        $articles = Article::visible()->published()
            ->orderBy('dateline', 'desc')
            ->limit(20)
            ->get();
            
        return response()->view('rss.index', compact('articles'))
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
```

---

## 六、后台管理（Laravel Filament / Nova / 自定义）

### 6.1 推荐方案：Laravel Filament

```php
// app/Filament/Resources/ArticleResource.php
class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required(),
            Select::make('cid')->relationship('category', 'name'),
            RichEditor::make('content'),
            TextInput::make('keywords'),
            Textarea::make('description'),
            Toggle::make('visible')->default(true),
            Toggle::make('stick')->default(false),
            DateTimePicker::make('dateline')->default(now()),
            // 附件上传组件
            FileUpload::make('new_attachments')
                ->multiple()
                ->directory('attachments')
                ->imageEditor()
                ->preserveFilenames(),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('articleid')->sortable(),
            TextColumn::make('title')->searchable(),
            TextColumn::make('category.name'),
            TextColumn::make('user.username'),
            TextColumn::make('dateline')->dateTime(),
            IconColumn::make('visible')->boolean(),
            IconColumn::make('stick')->boolean(),
        ])->filters([
            SelectFilter::make('cid')->relationship('category', 'name'),
            TernaryFilter::make('visible'),
        ]);
    }
}
```

### 6.2 后台功能对照表

| 原后台功能 | Filament 实现 | 备注 |
|-----------|--------------|------|
| 文章管理 | ArticleResource | 富文本编辑器、附件上传、标签输入 |
| 评论管理 | CommentResource | 批量审核、IP 搜索、垃圾评论过滤 |
| 分类管理 | CategoryResource | 拖拽排序 |
| 标签管理 | TagResource | 合并标签功能 |
| 附件管理 | AttachmentResource | 图片预览、缩略图管理 |
| 用户管理 | UserResource | 权限组管理 |
| 链接管理 | LinkResource | 拖拽排序 |
| 模板变量 | StylevarResource | 代码编辑器（HTML 片段） |
| 系统设置 | SettingsPage | 键值对配置 |
| 缓存管理 | 自定义页面 | 一键清理缓存按钮 |
| 数据库备份 | 自定义页面 | 调用 `mysqldump` 或 Laravel Backup |
| 日志查看 | 自定义页面 | 读取日志文件/数据库 |

---

## 七、安全加固清单

### 7.1 必须实现的安全措施

| 安全措施 | 实现方式 | 优先级 |
|----------|----------|--------|
| SQL 注入防护 | Eloquent ORM + 参数绑定 | 必须 |
| XSS 过滤 | HTMLPurifier / `e()` 辅助函数 | 必须 |
| CSRF 保护 | Laravel 内置 `@csrf` | 必须 |
| 密码哈希 | Laravel Bcrypt (`Hash::make`) | 必须 |
| 文件上传验证 | MIME 类型 + 文件内容 + 扩展名白名单 | 必须 |
| 附件下载安全 | 强制 `Content-Disposition: attachment`，禁止执行 | 必须 |
| 后台访问控制 | Filament 认证 + 权限中间件 | 必须 |
| 登录限流 | Laravel Throttle | 必须 |
| 敏感配置加密 | `.env` 文件 + `env()` 读取 | 必须 |
| 日志安全 | 存储到 `storage/logs`，禁止 Web 访问 | 必须 |
| HTTPS 强制 | 中间件 `HttpsProtocol` | 必须 |
| 内容安全策略 (CSP) | HTTP 头 `Content-Security-Policy` | 推荐 |
| 验证码 | reCAPTCHA / hCaptcha | 推荐 |

### 7.2 废弃的安全风险功能

- `tunnel_sql.php` — 删除，不迁移
- `opcache_*.php` — 删除，使用 Laravel 命令替代
- `debug_attach.php` — 删除，使用 Laravel Telescope 或日志
- 模板编辑 PHP 文件功能 — 限制只能编辑 HTML/CSS

---

## 八、性能优化清单

### 8.1 数据库层面

```sql
-- 必须添加的索引
ALTER TABLE articles ADD INDEX idx_visible_dateline (visible, dateline);
ALTER TABLE articles ADD INDEX idx_visible_cid_dateline (visible, cid, dateline);
ALTER TABLE articles ADD FULLTEXT INDEX ft_search (title, content, description);
ALTER TABLE comments ADD INDEX idx_articleid_visible (articleid, visible);
ALTER TABLE trackbacks ADD INDEX idx_articleid_visible (articleid, visible);
ALTER TABLE tags ADD INDEX idx_tag (tag);
ALTER TABLE attachments ADD INDEX idx_articleid (articleid);
```

### 8.2 应用层面

```php
// 1. Eager Loading 避免 N+1
Article::with(['category', 'user', 'comments'])->get();

// 2. 分页优化（大数据量时使用游标分页）
Article::visible()->paginate(20); // 传统分页
Article::visible()->cursorPaginate(20); // 游标分页（推荐）

// 3. 查询缓存
Article::visible()->remember(3600)->get();

// 4. 队列处理耗时任务
// 如：生成缩略图、发送邮件、RSS 更新
GenerateThumbnail::dispatch($attachment);

// 5. 数据库读写分离（高流量时）
// config/database.php 配置 read/write hosts
```

### 8.3 静态资源层面

- 使用 Laravel Mix / Vite 合并压缩 CSS/JS
- 图片使用 WebP 格式（带 JPEG fallback）
- 启用 CDN（Cloudflare / 阿里云 CDN）
- 浏览器缓存：`Cache-Control: max-age=31536000`

---

## 九、部署架构

```
┌─────────────────┐
│   Cloudflare    │  CDN + WAF + DDoS 防护
│    (可选)       │
└────────┬────────┘
         │
┌────────▼────────┐
│   Nginx/        │  负载均衡 + 静态文件 + SSL 终止
│   OpenResty     │  伪静态规则（兼容旧 URL）
└────────┬────────┘
         │
┌────────▼────────┐
│   PHP-FPM       │  Laravel 应用
│   (Laravel)     │  OPcache 启用
└────────┬────────┘
         │
┌────────▼────────┐
│   Redis         │  缓存 + Session + 队列
└────────┬────────┘
         │
┌────────▼────────┐
│   MySQL 8.0     │  主从复制（可选）
│   / MariaDB     │
└─────────────────┘
```

### 9.1 Docker Compose 配置

```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - ./:/var/www/html:delegated
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=production
      - DB_HOST=db
      - REDIS_HOST=redis
    depends_on:
      - db
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./deploy/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./public:/var/www/html/public:ro
      - ./storage/app/public:/var/www/html/storage/app/public:ro
      - certbot-data:/etc/letsencrypt
    depends_on:
      - app

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
    command: --default-authentication-plugin=mysql_native_password

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    command: php artisan queue:work --sleep=3 --tries=3
    volumes:
      - ./:/var/www/html:delegated
    depends_on:
      - db
      - redis

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    command: php artisan schedule:work
    volumes:
      - ./:/var/www/html:delegated
    depends_on:
      - db
      - redis

volumes:
  db_data:
  redis_data:
  certbot-data:
```

---

## 十、迁移步骤计划

### Phase 1: 基础设施（1-2 周）

1. 搭建 Laravel 项目骨架
2. 配置 Docker 开发环境
3. 创建数据库 Migration（映射现有表结构）
4. 配置 Redis、Queue、Mail 等基础设施
5. 实现用户认证系统（兼容旧密码）

### Phase 2: 数据迁移（1 周）

1. 编写数据迁移脚本（serialize → JSON）
2. 验证数据完整性
3. 测试附件文件路径兼容性
4. 备份现有数据库和文件

### Phase 3: 前台功能（2-3 周）

1. 首页文章列表 + 分页
2. 文章详情页 + 评论
3. 分类/标签/归档/搜索
4. RSS / Sitemap
5. 附件下载
6. URL 路由测试（100% 兼容旧链接）

### Phase 4: 后台功能（2-3 周）

1. Filament 后台搭建
2. 文章/评论/分类/标签 CRUD
3. 附件管理 + 缩略图生成
4. 用户/链接管理
5. 模板变量编辑
6. 系统设置
7. 缓存管理

### Phase 5: 测试与优化（1-2 周）

1. 功能测试（对照现有系统）
2. SEO 测试（URL、Meta、Sitemap）
3. 性能测试（Load Testing）
4. 安全审计
5. 数据一致性校验

### Phase 6: 上线切换（1 周）

1. 生产环境部署
2. 数据库最终同步
3. DNS 切换
4. 监控与回滚预案

---

## 十一、技术栈总结

| 层级 | 技术选型 | 说明 |
|------|----------|------|
| 框架 | Laravel 10/11 + PHP 8.2 | 最新 LTS 版本 |
| 后台 | Filament 3 | 快速搭建管理后台 |
| 数据库 | MySQL 8.0 / MariaDB 10.6 | 保留现有数据 |
| 缓存 | Redis 7 | 缓存 + Session + Queue |
| 搜索 | Laravel Scout + MeiliSearch | 替代 LIKE 搜索 |
| 队列 | Redis Queue + Laravel Horizon | 监控队列状态 |
| 文件存储 | Laravel Storage (本地/S3) | 附件存储 |
| 图片处理 | Intervention Image 3 | 缩略图生成 |
| 富文本 | Tiptap / TinyMCE | 文章编辑器 |
| 前端 | Tailwind CSS + Alpine.js | 现代化前端（可选 Vue/React） |
| 部署 | Docker + Nginx + PHP-FPM | 容器化部署 |
| CI/CD | GitHub Actions / GitLab CI | 自动化测试部署 |
| 监控 | Laravel Telescope + Sentry | 错误监控和调试 |
| 备份 | spatie/laravel-backup | 自动数据库备份 |

---

## 十二、注意事项与风险

### 12.1 数据风险

- **附件路径**: 现有附件路径格式必须保持兼容，否则旧文章中的 `[attach=xx]` 会失效
- **密码哈希**: 必须确认原系统密码算法，制定兼容方案
- **字符编码**: 原系统可能是 GBK/Latin1，迁移时需统一转为 UTF-8
- **序列化数据**: `attachments` 字段从 PHP serialize 转为 JSON

### 12.2 SEO 风险

- **URL 必须 100% 兼容**: 任何 URL 变更都会导致搜索引擎排名下降
- **Meta 信息保持**: title、description、keywords 必须与原系统一致
- **Sitemap 更新**: 上线后立即提交新 Sitemap 到搜索引擎
- **301 重定向**: 如有不可避免的 URL 变更，必须配置 301 重定向

### 12.3 性能风险

- **缓存预热**: 上线前需要预热所有缓存，避免首次访问大量查询数据库
- **数据库压力**: 迁移期间读写分离，避免影响线上服务
- **附件迁移**: 大量附件文件复制可能耗时较长，考虑增量同步

### 12.4 功能取舍

- **WAP 版**: 已废弃，不迁移
- **Trackback**: 现代博客已很少使用，可考虑移除或保留只读
- **SQLyog Tunnel**: 删除，不迁移
- **插件系统**: 原系统插件系统复杂，评估是否必要迁移

---

## 十三、现有 Bug 修复记录（迁移时需确保已修复）

| Bug | 影响文件 | 修复状态 | Laravel 中是否自动解决 |
|-----|----------|----------|----------------------|
| `preg_replace /e` 废弃 | 多处 | 已修复（callback） | ✅ Eloquent 不需要 |
| `mysql_*` 函数废弃 | `func_db_mysql.php` | 已迁移 PDO | ✅ Laravel 使用 PDO |
| `session_register` 废弃 | `global.php` | 已修复 | ✅ Laravel Session 管理 |
| `[attach=xx]` 解析失败 | `index.php`, `global.php` | 已修复 | 需实现附件解析逻辑 |
| 后台保存白屏 | `adminfunctions.php` | 已修复 | ✅ Filament 不需要 |
| `flock()` 类型错误 | `adminfunctions.php` | 已修复 | ✅ Laravel Log 不需要 |
| 附件反序列化错误 | 多处 | 已修复 | ✅ 使用 JSON |
| 前台闪烁问题 | `footer.php` | 已处理（禁用 PJAX） | ✅ 不使用 PJAX |

---

## 附录：原系统关键配置项

```php
// 需要从原 settings 表迁移的配置
$options = [
    'name' => '站点名称',
    'url' => '站点URL',
    'meta_keywords' => '默认关键词',
    'meta_description' => '默认描述',
    'title_keywords' => '标题后缀',
    'icp' => '备案号',
    'templatename' => '模板名',
    'timeformat' => '时间格式',
    'normaltime' => '文章时间格式',
    'comment_min_len' => 4,
    'comment_max_len' => 1000,
    'article_comment_num' => 20, // 每页评论数
    'article_num' => 10, // 首页文章数
    'related_shownum' => 5, // 相关文章数
    'related_title_limit' => 30,
    'title_limit' => 50,
    'attachments_dir' => 'attachments/', // 附件目录
    'attachments_thumbs' => 1, // 是否生成缩略图
    'attachments_thumbs_size' => '500x376', // 缩略图尺寸
    'attachments_display' => 0, // 附件显示方式
    'comment_order' => 1, // 评论排序
    'trackback_order' => 0, // Trackback排序
    'comment_order' => 1,
    'showmsg' => 0, // 是否显示跳转消息
    'enable_trackback' => 1,
    'enable_comment' => 1,
    'close_comment' => 0, // 全局关闭评论
    'seccode' => 1, // 验证码
    'gzipcompress' => 1, // Gzip压缩
    'rewrite_enable' => 1, // 伪静态
    'show_debug' => 0, // 调试信息
    'show_calendar' => 1, // 显示日历
    'show_categories' => 1, // 显示分类
    'show_archives' => 1, // 显示归档
    'show_statistics' => 1, // 显示统计
    'hottags_shownum' => 20, // 热门标签数
    'recentcomment_num' => 5, // 最新评论数
    'sidebarlinknum' => 10, // 侧边栏链接数
    'wap_enable' => 0, // WAP（已废弃）
    'rss_enable' => 1,
    'server_timezone' => '8', // 时区偏移
    'article_order' => 'dateline', // 文章排序字段
];

// 模板变量（stylevars 表）
$stylevar = [
    'huangjinlian', 'huangjinlian2', 'huangjinlian3', // 友情链接区块
    'aff_link', 'ggad1', 'ggad2', 'ggad3', // 广告位
    'site_left', 'weibo_qq', 'top_banner', // 侧边栏/顶部内容
    'sina_shared_button', 'readability', // 分享/阅读按钮
    'include_jquery', 'jquery_tab', // jQuery 配置
    'navbar_link', // 导航栏额外链接
];
```

---

**文档版本**: 1.0
**创建日期**: 2025-07-09
**适用项目**: neatstudio.com (SaBlog-X → Laravel 迁移)

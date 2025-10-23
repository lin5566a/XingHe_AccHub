# 星河系统 (XingHe System)

一个基于 ThinkPHP 5.1 开发的现代化支付管理系统，支持多端登录、商品管理、订单处理、用户管理等功能。

## 🚀 功能特性

### 核心功能
- **用户管理**: 用户注册、登录、多端登录支持
- **商品管理**: 商品分类、库存管理、价格设置
- **订单系统**: 订单创建、支付处理、状态跟踪
- **支付集成**: 多种支付方式支持
- **会员系统**: 会员等级、折扣管理
- **数据统计**: 销售统计、流量分析

### 高级功能
- **多端登录**: 支持多设备同时登录，会话管理
- **商品折扣**: 时间限制的商品折扣系统
- **视频教程**: 商品视频教程管理
- **访客追踪**: 用户行为分析和统计
- **安全防护**: XSS防护、CSRF保护、SQL注入防护
- **自动化脚本**: 订单检查、充值监控、系统维护

## 📋 系统要求

- **PHP**: >= 5.6 (推荐 7.0+)
- **MySQL**: >= 5.6 (推荐 5.7+)
- **Web服务器**: Apache 2.4+ 或 Nginx 1.16+
- **扩展**: PDO, OpenSSL, cURL, GD, mbstring

> **注意**: 项目已针对 PHP 5.6 和 MySQL 5.6 进行优化，详细兼容性说明请查看 [PHP56_COMPATIBILITY.md](PHP56_COMPATIBILITY.md)

## 🛠️ 安装部署

### 1. 克隆项目
```bash
git clone https://github.com/lin5566a/XingHe_AccHub.git
cd xinghe-payment-system
```

### 2. 安装依赖
```bash
composer install
```

### 3. 配置数据库
```bash
# 复制配置文件
cp application/database.example.php application/database.php
cp application/config.example.php application/config.php

# 编辑数据库配置
vim application/database.php
```

### 4. 创建数据库
```sql
-- 创建数据库
CREATE DATABASE `your_database_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 导入完整数据库结构
source database_complete.sql;
```

### 5. 设置目录权限
```bash
# 设置运行时目录权限
chmod -R 755 runtime/
chmod -R 755 public/uploads/
chmod -R 755 public/static/
```

### 6. 配置Shell脚本
```bash
# 复制shell脚本示例文件
cp application/shell/CheckOrder.sh.example application/shell/CheckOrder.sh
cp application/shell/CheckRechargeOrder.sh.example application/shell/CheckRechargeOrder.sh
cp application/shell/monitor.sh.example application/shell/monitor.sh

# 修改脚本中的路径配置
vim application/shell/CheckOrder.sh
vim application/shell/CheckRechargeOrder.sh
vim application/shell/monitor.sh

# 设置执行权限
chmod +x application/shell/*.sh
```

### 7. 配置Web服务器

#### Apache 配置
确保启用了 `mod_rewrite` 模块，项目已包含 `.htaccess` 文件。

#### Nginx 配置
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 📁 项目结构

```
xinghe-payment-system/
├── application/                 # 应用目录
│   ├── admin/                  # 后台管理模块
│   │   ├── controller/         # 控制器
│   │   ├── model/             # 模型
│   │   └── validate/           # 验证器
│   ├── index/                  # 前台模块
│   │   └── controller/         # 控制器
│   ├── common/                 # 公共模块
│   │   ├── constants/          # 常量定义
│   │   ├── model/             # 公共模型
│   │   ├── service/           # 服务层
│   │   ├── traits/            # 特性
│   │   └── utils/             # 工具类
│   ├── command/                # 命令行工具
│   ├── shell/                  # Shell脚本
│   │   ├── CheckOrder.sh.example        # 订单检查脚本示例
│   │   ├── CheckRechargeOrder.sh.example # 充值订单检查脚本示例
│   │   └── monitor.sh.example           # 监控脚本示例
│   ├── database.example.php   # 数据库配置示例
│   ├── config.example.php      # 应用配置示例
│   └── route.php              # 路由配置
├── public/                     # Web根目录
│   ├── static/                # 静态资源
│   ├── uploads/               # 上传文件
│   ├── index.php              # 入口文件
│   └── .htaccess              # Apache重写规则
├── runtime/                    # 运行时文件
├── thinkphp/                   # ThinkPHP框架
├── vendor/                     # Composer依赖
├── .gitignore                  # Git忽略文件
├── .htaccess                   # 根目录重写规则
├── composer.json               # Composer配置
├── SHELL_SCRIPTS_GUIDE.md      # Shell脚本使用指南
└── README.md                   # 项目说明
```

## 🔧 配置说明

### 数据库配置
编辑 `application/database.php`:
```php
return [
    'type'     => 'mysql',
    'hostname' => '127.0.0.1',
    'database' => 'your_database_name',
    'username' => 'your_username',
    'password' => 'your_password',
    'hostport' => '3306',
    'prefix'   => 'epay_',
    'charset'  => 'utf8mb4',
];
```

### 应用配置
编辑 `application/config.php`:
```php
return [
    'app_debug' => false,  // 生产环境请设置为 false
    'session' => [
        'domain' => '.your-domain.com',  // 修改为您的域名
    ],
];
```

## 📊 数据库表结构

### 主要数据表
- `epay_users` - 用户表
- `epay_user_sessions` - 用户会话表
- `epay_products` - 商品表
- `epay_product_orders` - 订单表
- `epay_categories` - 分类表
- `epay_member_levels` - 会员等级表
- `epay_payment_channels` - 支付渠道表

### 功能扩展表
- `epay_visitor_records` - 访客记录表
- `epay_admin_logs` - 管理员日志表
- `epay_system_configs` - 系统配置表

## 🔐 安全特性

- **输入验证**: 所有用户输入都经过验证和过滤
- **SQL注入防护**: 使用参数化查询
- **XSS防护**: 输出内容自动转义
- **CSRF保护**: 表单令牌验证
- **文件上传安全**: 文件类型和大小限制
- **访问控制**: 敏感目录访问限制

## 📈 API接口

### 用户相关
- `POST /v1/user/register` - 用户注册
- `POST /v1/user/login` - 用户登录
- `POST /v1/user/logout` - 用户登出
- `GET /v1/user/info` - 获取用户信息

### 商品相关
- `GET /v1/product/list` - 商品列表
- `GET /v1/product/detail` - 商品详情
- `POST /v1/order/create` - 创建订单

### 管理后台
- `GET /admin/product/list` - 商品管理
- `POST /admin/product/discount/set` - 设置商品折扣
- `GET /admin/order/list` - 订单管理

## 🚀 部署建议

### 生产环境
1. **关闭调试模式**: 设置 `app_debug => false`
2. **配置HTTPS**: 使用SSL证书
3. **设置缓存**: 启用Redis或Memcached
4. **定期备份**: 数据库和文件备份
5. **监控日志**: 设置日志监控和告警

### 性能优化
1. **启用OPcache**: PHP字节码缓存
2. **CDN加速**: 静态资源CDN分发
3. **数据库优化**: 索引优化、查询优化
4. **缓存策略**: 页面缓存、数据缓存

## 🤝 贡献指南

1. Fork 项目
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开 Pull Request

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

## 📞 技术支持与合作

### 🛠️ 定制开发
专业团队提供个性化开发服务，根据您的业务需求定制专属解决方案。

### 💳 支付通道对接
快速对接第三方支付平台，支持多种支付方式，确保交易安全稳定。

### 🤝 业务合作
多种合作模式，共创双赢。我们提供技术支持和业务合作机会。

### 📱 联系方式
- **QQ**: 3909001743
- **Telegram**: @sy9088
- **Issues**: [GitHub Issues](https://github.com/your-username/xinghe-payment-system/issues)
- **文档**: [项目Wiki](https://github.com/your-username/xinghe-payment-system/wiki)

## 🙏 致谢

感谢以下开源项目：
- [ThinkPHP](https://github.com/top-think/think) - PHP框架
- [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) - Excel处理
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - 邮件发送

---

⭐ 如果这个项目对您有帮助，请给我们一个星标！


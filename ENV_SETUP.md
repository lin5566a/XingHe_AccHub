# 环境配置示例文件

## 数据库配置
请复制 `application/database.example.php` 为 `application/database.php` 并修改以下配置：

```php
// 数据库配置
'hostname' => '127.0.0.1',        // 数据库服务器地址
'database' => 'your_database_name', // 数据库名称
'username' => 'your_username',     // 数据库用户名
'password' => 'your_password',     // 数据库密码
'hostport' => '3306',              // 数据库端口
'prefix'   => 'epay_',             // 数据表前缀
'charset'  => 'utf8',              // 字符集（MySQL 5.6 使用 utf8）
```

## 应用配置
请复制 `application/config.example.php` 为 `application/config.php` 并修改以下配置：

```php
// 会话域名配置
'session' => [
    'domain' => '.your-domain.com', // 请修改为您的域名
],

// 应用调试模式（生产环境请设置为 false）
'app_debug' => false,
```

## 数据库初始化
1. 创建数据库并导入 `database_complete.sql`（完整数据库结构）
2. 确保数据库字符集为 `utf8`（MySQL 5.6 兼容）


## 目录权限
确保以下目录具有写入权限：
- `/runtime/` - 运行时文件
- `/public/uploads/` - 上传文件
- `/public/static/` - 静态文件缓存

## Shell脚本配置
请复制以下shell脚本示例文件并修改路径：

```bash
# 复制shell脚本示例文件
cp application/shell/CheckOrder.sh.example application/shell/CheckOrder.sh
cp application/shell/CheckRechargeOrder.sh.example application/shell/CheckRechargeOrder.sh
cp application/shell/monitor.sh.example application/shell/monitor.sh

# 修改脚本中的路径
vim application/shell/CheckOrder.sh
vim application/shell/CheckRechargeOrder.sh
vim application/shell/monitor.sh

# 设置执行权限
chmod +x application/shell/*.sh
```

### Shell脚本说明
- `CheckOrder.sh` - 订单检查脚本，用于检查订单状态
- `CheckRechargeOrder.sh` - 充值订单检查脚本
- `monitor.sh` - 监控脚本，确保其他脚本正常运行

### 定时任务配置
```bash
# 添加到crontab
crontab -e

# 每分钟检查一次脚本是否运行
* * * * * /path/to/your/project/application/shell/monitor.sh
```

## Web服务器配置
- Apache: 确保启用了 `mod_rewrite` 模块
- Nginx: 配置 URL 重写规则

## 安全配置
- 修改默认管理员密码
- 配置 HTTPS（推荐）
- 定期备份数据库

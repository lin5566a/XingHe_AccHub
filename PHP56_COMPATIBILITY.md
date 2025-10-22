# PHP 5.6 兼容性说明

## 概述
本项目已针对 PHP 5.6 和 MySQL 5.6 进行了优化和测试，确保在较老的环境中也能正常运行。

## 兼容性调整

### 1. 数据库字符集
- **MySQL 5.6**: 使用 `utf8` 字符集和 `utf8_general_ci` 排序规则
- **MySQL 5.7+**: 推荐使用 `utf8mb4` 字符集和 `utf8mb4_unicode_ci` 排序规则

### 2. 数据库配置
```php
// application/database.php
return [
    'charset' => 'utf8',  // MySQL 5.6 使用 utf8
    'params' => [
        \PDO::ATTR_EMULATE_PREPARES => true,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"  // 注意是 utf8
    ],
];
```

### 3. 数据库表结构
- 所有表都使用 `utf8` 字符集
- 索引长度限制在 767 字节以内（MySQL 5.6 限制）
- 使用 `utf8_general_ci` 排序规则

### 4. PHP 版本要求
- **最低版本**: PHP 5.6
- **推荐版本**: PHP 7.0+
- **必需扩展**: PDO, OpenSSL, cURL, GD, mbstring

## 安装步骤

### 1. 环境准备
```bash
# 检查 PHP 版本
php -v

# 检查必需扩展
php -m | grep -E "(pdo|openssl|curl|gd|mbstring)"
```

### 2. 数据库创建
```sql
-- 创建数据库（MySQL 5.6）
CREATE DATABASE `your_database_name` 
DEFAULT CHARACTER SET utf8 
COLLATE utf8_general_ci;

-- 导入完整数据库结构
USE your_database_name;
SOURCE database_complete.sql;
```

### 3. 配置文件
```bash
# 复制配置文件
cp application/database.example.php application/database.php
cp application/config.example.php application/config.php

# 编辑数据库配置
vim application/database.php
```

### 4. 目录权限
```bash
# 设置目录权限
chmod -R 755 runtime/
chmod -R 755 public/uploads/
chmod -R 755 public/static/
```

## 注意事项

### 1. 字符集限制
- MySQL 5.6 的 `utf8` 字符集不支持 4 字节 Unicode 字符（如 emoji）
- 如需支持 emoji，建议升级到 MySQL 5.7+ 并使用 `utf8mb4`

### 2. 性能考虑
- PHP 5.6 性能相对较低，建议在生产环境中升级到 PHP 7.0+
- 启用 OPcache 可以显著提升性能

### 3. 安全更新
- PHP 5.6 已停止安全更新，建议尽快升级
- 确保 Web 服务器和 MySQL 版本保持最新

## 升级建议

### 1. PHP 升级路径
```
PHP 5.6 → PHP 7.0 → PHP 7.2 → PHP 7.4 → PHP 8.0+
```

### 2. MySQL 升级路径
```
MySQL 5.6 → MySQL 5.7 → MySQL 8.0+
```

### 3. 升级后调整
升级到更高版本后，可以：
- 将数据库字符集改为 `utf8mb4`
- 更新数据库配置中的字符集设置
- 利用新版本 PHP 的性能优化特性

## 故障排除

### 1. 字符集问题
```sql
-- 检查数据库字符集
SHOW VARIABLES LIKE 'character_set%';

-- 检查表字符集
SHOW CREATE TABLE epay_users;
```

### 2. 连接问题
```php
// 检查数据库连接
try {
    $pdo = new PDO($dsn, $username, $password);
    echo "数据库连接成功";
} catch (PDOException $e) {
    echo "连接失败: " . $e->getMessage();
}
```

### 3. 权限问题
```bash
# 检查目录权限
ls -la runtime/
ls -la public/uploads/

# 修复权限
chmod -R 755 runtime/
chmod -R 755 public/uploads/
```

## 支持

如果您在使用过程中遇到问题，请：
1. 检查 PHP 和 MySQL 版本
2. 确认所有必需扩展已安装
3. 检查数据库字符集配置
4. 查看错误日志文件

---

**注意**: 虽然项目支持 PHP 5.6，但强烈建议升级到更新的版本以获得更好的性能和安全性。

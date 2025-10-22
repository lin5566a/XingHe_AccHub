# IP数据库文件说明

## 文件要求
请将 `ip2region.xdb` 文件下载到此目录中。

## 下载地址
1. 访问 ip2region 官方仓库：https://github.com/lionsoul2014/ip2region
2. 下载 `data/ip2region.xdb` 文件
3. 将文件重命名为 `ip2region.xdb` 并放置在此目录中

## 文件路径
```
data/
└── ip2region.xdb
```

## 注意事项
- 文件大小约为 4MB
- 确保文件权限允许 PHP 读取
- 定期更新数据库文件以获得最新的IP地址信息

## 功能说明
此文件用于：
- 根据用户IP地址查询地理位置信息
- 实现安装包的地域限制功能
- 支持市级精度的地理位置识别

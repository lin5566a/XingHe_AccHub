# Shell脚本配置说明

## 📋 概述
本项目包含三个shell脚本，用于订单检查和系统监控。为了保护您的隐私，真实的脚本文件不会被上传到GitHub，而是提供示例文件供您配置。

## 📁 脚本文件

### 1. CheckOrder.sh
**功能**: 订单检查脚本
**说明**: 循环检查订单状态，确保订单能够正常处理

### 2. CheckRechargeOrder.sh
**功能**: 充值订单检查脚本
**说明**: 循环检查充值订单状态，确保充值流程正常

### 3. monitor.sh
**功能**: 监控脚本
**说明**: 监控其他脚本是否正常运行，如果脚本停止会自动重启

## 🔧 配置步骤

### 1. 复制示例文件
```bash
# 复制shell脚本示例文件
cp application/shell/CheckOrder.sh.example application/shell/CheckOrder.sh
cp application/shell/CheckRechargeOrder.sh.example application/shell/CheckRechargeOrder.sh
cp application/shell/monitor.sh.example application/shell/monitor.sh
```

### 2. 修改路径配置
编辑每个脚本文件，修改以下路径：

#### CheckOrder.sh
```bash
# 修改PHP路径
/usr/local/php-5.6/bin/php /path/to/your/project/think CheckOrder
```

#### CheckRechargeOrder.sh
```bash
# 修改PHP路径
/usr/local/php-5.6/bin/php /path/to/your/project/think CheckRechargeOrder
```

#### monitor.sh
```bash
# 修改脚本路径
shell_scripts=(
    "/path/to/your/project/application/shell/CheckOrder.sh"
    "/path/to/your/project/application/shell/CheckRechargeOrder.sh"
)

# 修改日志文件路径
log_file="/path/to/your/project/application/shell/monitor.log"
```

### 3. 设置执行权限
```bash
chmod +x application/shell/*.sh
```

## 🚀 运行方式

### 手动运行
```bash
# 运行订单检查脚本
./application/shell/CheckOrder.sh

# 运行充值订单检查脚本
./application/shell/CheckRechargeOrder.sh

# 运行监控脚本
./application/shell/monitor.sh
```

### 后台运行
```bash
# 后台运行订单检查脚本
nohup ./application/shell/CheckOrder.sh &

# 后台运行充值订单检查脚本
nohup ./application/shell/CheckRechargeOrder.sh &

# 后台运行监控脚本
nohup ./application/shell/monitor.sh &
```

### 定时任务配置
```bash
# 编辑crontab
crontab -e

# 添加定时任务（每分钟检查一次）
* * * * * /path/to/your/project/application/shell/monitor.sh
```

## 📊 监控和日志

### 查看运行状态
```bash
# 查看脚本是否在运行
ps aux | grep CheckOrder
ps aux | grep CheckRechargeOrder
ps aux | grep monitor
```

### 查看日志
```bash
# 查看监控日志
tail -f application/shell/monitor.log

# 查看系统日志
tail -f runtime/log/$(date +%Y%m%d).log
```

## ⚠️ 注意事项

### 1. 路径配置
- 确保所有路径都指向正确的项目目录
- PHP路径需要与您的环境匹配
- 日志文件路径需要有写入权限

### 2. 权限设置
- 脚本文件需要有执行权限
- 日志目录需要有写入权限
- 确保用户有权限运行PHP命令

### 3. 系统资源
- 脚本会持续运行，注意系统资源消耗
- 建议在服务器上运行，避免在开发环境长期运行
- 定期检查日志文件大小，避免占用过多磁盘空间

### 4. 安全考虑
- 不要将包含真实路径的脚本文件上传到公共仓库
- 定期检查脚本运行状态
- 监控系统资源使用情况

## 🔍 故障排除

### 脚本无法启动
1. 检查文件权限：`ls -la application/shell/`
2. 检查PHP路径：`which php`
3. 检查项目路径是否正确

### 脚本运行异常
1. 查看错误日志：`tail -f application/shell/monitor.log`
2. 检查PHP错误日志
3. 验证数据库连接

### 监控脚本不工作
1. 检查脚本路径配置
2. 验证pgrep命令是否可用
3. 检查日志文件权限

## 📞 技术支持

如果在配置过程中遇到问题，请联系：
- **QQ**: 3909001743
- **Telegram**: @sy9088

---

**注意**: 请根据您的实际环境修改脚本中的路径配置，确保系统能够正常运行。

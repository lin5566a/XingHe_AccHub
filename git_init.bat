@echo off
chcp 65001 >nul
echo === 星河支付系统 Git 初始化 ===

REM 检查是否已存在 .git 目录
if exist ".git" (
    echo ⚠️  项目已经是一个 Git 仓库
    set /p choice="是否要重新初始化？(y/N): "
    if /i not "%choice%"=="y" (
        echo 取消操作
        pause
        exit /b 1
    )
    rmdir /s /q .git
)

REM 初始化 Git 仓库
echo 📦 初始化 Git 仓库...
git init

REM 添加所有文件
echo 📁 添加文件到暂存区...
git add .

REM 创建初始提交
echo 💾 创建初始提交...
git commit -m "Initial commit: 星河支付系统

- 基于 ThinkPHP 5.1 的支付管理系统
- 支持多端登录、商品管理、订单处理
- 包含用户管理、支付集成、数据统计等功能
- 具备安全防护和性能优化特性"

echo.
echo ✅ Git 仓库初始化完成！
echo.
echo 📋 下一步操作：
echo 1. 添加远程仓库: git remote add origin ^<your-repo-url^>
echo 2. 推送到远程: git push -u origin main
echo 3. 配置 GitHub Pages 或其他部署服务
echo.
echo 🔧 配置说明：
echo - 复制 application/database.example.php 为 application/database.php
echo - 复制 application/config.example.php 为 application/config.php
echo - 修改数据库连接信息
echo - 导入数据库文件: database_product_discount.sql, database_user_sessions_simple.sql
echo.
echo 📖 详细说明请查看 README.md 和 ENV_SETUP.md
pause

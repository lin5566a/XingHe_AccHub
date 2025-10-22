#!/bin/bash
# Git 初始化脚本

echo "=== 星河支付系统 Git 初始化 ==="

# 检查是否已存在 .git 目录
if [ -d ".git" ]; then
    echo "⚠️  项目已经是一个 Git 仓库"
    read -p "是否要重新初始化？(y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "取消操作"
        exit 1
    fi
    rm -rf .git
fi

# 初始化 Git 仓库
echo "📦 初始化 Git 仓库..."
git init

# 添加所有文件
echo "📁 添加文件到暂存区..."
git add .

# 创建初始提交
echo "💾 创建初始提交..."
git commit -m "Initial commit: 星河支付系统

- 基于 ThinkPHP 5.1 的支付管理系统
- 支持多端登录、商品管理、订单处理
- 包含用户管理、支付集成、数据统计等功能
- 具备安全防护和性能优化特性"

echo ""
echo "✅ Git 仓库初始化完成！"
echo ""
echo "📋 下一步操作："
echo "1. 添加远程仓库: git remote add origin <your-repo-url>"
echo "2. 推送到远程: git push -u origin main"
echo "3. 配置 GitHub Pages 或其他部署服务"
echo ""
echo "🔧 配置说明："
echo "- 复制 application/database.example.php 为 application/database.php"
echo "- 复制 application/config.example.php 为 application/config.php"
echo "- 修改数据库连接信息"
echo "- 导入数据库文件: database_product_discount.sql, database_user_sessions_simple.sql"
echo ""
echo "📖 详细说明请查看 README.md 和 ENV_SETUP.md"

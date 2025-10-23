<template>
  <div class="technical-support-box">
    <div class="contact-container">
      <div class="contact-header">
        <i class="contact-icon el-icon-chat-dot-round"></i>
        <h4>技术支持与合作</h4>
      </div>
      <div class="service-cards">
        <div class="service-card">
          <div class="service-icon">🛠️</div>
          <div class="service-title">定制开发</div>
          <div class="service-desc">专业团队提供个性化开发服务</div>
        </div>
        <div class="service-card">
          <div class="service-icon">💳</div>
          <div class="service-title">支付通道对接</div>
          <div class="service-desc">快速对接第三方支付平台</div>
        </div>
        <div class="service-card">
          <div class="service-icon">🤝</div>
          <div class="service-title">业务合作</div>
          <div class="service-desc">多种合作模式，共创双赢</div>
        </div>
      </div>
      <div class="contact-methods">
        <div class="contact-item" @click="copyContact('3909001743', 'QQ')">
          <div class="contact-card">
            <div class="contact-main">
              <i class="el-icon el-icon-message"></i>
              <span class="contact-text">QQ: 3909001743</span>
            </div>
            <div class="copy-hint">点击复制</div>
          </div>
        </div>
        <div class="contact-item" @click="copyContact('@sy9088', 'Telegram')">
          <div class="contact-card">
            <div class="contact-main">
              <i class="el-icon el-icon-chat-dot-round"></i>
              <span class="contact-text">Telegram: @sy9088</span>
            </div>
            <div class="copy-hint">点击复制</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TechnicalSupport',
  methods: {
    async copyContact(text, type) {
      try {
        // 使用现代浏览器的 Clipboard API
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(text)
          this.$message.success(`${type} 已复制到剪贴板`)
        } else {
          // 降级方案：使用传统的 document.execCommand
          const textArea = document.createElement('textarea')
          textArea.value = text
          textArea.style.position = 'fixed'
          textArea.style.left = '-999999px'
          textArea.style.top = '-999999px'
          document.body.appendChild(textArea)
          textArea.focus()
          textArea.select()
          
          const successful = document.execCommand('copy')
          document.body.removeChild(textArea)
          
          if (successful) {
            this.$message.success(`${type} 已复制到剪贴板`)
          } else {
            this.$message.error('复制失败，请手动复制')
          }
        }
      } catch (err) {
        console.error('复制失败:', err)
        this.$message.error('复制失败，请手动复制')
      }
    }
  }
}
</script>
<style lang="scss" scoped>
.contact-container {
  margin-top: 24px;
  padding: 24px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px #0000001a;
  .contact-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
    h4{
      margin: 0;
      color: #1e293b;
      font-size: 18px;
      font-weight: 600
    }
    .contact-icon{
      font-size: 24px;
      color: #409eff;
      margin-right: 12px;
    }
  }
  .service-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    .service-card {
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      text-align: center;
      transition: all 0.3s ease;
      box-shadow: 0 1px 3px #0000001a;
      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px #00000026;
        border-color: #409eff;
    }
      .service-icon {
        font-size: 32px;
        margin-bottom: 12px;
      }
      .service-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
      }
      .service-desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
      }
    }
  }
  .contact-methods{
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    .contact-item{
        cursor: pointer;
        transition: all .3s ease;
        &:hover{
            transform: translateY(-2px);
        }
        .contact-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            min-width: 180px;
            transition: all .3s ease;
            box-shadow: 0 2px 4px #0000001a;
            &:hover{
                border-color: #409eff;
                box-shadow: 0 8px 16px #409eff33;
                .copy-hint{          
                    opacity: 1;          
                    color: #409eff;
                }
            }
            .contact-main {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 8px;
                .el-icon{
                  font-size: 18px;
                  color: #409eff;
                  margin-right: 8px;
                }
            }
            .copy-hint{
                font-size: 12px;
                color: #64748b;
                opacity: .8;
                transition: all .3s ease;
            }
        }
    }
  }
}
</style>

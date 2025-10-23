<template>
  <div class="user-guid-box">
    <div  class="instructions-container">
      <h4 >操作说明：</h4>
      <ol >
        <li >联系下方客服获取商户号和商户密钥</li>
        <li >将获取的商户号和密钥填入上方对应输入框</li>
        <li >
          根据业务需要启用微信支付、支付宝或USDT，并配置对应的产品编码
        </li>
        <li >
          产品编码用于区分不同的支付产品，请确保编码的唯一性
        </li>
        <li >点击"保存配置"按钮完成设置</li>
        <li >配置成功后，系统将自动对接相应的支付功能</li>
      </ol>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.user-guid-box{
    .instructions-container {
        margin-top: 30px;
        padding: 16px;
        background-color: #f8f8f8;
        border-radius: 4px;
        h4{
            margin-top: 0;
            margin-bottom: 12px;
            color: #303133;
            font-size: 14px;
        }
        ol{
            margin: 0;
            padding-left: 16px;
            li{
                line-height: 1.6;
                margin-bottom: 8px;
                color: #606266;
                font-size: 13px;
            }
        }
    }
}

</style>

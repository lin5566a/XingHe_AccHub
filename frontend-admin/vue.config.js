const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    client: {
        overlay: false, // 禁用 Webpack 错误覆盖层
    },
  },
  configureWebpack: {
  
  },
})

<template>
    <div class="character-replace-content p-3.5">
      <div class="space-y-3.5">
        <div class="space-y-3.5">
          <div>
            <div class="block text-sm font-medium text-gray-700 mb-3.5">输入数据</div>
            <div class="relative">
              <el-input type="textarea"
                class="text-base textarea-input w-full rounded-lg transition-shadow"
                :rows="7"
                v-model="inputData"
                placeholder="请输入需要处理的内容"/>
                <i class='old-copy iconfont icon-copy' @click="copyOldData" v-if='inputData'></i>                
                <span v-if="isCopyOld" class="old-copy-success primary-color">✓</span>
            </div>
          </div>
          <div class="flex py-3-5 gap-3.5 items-center">
            <div class="flex items-center flex-col sm:flex-row gap-3.5 sm:flex-1 w-full">
                <div class="flex items-center flex-col sm:flex-row sm:flex-1 w-full gap-3.5 ">
                    <el-input type="text" class="input-text flex-grow w-full sm:flex-1" placeholder="请输入替换前字符" v-model="replaceData"/>
                    <span class="font-medium ">替换为</span>
                    <el-input type="text" class="input-text flex-grow w-full sm:flex-1" placeholder="替换后字符" v-model="replaceString"/>
                </div>
                <div class="flex items-center flex-row  gap-3.5">
                    <el-button type="primary" class="btn-replace bg-from-blue-600 px-2 sm:px-4 text-sm sm:text-base rounded-md " @click="handleExecute"> 执行</el-button>
                    <el-button type="primary" class="btn-replace bg-from-blue-600 px-2 sm:px-4 text-sm sm:text-base  rounded-md " @click="handleReset">重置</el-button>
                    <el-button type="primary" class="btn-replace icon-btn bg-from-blue-600 rounded-md" @click="handleDownload" v-if='outputData'>
                        <i class="icon-download iconfont" style="font-size: 16px;"></i>
                    </el-button>
                </div>
            </div>
            
          </div>
          <div class="relative">
            <div class="flex justify-between items-center mb-3.5">
              <div class="text-sm font-medium text-gray-700">输出结果</div>
              <div class="copy-btn bg-blue-50 text-blue-600 text-sm px-3 py-1 rounded flex items-center gap-1"  @click="handleCopy" v-if='outputData'>
                  <i class='iconfont icon-copy mr-1' style="font-size: 12px"></i>
                  复制文本<span v-if="isCopy" class="ml-1"> ✓</span>
              </div>
            </div>
            <el-input type="textarea" class="text-base textarea-input result-input w-full  rounded-lg  font-mono" :readonly="true" :rows="7" v-model="outputData" placeholder=""/>
          </div>
        </div>
      </div>
    </div>
  </template>
  <script setup>
    import { ref } from 'vue'
    import { ElMessage } from 'element-plus'
    
    const inputData = ref('')
    const replaceData = ref('')
    const outputData = ref('')
    const replaceString = ref('')
    const isCopy = ref(false)
    const isCopyOld = ref(false)
    
    const handleExecute = () => {   
      if (!inputData.value || !replaceData.value || !replaceString.value) return
      outputData.value = inputData.value.split(replaceData.value).join(replaceString.value)
    }
    
    const handleReset = () => {
      inputData.value = ''
      replaceData.value = ''
      outputData.value = ''
      replaceString.value =''
    }
    const copyOldData=()=>{
        if (!inputData.value) {
        ElMessage.warning('没有可复制的内容')
        return
      }
      navigator.clipboard.writeText(inputData.value).then(() => {
        isCopyOld.value = true
        setTimeout(() => {
          isCopyOld.value = false
        }, 3000)
      }).catch(() => {
        ElMessage.error('复制失败')
      })
    }
    const handleCopy = () => {
      if (!outputData.value) {
        ElMessage.warning('没有可复制的内容')
        return
      }
      navigator.clipboard.writeText(outputData.value).then(() => {
        isCopy.value = true
        setTimeout(() => {
          isCopy.value = false
        }, 3000)
      }).catch(() => {
        ElMessage.error('复制失败')
      })
    }
  
    const handleDownload = () => {
      if (!outputData.value) {
        ElMessage.warning('没有可下载的内容')
        return
      }
      const blob = new Blob([outputData.value], { type: 'text/plain' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = '处理结果.txt'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      ElMessage.success('下载成功')
    }
  </script>
  
  <style scoped lang="scss">
    .character-replace-content{
        .old-copy{
          top:0.5rem;
          right:0.5rem;
          padding:0.5rem;
          font-size:16px;
          color:#6b7280;
          position: absolute;
            &:hover{
            color:#2563eb;
          }
        }
        .old-copy-success{
          position: absolute;
          top:-0.2rem;
          right: 0.2rem;      
        }
        .btn-replace{
          height: 42px;
          width:96px;
          margin-left: 0;
          &.icon-btn{
            width: 48px;
          }
        }
        .copy-btn{
          cursor: pointer;
          &:hover{
            background: #dbeafe;
          }
        }
        .input-text{
            // flex:1;
            height: 42px;
            font-size: 1rem;
            border-radius: 0.375rem;
        }
        // :deep(.input-text){
        //     flex:1;
        //     height: 42px;
        //     font-size: 1rem;
        //     border-radius: 0.375rem;
        // }
        :deep(.el-textarea__inner:focus) {
            box-shadow: 0 0 0 2px var(--primary-color) inset;
            outline: none;
        }
        // .textarea-input{
          
        // }
        .result-input{
            :deep(.el-textarea__inner:focus) {
                box-shadow: 0 0 0 2px #2563eb inset;
                outline: none;
            }
            :deep(.el-textarea__inner) {
                background-color: #f9fafb;
            }
        }
    }
      
  </style>
  
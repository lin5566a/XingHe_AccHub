<template>
    <div>
      <el-dialog
        title="视频管理"
        :visible.sync="dialogVisible"
        width="780px"
        @close="handleClose"
        :before-close="handleClose"
        :close-on-click-modal="false"
      >
        <div class="video-manage-box">
          <div class="goods-name">
            <span class="name-label">商品名称</span>
            <span class="name-value">{{ updateVideoData.name }}</span>
          </div>
  
          <div class="video-box">
            <!-- 上传框：只有没有 video 时才显示 -->
            <el-upload
                v-if="!video"
                class="upload-video"
                drag              
                :action="uploadAction"
                :headers="uploadHeaders"
                :before-upload="beforeUpload"
                :on-progress="handleProgress"
                :on-error="handleError"                
                :http-request="customUpload"
                name="video"
                accept="video/*"
                :show-file-list="false"
                :data="{product_id:updateVideoData.id}"
              >
              <div class="upload-video-box">
                <i class="upload-video-icon el-icon-video-camera"></i>
                <div class="upload-text">
                  点击或拖拽上传视频文件
                  <div class="upload-tip">每个商品最多1个视频，最大200MB</div>
                </div>
              </div>
            </el-upload>
  
            <!-- 预览框：上传后显示，替代上传框 -->
            <div class="video-list" v-else>
              <div class="video-item">
                <div class="thumb">
                  <video
                    ref="previewVideo"
                    :src="video.src"
                    controls
                    preload="metadata"
                    @error="handleVideoError"
                  ></video>
                </div>
                <div class="video-info">
                    <div class="info">
                        <div class="file-name" :title="video.name">{{ video.name }}</div>
                        <div class="file-meta" v-if="video.size">
                            <span class="size">{{ formattedSize }}</span>
                        </div>
                        <div class="progress-wrap" v-if="video.progress < 100 && !video.error && !video.serverUrl">
                            <div class="progress-bar">
                            <div class="progress-inner" :style="{ width: video.progress + '%' }"></div>
                            </div>
                            <div class="progress-text">{{ video.progress }}%</div>
                        </div>
                        <!-- <div class="upload-status" v-else-if="video.serverUrl">
                            <i class="el-icon-check status-success"></i>
                            <span class="status-text">上传完成</span>
                        </div> -->
                        <div class="upload-status" v-else-if="video.error">
                            <i class="el-icon-close status-error"></i>
                            <span class="status-text error">{{ video.error }}</span>
                        </div>
                        <!-- <div class="upload-status" v-else-if="video && !video.serverUrl && !video.error">
                            <i class="el-icon-upload2 status-waiting"></i>
                            <span class="status-text waiting">准备上传</span>
                        </div> -->
                    </div>
    
                    <div class="actions">
                        <el-button type="text" size="small" @click="handlePreview">预 览</el-button>
                        <el-button class="text-btn-danger" type="text" size="small" @click="handleRemove">删 除</el-button>
                    </div>
                </div>
                
              </div>
            </div>
          </div>
        </div>
  
        <span slot="footer" class="dialog-footer">
          <el-button @click="handleClose">取 消</el-button>
          <el-button type="primary" @click="sureVideo" :disabled="!video || loading ||video.serverUrl" :loading="loading">保 存</el-button>
        </span>
      </el-dialog>
    </div>
  </template>
<script>
export default {
  props: {
    dialogVisible: { type: Boolean, default: false },
    videoData: { type: Object, default: () => ({}) }
  },
  data() {
    return {
      loading: false,
      updateVideoData: {},
      video: null, // { file, name, size, src, progress, serverUrl, error }
      uploadHeaders: {
          Authorization: 'Bearer ' + this.$local.get('token')
      },
      uploadAction: this.$baseURL + '/api/admin/product/video/upload',
      uploadController: null, // 用于取消上传
    };
  },
  mounted() {
    this.updateVideoData = this.videoData || {};
    // 如果父组件传入已有视频 url，需要初始化显示（可选）
    if (this.videoData && (this.videoData.tutorial_video)) {
      this.video = {
        file: null,
        name: this.videoData.tutorial_video_name,
        size: this.videoData.tutorial_video_size || 0,
        src: this.$baseURL + this.videoData.tutorial_video,
        progress: 100,
        serverUrl: this.$baseURL + this.videoData.tutorial_video,
        error: null
      };
    }
  },
  computed: {
    formattedSize() {
      if (!this.video) return '';
      const s = this.video.size || 0;
      if (s < 1024) return s + ' B';
      if (s < 1024 * 1024) return (s / 1024).toFixed(1) + ' KB';
      return (s / (1024 * 1024)).toFixed(2) + ' MB';
    }
  },
  methods: {
    handleClose() {
      this.cancelUpload();
      this.cleanupVideo();
      this.$emit('handleClose');
    },
    
    sureVideo() {
      if (!this.video || !this.video.file) {
        this.$message.warning('请先选择视频文件');
        return;
      }
      
      if (this.video.serverUrl) {
        // 如果已经上传成功，直接保存
        this.handleClose();
        return;
      }
      
      // 开始上传视频
      this.uploadVideo();
    },

    // beforeUpload 验证文件
    beforeUpload(file) {
      const isVideo = file.type && file.type.startsWith('video/');
      const isLt = file.size / 1024 / 1024 <= 200;
      
      if (!isVideo) {
        this.$message.error('请选择视频文件');
        return false;
      }
      if (!isLt) {
        this.$message.error('视频大小不能超过 200MB');
        return false;
      }

      // 如果已有视频，先清理
      if (this.video) {
        this.cleanupVideo();
      }

      // 创建本地预览
      const src = URL.createObjectURL(file);
      this.video = {
        file,
        name: file.name,
        size: file.size,
        src,
        progress: 0,
        serverUrl: null,
        error: null
      };

      return false; // 阻止自动上传，只做预览
    },

    // 上传视频方法（点击保存按钮时调用）
    uploadVideo() {
      if (!this.video || !this.video.file) {
        this.$message.error('没有可上传的视频文件');
        return;
      }      
      const formData = new FormData();
      formData.append('video', this.video.file);
      formData.append('product_id', this.updateVideoData.id);
      
      // 创建 AbortController 用于取消上传
      this.uploadController = new AbortController();
      this.loading = true;
      
      // 模拟进度更新
      this.simulateProgress();
      
      fetch(this.uploadAction, {
        method: 'POST',
        headers: this.uploadHeaders,
        body: formData,
        signal: this.uploadController.signal
      })
      .then(res => {
        this.loading = false;
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then(response => {
        this.loading = false;
        this.handleSuccess(response, this.video.file);
        // 上传成功后，通知父组件并关闭对话框
        this.$emit('sureVideo', this.video);
        this.handleClose();
      })
      .catch(error => {
        this.loading = false;
        if (error.name === 'AbortError') {
          console.log('上传已取消');
          return;
        }
        console.error('上传失败:', error);
        this.handleError(error, this.video.file);
      });
    },

    // 自定义上传（保留原有方法，但不会被调用）
    customUpload(options) {
      console.log('开始上传:', options.file.name);
      
      const formData = new FormData();
      formData.append('video', options.file);
      formData.append('product_id', this.updateVideoData.id);
      
      // 创建 AbortController 用于取消上传
      this.uploadController = new AbortController();
      this.loading = true;
      
      // 模拟进度更新
      this.simulateProgress();
      
      fetch(this.uploadAction, {
        method: 'POST',
        headers: this.uploadHeaders,
        body: formData,
        signal: this.uploadController.signal
      })
      .then(res => {
        this.loading = false;
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then(response => {
        this.loading = false;
        this.handleSuccess(response, options.file);
        options.onSuccess(response, options.file);
      })
      .catch(error => {
        this.loading = false;
        if (error.name === 'AbortError') {
          console.log('上传已取消');
          return;
        }
        console.error('上传失败:', error);
        this.handleError(error, options.file);
        options.onError(error);
      });
    },
    // 上传进度回调
    handleProgress(event, file, fileList) {
      console.log(event,'==handleProgress===')
      if (!this.video) return;
      const percent = Math.floor(event.percent);
      this.video.progress = Math.min(percent, 99); // 最大99%，成功时设为100%
    },

    // 上传成功回调
    handleSuccess(res, file, fileList) {
      if (!this.video) return;      
      // 根据后端返回结构调整
      if(res.code == 1){
        const serverUrl = this.$baseURL +  res.data.video_url;
        this.$message.success(res.msg)
        this.video.progress = 100;
        this.video.serverUrl = serverUrl;
        this.video.src = serverUrl; // 切换为服务器地址
        this.video.error = null;        
        this.$emit('updateVideo')
      }else{
        this.$message.error(res.msg)
      }
    },

    handleError(err, file, fileList) {
      if (!this.video) return;      
      let errorMessage = '上传失败';
      if (err.message) {
        errorMessage = err.message;
      } else if (typeof err === 'string') {
        errorMessage = err;
      }
      
      this.video.error = errorMessage;
      this.video.progress = 0;
      this.$message.error(errorMessage);
    },

    handleRemove() {
      this.$confirm('确定要删除当前视频吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        console.log('删除视频:',this.video.serverUrl);
        if(this.video.serverUrl){
          this.deleteProductVideo()
        }
        this.cleanupVideo();
      
      }).catch(() => {
        this.$message.info('已取消删除');
      });
    },
    //删除商品视频
    deleteProductVideo(){
      // console.log(this.videoData,'===videoData')
      // return
      this.$api.deleteProductVideo({product_id:this.updateVideoData.id}).then(res=>{
        if(res.code == 1){
          this.$message.success(res.msg)
          // this.cancelUpload();
          this.cleanupVideo();
          this.$emit('updateVideo')
        }else{
          this.$message.error(res.msg)
        }
      })
    },

    handlePreview() {
      // if (!this.video || !this.video.serverUrl) {
      //   this.$message.warning('视频还未上传完成');
      //   return;
      // }
      let src = this.video.serverUrl || this.video.src;
      // 在新窗口播放视频
      const w = window.open('', '_blank');
      w.document.write(`
        <html>
          <head><title>视频预览</title></head>
          <body style="margin:0;padding:20px;background:#000;display:flex;justify-content:center;align-items:center;min-height:100vh;">
            <video src="${src}" controls autoplay style="max-width:100%;max-height:100%;"></video>
          </body>
        </html>
      `);
    },

   
    cancelUpload() {
      if (this.uploadController) {
        this.uploadController.abort();
        this.uploadController = null;
      }
    },

    handleVideoError() {
      this.$message.error('视频加载失败，请检查文件是否损坏');
    },

    cleanupVideo() {
      this.cancelUpload();
      if (this.video && this.video.file && this.video.src && this.isObjectURL(this.video.src)) {
        try { 
          URL.revokeObjectURL(this.video.src); 
        } catch (e) {
          console.warn('释放 blob URL 失败:', e);
        }
      }
      this.video = null;
    },

    isObjectURL(url) {
      return typeof url === 'string' && url.startsWith('blob:');
    },

    // 模拟进度更新（用于测试）
    simulateProgress() {
      if (!this.video) return;
      
      const step = () => {
        if (!this.video || this.video.progress >= 90) return;
        
        this.video.progress = Math.min(90, this.video.progress + Math.floor(Math.random() * 10) + 5);
        setTimeout(step, 200);
      };
      
      setTimeout(step, 500);
    }
  },
  beforeDestroy() {
    this.cleanupVideo();
  }
};
</script>
<style lang="scss">
    .video-manage-box{
        .el-upload{
            width: 100%;
            .el-upload-dragger {
                border: 2px dashed #d9d9d9;
                width: 100%;
            }
        }
    }
</style>
<style lang="scss" scoped>
.video-manage-box {
  .goods-name {
    display: flex;
    font-size: 14px;
    margin-bottom: 18px;
    .name-label {
      color: #606266;
      font-size: 14px;
      height: 32px;
      padding: 0 12px 0 0;
      width: 100px;
      display: flex;
      align-items: center;
      justify-content: flex-end;
    }
    .name-value {
      background-color: #f5f7fa;
      height: 32px;
      cursor: not-allowed;
      color: #a8abb2;
      display: flex;
      padding: 1px 11px;
      flex: 1;
      align-items: center;
      justify-content: flex-start;
      box-shadow: 0 0 0 1px #e4e7ed inset;
    }
  }

  .video-box {
    max-height: 500px;
    overflow-y: auto;

    .upload-video-box {
      display: flex;
      align-items: center;
      height: 100%;
      padding: 20px;
      box-sizing: border-box;
      .upload-video-icon {
        font-size: 32px;
        color: #909399;
      }
      .upload-text {
        color: #606266;
        font-size: 14px;
        margin-left: 12px;
        .upload-tip {
          color: #909399;
          font-size: 12px;
          margin-top: 4px;
          padding: 10px 0 0 0;
        }
      }
    }

    .video-list {
      margin-top: 12px;
      .video-item {
        display: flex;
        align-items: flex-start;
        padding: 12px;
        background: #fff;
        border-radius: 6px;
        border: 1px solid #edf0f5;

        .thumb {
          width: 260px;
          height: 150px;
          background: #f6f7fb;
          border-radius: 4px;
          overflow: hidden;
          display: flex;
          align-items: center;
          justify-content: center;
          video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
          }
        }
        .video-info{
            flex: 1;
        }

        .info {
          flex: 1;
          padding: 0 16px;
          .file-name {
            font-size: 14px;
            color: #303133;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 420px;
          }
          .file-meta {
            margin-top: 6px;
            font-size: 12px;
            color: #909399;
          }
          .progress-wrap {
            margin-top: 10px;
            display: flex;
            align-items: center;
          }
          .progress-bar {
            flex: 1;
            height: 8px;
            background: #f2f6fb;
            border-radius: 4px;
            overflow: hidden;
            margin-right: 10px;
          }
          .progress-inner {
            height: 100%;
            background: linear-gradient(90deg, #409eff, #66b1ff);
            width: 0%;
            transition: width 0.3s ease;
          }
          .progress-text {
            font-size: 12px;
            color: #606266;
            width: 48px;
            text-align: right;
          }
        }

        .actions {
          display: flex;
          align-items: flex-start;
          gap: 4px;
          margin-left: 20px;
          margin-top: 10px;
        }
        
        .upload-status {
          margin-top: 10px;
          display: flex;
          align-items: center;
          font-size: 12px;
          
          .status-success {
            color: #67c23a;
            margin-right: 4px;
          }
          
          .status-error {
            color: #f56c6c;
            margin-right: 4px;
          }
          
          .status-waiting {
            color: #909399;
            margin-right: 4px;
          }
          
          .status-text {
            color: #67c23a;
            
            &.error {
              color: #f56c6c;
            }
            
            &.waiting {
              color: #909399;
            }
          }
        }
      }
    }
  }
}
</style>

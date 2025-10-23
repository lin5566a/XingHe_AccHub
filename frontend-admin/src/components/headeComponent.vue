<template>
    <div class="header-box">
        <div class="header-left">
            <i class="nav-icon" :class=" isCollapse? 'el-icon-s-unfold':'el-icon-s-fold'" @click="setMenuOpen"></i>
            <div>
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item><span @click="backHome" class="breadcrumb-first" :class = "pathList.length>0?'islink':''"><i class="bread-icon el-icon-data-analysis"></i>首页</span></el-breadcrumb-item>
                    <el-breadcrumb-item v-for="(item,index) in pathList" :key="index"><a href="#"><i class="bread-icon" :class="item.meta.icon"></i>{{item.meta.title}}</a></el-breadcrumb-item>
                </el-breadcrumb>
            </div>
        </div>
        <div class="header-right"> 
            <el-dropdown trigger="click" class="mr20">
                <div class="msg-btn" @click="readNewMessage">
                    <el-badge :hidden="!messageData.stats ||  messageData.stats.total == 0" :value=" messageData.stats ? messageData.stats.total : ''" class="item" >
                        <span class="msg-icon-btn" :class="newMessages ?'shake':''"><i class="f18 el-icon-bell"></i></span>
                    </el-badge>
                </div>
                
                <el-dropdown-menu slot="dropdown">
                    <div class="flexX message-center-box" >                       
                        <MessageCenter :messageData="messageData" v-if="messageData" @readMessage="readMessage" @readAllMessage="readAllMessage" @messageReadAll="messageReadAll"></MessageCenter>
                    </div>
                </el-dropdown-menu>
            </el-dropdown>

            <el-dropdown trigger="click" @command="command" >
                <span class="el-dropdown-link">
                    <el-image class="head-image" :src="localUserInfo.avatar ? $baseURL+localUserInfo.avatar : headerData.headImg " fit="fill"></el-image> 
                    {{localUserInfo.username}}
                    <i class="el-icon-arrow-down el-icon--right"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item :command="lookInfo" icon="el-icon-user-solid">个人信息</el-dropdown-item>
                    <el-dropdown-item :command="setPwd" icon="el-icon-key">修改密码</el-dropdown-item>
                    <el-dropdown-item :command="logout" divided icon="el-icon-switch-button">退出登录</el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </div>
    </div>
</template>
<script>
import MessageCenter from "@/components/messageCenter/messageCenter.vue"
    export default {
        name: "headerComponent",
        components:{
            MessageCenter
        },
        data() {
            return {
                headerData:{
                    pathList:[],
                    headImg:'https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png'
                },
                localUserInfo: JSON.parse(this.$local.get('userInfo') || '{}'),
                newMessages: this.$local.get('newMessages')
            };
        },
        props:{
            isCollapse:{
                type:Boolean,
                default:false,
            },
            pathList:{
                type:Array,
                default(){
                    return []
                }
            },
            userInfo:{
                type:Object,
                default(){
                    return {}
                }
            },
            messageData:{
                type:Object,
                default(){
                    return {}
                }
            }
        },
        created() {
            // 监听用户信息更新事件
            this.$eventBus.$on('updateUserInfo', (userInfo) => {
                this.localUserInfo = userInfo
            })
            window.addEventListener('local-storage-changed', (e) => {
                const { key, newValue, oldValue, source } = e.detail;
                // 处理逻辑：更新 UI、触发 store、刷新缓存等
                if(key == 'newMessages'){
                    // if(oldValue){
                        this.newMessages = this.$local.get('newMessages')
                // console.log('local-storage-changed', key, newValue, oldValue, source);
                    // }
                }
            });
        },
        beforeDestroy() {
            // 组件销毁前移除事件监听
            this.$eventBus.$off('updateUserInfo')
            window.removeEventListener('local-storage-changed', this._onLocalStorageChanged);
  
        },
        mounted(){
        
        },
        methods: {
            backHome(){
                if (this.$route.path != '/data') {                    
                    this.$emit("setMenu")
                }
            },
            setMenuOpen(){
                this.$emit("setMenuOpen")
            },
            //退出登录            
            logout(){
                // console.log('退出登录')
                this.$local.remove("token")
                this.$local.remove("userInfo")
                
                // this.$local.remove("username")
                this.$router.push('/login')
            },
            lookInfo(){
                if ( this.$route.path != '/info') {
                    this.$router.push('/info')
                }    
            },
            setPwd(){
                if ( this.$route.path != '/resetPwd') {
                    this.$router.push('/resetPwd')
                }    
            },
            //菜单点击
            command(val){
                val()
            },
            //点击消息通知图标事件
            readNewMessage(){                  
                this.$local.set('newMessages',false)
            },
            //单个消息阅读
            readMessage(message){
                this.$emit('readMessage',message)
            },
            //分消息类型  全部消息查看
            readAllMessage(type){
                this.$emit('readAllMessage',type)
            },
            //消息全部已读
            messageReadAll(){
                this.$emit('messageReadAll')
            }
        },
    }
</script>
<style>
.el-breadcrumb__inner a, .el-breadcrumb__inner.is-link{
    color: #409eff;
    font-weight: 700 !important;
}
.el-breadcrumb__item:last-child .el-breadcrumb__inner a ,
.el-breadcrumb__item:last-child .el-breadcrumb__inner:hover ,
.el-breadcrumb__item:last-child .el-breadcrumb__inner a, 
.el-breadcrumb__item:last-child .el-breadcrumb__inner a:hover{
    color: #303133;
}

</style>
<style lang="scss" scoped>

    .header-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        
        width: 100%;
        box-sizing: border-box;
        .header-left{
            display: flex;
            align-items: center;
            .nav-icon{
                font-size: 20px;
                cursor: pointer;
                margin-right: 20px;
                color:#333;
            }
            .bread-icon{
                margin-right: 6px;
                font-size: 16px;
            }
            .breadcrumb-first{
                font-weight: 700;
                color:#303133;
                &.islink{
                    cursor: pointer;
                    color: #409eff ;
                }
               
                
            }
        }
        .header-right{
            display: flex;
            align-items: center;
            .head-image{
                height: 32px;
                width: 32px;
                border-radius: 50%;
                margin-right: 10px;
            }
            .el-dropdown-link{
                display: flex;
                align-items: center;
                cursor: pointer;
            }
            .msg-btn{
                padding: 8px;
                border-radius: 4px;
                color: #606266;
                &:hover {
                    background-color: #f5f5f5;
                    color:#3377FF;
                }
                .msg-icon-btn{
                    cursor: pointer;
                    transition: background-color .3s;
                    display: inline-flex;
                    line-height: 1;
                    position: relative;
                    vertical-align: top;
                    &.shake{
                        animation: sway 0.5s ease-in-out infinite;
                        will-change: transform;
                    }
                }
                /* 关键帧：左右平滑摆动 */
                @keyframes sway {
                    0%, 100% {
                        transform: rotate(0);
                    }
                    25% {
                        transform: rotate(-10deg);
                    }
                    75% {
                        transform: rotate(10deg);
                    }
                }
            }
          
            
        }
    }  
    .message-center-box{
        padding: 0 12px;
        width:362px;
    }
</style>
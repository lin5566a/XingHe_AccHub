<template>
  <div class="home" >
    <el-container v-if="menuData.isLogin">
      <el-aside width="200px">
          <div class="logo">
            <i class="logo-icon el-icon-monitor"></i>
            <span :class="menuData.isCollapse?'hide':''">{{$proName}}</span>
          </div>
        <Menu :activeMenu = menuData.activeMenu @selectMenu="selectMenu" :isCollapse="menuData.isCollapse"></Menu>
      
      </el-aside>
      <el-container>
        <el-header>
          <HeaderComponent :messageData="notificationsData.data" :userInfo = "userInfo" 
          @readMessage="readMessage" 
          @readAllMessage="readAllMessage"
          @messageReadAll="messageReadAll"
          @setMenu="setMenu" @setMenuOpen = "setMenuOpen" :isCollapse="menuData.isCollapse" :pathList="headerData.pathList"></HeaderComponent>
        </el-header>
        <el-main><router-view /></el-main>
      </el-container>
    </el-container>
    <div v-else>
      <span @click="gotoLogin">还未登录，去登录</span>
    </div>
  </div>
</template>

<script>
// import router from '@/router';
import Menu from '@/components/menu.vue';
import HeaderComponent from '@/components/headeComponent.vue';
import playNotificationSound from '@/utils/paySound'
export default {
  name: "HomeView",
  components: {
    Menu,
    HeaderComponent
  },
  data() {
    return {
      menuData:{
        isCollapse:false ,
        activeMenu:'',
        isLogin:false,
      },
      headerData:{
        pathList: [],
      },
      userInfo:{},
      notificationsData:{
        data:{},
        interval:null,
      },
    };
  },
  mounted(){
    this.getUserInfo()
    let token = this.$local.get('token');
    if(token){
      //设置默认路由
      this.menuData.isLogin = true
      if ( this.$route.path == '/') {
          this.$router.push('/data');
      }      
    }else{
      this.menuData.isLogin = false
      this.$router.push('/login');
    }
    clearInterval(this.notificationsData.interval)
      this.notificationsData.interval = setInterval(()=>{
        if(this.$local.get('token')){
          this.notificationsStats()
        }else{
          clearInterval(this.notificationsData.interval)
        }
          
      },5000)
      this.notifications()
      this.notificationsStats()
   
  
  },
  beforeDestroy() {
    clearInterval(this.notificationsData.interval)
  },
  methods: {
    getUserInfo(){
      let userInfo = this.$local.get("userInfo")
      this.userInfo = JSON.parse(userInfo)
      // console.log(this.userInfo,'this.userInfo')
    },
    setMenuOpen(){
      this.menuData.isCollapse = !this.menuData.isCollapse
    },
    selectMenu(key){
      this.$nextTick(()=>{
        this.menuData.activeMenu = key
      })
      
    },
    setMenu(){
      this.$nextTick(()=>{
        this.$router.push('/data');      
        this.selectMenu('data')
      })      
    },
    gotoLogin(){
      this.$router.push('/login');
    },
    //通知中心数据获取
    notifications(newManual,newReplen,newMessages){ 
      this.$api.notifications({}).then(res=>{
        if(res.code == 1){ 
          this.notificationsData.data = res.data;
          // console.log(this.notificationsData.data)
          if((res.data.sound_settings.manual_shipment_sound && newManual )|| (res.data.sound_settings.replenishment_sound && newReplen) ){
            playNotificationSound()
          }
          if(newMessages){             
            this.$local.set('newMessages',true)
          }
         
        }
      })
    },
    //获取消息个数
    notificationsStats(){
      this.$api.notificationsStats({}).then(res=>{
        // console.log(res)
        if(res.code == 1){ 
            // manual_shipment: 0replenishment: 1 total: 1
            let manual_shipment = this.$local.get('manual_shipment')
            let replenishment = this.$local.get('replenishment')
            let total = this.$local.get('total')
            this.$local.set('manual_shipment',res.data.manual_shipment)
            this.$local.set('replenishment',res.data.replenishment)
            this.$local.set('total',res.data.total)

          if(res.data.total > total){           
            let newManual = false
            let newReplen = false
            let newMessages = true
            if(res.data.manual_shipment > manual_shipment ){
              newManual = true
            }
            if(res.data.replenishment > replenishment ){
              newReplen = true
            }
            this.notifications(newManual,newReplen,newMessages)
          }          
        }
      })
    },
    //单个阅读消息
    readMessage(message){ 
      let data ={
          id:message.id,
      }
      this.$api.readNotifications(data).then(res=>{
          if(res.code == 1){ 
            this.$local.set('manual_shipment',res.data.stats.manual_shipment)
            this.$local.set('replenishment',res.data.stats.replenishment)
            this.$local.set('total',res.data.stats.total)
            this.$local.set('newMessages',false)
            this.notifications()
          }
      })
    },
    //分消息类型  全部消息查看
    readAllMessage(type){
      let data ={
          type:type,
      }
      this.$api.readAllNotifications(data).then(res=>{
        if(res.code == 1){
          this.$local.set('manual_shipment',res.data.stats.manual_shipment)
          this.$local.set('replenishment',res.data.stats.replenishment)
          this.$local.set('total',res.data.stats.total)
          this.$local.set('newMessages',false)
          this.notifications()
        }
      })
    },
    //消息全部已读
    messageReadAll(){
      this.$api.readAllMessage({}).then(res=>{
        if(res.code == 1){
          this.$local.set('manual_shipment',res.data.stats.manual_shipment)
          this.$local.set('replenishment',res.data.stats.replenishment)
          this.$local.set('total',res.data.stats.total)
          this.$local.set('newMessages',false)
          this.notifications()
        }
      })
    }
  },
  watch: {
    $route:{
      handler(to){
          this.headerData.pathList = []
          let route = to;
          let parentId = route.meta.parentId;
          let routerList = this.$router.options.routes;
          //导航选中状态
          if(route.path == '/'){
            this.menuData.activeMenu =  'data'
          }else{
            this.menuData.activeMenu =  route.path
          } 

          //页面头部面包屑路由展示
          for(let i = 0; i<routerList.length; i++){                
            if(routerList[i].children){ //获取二级路由          
              //获取父节点路由
              for(let j = 0; j < routerList[i].children.length; j++){
                if(routerList[i].children[j].meta.id == parentId){
                  this.headerData.pathList.push(routerList[i].children[j]);
                }
              }                      
            }                    
          }
          if(route.path != '/data'){
            this.headerData.pathList.push(route);
          }
      },
      immediate: true,
    }    
  }
}
</script>

<style lang="scss">
  .el-container{
    height: 100vh;
  }
 .el-aside {
    background-color: #001529;
    color: #fff;
    text-align: center;
    height: 100vh;
    overflow-x:hidden;
    width: auto !important;
  }
  .el-header{
    background-color: #fff;
    border-bottom: 1px solid #e6e6e6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
  }
  .el-main {    
    flex: 1;
    padding: 20px;
    background-color: #f0f2f5;
    height: calc(100vh - 60px);
    box-sizing:border-box;
    overflow: auto;
  }
  
</style>
<style lang="scss" scoped>
  .logo{
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #002140;
    color: #fff;
    .logo-icon{
      font-size: 20px;
      margin-right: 8px;
    }
    .hide{
      display: none;
    }
  }
</style>
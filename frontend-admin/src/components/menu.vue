<template>
  <div class="nav-box">
    
    <el-menu :default-active="activeMenu" class="el-menu-vertical-demo"
     @open="handleOpen" @close="handleClose" :collapse="isCollapse" @select="selectMenu"
     background-color="#001529"
      text-color="#fff"      
      :router="true">
        
      <template v-for="item in menuData.nav">
        <el-submenu :index="item.path" v-if="item.child.length > 0" >
          <template slot="title">
            <i class="meta-icon" :class="item.meta.icon"></i>
            <span slot="title">{{item.meta.title}}</span>
          </template>
          <el-menu-item :route="cItem.path" :index="cItem.path" v-for="(cItem,cIndex) in item.child" :key = "cIndex">          
            <!-- <i class="meta-icon" :class="cItem.meta.icon"></i> -->
            <span slot="title">{{cItem.meta.title}}</span>
          </el-menu-item>          
        </el-submenu>
        <el-menu-item  :route="item.path"  :index="item.path" v-if="item.child.length == 0">
          <i class="meta-icon" :class="item.meta.icon"></i>
          <span slot="title">{{item.meta.title}}</span>
        </el-menu-item>
        
       
      </template>
      
    </el-menu>
   
    
  </div>
</template>

<script>
export default {
  name: "HomeView",
  data() {
    return {
      menuData:{
        navAll:[],
        nav:[],
        // activeMenu:'',
      }        
    };
  },
  props:{
    activeMenu:{
      type:String,
      default:"data"
    },
    isCollapse:{
      type:Boolean,
      default:false
    }
  },
  mounted(){
    //设置导航默认路由
    // if (this.$route.path == '/') {
    //     this.menuData.activeMenu =  'data'
    // }else{      
    //   this.menuData.activeMenu =  this.$route.path.substring(1,this.$route.path.length)
    // }
    //获取导航内容
    this.getMenu()
  },
  methods: {
   
    handleOpen(key, keyPath) {
        console.log(key, keyPath,'===');
    },
    handleClose(key, keyPath) {
      console.log(key, keyPath,'++++');
    },
    selectMenu(key){
      this.$emit('selectMenu',key)
    },
    getMenu(){

      let routes = this.$router.options.routes; // 获取总路由数据
      let directory = []; //定义目录数组
      for(let i = 0; i<routes.length; i++ ){
        if(routes[i].children){ //获取二级路由          
          //过滤二级路由 为目录的数据
          directory = routes[i].children.filter(item => item.directory)
        }
        
      }
      this.menuData.navAll = directory
      //对目录（二级路由）进行分组
      //获取一级目录
      let parentNav = []
      for(let i = 0; i<directory.length; i++){
        if(directory[i].meta.parentId == '0'){
          directory[i].child=[]
          parentNav.push(directory[i])
        }
      }
      //获取二级目录 添加到一级目录下
      for(let i = 0; i<parentNav.length; i++){
        for(let j = 0; j<directory.length; j++){
          if(directory[j].meta.parentId == parentNav[i].meta.id){
            parentNav[i].child.push(directory[j])
          }
        }
      }
      this.menuData.nav = parentNav
      // console.log(this.menuData.nav)
    },
    navClick(item){
      if(item.meta.link){
       
        if (this.$route.path !== item.path) {
          this.$router.push(item.path);
        }
      }else{
        // console.log(item.child)        
      }
    }
  }
}
</script>
<style lang="scss" >
  .el-menu {
    border-right: none;
  }
  .el-menu-vertical-demo:not(.el-menu--collapse) {
    width: 200px;
    min-height: 400px;
  }
  .meta-icon{
      color: #fff !important; 
      font-size: 18px;
      margin-right: 5px;
    }
  .el-menu-item.is-active {
    background-color: #1890ff !important;
    color: #fff !important;  
    .meta-icon{
      color: #fff !important; 
    }
  }
  .el-menu-item:hover, .el-sub-menu__title:hover, .el-submenu__title:hover {
    background-color: #1890ff20 !important;
    color: #1890ff !important;
    .meta-icon{
      color: #1890ff !important; 
    }
  }
  .el-submenu.is-active .el-submenu__title {
    color: #1890ff !important;
    .meta-icon{
      color: #1890ff !important; 
    }
  }
  .nav-box{
    text-align: left;
    
  }
</style>
import Vue from 'vue'
import VueRouter from 'vue-router'
import HomeView from '../views/HomeView.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    children: [
      {
        path: '/data',
        name:'Dataview',
        component: ()=>import('../views/DataView/DataView.vue'),
        directory:true,
        meta: {
          id:'1',
          title: '数据概览',    
          leven:0,
          parentId:'0',
          link:true,     
          icon:'el-icon-data-analysis'  
        },
      },
      {
        path: '/cost',
        name:'Cost',
        component: ()=>import('../views/Cost/cost.vue'),
        directory:true,
        meta: {
          id:'9',
          title: '成本中心',    
          leven:0,
          parentId:'0',
          link:true,     
          icon:'el-icon-money'  
        },
      },
      {
        path: '/orderDirectory',
        name:'OrderDirectory',
        component: ()=>import('../views/Order/OrderView.vue'),
        directory:true,
        meta: {
          id:'2',
          title: '订单管理',    
          leven:0,
          parentId:'0',
          link:false, 
          icon:'el-icon-shopping-cart-2'      
        },
      },   
      {
        path: '/order',
        name:'Order',
        component: ()=>import('../views/Order/OrderView.vue'),
        directory:true,
        meta: {
          id:'2a',
          title: '商品订单',    
          leven:1,
          parentId:'2',
          link:true,       
          icon:'el-icon-shopping-cart-2' 
        },
      },{
        path: '/recharge',
        name:'Recharge',
        component: ()=>import('../views/Order/recharge.vue'),
        directory:true,
        meta: {
          id:'2b',
          title: '充值订单',    
          leven:1,
          parentId:'2',
          link:true,       
          icon:'el-icon-shopping-cart-2' 
        },
      },
      

      {
        path: '/productDirectory',
        name:'ProductDirectory',
        component: ()=>import('../views/Product/ProductList.vue'),
        directory:true,
        meta: {
          id:'3',
          title: '商品管理',    
          leven:0,
          parentId:'0',
          link:false,    
          icon:'el-icon-goods'   
        },
      }, 
      {
        path: '/productList',
        name:'ProductList',
        component: ()=>import('../views/Product/ProductList.vue'),
        directory:true,
        meta: {
          id:'3a',
          title: '商品列表',    
          leven:1,
          parentId:'3',
          link:true, 
          icon:'el-icon-goods'      
        },
      },
      {
        path: '/classList',
        name:'ClassList',
        component: ()=>import('../views/Product/ClassList.vue'),
        directory:true,
        meta: {
          id:'3b',
          title: '分类管理',    
          leven:1,
          parentId:'3',
          link:true,
          icon:'el-icon-menu'       
        },
      },
      {
        path: '/inventoryManage',
        name:'InventoryManage',
        component: ()=>import('../views/Product/InventoryManage.vue'),
        directory:false,
        meta: {
          id:'3c',
          title: '库存管理',    
          leven:1,
          parentId:'3',
          link:true,   
          icon:'el-icon-box'    
        },
      },
      {
        path: '/userManage',
        name:'UserManage',
        component: ()=>import('../views/User/UserManage.vue'),
        directory:true,
        meta: {
          id:'4',
          title: '用户管理',    
          leven:0,
          parentId:'0',
          link:true,
          icon:'el-icon-user', 
        },
      },
      {
        path: '/paymentSet',
        name:'PaymentSet',
        component: ()=>import('../views/Payment/paymentSet.vue'),
        directory:true,
        meta: {
          id:'8',
          title: '支付配置',    
          leven:0,
          parentId:'0',
          link:true,
          icon:'el-icon-setting', 
        },
      },
      {
        path: '/memberSet',
        name:'MemberSet',
        component: ()=>import('../views/MemberSet/memberSet.vue'),
        directory:true,
        meta: {
          id:'8',
          title: '会员设置',    
          leven:0,
          parentId:'0',
          link:true,
          icon:'el-icon-user', 
        },
      },
      {
        path: '/contentDirectory',
        name:'ContentDirectory',
        component: ()=>import('../views/Content/DocumentSetting.vue'),
        directory:true,
        meta: {
          id:'5',
          title: '内容管理',    
          leven:0,
          parentId:'0',
          link:false,
          icon:'el-icon-document'    
        },
      }, 
     
      {
        path: '/templateSetting',
        name:'TemplateSetting',
        component: ()=>import('../views/Content/TemplateSetting.vue'),
        directory:true,
        meta: {
          id:'5b',
          title: '模板设置',    
          leven:1,
          parentId:'5',
          link:true,
          icon:'el-icon-document'    
        },
      }, 
      {
        path: '/insiteMessages',
        name:'InsiteMessages',
        component: ()=>import('../views/Content/InsiteMessages.vue'),
        directory:true,
        meta: {
          id:'5c',
          title: '站内信',    
          leven:1,
          parentId:'5',
          link:true,
          icon:'el-icon-chat-line-square'       
        },
      }, 
      {
        path: '/announcement',
        name:'Announcement',
        component: ()=>import('../views/Content/announcement.vue'),
        directory:true,
        meta: {
          id:'5d',
          title: '公告管理',    
          leven:1,
          parentId:'5',
          link:true,
          icon:'el-icon-chat-line-square'       
        },
      }, 
      {
        path: '/dialogManage',
        name:'DialogManage',
        component: ()=>import('../views/Content/dialogManage.vue'),
        directory:true,
        meta: {
          id:'5e',
          title: '弹窗管理',    
          leven:1,
          parentId:'5',
          link:true,
          icon:'el-icon-chat-line-square'       
        },
      }, 
      {
        path: '/helpCenter',
        name:'HelpCenter',
        component: ()=>import('../views/Content/HelpCenter.vue'),
        directory:true,
        meta: {
          id:'5f',
          title: '帮助中心',    
          leven:1,
          parentId:'5',
          link:true,   
          icon:'el-icon-question'    
        },
      }, 
      {
        path: '/settingDirectory',
        name:'SettingDirectory',
        component: ()=>import('../views/Setting/SystemSetting.vue'),
        directory:true,
        meta: {
          id:'6',
          title: '系统管理',    
          leven:0,
          parentId:'0',
          link:false,   
          icon:'el-icon-setting'    
        },
      }, 
      {
        path: '/systemSetting',
        name:'SystemSetting',
        component: ()=>import('../views/Setting/SystemSetting.vue'),
        directory:true,
        meta: {
          id:'6a',
          title: '系统设置',    
          leven:0,
          parentId:'6',
          link:true, 
          icon:'el-icon-setting'      
        },
      }, 
      {
        path: '/systemDocumentSetting',
        name:'SystemDocumentSetting',
        component: ()=>import('../views/Setting/SystemDocumentSetting.vue'),
        directory:true,
        meta: {
          id:'6b',
          title: '系统文档设置',    
          leven:0,
          parentId:'6',
          link:true,  
          icon:'el-icon-document'     
        },
      },  
      {
        path: '/accountManage',
        name:'AccountManage',
        component: ()=>import('../views/Setting/AccountManage.vue'),
        directory:true,
        meta: {
          id:'6c',
          title: '账户管理',    
          leven:0,
          parentId:'6',
          link:true,  
          icon:'el-icon-lock'     
        },
      }, 
      {
        path: '/OperationLog',
        name:'OperationLog',
        component: ()=>import('../views/Setting/OperationLog.vue'),
        directory:true,
        meta: {
          id:'6d',
          title: '操作日志',    
          leven:0,
          parentId:'6',
          link:true,  
          icon:'el-icon-s-order'     
        },
      }, 
      {
        path: '/personal',
        name:'Personal',
        component: ()=>import('../views/Personal/Personal.vue'),
        directory:false,
        meta: {
          id:'7',
          title: '用户',    
          leven:0,
          parentId:'0',
          link:false,  
          icon:'el-icon-user'     
        },
      }, 
      {
        path: '/info',
        name:'Info',
        component: ()=>import('../views/Personal/Personal.vue'),
        directory:false,
        meta: {
          id:'7a',
          title: '个人信息',    
          leven:0,
          parentId:'7',
          link:true,  
          icon:'el-icon-user'     
        },
      }, 
      {
        path: '/resetPwd',
        name:'ResetPwd',
        component: ()=>import('../views/Personal/updatePwd.vue'),
        directory:false,
        meta: {
          id:'7b',
          title: '修改密码',    
          leven:0,
          parentId:'7',
          link:true,  
          icon:'el-icon-lock'     
        },
      }, 
    ]
  },
  {
    path: '/login',
    name: 'login',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/LoginView.vue')
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router

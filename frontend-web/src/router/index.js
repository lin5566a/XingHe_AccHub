import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [  
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/login.vue')
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/register.vue')
  },

  {
    path: '/about',
    name: 'about',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  },
  {
    path: '/mall',
    name: 'mall',
    component: () => import('../views/MallView.vue')
  },
  {
    path: '/orders',
    name: 'orders',
    component: () => import('../views/order/index.vue')
  },
  {
    path: '/help',
    name: 'help',
    component: () => import('../views/help.vue')
  },
  {
    path: '/help/article',
    name: 'article',
    component: () => import( '../views/HelpArticle.vue')
  },  
  {
    path: '/personal',
    name: 'personal',
    component: () => import( '../views/Personal.vue'),
    children:[
      {
        path: 'info',
        name: 'info',
        component: () => import( '../views/personalInfo/PersonalInfo.vue')
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import( '../views/personalInfo/PersonalProfile.vue')
      },
      {
        path: 'order',
        name: 'order',
        component: () => import( '../views/personalInfo/PersonalOrder.vue')
      },
      {
        path: 'rechargeOrder',
        name:'rechargeOrder',
        component: () => import( '../views/personalInfo/rechargeOrder.vue')
      },
      {
        path: 'billRecords',
        name:'billRecords',
        component: () => import( '../views/personalInfo/billRecords.vue')
      }
    ]
  },  
  {
    path: '/product-detail',
    name: 'product-detail',
    component: () => import( '../views/productDetail.vue')
  },
  {
    path: '/payment',
    name: 'payment',
    component: () => import( '../views/payment.vue')
  },
  {
    path: '/utilities',
    name: 'utilities',
    component: () => import( '../views/Utilities/index.vue'),
   
  },
  
  {
    path: '/recharge',
    name: 'recharge',
    component: () => import( '../views/recharge.vue'),
   
  },
  {
    path: '/rechargePay',
    name: 'rechargePay',
    component: () => import( '../views/rechargePay.vue'),
   
  },
 
]
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    }
    return { top: 0 }
  }
})

export default router

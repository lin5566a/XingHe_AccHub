<!-- 
  🛠定制开发 - 专业团队提供个性化开发服务
  💳支付通道对接 - 快速对接第三方支付平台
  🤝业务合作 - 多种合作模式，共创双赢
  QQ: 3909001743 | Telegram: @sy9088 
-->
<template>
  <div class="login">
    <div class="login-bg"></div>
    <div class="login-div">
      <div class="login-div-name">
        <span class="name-icon"><i  type="primary"  class="el-icon-monitor"></i></span><span class="name-text">{{$proName}}</span>
      </div>
      <div class="login-div-head">账号登录</div>
      <div class="login-div-con">
        <el-form :model="form" :rules="rules" ref="form" label-width="1px" class="" size="lager">
          <el-form-item label="" prop="name">
            <el-input v-model="form.name" placeholder="请输入用户名" clearable></el-input>
          </el-form-item>
          <el-form-item label="" prop="password">
            <el-input show-password v-model="form.password" placeholder="请输入密码" clearable></el-input>
          </el-form-item>
          <el-form-item label="" prop="code">
            <div class="code-box">
              <el-input class="code-input" v-model="form.code" placeholder="请输入验证码" clearable></el-input>
              <div class="code-img-box"><el-image @click="getCode()" class="code-img" :src="codeImg" fit="fill"></el-image></div>
            </div>           
          </el-form-item>
          <el-form-item label="" prop="norules">
            <el-checkbox v-model="form.checked">记住密码</el-checkbox>
          </el-form-item>   
          <el-form-item label="" prop="norules">
            <el-button class="login-btn" type="primary" @click="login" :disabled="loading">登 录</el-button>    
          </el-form-item>    
        </el-form>
      </div>
    </div>    
  </div>
</template>
<script>
import router from "@/router";
import Cookies from 'js-cookie';
	import axios from 'axios'
export default ({
  data() {
    return{
      form:{
        name:'',
        password:'',
        code:'',
        checked:false,
      },
      loading:false,
      rules:{
        name:[
          {required:true,message:'请输入用户名',trigger:'blur'},
          {min:3,max:20,message:'长度在 3 到 20 个字符',trigger:'blur'}
        ],
        password:[
          {required:true,message:'请输入密码',trigger:'blur'},
          {min:6,max:20,message:'长度在 6 到 20 个字符',trigger:'blur'}
        ],
        code:[
          {required:true,message:'请输入验证码',trigger:'blur'},
          {min:4,max:6,message:'长度在 4  到 6 个字符',trigger:'blur'}
        ],
        norules:[
          {required:false,trigger:'blur'},
        ]
      },
      codeImg:'',
      code_token:'',
    }
  },
  mounted(){
    this.getCode();
    const savedUsername = Cookies.get('username');
    const savedPassword = Cookies.get('password');
    if (savedUsername && savedPassword) {
      this.form.name = savedUsername;
      this.form.password = savedPassword;
      this.form.checked = true;
    }
  },
  methods:{
    login(){
      this.$refs['form'].validate((valid) => {
        if (valid) {

          if (this.form.checked) {
            // 保存用户名和密码到 Cookie
            Cookies.set('username', this.form.name, { expires: 7 }); // 设置有效期为7天
            Cookies.set('password',  this.form.password, { expires: 7 });
          } else {
            // 清除 Cookie 数据
            Cookies.remove('username');
            Cookies.remove('password');
          }

          //登录提交数据
          let data = {
            username:this.form.name,
            password:this.$md5(this.form.password),
            captcha:this.form.code,
            code_token:this.code_token,
          }          
          this.$local.remove('token')
          this.loading = true
          this.$api.dologin(data).then(res=>{
            this.loading = false
            if(res.code == 1){   

              // console.log(res.data.token,'登录接口获取token')
              this.$local.set('token',res.data.token)
              // console.log(this.$local.get('token'),'登录成功保存token')
              let userInfo = res.data.userInfo
              this.$local.set('userInfo',JSON.stringify(userInfo))
              router.push('/')
              this.$message({
                message: '登录成功',
                type: 'success',
                duration:3000,
              });
                //登录成功  存储账号数据          
            }else{
              this.getCode()
              this.$message.error(res.msg)
            }
            
              
          }).catch(e=>{
            this.getCode()
            this.loading = false
            // console.log(e,'e')
          })
         
        } else {
          return false;
        }
      });
    },
    async getCode(){
       // 在 URL 后面加上时间戳，防止浏览器缓存
       this.$api.captcha({}).then(res=>{
          if(res.code == 1){
            this.codeImg = res.data.image
            this.code_token = res.data.code_token
          }
       })
      // this.codeImg = `${this.$baseURL}/api/captcha?_t=${Date.now()}`
      // try {
			// 		const response = await axios.get(`${this.$baseURL}/api/captcha`, {	responseType: "JSON",	withCredentials: true });// 允许携带 cookie					
			// 		this.codeImg = JSON.parse(response.data);
      //     console.log(this.codeImg,'图片')
			// 	} catch (error) {
			// 		console.error("验证码获取失败", error);
			// 	}
    },
   
  }
})
</script>

<style lang="scss" scoped>
  .login{
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    .login-bg{
      background-color: #f5f7fa;
      width: 100vw;
      height: 100vh;
      position: absolute;
      left: 0;
      top: 0;
    }
    .login-div{
      width: 420px;
      padding: 40px;
      background-color: #fffffff2;
      border-radius: 8px;
      box-shadow: 0 8px 24px #00000026;
      position: relative;
      z-index: 1;
      box-sizing: border-box;
      .login-div-name{
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        .name-icon{
          font-size: 28px;
          color: #409eff;
          margin-right: 10px;
        }
        .name-text{
          font-size: 24px;
          color: #303133;
          margin: 0;
          font-weight: 600;
        }
      }
      .login-div-head{
        font-size: 18px;
        color: #303133;
        margin-bottom: 20px;
        font-weight: 500;
        text-align: center;
      }
      .login-div-con{
        .code-box{
          display: flex;
          justify-content: space-between;
          align-items: center;
          .code-input{
            width: 210px;
          }
          .code-img-box{
            width:120px;
            height: 40px;
            display: flex;
            align-items: center;
            .code-img{
              width:120px;
              height: 40px;
              border-radius: 4px;
            }
          }
        }
        .login-btn{
          width: 100%;
          height: 44px;
          font-size: 16px;
          border-radius: 4px;
          background: linear-gradient(to right, #1976d2, #2196f3);
          border: none;
        }
      }
    }
  }
 
</style>
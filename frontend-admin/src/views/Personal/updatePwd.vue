<template>
    <div class="reset-password-container">
      <el-card class="box-card">
        <template #header>
          <div class="card-header">
            <span>修改密码</span>
          </div>
        </template>
        
        <div class="reset-password-content">
          <el-steps :active="currentStep" finish-status="success" simple>
            <el-step title="验证身份" icon="Lock" />
            <el-step title="设置新密码" icon="Key" />
            <el-step title="完成" icon="CircleCheck" />
          </el-steps>
          
          <!-- 步骤1：验证身份 -->
          <div v-if="currentStep === 0" class="step-content">
            <el-form 
              :model="verifyForm" 
              :rules="verifyRules" 
              ref="verifyFormRef" 
              label-width="100px" 
              class="verify-form"
            >
              <el-form-item label="当前密码" prop="currentPassword">
                <el-input 
                  v-model="verifyForm.currentPassword" 
                  type="password" 
                  placeholder="请输入当前密码"
                  show-password
                ></el-input>
              </el-form-item>
              
              <el-form-item label="验证码" prop="captcha">
                <div class="captcha-container">
                  <el-input class="form-input"
                    v-model="verifyForm.captcha" 
                    placeholder="请输入验证码"
                  ></el-input>
                  <div class="captcha-image" @click="refreshCaptcha">
                    <img :src="captchaUrl" alt="验证码" />
                  </div>
                </div>
              </el-form-item>
              
              <el-form-item>
                <el-button type="primary" @click="handleVerify" :loading="verifyLoading">下一步</el-button>
                <el-button @click="handleCancel">取消</el-button>
              </el-form-item>
            </el-form>
          </div>
          
          <!-- 步骤2：设置新密码 -->
          <div v-else-if="currentStep === 1" class="step-content">
            <el-form 
              :model="passwordForm" 
              :rules="passwordRules" 
              ref="passwordFormRef" 
              label-width="100px" 
              class="password-form"
            >
              <el-form-item label="新密码" prop="newPassword">
                <el-input 
                  v-model="passwordForm.newPassword" 
                  type="password" 
                  placeholder="请输入新密码"
                  show-password
                ></el-input>
                <div class="password-strength">
                  <div class="strength-label">密码强度：</div>
                  <div class="strength-meter">
                    <div 
                      class="strength-bar" 
                      :class="passwordStrengthClass"
                      :style="{ width: passwordStrength + '%' }"
                    ></div>
                  </div>
                  <div class="strength-text" :class="passwordStrengthClass">{{ passwordStrengthText }}</div>
                </div>
                <div class="password-tips">
                  <p>密码必须包含以下条件：</p>
                  <ul>
                    <li :class="{ active: passwordHasLength }">长度至少为8个字符</li>
                    <li :class="{ active: passwordHasLower }">包含小写字母</li>
                    <li :class="{ active: passwordHasUpper }">包含大写字母</li>
                    <li :class="{ active: passwordHasNumber }">包含数字</li>
                    <li :class="{ active: passwordHasSpecial }">包含特殊字符</li>
                  </ul>
                </div>
              </el-form-item>
              
              <el-form-item label="确认新密码" prop="confirmPassword">
                <el-input 
                  v-model="passwordForm.confirmPassword" 
                  type="password" 
                  placeholder="请再次输入新密码"
                  show-password
                ></el-input>
              </el-form-item>
              
              <el-form-item>
                <el-button type="primary" @click="handleResetPassword" :loading="resetLoading">提交</el-button>
                <el-button @click="handlePrevStep">上一步</el-button>
              </el-form-item>
            </el-form>
          </div>
          
          <!-- 步骤3：完成 -->
          <div v-else class="step-content success-content">
            <el-result
              icon="success"
              title="密码修改成功"
              sub-title="您的账户密码已经成功修改，请使用新密码登录系统"
            >
              <template #extra>
                <el-button type="primary" @click="handleBackToProfile">返回个人中心</el-button>
                <el-button @click="handleReLogin">重新登录</el-button>
              </template>
            </el-result>
          </div>
        </div>
      </el-card>
    </div>
</template>

<script>
export default {
    name: "updatePwd",
    data() {
        return {
            currentStep: 0,
            token:'',
            code_token:'',
            verifyLoading: false,
            resetLoading: false,
            captchaUrl: '',
            verifyForm: {
                currentPassword: '',
                captcha: ''
            },
            passwordForm: {
                newPassword: '',
                confirmPassword: ''
            },
            verifyRules: {
                currentPassword: [
                    { required: true, message: '请输入当前密码', trigger: 'blur' }
                ],
                captcha: [
                    { required: true, message: '请输入验证码', trigger: 'blur' }
                ]
            },
            passwordRules: {
                newPassword: [
                    { required: true, message: '请输入新密码', trigger: 'blur' },
                    { validator: this.validatePassword, trigger: 'blur' }
                ],
                confirmPassword: [
                    { required: true, message: '请确认新密码', trigger: 'blur' },
                    { validator: this.validateConfirmPassword, trigger: 'blur' }
                ]
            }
        }
    },
    computed: {
        passwordStrength() {
            let strength = 0;
            if (this.passwordForm.newPassword.length >= 8) strength += 20;
            if (/[a-z]/.test(this.passwordForm.newPassword)) strength += 20;
            if (/[A-Z]/.test(this.passwordForm.newPassword)) strength += 20;
            if (/[0-9]/.test(this.passwordForm.newPassword)) strength += 20;
            if (/[^a-zA-Z0-9]/.test(this.passwordForm.newPassword)) strength += 20;
            return strength;
        },
        passwordStrengthClass() {
            if (this.passwordStrength <= 40) return 'weak';
            if (this.passwordStrength <= 80) return 'medium';
            return 'strong';
        },
        passwordStrengthText() {
            if (this.passwordStrength <= 40) return '弱';
            if (this.passwordStrength <= 80) return '中';
            return '强';
        },
        passwordHasLength() {
            return this.passwordForm.newPassword.length >= 8;
        },
        passwordHasLower() {
            return /[a-z]/.test(this.passwordForm.newPassword);
        },
        passwordHasUpper() {
            return /[A-Z]/.test(this.passwordForm.newPassword);
        },
        passwordHasNumber() {
            return /[0-9]/.test(this.passwordForm.newPassword);
        },
        passwordHasSpecial() {
            return /[^a-zA-Z0-9]/.test(this.passwordForm.newPassword);
        }
    },
    created() {
        this.refreshCaptcha();
    },
    methods: {
        // 刷新验证码
        refreshCaptcha() {
          this.$api.captcha({}).then(res=>{
            if(res.code == 1){
              this.captchaUrl = res.data.image
              this.code_token = res.data.code_token
            }
          })
            // this.captchaUrl = `${this.$baseURL}/api/captcha?_t=${Date.now()}`;
        },
        // 验证密码强度
        validatePassword(rule, value, callback) {
            if (!value) {
                callback(new Error('请输入新密码'));
            } else if (value.length < 8) {
                callback(new Error('密码长度不能小于8位'));
            } else if (!/[a-z]/.test(value)) {
                callback(new Error('密码必须包含小写字母'));
            } else if (!/[A-Z]/.test(value)) {
                callback(new Error('密码必须包含大写字母'));
            } else if (!/[0-9]/.test(value)) {
                callback(new Error('密码必须包含数字'));
            } else if (!/[^a-zA-Z0-9]/.test(value)) {
                callback(new Error('密码必须包含特殊字符'));
            } else {
                callback();
            }
        },
        // 验证确认密码
        validateConfirmPassword(rule, value, callback) {
            if (value !== this.passwordForm.newPassword) {
                callback(new Error('两次输入密码不一致'));
            } else {
                callback();
            }
        },
        // 验证身份
        handleVerify() {
            this.$refs.verifyFormRef.validate(valid => {
                if (valid) {
                    this.verifyLoading = true;
                    const data = {
                        old_password: this.verifyForm.currentPassword,
                        captcha: this.verifyForm.captcha,
                        code_token: this.code_token,
                    };
                    this.$api.verifyPassword(data).then(res => {
                        this.verifyLoading = false;
                        if (res.code === 1) {
                            this.currentStep = 1;
                            this.token = res.data.token;
                            this.$message.success(res.msg);
                        } else {
                            this.$message.error(res.msg);
                            this.token = ''
                            this.refreshCaptcha();
                        }
                    }).catch(err => {
                        this.verifyLoading = false;
                        this.token = ''
                        // this.$message.error('验证失败');
                        this.refreshCaptcha();
                    });
                }
            });
        },
        // 修改密码
        handleResetPassword() {
            this.$refs.passwordFormRef.validate(valid => {
                if (valid) {
                    this.resetLoading = true;
                    const data = {
                        token: this.token,
                        new_password: this.passwordForm.newPassword,
                        confirm_password: this.passwordForm.confirmPassword
                    };
                    this.$api.resetPassword(data).then(res => {
                        this.resetLoading = false;
                        if (res.code === 1) {
                            this.currentStep = 2;
                            this.$message.success(res.msg);
                        } else {
                            this.$message.error(res.msg);
                        }
                    }).catch(err => {
                        this.resetLoading = false;
                    });
                }
            });
        },
        // 上一步
        handlePrevStep() {
            this.currentStep = 0;
            this.verifyForm.captcha = '';
            this.refreshCaptcha();
        },
        // 取消
        handleCancel() {
            this.$router.push('/info');
        },
        // 返回个人中心
        handleBackToProfile() {
            this.$router.push('/info');
        },
        // 重新登录
        handleReLogin() {
            this.$local.remove('token');
            this.$router.push('/login');
        }
    }
}
</script>

<style lang="scss" scoped>
.reset-password-container {
  padding: 20px;
}

.box-card {
  max-width: 800px;
  margin: 0 auto;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.reset-password-content {
  padding: 20px 0;
}

.step-content {
  margin-top: 40px;
}

.verify-form,
.password-form {
  max-width: 500px;
  margin: 0 auto;
}

.captcha-container {
  display: flex;
  align-items: center;
  gap: 10px;
  .form-input{
    flex: 1;
  }
}

.captcha-image {
  height: 40px;
  cursor: pointer;
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.captcha-image img {
  height: 100%;
  width: 120px;
  object-fit: cover;
}

.password-strength {
  margin-top: 10px;
  display: flex;
  align-items: center;
}

.strength-label {
  font-size: 14px;
  color: #606266;
  margin-right: 10px;
}

.strength-meter {
  flex: 1;
  height: 6px;
  background-color: #ebeef5;
  border-radius: 3px;
  overflow: hidden;
}

.strength-bar {
  height: 100%;
  transition: width 0.3s ease;
}

.strength-bar.weak {
  background-color: #f56c6c;
}

.strength-bar.medium {
  background-color: #e6a23c;
}

.strength-bar.strong {
  background-color: #67c23a;
}

.strength-text {
  margin-left: 10px;
  font-size: 14px;
}

.strength-text.weak {
  color: #f56c6c;
}

.strength-text.medium {
  color: #e6a23c;
}

.strength-text.strong {
  color: #67c23a;
}

.password-tips {
  margin-top: 15px;
  padding: 10px;
  background-color: #f8f8f8;
  border-radius: 4px;
}

.password-tips p {
  margin: 0 0 5px 0;
  font-size: 14px;
  color: #606266;
}

.password-tips ul {
  margin: 0;
  padding-left: 20px;
}

.password-tips li {
  margin: 5px 0;
  font-size: 13px;
  color: #909399;
}

.password-tips li.active {
  color: #67c23a;
}

.success-content {
  text-align: center;
  padding: 20px 0;
}
</style> 
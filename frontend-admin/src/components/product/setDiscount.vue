<template>
    <div class="set-discount-box"> 
        <el-dialog
        :title="title"
        :visible.sync="dialogVisible"
        width="600px"
        @close="handleClose"
        :before-close="handleClose"
        :close-on-click-modal="false"
      >
        <div class="discount-content">
            <el-form ref="form" :model="discountData.form" label-width="90px">
                <el-form-item label="商品名称">
                    <el-input class="form-input" size="small" v-if="!bath" v-model="discountData.form.name" :disabled="true" placeholder="请输入商品名称" />
                    <el-select class="form-input" size="small" v-if="bath" v-model="discountData.form.names" multiple filterable collapse-tags placeholder="请选择药设置折扣的商品">                        
                        <el-option v-for="item in optionList" :key="item.id" :label="item.name" :value="item.id">
                            <!-- <span style="float: left">{{ item.label }}</span>
                            <span style="float: right; color: #8492a6; font-size: 13px">{{ item.value }}</span> -->
                        </el-option>
                    </el-select>
                    <div v-if="bath" class="form-tips">支持搜索商品名称，可选择多个商品进行批量设置</div>
                </el-form-item>
                <el-form-item label="启用折扣">
                    <el-switch size="small" v-model="discountData.form.discount_enabled" :active-value="1" :inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="折扣百分比" v-if="discountData.form.discount_enabled">
                    <el-input-number class="form-input" size="small" v-model="discountData.form.discount_percentage" @change="handleChange" :min="0" :max="99" :step="1"></el-input-number>
                    <div class="form-tips">输入折扣百分比，例如80表示打8折</div>
                </el-form-item>
                <el-form-item label="开始时间" v-if="discountData.form.discount_enabled">
                    <el-date-picker class="form-input" size="small" v-model="discountData.form.discount_start_time" type="datetime" format="yyyy-MM-dd HH:mm:ss" placeholder="选择日期时间"></el-date-picker>
                </el-form-item>
                <el-form-item label="结束时间" v-if="discountData.form.discount_enabled">
                    <el-date-picker class="form-input"  size="small" v-model="discountData.form.discount_end_time" type="datetime" format="yyyy-MM-dd HH:mm:ss" placeholder="选择日期时间"></el-date-picker>
                </el-form-item>
            </el-form>

        </div>
  
        <span slot="footer" class="dialog-footer">
          <el-button @click="handleClose">取 消</el-button>
          <el-button type="primary" @click="sureDiscount" :disabled="loading ||(bath && (discountData.form.discount_enabled == 0 || !discountData.form.names || discountData.form.names.length == 0))" :loading="loading">确定</el-button>
        </span>
      </el-dialog>
    </div>
</template>
<script>
    export default {
        props: {
            dialogVisible: {
                type: Boolean,
                default: false
            },
            title: {
                type: String,
                default: ''
            },
            bath:{
                type: Boolean,
                default: false
            },
            loading:{
                type:Boolean,
                default: false,
            },
            discountForm: {
                type: Object,
                default: () => ({})
            },
            optionList:{
                type:Array,
                default:()=>([])
            }
        },
        data() {
            return {
                discountData:{
                    form:{},                  
                }
            }
        },
        mounted(){
            this.discountData.form = {...this.discountData.form,...this.discountForm}
        },
        methods: {
            handleClose() {
                this.$emit('close')
            },
            sureDiscount() {
                this.$emit('sure',this.discountData.form,this.bath)
            },
            handleChange(val) {
                this.discountData.form.percent = val
            }
        }
    }
</script>
<style lang="scss" scoped>
.set-discount-box{
    .form-input{
        width: 100%;
    }
    .form-tips{
        margin-top: 5px;
        font-size: 12px;
        color: #909399;
        text-align: left;
    }
}
    
</style>
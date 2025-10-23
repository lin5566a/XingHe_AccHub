<template>
    <div class="batch-edit-cost">
        <el-dialog
                title="批量修改成本价"
                :visible.sync="dialogVisible"
                width="500px"
                custom-class="dialog-class"
                @close="close"
                :close-on-click-modal="false"
                :top="top"
                >
                <span class="send-dialog-con">
                    <el-form ref="form" :model="localForm" :rules="rules" label-width="100px" size="small">
                        <el-form-item label="选择批次" prop="batch_id">
                            <el-select  class="form-input" filterable v-model="localForm.batch_id" placeholder="请选择批次" clearable size="small">
                                <el-option v-for="item in batchList" :key="item.batch_id" :label="item.label" :value="item.batch_id"></el-option>
                            </el-select>
                            <div class="form-tip">选择要修改成本价的批次</div>
                        </el-form-item>
                        <el-form-item label="新成本价" prop="cost_price">
                            <el-input-number class="form-input" v-model="localForm.cost_price" :precision="2" :step="0.01" :min="0" size="small"></el-input-number>
                            <div class="form-tip">该批次下所有卡密将更新为此成本价</div>
                          </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input
                                type="textarea"
                                :rows="2"
                                placeholder="可选：添加修改原因等备注信息"
                                v-model="localForm.remark">
                            </el-input>
                        </el-form-item>
                    </el-form>
                </span>
                <span slot="footer" class="dialog-footer">
                    <el-button class="f14" @click="close" size="small">取 消</el-button>
                    <el-button class="f14" type="primary" @click="sure('form')" size="small" :disabled="loading">确认</el-button>
                </span>
            </el-dialog>
    </div>
</template>
<script>
export default {
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    form: {
      type: Object,
      required: true
    },
    batchList: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      localForm: { ...this.form },
      rules: {
        batch_id: [
          { required: true, message: '请选择批次', trigger: 'change' }
        ],
        cost_price: [
          { required: true, message: '请输入新成本价', trigger: 'blur' },
        ],
        remark: [
          { required: false, message: '', trigger: 'blur' }
        ]
      }
    }
  },
  watch: {
    form: {
      handler(val) {
        this.localForm = { ...val };
      },
      deep: true,
      immediate: true
    }
  },
  computed: {
    dialogVisible: {
      get() { return this.visible; },
      set(val) { this.$emit('update:visible', val); }
    },
    top() {
      return '15vh';
    }
  },
  methods: {
    close() {
      this.$refs.form.resetFields();   
      this.$emit('close');
    },
    sure(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          if(this.localForm.cost_price>0){
            this.$emit('sure', { ...this.localForm });
          }else{
            this.$message.warning('请输入有效的成本价');
          }
          
        }
      });
    }
  }
}
</script>
<style scoped lang="scss">
  .form-input{
    width: 100%;
  }
  .form-tip{
            margin-top: 5px;
            font-size: 12px;
            color: #909399;
        }
</style>
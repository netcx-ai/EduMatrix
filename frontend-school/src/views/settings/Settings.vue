<template>
  <div class="settings">
    <div class="page-header">
      <h2>学校设置</h2>
      <el-button type="primary" @click="handleSave" :loading="loading">保存设置</el-button>
    </div>
    
    <el-card class="settings-card">
      <template #header>
        <div class="card-header">
          <span>基本信息</span>
        </div>
      </template>
      <el-form :model="basicForm" :rules="basicRules" ref="basicFormRef" label-width="120px">
        <el-form-item label="学校名称" prop="schoolName">
          <el-input v-model="basicForm.schoolName" placeholder="请输入学校名称" />
        </el-form-item>
        <el-form-item label="学校编码" prop="schoolCode">
          <el-input v-model="basicForm.schoolCode" placeholder="请输入学校编码" />
        </el-form-item>
        <el-form-item label="学校地址" prop="schoolAddress">
          <el-input v-model="basicForm.schoolAddress" placeholder="请输入学校地址" />
        </el-form-item>
        <el-form-item label="联系电话" prop="phone">
          <el-input v-model="basicForm.phone" placeholder="请输入联系电话" />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="basicForm.email" placeholder="请输入邮箱" />
        </el-form-item>
        <el-form-item label="网站" prop="website">
          <el-input v-model="basicForm.website" placeholder="请输入网站地址" />
        </el-form-item>
        <el-form-item label="学校简介" prop="description">
          <el-input v-model="basicForm.description" type="textarea" :rows="4" placeholder="请输入学校简介" />
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getSettings, saveSettings } from '@/api/settings'

export default {
  name: 'Settings',
  setup() {
    const loading = ref(false)
    
    // 表单ref
    const basicFormRef = ref()
    
    // 基本信息表单
    const basicForm = reactive({
      schoolName: '',
      schoolCode: '',
      schoolAddress: '',
      phone: '',
      email: '',
      website: '',
      description: ''
    })
    
    // 验证规则
    const basicRules = {
      schoolName: [
        { required: true, message: '请输入学校名称', trigger: 'blur' }
      ],
      schoolCode: [
        { required: true, message: '请输入学校编码', trigger: 'blur' }
      ],
      phone: [
        { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号格式', trigger: 'blur' }
      ],
      email: [
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
      ]
    }
    
    // 获取设置
    const fetchSettings = async () => {
      loading.value = true
      try {
        const res = await getSettings()
        const data = res.data || {}
        
        // 将返回的数据分配到表单
        Object.assign(basicForm, {
          schoolName: data.schoolName || '',
          schoolCode: data.schoolCode || '',
          schoolAddress: data.schoolAddress || '',
          phone: data.phone || '',
          email: data.email || '',
          website: data.website || '',
          description: data.description || ''
        })
      } catch (error) {
        console.error('获取设置失败:', error)
        ElMessage.error('获取设置失败')
      } finally {
        loading.value = false
      }
    }
    
    // 保存设置
    const handleSave = async () => {
      try {
        // 验证表单
        await basicFormRef.value.validate()
        
        loading.value = true
        
        console.log('发送的数据:', basicForm) // 调试日志
        
        await saveSettings(basicForm)
        ElMessage.success('保存成功')
      } catch (error) {
        console.error('保存失败:', error)
        ElMessage.error('保存失败')
      } finally {
        loading.value = false
      }
    }
    
    onMounted(fetchSettings)
    
    return {
      loading,
      basicForm,
      basicFormRef,
      basicRules,
      handleSave
    }
  }
}
</script>

<style scoped>
.settings {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 0 4px;
}

.page-header h2 {
  margin: 0;
  color: #303133;
  font-size: 24px;
  font-weight: 600;
}

.settings-card {
  max-width: 800px;
  margin: 0 auto;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
}

.card-header {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.settings-card .el-form {
  padding: 24px 32px;
}

.settings-card .el-form-item {
  margin-bottom: 24px;
}

.settings-card .el-form-item:last-child {
  margin-bottom: 0;
}

.settings-card .el-input,
.settings-card .el-textarea {
  width: 100%;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .settings {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .settings-card .el-form {
    padding: 20px;
  }
  
  .settings-card .el-form-item {
    margin-bottom: 20px;
  }
}

@media (max-width: 480px) {
  .settings {
    padding: 12px;
  }
  
  .settings-card .el-form {
    padding: 16px;
  }
  
  .page-header h2 {
    font-size: 20px;
  }
}

/* 表单项标签样式优化 */
.settings-card :deep(.el-form-item__label) {
  font-weight: 500;
  color: #606266;
}

/* 按钮样式优化 */
.page-header .el-button {
  padding: 12px 24px;
  font-weight: 500;
}

/* 输入框聚焦效果 */
.settings-card :deep(.el-input__wrapper:focus-within) {
  box-shadow: 0 0 0 1px #409eff inset;
}
</style>
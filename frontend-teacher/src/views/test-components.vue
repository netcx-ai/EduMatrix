<template>
  <div class="test-container">
    <h2>Element Plus 组件测试</h2>
    
    <div class="test-section">
      <h3>选择器组件测试</h3>
      <el-form inline>
        <el-form-item label="工具类型">
          <el-select v-model="testForm.tool" placeholder="选择工具" clearable>
            <el-option label="文档生成" value="doc" />
            <el-option label="图片生成" value="image" />
            <el-option label="视频生成" value="video" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="状态">
          <el-select v-model="testForm.status" placeholder="选择状态" clearable>
            <el-option label="成功" value="success" />
            <el-option label="失败" value="failed" />
            <el-option label="处理中" value="processing" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="testForm.date_range"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
          />
        </el-form-item>
      </el-form>
    </div>
    
    <div class="test-section">
      <h3>当前值</h3>
      <pre>{{ JSON.stringify(testForm, null, 2) }}</pre>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const testForm = reactive({
  tool: '',
  status: '',
  date_range: []
})
</script>

<style scoped>
.test-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.test-section {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.test-section h3 {
  margin-bottom: 16px;
  color: #303133;
}

pre {
  background: #f5f7fa;
  padding: 12px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 14px;
  color: #606266;
}
</style> 
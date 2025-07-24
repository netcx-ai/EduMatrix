<template>
  <div class="demo-container">
    <el-card class="demo-card">
      <template #header>
        <div class="card-header">
          <h2>🎯 EduMatrix 业务流程演示</h2>
          <p>展示教师使用AI工具生成内容并保存到内容库的完整流程</p>
        </div>
      </template>
      
      <div class="demo-content">
        <!-- 流程步骤 -->
        <div class="flow-steps">
          <el-steps :active="currentStep" finish-status="success" align-center>
            <el-step title="登录系统" description="教师登录EduMatrix平台" />
            <el-step title="使用AI工具" description="选择AI工具生成教学内容" />
            <el-step title="生成内容" description="AI生成高质量教学内容" />
            <el-step title="保存内容库" description="将内容保存到个人内容库" />
            <el-step title="导出文档" description="导出Word格式文档" />
          </el-steps>
        </div>
        
        <!-- 当前步骤内容 -->
        <div class="step-content">
          <div v-if="currentStep === 0" class="step-panel">
            <h3>步骤 1: 登录系统</h3>
            <p>教师使用账号密码登录EduMatrix教育管理平台</p>
            <el-button type="primary" @click="nextStep">开始演示</el-button>
          </div>
          
          <div v-if="currentStep === 1" class="step-panel">
            <h3>步骤 2: 使用AI工具</h3>
            <p>在AI工具页面选择需要的工具类型：</p>
            <div class="tool-list">
              <el-tag v-for="tool in aiTools" :key="tool.code" type="success" class="tool-tag">
                {{ tool.name }}
              </el-tag>
            </div>
            <el-button type="primary" @click="nextStep">下一步</el-button>
          </div>
          
          <div v-if="currentStep === 2" class="step-panel">
            <h3>步骤 3: 生成内容</h3>
            <p>AI工具正在生成教学内容...</p>
            <div class="generated-content">
              <el-card class="content-preview">
                <template #header>
                  <span>生成的内容预览</span>
                </template>
                <div class="content-text">
                  <h4>数学基础运算 教学讲稿</h4>
                  <p><strong>教学目标：</strong>掌握加减乘除基本运算</p>
                  <p><strong>教学对象：</strong>三年级学生</p>
                  <p><strong>教学时长：</strong>45分钟</p>
                  <p><strong>教学过程：</strong></p>
                  <ul>
                    <li>导入新课 (5分钟)</li>
                    <li>新课讲解 (25分钟)</li>
                    <li>练习巩固 (10分钟)</li>
                    <li>总结归纳 (5分钟)</li>
                  </ul>
                </div>
              </el-card>
            </div>
            <el-button type="primary" @click="nextStep">保存到内容库</el-button>
          </div>
          
          <div v-if="currentStep === 3" class="step-panel">
            <h3>步骤 4: 保存内容库</h3>
            <p>内容已成功保存到个人内容库</p>
            <div class="content-info">
              <el-descriptions :column="2" border>
                <el-descriptions-item label="内容名称">数学基础运算教学讲稿</el-descriptions-item>
                <el-descriptions-item label="内容类型">教学讲稿</el-descriptions-item>
                <el-descriptions-item label="创建时间">{{ new Date().toLocaleString() }}</el-descriptions-item>
                <el-descriptions-item label="状态">草稿</el-descriptions-item>
              </el-descriptions>
            </div>
            <el-button type="primary" @click="nextStep">导出Word文档</el-button>
          </div>
          
          <div v-if="currentStep === 4" class="step-panel">
            <h3>步骤 5: 导出文档</h3>
            <p>Word文档导出成功！</p>
            <div class="export-info">
              <el-result
                icon="success"
                title="文档导出成功"
                sub-title="数学基础运算教学讲稿.docx"
              >
                <template #extra>
                  <el-button type="primary" @click="downloadFile">下载文档</el-button>
                  <el-button @click="viewInLibrary">在内容库中查看</el-button>
                </template>
              </el-result>
            </div>
            <el-button type="primary" @click="restartDemo">重新开始演示</el-button>
          </div>
        </div>
        
        <!-- 功能特性展示 -->
        <div class="features">
          <h3>🎉 核心功能特性</h3>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-card class="feature-card">
                <template #header>
                  <div class="feature-header">
                    <el-icon><MagicStick /></el-icon>
                    <span>AI智能生成</span>
                  </div>
                </template>
                <ul>
                  <li>讲稿生成</li>
                  <li>作业生成</li>
                  <li>题库生成</li>
                  <li>课程分析</li>
                  <li>教案生成</li>
                  <li>教学反思</li>
                </ul>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card class="feature-card">
                <template #header>
                  <div class="feature-header">
                    <el-icon><Document /></el-icon>
                    <span>内容库管理</span>
                  </div>
                </template>
                <ul>
                  <li>内容保存</li>
                  <li>分类管理</li>
                  <li>搜索查找</li>
                  <li>版本控制</li>
                  <li>分享协作</li>
                  <li>审核流程</li>
                </ul>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card class="feature-card">
                <template #header>
                  <div class="feature-header">
                    <el-icon><Download /></el-icon>
                    <span>文档导出</span>
                  </div>
                </template>
                <ul>
                  <li>Word格式</li>
                  <li>PDF格式</li>
                  <li>自定义模板</li>
                  <li>批量导出</li>
                  <li>在线预览</li>
                  <li>版本管理</li>
                </ul>
              </el-card>
            </el-col>
          </el-row>
        </div>
        
        <!-- 快速导航 -->
        <div class="quick-nav">
          <h3>🚀 快速导航</h3>
          <el-row :gutter="20">
            <el-col :span="6">
              <el-button type="primary" @click="goToAiTools" class="nav-btn">
                <el-icon><MagicStick /></el-icon>
                AI工具
              </el-button>
            </el-col>
            <el-col :span="6">
              <el-button type="success" @click="goToContent" class="nav-btn">
                <el-icon><Document /></el-icon>
                内容库
              </el-button>
            </el-col>
            <el-col :span="6">
              <el-button type="warning" @click="goToFiles" class="nav-btn">
                <el-icon><Folder /></el-icon>
                文件管理
              </el-button>
            </el-col>
            <el-col :span="6">
              <el-button type="info" @click="goToCourses" class="nav-btn">
                <el-icon><School /></el-icon>
                课程管理
              </el-button>
            </el-col>
          </el-row>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { 
  MagicStick, 
  Document, 
  Download, 
  Folder, 
  School 
} from '@element-plus/icons-vue'

const router = useRouter()
const currentStep = ref(0)

const aiTools = [
  { code: 'lecture_generator', name: '讲稿生成' },
  { code: 'homework_generator', name: '作业生成' },
  { code: 'question_bank_generator', name: '题库生成' },
  { code: 'course_analysis', name: '课程分析' },
  { code: 'lesson_plan', name: '教案生成' },
  { code: 'teaching_reflection', name: '教学反思' }
]

const nextStep = () => {
  if (currentStep.value < 4) {
    currentStep.value++
  }
}

const restartDemo = () => {
  currentStep.value = 0
}

const downloadFile = () => {
  // 模拟文件下载
  const link = document.createElement('a')
  link.href = 'data:text/plain;charset=utf-8,数学基础运算教学讲稿内容'
  link.download = '数学基础运算教学讲稿.docx'
  link.click()
}

const viewInLibrary = () => {
  router.push('/teacher/content')
}

const goToAiTools = () => {
  router.push('/teacher/ai-tools')
}

const goToContent = () => {
  router.push('/teacher/content')
}

const goToFiles = () => {
  router.push('/teacher/files')
}

const goToCourses = () => {
  router.push('/teacher/courses')
}
</script>

<style scoped>
.demo-container {
  padding: 20px;
  background: #f5f7fa;
  min-height: calc(100vh - 60px);
}

.demo-card {
  max-width: 1200px;
  margin: 0 auto;
}

.card-header {
  text-align: center;
}

.card-header h2 {
  margin: 0 0 10px 0;
  color: #303133;
}

.card-header p {
  margin: 0;
  color: #606266;
  font-size: 14px;
}

.demo-content {
  padding: 20px 0;
}

.flow-steps {
  margin-bottom: 40px;
}

.step-content {
  margin-bottom: 40px;
}

.step-panel {
  text-align: center;
  padding: 40px 20px;
  background: #fafafa;
  border-radius: 8px;
  margin: 20px 0;
}

.step-panel h3 {
  color: #303133;
  margin-bottom: 20px;
}

.tool-list {
  margin: 20px 0;
}

.tool-tag {
  margin: 5px;
  font-size: 14px;
}

.generated-content {
  margin: 20px 0;
}

.content-preview {
  max-width: 600px;
  margin: 0 auto;
  text-align: left;
}

.content-text {
  line-height: 1.6;
}

.content-text h4 {
  color: #303133;
  margin-bottom: 15px;
}

.content-text ul {
  margin: 10px 0;
  padding-left: 20px;
}

.content-info {
  margin: 20px 0;
}

.export-info {
  margin: 20px 0;
}

.features {
  margin-bottom: 40px;
}

.features h3 {
  text-align: center;
  margin-bottom: 20px;
  color: #303133;
}

.feature-card {
  height: 100%;
}

.feature-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: bold;
}

.feature-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.feature-card li {
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
  color: #606266;
}

.feature-card li:last-child {
  border-bottom: none;
}

.quick-nav {
  text-align: center;
}

.quick-nav h3 {
  margin-bottom: 20px;
  color: #303133;
}

.nav-btn {
  width: 100%;
  height: 60px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
}

.nav-btn .el-icon {
  font-size: 20px;
}
</style> 
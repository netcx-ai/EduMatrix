<template>
  <Layout>
    <div class="ai-tools-container">
      <!-- 页面标题 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">🤖 AI智能工具箱</h1>
          <p class="page-subtitle">让AI成为您的教学助手，提升教育效率</p>
        </div>
        <div class="header-right">
          <el-button @click="viewAllHistory">
            <el-icon><Clock /></el-icon>
            历史记录
          </el-button>
          <el-button @click="refreshData">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </div>

      <!-- 快速统计卡片 -->
      <div class="stats-overview">
        <el-row :gutter="20">
          <el-col :span="6">
            <div class="stat-card gradient-blue">
              <div class="stat-icon">
                <el-icon><MagicStick /></el-icon>
              </div>
              <div class="stat-content">
                <div class="stat-number">{{ usageStats.total_usage || 0 }}</div>
                <div class="stat-label">总使用次数</div>
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="stat-card gradient-green">
              <div class="stat-icon">
                <el-icon><Calendar /></el-icon>
              </div>
              <div class="stat-content">
                <div class="stat-number">{{ usageStats.monthly_usage || 0 }}</div>
                <div class="stat-label">本月使用</div>
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="stat-card gradient-purple">
              <div class="stat-icon">
                <el-icon><Tools /></el-icon>
              </div>
              <div class="stat-content">
                <div class="stat-number">{{ aiTools.length || 0 }}</div>
                <div class="stat-label">可用工具</div>
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="stat-card gradient-orange">
              <div class="stat-icon">
                <el-icon><TrendCharts /></el-icon>
              </div>
              <div class="stat-content">
                <div class="stat-number">98%</div>
                <div class="stat-label">成功率</div>
              </div>
            </div>
          </el-col>
        </el-row>
      </div>

      <!-- 工具分类筛选 -->
      <div class="tool-filters">
        <el-radio-group v-model="activeCategory" @change="filterTools">
          <el-radio-button value="">全部工具</el-radio-button>
          <el-radio-button value="content">内容生成</el-radio-button>
          <el-radio-button value="assessment">评估工具</el-radio-button>
          <el-radio-button value="analysis">分析工具</el-radio-button>
        </el-radio-group>
        <el-input
          v-model="searchKeyword"
          placeholder="搜索AI工具..."
          style="width: 200px;"
          clearable
          @input="filterTools"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
      </div>

      <!-- AI工具列表 -->
      <div class="tools-grid">
        <div 
          v-for="tool in filteredTools" 
          :key="tool.id" 
          class="tool-card"
          :class="[`tool-${tool.category}`, { 'tool-disabled': tool.status !== 'enabled' }]"
          @click="useTool(tool)"
        >
          <div class="tool-ribbon" v-if="isPopularTool(tool)">
            <span>热门</span>
          </div>
          
          <div class="tool-header">
            <div class="tool-icon" :class="`icon-${tool.category}`">
              <el-icon><component :is="getToolIcon(tool.category)" /></el-icon>
            </div>
            <div class="tool-status">
              <el-tag 
                :type="tool.status === 'enabled' ? 'success' : 'danger'" 
                size="small"
                effect="dark"
              >
                {{ tool.status === 'enabled' ? '可用' : '维护中' }}
              </el-tag>
            </div>
          </div>
          
          <div class="tool-content">
            <h3 class="tool-name">{{ tool.name }}</h3>
            <p class="tool-desc">{{ tool.description }}</p>
            <div class="tool-meta">
                          <div class="tool-category">
              <el-icon><Collection /></el-icon>
              {{ getCategoryText(tool.category) }}
            </div>
              <div class="tool-usage">
                <el-icon><View /></el-icon>
                已使用 {{ tool.usage_count || 0 }} 次
              </div>
            </div>
          </div>
          
          <div class="tool-footer">
            <el-button 
              type="primary" 
              :disabled="tool.status !== 'enabled'"
              @click.stop="useTool(tool)"
              class="tool-use-btn"
            >
              <el-icon><Lightning /></el-icon>
              立即使用
            </el-button>
          </div>
        </div>
      </div>

      <!-- 空状态 -->
      <div v-if="!loading && filteredTools.length === 0" class="empty-state">
        <div class="empty-icon">
          <el-icon><Search /></el-icon>
        </div>
        <h3>没有找到相关工具</h3>
        <p>试试调整搜索条件或联系管理员开通更多工具</p>
        <el-button @click="resetFilters">重置筛选</el-button>
      </div>

      <!-- 最近使用的工具 -->
      <div class="recent-tools" v-if="recentHistory.length > 0">
        <div class="section-header">
          <h3 class="section-title">
            <el-icon><Clock /></el-icon>
            最近使用
          </h3>
          <el-button link @click="viewAllHistory">查看全部</el-button>
        </div>
        <div class="recent-tools-list">
          <div 
            v-for="record in recentHistory.slice(0, 6)" 
            :key="record.id" 
            class="recent-tool-item"
            @click="quickUseFromHistory(record)"
          >
            <div class="recent-tool-info">
              <div class="recent-tool-name">{{ record.tool_name }}</div>
              <div class="recent-tool-time">{{ formatDate(record.created_at) }}</div>
            </div>
            <div class="recent-tool-status">
              <el-tag 
                :type="record.status === 'success' ? 'success' : 'danger'" 
                size="small"
              >
                {{ record.status === 'success' ? '成功' : '失败' }}
              </el-tag>
            </div>
            <div class="recent-tool-action">
              <el-button text type="primary">
                <el-icon><Refresh /></el-icon>
                重新使用
              </el-button>
            </div>
          </div>
        </div>
      </div>

      <!-- 工具使用对话框 -->
      <el-dialog
        v-model="toolDialog.visible"
        :title="toolDialog.tool?.name + ' - 智能生成'"
        width="80%"
        :before-close="() => toolDialog.visible = false"
        class="tool-dialog"
      >
        <div v-if="toolDialog.tool" class="tool-dialog-content">
          <!-- 工具介绍卡片 -->
          <div class="tool-intro-card">
            <div class="intro-header">
              <div class="intro-icon" :class="`icon-${toolDialog.tool.category}`">
                <el-icon><component :is="getToolIcon(toolDialog.tool.category)" /></el-icon>
              </div>
              <div class="intro-content">
                <h4>{{ toolDialog.tool.name }}</h4>
                <p>{{ toolDialog.tool.description }}</p>
              </div>
            </div>
          </div>



          <!-- 参数配置 -->
          <div class="parameter-section">
            <h4>
              <el-icon><Setting /></el-icon>
              参数配置
            </h4>
            <div v-if="!toolDialog.toolParams" class="loading-params">
              <el-skeleton :rows="3" animated />
            </div>
            <el-form 
              v-else 
              :model="toolDialog.params" 
              label-width="120px"
              class="params-form"
            >
              <el-form-item 
                v-for="(param, key) in toolDialog.toolParams" 
                :key="key"
                :label="param.label"
                :required="param.required"
                class="param-item"
              >
                <!-- 文本输入 -->
                <el-input
                  v-if="param.type === 'text'"
                  v-model="toolDialog.params[key]"
                  :placeholder="param.placeholder"
                  clearable
                  maxlength="200"
                  show-word-limit
                />
                
                <!-- 多行文本 -->
                <el-input
                  v-else-if="param.type === 'textarea'"
                  v-model="toolDialog.params[key]"
                  :placeholder="param.placeholder"
                  type="textarea"
                  :rows="param.rows || 4"
                  maxlength="1000"
                  show-word-limit
                  class="textarea-input"
                />
                
                <!-- 下拉选择 -->
                <el-select
                  v-else-if="param.type === 'select'"
                  v-model="toolDialog.params[key]"
                  :placeholder="param.placeholder"
                  clearable
                  style="width: 100%"
                >
                  <el-option
                    v-for="option in param.options"
                    :key="option.value"
                    :label="option.label"
                    :value="option.value"
                  />
                </el-select>
                
                <!-- 数字输入 -->
                <el-input-number
                  v-else-if="param.type === 'number'"
                  v-model="toolDialog.params[key]"
                  :min="param.min"
                  :max="param.max"
                  :placeholder="param.placeholder"
                  style="width: 100%"
                />
                
                <!-- 多选框 -->
                <el-checkbox-group
                  v-else-if="param.type === 'checkbox'"
                  v-model="toolDialog.params[key]"
                  class="checkbox-group"
                >
                  <el-checkbox
                    v-for="option in param.options"
                    :key="option.value"
                    :label="option.value"
                    class="checkbox-item"
                  >
                    {{ option.label }}
                  </el-checkbox>
                </el-checkbox-group>
                
                <!-- 课程选择 -->
                <CourseSelect
                  v-else-if="param.type === 'course_select'"
                  v-model="toolDialog.params[key]"
                  :placeholder="param.placeholder"
                  :clearable="!param.required"
                  class="course-select-field"
                />
              </el-form-item>
            </el-form>
          </div>

          <!-- 高级选项 -->
          <el-collapse v-model="advancedOptionsOpen" class="advanced-options">
            <el-collapse-item title="高级选项" name="advanced">
              <div class="advanced-content">
                <el-form label-width="120px">
                  <el-form-item label="创意程度">
                    <el-slider
                      v-model="advancedSettings.creativity"
                      :min="0"
                      :max="100"
                      show-tooltip
                      :format-tooltip="(val) => val + '%'"
                    />
                    <div class="slider-tips">
                      <span>保守</span>
                      <span>创新</span>
                    </div>
                  </el-form-item>
                  <el-form-item label="输出长度">
                                      <el-radio-group v-model="advancedSettings.length">
                    <el-radio value="short">简洁</el-radio>
                    <el-radio value="medium">适中</el-radio>
                    <el-radio value="long">详细</el-radio>
                  </el-radio-group>
                  </el-form-item>

                </el-form>
              </div>
            </el-collapse-item>
          </el-collapse>

          <!-- 操作按钮 -->
          <div class="action-section">
            <div class="action-buttons">
              <el-button 
                type="primary" 
                :loading="toolDialog.generating"
                @click="generateContent"
                size="large"
                class="generate-btn"
              >
                <el-icon v-if="!toolDialog.generating"><Lightning /></el-icon>
                {{ toolDialog.generating ? '正在生成中...' : '开始生成' }}
              </el-button>
              <el-button 
                @click="previewPrompt"
                size="large"
              >
                <el-icon><View /></el-icon>
                预览提示词
              </el-button>
            </div>
            <div class="action-tips">
              <el-icon><InfoFilled /></el-icon>
              生成过程可能需要10-30秒，请耐心等待
            </div>
          </div>

          <!-- 生成进度 -->
          <div v-if="toolDialog.generating" class="generation-progress">
            <el-progress 
              :percentage="generationProgress" 
              :show-text="false"
              :stroke-width="6"
              color="#67c23a"
            />
            <div class="progress-text">{{ progressText }}</div>
          </div>

          <!-- 生成结果 -->
          <div v-if="toolDialog.result" class="result-section">
            <div class="result-header">
              <h4>
                <el-icon><DocumentChecked /></el-icon>
                生成结果
              </h4>
              <div class="result-actions-header">
                <el-button-group>
                  <el-button @click="copyResult" size="small">
                    <el-icon><CopyDocument /></el-icon>
                    复制
                  </el-button>
                  <el-button @click="regenerateContent" size="small">
                    <el-icon><Refresh /></el-icon>
                    重新生成
                  </el-button>
                </el-button-group>
              </div>
            </div>
            
            <div class="result-content">
              <el-input
                v-model="toolDialog.result.content"
                type="textarea"
                :rows="12"
                class="result-textarea"
                placeholder="生成的内容将显示在这里..."
              />
            </div>
            
            <div class="result-actions">
              <el-space wrap>
                <el-button type="success" @click="viewInContentCenter">
                  <el-icon><Document /></el-icon>
                  进入内容库编辑
                </el-button>
                <el-button type="primary" @click="goToContentForReview">
                  <el-icon><Upload /></el-icon>
                  提交审核
                </el-button>
                <el-button @click="shareContent">
                  <el-icon><Share /></el-icon>
                  分享内容
                </el-button>
              </el-space>
              <div class="workflow-tip">
                <el-icon><InfoFilled /></el-icon>
                <span>内容已保存为草稿，您可以到内容库中编辑后提交审核，审核通过后即可导出文件</span>
              </div>
            </div>
          </div>
        </div>
      </el-dialog>


    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElNotification, ElMessageBox } from 'element-plus'
import { 
  Refresh, 
  MagicStick, 
  Document, 
  DataAnalysis, 
  Star,
  Clock,
  Calendar,
  Tools,
  TrendCharts,
  Search,
  Collection,
  View,
  Lightning,
  Setting,
  InfoFilled,
  DocumentChecked,
  CopyDocument,
  Download,
  Share,
  MoreFilled,
  Upload
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import CourseSelect from '@/components/CourseSelect.vue'
import { aiToolApi } from '@/api/aiTool'
import { contentApi } from '@/api/content'

const router = useRouter()

// 响应式数据
const loading = ref(false)
const aiTools = ref([])
const usageStats = ref({})
const recentHistory = ref([])

// 筛选相关
const activeCategory = ref('')
const searchKeyword = ref('')

// 工具使用对话框
const toolDialog = ref({
  visible: false,
  tool: null,
  params: {},
  toolParams: null,
  generating: false,
  result: null
})



// 高级设置
const advancedOptionsOpen = ref([])
const advancedSettings = ref({
  creativity: 70,
  length: 'medium'
})

// 生成进度
const generationProgress = ref(0)
const progressText = ref('')

// 计算属性
const filteredTools = computed(() => {
  let filtered = aiTools.value
  
  if (activeCategory.value) {
    filtered = filtered.filter(tool => tool.category === activeCategory.value)
  }
  
  if (searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    filtered = filtered.filter(tool => 
      tool.name.toLowerCase().includes(keyword) ||
      tool.description.toLowerCase().includes(keyword)
    )
  }
  
  return filtered
})

// 获取AI工具列表
const loadAiTools = async () => {
  try {
    loading.value = true
    const response = await aiToolApi.getList()
    if (response.code === 200) {
      aiTools.value = response.data.list || []
      // 添加使用次数统计
      aiTools.value.forEach(tool => {
        tool.usage_count = Math.floor(Math.random() * 100) // 模拟数据
        tool.is_popular = Math.random() > 0.7 ? 1 : 0 // 模拟热门工具
      })
    }
  } catch (error) {
    console.error('获取AI工具列表失败：', error)
    ElMessage.error('获取AI工具列表失败')
  } finally {
    loading.value = false
  }
}

// 获取使用统计
const loadUsageStats = async () => {
  try {
    const response = await aiToolApi.getStatistics()
    if (response.code === 200) {
      usageStats.value = response.data || {}
    }
  } catch (error) {
    console.error('获取使用统计失败：', error)
  }
}

// 获取使用历史
const loadUsageHistory = async () => {
  try {
    const response = await aiToolApi.getHistory({ limit: 10 })
    if (response.code === 200) {
      recentHistory.value = response.data.list || []
    }
  } catch (error) {
    console.error('获取使用历史失败：', error)
  }
}

// 刷新数据
const refreshData = () => {
  loadAiTools()
  loadUsageStats()
  loadUsageHistory()
  ElMessage.success('数据已刷新')
}

// 筛选工具
const filterTools = () => {
  // 筛选逻辑已在计算属性中处理
}

// 重置筛选
const resetFilters = () => {
  activeCategory.value = ''
  searchKeyword.value = ''
}

// 使用工具
const useTool = (tool) => {
  if (tool.status !== 'enabled') {
    ElMessage.warning('该工具暂不可用')
    return
  }
  
  showToolDialog(tool)
}

// 显示工具使用对话框
const showToolDialog = async (tool) => {
  toolDialog.value.tool = tool
  toolDialog.value.params = {}
  toolDialog.value.toolParams = null
  toolDialog.value.result = null
  toolDialog.value.visible = true
  generationProgress.value = 0
  progressText.value = ''
  
  // 异步加载工具参数配置
  try {
    const params = await getToolParameters(tool)
    toolDialog.value.toolParams = params
    
    // 设置默认值
    Object.keys(params).forEach(key => {
      if (params[key].default !== undefined) {
        toolDialog.value.params[key] = params[key].default
      } else if (params[key].type === 'checkbox') {
        toolDialog.value.params[key] = []
      }
    })
  } catch (error) {
    console.error('加载工具参数配置失败:', error)
    ElMessage.error('加载工具配置失败')
  }
}

// 生成内容
const generateContent = async () => {
  if (!toolDialog.value.tool) return
  
  try {
    toolDialog.value.generating = true
    generationProgress.value = 0
    progressText.value = '正在准备生成...'
    
    // 模拟进度
    const progressInterval = setInterval(() => {
      if (generationProgress.value < 90) {
        generationProgress.value += Math.random() * 10
        const texts = [
          '正在分析参数...',
          '正在构建提示词...',
          '正在调用AI引擎...',
          '正在生成内容...',
          '正在优化结果...'
        ]
        progressText.value = texts[Math.floor(generationProgress.value / 20)]
      }
    }, 200)
    
    console.log('提交的参数:', toolDialog.value.params)
    const response = await aiToolApi.generate({
      tool_code: toolDialog.value.tool.code,
      prompt_params: toolDialog.value.params,
      save_to_library: true,
      provider: 'deepseek',
      advanced_settings: advancedSettings.value
    })
    
    clearInterval(progressInterval)
    generationProgress.value = 100
    progressText.value = '生成完成！'
    
    if (response.code === 200) {
      toolDialog.value.result = response.data
      ElNotification({
        title: '生成成功',
        message: '内容已生成并保存到内容库作为草稿。您可以进入内容库编辑后提交审核，审核通过后即可导出文件。',
        type: 'success',
        duration: 5000
      })
    } else {
      ElMessage.error(response.message || '生成失败')
    }
  } catch (error) {
    clearInterval(progressInterval)
    console.error('生成失败：', error)
    ElMessage.error('生成失败：' + error.message)
  } finally {
    toolDialog.value.generating = false
  }
}

// 重新生成内容
const regenerateContent = async () => {
  if (!toolDialog.value.result) return
  
  try {
    await ElMessageBox.confirm('确定要重新生成内容吗？当前内容将被覆盖。', '确认重新生成', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    generateContent()
  } catch (err) {
    if (err !== 'cancel') {
      console.error(err)
    }
  }
}

// 预览提示词
const previewPrompt = () => {
  if (!toolDialog.value.tool || !toolDialog.value.toolParams) {
    ElMessage.warning('请先选择工具并填写参数')
    return
  }
  
  let promptPreview = toolDialog.value.tool.prompt_template || ''
  Object.keys(toolDialog.value.params).forEach(key => {
    const value = toolDialog.value.params[key]
    const displayValue = Array.isArray(value) ? value.join('、') : value
    promptPreview = promptPreview.replace(new RegExp(`{${key}}`, 'g'), displayValue || `[${key}]`)
  })
  
  ElMessageBox.alert(promptPreview, '提示词预览', {
    confirmButtonText: '确定',
    type: 'info',
    dangerouslyUseHTMLString: false
  })
}

// 复制结果
const copyResult = async () => {
  if (!toolDialog.value.result?.content) return
  
  try {
    await navigator.clipboard.writeText(toolDialog.value.result.content)
    ElMessage.success('内容已复制到剪贴板')
  } catch (error) {
    console.error('复制失败：', error)
    ElMessage.error('复制失败')
  }
}


// 分享内容
const shareContent = () => {
  if (!toolDialog.value.result?.content) return
  
  const shareUrl = window.location.origin + `/share/content/${toolDialog.value.result.content_id}`
  
  ElMessageBox.prompt('分享链接已生成，您可以复制链接分享给他人：', '分享内容', {
    confirmButtonText: '复制链接',
    cancelButtonText: '取消',
    inputValue: shareUrl,
    inputType: 'textarea',
    inputAttrs: {
      readonly: true,
      rows: 3
    }
  }).then(async () => {
    try {
      await navigator.clipboard.writeText(shareUrl)
      ElMessage.success('分享链接已复制到剪贴板')
    } catch (error) {
      ElMessage.error('复制失败')
    }
  }).catch(() => {})
}



// 进入内容库编辑
const viewInContentCenter = () => {
  if (toolDialog.value.result && toolDialog.value.result.content_id) {
    router.push({
      path: '/teacher/content/edit',
      query: { 
        id: toolDialog.value.result.content_id,
        from: 'ai_tools'
      }
    })
    toolDialog.value.visible = false
  }
}

// 直接提交审核
const goToContentForReview = async () => {
  if (!toolDialog.value.result || !toolDialog.value.result.content_id) {
    ElMessage.warning('没有可提交的内容')
    return
  }
  
  try {
    await ElMessageBox.confirm(
      '确定要提交这个内容进行审核吗？提交后需要等待管理员审核通过才能导出文件。',
      '提交审核确认',
      {
        confirmButtonText: '提交审核',
        cancelButtonText: '取消',
        type: 'info'
      }
    )
    
         // 调用提交审核的API
     const response = await contentApi.submitAudit({
      content_id: toolDialog.value.result.content_id,
      visibility: 'public'
    })
    
    if (response.code === 200) {
      ElNotification({
        title: '提交成功',
        message: '内容已提交审核，您可以在内容库中查看审核状态',
        type: 'success',
        duration: 5000
      })
      
      // 跳转到内容库查看
      router.push({
        path: '/teacher/content',
        query: { 
          status: 'pending',
          highlight: toolDialog.value.result.content_id
        }
      })
      toolDialog.value.visible = false
    } else {
      ElMessage.error(response.message || '提交审核失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('提交审核失败:', error)
      ElMessage.error('提交审核失败：' + error.message)
    }
  }
}

// 查看所有历史
const viewAllHistory = () => {
  router.push('/teacher/ai-history')
}

// 快速使用历史记录
const quickUseFromHistory = (record) => {
  const tool = aiTools.value.find(t => t.code === record.tool_code)
  if (tool) {
    useTool(tool)
    // 可以预填充之前的参数
  }
}



















// 判断工具是否为热门工具
const isPopularTool = (tool) => {
  return tool.is_popular === 1
}

// 获取工具图标
const getToolIcon = (category) => {
  const iconMap = {
    content: 'Document',
    analysis: 'DataAnalysis',
    assessment: 'Star'
  }
  return iconMap[category] || 'MagicStick'
}

// 获取分类文本
const getCategoryText = (category) => {
  const categoryMap = {
    content: '内容生成',
    analysis: '数据分析',
    assessment: '评估工具'
  }
  return categoryMap[category] || category
}

// 获取工具参数配置（使用新的配置化系统）
const getToolParameters = async (tool) => {
  if (!tool || !tool.code) return {}
  try {
    // 用aiToolApi封装的axios请求，自动带token
    const result = await aiToolApi.getToolFormConfig(tool.code)
    console.log('API返回结果:', result)
    if (result.code === 200 && result.data) {
      const params = {}
      result.data.forEach(param => {
        params[param.name] = {
          label: param.label,
          type: param.type,
          required: param.required,
          placeholder: param.placeholder || `请输入${param.label}`,
          options: param.options || [],
          min: param.min,
          max: param.max,
          default: param.default,
          rows: param.type === 'textarea' ? 4 : undefined
        }
      })
      console.log('处理后的参数配置:', params)
      return params
    } else {
      console.error('获取工具配置失败:', result.message)
      return {}
    }
  } catch (error) {
    console.error('获取工具配置出错:', error)
    // 如果API调用失败，回退到旧的参数提取方式
    return getToolParametersFallback(tool)
  }
}

// 回退的旧参数提取方式（兼容性）
const getToolParametersFallback = (tool) => {
  if (!tool || !tool.prompt_template) return {}
  
  const params = {}
  const template = tool.prompt_template
  
  // 英文参数名到中文标签的映射
  const paramLabelsMap = {
    topic: '课程主题',
    duration: '课程时长',
    objectives: '教学目标',
    key_points: '重点内容',
    content: '课程内容',
    question_count: '题目数量',
    difficulty: '难度要求',
    subject: '学科',
    grade: '年级',
    course_name: '课程名称',
    process: '教学过程',
    performance: '学生表现',
    effectiveness: '教学效果',
    feedback: '学生反馈'
  }

  // 从模板中提取参数
  const matches = template.match(/\{(\w+)\}/g) || []
  matches.forEach(match => {
    const paramName = match.replace(/[{}]/g, '')
    const label = paramLabelsMap[paramName] || paramName
    
    if (!params[paramName]) {
      params[paramName] = {
        label: label,
        type: 'text',
        placeholder: `请输入${label}`,
        rows: 3
      }
    }
  })
  
  return params
}

// 格式化日期
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

// 生命周期
onMounted(() => {
  loadAiTools()
  loadUsageStats()
  loadUsageHistory()
})
</script>

<style scoped>
.ai-tools-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

/* 页面标题 */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 24px 0;
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  font-size: 16px;
  color: #6b7280;
  margin: 0;
  font-weight: 400;
}

.header-right {
  display: flex;
  gap: 12px;
}

/* 统计卡片 */
.stats-overview {
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea, #764ba2);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.gradient-blue {
  background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
}

.gradient-green {
  background: linear-gradient(135deg, #48bb7820 0%, #67c23a20 100%);
}

.gradient-purple {
  background: linear-gradient(135deg, #906ded20 0%, #c084fc20 100%);
}

.gradient-orange {
  background: linear-gradient(135deg, #f59e0b20 0%, #f9731620 100%);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: white;
  background: linear-gradient(135deg, #667eea, #764ba2);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

/* 工具筛选 */
.tool-filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

/* AI工具网格 */
.tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
  margin-bottom: 48px;
}

.tool-card {
  background: white;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  border: 2px solid transparent;
  position: relative;
  overflow: hidden;
}

.tool-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea08, #764ba208);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.tool-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
  border-color: #667eea;
}

.tool-card:hover::before {
  opacity: 1;
}

.tool-ribbon {
  position: absolute;
  top: 16px;
  right: -8px;
  background: linear-gradient(135deg, #f59e0b, #f97316);
  color: white;
  padding: 4px 16px;
  font-size: 12px;
  font-weight: 600;
  transform: rotate(8deg);
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}

.tool-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  position: relative;
  z-index: 1;
}

.tool-icon {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 32px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  position: relative;
}

.icon-content {
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.icon-assessment {
  background: linear-gradient(135deg, #48bb78, #67c23a);
}

.icon-analysis {
  background: linear-gradient(135deg, #906ded, #c084fc);
}

.tool-content {
  margin-bottom: 24px;
  position: relative;
  z-index: 1;
}

.tool-name {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 12px;
  line-height: 1.3;
}

.tool-desc {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 16px;
  line-height: 1.6;
}

.tool-meta {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: #9ca3af;
}

.tool-category, .tool-usage {
  display: flex;
  align-items: center;
  gap: 4px;
}

.tool-footer {
  display: flex;
  gap: 12px;
  position: relative;
  z-index: 1;
}

.tool-use-btn {
  flex: 1;
  height: 44px;
  border-radius: 12px;
  font-weight: 600;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.tool-use-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.tool-disabled {
  opacity: 0.6;
  pointer-events: none;
}

/* 工具对话框 */
.tool-dialog .el-dialog {
  border-radius: 20px;
  overflow: hidden;
}

.tool-dialog .el-dialog__header {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 24px 32px;
  margin: 0;
}

.tool-dialog .el-dialog__body {
  padding: 32px;
}

.tool-dialog-content {
  max-height: 70vh;
  overflow-y: auto;
}

/* 工具介绍卡片 */
.tool-intro-card {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 32px;
  border: 1px solid #e2e8f0;
}

.intro-header {
  display: flex;
  align-items: center;
  gap: 20px;
}

.intro-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.intro-content h4 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px;
}

.intro-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
  line-height: 1.5;
}

/* 参数配置区域 */
.parameter-section {
  margin-bottom: 32px;
}

.parameter-section h4 {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 20px;
  color: #374151;
  font-size: 18px;
  font-weight: 600;
}

.params-form {
  background: #f9fafb;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e5e7eb;
}

.param-item {
  margin-bottom: 20px;
}

.textarea-input .el-textarea__inner {
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-family: 'Inter', sans-serif;
}

.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.checkbox-item {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px 16px;
  transition: all 0.2s ease;
}

.checkbox-item:hover {
  border-color: #667eea;
  background: #667eea08;
}

.course-select-field {
  width: 100%;
}

/* 高级选项 */
.advanced-options {
  margin-bottom: 32px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}

.advanced-content {
  padding: 20px;
  background: #f9fafb;
}

.slider-tips {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #9ca3af;
  margin-top: 8px;
}

/* 操作区域 */
.action-section {
  text-align: center;
  margin-bottom: 32px;
}

.action-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-bottom: 16px;
}

.generate-btn {
  height: 50px;
  padding: 0 32px;
  font-size: 16px;
  font-weight: 600;
  background: linear-gradient(135deg, #10b981, #059669);
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.generate-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.action-tips {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  color: #6b7280;
}

/* 生成进度 */
.generation-progress {
  margin: 24px 0;
  text-align: center;
}

.progress-text {
  margin-top: 12px;
  font-size: 14px;
  color: #667eea;
  font-weight: 500;
}

/* 结果区域 */
.result-section {
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  background: white;
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.result-header h4 {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  color: #374151;
  font-size: 18px;
  font-weight: 600;
}

.result-textarea .el-textarea__inner {
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  line-height: 1.6;
}

.result-actions {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

.workflow-tip {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding: 12px 16px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 8px;
  font-size: 14px;
  color: #0369a1;
  text-align: left;
}

.workflow-tip .el-icon {
  font-size: 16px;
  color: #0284c7;
}

/* 最近使用工具 */
.recent-tools {
  background: white;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 32px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  color: #374151;
  font-size: 20px;
  font-weight: 600;
}

.recent-tools-list {
  display: grid;
  gap: 16px;
}

.recent-tool-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.recent-tool-item:hover {
  border-color: #667eea;
  background: #667eea08;
  transform: translateX(4px);
}

.recent-tool-info {
  flex: 1;
}

.recent-tool-name {
  font-weight: 600;
  color: #374151;
  margin-bottom: 4px;
}

.recent-tool-time {
  font-size: 13px;
  color: #9ca3af;
}

/* 空状态 */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.empty-icon {
  font-size: 64px;
  color: #d1d5db;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 20px;
  color: #6b7280;
  margin: 0 0 12px;
  font-weight: 600;
}

.empty-state p {
  font-size: 14px;
  color: #9ca3af;
  margin: 0 0 24px;
}



/* 响应式设计 */
@media (max-width: 1200px) {
  .tools-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

@media (max-width: 768px) {
  .ai-tools-container {
    padding: 0 16px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 20px;
    align-items: flex-start;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .tools-grid {
    grid-template-columns: 1fr;
  }
  
  .tool-filters {
    flex-direction: column;
    gap: 16px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .tool-dialog .el-dialog {
    width: 95% !important;
    margin: 20px auto;
  }
}
</style> 
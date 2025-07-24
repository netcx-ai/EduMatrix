<template>
  <div class="course-select-container">
    <el-select
      v-model="selectedCourse"
      :placeholder="placeholder"
      :clearable="clearable"
      :disabled="disabled"
      @change="handleChange"
      class="course-select"
      filterable
      remote
      reserve-keyword
      :remote-method="searchCourses"
      :loading="loading"
      :size="size"
    >
      <el-option-group
        v-for="group in courseGroups"
        :key="group.label"
        :label="group.label"
      >
        <el-option
          v-for="course in group.options"
          :key="course.value"
          :label="course.label"
          :value="course.value"
        >
          <div class="course-option">
            <div class="course-name">{{ course.label }}</div>
            <div class="course-meta">
              <span class="course-code">{{ course.course_code }}</span>
              <span class="course-role" v-if="course.role">{{ course.role }}</span>
            </div>
          </div>
        </el-option>
      </el-option-group>
    </el-select>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/api/user'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  placeholder: {
    type: String,
    default: '请选择课程'
  },
  clearable: {
    type: Boolean,
    default: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'default'
  },
  format: {
    type: String,
    default: 'grouped', // grouped | flat
    validator: (value) => ['grouped', 'flat'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// 响应式数据
const selectedCourse = ref(props.modelValue)
const courseGroups = ref([])
const loading = ref(false)
const allCourses = ref([])

// 监听外部值变化
watch(() => props.modelValue, (newVal) => {
  selectedCourse.value = newVal
})

// 加载课程数据
const loadCourses = async () => {
  try {
    loading.value = true
    const response = await api.get('/teacher/courses/options', {
      params: { format: props.format }
    })
    
    if (response.code === 200) {
      if (props.format === 'grouped') {
        courseGroups.value = response.data
      } else {
        // 平铺格式转换为分组格式
        courseGroups.value = [{
          label: '全部课程',
          options: response.data
        }]
      }
      
      // 存储所有课程用于搜索
      allCourses.value = response.data.reduce((acc, group) => {
        return acc.concat(group.options || [])
      }, [])
    }
  } catch (error) {
    console.error('加载课程列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 搜索课程
const searchCourses = async (query) => {
  if (!query) {
    await loadCourses()
    return
  }
  
  const filtered = allCourses.value.filter(course => 
    course.label.toLowerCase().includes(query.toLowerCase()) ||
    (course.course_code && course.course_code.toLowerCase().includes(query.toLowerCase()))
  )
  
  courseGroups.value = [{
    label: '搜索结果',
    options: filtered
  }]
}

// 处理选择变化
const handleChange = (value) => {
  emit('update:modelValue', value)
  emit('change', value)
}

// 组件挂载时加载数据
onMounted(() => {
  loadCourses()
})
</script>

<style scoped>
.course-select-container {
  width: 100%;
}

.course-select {
  width: 100%;
}

.course-option {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.course-name {
  font-weight: 500;
  color: #303133;
}

.course-meta {
  display: flex;
  gap: 8px;
  font-size: 12px;
  color: #909399;
}

.course-code {
  background: #f0f2f5;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
}

.course-role {
  color: #409eff;
  font-weight: 500;
}

/* 分组标题样式 */
:deep(.el-select-group__title) {
  font-weight: 600;
  color: #606266;
  background: #f5f7fa;
  padding: 8px 12px;
  border-bottom: 1px solid #e4e7ed;
}

/* 选项样式 */
:deep(.el-select-dropdown__item) {
  padding: 8px 12px;
  height: auto;
  line-height: 1.4;
}

:deep(.el-select-dropdown__item:hover) {
  background: #f5f7fa;
}
</style> 
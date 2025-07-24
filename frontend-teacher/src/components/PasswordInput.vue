<template>
  <div class="password-input-wrapper">
    <el-form-item 
      :label="label" 
      :prop="prop"
      :rules="passwordRules">
      <el-input 
        v-model="password" 
        :type="showPassword ? 'text' : 'password'"
        :placeholder="placeholder"
        @input="handleInput">
        <template #suffix>
          <el-icon 
            class="password-eye"
            @click="togglePasswordVisibility">
            <View v-if="showPassword"/>
            <Hide v-else/>
          </el-icon>
        </template>
      </el-input>
      <!-- 密码强度指示器 -->
      <div v-if="showStrength" class="password-strength">
        <div class="strength-label">密码强度:</div>
        <div class="strength-bars">
          <div 
            v-for="n in 3" 
            :key="n"
            class="strength-bar"
            :class="[
              {'active': passwordStrength >= n},
              strengthLevel
            ]">
          </div>
        </div>
        <div class="strength-text" :class="strengthLevel">
          {{ strengthText }}
        </div>
      </div>
      <!-- 密码要求提示 -->
      <div class="password-requirements">
        <div class="requirement" :class="{ met: hasLength }">
          ✓ 8-32个字符
        </div>
        <div class="requirement" :class="{ met: hasUpperCase }">
          ✓ 至少1个大写字母
        </div>
        <div class="requirement" :class="{ met: hasLowerCase }">
          ✓ 至少1个小写字母
        </div>
        <div class="requirement" :class="{ met: hasNumber }">
          ✓ 至少1个数字
        </div>
      </div>
    </el-form-item>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { View, Hide } from '@element-plus/icons-vue'

export default {
  name: 'PasswordInput',
  components: { View, Hide },
  props: {
    modelValue: String,
    label: String,
    prop: String,
    placeholder: {
      type: String,
      default: '请输入密码'
    },
    showStrength: {
      type: Boolean,
      default: true
    }
  },
  emits: ['update:modelValue', 'strength-change'],
  setup(props, { emit }) {
    const password = computed({
      get: () => props.modelValue,
      set: (value) => emit('update:modelValue', value)
    })
    
    const showPassword = ref(false)
    
    // 密码规则检查
    const hasLength = computed(() => {
      return password.value?.length >= 8 && password.value?.length <= 32
    })
    const hasUpperCase = computed(() => /[A-Z]/.test(password.value || ''))
    const hasLowerCase = computed(() => /[a-z]/.test(password.value || ''))
    const hasNumber = computed(() => /\d/.test(password.value || ''))
    
    // 计算密码强度
    const passwordStrength = computed(() => {
      let strength = 0
      if (hasLength.value) strength++
      if (hasUpperCase.value && hasLowerCase.value) strength++
      if (hasNumber.value) strength++
      return strength
    })
    
    // 密码强度等级
    const strengthLevel = computed(() => {
      if (passwordStrength.value <= 1) return 'weak'
      if (passwordStrength.value === 2) return 'medium'
      return 'strong'
    })
    
    // 密码强度文字
    const strengthText = computed(() => {
      switch (strengthLevel.value) {
        case 'weak': return '弱'
        case 'medium': return '中'
        case 'strong': return '强'
        default: return ''
      }
    })
    
    // 密码验证规则
    const passwordRules = [
      { required: true, message: '请输入密码', trigger: 'blur' },
      { min: 8, max: 32, message: '密码长度必须在8-32个字符之间', trigger: 'blur' },
      { 
        validator: (rule, value, callback) => {
          if (value && !hasUpperCase.value) {
            callback(new Error('密码必须包含至少1个大写字母'))
          } else if (value && !hasLowerCase.value) {
            callback(new Error('密码必须包含至少1个小写字母'))
          } else if (value && !hasNumber.value) {
            callback(new Error('密码必须包含至少1个数字'))
          } else {
            callback()
          }
        },
        trigger: 'blur'
      }
    ]
    
    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value
    }
    
    const handleInput = () => {
      emit('strength-change', {
        strength: passwordStrength.value,
        level: strengthLevel.value
      })
    }
    
    return {
      password,
      showPassword,
      passwordRules,
      hasLength,
      hasUpperCase,
      hasLowerCase,
      hasNumber,
      passwordStrength,
      strengthLevel,
      strengthText,
      togglePasswordVisibility,
      handleInput
    }
  }
}
</script>

<style scoped>
.password-input-wrapper {
  width: 100%;
}

.password-eye {
  cursor: pointer;
}

.password-strength {
  margin-top: 8px;
  display: flex;
  align-items: center;
}

.strength-label {
  margin-right: 8px;
  font-size: 12px;
  color: #606266;
}

.strength-bars {
  display: flex;
  gap: 4px;
}

.strength-bar {
  width: 30px;
  height: 4px;
  background: #DCDFE6;
  border-radius: 2px;
  transition: all 0.3s;
}

.strength-bar.active.weak {
  background: #F56C6C;
}

.strength-bar.active.medium {
  background: #E6A23C;
}

.strength-bar.active.strong {
  background: #67C23A;
}

.strength-text {
  margin-left: 8px;
  font-size: 12px;
}

.strength-text.weak {
  color: #F56C6C;
}

.strength-text.medium {
  color: #E6A23C;
}

.strength-text.strong {
  color: #67C23A;
}

.password-requirements {
  margin-top: 8px;
  font-size: 12px;
  color: #909399;
}

.requirement {
  margin: 4px 0;
}

.requirement.met {
  color: #67C23A;
}
</style> 
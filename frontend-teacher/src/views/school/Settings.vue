<template>
  <div class="settings-page">
    <Layout>
      <template #content>
        <div class="page-header">
          <h2>⚙️ 学校设置</h2>
        </div>

        <el-tabs v-model="activeTab" class="settings-tabs">
          <!-- 基本信息 -->
          <el-tab-pane label="基本信息" name="basic">
            <el-card class="setting-card">
              <template #header>
                <div class="card-header">
                  <span>🏫 学校基本信息</span>
                </div>
              </template>
              
              <el-form
                ref="basicFormRef"
                :model="basicForm"
                :rules="basicRules"
                label-width="120px"
              >
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="学校名称" prop="name">
                      <el-input v-model="basicForm.name" placeholder="请输入学校名称" />
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="学校代码" prop="code">
                      <el-input v-model="basicForm.code" placeholder="请输入学校代码" />
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="联系人" prop="contact">
                      <el-input v-model="basicForm.contact" placeholder="请输入联系人姓名" />
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="联系电话" prop="phone">
                      <el-input v-model="basicForm.phone" placeholder="请输入联系电话" />
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-form-item label="邮箱" prop="email">
                      <el-input v-model="basicForm.email" placeholder="请输入邮箱" />
                    </el-form-item>
                  </el-col>
                  <el-col :span="12">
                    <el-form-item label="地址" prop="address">
                      <el-input v-model="basicForm.address" placeholder="请输入学校地址" />
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-form-item label="学校简介" prop="description">
                  <el-input
                    v-model="basicForm.description"
                    type="textarea"
                    :rows="4"
                    placeholder="请输入学校简介"
                  />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="saveBasicInfo" :loading="saving">
                    保存基本信息
                  </el-button>
                </el-form-item>
              </el-form>
            </el-card>
          </el-tab-pane>

          <!-- 公告管理 -->
          <el-tab-pane label="公告管理" name="announcement">
            <el-card class="setting-card">
              <template #header>
                <div class="card-header">
                  <span>📢 公告管理</span>
                  <el-button type="primary" @click="showAnnouncementDialog">
                    <el-icon><Plus /></el-icon>
                    发布公告
                  </el-button>
                </div>
              </template>
              
              <el-table :data="announcements" v-loading="loading" style="width: 100%">
                <el-table-column prop="title" label="标题" min-width="200" />
                <el-table-column prop="content" label="内容" min-width="300" show-overflow-tooltip />
                <el-table-column prop="status" label="状态" width="100" align="center">
                  <template #default="scope">
                    <el-tag :type="scope.row.status === 'published' ? 'success' : 'info'">
                      {{ scope.row.status === 'published' ? '已发布' : '草稿' }}
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column prop="publishTime" label="发布时间" width="180" />
                <el-table-column label="操作" width="200" fixed="right">
                  <template #default="scope">
                    <el-button size="small" @click="viewAnnouncement(scope.row)">查看</el-button>
                    <el-button size="small" type="primary" @click="editAnnouncement(scope.row)">编辑</el-button>
                    <el-button size="small" type="danger" @click="deleteAnnouncement(scope.row)">删除</el-button>
                  </template>
                </el-table-column>
              </el-table>
            </el-card>
          </el-tab-pane>

          <!-- 系统设置 -->
          <el-tab-pane label="系统设置" name="system">
            <el-card class="setting-card">
              <template #header>
                <div class="card-header">
                  <span>🔧 系统设置</span>
                </div>
              </template>
              
              <el-form
                ref="systemFormRef"
                :model="systemForm"
                label-width="150px"
              >
                <el-form-item label="教师注册审核">
                  <el-switch
                    v-model="systemForm.teacherApproval"
                    active-text="需要审核"
                    inactive-text="自动通过"
                  />
                </el-form-item>
                <el-form-item label="课程发布审核">
                  <el-switch
                    v-model="systemForm.courseApproval"
                    active-text="需要审核"
                    inactive-text="自动通过"
                  />
                </el-form-item>
                <el-form-item label="文件上传限制">
                  <el-input-number
                    v-model="systemForm.fileSizeLimit"
                    :min="1"
                    :max="100"
                    :step="1"
                  />
                  <span style="margin-left: 10px;">MB</span>
                </el-form-item>
                <el-form-item label="AI工具使用限制">
                  <el-input-number
                    v-model="systemForm.aiUsageLimit"
                    :min="0"
                    :max="1000"
                    :step="10"
                  />
                  <span style="margin-left: 10px;">次/天</span>
                </el-form-item>
                <el-form-item label="系统维护模式">
                  <el-switch
                    v-model="systemForm.maintenanceMode"
                    active-text="开启"
                    inactive-text="关闭"
                  />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="saveSystemSettings" :loading="saving">
                    保存系统设置
                  </el-button>
                </el-form-item>
              </el-form>
            </el-card>
          </el-tab-pane>
        </el-tabs>

        <!-- 公告对话框 -->
        <el-dialog
          v-model="announcementDialogVisible"
          :title="announcementDialogTitle"
          width="700px"
          @close="resetAnnouncementForm"
        >
          <el-form
            ref="announcementFormRef"
            :model="announcementForm"
            :rules="announcementRules"
            label-width="100px"
          >
            <el-form-item label="标题" prop="title">
              <el-input v-model="announcementForm.title" placeholder="请输入公告标题" />
            </el-form-item>
            <el-form-item label="内容" prop="content">
              <el-input
                v-model="announcementForm.content"
                type="textarea"
                :rows="6"
                placeholder="请输入公告内容"
              />
            </el-form-item>
            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="announcementForm.status">
                <el-radio label="published">立即发布</el-radio>
                <el-radio label="draft">保存草稿</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-form>
          <template #footer>
            <span class="dialog-footer">
              <el-button @click="announcementDialogVisible = false">取消</el-button>
              <el-button type="primary" @click="submitAnnouncement" :loading="submitting">
                确定
              </el-button>
            </span>
          </template>
        </el-dialog>

        <!-- 公告详情对话框 -->
        <el-dialog
          v-model="detailVisible"
          title="公告详情"
          width="600px"
        >
          <div v-if="currentAnnouncement" class="announcement-detail">
            <h3>{{ currentAnnouncement.title }}</h3>
            <div class="announcement-meta">
              <span>发布时间：{{ currentAnnouncement.publishTime }}</span>
              <el-tag :type="currentAnnouncement.status === 'published' ? 'success' : 'info'">
                {{ currentAnnouncement.status === 'published' ? '已发布' : '草稿' }}
              </el-tag>
            </div>
            <div class="announcement-content">
              {{ currentAnnouncement.content }}
            </div>
          </div>
        </el-dialog>
      </template>
    </Layout>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { schoolApi } from '@/api/school'

export default {
  name: 'Settings',
  components: {
    Layout,
    Plus
  },
  setup() {
    const activeTab = ref('basic')
    const loading = ref(false)
    const saving = ref(false)
    const submitting = ref(false)
    const announcementDialogVisible = ref(false)
    const detailVisible = ref(false)
    const announcementDialogTitle = ref('发布公告')
    const isEdit = ref(false)
    const currentAnnouncement = ref(null)

    // 表单引用
    const basicFormRef = ref()
    const systemFormRef = ref()
    const announcementFormRef = ref()

    // 基本信息表单
    const basicForm = reactive({
      name: '',
      code: '',
      contact: '',
      phone: '',
      email: '',
      address: '',
      description: ''
    })

    // 系统设置表单
    const systemForm = reactive({
      teacherApproval: true,
      courseApproval: true,
      fileSizeLimit: 50,
      aiUsageLimit: 100,
      maintenanceMode: false
    })

    // 公告表单
    const announcementForm = reactive({
      id: null,
      title: '',
      content: '',
      status: 'published'
    })

    // 表单验证规则
    const basicRules = {
      name: [
        { required: true, message: '请输入学校名称', trigger: 'blur' }
      ],
      code: [
        { required: true, message: '请输入学校代码', trigger: 'blur' }
      ],
      contact: [
        { required: true, message: '请输入联系人', trigger: 'blur' }
      ],
      phone: [
        { required: true, message: '请输入联系电话', trigger: 'blur' }
      ],
      email: [
        { required: true, message: '请输入邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
      ]
    }

    const announcementRules = {
      title: [
        { required: true, message: '请输入公告标题', trigger: 'blur' }
      ],
      content: [
        { required: true, message: '请输入公告内容', trigger: 'blur' }
      ]
    }

    // 数据列表
    const announcements = ref([])

    // 获取学校信息
    const getSchoolInfo = async () => {
      try {
        const res = await schoolApi.getSchoolInfo()
        Object.assign(basicForm, res.data)
      } catch (error) {
        console.error('获取学校信息失败:', error)
      }
    }

    // 获取公告列表
    const getAnnouncements = async () => {
      loading.value = true
      try {
        const res = await schoolApi.getAnnouncements()
        announcements.value = res.data.list || []
      } catch (error) {
        ElMessage.error('获取公告列表失败')
        console.error(error)
      } finally {
        loading.value = false
      }
    }

    // 保存基本信息
    const saveBasicInfo = async () => {
      if (!basicFormRef.value) return

      try {
        await basicFormRef.value.validate()
        saving.value = true
        await schoolApi.updateSchoolInfo(basicForm)
        ElMessage.success('保存成功')
      } catch (error) {
        if (error !== false) {
          ElMessage.error('保存失败')
          console.error(error)
        }
      } finally {
        saving.value = false
      }
    }

    // 保存系统设置
    const saveSystemSettings = async () => {
      try {
        saving.value = true
        // TODO: 调用系统设置API
        ElMessage.success('保存成功')
      } catch (error) {
        ElMessage.error('保存失败')
        console.error(error)
      } finally {
        saving.value = false
      }
    }

    // 显示公告对话框
    const showAnnouncementDialog = () => {
      announcementDialogTitle.value = '发布公告'
      isEdit.value = false
      announcementDialogVisible.value = true
    }

    // 编辑公告
    const editAnnouncement = (row) => {
      announcementDialogTitle.value = '编辑公告'
      isEdit.value = true
      Object.assign(announcementForm, row)
      announcementDialogVisible.value = true
    }

    // 查看公告详情
    const viewAnnouncement = (row) => {
      currentAnnouncement.value = row
      detailVisible.value = true
    }

    // 删除公告
    const deleteAnnouncement = async (row) => {
      try {
        await ElMessageBox.confirm(
          `确定要删除公告"${row.title}"吗？`,
          '确认删除',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )

        await schoolApi.deleteAnnouncement(row.id)
        ElMessage.success('删除成功')
        getAnnouncements()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('删除失败')
          console.error(error)
        }
      }
    }

    // 提交公告
    const submitAnnouncement = async () => {
      if (!announcementFormRef.value) return

      try {
        await announcementFormRef.value.validate()
        submitting.value = true

        if (isEdit.value) {
          await schoolApi.updateAnnouncement(announcementForm.id, announcementForm)
          ElMessage.success('更新成功')
        } else {
          await schoolApi.createAnnouncement(announcementForm)
          ElMessage.success('发布成功')
        }

        announcementDialogVisible.value = false
        getAnnouncements()
      } catch (error) {
        if (error !== false) {
          ElMessage.error(isEdit.value ? '更新失败' : '发布失败')
          console.error(error)
        }
      } finally {
        submitting.value = false
      }
    }

    // 重置公告表单
    const resetAnnouncementForm = () => {
      if (announcementFormRef.value) {
        announcementFormRef.value.resetFields()
      }
      Object.assign(announcementForm, {
        id: null,
        title: '',
        content: '',
        status: 'published'
      })
    }

    onMounted(() => {
      getSchoolInfo()
      getAnnouncements()
    })

    return {
      activeTab,
      loading,
      saving,
      submitting,
      announcementDialogVisible,
      detailVisible,
      announcementDialogTitle,
      basicForm,
      systemForm,
      announcementForm,
      basicRules,
      announcementRules,
      announcements,
      currentAnnouncement,
      basicFormRef,
      systemFormRef,
      announcementFormRef,
      saveBasicInfo,
      saveSystemSettings,
      showAnnouncementDialog,
      editAnnouncement,
      viewAnnouncement,
      deleteAnnouncement,
      submitAnnouncement,
      resetAnnouncementForm
    }
  }
}
</script>

<style scoped>
.settings-page {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}

.settings-tabs {
  background: #fff;
  border-radius: 4px;
}

.setting-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.announcement-detail {
  padding: 20px 0;
}

.announcement-detail h3 {
  margin-bottom: 15px;
  color: #333;
}

.announcement-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  color: #666;
  font-size: 0.9em;
}

.announcement-content {
  line-height: 1.6;
  color: #333;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style> 
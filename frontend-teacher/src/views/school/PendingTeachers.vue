<template>
  <div class="page-pending-teachers" v-loading="loading">
    <el-card>
      <h2>待审核教师</h2>
      <el-table :data="list" style="width:100%" stripe>
        <el-table-column prop="id" label="ID" width="80"/>
        <el-table-column prop="real_name" label="姓名"/>
        <el-table-column prop="phone" label="手机号"/>
        <el-table-column prop="email" label="邮箱"/>
        <el-table-column prop="apply_time" label="申请时间"/>
        <el-table-column label="操作" width="180">
          <template #default="{row}">
            <el-button size="small" type="success" @click="approve(row.id)">通过</el-button>
            <el-button size="small" type="danger" @click="reject(row.id)">拒绝</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-if="pagination.total>pagination.pageSize"
                     background layout="prev, pager, next" :total="pagination.total"
                     :page-size="pagination.pageSize" @current-change="changePage" />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/utils/axios';
import { ElMessage } from 'element-plus';

const list = ref([]);
const loading = ref(false);
const pagination = ref({ page:1, pageSize:20, total:0 });

const fetchList = async ()=>{
  loading.value=true;
  try{
    const res = await axios.get('/school/teacher/pending', { params:{ page:pagination.value.page, limit:pagination.value.pageSize } });
    if(res.code===0){
      list.value = res.data.items || res.data.list || [];
      pagination.value.total = res.data.total || 0;
    }else{ElMessage.error(res.msg||'获取失败');}
  }catch(e){ElMessage.error('网络错误');}
  loading.value=false;
};

const changePage = p=>{ pagination.value.page=p; fetchList(); };

const approve = async id=>{
  try{
    const res = await axios.post('/school/teacher/approve', { id });
    if(res.code===0){ ElMessage.success('已通过'); fetchList(); } else{ ElMessage.error(res.msg||'操作失败'); }
  }catch(e){ ElMessage.error('网络错误'); }
};
const reject = async id=>{
  try{
    const res = await axios.post('/school/teacher/reject', { id });
    if(res.code===0){ ElMessage.success('已拒绝'); fetchList(); } else{ ElMessage.error(res.msg||'操作失败'); }
  }catch(e){ ElMessage.error('网络错误'); }
};

onMounted(fetchList);
</script>

<style scoped>
.page-pending-teachers{max-width:1000px;margin:20px auto;}
</style> 
<template>
  <div class="page-password" v-loading="loading">
    <el-card>
      <h2>修改密码</h2>
      <el-form :model="form" :rules="rules" ref="formRef" label-width="120px">
        <el-form-item label="原密码" prop="old_password">
          <el-input v-model="form.old_password" type="password" autocomplete="off" />
        </el-form-item>
        <el-form-item label="新密码" prop="new_password">
          <el-input v-model="form.new_password" type="password" autocomplete="off" />
        </el-form-item>
        <el-form-item label="确认密码" prop="confirm_password">
          <el-input v-model="form.confirm_password" type="password" autocomplete="off" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submit">保存</el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import axios from '@/utils/axios';

const loading = ref(false);
const form = ref({ old_password:'', new_password:'', confirm_password:'' });
const formRef = ref();
const rules = {
  old_password:[{required:true,message:'请输入原密码',trigger:'blur'}],
  new_password:[{required:true,message:'请输入新密码',trigger:'blur'}],
  confirm_password:[
    {required:true,message:'请确认密码',trigger:'blur'},
    { validator:(_,v,cb)=>{ v!==form.value.new_password? cb('两次密码不一致'):cb(); } ,trigger:'blur'}
  ]
};

const submit = ()=>{
  formRef.value.validate(async valid=>{
    if(!valid) return;
    loading.value=true;
    try{
      const res = await axios.post('/school/changePassword', form.value);
      if(res.code===0){ ElMessage.success('修改成功'); history.back(); }
      else{ ElMessage.error(res.msg||'修改失败'); }
    }catch(e){ ElMessage.error('网络错误'); }
    loading.value=false;
  });
};
</script>

<style scoped>
.page-password{max-width:600px;margin:30px auto;}
</style> 
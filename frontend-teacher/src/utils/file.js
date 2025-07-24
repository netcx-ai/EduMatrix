import { ElMessage } from 'element-plus'

export const downloadById = async (file) => {
  if (!file || !file.id) return
  console.log('downloadById -> id:', file.id, 'name:', file.original_name || file.name)
  try {
    const token = localStorage.getItem('token')
    const resp = await fetch(`/api/teacher/files/${file.id}/raw`, {
      headers: {
        Authorization: token ? `Bearer ${token}` : ''
      }
    })
    if (!resp.ok) throw new Error('网络错误')
    const blob = await resp.blob()
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    // 兼容接口字段
    const filename = file.original_name || file.name || 'download'
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  } catch (err) {
    console.error(err)
    ElMessage.error('下载失败')
  }
} 
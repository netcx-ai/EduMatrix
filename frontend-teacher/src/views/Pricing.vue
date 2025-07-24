<template>
  <div class="pricing">
    <el-header class="header">
      <div class="header-content">
        <div class="logo">
          <h2>🏠 EduMatrix 教育管理系统</h2>
        </div>
        <div class="nav-menu">
          <el-menu mode="horizontal" :router="true" class="nav-menu">
            <el-menu-item index="/">首页</el-menu-item>
            <el-menu-item index="/help">帮助中心</el-menu-item>
            <el-menu-item index="/pricing">定价方案</el-menu-item>
            <el-menu-item index="/login">登录</el-menu-item>
            <el-menu-item index="/register">注册</el-menu-item>
          </el-menu>
        </div>
      </div>
    </el-header>

    <el-main class="main-content">
      <div class="pricing-header">
        <h1>💰 定价方案</h1>
        <p>选择最适合您需求的套餐方案</p>
      </div>

      <!-- 计费周期切换 -->
      <div class="billing-toggle">
        <el-radio-group v-model="billingCycle">
          <el-radio-button label="monthly">按月付费</el-radio-button>
          <el-radio-button label="yearly">按年付费</el-radio-button>
        </el-radio-group>
        <div class="discount-badge" v-if="billingCycle === 'yearly'">
          <el-tag type="success">年付享8折优惠</el-tag>
        </div>
      </div>

      <!-- 套餐卡片 -->
      <div class="pricing-cards">
        <el-row :gutter="30">
          <!-- 基础版 -->
          <el-col :span="8">
            <el-card class="pricing-card">
              <div class="card-header">
                <h3>基础版</h3>
                <div class="price">
                  <span class="currency">¥</span>
                  <span class="amount">{{ getPrice('basic') }}</span>
                  <span class="period">/{{ billingCycle === 'monthly' ? '月' : '年' }}</span>
                </div>
                <p class="description">适合小型教育机构</p>
              </div>
              
              <div class="features">
                <h4>包含功能：</h4>
                <ul>
                  <li>✓ 最多50个教师账号</li>
                  <li>✓ 基础课程管理</li>
                  <li>✓ 文件存储 10GB</li>
                  <li>✓ 基础AI工具（每月100次）</li>
                  <li>✓ 邮件支持</li>
                </ul>
              </div>
              
              <div class="card-footer">
                <el-button type="primary" size="large" @click="selectPlan('basic')">
                  选择基础版
                </el-button>
              </div>
            </el-card>
          </el-col>

          <!-- 专业版 -->
          <el-col :span="8">
            <el-card class="pricing-card popular">
              <div class="popular-badge">推荐</div>
              <div class="card-header">
                <h3>专业版</h3>
                <div class="price">
                  <span class="currency">¥</span>
                  <span class="amount">{{ getPrice('professional') }}</span>
                  <span class="period">/{{ billingCycle === 'monthly' ? '月' : '年' }}</span>
                </div>
                <p class="description">适合中型教育机构</p>
              </div>
              
              <div class="features">
                <h4>包含功能：</h4>
                <ul>
                  <li>✓ 最多200个教师账号</li>
                  <li>✓ 完整课程管理</li>
                  <li>✓ 文件存储 100GB</li>
                  <li>✓ 完整AI工具（每月500次）</li>
                  <li>✓ 优先技术支持</li>
                  <li>✓ 详细数据统计</li>
                </ul>
              </div>
              
              <div class="card-footer">
                <el-button type="primary" size="large" @click="selectPlan('professional')">
                  选择专业版
                </el-button>
              </div>
            </el-card>
          </el-col>

          <!-- 企业版 -->
          <el-col :span="8">
            <el-card class="pricing-card">
              <div class="card-header">
                <h3>企业版</h3>
                <div class="price">
                  <span class="currency">¥</span>
                  <span class="amount">{{ getPrice('enterprise') }}</span>
                  <span class="period">/{{ billingCycle === 'monthly' ? '月' : '年' }}</span>
                </div>
                <p class="description">适合大型教育机构</p>
              </div>
              
              <div class="features">
                <h4>包含功能：</h4>
                <ul>
                  <li>✓ 无限教师账号</li>
                  <li>✓ 高级课程管理</li>
                  <li>✓ 文件存储 1TB</li>
                  <li>✓ 完整AI工具（无限次）</li>
                  <li>✓ 专属客户经理</li>
                  <li>✓ 高级数据分析</li>
                </ul>
              </div>
              
              <div class="card-footer">
                <el-button type="primary" size="large" @click="selectPlan('enterprise')">
                  选择企业版
                </el-button>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <!-- 功能对比表 -->
      <div class="feature-comparison">
        <h2>功能对比</h2>
        <el-table :data="featureComparison" border style="width: 100%">
          <el-table-column prop="feature" label="功能" width="200" />
          <el-table-column prop="basic" label="基础版" align="center" />
          <el-table-column prop="professional" label="专业版" align="center" />
          <el-table-column prop="enterprise" label="企业版" align="center" />
        </el-table>
      </div>

      <!-- 常见问题 -->
      <div class="faq-section">
        <h2>常见问题</h2>
        <el-collapse v-model="activeFAQ">
          <el-collapse-item title="可以随时升级或降级套餐吗？" name="upgrade">
            <p>是的，您可以随时升级套餐，升级后立即生效。降级需要等到当前计费周期结束后生效。</p>
          </el-collapse-item>
          <el-collapse-item title="是否支持免费试用？" name="trial">
            <p>是的，我们提供14天免费试用，无需信用卡，可以体验所有功能。</p>
          </el-collapse-item>
          <el-collapse-item title="如何计算AI工具使用次数？" name="ai-usage">
            <p>每次调用AI工具（如生成讲稿、作业等）计为1次使用。失败的重试不计入使用次数。</p>
          </el-collapse-item>
          <el-collapse-item title="是否支持自定义功能开发？" name="custom">
            <p>企业版支持自定义功能开发，我们会根据您的需求提供定制化解决方案。</p>
          </el-collapse-item>
        </el-collapse>
      </div>

      <!-- 联系销售 -->
      <div class="contact-sales">
        <el-card class="sales-card">
          <h3>需要更多帮助？</h3>
          <p>我们的销售团队随时为您提供专业的咨询服务</p>
          <div class="sales-actions">
            <el-button type="primary" size="large" @click="contactSales">
              联系销售
            </el-button>
            <el-button size="large" @click="scheduleDemo">
              预约演示
            </el-button>
          </div>
        </el-card>
      </div>
    </el-main>

    <el-footer class="footer">
      <p>&copy; 2024 EduMatrix 教育管理系统. 保留所有权利.</p>
    </el-footer>
  </div>
</template>

<script>
export default {
  name: 'Pricing',
  data() {
    return {
      billingCycle: 'monthly',
      activeFAQ: ['upgrade'],
      pricing: {
        monthly: {
          basic: 299,
          professional: 599,
          enterprise: 1999
        },
        yearly: {
          basic: 2870, // 299 * 12 * 0.8
          professional: 5750, // 599 * 12 * 0.8
          enterprise: 19190 // 1999 * 12 * 0.8
        }
      },
      featureComparison: [
        {
          feature: '教师账号数量',
          basic: '最多50个',
          professional: '最多200个',
          enterprise: '无限'
        },
        {
          feature: '文件存储',
          basic: '10GB',
          professional: '100GB',
          enterprise: '1TB'
        },
        {
          feature: 'AI工具使用次数',
          basic: '每月100次',
          professional: '每月500次',
          enterprise: '无限'
        },
        {
          feature: '技术支持',
          basic: '邮件支持',
          professional: '优先技术支持',
          enterprise: '专属客户经理'
        },
        {
          feature: 'API接口',
          basic: '不支持',
          professional: '基础API',
          enterprise: '完整API'
        },
        {
          feature: '自定义品牌',
          basic: '不支持',
          professional: '支持',
          enterprise: '支持'
        }
      ]
    }
  },
  methods: {
    getPrice(plan) {
      return this.pricing[this.billingCycle][plan]
    },
    selectPlan(plan) {
      this.$router.push({
        path: '/register',
        query: { plan, billing: this.billingCycle }
      })
    },
    contactSales() {
      this.$message.info('请联系我们的销售团队：sales@edumatrix.com')
    },
    scheduleDemo() {
      this.$message.info('请发送邮件预约演示：demo@edumatrix.com')
    }
  }
}
</script>

<style scoped>
.pricing {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.logo h2 {
  margin: 0;
  color: #409EFF;
}

.nav-menu {
  border-bottom: none;
}

.main-content {
  margin-top: 60px;
  padding: 20px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.pricing-header {
  text-align: center;
  margin-bottom: 40px;
}

.pricing-header h1 {
  font-size: 2.5em;
  color: #333;
  margin-bottom: 10px;
}

.pricing-header p {
  font-size: 1.2em;
  color: #666;
}

.billing-toggle {
  text-align: center;
  margin-bottom: 40px;
}

.discount-badge {
  margin-top: 10px;
}

.pricing-cards {
  margin-bottom: 60px;
}

.pricing-card {
  position: relative;
  text-align: center;
  transition: transform 0.3s;
  height: 500px;
}

.pricing-card:hover {
  transform: translateY(-5px);
}

.pricing-card.popular {
  border: 2px solid #409EFF;
  transform: scale(1.05);
}

.popular-badge {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  background: #409EFF;
  color: white;
  padding: 5px 15px;
  border-radius: 15px;
  font-size: 12px;
}

.card-header h3 {
  color: #333;
  margin-bottom: 20px;
  font-size: 1.5em;
}

.price {
  margin-bottom: 15px;
}

.currency {
  font-size: 1.2em;
  color: #666;
}

.amount {
  font-size: 3em;
  font-weight: bold;
  color: #409EFF;
}

.period {
  font-size: 1em;
  color: #666;
}

.description {
  color: #666;
  margin-bottom: 30px;
}

.features {
  text-align: left;
  margin-bottom: 30px;
}

.features h4 {
  color: #333;
  margin-bottom: 15px;
}

.features ul {
  list-style: none;
  padding: 0;
}

.features li {
  margin-bottom: 10px;
  color: #666;
}

.card-footer {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
}

.feature-comparison {
  margin-bottom: 60px;
}

.feature-comparison h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.faq-section {
  margin-bottom: 60px;
}

.faq-section h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.contact-sales {
  margin-bottom: 40px;
}

.sales-card {
  text-align: center;
  padding: 40px;
}

.sales-card h3 {
  color: #333;
  margin-bottom: 15px;
}

.sales-card p {
  color: #666;
  margin-bottom: 30px;
}

.sales-actions {
  display: flex;
  justify-content: center;
  gap: 20px;
}

.footer {
  background: #333;
  color: white;
  text-align: center;
  padding: 20px;
  margin-top: auto;
}

@media (max-width: 768px) {
  .pricing-card {
    margin-bottom: 20px;
    height: auto;
  }
  
  .pricing-card.popular {
    transform: none;
  }
  
  .sales-actions {
    flex-direction: column;
    align-items: center;
  }
}
</style> 
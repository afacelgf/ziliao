<template>
  <div class="tabbar-container">
    <div class="tabbar">
      <div 
        class="tab-item" 
        :class="{ active: currentRoute === '/' }"
        @click="navigateTo('/')"
      >
        <div class="tab-icon">🏠</div>
        <div class="tab-text">首页</div>
      </div>
      
      <div 
        class="tab-item" 
        :class="{ active: currentRoute === '/category' }"
        @click="navigateTo('/category')"
      >
        <div class="tab-icon">📚</div>
        <div class="tab-text">分类</div>
      </div>
      
      <div 
        class="tab-item" 
        :class="{ active: currentRoute === '/profile' }"
        @click="navigateTo('/profile')"
      >
        <div class="tab-icon">👤</div>
        <div class="tab-text">我的</div>
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter, useRoute } from 'vue-router'
import { computed } from 'vue'

export default {
  name: 'TabBar',
  setup() {
    const router = useRouter()
    const route = useRoute()
    
    const currentRoute = computed(() => route.path)
    
    const navigateTo = (path) => {
      if (route.path !== path) {
        router.push(path)
      }
    }
    
    return {
      currentRoute,
      navigateTo
    }
  }
}
</script>

<style scoped>
.tabbar-container {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.1);
}

.tabbar {
  display: flex;
  height: 60px;
  max-width: 500px;
  margin: 0 auto;
  padding: 0 16px;
}

.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 8px 4px;
  border-radius: 8px;
  margin: 4px 2px;
}

.tab-item:hover {
  background: rgba(0, 122, 255, 0.1);
}

.tab-item.active {
  background: rgba(0, 122, 255, 0.15);
}

.tab-icon {
  font-size: 20px;
  margin-bottom: 2px;
  transition: all 0.3s ease;
}

.tab-text {
  font-size: 11px;
  color: #7A7E83;
  transition: all 0.3s ease;
  font-weight: 500;
}

.tab-item.active .tab-text {
  color: #007aff;
  font-weight: 600;
}

.tab-item.active .tab-icon {
  transform: scale(1.1);
}

/* 响应式设计 */
@media (max-width: 480px) {
  .tabbar {
    padding: 0 8px;
  }
  
  .tab-item {
    margin: 4px 1px;
  }
  
  .tab-icon {
    font-size: 18px;
  }
  
  .tab-text {
    font-size: 10px;
  }
}

/* 为页面内容添加底部间距，避免被 tabbar 遮挡 */
:global(.page-with-tabbar) {
  padding-bottom: 80px;
}
</style>
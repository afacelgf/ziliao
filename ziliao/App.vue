<template>
	<div id="app">
		<div class="main-content" :class="{ 'page-with-tabbar': showTabBar }">
			<router-view />
		</div>
		<TabBar v-if="showTabBar" />
	</div>
</template>

<script>
import TabBar from './components/TabBar.vue'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

export default {
	name: 'App',
	components: {
		TabBar
	},
	setup() {
		const route = useRoute()
		
		// 定义哪些页面显示 TabBar
		const tabBarPages = ['/', '/category', '/profile']
		
		const showTabBar = computed(() => {
			return tabBarPages.includes(route.path)
		})
		
		return {
			showTabBar
		}
	},
	mounted() {
		console.log('App mounted')
	}
}
</script>

<style>
	/*每个页面公共css */
	@import url('./static/css/common.css');
	
	#app {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', 'Helvetica Neue', Helvetica, Arial, sans-serif;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}
	
	.main-content {
		min-height: 100vh;
	}
	
	.main-content.page-with-tabbar {
		padding-bottom: 80px;
	}
</style>
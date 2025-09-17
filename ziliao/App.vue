<template>
	<div id="app">
		<!-- <nav class="navigation">
			<router-link to="/" class="nav-link">首页</router-link>
			<router-link to="/subjects" class="nav-link">学科</router-link>
			<router-link to="/pdf" class="nav-link">PDF</router-link>
		</nav> -->
		<router-view />
	</div>
</template>

<script>
	export default {
		name: 'App',
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
</style>
<template>
	<div id="app">
		
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
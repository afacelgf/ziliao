<template>
	<div class="profile-container">
		<!-- 用户信息区域 -->
		<div class="profile-header">
			<div class="avatar">
				<span class="avatar-icon">👤</span>
			</div>
			<div class="user-info">
				<div class="username">未登录</div>
				<div class="user-desc">登录后可同步收藏与历史记录</div>
			</div>
			<div class="login-btn" @click="handleLogin">
				<span>登录</span>
			</div>
		</div>

		<!-- 统计信息 -->
		<div class="stats-section">
			<div class="stat-item">
				<div class="stat-number">0</div>
				<div class="stat-label">收藏资料</div>
			</div>
			<div class="stat-item">
				<div class="stat-number">0</div>
				<div class="stat-label">浏览历史</div>
			</div>
			<div class="stat-item">
				<div class="stat-number">0</div>
				<div class="stat-label">下载记录</div>
			</div>
		</div>

		<!-- 功能菜单 -->
		<div class="menu-section">
			<div class="menu-card">
				<div class="menu-title">我的资料</div>
				<div class="menu-item" @click="goToFavorites">
					<div class="menu-icon">❤️</div>
					<div class="menu-text">我的收藏</div>
					<div class="menu-arrow">›</div>
				</div>
				<div class="menu-item" @click="goToHistory">
					<div class="menu-icon">📚</div>
					<div class="menu-text">浏览历史</div>
					<div class="menu-arrow">›</div>
				</div>
				<div class="menu-item" @click="goToDownloads">
					<div class="menu-icon">📥</div>
					<div class="menu-text">下载记录</div>
					<div class="menu-arrow">›</div>
				</div>
			</div>

			<div class="menu-card">
				<div class="menu-title">帮助与反馈</div>
				<div class="menu-item" @click="goToFeedback">
					<div class="menu-icon">💬</div>
					<div class="menu-text">意见反馈</div>
					<div class="menu-arrow">›</div>
				</div>
				<div class="menu-item" @click="goToAbout">
					<div class="menu-icon">ℹ️</div>
					<div class="menu-text">关于我们</div>
					<div class="menu-arrow">›</div>
				</div>
			</div>

			<div class="menu-card">
				<div class="menu-title">设置</div>
				<div class="menu-item">
					<div class="menu-icon">🎨</div>
					<div class="menu-text">主题设置</div>
					<div class="menu-value">默认</div>
				</div>
				<div class="menu-item">
					<div class="menu-icon">🔔</div>
					<div class="menu-text">消息通知</div>
					<div class="menu-value">开启</div>
				</div>
			</div>
		</div>

		<!-- 版本信息 -->
		<div class="version-info">
			<div class="version-text">誉享学资料分享平台 v1.0.0</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'ProfilePage',
	data() {
		return {
			isLoggedIn: false,
			userInfo: {
				username: '未登录',
				avatar: ''
			}
		}
	},
	methods: {
		handleLogin() {
			// 登录逻辑
			uni.showToast({
				title: '登录功能开发中',
				icon: 'none'
			})
		},
		
		goToFavorites() {
			uni.showToast({
				title: '收藏功能开发中',
				icon: 'none'
			})
		},
		
		goToHistory() {
			uni.showToast({
				title: '历史记录功能开发中',
				icon: 'none'
			})
		},
		
		goToDownloads() {
			uni.showToast({
				title: '下载记录功能开发中',
				icon: 'none'
			})
		},
		
		goToFeedback() {
			uni.navigateTo({
				url: '/pages/request/request'
			})
		},
		
		goToAbout() {
			uni.showToast({
				title: '关于我们功能开发中',
				icon: 'none'
			})
		}
	}
}
</script>

<style scoped>
.profile-container {
	min-height: 100vh;
	background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
	padding: 0 16px 16px;
}

/* 用户信息区域 */
.profile-header {
	display: flex;
	align-items: center;
	background: rgba(255, 255, 255, 0.95);
	backdrop-filter: blur(20px);
	border-radius: 16px;
	padding: 20px;
	margin: 16px 0;
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.avatar {
	width: 64px;
	height: 64px;
	border-radius: 32px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 16px;
}

.avatar-icon {
	font-size: 28px;
	color: white;
}

.user-info {
	flex: 1;
}

.username {
	font-size: 18px;
	font-weight: 600;
	color: #333;
	margin-bottom: 4px;
}

.user-desc {
	font-size: 14px;
	color: #666;
}

.login-btn {
	padding: 8px 16px;
	background: #007aff;
	color: white;
	border-radius: 20px;
	font-size: 14px;
	cursor: pointer;
}

/* 统计信息 */
.stats-section {
	display: flex;
	background: rgba(255, 255, 255, 0.95);
	backdrop-filter: blur(20px);
	border-radius: 16px;
	padding: 20px;
	margin-bottom: 16px;
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.stat-item {
	flex: 1;
	text-align: center;
}

.stat-number {
	font-size: 24px;
	font-weight: 600;
	color: #007aff;
	margin-bottom: 4px;
}

.stat-label {
	font-size: 12px;
	color: #666;
}

/* 菜单区域 */
.menu-section {
	margin-bottom: 16px;
}

.menu-card {
	background: rgba(255, 255, 255, 0.95);
	backdrop-filter: blur(20px);
	border-radius: 16px;
	padding: 16px;
	margin-bottom: 16px;
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.menu-title {
	font-size: 16px;
	font-weight: 600;
	color: #333;
	margin-bottom: 12px;
}

.menu-item {
	display: flex;
	align-items: center;
	padding: 12px 0;
	border-bottom: 1px solid rgba(0, 0, 0, 0.05);
	cursor: pointer;
}

.menu-item:last-child {
	border-bottom: none;
}

.menu-icon {
	width: 32px;
	font-size: 18px;
	margin-right: 12px;
}

.menu-text {
	flex: 1;
	font-size: 15px;
	color: #333;
}

.menu-arrow {
	font-size: 18px;
	color: #ccc;
}

.menu-value {
	font-size: 14px;
	color: #666;
}

/* 版本信息 */
.version-info {
	text-align: center;
	padding: 20px;
}

.version-text {
	font-size: 12px;
	color: #999;
}

/* 响应式设计 */
@media (max-width: 480px) {
	.profile-container {
		padding: 0 12px 12px;
	}
	
	.profile-header {
		padding: 16px;
		margin: 12px 0;
	}
	
	.avatar {
		width: 56px;
		height: 56px;
		border-radius: 28px;
	}
	
	.avatar-icon {
		font-size: 24px;
	}
	
	.username {
		font-size: 16px;
	}
}
</style>
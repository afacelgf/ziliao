<template>
	<div class="search-container">
		<!-- 头部搜索区域 -->
		<div class="search-header">
			<div class="search-bar">
				<button class="back-btn" @click="goBack">
					<span class="back-icon">←</span>
				</button>
				<div class="search-input-wrapper">
					<input 
						type="text" 
						class="search-input" 
						placeholder="输入您想要的内容"
						v-model="searchKeyword"
						@keyup.enter="performSearch"
					>
					<button class="search-submit-btn" @click="performSearch" :disabled="!searchKeyword.trim()">
						搜索
					</button>
				</div>
			</div>
		</div>

		<!-- 搜索内容区域 -->
		<div class="search-content">
			<!-- 热搜推荐 -->
			<div class="hot-search-section" v-if="!hasSearched">
				<div class="section-header">
					<span class="section-title">大家都在搜</span>
				</div>
				<div class="hot-tags">
					<div 
						class="hot-tag" 
						v-for="(tag, index) in hotSearchTags" 
						:key="index"
						@click="searchHotTag(tag)"
					>
						{{ tag }}
					</div>
				</div>
			</div>

			<!-- 搜索历史 -->
			<div class="search-history-section" v-if="!hasSearched && searchHistory.length > 0">
				<div class="section-header">
					<span class="section-title">搜索历史</span>
					<button class="clear-history-btn" @click="clearSearchHistory">
						<span class="clear-icon">🗑️</span>
					</button>
				</div>
				<div class="history-list">
					<div 
						class="history-item" 
						v-for="(item, index) in searchHistory" 
						:key="index"
						@click="searchHistoryItem(item)"
					>
						<span class="history-icon">🕐</span>
						<span class="history-text">{{ item }}</span>
					</div>
				</div>
			</div>

			<!-- 搜索结果 -->
			<div class="search-results-section" v-if="hasSearched">
				<div class="results-header">
					<span class="results-count">找到 {{ searchResults.length }} 个相关资料</span>
				</div>
				
				<div class="results-list" v-if="searchResults.length > 0">
					<div 
						class="result-item" 
						v-for="result in searchResults" 
						:key="result.id"
						@click="openMaterial(result)"
					>
						<div class="result-info">
							<div class="result-title">{{ result.name }}</div>
							<!-- <div class="result-meta">
								<span class="result-grade">{{ result.grade_name }}</span>
								<span class="result-subject">{{ result.subject_name }}</span>
								<span class="result-type">{{ result.type_name }}</span>
							</div> -->
							<div class="result-description">{{ result.description }}</div>
						</div>
						<div class="result-action">
							<span class="view-icon">👁️</span>
						</div>
					</div>
				</div>

				<div class="no-results" v-else>
					<div class="no-results-icon">🔍</div>
					<div class="no-results-text">没有找到相关资料</div>
					<div class="no-results-tip">试试其他关键词吧</div>
					<button class="feedback-btn" @click="goToRequest">我要反馈</button>
				</div>
			</div>
		</div>

		<!-- 下载弹窗 -->
		<div class="download-overlay" v-if="showModal" @click="closeModal">
			<div class="download-dialog" @click.stop>
				<div class="dialog-header">
					<div class="dialog-icon">📥</div>
					<div class="dialog-title">资料详情</div>
					<div class="dialog-close" @click="closeModal">×</div>
				</div>
				
				<div class="dialog-content">
					<div class="file-preview-large">
						<div class="file-icon-large">📄</div>
						<div class="file-info-large">
							<div class="file-name-large">{{ selectedFile.name }}</div>
							<div class="file-desc-large">{{ selectedFile.description }}</div>
						</div>
					</div>
				</div>
				
				<div class="dialog-actions">
					<div class="action-button secondary" @click="downloadFile">
						<span class="button-icon">📃</span>
						<span class="button-text">查看文件</span>
					</div>
					<div class="action-button primary" @click="copyUrl">
						<span class="button-icon">📋</span>
						<span class="button-text">复制下载链接</span>
					</div>
				</div>
			</div>
		</div>

		<!-- 吐司提示 -->
		<div class="toast" v-if="toastVisible" :class="toastType">
			<div class="toast-icon">{{ toastIcon }}</div>
			<div class="toast-message">{{ toastMessage }}</div>
		</div>
	</div>
</template>

<script>
import { materialService } from '@/utils/dataService.js'

export default {
	data() {
		return {
			searchKeyword: '',
			hasSearched: false,
			searchResults: [],
			searchHistory: [],
			loading: false,
			showModal: false,
			selectedFile: {},
			toastVisible: false,
			toastMessage: '',
			toastType: 'success',
			toastIcon: '✓',
			// 热搜标签数据（固定本地数据）
			hotSearchTags: [
				'数学',
				'五年级',
				'三年级',
				'物理'
			]
		}
	},
	
	mounted() {
		this.loadSearchHistory()
	},
	
	methods: {
		goBack() {
			this.$router.go(-1)
		},
		
		// 执行搜索
		async performSearch() {
			const keyword = this.searchKeyword.trim()
			if (!keyword) return
			
			this.loading = true
			this.hasSearched = true
			
			try {
				// 调用搜索接口
				const results = await materialService.searchMaterials(keyword)
				this.searchResults = results
				// 保存搜索历史
				this.saveSearchHistory(keyword)
				console.log('搜索结果:', results)
			} catch (error) {
				console.error('搜索失败:', error)
				this.searchResults = []
			} finally {
				this.loading = false
			}
		},
		
		// 点击热搜标签
		searchHotTag(tag) {
			this.searchKeyword = tag
			this.performSearch()
		},
		
		// 点击搜索历史
		searchHistoryItem(item) {
			this.searchKeyword = item
			this.performSearch()
		},
		
		// 保存搜索历史
		saveSearchHistory(keyword) {
			// 移除重复项
			this.searchHistory = this.searchHistory.filter(item => item !== keyword)
			// 添加到开头
			this.searchHistory.unshift(keyword)
			// 限制历史记录数量
			if (this.searchHistory.length > 10) {
				this.searchHistory = this.searchHistory.slice(0, 10)
			}
			// 保存到本地存储
			try {
				localStorage.setItem('searchHistory', JSON.stringify(this.searchHistory))
			} catch (error) {
				console.error('保存搜索历史失败:', error)
			}
		},
		
		// 加载搜索历史
		loadSearchHistory() {
			try {
				const history = localStorage.getItem('searchHistory')
				if (history) {
					this.searchHistory = JSON.parse(history)
				}
			} catch (error) {
				console.error('加载搜索历史失败:', error)
				this.searchHistory = []
			}
		},
		
		// 清空搜索历史
		clearSearchHistory() {
			this.searchHistory = []
			try {
				localStorage.removeItem('searchHistory')
			} catch (error) {
				console.error('清空搜索历史失败:', error)
			}
		},
		
		// 打开资料详情
		openMaterial(material) {
			// 显示弹框而不是直接跳转
			this.showDownloadDialog(material)
		},
		
		// 显示下载弹框
		showDownloadDialog(material) {
			this.selectedFile = {
				...material,
				name: material.name,
				description: material.description,
				download_url: material.download_url 
			}
			this.showModal = true
		},
		
		// 关闭弹框
		closeModal() {
			this.showModal = false
			this.selectedFile = {}
		},
		
		// 复制下载链接
		copyUrl() {
			// 复制下载链接到剪贴板
			if (navigator.clipboard && window.isSecureContext) {
				// 使用现代 Clipboard API
				navigator.clipboard.writeText(this.selectedFile.download_url).then(() => {
					this.showToast('链接已复制', 'success')
					this.closeModal()
				}).catch(() => {
					this.showToast('复制失败', 'error')
				})
			} else {
				// 降级方案：使用传统方法
				try {
					const textArea = document.createElement('textarea')
					textArea.value = this.selectedFile.download_url
					document.body.appendChild(textArea)
					textArea.select()
					document.execCommand('copy')
					document.body.removeChild(textArea)
					this.showToast('链接已复制', 'success')
					this.closeModal()
				} catch (err) {
					this.showToast('复制失败', 'error')
				}
			}
		},
		
		// 下载文件
		downloadFile() {
			// 根据URL下载文件到本地
			try {
				const link = document.createElement('a')
				console.log(this.selectedFile.download_url)
				link.href = this.selectedFile.download_url
				link.download = this.selectedFile.name || 'download'
				link.target = '_blank'
				document.body.appendChild(link)
				link.click()
				document.body.removeChild(link)
				// alert('开始下载')
				this.closeModal()
			} catch (error) {
				console.error('下载失败:', error)
				this.showToast('下载失败，请尝试复制链接手动下载', 'error')
			}
		},
		
		// 显示吐司提示
		showToast(message, type = 'success') {
			this.toastMessage = message
			this.toastType = type
			this.toastIcon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'
			this.toastVisible = true
			
			// 3秒后自动隐藏
			setTimeout(() => {
				this.toastVisible = false
			}, 3000)
		},
		
		// 跳转到申请资料页面
		goToRequest() {
			this.$router.push('/request');
		}
	}
}
</script>

<style scoped>
.search-container {
	min-height: 100vh;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	display: flex;
	flex-direction: column;
}

/* 头部搜索区域 */
.search-header {
	padding: 20px;
	background: rgba(255, 255, 255, 0.1);
	backdrop-filter: blur(10px);
}

.search-bar {
	display: flex;
	align-items: center;
	gap: 12px;
}

.back-btn {
	width: 40px;
	height: 40px;
	border-radius: 20px;
	background: rgba(255, 255, 255, 0.2);
	border: none;
	color: white;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all 0.3s ease;
}

.back-btn:hover {
	background: rgba(255, 255, 255, 0.3);
	transform: scale(1.1);
}

.back-icon {
	font-size: 20px;
	font-weight: bold;
}

.search-input-wrapper {
	flex: 1;
	display: flex;
	background: rgba(255, 255, 255, 0.9);
	border-radius: 25px;
	overflow: hidden;
}

.search-input {
	flex: 1;
	padding: 12px 20px;
	border: none;
	outline: none;
	font-size: 16px;
	background: transparent;
}

.search-input::placeholder {
	color: #999;
}

.search-submit-btn {
	padding: 12px 20px;
	background: #667eea;
	color: white;
	border: none;
	cursor: pointer;
	font-size: 16px;
	font-weight: 500;
	transition: all 0.3s ease;
}

.search-submit-btn:hover:not(:disabled) {
	background: #5a6fd8;
}

.search-submit-btn:disabled {
	background: #ccc;
	cursor: not-allowed;
}

/* 搜索内容区域 */
.search-content {
	flex: 1;
	padding: 20px;
	overflow-y: auto;
}

/* 热搜推荐 */
.hot-search-section {
	margin-bottom: 30px;
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 15px;
}

.section-title {
	color: white;
	font-size: 18px;
	font-weight: bold;
}

.hot-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
}

.hot-tag {
	background: rgba(255, 255, 255, 0.2);
	color: white;
	padding: 8px 16px;
	border-radius: 20px;
	cursor: pointer;
	transition: all 0.3s ease;
	font-size: 14px;
}

.hot-tag:hover {
	background: rgba(255, 255, 255, 0.3);
	transform: translateY(-2px);
}

/* 搜索历史 */
.search-history-section {
	margin-bottom: 30px;
}

.clear-history-btn {
	background: none;
	border: none;
	color: white;
	cursor: pointer;
	padding: 4px;
	border-radius: 4px;
	transition: all 0.3s ease;
}

.clear-history-btn:hover {
	background: rgba(255, 255, 255, 0.2);
}

.clear-icon {
	font-size: 16px;
}

.history-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.history-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px 16px;
	background: rgba(255, 255, 255, 0.1);
	border-radius: 12px;
	cursor: pointer;
	transition: all 0.3s ease;
}

.history-item:hover {
	background: rgba(255, 255, 255, 0.2);
}

.history-icon {
	font-size: 16px;
	opacity: 0.7;
}

.history-text {
	color: white;
	font-size: 14px;
}

/* 搜索结果 */
.search-results-section {
	color: white;
}

.results-header {
	margin-bottom: 20px;
}

.results-count {
	font-size: 16px;
	opacity: 0.9;
}

.results-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.result-item {
	background: rgba(255, 255, 255, 0.1);
	border-radius: 12px;
	padding: 16px;
	cursor: pointer;
	transition: all 0.3s ease;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.result-item:hover {
	background: rgba(255, 255, 255, 0.2);
	transform: translateY(-2px);
}

.result-info {
	flex: 1;
}

.result-title {
	font-size: 16px;
	font-weight: bold;
	margin-bottom: 8px;
}

.result-meta {
	display: flex;
	gap: 12px;
	margin-bottom: 8px;
	font-size: 12px;
	opacity: 0.8;
}

.result-grade,
.result-subject,
.result-type {
	background: rgba(255, 255, 255, 0.2);
	padding: 2px 8px;
	border-radius: 10px;
}

.result-description {
	font-size: 14px;
	opacity: 0.9;
	line-height: 1.4;
}

.result-action {
	margin-left: 16px;
}

.view-icon {
	font-size: 20px;
	opacity: 0.7;
}

/* 无结果状态 */
.no-results {
	text-align: center;
	padding: 60px 20px;
	color: white;
}

.no-results-icon {
	font-size: 48px;
	margin-bottom: 16px;
	opacity: 0.5;
}

.no-results-text {
	font-size: 18px;
	font-weight: bold;
	margin-bottom: 8px;
}

.no-results-tip {
	font-size: 14px;
	opacity: 0.7;
}

/* 移动端适配 */
@media (max-width: 768px) {
	.search-header {
		padding: 15px;
	}
	
	.search-input {
		font-size: 14px;
		padding: 10px 16px;
	}
	
	.search-submit-btn {
		padding: 10px 16px;
		font-size: 14px;
	}
	
	.search-content {
		padding: 15px;
	}
	
	.section-title {
		font-size: 16px;
	}
	
	.hot-tag {
		font-size: 12px;
		padding: 6px 12px;
	}
	
	.result-item {
		padding: 12px;
	}
	
	.result-title {
		font-size: 14px;
	}
	
	.result-meta {
		font-size: 11px;
	}
	
	.result-description {
		font-size: 13px;
	}
}

/* 下载弹窗样式 */
.download-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.6);
	backdrop-filter: blur(8px);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 1000;
	animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}

.download-dialog {
	background: rgba(255, 255, 255, 0.95);
	backdrop-filter: blur(20px);
	border-radius: 24px;
	width: 90%;
	max-width: 480px;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
	animation: slideUp 0.3s ease;
	overflow: hidden;
}

@keyframes slideUp {
	from { 
		opacity: 0;
		transform: translateY(30px);
	}
	to { 
		opacity: 1;
		transform: translateY(0);
	}
}

.dialog-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 24px;
	border-bottom: 1px solid rgba(189, 195, 199, 0.2);
}

.dialog-icon {
	font-size: 20px;
	margin-right: 12px;
}

.dialog-title {
	font-size: 16px;
	font-weight: 400;
	color: #2c3e50;
	flex: 1;
}

.dialog-close {
	width: 36px;
	height: 36px;
	border-radius: 18px;
	background: rgba(127, 140, 141, 0.1);
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	font-size: 20px;
	color: #7f8c8d;
	transition: all 0.3s ease;
}

.dialog-close:hover {
	background: rgba(127, 140, 141, 0.2);
}

.dialog-content {
	padding: 24px;
}

.file-preview-large {
	display: flex;
	align-items: center;
	margin-bottom: 24px;
	padding: 10px;
	background: rgba(102, 126, 234, 0.05);
	border-radius: 16px;
}

.file-icon-large {
	width: 30px;
	height: 30px;
	border-radius: 16px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 25px;
	color: white;
	margin-right: 16px;
}

.file-info-large {
	flex: 1;
}

.file-name-large {
	font-size: 16px;
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 4px;
}

.file-desc-large {
	font-size: 14px;
	color: #7f8c8d;
}

.dialog-actions {
	padding: 24px;
	border-top: 1px solid rgba(189, 195, 199, 0.2);
	display: flex;
	gap: 12px;
}

.action-button {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 16px 12px;
	border-radius: 12px;
	cursor: pointer;
	transition: all 0.3s ease;
	font-weight: 400;
	flex: 1;
}

.action-button.secondary {
	background: rgba(127, 140, 141, 0.1);
	color: #7f8c8d;
	border: 1px solid rgba(127, 140, 141, 0.2);
}

.action-button.secondary:hover {
	background: rgba(127, 140, 141, 0.2);
	transform: translateY(-2px);
	box-shadow: 0 4px 15px rgba(127, 140, 141, 0.3);
}

.action-button.primary {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	box-shadow: 0 4px 15px rgba(57, 89, 231, 0.4);
}

.action-button.primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 25px rgba(46, 80, 233, 0.6);
}

.button-icon {
	margin-right: 8px;
	font-size: 16px;
}

.button-text {
	font-size: 13px;
}

/* 反馈按钮样式 */
.feedback-btn {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 20px;
	font-size: 14px;
	cursor: pointer;
	margin-top: 10px;
	transition: all 0.3s ease;
	box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.feedback-btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.feedback-btn:active {
	transform: translateY(0);
}

/* 吐司提示样式 */
.toast {
	position: fixed;
	top: 50px;
	left: 50%;
	transform: translateX(-50%);
	background: rgba(0, 0, 0, 0.8);
	color: white;
	padding: 12px 20px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	gap: 8px;
	z-index: 10000;
	animation: toastSlideIn 0.3s ease-out;
	font-size: 14px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.toast.success {
	background: rgba(76, 175, 80, 0.9);
}

.toast.error {
	background: rgba(244, 67, 54, 0.9);
}

.toast.info {
	background: rgba(33, 150, 243, 0.9);
}

.toast-icon {
	font-size: 16px;
	font-weight: bold;
}

.toast-message {
	font-size: 14px;
}

@keyframes toastSlideIn {
	from {
		opacity: 0;
		transform: translateX(-50%) translateY(-20px);
	}
	to {
		opacity: 1;
		transform: translateX(-50%) translateY(0);
	}
}
</style>
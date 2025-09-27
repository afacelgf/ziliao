<template>
	<div class="page-container">
		<!-- 头部导航栏 -->
		<div class="header-nav">
			<div class="nav-left">
				<div class="back-button" @click="goBack">
					<span class="back-icon">←</span>
				</div>
			</div>
			<div class="nav-center">
				<div class="page-title">{{ selectedGradeName + '-' + subjectName }}</div>
			</div>
			<div class="nav-right">
				<div class="menu-button" @click="showMenu = !showMenu">
					<span class="menu-icon">⋮</span>
				</div>
			</div>
		</div>

		<!-- 分类筛选栏 -->
		<div class="filter-section"  v-if="categories.length > 0">
			<div class="category-scroll">
				<div class="category-list">
					<div 
						class="category-item" 
						:class="{ active: selectedCategory === category.id }"
						v-for="category in categories" 
						:key="category.id"
						@click="selectCategory(category.id)"
					>
						<span class="category-label">{{ category.name }}</span>
					</div>
				</div>
			</div>
		</div>

		<!-- 主要内容区域 -->
		<div class="main-content">
			<!-- 内容展示区域 -->
			<div class="content-section">
				<!-- 空白状态 -->
				<div v-if="currentFiles.length === 0 && !loading" class="empty-container">
					<div class="empty-illustration">
						<span class="empty-icon">📚</span>
						<div class="empty-decoration"></div>
					</div>
					<div class="empty-content">
						<div class="empty-description">暂时相关资料<br/>请尝试选择其他学科/分类</div>
					</div>
				</div>
				
				<!-- 加载状态 -->
				<div v-if="loading" class="loading-container">
					<div class="loading-spinner">
						<div class="spinner-ring"></div>
						<div class="spinner-ring"></div>
						<div class="spinner-ring"></div>
					</div>
					<div class="loading-text">正在加载资料...</div>
				</div>
				
				<!-- 文件列表 -->
				<div v-if="currentFiles.length > 0" class="files-list">
					<div 
						class="file-item" 
						v-for="file in currentFiles" 
						:key="file.id"
						@click="showDownloadDialog(file)"
					>
						<div class="file-content">
							<div class="file-title">{{ file.name }}</div>
							<div class="file-description">{{ file.description }}</div>
						</div>
						<div class="file-action">
							<div class="download-button">下载</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- 下载弹窗 -->
		<div class="download-overlay" v-if="showModal" @click="closeModal">
			<div class="download-dialog" @click.stop>
				<div class="dialog-header">
					<div class="dialog-icon">📥</div>
					<div class="dialog-title">下载资料</div>
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
					
					<div class="download-section">
						<div class="download-label">下载链接</div>
						<div class="url-display">
							<span class="url-text" :selectable="true">{{ selectedFile.download_url }}</span>
						</div>
					</div>
				</div>
				
				<div class="dialog-actions">
					<div class="action-button primary" @click="copyUrl">
						<span class="button-icon">📋</span>
						<span class="button-text">复制链接</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import { materialService } from '@/utils/dataService.js'

	export default {
		data() {
			return {
				selectedGradeId: '',
				selectedGradeName: '',
				subjectName: '',
				subjectId: '',
				selectedCategory: null,
				selectedTypeId: null,
				showModal: false,
				selectedFile: {},
				loading: false,
				categoriesLoading: false,
				categories: [],
				currentFiles: [],
				showMenu: false
			}
		},

		mounted() {
			// 从路由参数中获取地区、年级和科目信息
			const query = this.$route.query;
			
			if (query.grade_id) {
				this.selectedGradeId = query.grade_id;
			}
			if (query.grade_name) {
				this.selectedGradeName = query.grade_name;
			}
			if (query.subject_name) {
				this.subjectName = query.subject_name;
			}
			if (query.subject_id) {
				this.subjectId = query.subject_id;
			}
			// 加载资料分类和资料列表
			this.loadMaterialTypes();
		},
		methods: {
			async loadMaterialTypes() {
				this.categoriesLoading = true
				try {
					const data = await materialService.getMaterialTypeList(
						this.selectedGradeId, 
						this.subjectId
					)
					
					this.categories = data.map(item => ({
						id: item.id,
						name: item.name,
						icon: item.icon || '📄'
					}))
					// 默认选择第一个分类
					if (this.categories.length > 0) {
						this.selectedCategory = this.categories[0].id
						this.selectedTypeId = this.categories[0].id
						await this.loadMaterialList()
					}
				} catch (error) {
					console.error('加载资料分类失败:', error)
				} finally {
					this.categoriesLoading = false
				}
			},
			
			async loadMaterialList() {
				this.loading = true
				try {
					const data = await materialService.getMaterialList(
						this.selectedGradeId,
						this.subjectId,
						this.selectedTypeId
					)
					this.currentFiles = data
				} catch (error) {
					console.error('加载资料列表失败:', error)
				} finally {
					this.loading = false
				}
			},
			
			async selectCategory(categoryId) {
				this.selectedCategory = categoryId
				this.selectedTypeId = categoryId
				await this.loadMaterialList()
			},
			
			goBack() {
				this.$router.go(-1);
			},
			showDownloadDialog(file) {
				this.selectedFile = file;
				this.showModal = true;
			},
			closeModal() {
				this.showModal = false;
				this.selectedFile = {};
			},
			copyUrl() {
				// 复制下载链接到剪贴板
				if (navigator.clipboard && window.isSecureContext) {
					// 使用现代 Clipboard API
					navigator.clipboard.writeText(this.selectedFile.download_url).then(() => {
						alert('链接已复制');
						this.closeModal();
					}).catch(() => {
						alert('复制失败');
					});
				} else {
					// 降级方案：使用传统方法
					try {
						const textArea = document.createElement('textarea');
						textArea.value = this.selectedFile.download_url;
						document.body.appendChild(textArea);
						textArea.select();
						document.execCommand('copy');
						document.body.removeChild(textArea);
						alert('链接已复制');
						this.closeModal();
					} catch (err) {
						alert('复制失败');
					}
				}
			}
		}
	}
</script>

<style scoped>
/* 页面容器 */
.page-container {
	min-height: 100vh;
	background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
	display: flex;
	flex-direction: column;
}

/* 头部导航栏 */
.header-nav {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px 24px;
	background: rgba(255, 255, 255, 0.95);
	backdrop-filter: blur(20px);
	border-bottom: 1px solid rgba(255, 255, 255, 0.2);
	box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
}

.nav-left, .nav-right {
	width: 60px;
	display: flex;
	justify-content: center;
}

.nav-center {
	flex: 1;
	text-align: center;
}

.back-button, .menu-button {
	width: 44px;
	height: 44px;
	border-radius: 22px;
	background: rgba(255, 255, 255, 0.9);
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all 0.3s ease;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.back-button:hover, .menu-button:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.back-icon, .menu-icon {
	font-size: 20px;
	color: #333;
	font-weight: 600;
}

.page-title {
	font-size: 20px;
	font-weight: 700;
	color: #2c3e50;
	margin-bottom: 2px;
}

.page-subtitle {
	font-size: 14px;
	color: #7f8c8d;
	font-weight: 500;
}

/* 主要内容区域 */
.main-content {
	flex: 1;
	padding: 0 24px 24px 24px;
	display: flex;
	flex-direction: column;
}

/* 分类筛选栏 */
.filter-section {
	background: rgba(255, 255, 255, 0.95);
	backdrop-filter: blur(20px);
	border-bottom: 1px solid rgba(255, 255, 255, 0.2);
	padding: 16px 10px;
}

.category-scroll {
	width: 100%;
	overflow-x: auto;
	overflow-y: hidden;
}

.category-scroll::-webkit-scrollbar {
	display: none;
}

.category-list {
	display: flex;
	gap: 8px;
	padding: 4px 0;
	width: max-content;
	min-width: 100%;
}

.category-item {
	padding: 4px 16px;
	background: rgba(255, 255, 255, 0.8);
	border-radius: 20px;
	cursor: pointer;
	transition: all 0.3s ease;
	flex-shrink: 0;
	border: 2px solid transparent;
	white-space: nowrap;
}

.category-item:hover {
	background: rgba(255, 255, 255, 0.9);
}

.category-item.active {
	background: #007AFF;
	color: white;
}

.category-label {
	font-size: 14px;
	font-weight: 500;
	text-align: center;
}

/* 内容展示区域 */
.content-section {
	flex: 1;
	background: rgba(255, 255, 255, 0.9);
	border-radius: 20px;
	padding: 24px;
	backdrop-filter: blur(20px);
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
	min-height: 400px;
	margin-top: 24px;
}

/* 文件列表 */
.files-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.file-item {
	background: rgba(255, 255, 255, 0.9);
	border-radius: 12px;
	padding: 16px 20px;
	cursor: pointer;
	transition: all 0.3s ease;
	border: 1px solid rgba(255, 255, 255, 0.2);
	backdrop-filter: blur(10px);
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.file-item:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
	border-color: rgba(0, 122, 255, 0.3);
}

.file-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.file-title {
	font-size: 16px;
	font-weight: 600;
	color: #2c3e50;
	line-height: 1.4;
}

.file-description {
	font-size: 14px;
	color: #7f8c8d;
	line-height: 1.5;
}

.file-action {
	flex-shrink: 0;
	margin-left: 16px;
}

.download-button {
	background: #007AFF;
	color: white;
	padding: 8px 16px;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 500;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.3s ease;
	cursor: pointer;
	min-width: 60px;
}

.download-button:hover {
	background: #0056CC;
	transform: translateY(-1px);
}

/* 空白状态 */
.empty-container {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	min-height: 300px;
	text-align: center;
}

.empty-illustration {
	position: relative;
	margin-bottom: 32px;
}

.empty-icon {
	font-size: 80px;
	opacity: 0.6;
	display: block;
}

.empty-decoration {
	position: absolute;
	top: -10px;
	right: -10px;
	width: 20px;
	height: 20px;
	border-radius: 50%;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	animation: float 3s ease-in-out infinite;
}

@keyframes float {
	0%, 100% { transform: translateY(0px); }
	50% { transform: translateY(-10px); }
}

.empty-content {
	max-width: 280px;
}

.empty-title {
	font-size: 22px;
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 12px;
}

.empty-description {
	font-size: 15px;
	color: #7f8c8d;
	line-height: 1.6;
}

/* 加载状态 */
.loading-container {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	min-height: 300px;
}

.loading-spinner {
	position: relative;
	width: 60px;
	height: 60px;
	margin-bottom: 24px;
}

.spinner-ring {
	position: absolute;
	width: 100%;
	height: 100%;
	border: 3px solid transparent;
	border-top: 3px solid #667eea;
	border-radius: 50%;
	animation: spin 1.2s linear infinite;
}

.spinner-ring:nth-child(2) {
	width: 80%;
	height: 80%;
	top: 10%;
	left: 10%;
	border-top-color: #764ba2;
	animation-delay: -0.4s;
}

.spinner-ring:nth-child(3) {
	width: 60%;
	height: 60%;
	top: 20%;
	left: 20%;
	border-top-color: #667eea;
	animation-delay: -0.8s;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

.loading-text {
	font-size: 16px;
	color: #7f8c8d;
	font-weight: 500;
}

/* 下载弹窗 */
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
	font-size: 24px;
	margin-right: 12px;
}

.dialog-title {
	font-size: 20px;
	font-weight: 600;
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
	padding: 20px;
	background: rgba(102, 126, 234, 0.05);
	border-radius: 16px;
}

.file-icon-large {
	width: 64px;
	height: 64px;
	border-radius: 16px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 32px;
	color: white;
	margin-right: 16px;
}

.file-info-large {
	flex: 1;
}

.file-name-large {
	font-size: 18px;
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 4px;
}

.file-desc-large {
	font-size: 14px;
	color: #7f8c8d;
}

.download-section {
	background: rgba(247, 248, 249, 0.8);
	border-radius: 12px;
	padding: 20px;
}

.download-label {
	font-size: 14px;
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 12px;
}

.url-display {
	background: white;
	border-radius: 8px;
	padding: 16px;
	border: 1px solid rgba(189, 195, 199, 0.3);
}

.url-text {
	font-size: 13px;
	color: #34495e;
	word-break: break-all;
	line-height: 1.5;
	font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
}

.dialog-actions {
	padding: 24px;
	border-top: 1px solid rgba(189, 195, 199, 0.2);
}

.action-button {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 16px 24px;
	border-radius: 12px;
	cursor: pointer;
	transition: all 0.3s ease;
	font-weight: 600;
}

.action-button.primary {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.action-button.primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.button-icon {
	margin-right: 8px;
	font-size: 16px;
}

.button-text {
	font-size: 15px;
}

/* 响应式设计 */
@media (max-width: 768px) {
	.header-nav {
		padding: 16px 20px;
	}
	
	.main-content {
		padding: 20px;
		gap: 20px;
	}
	
	.filter-section, .content-section {
		padding: 10px;
	}
	
	.files-grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}
	
	.file-card {
		padding: 16px;
	}
	
	.empty-icon {
		font-size: 64px;
	}
	
	.empty-title {
		font-size: 20px;
	}
	
	.download-dialog {
		margin: 20px;
		max-width: none;
	}
	
	.dialog-header, .dialog-content, .dialog-actions {
		padding: 20px;
	}
}

@media (max-width: 480px) {
	.category-list {
		gap: 8px;
	}
	
	.category-item {
		min-width: 70px;
		padding: 10px 12px;
	}
	
	.category-icon-wrapper {
		width: 36px;
		height: 36px;
		border-radius: 18px;
	}
	
	.category-icon {
		font-size: 18px;
	}
	
	.category-label {
		font-size: 11px;
		max-width: 60px;
	}
}
</style>
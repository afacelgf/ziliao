<template>
	<view class="container">
		<!-- 头部导航 -->
		<view class="header">
			<view class="back-btn" @click="goBack">
				<text class="back-icon">←</text>
			</view>
			<view class="header-info">
				<view class="header-title">{{ subjectName }}</view>
				<view class="header-subtitle">{{ selectedGrade }}年级</view>
			</view>
			<view class="menu-btn" @click="showMenu = !showMenu">
				<text class="menu-icon">⋮</text>
			</view>
		</view>

		<!-- 分类选择区域 -->
		<view class="category-section">
			<view class="category-tabs">
				<view 
					class="category-tab" 
					:class="{ active: selectedCategory === category.id }"
					v-for="category in categories" 
					:key="category.id"
					@click="selectCategory(category.id)"
				>
					<text class="category-icon">{{ category.icon }}</text>
					<text class="category-name">{{ category.name }}</text>
				</view>
			</view>
		</view>

		<!-- PDF文件列表区域 -->
		<view class="file-list-section">
			<view class="file-list">
				<view 
					class="file-item" 
					v-for="file in currentFiles" 
					:key="file.id"
					@click="showDownloadDialog(file)"
				>
					<view class="file-icon">📄</view>
					<view class="file-info">
						<view class="file-name">{{ file.name }}</view>
						<view class="file-description">{{ file.description }}</view>
						<view class="file-size">{{ file.size }}</view>
					</view>
					<view class="file-arrow">›</view>
				</view>
			</view>
		</view>

		<!-- 下载URL弹窗 -->
		<view class="download-modal" v-if="showModal" @click="closeModal">
			<view class="modal-content" @click.stop>
				<view class="modal-header">
					<view class="modal-title">下载链接</view>
					<view class="modal-close" @click="closeModal">×</view>
				</view>
				<view class="modal-body">
					<view class="file-info-modal">
						<view class="file-name-modal">{{ selectedFile.name }}</view>
						<view class="file-description-modal">{{ selectedFile.description }}</view>
					</view>
					<view class="url-container">
						<view class="url-label">下载地址：</view>
						<view class="url-text" :selectable="true">{{ selectedFile.downloadUrl }}</view>
					</view>
				</view>
				<view class="modal-footer">
					<view class="copy-btn" @click="copyUrl">
						<text class="copy-icon">📋</text>
						<text class="copy-text">复制链接</text>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				selectedGrade: '',
				subjectName: '',
				subjectId: '',
				selectedCategory: 'textbook',
				showModal: false,
				selectedFile: {},
				categories: [
				{ id: 'textbook', name: '教材', icon: '📚' },
				{ id: 'exercise', name: '练习册', icon: '📝' },
				{ id: 'test', name: '试卷', icon: '📄' },
				{ id: 'reference', name: '参考资料', icon: '📖' }
			],
			fileData: {
				textbook: [
					{
						id: 1,
						name: '小学数学《单元易错专练》1年级上册.pdf',
						description: '1年级上册数学课本',
						size: '12.5MB',
						downloadUrl: 'https://example.com/math-grade1-textbook.pdf'
					},
					{
						id: 2,
						name: '小学数学《单元易错专练》2年级上册.pdf',
						description: '2年级上册数学课本',
						size: '15.2MB',
						downloadUrl: 'https://example.com/math-grade2-textbook.pdf'
					},
					{
						id: 3,
						name: '小学数学《单元易错专练》3年级上册.pdf',
						description: '3年级上册数学课本',
						size: '18.7MB',
						downloadUrl: 'https://example.com/math-grade3-textbook.pdf'
					}
				],
				exercise: [
					{
						id: 4,
						name: '小学数学《单元易错专练》4年级上册.pdf',
						description: '4年级数学练习册',
						size: '14.3MB',
						downloadUrl: 'https://example.com/math-grade4-exercise.pdf'
					},
					{
						id: 5,
						name: '小学数学《单元易错专练》5年级上册.pdf',
						description: '5年级数学练习册',
						size: '16.8MB',
						downloadUrl: 'https://example.com/math-grade5-exercise.pdf'
					},
					{
						id: 6,
						name: '小学数学《单元易错专练》6年级上册.pdf',
						description: '6年级数学练习册',
						size: '19.2MB',
						downloadUrl: 'https://example.com/math-grade6-exercise.pdf'
					}
				],
				test: [
					{
						id: 7,
						name: '小学数学期末测试卷1年级.pdf',
						description: '1年级期末数学测试',
						size: '8.5MB',
						downloadUrl: 'https://example.com/math-test-grade1.pdf'
					},
					{
						id: 8,
						name: '小学数学期末测试卷2年级.pdf',
						description: '2年级期末数学测试',
						size: '9.2MB',
						downloadUrl: 'https://example.com/math-test-grade2.pdf'
					},
					{
						id: 9,
						name: '小学数学期末测试卷3年级.pdf',
						description: '3年级期末数学测试',
						size: '10.1MB',
						downloadUrl: 'https://example.com/math-test-grade3.pdf'
					}
				],
				reference: [
					{
						id: 10,
						name: '小学数学知识点总结.pdf',
						description: '数学知识点汇总',
						size: '22.3MB',
						downloadUrl: 'https://example.com/math-summary.pdf'
					},
					{
						id: 11,
						name: '小学数学解题技巧.pdf',
						description: '数学解题方法和技巧',
						size: '18.9MB',
						downloadUrl: 'https://example.com/math-skills.pdf'
					},
					{
						id: 12,
						name: '小学数学公式大全.pdf',
						description: '数学公式汇总',
						size: '13.7MB',
						downloadUrl: 'https://example.com/math-formulas.pdf'
					}
				]
			}
			}
		},
		computed: {
			currentFiles() {
				return this.fileData[this.selectedCategory] || [];
			}
		},
		onLoad(options) {
			// 获取传递的参数
			if (options.grade) {
				this.selectedGrade = options.grade;
			}
			if (options.subject) {
				this.subjectName = options.subject;
			}
			if (options.subjectId) {
				this.subjectId = options.subjectId;
			}
		},
		methods: {
			goBack() {
				this.$router.go(-1);
			},
			selectCategory(categoryId) {
				this.selectedCategory = categoryId;
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
				uni.setClipboardData({
					data: this.selectedFile.downloadUrl,
					success: () => {
						uni.showToast({
							title: '链接已复制',
							icon: 'success'
						});
						this.closeModal();
					},
					fail: () => {
						uni.showToast({
							title: '复制失败',
							icon: 'none'
						});
					}
				});
			}
		}
	}
</script>

<style scoped>
	.container {
		min-height: 100vh;
		background: #f5f5f5;
		display: flex;
		flex-direction: column;
	}

	/* 头部导航 */
	.header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 15px 20px;
		background: white;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		position: relative;
		z-index: 100;
	}

	.back-btn, .menu-btn {
		width: 40px;
		height: 40px;
		border-radius: 20px;
		background: #f0f0f0;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.back-btn:hover, .menu-btn:hover {
		background: #e0e0e0;
		transform: scale(1.1);
	}

	.back-icon, .menu-icon {
		font-size: 18px;
		font-weight: bold;
		color: #333;
	}

	.header-info {
		text-align: center;
	}

	.header-title {
		font-size: 18px;
		font-weight: bold;
		color: #333;
		margin-bottom: 2px;
	}

	.header-subtitle {
		font-size: 14px;
		color: #666;
	}

	/* 分类选择区域 */
	.category-section {
		background: white;
		padding: 20px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	}

	.category-tabs {
		display: flex;
		gap: 15px;
		overflow-x: auto;
	}

	.category-tab {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 15px 20px;
		background: #f8f9fa;
		border-radius: 12px;
		cursor: pointer;
		transition: all 0.3s ease;
		min-width: 80px;
		border: 2px solid transparent;
	}

	.category-tab.active {
		background: #e3f2fd;
		border-color: #2196f3;
	}

	.category-tab:hover {
		background: #e9ecef;
		transform: translateY(-2px);
	}

	.category-tab.active:hover {
		background: #e3f2fd;
	}

	.category-icon {
		font-size: 24px;
		margin-bottom: 8px;
	}

	.category-name {
		font-size: 14px;
		font-weight: bold;
		color: #333;
	}

	/* 文件列表区域 */
	.file-list-section {
		flex: 1;
		background: #f5f5f5;
		padding: 20px;
		overflow-y: auto;
	}

	.file-list {
		max-width: 800px;
		margin: 0 auto;
	}

	.file-item {
		display: flex;
		align-items: center;
		background: white;
		border-radius: 12px;
		padding: 20px;
		margin-bottom: 15px;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	}

	.file-item:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
	}

	.file-icon {
		font-size: 32px;
		margin-right: 15px;
		color: #ff6b6b;
	}

	.file-info {
		flex: 1;
	}

	.file-name {
		font-size: 16px;
		font-weight: bold;
		color: #333;
		margin-bottom: 5px;
	}

	.file-description {
		font-size: 14px;
		color: #666;
		margin-bottom: 5px;
	}

	.file-size {
		font-size: 12px;
		color: #999;
	}

	.file-arrow {
		font-size: 20px;
		color: #ccc;
		margin-left: 15px;
	}

	/* 下载弹窗 */
	.download-modal {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.5);
		z-index: 1000;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px;
	}

	.modal-content {
		background: white;
		border-radius: 16px;
		width: 100%;
		max-width: 500px;
		max-height: 80vh;
		overflow: hidden;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
	}

	.modal-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 20px;
		border-bottom: 1px solid #e9ecef;
	}

	.modal-title {
		font-size: 18px;
		font-weight: bold;
		color: #333;
	}

	.modal-close {
		width: 32px;
		height: 32px;
		border-radius: 16px;
		background: #f8f9fa;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		font-size: 20px;
		color: #666;
		transition: all 0.3s ease;
	}

	.modal-close:hover {
		background: #e9ecef;
		color: #333;
	}

	.modal-body {
		padding: 20px;
	}

	.file-info-modal {
		margin-bottom: 20px;
	}

	.file-name-modal {
		font-size: 16px;
		font-weight: bold;
		color: #333;
		margin-bottom: 8px;
	}

	.file-description-modal {
		font-size: 14px;
		color: #666;
	}

	.url-container {
		background: #f8f9fa;
		border-radius: 8px;
		padding: 15px;
	}

	.url-label {
		font-size: 14px;
		color: #666;
		margin-bottom: 8px;
	}

	.url-text {
		font-size: 13px;
		color: #333;
		word-break: break-all;
		line-height: 1.5;
		background: white;
		padding: 10px;
		border-radius: 6px;
		border: 1px solid #e9ecef;
	}

	.modal-footer {
		padding: 20px;
		border-top: 1px solid #e9ecef;
		display: flex;
		justify-content: center;
	}

	.copy-btn {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px 24px;
		background: #2196f3;
		color: white;
		border-radius: 8px;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.copy-btn:hover {
		background: #1976d2;
		transform: translateY(-1px);
	}

	.copy-icon {
		font-size: 16px;
	}

	.copy-text {
		font-size: 14px;
		font-weight: bold;
	}

	/* 移动端适配 */
	@media (max-width: 768px) {
		.header {
			padding: 12px 15px;
		}

		.header-title {
			font-size: 16px;
		}

		.header-subtitle {
			font-size: 12px;
		}

		.pdf-section {
			margin: 5px;
		}

		.pdf-toolbar {
			padding: 12px 15px;
		}

		.page-info {
			font-size: 12px;
		}

		.zoom-controls {
			gap: 8px;
		}

		.zoom-btn {
			width: 28px;
			height: 28px;
			font-size: 14px;
		}

		.zoom-display {
			font-size: 12px;
			min-width: 40px;
		}

		.pdf-placeholder {
			padding: 30px 15px;
		}

		.placeholder-icon {
			font-size: 48px;
		}

		.placeholder-title {
			font-size: 20px;
		}

		.placeholder-subtitle {
			font-size: 14px;
		}

		.content-section {
			padding: 15px;
		}

		.section-title {
			font-size: 16px;
		}

		.section-content {
			font-size: 13px;
		}

		.page-navigation {
			padding: 12px 15px;
		}

		.nav-btn {
			padding: 8px 12px;
			font-size: 12px;
		}

		.page-input-field {
			width: 50px;
			height: 32px;
			font-size: 12px;
		}
	}
</style>
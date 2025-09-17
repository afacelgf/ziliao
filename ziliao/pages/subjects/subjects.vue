<template>
	<view class="container">
		<!-- 头部导航 -->
		<view class="header">
			<view class="back-btn" @click="goBack">
				<text class="back-icon">←</text>
			</view>
			<view class="header-title">选择科目</view>
			<view class="grade-info">{{ selectedGrade }}年级</view>
		</view>

		<!-- 科目选择区域 -->
		<view class="subjects-section">
			<view class="subjects-title">请选择学习科目</view>
			<view class="subjects-grid">
				<view 
					class="subject-item" 
					v-for="subject in subjects" 
					:key="subject.id"
					@click="selectSubject(subject)"
				>
					<view class="subject-icon">{{ subject.icon }}</view>
					<view class="subject-name">{{ subject.name }}</view>
					<view class="subject-description">{{ subject.description }}</view>
				</view>
			</view>
		</view>

		<!-- 底部提示 -->
		<view class="footer-tip">
			<text class="tip-text">选择科目后即可开始学习</text>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				selectedGrade: '',
				subjects: [
					{
						id: 1,
						name: '语文',
						icon: '📚',
						description: '阅读理解、作文写作、古诗词'
					},
					{
						id: 2,
						name: '数学',
						icon: '🔢',
						description: '计算、几何、应用题'
					},
					{
						id: 3,
						name: '英语',
						icon: '🌍',
						description: '单词、语法、口语练习'
					}
				]
			}
		},
		onLoad(options) {
			// 获取传递的年级信息
			if (options.grade) {
				this.selectedGrade = options.grade;
			}
		},
		methods: {
			goBack() {
				uni.navigateBack();
			},
			selectSubject(subject) {
				// 跳转到PDF预览页面
				uni.navigateTo({
					url: `/pages/pdf/pdf?grade=${this.selectedGrade}&subject=${subject.name}&subjectId=${subject.id}`
				});
			}
		}
	}
</script>

<style scoped>
	.container {
		min-height: 100vh;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		display: flex;
		flex-direction: column;
	}

	/* 头部导航 */
	.header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 20px;
		color: white;
		position: relative;
	}

	.back-btn {
		width: 40px;
		height: 40px;
		border-radius: 20px;
		background: rgba(255, 255, 255, 0.2);
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

	.header-title {
		font-size: 20px;
		font-weight: bold;
		position: absolute;
		left: 50%;
		transform: translateX(-50%);
	}

	.grade-info {
		font-size: 16px;
		background: rgba(255, 255, 255, 0.2);
		padding: 8px 16px;
		border-radius: 20px;
		backdrop-filter: blur(10px);
	}

	/* 科目选择区域 */
	.subjects-section {
		flex: 1;
		padding: 40px 20px;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}

	.subjects-title {
		font-size: 24px;
		font-weight: bold;
		color: white;
		text-align: center;
		margin-bottom: 40px;
	}

	.subjects-grid {
		display: flex;
		flex-direction: column;
		gap: 20px;
		width: 100%;
		max-width: 400px;
	}

	.subject-item {
		background: rgba(255, 255, 255, 0.95);
		border-radius: 16px;
		padding: 30px 20px;
		text-align: center;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.subject-item:hover {
		transform: translateY(-5px);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
		background: rgba(255, 255, 255, 1);
	}

	.subject-icon {
		font-size: 48px;
		margin-bottom: 15px;
	}

	.subject-name {
		font-size: 24px;
		font-weight: bold;
		color: #333;
		margin-bottom: 10px;
	}

	.subject-description {
		font-size: 14px;
		color: #666;
		line-height: 1.4;
	}

	/* 底部提示 */
	.footer-tip {
		padding: 20px;
		text-align: center;
	}

	.tip-text {
		color: white;
		font-size: 16px;
		opacity: 0.8;
	}

	/* 移动端适配 */
	@media (max-width: 768px) {
		.header {
			padding: 15px;
		}

		.header-title {
			font-size: 18px;
		}

		.grade-info {
			font-size: 14px;
			padding: 6px 12px;
		}

		.subjects-section {
			padding: 30px 15px;
		}

		.subjects-title {
			font-size: 20px;
			margin-bottom: 30px;
		}

		.subjects-grid {
			max-width: 350px;
		}

		.subject-item {
			padding: 25px 15px;
		}

		.subject-icon {
			font-size: 40px;
		}

		.subject-name {
			font-size: 20px;
		}

		.subject-description {
			font-size: 13px;
		}
	}

	@media (max-width: 480px) {
		.header {
			padding: 12px;
		}

		.header-title {
			font-size: 16px;
		}

		.grade-info {
			font-size: 12px;
			padding: 4px 8px;
		}

		.subjects-section {
			padding: 20px 12px;
		}

		.subjects-title {
			font-size: 18px;
			margin-bottom: 25px;
		}

		.subjects-grid {
			max-width: 300px;
			gap: 15px;
		}

		.subject-item {
			padding: 20px 12px;
		}

		.subject-icon {
			font-size: 36px;
			margin-bottom: 12px;
		}

		.subject-name {
			font-size: 18px;
			margin-bottom: 8px;
		}

		.subject-description {
			font-size: 12px;
		}

		.tip-text {
			font-size: 14px;
		}
	}
</style>
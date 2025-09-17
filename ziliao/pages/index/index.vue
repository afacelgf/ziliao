<template>
	<view class="container">
		<!-- 头部标题区域 -->
		<view class="header-section">
			<view class="header-title">均一教育平台</view>
			<view class="header-subtitle">选择您的年级开始学习</view>
		</view>

		<!-- 九宫格年级选择区域 -->
		<view class="grade-section">
			<view class="grade-grid">
				<view 
					class="grade-item" 
					v-for="grade in grades" 
					:key="grade.id"
					:class="{ 'selected': selectedGrade === grade.id }"
					@click="selectGrade(grade.id)"
				>
					<view class="grade-number">{{ grade.name }}</view>
					<view class="grade-label">年级</view>
				</view>
			</view>
		</view>

		<!-- 底部欢迎区域 -->
		<view class="welcome-section">
			<view class="welcome-text">欢迎来到均一教育平台</view>
			<view class="welcome-subtitle">让学习变得更有趣</view>
			<button 
				class="enter-btn" 
				:disabled="!selectedGrade"
				@click="enterSubjects"
			>
				进入学习
			</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: '均一教育平台',
				selectedGrade: null,
				grades: [
					{ id: 1, name: '一' },
					{ id: 2, name: '二' },
					{ id: 3, name: '三' },
					{ id: 4, name: '四' },
					{ id: 5, name: '五' },
					{ id: 6, name: '六' },
					{ id: 7, name: '七' },
					{ id: 8, name: '八' },
					{ id: 9, name: '九' }
				]
			}
		},
		onLoad() {
			
		},
		methods: {
			selectGrade(gradeId) {
				this.selectedGrade = gradeId;
			},
			enterSubjects() {
				if (this.selectedGrade) {
					uni.navigateTo({
						url: `/pages/subjects/subjects?grade=${this.selectedGrade}`
					});
				}
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
		padding: 20px;
	}

	/* 头部标题区域 */
	.header-section {
		text-align: center;
		color: white;
		padding: 40px 0;
	}

	.header-title {
		font-size: 32px;
		font-weight: bold;
		margin-bottom: 10px;
	}

	.header-subtitle {
		font-size: 18px;
		opacity: 0.9;
	}

	/* 九宫格年级选择区域 */
	.grade-section {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px 0;
	}

	.grade-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 20px;
		max-width: 400px;
		width: 100%;
	}

	.grade-item {
		background: rgba(255, 255, 255, 0.9);
		border-radius: 16px;
		padding: 20px;
		text-align: center;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
		min-height: 100px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}

	.grade-item:hover {
		transform: translateY(-5px);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
	}

	.grade-item.selected {
		background: #667eea;
		color: white;
		transform: scale(1.05);
	}

	.grade-number {
		font-size: 28px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.grade-label {
		font-size: 14px;
		opacity: 0.8;
	}

	/* 底部欢迎区域 */
	.welcome-section {
		text-align: center;
		color: white;
		padding: 40px 0;
	}

	.welcome-text {
		font-size: 24px;
		font-weight: bold;
		margin-bottom: 10px;
	}

	.welcome-subtitle {
		font-size: 16px;
		opacity: 0.9;
		margin-bottom: 30px;
	}

	.enter-btn {
		background: #4CAF50;
		color: white;
		border: none;
		border-radius: 25px;
		padding: 15px 40px;
		font-size: 18px;
		font-weight: bold;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
	}

	.enter-btn:hover {
		background: #45a049;
		transform: translateY(-2px);
		box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
	}

	.enter-btn:disabled {
		background: #cccccc;
		cursor: not-allowed;
		transform: none;
		box-shadow: none;
	}

	/* 移动端适配 */
	@media (max-width: 768px) {
		.container {
			padding: 15px;
		}

		.header-title {
			font-size: 28px;
		}

		.header-subtitle {
			font-size: 16px;
		}

		.grade-grid {
			gap: 15px;
			max-width: 350px;
		}

		.grade-item {
			padding: 15px;
			min-height: 80px;
		}

		.grade-number {
			font-size: 24px;
		}

		.welcome-text {
			font-size: 20px;
		}

		.enter-btn {
			padding: 12px 30px;
			font-size: 16px;
		}
	}

	@media (max-width: 480px) {
		.header-section {
			padding: 30px 0;
		}

		.header-title {
			font-size: 24px;
		}

		.header-subtitle {
			font-size: 14px;
		}

		.grade-grid {
			gap: 12px;
			max-width: 300px;
		}

		.grade-item {
			padding: 12px;
			min-height: 70px;
		}

		.grade-number {
			font-size: 20px;
		}

		.grade-label {
			font-size: 12px;
		}

		.welcome-section {
			padding: 30px 0;
		}

		.welcome-text {
			font-size: 18px;
		}

		.welcome-subtitle {
			font-size: 14px;
		}

		.enter-btn {
			padding: 10px 25px;
			font-size: 14px;
		}
	}
</style>
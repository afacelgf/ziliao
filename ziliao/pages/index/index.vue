<template>
	<div class="container">
		<!-- 头部标题区域 -->
		<div class="header-section">
			<div class="header-title">欢迎来到誉享学资料分享平台</div>
		</div>

		<!-- 九宫格年级选择区域 -->
		<div class="grade-section">
			<div class="grade-grid">
				<div 
					class="grade-item" 
					v-for="grade in grades" 
					:key="grade.id"
					:class="{ 'selected': selectedGradeId === grade.id }"
					@click="selectGrade(grade.id, grade.grade_name)"		
				>
					<div class="grade-number">{{ grade.grade_level }}</div>
					<div class="grade-label">{{ grade.grade_name }}</div>
				</div>
			</div>
		</div>

		<!-- 底部欢迎区域 -->
		<div class="welcome-section">
			<div class="header-subtitle">请选择您的对应年级</div>
			<button 
				class="enter-btn" 
				:disabled="!selectedGradeId"
				@click="enterSubjects"
			>
				进入资料库
			</button>
		</div>
	</div>
</template>

<script>
	import { gradeService } from '@/utils/dataService.js'

	export default {
		data() {
			return {
				selectedGradeId: null,
				selectedGradeName: null,
				grades: [],
				loading: false
			}
		},
		mounted() {
			// 页面加载时获取年级列表
			this.loadGrades()
			// 恢复上次选中的年级
			this.restoreSelectedGrade()
		},
		methods: {
			async loadGrades() {
				this.loading = true
				try {
					this.grades = await gradeService.getGradeList()
					console.log('年级列表:', this.grades)
				} catch (error) {
					console.error('加载年级列表失败:', error)
				} finally {
					this.loading = false
				}
			},
			
			selectGrade(gradeId, gradeName) {
				console.log('选择年级:', gradeId, gradeName)
				this.selectedGradeId = gradeId;
				this.selectedGradeName = gradeName;
				// 保存选中的年级到本地存储
				this.saveSelectedGrade(gradeId, gradeName);
			},
			
			// 保存选中的年级到本地存储
			saveSelectedGrade(gradeId, gradeName) {
				try {
					const gradeData = {
						gradeId: gradeId,
						gradeName: gradeName,
						timestamp: Date.now()
					};
					localStorage.setItem('selectedGrade', JSON.stringify(gradeData));
				} catch (error) {
					console.error('保存年级选择失败:', error);
				}
			},
			
			// 从本地存储恢复选中的年级
			restoreSelectedGrade() {
				try {
					const savedGradeStr = localStorage.getItem('selectedGrade');
					if (savedGradeStr) {
						const savedGrade = JSON.parse(savedGradeStr);
						if (savedGrade && savedGrade.gradeId) {
							this.selectedGradeId = savedGrade.gradeId;
							this.selectedGradeName = savedGrade.gradeName;
							console.log('恢复上次选中的年级:', savedGrade);
						}
					}
				} catch (error) {
					console.error('恢复年级选择失败:', error);
				}
			},
			enterSubjects() {
				if (this.selectedGradeId) {
					// 传递选中的地区和年级参数
					this.$router.push({
						path: '/subjects',
						query: { 
							grade_id: this.selectedGradeId,
							grade_name: this.selectedGradeName
						}
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

	/* 地区选择区域 */
	.region-section {
		text-align: center;
		color: white;
		padding: 20px 0;
	}

	.section-title {
		font-size: 20px;
		font-weight: bold;
		margin-bottom: 15px;
		opacity: 0.9;
	}

	.region-tabs {
		display: flex;
		justify-content: center;
		gap: 15px;
		flex-wrap: wrap;
	}

	.region-tab {
		background: rgba(255, 255, 255, 0.2);
		color: white;
		border: 2px solid rgba(255, 255, 255, 0.3);
		border-radius: 25px;
		padding: 10px 25px;
		cursor: pointer;
		transition: all 0.3s ease;
		font-size: 16px;
		font-weight: 500;
	}

	.region-tab:hover {
		background: rgba(255, 255, 255, 0.3);
		transform: translateY(-2px);
	}

	.region-tab.active {
		background: rgba(255, 255, 255, 0.9);
		color: #667eea;
		border-color: rgba(255, 255, 255, 0.9);
		transform: scale(1.05);
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
		margin-bottom: 10px;
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

		.section-title {
			font-size: 18px;
		}

		.region-tab {
			padding: 8px 20px;
			font-size: 14px;
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

		.section-title {
			font-size: 16px;
		}

		.region-tabs {
			gap: 10px;
		}

		.region-tab {
			padding: 6px 15px;
			font-size: 12px;
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
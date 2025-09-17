<template>
	<div class="container">
		<!-- 头部导航 -->
		<div class="header">
			<div class="back-btn" @click="goBack">
				<span class="back-icon">←</span>
			</div>
			<div class="header-title">选择科目</div>
			<div class="grade-info">{{ selectedGrade }}年级</div>
		</div>

		<!-- 科目选择区域 -->
		<div class="subjects-section">
			<div class="subjects-title">请选择学习科目</div>
			<div class="subjects-grid">
				<div 
					class="subject-item" 
					v-for="subject in subjects" 
					:key="subject.id"
					:class="{ 'selected': selectedSubject === subject.id }"
					@click="selectSubject(subject)"
				>
					<div class="subject-icon">{{ subject.icon }}</div>
					<div class="subject-name">{{ subject.name }}</div>
					<div class="subject-description">{{ subject.description }}</div>
				</div>
			</div>
		</div>

		<!-- 底部按钮区域 -->
		<div class="footer-section">
			<div class="selected-info" v-if="selectedSubject">
				<span class="selected-text">已选择：{{ getSelectedSubjectName() }}</span>
			</div>
			<button 
				class="next-btn" 
				:disabled="!selectedSubject"
				@click="goToNextStep"
			>
				下一步
			</button>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				selectedGrade: '一',
				selectedSubject: 1, // 默认选中第一个科目
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
						description: '算术、几何、代数基础'
					},
					{
						id: 3,
						name: '英语',
						icon: '🌍',
						description: '单词、语法、口语练习'
					},
					{
						id: 4,
						name: '科学',
						icon: '🔬',
						description: '自然科学、实验探索'
					},
					{
						id: 5,
						name: '社会',
						icon: '🏛️',
						description: '历史、地理、公民教育'
					},
					{
						id: 6,
						name: '艺术',
						icon: '🎨',
						description: '美术、音乐、创意表达'
					}
				]
			}
		},
		methods: {
			goBack() {
				this.$router.go(-1);
			},
			selectSubject(subject) {
				this.selectedSubject = subject.id;
			},
			getSelectedSubjectName() {
				const subject = this.subjects.find(s => s.id === this.selectedSubject);
				return subject ? subject.name : '';
			},
			goToNextStep() {
				if (this.selectedSubject) {
					this.$router.push('/pdf');
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
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 20px;
		width: 100%;
		max-width: 600px;
	}

	.subject-item {
		background: rgba(255, 255, 255, 0.95);
		border-radius: 16px;
		padding: 25px 15px;
		text-align: center;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
		display: flex;
		flex-direction: column;
		align-items: center;
		border: 3px solid transparent;
	}

	.subject-item:hover {
		transform: translateY(-3px);
		box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
		background: rgba(255, 255, 255, 1);
	}

	.subject-item.selected {
		border-color: #667eea;
		background: rgba(102, 126, 234, 0.1);
		transform: translateY(-3px);
		box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
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

	/* 底部按钮区域 */
	.footer-section {
		padding: 20px;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 15px;
	}

	.selected-info {
		color: white;
		font-size: 16px;
		opacity: 0.9;
	}

	.selected-text {
		background: rgba(255, 255, 255, 0.2);
		padding: 8px 16px;
		border-radius: 20px;
		backdrop-filter: blur(10px);
	}

	.next-btn {
		background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
		color: white;
		border: none;
		border-radius: 25px;
		padding: 15px 40px;
		font-size: 18px;
		font-weight: bold;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
		min-width: 120px;
	}

	.next-btn:hover:not(:disabled) {
		transform: translateY(-2px);
		box-shadow: 0 6px 20px rgba(79, 172, 254, 0.6);
	}

	.next-btn:disabled {
		background: rgba(255, 255, 255, 0.3);
		color: rgba(255, 255, 255, 0.6);
		cursor: not-allowed;
		box-shadow: none;
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
			max-width: 500px;
		}

		.subject-item {
			padding: 20px 12px;
		}

		.subject-icon {
			font-size: 40px;
		}

		.subject-name {
			font-size: 18px;
		}

		.subject-description {
			font-size: 13px;
		}

		.next-btn {
			padding: 12px 30px;
			font-size: 16px;
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
			max-width: 400px;
			gap: 15px;
		}

		.subject-item {
			padding: 18px 10px;
		}

		.subject-icon {
			font-size: 36px;
			margin-bottom: 12px;
		}

		.subject-name {
			font-size: 16px;
			margin-bottom: 8px;
		}

		.subject-description {
			font-size: 12px;
		}

		.next-btn {
			padding: 10px 25px;
			font-size: 14px;
		}
	}
</style>
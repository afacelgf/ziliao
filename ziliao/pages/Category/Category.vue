<template>
	<div class="category-page">
		<!-- 顶部标题栏 -->
		<div class="header">
			<div class="header-left">
				<div class="back-btn" @click="goBack">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
			</div>
			<div class="header-center">
				<h1 class="page-title">资源分类</h1>
			</div>
			<div class="header-right">
				<div class="menu-btn">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<circle cx="12" cy="12" r="1" fill="currentColor"/>
						<circle cx="19" cy="12" r="1" fill="currentColor"/>
						<circle cx="5" cy="12" r="1" fill="currentColor"/>
					</svg>
				</div>
			</div>
		</div>

		<!-- 搜索框 -->
		<div class="search-section">
			<div class="search-box" @click="goToSearch">
				<svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
					<circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
					<path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
				</svg>
				<span class="search-placeholder">点击搜索资源</span>
			</div>
		</div>

		<!-- 主要内容区域 -->
		<div class="main-content">
			<!-- 左侧年级列表 -->
			<div class="grade-sidebar">
				<div 
					class="grade-item" 
					v-for="grade in grades" 
					:key="grade.id"
					:class="{ 'active': selectedGradeId === grade.id }"
					@click="selectGrade(grade.id)"
				>
					{{ grade.name }}
				</div>
			</div>

			<!-- 右侧学科内容 -->
			<div class="subject-content">
				<!-- 年级标签 -->
				<div class="grade-tabs">
					<div 
						class="grade-tab" 
						v-for="tab in gradeTabs" 
						:key="tab.id"
						:class="{ 'active': selectedTab === tab.id }"
						@click="selectTab(tab.id)"
					>
						{{ tab.name }}
					</div>
				</div>

				<!-- 学科列表 -->
				<div class="subjects-list">
					<div 
						class="subject-section" 
						v-for="subject in subjects" 
						:key="subject.id"
					>
						<div class="subject-header">
							<h3 class="subject-title">{{ subject.name }}</h3>
							<span class="view-all">全部 ></span>
						</div>
						<div class="resource-grid">
							<div 
								class="resource-item" 
								v-for="resource in subject.resources" 
								:key="resource.id"
								:class="resource.type"
								@click="selectResource(resource)"
							>
								<div class="resource-icon">{{ resource.icon }}</div>
								<div class="resource-name">{{ resource.name }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Category',
	data() {
		return {
			selectedGradeId: 3, // 默认选中三年级
			selectedTab: 'upper', // 默认选中上册
			grades: [
				{ id: 0, name: '幼升小' },
				{ id: 1, name: '一年级' },
				{ id: 2, name: '二年级' },
				{ id: 3, name: '三年级' },
				{ id: 4, name: '四年级' },
				{ id: 5, name: '五年级' },
				{ id: 6, name: '六年级' },
				{ id: 7, name: '七年级' },
				{ id: 8, name: '八年级' },
				{ id: 9, name: '九年级' }
			],
			gradeTabs: [
				{ id: 'upper', name: '三年级上册' },
				{ id: 'lower', name: '三年级下册' }
			],
			subjects: [
				{
					id: 'chinese',
					name: '语文',
					resources: [
						{ id: 1, name: '电子课本', icon: '语', type: 'orange' },
						{ id: 2, name: '重点汇总', icon: '语', type: 'orange' },
						{ id: 3, name: '生字字帖', icon: '语', type: 'orange' },
						{ id: 4, name: '同步练习', icon: '语', type: 'orange' },
						{ id: 5, name: '基础专项', icon: '语', type: 'orange' },
						{ id: 6, name: '阅读专项', icon: '语', type: 'orange' },
						{ id: 7, name: '作文专项', icon: '语', type: 'orange' },
						{ id: 8, name: '综合练习', icon: '语', type: 'orange' },
						{ id: 9, name: '单元试卷', icon: '语', type: 'orange' },
						{ id: 10, name: '期中试卷', icon: '语', type: 'orange' },
						{ id: 11, name: '期末试卷', icon: '语', type: 'orange' },
						{ id: 12, name: '寒假作业', icon: '语', type: 'orange' }
					]
				},
				{
					id: 'math',
					name: '数学',
					resources: [
						{ id: 13, name: '电子课本', icon: '数', type: 'blue' },
						{ id: 14, name: '重点汇总', icon: '数', type: 'blue' },
						{ id: 15, name: '口算计算', icon: '数', type: 'blue' },
						{ id: 16, name: '同步练习', icon: '数', type: 'blue' },
						{ id: 17, name: '专项合集', icon: '数', type: 'blue' },
						{ id: 18, name: '应用专项', icon: '数', type: 'blue' },
						{ id: 19, name: '奥数思维', icon: '数', type: 'blue' },
						{ id: 20, name: '综合练习', icon: '数', type: 'blue' },
						{ id: 21, name: '单元试卷', icon: '数', type: 'blue' },
						{ id: 22, name: '期中试卷', icon: '数', type: 'blue' },
						{ id: 23, name: '期末试卷', icon: '数', type: 'blue' },
						{ id: 24, name: '寒假作业', icon: '数', type: 'blue' }
					]
				},
				{
					id: 'english',
					name: '英语',
					resources: [
						{ id: 25, name: '电子课本', icon: '英', type: 'green' },
						{ id: 26, name: '重点汇总', icon: '英', type: 'green' },
						{ id: 27, name: '单词听写', icon: '英', type: 'green' },
						{ id: 28, name: '同步练习', icon: '英', type: 'green' }
					]
				}
			]
		}
	},
	methods: {
		goBack() {
			this.$router.go(-1);
		},
		
		goToSearch() {
			this.$router.push('/search');
		},
		
		selectGrade(gradeId) {
			this.selectedGradeId = gradeId;
			// 更新年级标签
			const gradeName = this.grades.find(g => g.id === gradeId)?.name || '三年级';
			this.gradeTabs = [
				{ id: 'upper', name: `${gradeName}上册` },
				{ id: 'lower', name: `${gradeName}下册` }
			];
		},
		
		selectTab(tabId) {
			this.selectedTab = tabId;
		},
		
		selectResource(resource) {
			console.log('选择资源:', resource);
			// 这里可以跳转到具体的资源页面
		}
	}
}
</script>

<style scoped>
.category-page {
	min-height: 100vh;
	background: #f5f5f5;
	display: flex;
	flex-direction: column;
}

/* 顶部标题栏 */
.header {
	background: white;
	height: 60px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 16px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	position: relative;
	z-index: 10;
}

.header-left, .header-right {
	width: 60px;
	display: flex;
	justify-content: center;
}

.header-center {
	flex: 1;
	text-align: center;
}

.back-btn, .menu-btn {
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	border-radius: 8px;
	transition: background-color 0.2s;
}

.back-btn:hover, .menu-btn:hover {
	background: #f0f0f0;
}

.page-title {
	font-size: 18px;
	font-weight: 600;
	color: #333;
	margin: 0;
}

/* 搜索框 */
.search-section {
	background: white;
	padding: 12px 16px;
	border-bottom: 1px solid #f0f0f0;
}

.search-box {
	background: #f8f9fa;
	border-radius: 24px;
	height: 44px;
	display: flex;
	align-items: center;
	padding: 0 16px;
	cursor: pointer;
	transition: background-color 0.2s;
}

.search-box:hover {
	background: #e9ecef;
}

.search-icon {
	color: #999;
	margin-right: 8px;
}

.search-placeholder {
	color: #999;
	font-size: 14px;
}

/* 主要内容区域 */
.main-content {
	flex: 1;
	display: flex;
	background: white;
}

/* 左侧年级列表 */
.grade-sidebar {
	width: 100px;
	background: #f8f9fa;
	border-right: 1px solid #e9ecef;
	padding: 8px 0;
}

.grade-item {
	height: 48px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 14px;
	color: #666;
	cursor: pointer;
	transition: all 0.2s;
	border-right: 3px solid transparent;
}

.grade-item:hover {
	background: #e9ecef;
	color: #333;
}

.grade-item.active {
	background: white;
	color: #007bff;
	border-right-color: #007bff;
	font-weight: 500;
}

/* 右侧学科内容 */
.subject-content {
	flex: 1;
	padding: 16px;
	overflow-y: auto;
}

/* 年级标签 */
.grade-tabs {
	display: flex;
	margin-bottom: 20px;
	border-bottom: 1px solid #e9ecef;
}

.grade-tab {
	padding: 12px 20px;
	font-size: 14px;
	color: #666;
	cursor: pointer;
	border-bottom: 2px solid transparent;
	transition: all 0.2s;
}

.grade-tab:hover {
	color: #333;
}

.grade-tab.active {
	color: #007bff;
	border-bottom-color: #007bff;
	font-weight: 500;
}

/* 学科列表 */
.subjects-list {
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.subject-section {
	background: white;
}

.subject-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.subject-title {
	font-size: 16px;
	font-weight: 600;
	color: #333;
	margin: 0;
}

.view-all {
	font-size: 12px;
	color: #999;
	cursor: pointer;
}

.view-all:hover {
	color: #007bff;
}

/* 资源网格 */
.resource-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 12px;
}

.resource-item {
	background: white;
	border-radius: 8px;
	padding: 12px 8px;
	text-align: center;
	cursor: pointer;
	transition: all 0.2s;
	border: 1px solid #e9ecef;
	min-height: 70px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
}

.resource-item:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.resource-icon {
	width: 32px;
	height: 32px;
	border-radius: 6px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 14px;
	font-weight: bold;
	color: white;
	margin-bottom: 6px;
}

.resource-item.orange .resource-icon {
	background: #ff6b35;
}

.resource-item.blue .resource-icon {
	background: #4a90e2;
}

.resource-item.green .resource-icon {
	background: #7ed321;
}

.resource-name {
	font-size: 12px;
	color: #333;
	line-height: 1.2;
}

/* 响应式设计 */
@media (max-width: 768px) {
	.resource-grid {
		grid-template-columns: repeat(3, 1fr);
		gap: 8px;
	}
	
	.grade-sidebar {
		width: 80px;
	}
	
	.subject-content {
		padding: 12px;
	}
}

@media (max-width: 480px) {
	.resource-grid {
		grid-template-columns: repeat(2, 1fr);
	}
	
	.grade-sidebar {
		width: 70px;
	}
	
	.grade-item {
		font-size: 12px;
		height: 44px;
	}
}
</style>
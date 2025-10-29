<template>
	<div class="home-container">
		<!-- 顶部搜索框 -->
		<div class="search-header">
			<div class="search-box" @click="goToSearch">
				<span class="search-icon">🔍</span>
				<span class="search-placeholder">搜索资料</span>
			</div>
		</div>

		<!-- 轮播图区域 -->
		<div class="carousel-section">
			<div class="carousel-container">
				<div class="carousel-wrapper" :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
					<div 
						class="carousel-slide" 
						v-for="(slide, index) in carouselData" 
						:key="index"
					>
						<img :src="slide.image" :alt="slide.title" />
						<div class="slide-content">
							<h3>{{ slide.title }}</h3>
							<p>{{ slide.subtitle }}</p>
						</div>
					</div>
				</div>
				<div class="carousel-indicators">
					<span 
						v-for="(slide, index) in carouselData" 
						:key="index"
						:class="{ active: currentSlide === index }"
						@click="currentSlide = index"
					></span>
				</div>
			</div>
		</div>

		<!-- 可滚动消息栏 -->
		<div class="message-bar">
			<div class="message-icon">📢</div>
			<div class="message-content">
				<div class="scrolling-text">
					{{ currentMessage }}
				</div>
			</div>
		</div>

		<!-- 年级选择区域 (2行布局) -->
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

		<!-- 展示列表 -->
		<div class="content-section">
			<div class="section-header">
				<h3>📚 最新</h3>
				<span class="update-info">今日更新296份，总计27743份</span>
			</div>
			<div class="content-list">
				<div 
					class="content-item" 
					v-for="item in contentList" 
					:key="item.id"
					@click="viewContent(item)"
				>
					<div class="item-thumbnail">
						<img :src="item.thumbnail" :alt="item.title" />
					</div>
					<div class="item-info">
						<h4 class="item-title">{{ item.title }}</h4>
						<div class="item-meta">
							<span class="item-tag">{{ item.tag }}</span>
							<span class="item-date">{{ item.date }}</span>
						</div>
						<div class="item-stats">
							<span class="download-count">{{ item.downloads }}次下载</span>
						</div>
					</div>
				</div>
			</div>
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
				loading: false,
				// 轮播图数据
				currentSlide: 0,
				carouselData: [
					{
						image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE2MCIgdmlld0JveD0iMCAwIDMwMCAxNjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMTYwIiBmaWxsPSIjRkY2QjM1Ii8+Cjx0ZXh0IHg9IjE1MCIgeT0iNzAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZm9udC13ZWlnaHQ9ImJvbGQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7np7Dnu53ogIHml6fotYTmloY8L3RleHQ+Cjx0ZXh0IHg9IjE1MCIgeT0iMTAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7liKvnu5nlranlrZDnlKjlkKY8L3RleHQ+Cjwvc3ZnPgo=',
						title: '绝绝老旧资料',
						subtitle: '别给孩子用啦'
					},
					{
						image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE2MCIgdmlld0JveD0iMCAwIDMwMCAxNjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMTYwIiBmaWxsPSIjNDA5RUZGIi8+Cjx0ZXh0IHg9IjE1MCIgeT0iNzAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZm9udC13ZWlnaHQ9ImJvbGQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7lhYjplYfmlZnovr7mlbDmm7Q8L3RleHQ+Cjx0ZXh0IHg9IjE1MCIgeT0iMTAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7lhYjplYfmlZnovr7mlbDmm7Tmm7TmlrA8L3RleHQ+Cjwvc3ZnPgo=',
						title: '先锋教辅随教材更新',
						subtitle: '先锋教辅随教材更新'
					}
				],
				// 消息栏数据
				messages: [
					'！本站原创资料均有版权，侵权必究，严禁转载！',
					'欢迎使用先锋学霸资料平台，海量优质资料等你来！',
					'每日更新最新教学资料，助力学习成长！'
				],
				currentMessageIndex: 0,
				// 内容列表数据
				contentList: [
					{
						id: 1,
						title: '七年级（上）生物2.4.2"种"是生物分类的基本的单位（济南版）',
						thumbnail: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgODAgMTAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjVGNUY1IiBzdHJva2U9IiNEREREREQiLz4KPHRleHQgeD0iNDAiIHk9IjUwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPuaWh+aho+mihOiniDwvdGV4dD4KPC9zdmc+Cg==',
						tag: '免费免费',
						date: '2025-10-29',
						downloads: 273
					},
					{
						id: 2,
						title: '八年级（上）数学第一章《三角形》综合测试卷',
						thumbnail: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgODAgMTAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjVGNUY1IiBzdHJva2U9IiNEREREREQiLz4KPHRleHQgeD0iNDAiIHk9IjUwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPuaWh+aho+mihOiniDwvdGV4dD4KPC9zdmc+Cg==',
						tag: '先锋教辅',
						date: '2025-10-28',
						downloads: 456
					}
				]
			}
		},
		computed: {
			currentMessage() {
				return this.messages[this.currentMessageIndex]
			}
		},
		mounted() {
			// 页面加载时获取年级列表
			this.loadGrades()
			// 恢复上次选中的年级
			this.restoreSelectedGrade()
			// 启动轮播图自动播放
			this.startCarousel()
			// 启动消息栏滚动
			this.startMessageRotation()
		},
		beforeUnmount() {
			// 清理定时器
			if (this.carouselTimer) {
				clearInterval(this.carouselTimer)
			}
			if (this.messageTimer) {
				clearInterval(this.messageTimer)
			}
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
				// 直接跳转到科目页面
				this.enterSubjects();
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
			},
			
			goToSearch() {
				// 跳转到搜索页面
				this.$router.push('/search');
			},
			
			// 启动轮播图自动播放
			startCarousel() {
				this.carouselTimer = setInterval(() => {
					this.currentSlide = (this.currentSlide + 1) % this.carouselData.length
				}, 4000)
			},
			
			// 启动消息栏滚动
			startMessageRotation() {
				this.messageTimer = setInterval(() => {
					this.currentMessageIndex = (this.currentMessageIndex + 1) % this.messages.length
				}, 3000)
			},
			
			// 查看内容详情
			viewContent(item) {
				console.log('查看内容:', item)
				// 这里可以跳转到详情页面或者打开PDF
			}
		}
	}
</script>

<style scoped>
	.home-container {
		min-height: 100vh;
		background: linear-gradient(135deg, #4A90E2 0%, #50C9C3 100%);
		padding: 0;
	}

	/* 顶部搜索框 */
	.search-header {
		padding: 20px 16px 16px;
		background: linear-gradient(135deg, #4A90E2 0%, #50C9C3 100%);
	}

	.search-box {
		background: rgba(255, 255, 255, 0.95);
		border-radius: 25px;
		padding: 12px 20px;
		display: flex;
		align-items: center;
		gap: 10px;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
	}

	.search-box:hover {
		background: white;
		transform: translateY(-1px);
		box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
	}

	.search-icon {
		font-size: 18px;
		color: #666;
	}

	.search-placeholder {
		color: #999;
		font-size: 16px;
		flex: 1;
	}

	/* 轮播图区域 */
	.carousel-section {
		padding: 0 16px 16px;
	}

	.carousel-container {
		position: relative;
		border-radius: 12px;
		overflow: hidden;
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
	}

	.carousel-wrapper {
		display: flex;
		transition: transform 0.5s ease;
	}

	.carousel-slide {
		min-width: 100%;
		position: relative;
		height: 160px;
	}

	.carousel-slide img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.slide-content {
		position: absolute;
		bottom: 0;
		left: 0;
		right: 0;
		background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
		color: white;
		padding: 20px;
	}

	.slide-content h3 {
		font-size: 18px;
		font-weight: bold;
		margin: 0 0 5px 0;
	}

	.slide-content p {
		font-size: 14px;
		margin: 0;
		opacity: 0.9;
	}

	.carousel-indicators {
		position: absolute;
		bottom: 10px;
		left: 50%;
		transform: translateX(-50%);
		display: flex;
		gap: 8px;
	}

	.carousel-indicators span {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.5);
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.carousel-indicators span.active {
		background: white;
		transform: scale(1.2);
	}

	/* 消息栏 */
	.message-bar {
		background: rgba(255, 255, 255, 0.95);
		margin: 0 16px 16px;
		border-radius: 8px;
		padding: 12px 16px;
		display: flex;
		align-items: center;
		gap: 12px;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	}

	.message-icon {
		font-size: 16px;
		flex-shrink: 0;
	}

	.message-content {
		flex: 1;
		overflow: hidden;
	}

	.scrolling-text {
		font-size: 14px;
		color: #333;
		white-space: nowrap;
		animation: scroll-text 15s linear infinite;
	}

	@keyframes scroll-text {
		0% { transform: translateX(100%); }
		100% { transform: translateX(-100%); }
	}

	/* 年级选择区域 */
	.grade-section {
		padding: 0 16px 16px;
	}

	.grade-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		grid-template-rows: repeat(3, 1fr);
		gap: 12px;
	}

	.grade-item {
		background: rgba(255, 255, 255, 0.95);
		border-radius: 12px;
		padding: 12px 8px;
		text-align: center;
		cursor: pointer;
		transition: all 0.3s ease;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		min-height: 60px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}

	.grade-item:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		background: white;
	}

	.grade-item.selected {
		background: #4A90E2;
		color: white;
		transform: scale(1.05);
	}

	.grade-number {
		font-size: 18px;
		font-weight: bold;
		margin-bottom: 2px;
	}

	.grade-label {
		font-size: 11px;
		opacity: 0.8;
	}

	/* 内容区域 */
	.content-section {
		background: white;
		border-radius: 16px 16px 0 0;
		margin-top: 8px;
		padding: 20px 16px;
		min-height: 300px;
	}

	.section-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 16px;
		padding-bottom: 12px;
		border-bottom: 1px solid #f0f0f0;
	}

	.section-header h3 {
		font-size: 18px;
		font-weight: bold;
		color: #333;
		margin: 0;
	}

	.update-info {
		font-size: 12px;
		color: #666;
	}

	.content-list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	.content-item {
		display: flex;
		background: #f8f9fa;
		border-radius: 12px;
		padding: 12px;
		cursor: pointer;
		transition: all 0.3s ease;
		border: 1px solid #e9ecef;
	}

	.content-item:hover {
		background: #e3f2fd;
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
	}

	.item-thumbnail {
		width: 60px;
		height: 75px;
		border-radius: 8px;
		overflow: hidden;
		flex-shrink: 0;
		margin-right: 12px;
	}

	.item-thumbnail img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.item-info {
		flex: 1;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.item-title {
		font-size: 14px;
		font-weight: 500;
		color: #333;
		margin: 0 0 8px 0;
		line-height: 1.4;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.item-meta {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 4px;
	}

	.item-tag {
		background: #FF6B35;
		color: white;
		font-size: 10px;
		padding: 2px 6px;
		border-radius: 4px;
		font-weight: 500;
	}

	.item-date {
		font-size: 11px;
		color: #999;
	}

	.item-stats {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	.download-count {
		font-size: 11px;
		color: #666;
	}

	/* 移动端适配 */
	@media (max-width: 768px) {
		.search-header {
			padding: 16px 12px 12px;
		}

		.carousel-section {
			padding: 0 12px 12px;
		}

		.message-bar {
			margin: 0 12px 12px;
		}

		.grade-section {
			padding: 0 12px 12px;
		}

		.grade-grid {
			gap: 8px;
		}

		.grade-item {
			min-height: 50px;
			padding: 8px 4px;
		}

		.grade-number {
			font-size: 16px;
		}

		.grade-label {
			font-size: 10px;
		}

		.content-section {
			padding: 16px 12px;
		}
	}

	@media (max-width: 480px) {
		.search-box {
			padding: 10px 16px;
		}

		.search-placeholder {
			font-size: 14px;
		}

		.carousel-slide {
			height: 140px;
		}

		.slide-content h3 {
			font-size: 16px;
		}

		.slide-content p {
			font-size: 12px;
		}

		.grade-grid {
			gap: 6px;
		}

		.grade-item {
			min-height: 45px;
			padding: 6px 2px;
		}

		.grade-number {
			font-size: 14px;
		}

		.grade-label {
			font-size: 9px;
		}

		.item-thumbnail {
			width: 50px;
			height: 60px;
		}

		.item-title {
			font-size: 13px;
		}
	}
</style>
<template>
	<div class="home-container" ref="homeContainer">
		<!-- 固定头部区域 -->
		<div class="fixed-header" :class="{ 'is-fixed': isHeaderFixed }">

			<!-- 统计信息栏 -->
			<div class="stats-bar">
				<div class="stats-left">
					<div class="stat-item">
						<div class="stat-number">3,072,246</div>
						<div class="stat-label">已收录试卷</div>
					</div>
					<div class="stat-item">
						<div class="stat-number">2,11</div>
						<div class="stat-label">24小时新增</div>
					</div>
				</div>
				<div class="mascot">
				   	<div class="grade-info" @click="showGradeSelector">
						<span class="grade-text">{{ currentGradeDisplay || '五年级上册' }}</span>
						<span class="arrow-icon">⇄</span>
				    </div>
				</div>
			</div>

			<!-- 搜索框 -->
			<div class="search-header">
				<div class="search-box" @click="goToSearch">
					<span class="search-icon">🔍</span>
					<span class="search-placeholder">搜索资料</span>
				</div>
			</div>
		</div>

		<!-- 可滚动内容区域 -->
		<div class="scrollable-content" :style="{ paddingTop: isHeaderFixed ? headerHeight + 'px' : '0px' }">

			<!-- 轮播图区域 -->
			<div class="carousel-section">
				<div class="carousel-container">
					<div class="carousel-wrapper" :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
						<div class="carousel-slide" v-for="(slide, index) in carouselData" :key="index">
							<img :src="slide.image" :alt="slide.title" />
							<div class="slide-content">
								<h3>{{ slide.title }}</h3>
								<p>{{ slide.subtitle }}</p>
							</div>
						</div>
					</div>
					<div class="carousel-indicators">
						<span v-for="(slide, index) in carouselData" :key="index"
							:class="{ active: currentSlide === index }" @click="currentSlide = index"></span>
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
			<div class="category-section">
				<div class="category-grid">
					<div class="category-item" v-for="category in categories" :key="category.id"
						:class="{ 'selected': selectedCategoryId === category.id }"
						@click="selectCategory(category.id, category.name)">
						<div class="category-icon" :style="{ backgroundColor: category.color }">
							<span class="icon-text">{{ category.icon }}</span>
						</div>
						<div class="category-label">{{ category.name }}</div>
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
					<div class="content-item" v-for="item in contentList" :key="item.id" @click="viewContent(item)">
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

		</div> <!-- 关闭 scrollable-content -->

		<!-- 年级选择弹窗 -->
		<div class="grade-selector-overlay" v-if="showGradeSelectorModal" @click="hideGradeSelector">
			<div class="grade-selector-modal" @click.stop>
				<div class="modal-header">
					<h3>切换年级学期</h3>
					<button class="close-btn" @click="hideGradeSelector">✕</button>
				</div>
				<div class="modal-content">
					<!-- 小学年级选项 -->
					<div class="grade-category">
						<div class="category-header">
							<span class="category-tab active">小学</span>
						</div>
						<div class="grade-options">
							<div class="semester-columns">
								<!-- 上册列 -->
								<div class="semester-column">
									<div class="grade-option"
										v-for="grade in primaryGrades.filter(g => g.semester === 1)" :key="grade.id"
										:class="{ 'selected': selectedModalGrade === grade.id }"
										@click="selectModalGrade(grade)">
										{{ grade.name }}
									</div>
								</div>
								<!-- 下册列 -->
								<div class="semester-column">
									<div class="grade-option"
										v-for="grade in primaryGrades.filter(g => g.semester === 2)" :key="grade.id"
										:class="{ 'selected': selectedModalGrade === grade.id }"
										@click="selectModalGrade(grade)">
										{{ grade.name }}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- 关闭 home-container -->
</template>

<script>
import { gradeService } from '@/utils/dataService.js'

export default {
	data() {
		return {
			selectedCategoryId: null,
			selectedCategoryName: null,
			categories: [
				{ id: 'unit', name: '单元', icon: '📝', color: '#FF9500' },
				{ id: 'monthly', name: '月考', icon: '📅', color: '#007AFF' },
				{ id: 'midterm', name: '期中', icon: '📊', color: '#FF9500' },
				{ id: 'final', name: '期末', icon: '🎯', color: '#AF52DE' },
				{ id: 'knowledge', name: '知识点', icon: '⭐', color: '#FF2D92' },
				{ id: 'entrance', name: '开学考', icon: '📈', color: '#007AFF' },
				{ id: 'summer', name: '暑假', icon: '🌞', color: '#34C759' },
				{ id: 'winter', name: '寒假', icon: '❄️', color: '#FF3B30' }
			],
			loading: false,
			// 头部固定相关
			isHeaderFixed: false,
			headerHeight: 0,
			// 年级选择弹窗相关
			showGradeSelectorModal: false,
			selectedModalGrade: null,
			currentGradeDisplay: '五年级上册',
			// 小学年级数据
			primaryGrades: [
				{ id: 'grade1-1', name: '一年级上册', level: 1, semester: 1 },
				{ id: 'grade1-2', name: '一年级下册', level: 1, semester: 2 },
				{ id: 'grade2-1', name: '二年级上册', level: 2, semester: 1 },
				{ id: 'grade2-2', name: '二年级下册', level: 2, semester: 2 },
				{ id: 'grade3-1', name: '三年级上册', level: 3, semester: 1 },
				{ id: 'grade3-2', name: '三年级下册', level: 3, semester: 2 },
				{ id: 'grade4-1', name: '四年级上册', level: 4, semester: 1 },
				{ id: 'grade4-2', name: '四年级下册', level: 4, semester: 2 },
				{ id: 'grade5-1', name: '五年级上册', level: 5, semester: 1 },
				{ id: 'grade5-2', name: '五年级下册', level: 5, semester: 2 },
				{ id: 'grade6-1', name: '六年级上册', level: 6, semester: 1 },
				{ id: 'grade6-2', name: '六年级下册', level: 6, semester: 2 }
			],
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
				},
				{
					id: 3,
					title: '七年级（上）生物2.4.2"种"是生物分类的基本的单位（济南版）',
					thumbnail: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgODAgMTAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjVGNUY1IiBzdHJva2U9IiNEREREREQiLz4KPHRleHQgeD0iNDAiIHk9IjUwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM2NjY2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPuaWh+aho+mihOiniDwvdGV4dD4KPC9zdmc+Cg==',
					tag: '免费免费',
					date: '2025-10-29',
					downloads: 273
				},
				{
					id: 4,
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
		// 恢复上次选中的类别
		this.restoreSelectedCategory()
		// 恢复顶部年级选择
		this.restoreGradeSelection()
		// 启动轮播图自动播放
		this.startCarousel()
		// 启动消息栏滚动
		this.startMessageRotation()
		// 初始化滚动监听
		this.initScrollListener()
		// 计算头部高度
		this.$nextTick(() => {
			this.calculateHeaderHeight()
		})
	},
	beforeUnmount() {
		// 清理定时器
		if (this.carouselTimer) {
			clearInterval(this.carouselTimer)
		}
		if (this.messageTimer) {
			clearInterval(this.messageTimer)
		}
		// 清理滚动监听
		window.removeEventListener('scroll', this.handleScroll)
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

		selectCategory(categoryId, categoryName) {
			console.log('选择类别:', categoryId, categoryName)
			this.selectedCategoryId = categoryId;
			this.selectedCategoryName = categoryName;
			// 保存选中的类别到本地存储
			this.saveSelectedCategory(categoryId, categoryName);
			// 直接跳转到科目页面
			this.enterSubjects();
		},

		// 保存选中的类别到本地存储
		saveSelectedCategory(categoryId, categoryName) {
			try {
				const categoryData = {
					categoryId: categoryId,
					categoryName: categoryName,
					timestamp: Date.now()
				};
				localStorage.setItem('selectedCategory', JSON.stringify(categoryData));
			} catch (error) {
				console.error('保存类别选择失败:', error);
			}
		},

		// 从本地存储恢复选中的类别
		restoreSelectedCategory() {
			try {
				const savedCategoryStr = localStorage.getItem('selectedCategory');
				if (savedCategoryStr) {
					const savedCategory = JSON.parse(savedCategoryStr);
					if (savedCategory && savedCategory.categoryId) {
						this.selectedCategoryId = savedCategory.categoryId;
						this.selectedCategoryName = savedCategory.categoryName;
						console.log('恢复上次选中的类别:', savedCategory);
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
		},

		// 初始化滚动监听
		initScrollListener() {
			window.addEventListener('scroll', this.handleScroll, { passive: true })
		},

		// 处理滚动事件
		handleScroll() {
			const scrollTop = window.pageYOffset || document.documentElement.scrollTop
			// 当滚动超过100px时固定头部
			this.isHeaderFixed = scrollTop > 100
		},

		// 计算头部高度
		calculateHeaderHeight() {
			const headerEl = this.$refs.homeContainer?.querySelector('.fixed-header')
			if (headerEl) {
				// 确保获取准确的高度
				this.headerHeight = headerEl.getBoundingClientRect().height
				console.log('头部高度:', this.headerHeight)
			}
		},

		// 年级选择弹窗相关方法
		showGradeSelector() {
			this.showGradeSelectorModal = true
			// 设置当前选中的年级
			const currentGrade = this.primaryGrades.find(grade => grade.name === this.currentGradeDisplay)
			if (currentGrade) {
				this.selectedModalGrade = currentGrade.id
			}
		},

		hideGradeSelector() {
			this.showGradeSelectorModal = false
			this.selectedModalGrade = null
		},

		selectModalGrade(grade) {
			this.selectedModalGrade = grade.id
			this.currentGradeDisplay = grade.name
			// 保存到本地存储
			this.saveGradeSelection(grade)
			// 关闭弹窗
			this.hideGradeSelector()
		},

		saveGradeSelection(grade) {
			try {
				const gradeData = {
					id: grade.id,
					name: grade.name,
					level: grade.level,
					semester: grade.semester,
					timestamp: Date.now()
				}
				localStorage.setItem('selectedTopGrade', JSON.stringify(gradeData))
			} catch (error) {
				console.error('保存年级选择失败:', error)
			}
		},

		restoreGradeSelection() {
			try {
				const savedGradeStr = localStorage.getItem('selectedTopGrade')
				if (savedGradeStr) {
					const savedGrade = JSON.parse(savedGradeStr)
					if (savedGrade && savedGrade.name) {
						this.currentGradeDisplay = savedGrade.name
						console.log('恢复上次选中的顶部年级:', savedGrade)
					}
				}
			} catch (error) {
				console.error('恢复年级选择失败:', error)
			}
		}
	}
}
</script>

<style scoped>
.home-container {
	min-height: 100vh;
	background: #f5f5f5;
	padding: 0;
}

/* 固定头部区域 */
.fixed-header {
	background: linear-gradient(135deg, #4A90E2 0%, #50C9C3 100%);
	background-image: url('/static/images/homebg.jpg');
	background-size: 100% 100%;
	/* 👈 修改为拉伸铺满模式 */
	background-position: center;
	background-repeat: no-repeat;
	position: relative;
	transition: all 0.3s ease;
	z-index: 1000;
}

.fixed-header::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: linear-gradient(135deg, rgba(74, 144, 226, 0.7) 0%, rgba(80, 201, 195, 0.7) 100%);
	z-index: 1;
}

.fixed-header>* {
	position: relative;
	z-index: 2;
}

.fixed-header.is-fixed {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* 顶部信息栏 */
.top-info-bar {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 16px;
	background: rgba(255, 255, 255, 0.1);
}

.grade-info {
	display: flex;
	align-items: center;
	gap: 8px;
}

.grade-text {
	color: white;
	font-size: 14px;
	font-weight: 500;
}

.arrow-icon {
	color: white;
	font-size: 16px;
}

.app-download {
	background: #FF6B35;
	padding: 4px 12px;
	border-radius: 12px;
}

.download-text {
	color: white;
	font-size: 12px;
	font-weight: 500;
}

/* 统计信息栏 */
.stats-bar {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px 16px;
	background: rgba(255, 255, 255, 0.05);
}

.stats-left {
	display: flex;
	gap: 24px;
}

.stat-item {
	text-align: left;
}

.stat-number {
	color: white;
	font-size: 18px;
	font-weight: bold;
	line-height: 1.2;
}

.stat-label {
	color: rgba(255, 255, 255, 0.8);
	font-size: 11px;
	margin-top: 2px;
}

.mascot {
	display: flex;
	align-items: center;
	gap: 8px;
}

.mascot-character {
	font-size: 24px;
	background: white;
	border-radius: 50%;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.speech-bubble {
	background: white;
	color: #333;
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 11px;
	position: relative;
}

.speech-bubble::before {
	content: '';
	position: absolute;
	left: -6px;
	top: 50%;
	transform: translateY(-50%);
	width: 0;
	height: 0;
	border-top: 6px solid transparent;
	border-bottom: 6px solid transparent;
	border-right: 6px solid white;
}

/* 搜索框 */
.search-header {
	padding: 0px 16px 2px;
}

.search-box {
	background: rgba(255, 255, 255, 0.95);
	border-radius: 25px;
	padding: 2px 20px;
	display: flex;
	align-items: center;
	justify-content: center;
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
}

/* 可滚动内容区域 */
.scrollable-content {
	background: #f5f5f5;
}

/* 轮播图区域 */
.carousel-section {
	padding: 16px 16px 16px;
	background: #f5f5f5;
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
	0% {
		transform: translateX(100%);
	}

	100% {
		transform: translateX(-100%);
	}
}

/* 类别选择区域 */
.category-section {
	padding: 0 16px 16px;
}

.category-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	grid-template-rows: repeat(2, 1fr);
	gap: 12px;
}

.category-item {
	background: rgba(255, 255, 255, 0.95);
	border-radius: 12px;
	padding: 12px 8px;
	text-align: center;
	cursor: pointer;
	transition: all 0.3s ease;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	min-height: 70px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
}

.category-item:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
	background: white;
}

.category-item.selected {
	background: #4A90E2;
	color: white;
	transform: scale(1.05);
}

.category-icon {
	width: 32px;
	height: 32px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 4px;
}

.icon-text {
	font-size: 16px;
	line-height: 1;
}

.category-label {
	font-size: 11px;
	font-weight: 500;
	color: #333;
}

.category-item.selected .category-label {
	color: white;
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

/* 年级选择弹窗样式 */
.grade-selector-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 2000;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
}

.grade-selector-modal {
	background: white;
	border-radius: 16px;
	width: 100%;
	max-width: 400px;
	max-height: 80vh;
	overflow: hidden;
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
	animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
	from {
		opacity: 0;
		transform: translateY(-20px) scale(0.95);
	}

	to {
		opacity: 1;
		transform: translateY(0) scale(1);
	}
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px 20px 16px;
	border-bottom: 1px solid #f0f0f0;
}

.modal-header h3 {
	font-size: 18px;
	font-weight: bold;
	color: #333;
	margin: 0;
}

.close-btn {
	background: none;
	border: none;
	font-size: 20px;
	color: #999;
	cursor: pointer;
	padding: 4px;
	border-radius: 50%;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.2s ease;
}

.close-btn:hover {
	background: #f5f5f5;
	color: #666;
}

.modal-content {
	padding: 0 20px 20px;
	max-height: 60vh;
	overflow-y: auto;
}

.grade-category {
	margin-bottom: 16px;
}

.category-header {
	margin-bottom: 16px;
}

.category-tab {
	display: inline-block;
	padding: 8px 16px;
	background: #4A90E2;
	color: white;
	border-radius: 20px;
	font-size: 14px;
	font-weight: 500;
}

.grade-options {
	padding: 0 8px;
}

.semester-columns {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 16px;
}

.semester-column {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.grade-option {
	background: #f8f9fa;
	border: 2px solid #e9ecef;
	border-radius: 12px;
	padding: 16px 12px;
	text-align: center;
	cursor: pointer;
	transition: all 0.3s ease;
	font-size: 14px;
	font-weight: 500;
	color: #333;
}

.grade-option:hover {
	background: #e3f2fd;
	border-color: #4A90E2;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
}

.grade-option.selected {
	background: #4A90E2;
	border-color: #4A90E2;
	color: white;
	transform: scale(1.05);
	box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
}

/* 让年级信息可点击的样式 */
.grade-info {
	cursor: pointer;
	transition: all 0.2s ease;
	padding: 4px 8px;
	border-radius: 8px;
}

.grade-info:hover {
	background: rgba(255, 255, 255, 0.2);
	transform: scale(1.05);
}

.grade-info:active {
	transform: scale(0.98);
}

/* 移动端适配 */
@media (max-width: 768px) {
	.top-info-bar {
		padding: 6px 12px;
	}

	.grade-text {
		font-size: 13px;
	}

	.download-text {
		font-size: 11px;
	}

	.stats-bar {
		padding: 10px 12px;
	}

	.stats-left {
		gap: 16px;
	}

	.stat-number {
		font-size: 16px;
	}

	.stat-label {
		font-size: 10px;
	}

	.mascot-character {
		width: 35px;
		height: 35px;
		font-size: 20px;
	}

	.speech-bubble {
		font-size: 10px;
		padding: 3px 6px;
	}

	.search-header {
		padding: 0px 12px 2px;
	}

	.carousel-section {
		padding: 12px 12px 12px;
	}

	.message-bar {
		margin: 0 12px 12px;
	}

	.category-section {
		padding: 0 12px 12px;
	}

	.category-grid {
		gap: 8px;
	}

	.category-item {
		min-height: 60px;
		padding: 8px 4px;
	}

	.category-icon {
		width: 28px;
		height: 28px;
	}

	.icon-text {
		font-size: 14px;
	}

	.category-label {
		font-size: 10px;
	}

	.content-section {
		padding: 16px 12px;
	}

	/* 弹窗移动端适配 */
	.grade-selector-overlay {
		padding: 16px;
	}

	.grade-selector-modal {
		max-width: 100%;
	}

	.modal-header {
		padding: 16px 16px 12px;
	}

	.modal-header h3 {
		font-size: 16px;
	}

	.modal-content {
		padding: 0 16px 16px;
	}

	.grade-options {
		padding: 0 4px;
	}

	.semester-columns {
		gap: 12px;
	}

	.semester-column {
		gap: 8px;
	}

	.grade-option {
		padding: 12px 8px;
		font-size: 13px;
	}
}

@media (max-width: 480px) {
	.top-info-bar {
		padding: 4px 8px;
	}

	.grade-text {
		font-size: 12px;
	}

	.app-download {
		padding: 3px 8px;
	}

	.download-text {
		font-size: 10px;
	}

	.stats-bar {
		padding: 8px;
	}

	.stats-left {
		gap: 12px;
	}

	.stat-number {
		font-size: 14px;
	}

	.stat-label {
		font-size: 9px;
	}

	.mascot-character {
		width: 30px;
		height: 30px;
		font-size: 18px;
	}

	.speech-bubble {
		font-size: 9px;
		padding: 2px 4px;
	}

	.search-box {
		padding: 2px 16px;
		justify-content: center;
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

	.category-grid {
		gap: 6px;
	}

	.category-item {
		min-height: 50px;
		padding: 6px 2px;
	}

	.category-icon {
		width: 24px;
		height: 24px;
	}

	.icon-text {
		font-size: 12px;
	}

	.category-label {
		font-size: 9px;
	}

	.item-thumbnail {
		width: 50px;
		height: 60px;
	}

	.item-title {
		font-size: 13px;
	}

	/* 弹窗小屏幕适配 */
	.grade-selector-overlay {
		padding: 12px;
	}

	.modal-header {
		padding: 12px 12px 8px;
	}

	.modal-content {
		padding: 0 12px 12px;
	}

	.grade-option {
		padding: 10px 6px;
		font-size: 12px;
	}
}
</style>
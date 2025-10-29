<template>
	<div class="request-page">
		<!-- 顶部导航栏 -->
		<div class="header">
			<div class="back-btn" @click="goBack">
				<span class="back-icon">←</span>
			</div>
			<div class="header-title">申请资料</div>
			<div class="placeholder"></div>
		</div>

		<!-- 表单内容 -->
		<div class="form-container">
			<div class="form-title">请填写您需要的资料信息</div>
			
			<div class="form-item">
				<label class="form-label">资料名称</label>
				<input 
					type="text" 
					class="form-input" 
					v-model="formData.materialName"
					placeholder="请输入您需要的资料名称"
					maxlength="50"
				/>
			</div>

			<div class="form-item">
				<label class="form-label">年级</label>
				<select class="form-select" v-model="formData.grade">
					<option value="">请选择年级</option>
					<option value="小学一年级">小学一年级</option>
					<option value="小学二年级">小学二年级</option>
					<option value="小学三年级">小学三年级</option>
					<option value="小学四年级">小学四年级</option>
					<option value="小学五年级">小学五年级</option>
					<option value="小学六年级">小学六年级</option>
					<option value="初中一年级">初中一年级</option>
					<option value="初中二年级">初中二年级</option>
					<option value="初中三年级">初中三年级</option>
					<option value="高中一年级">高中一年级</option>
					<option value="高中二年级">高中二年级</option>
					<option value="高中三年级">高中三年级</option>
				</select>
			</div>

			<div class="form-item">
				<label class="form-label">科目</label>
				<select class="form-select" v-model="formData.subject">
					<option value="">请选择科目</option>
					<option value="语文">语文</option>
					<option value="数学">数学</option>
					<option value="英语">英语</option>
					<option value="物理">物理</option>
					<option value="化学">化学</option>
					<option value="生物">生物</option>
					<option value="历史">历史</option>
					<option value="地理">地理</option>
					<option value="政治">政治</option>
					<option value="其他">其他</option>
				</select>
			</div>

			<button class="submit-btn" @click="submitRequest" :disabled="isSubmitting">
				{{ isSubmitting ? '提交中...' : '提交申请' }}
			</button>
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
			formData: {
				materialName: '',
				grade: '',
				subject: ''
			},
			isSubmitting: false,
			toastVisible: false,
			toastMessage: '',
			toastType: 'success',
			toastIcon: '✓'
		}
	},
	methods: {
		// 返回上一页
		goBack() {
			this.$router.back()
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

		// 表单验证
		validateForm() {
			if (!this.formData.materialName.trim()) {
				this.showToast('请输入资料名称', 'error')
				return false
			}
			if (!this.formData.grade) {
				this.showToast('请选择年级', 'error')
				return false
			}
			if (!this.formData.subject) {
				this.showToast('请选择科目', 'error')
				return false
			}
			return true
		},

		// 提交申请
		async submitRequest() {
			if (!this.validateForm()) {
				return
			}

			this.isSubmitting = true

			try {
				// 调用dataService中的提交方法
				const requestData = {
					material_name: this.formData.materialName,
					grade: this.formData.grade,
					subject: this.formData.subject
				}
				await materialService.submitMaterialRequest(requestData)
				this.showToast('申请提交成功！我们会尽快处理您的请求', 'success')
				// 2秒后返回上一页
				setTimeout(() => {
					this.goBack()
				}, 2000)
				
			} catch (error) {
				console.error('提交申请失败:', error)
				this.showToast('提交失败，请稍后重试', 'error')
			} finally {
				this.isSubmitting = false
			}
		}
	}
}
</script>

<style scoped>
.request-page {
	min-height: 100vh;
	background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* 顶部导航栏 */
.header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px 15px;
	background: white;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	position: sticky;
	top: 0;
	z-index: 100;
}

.back-btn {
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	border-radius: 50%;
	transition: background-color 0.3s ease;
}

.back-btn:hover {
	background-color: #f5f5f5;
}

.back-icon {
	font-size: 20px;
	color: #333;
}

.header-title {
	font-size: 18px;
	font-weight: bold;
	color: #333;
}

.placeholder {
	width: 40px;
}

/* 表单容器 */
.form-container {
	padding: 30px 20px;
	max-width: 500px;
	margin: 0 auto;
}

.form-title {
	font-size: 20px;
	font-weight: bold;
	color: #333;
	text-align: center;
	margin-bottom: 30px;
}

.form-item {
	margin-bottom: 25px;
}

.form-label {
	display: block;
	font-size: 16px;
	color: #555;
	margin-bottom: 8px;
	font-weight: 500;
}

.form-input,
.form-select {
	width: 100%;
	padding: 12px 15px;
	border: 2px solid #e1e5e9;
	border-radius: 8px;
	font-size: 16px;
	background: white;
	transition: border-color 0.3s ease;
	box-sizing: border-box;
}

.form-input:focus,
.form-select:focus {
	outline: none;
	border-color: #667eea;
	box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input::placeholder {
	color: #999;
}

.form-select {
	cursor: pointer;
}

.submit-btn {
	width: 100%;
	padding: 15px;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: bold;
	cursor: pointer;
	transition: all 0.3s ease;
	margin-top: 20px;
}

.submit-btn:hover:not(:disabled) {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.submit-btn:disabled {
	opacity: 0.6;
	cursor: not-allowed;
	transform: none;
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

/* 响应式设计 */
@media (max-width: 768px) {
	.form-container {
		padding: 20px 15px;
	}
	
	.form-title {
		font-size: 18px;
	}
	
	.form-input,
	.form-select {
		font-size: 14px;
	}
}
</style>
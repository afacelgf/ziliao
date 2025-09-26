/**
 * 数据服务类
 * 提供年级、科目、资料等业务数据的API接口
 */

import { get, post } from './api.js'

/**
 * 年级相关API
 */
export const gradeService = {
  /**
   * 获取年级列表
   * @returns {Promise<Array>} 年级列表
   */
  async getGradeList() {
    try {
      const response = await get('grades')
      
      // 检查响应状态码
      if (response.code === 200) {
        return response.result
      } else {
        throw new Error(`API返回错误: ${response.message || '未知错误'}`)
      }
    } catch (error) {
      console.warn('获取年级列表失败，使用默认数据:', error.message)
      // 返回默认年级数据
      return [
        { id: 1, grade_name: "一年级", grade_level: 1, description: "小学一年级" },
        { id: 2, grade_name: "二年级", grade_level: 2, description: "小学二年级" },
        { id: 3, grade_name: "三年级", grade_level: 3, description: "小学三年级" },
        { id: 4, grade_name: "四年级", grade_level: 4, description: "小学四年级" },
        { id: 5, grade_name: "五年级", grade_level: 5, description: "小学五年级" },
        { id: 6, grade_name: "六年级", grade_level: 6, description: "小学六年级" }
      ]
    }
  }
}

/**
 * 科目相关API
 */
export const subjectService = {
  /**
   * 获取科目列表
   * @param {number} grade_id - 年级参数
   * @returns {Promise<Array>} 科目列表
   */
  async getSubjectList(grade_id) {
    try {
      const params = { grade_id }
      const response = await get('getSubjectList', params)
      
      // 检查响应状态码
      if (response.code === 200) {
        return response.result
      } else {
        throw new Error(`API返回错误: ${response.message || '未知错误'}`)
      }
    } catch (error) {
      console.warn('获取科目列表失败，使用默认数据:', error.message)
      // 返回默认科目数据
      return [
        { id: 1, name: "语文", icon: "📚" },
        { id: 2, name: "数学", icon: "🔢" },
        { id: 3, name: "英语", icon: "🔤" },
        { id: 4, name: "科学", icon: "🔬" }
      ]
    }
  }
}

/**
 * 资料相关API
 */
export const materialService = {
  /**
   * 获取资料类型列表
   * @param {number} grade_id - 年级参数
   * @param {number} subject_id - 科目参数
   * @returns {Promise<Array>} 资料类型列表
   */
  async getMaterialTypeList(grade_id, subject_id) {
    try {
      const params = { grade_id, subject_id }
      const response = await get('getTypeList', params)
      
      // 检查响应状态码
      if (response.code === 200) {
        return response.result
      } else {
        throw new Error(`API返回错误: ${response.message || '未知错误'}`)
      }
    } catch (error) {
      console.warn('获取资料类型列表失败，使用默认数据:', error.message)
      // 返回默认资料类型数据
      return [
        { id: 1, name: '期中期末试卷', icon: '📚' },
        { id: 2, name: '单元测验卷', icon: '📝' },
        { id: 3, name: '练习题', icon: '📄' },
        { id: 4, name: '参考资料', icon: '📖' }
      ]
    }
  },

   /**
   * 获取资料列表
   * @param {number} grade_id - 年级参数
   * @param {number} subject_id - 科目参数
   * @param {number} type_id - 资料类型参数
   * @returns {Promise<Array>} 资料列表
   */
  async getMaterialList(grade_id, subject_id, type_id) {
    try {
      const params = { grade_id, subject_id, type_id }
      const response = await get('getMaterialList', params)
      
      // 检查响应状态码
      if (response.code === 200) {
        return response.result
      } else {
        throw new Error(`API返回错误: ${response.message || '未知错误'}`)
      }
    } catch (error) {
      console.warn('获取资料列表失败，使用默认数据:', error.message)
      // 返回默认资料数据
      return [
        {
          id: 1,
          name: '小学数学《单元易错专练》1年级上册.pdf',
          description: '1年级上册数学课本',
          size: '12.5MB',
          downloadUrl: 'https://example.com/math-grade1-textbook.pdf'
        }
      ]
    }
  },

  /**
   * 获取资料详情
   * @param {number} materialId - 资料ID
   * @returns {Promise<Object>} 资料详情
   */
  async getMaterialDetail(materialId) {
    try {
      const response = await get(`materials/${materialId}`)
      
      // 检查响应状态码
      if (response.code === 200) {
        return response.result
      } else {
        throw new Error(`API返回错误: ${response.message || '未知错误'}`)
      }
    } catch (error) {
      console.error('获取资料详情失败:', error.message)
      throw error
    }
  }
}


// 导出所有服务
export default {
  gradeService,
  subjectService,
  materialService
}
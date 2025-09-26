/**
 * 网络请求工具类
 * 提供统一的API请求方法和数据获取接口
 */

// API基础配置
const API_CONFIG = {
  // 基础URL，可以根据环境配置
  BASE_URL: process.env.NODE_ENV === 'production' 
    ? 'https://jxpyq666.com/api/ziliao/' 
    : 'https://jxpyq666.com/api/ziliao/',
  
  // 请求超时时间
  TIMEOUT: 10000,
  
  // 默认请求头
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
}

/**
 * 基础请求方法
 * @param {string} url - 请求URL
 * @param {object} options - 请求配置
 * @returns {Promise} 请求结果
 */
async function request(url, options = {}) {
  const {
    method = 'GET',
    data = null,
    headers = {},
    timeout = API_CONFIG.TIMEOUT
  } = options

  const config = {
    method,
    headers: {
      ...API_CONFIG.DEFAULT_HEADERS,
      ...headers
    }
  }

  // 添加请求体
  if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
    config.body = JSON.stringify(data)
  }

  // 处理GET请求的查询参数
  let requestUrl = `${API_CONFIG.BASE_URL}${url}`
  if (data && method === 'GET') {
    const params = new URLSearchParams(data)
    requestUrl += `?${params.toString()}`
  }

  try {
    // 创建超时控制
    const controller = new AbortController()
    const timeoutId = setTimeout(() => controller.abort(), timeout)

    const response = await fetch(requestUrl, {
      ...config,
      signal: controller.signal
    })

    clearTimeout(timeoutId)

    // 检查响应状态
    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status} ${response.statusText}`)
    }

    const result = await response.json()
    
    // 统一的响应格式处理
    if (result.code !== undefined && result.code !== 200) {
      throw new Error(result.message || '请求失败')
    }

    return result.data || result
  } catch (error) {
    console.error('API请求错误:', error)
    // 处理不同类型的错误
    if (error.name === 'AbortError') {
      throw new Error('请求超时，请检查网络连接')
    }
    
    if (!navigator.onLine) {
      throw new Error('网络连接异常，请检查网络设置')
    }
    
    throw error
  }
}

/**
 * GET请求
 * @param {string} url - 请求URL
 * @param {object} params - 查询参数
 * @param {object} options - 其他配置
 * @returns {Promise} 请求结果
 */
export function get(url, params = {}, options = {}) {
  return request(url, {
    method: 'GET',
    data: params,
    ...options
  })
}

/**
 * POST请求
 * @param {string} url - 请求URL
 * @param {object} data - 请求数据
 * @param {object} options - 其他配置
 * @returns {Promise} 请求结果
 */
export function post(url, data = {}, options = {}) {
  return request(url, {
    method: 'POST',
    data,
    ...options
  })
}

/**
 * PUT请求
 * @param {string} url - 请求URL
 * @param {object} data - 请求数据
 * @param {object} options - 其他配置
 * @returns {Promise} 请求结果
 */
export function put(url, data = {}, options = {}) {
  return request(url, {
    method: 'PUT',
    data,
    ...options
  })
}

/**
 * DELETE请求
 * @param {string} url - 请求URL
 * @param {object} options - 其他配置
 * @returns {Promise} 请求结果
 */
export function del(url, options = {}) {
  return request(url, {
    method: 'DELETE',
    ...options
  })
}

// 导出基础请求方法
export { request }
export default {
  get,
  post,
  put,
  delete: del,
  request
}
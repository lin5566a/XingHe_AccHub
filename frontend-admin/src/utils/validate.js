import { Message } from 'element-ui'
/**
 * Created by PanJiaChen on 16/11/18.
 */

// export function validUsername(str) {
//   const valid_map = ['admin', 'editor']
//   return valid_map.indexOf(str.trim()) >= 0
// }

// 检验是否为空
export const isEmpty = function(checkItem, msg = '请输入') {
	// console.log('=kong=')
  if (!(checkItem + '') || !((checkItem + '').trim())) {
    Message.error(msg)
    return true
  }
  return false
}

// 验证是否手机号
export const isMobile = function(phone, msg = '请输入正确的手机号') {
  var pattern = /^1[3-9]\d{9}$/
  if (!pattern.test(phone)) {
    Message.error(msg)
    return false
  }
  return true
}

// 验证是否座机号与手机号
export const isTelNumber = function(phone) {
  var isM = /^[\d\-]+&/
  var value = phone.trim()
  if (isM.test(value)) {
    return true
  } else {
    return false
  }
}

// 复制
export const copyFun = function(value) {
  const inputDom = document.createElement('input')
  inputDom.value = value
  document.body.appendChild(inputDom)
  inputDom.select() // 选择对象
  document.execCommand('Copy') // 执行浏览器复制命令
  document.body.removeChild(inputDom) // 删除DOM
  Message.success('复制成功！')
}

// 纯数字验证
export const isAllNumber = function(checkItem, msg = '不能输入纯数字') {
  var pattern = /^[0-9]*$/
  if (pattern.test(checkItem)) {
    return true
  }
  return false
}
// 正整数验证，包括0
export const isPositiveNumber = function(checkItem, msg = '数量必须为正整数') {
  var pattern = /^[+]{0,1}(\d+)$/
  if (pattern.test(checkItem)) {
    return true
  }
  Message.error(msg)
  return false
}
// 金额验证，最多保留两位小数
export const isMoney = function(checkItem, msg = '金额最多输入两位小数') {
  var pattern = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/
  if (pattern.test(checkItem) || checkItem === 0 || checkItem === '0') {
    return true
  }
  Message.error(msg)
  return false
}
// 金额验证，最多保留三位小数
export const isMoney3 = function(checkItem, msg = '金额最多输入三位小数') {
  var pattern = /^(([1-9][0-9]*)|(([0]\.\d{1,3}|[1-9][0-9]*\.\d{1,3})))$/
  if (pattern.test(checkItem) || checkItem === 0 || checkItem === '0') {
    return true
  }
  Message.error(msg)
  return false
}
// 金额验证，最多保留四位小数
export const isMoney4 = function(checkItem, msg = '金额最多输入四位小数') {
  var pattern = /^(([1-9][0-9]*)|(([0]\.\d{1,4}|[1-9][0-9]*\.\d{1,4})))$/
  if (pattern.test(checkItem) || checkItem === 0 || checkItem === '0') {
    return true
  }
  Message.error(msg)
  return false
}
// 是否包含汉字
export const isIncludeHanzi = function(checkItem, msg = '不能包含汉字') {
  var pattern = /.*[\u4e00-\u9fa5]{1,}.*/
  if (pattern.test(checkItem)) {
    Message.error(msg)
    return true
  }
  return false
}
// 是否存在空格或者换行符
export const isSpaceNewline = function(checkItem, msg = '不能输入空格或者换行符') {
  var space = /\s+/g
  var newline = /[\r\n]/g
  if (space.test(checkItem) || newline.test(checkItem)) {
    Message.error(msg)
    return true
  }
  return false
}
// 是否包含特殊字符
export const isIncludeSpecial = function(checkItem, msg = '不能包含特殊字符') {
  var pattern = /[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]·~！@#￥%……&*（）——\-+={}|《》？：“”【】、；‘’，。、↑↓]/im
  if (pattern.test(checkItem)) {
    Message.error(msg)
    return true
  }
  return false
}

// 纯字母或数字和字母的组合验证
export const isAllNumberAndZimu = function(checkItem, msg = '只能输入纯字母或者数字和字母的组合') {
  var pattern = /^[A-Za-z0-9]+$ | ^[A-Za-z]+$/
  if (!pattern.test(checkItem)) {
    return true
  }
  Message.error(msg)
  return false
}

// 6-16位密码验证
export const isRegPassword = function(text, msg = '请输入正确的密码格式') {
  var regex = /^[0-9a-zA-Z][0-9a-zA-Z.+\-!@#$\%\^\&\*\(\)]{5,17}$/
  if (regex.test(text)) {
    return true
  }
  Message.error(msg)
  return false
}

export const isIdCard = function(text, msg = '请输入正确的身份证号码') {
  // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X。
  var pattern = /(^\d{15}$)|(^\d{17}([0-9]|X)$)/
  if (!pattern.test(text)) {
    Message.error(msg)
    return false
  }

  return true
}
/**
 * kind-of 判断一个变量的类型
 * @param  {*} val
 * @return {String}
 */
export const kindOf = function(val) {
  if (val === null) {
    return 'null'
  }

  if (val === undefined) {
    return 'undefined'
  }

  if (typeof val !== 'object') {
    return typeof val
  }

  if (Array.isArray(val)) {
    return 'array'
  }

  return {}.toString.call(val).slice(8, -1).toLowerCase()
}

/**
 * @param {string} path
 * @returns {Boolean}
 */
export function isExternal(path) {
  return /^(https?:|mailto:|tel:)/.test(path)
}

/**
 * @param {string} str
 * @returns {Boolean}
 */
export function validUsername(str) {
  const valid_map = ['admin', 'editor']
  return valid_map.indexOf(str.trim()) >= 0
}

/** ------------------------  Date over -------------------------- **/


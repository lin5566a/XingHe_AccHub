/**
 * 本地存储操作封装
 * created by 小貂蝉 2020-03-31
 */

//  暴露
// export default {
//   get(key) {
// 	  if(window.localStorage.getItem(key) && window.localStorage.getItem(key) != "undefined"){
//       let data = null
//       try {
// 	  	data = JSON.parse(window.localStorage.getItem(key));
// 	  } catch (error) {
// 	  	data = window.localStorage.getItem(key)
// 	  }
// 	  	return data
// 	  }else{
// 	  	return false
// 	  }
	  
//     // console.log(window.localStorage.getItem(key))
//     // return JSON.parse(window.localStorage.getItem(key));
//   },
//   set(key, value) {
//     // console.log(value)
//     window.localStorage.setItem(key, JSON.stringify(value));
//   },
//   remove(key) {
//     window.localStorage.removeItem(key);
//   },
//   clear() {
//     window.localStorage.clear();
//   }
// };
const emitStorageChange = (key, newValue, oldValue, source = 'local') => {
  const event = new CustomEvent('local-storage-changed', {
    detail: { key, newValue, oldValue, source } // source 用于标记是本窗口触发还是 storage 事件触发
  });
  window.dispatchEvent(event);
};

export default {
  get(key) {
    const raw = window.localStorage.getItem(key);
    if (raw && raw !== 'undefined') {
      try { return JSON.parse(raw); } catch { return raw; }
    }
    return false;
  },
  set(key, value) {
    const oldRaw = window.localStorage.getItem(key);
    const oldValue = oldRaw && oldRaw !== 'undefined' ? (() => {
      try { return JSON.parse(oldRaw); } catch { return oldRaw; }
    })() : null;

    window.localStorage.setItem(key, JSON.stringify(value));
    emitStorageChange(key, value, oldValue, 'local');
  },
  remove(key) {
    const oldRaw = window.localStorage.getItem(key);
    const oldValue = oldRaw && oldRaw !== 'undefined' ? (() => {
      try { return JSON.parse(oldRaw); } catch { return oldRaw; }
    })() : null;

    window.localStorage.removeItem(key);
    emitStorageChange(key, null, oldValue, 'local');
  },
  clear() {
    window.localStorage.clear();
    emitStorageChange(null, null, null, 'local');
  }
};
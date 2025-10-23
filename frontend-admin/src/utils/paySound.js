// 播放提示音的核心函数
const playNotificationSound = () => {
    try {
      // 创建音频上下文
      const audioContext = new (window.AudioContext || window.webkitAudioContext)()
      
      // 创建振荡器
      const oscillator = audioContext.createOscillator()
      const gainNode = audioContext.createGain()
      
      // 连接节点
      oscillator.connect(gainNode)
      gainNode.connect(audioContext.destination)
      
      // 设置音频参数
      oscillator.frequency.setValueAtTime(800, audioContext.currentTime) // 800Hz
      oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1) // 600Hz
      
      gainNode.gain.setValueAtTime(0.1, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2)
      
      // 播放音频
      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.2)
    } catch (error) {
      console.warn('无法播放提示音:', error)
    }
    console.log('播放提示音')
  }
  export default playNotificationSound
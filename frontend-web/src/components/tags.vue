<template>
    <span :style="getTagStyle()" class="tag-item text-white text-xs px-2 py-1 mr-2" :class="getTagClass()">       
        <slot> {{ tag }}</slot>
    </span>
</template>
<script setup>
/**
 * 标签组件
 * @param {String} tag 标签内容
 * @param {String} type 标签类型
 * @param {String} bgColor 标签背景颜色
 * @param {String} textColor 标签文字颜色
 * @param {String} rounded 标签圆角
 * 优先使用bgColor和textColor，其次使用type
*/ 

import { defineProps } from 'vue'
const props = defineProps({
    tag: {
        type: String, 
        default:''
    },
    type:{
        type: String,
        default: 'primary'
    },
    bgColor:{
        type: String,
        default: ''
    },
    textColor:{
        type: String,
        default: ''
    },
    rounded:{
        type: String,
        default: 'rounded-md'
    }
})
const getTagStyle = () => {
    let bgColor="";
    let textColor = "";
    if(props.bgColor){
        bgColor = `background: ${props.bgColor}`
    }
    if(props.textColor){
        textColor = `color: ${props.textColor}`
    }
    if(bgColor || textColor){
        return `${bgColor};${textColor};`
    }
    if(props.type === 'primary'){
        return 'background-color: var(--primary-color);color: #fff;'
    }else if(props.type === 'success'){
        return 'background-color: var(--success-color);color: #fff;'
    }else if(props.type === 'warning'){
        return 'background-color: var(--warning-color);color: #fff;'
    }else if(props.type === 'danger'){
        return 'background-color: var(--danger-color);color: #fff;'
    }
}
const getTagClass = () => {
    return props.rounded ? props.rounded : 'rounded-md'
}
</script>
<style scoped>
    .tag-item{
        background-color: #009688;
        color: #fff;
    }
</style>

<template>
    <div>
      <h1>在 Vue 中使用 React 图标</h1>
      <div id="react-icon"></div>
    </div>
  </template>
  
  <script setup>
  import { onMounted } from 'vue';
  import React from 'react';
  import ReactDOM from 'react-dom';
  import ReactIconWrapper from './ReactIconWrapper.jsx';
  
  onMounted(() => {
    ReactDOM.render(<ReactIconWrapper />, document.getElementById('react-icon'));
  });
  </script>
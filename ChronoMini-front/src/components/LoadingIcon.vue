<template>
  <div class="loading">
    <div class="container">
      <font-awesome-icon icon="fa-solid fa-child" class="children" />
    </div>
  </div>
</template>

<style scoped>

.loading {
  z-index: 9999;
  width: 100vw;
  height: 100vh; 
  background: linear-gradient(-45deg, #78BB99, #a0aba6);
  opacity: 0.8; 
  position: fixed; 
  top: 0; 
  left: 0; 
  display: flex; 
  justify-content: center; 
  align-items: center; 
}


.container {
  margin: 0; 
  height: auto; 
}

.children {
  color:black;
  font-size: 30px;
  animation: rotation 2s infinite;
  height: 100px;
}

@keyframes rotation {
  to {
    transform: rotateY(360deg); 
  }
}
</style>